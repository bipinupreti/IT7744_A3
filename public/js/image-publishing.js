const form = document.querySelector('#publish-image');
const statusMessage = document.querySelector('#publish-status');
const gallery = document.querySelector('#my-images');
const publishButton = form.querySelector('button[type="submit"]');

let uploadRequestId;

function clearUploadRequestId() {
    uploadRequestId = undefined;
}

form.addEventListener('input', clearUploadRequestId);
form.addEventListener('change', clearUploadRequestId);

const deleteStatus = document.createElement('p');
deleteStatus.setAttribute('role', 'status');
gallery.before(deleteStatus);

const filters = {
    none: 'none',
    grayscale: 'grayscale(1)',
    sepia: 'sepia(1)',
};

function createDeleteForm(url) {
    const deleteForm = document.createElement('form');
    deleteForm.method = 'POST';
    deleteForm.action = url;
    deleteForm.dataset.asyncDelete = '';

    const fields = {
        _token: form.elements.namedItem('_token').value,
        _method: 'DELETE',
    };

    for (const [name, value] of Object.entries(fields)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        deleteForm.append(input);
    }

    const button = document.createElement('button');
    button.type = 'submit';
    button.textContent = 'Delete';
    deleteForm.append(button);

    return deleteForm;
}

function addImageToGallery(post) {
    const cardId = `image-post-${post.id}`;

    if (document.getElementById(cardId)) {
        return;
    }

    const card = document.createElement('article');
    card.id = cardId;

    const title = document.createElement('h3');
    const image = document.createElement('img');
    const description = document.createElement('p');

    title.textContent = post.title;
    description.textContent = post.description;
    image.src = post.image_url;
    image.alt = post.title;
    image.width = 240;
    image.style.filter = Object.hasOwn(filters, post.filter)
        ? filters[post.filter]
        : 'none';

    card.append(title, image, description, createDeleteForm(post.delete_url));
    gallery.querySelector('#no-images')?.remove();
    gallery.prepend(card);
}

form.addEventListener('submit', (event) => {
    event.preventDefault();

    if (publishButton.disabled) {
        return;
    }

    const data = new FormData(form);
    const title = data.get('title').trim();
    const description = data.get('description').trim();
    const image = data.get('image');

    if (!title || title.length > 120 || !description || description.length > 1000) {
        statusMessage.textContent = 'Enter a title and description within the character limits.';
        return;
    }

    if (!Object.hasOwn(filters, data.get('filter'))) {
        statusMessage.textContent = 'Choose a valid filter.';
        return;
    }

    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

    if (!image || !allowedTypes.includes(image.type) || !image.size || image.size > 2 * 1024 * 1024) {
        statusMessage.textContent = 'Choose a JPG, PNG or WebP image up to 2 MB.';
        return;
    }

    data.set('title', title);
    data.set('description', description);

    // Reuse the request ID when retrying the same form values.
    uploadRequestId ??= window.crypto.randomUUID();
    const submittedRequestId = uploadRequestId;
    data.set('upload_request_id', submittedRequestId);

    publishButton.disabled = true;
    statusMessage.textContent = 'Publishing…';

    const request = new XMLHttpRequest();
    request.open('POST', form.action);
    request.responseType = 'json';
    request.timeout = 30000;
    request.setRequestHeader('Accept', 'application/json');
    request.setRequestHeader('Authorization', `Bearer ${form.dataset.uploadToken}`);

    request.addEventListener('load', () => {
        if ([200, 201].includes(request.status) && request.response?.delete_url) {
            addImageToGallery(request.response);

            // Preserve any new edits made while this upload was in progress.
            if (uploadRequestId === submittedRequestId) {
                form.reset();
                clearUploadRequestId();
            }

            statusMessage.textContent = request.status === 201
                ? 'Image published.'
                : 'This upload was already saved.';
            return;
        }

        if ([401, 403, 419].includes(request.status)) {
            statusMessage.textContent = 'Reload the page or sign in again, then retry.';
            return;
        }

        const errors = request.response?.errors;
        statusMessage.textContent = errors
            ? Object.values(errors).flat().join(' ')
            : 'The image could not be published.';
    });

    request.addEventListener('error', () => {
        statusMessage.textContent = 'Connection failed. Retry without changing the form to check this upload.';
    });

    request.addEventListener('timeout', () => {
        statusMessage.textContent = 'The request timed out. Retry without changing the form to check this upload.';
    });

    request.addEventListener('loadend', () => {
        publishButton.disabled = false;
    });

    request.send(data);
});

gallery.addEventListener('submit', async (event) => {
    const deleteForm = event.target;

    if (!deleteForm.matches('[data-async-delete]')) {
        return;
    }

    event.preventDefault();

    const button = deleteForm.querySelector('button[type="submit"]');

    if (button.disabled || !window.confirm('Delete this image post?')) {
        return;
    }

    button.disabled = true;
    deleteStatus.textContent = 'Deleting…';

    try {
        const response = await fetch(deleteForm.action, {
            method: 'POST',
            headers: { Accept: 'application/json' },
            body: new FormData(deleteForm),
        });

        if (response.status === 401) {
            deleteStatus.textContent = 'Sign in again to delete images.';
            return;
        }

        if (response.status === 419) {
            deleteStatus.textContent = 'Reload the dashboard and try again.';
            return;
        }

        if (!response.ok) {
            throw new Error('Delete failed.');
        }

        const { message } = await response.json();
        deleteForm.closest('article').remove();
        deleteStatus.textContent = message;

        if (!gallery.querySelector('article')) {
            const emptyMessage = document.createElement('p');
            emptyMessage.id = 'no-images';
            emptyMessage.textContent = 'You haven’t published any images yet.';
            gallery.append(emptyMessage);
        }
    } catch {
        deleteStatus.textContent = 'The image could not be deleted. Please try again.';
    } finally {
        button.disabled = false;
    }
});