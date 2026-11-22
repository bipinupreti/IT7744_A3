const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const { resolve } = require('node:path');
const test = require('node:test');
const { runInNewContext } = require('node:vm');

const publishingScript = readFileSync(
    resolve(__dirname, '../../public/js/image-publishing.js'),
    'utf8',
);

function startPublishing({ type = 'image/png', size = 1024 } = {}) {
    const formListeners = new Map();
    const requests = [];
    const publishButton = { disabled: false };
    const status = { textContent: '' };
    let generatedIds = 0;

    const form = {
        action: '/api/image-posts',
        dataset: { uploadToken: 'test-session-token' },
        querySelector: () => publishButton,
        addEventListener: (name, handler) => formListeners.set(name, handler),
    };

    class TestFormData extends Map {
        constructor() {
            super([
                ['title', '  Test photo  '],
                ['description', '  Test description  '],
                ['filter', 'none'],
                ['image', { type, size }],
            ]);
        }
    }

    class TestRequest {
        headers = {};
        listeners = new Map();

        open(method, url) {
            this.method = method;
            this.url = url;
        }

        setRequestHeader(name, value) {
            this.headers[name] = value;
        }

        addEventListener(name, handler) {
            this.listeners.set(name, handler);
        }

        send(data) {
            this.data = data;
            requests.push(this);
        }

        fail() {
            this.listeners.get('error')();
            this.listeners.get('loadend')();
        }
    }

    const elements = {
        '#publish-image': form,
        '#publish-status': status,
        '#my-images': { before() {}, addEventListener() {} },
    };

    runInNewContext(publishingScript, {
        document: {
            querySelector: (selector) => elements[selector],
            createElement: () => ({ setAttribute() {} }),
        },
        window: {
            crypto: {
                randomUUID() {
                    generatedIds += 1;
                    return `upload-${generatedIds}`;
                },
            },
        },
        FormData: TestFormData,
        XMLHttpRequest: TestRequest,
    });

    return {
        requests,
        status,
        publishButton,
        submit: () => formListeners.get('submit')({ preventDefault() {} }),
    };
}

test('rejects an unsupported file type before sending a request', () => {
    const publishing = startPublishing({ type: 'text/plain' });
    publishing.submit();

    assert.equal(publishing.requests.length, 0);
    assert.match(publishing.status.textContent, /JPG, PNG or WebP/);
});

test('rejects an image larger than 2 MB before sending a request', () => {
    const publishing = startPublishing({ size: 2 * 1024 * 1024 + 1 });
    publishing.submit();

    assert.equal(publishing.requests.length, 0);
    assert.match(publishing.status.textContent, /up to 2 MB/);
});

test('sends valid uploads with trimmed text and an authorisation header', () => {
    const publishing = startPublishing();
    publishing.submit();

    assert.equal(publishing.requests.length, 1);
    const [request] = publishing.requests;

    assert.equal(request.method, 'POST');
    assert.equal(request.url, '/api/image-posts');
    assert.equal(request.headers.Accept, 'application/json');
    assert.equal(request.headers.Authorization, 'Bearer test-session-token');
    assert.equal(request.data.get('title'), 'Test photo');
    assert.equal(request.data.get('description'), 'Test description');
    assert.ok(request.data.get('upload_request_id'));

    assert.equal(publishing.publishButton.disabled, true);
    publishing.submit();
    assert.equal(publishing.requests.length, 1);
});

test('reuses the request ID when an unchanged upload is retried', () => {
    const publishing = startPublishing();

    publishing.submit();
    const [firstRequest] = publishing.requests;
    firstRequest.fail();
    assert.equal(publishing.publishButton.disabled, false);

    publishing.submit();

    assert.equal(publishing.requests.length, 2);
    const retry = publishing.requests[1];
    assert.ok(firstRequest.data.get('upload_request_id'));
    assert.equal(
        retry.data.get('upload_request_id'),
        firstRequest.data.get('upload_request_id'),
    );
});