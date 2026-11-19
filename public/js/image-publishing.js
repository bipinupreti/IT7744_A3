const form = document.querySelector('#publish-image');
const statusMessage = document.querySelector('#publish-status');
const gallery = document.querySelector('#my-images');
const publishButton = form.querySelector('button[type="submit"]');

const filters = {
    none: 'none',
    grayscale: 'grayscale(1)',
    sepia: 'sepia(1)',
};

function addImageToGallery(post) {
    const card = document.createElement('article');
    const title = document.createElement('h3');
    const image = document.createElement('img');
    const description = document.createElement('p');

    title.textContent = post.title;
    description.textContent = post.description;
    image.src = post.image_url;
    image.alt = post.title;
    image.width = 240;
    image.style.filter = filters[post.filter] ?? 'none';

    card.append(title, image, description);
    document.querySelector('#no-images')?.remove();
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

    if (!image || !allowedTypes.includes(image.type) || image.size > 2 * 1024 * 1024) {
        statusMessage.textContent = 'Choose a JPG, PNG or WebP image up to 2 MB.';
        return;
    }

    data.set('title', title);
    data.set('description', description);
    publishButton.disabled = true;
    statusMessage.textContent = 'Publishing…';

    const request = new XMLHttpRequest();
    request.open('POST', form.action);
    request.responseType = 'json';
    request.setRequestHeader('Accept', 'application/json');
    request.setRequestHeader('Authorization', `Bearer ${form.dataset.uploadToken}`);

    request.addEventListener('load', () => {
        if (request.status === 201 && request.response) {
            addImageToGallery(request.response);
            form.reset();
            statusMessage.textContent = 'Image published.';
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
        statusMessage.textContent = 'Connection failed. Please try again.';
    });

    request.addEventListener('loadend', () => {
        publishButton.disabled = false;
    });

    request.send(data);
});