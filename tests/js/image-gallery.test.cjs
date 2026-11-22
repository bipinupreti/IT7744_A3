const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const { resolve } = require('node:path');
const test = require('node:test');
const { runInNewContext } = require('node:vm');

const galleryScript = readFileSync(
    resolve(__dirname, '../../public/js/image-gallery.js'),
    'utf8',
);

async function startGallery({ signedIn = true, expired = false } = {}) {
    const requests = [];
    let onMount;
    let render;

    const root = {
        dataset: {
            loggedIn: String(signedIn),
            latestUrl: '/api/image-posts/latest',
            mineUrl: '/api/image-posts/mine',
            othersUrl: '/api/image-posts/others',
        },
    };

    // Simulate the Vue functions the gallery uses to manage state and render.
    const Vue = {
        ref: (value) => ({ value }),
        computed: (getter) => ({
            get value() {
                return getter();
            },
        }),
        h(tag, props, children) {
            if (typeof props === 'string' || Array.isArray(props)) {
                return { tag, props: {}, children: props };
            }
            return { tag, props, children };
        },
        onMounted(callback) {
            onMount = callback;
        },
        createApp(component) {
            return {
                mount(element) {
                    assert.equal(element, root);
                    render = component.setup();
                },
            };
        },
    };

    runInNewContext(galleryScript, {
        window: { Vue },
        document: {
            querySelector(selector) {
                assert.equal(selector, '#image-gallery');
                return root;
            },
        },
        AbortController,
        console,
        async fetch(url) {
            requests.push(url);

            if (expired && url === root.dataset.mineUrl) {
                return { status: 401 };
            }

            return {
                ok: true,
                async json() {
                    return { images: [] };
                },
            };
        },
    });

    await onMount();

    return {
        requests,
        buttons: () => render()[1].children,
        status: () => render()[2].children[1].children,
    };
}

test('guests can load Latest Images and see the empty-gallery message', async () => {
    const gallery = await startGallery({ signedIn: false });

    assert.deepEqual(gallery.requests, ['/api/image-posts/latest']);
    assert.deepEqual(
        Array.from(gallery.buttons(), (button) => button.children),
        ['Latest Images'],
    );
    assert.equal(gallery.status(), 'There are no images in this view yet.');
});

test('signed-in users can request all three gallery views', async () => {
    const gallery = await startGallery();

    for (const label of ['My Images', 'Other Users’ Images', 'Latest Images']) {
        const button = gallery.buttons().find((item) => item.children === label);
        assert.ok(button, `Expected a ${label} button`);
        await button.props.onClick();

        const selected = gallery.buttons().find((item) => item.children === label);
        assert.equal(selected.props['aria-pressed'], 'true');
    }

    assert.deepEqual(gallery.requests, [
        '/api/image-posts/latest',
        '/api/image-posts/mine',
        '/api/image-posts/others',
        '/api/image-posts/latest',
    ]);
});

test('an expired session hides the restricted gallery views', async () => {
    const gallery = await startGallery({ expired: true });
    const myImages = gallery.buttons().find((button) => button.children === 'My Images');

    assert.ok(myImages);
    await myImages.props.onClick();

    assert.equal(gallery.requests.at(-1), '/api/image-posts/mine');
    assert.deepEqual(
        Array.from(gallery.buttons(), (button) => button.children),
        ['Latest Images'],
    );
    assert.match(gallery.status(), /your session ended/i);
});