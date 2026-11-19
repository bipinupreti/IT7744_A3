const gallery = document.querySelector('#image-gallery');
const buttons = gallery.querySelectorAll('button[data-url]');
const heading = gallery.querySelector('#gallery-heading');
const statusMessage = gallery.querySelector('#gallery-status');
const results = gallery.querySelector('#gallery-results');

const filters = new Map([
    ['none', 'none'],
    ['grayscale', 'grayscale(1)'],
    ['sepia', 'sepia(1)'],
]);

let activeRequest;

function createImageCard(post) {
    const card = document.createElement('article');
    const title = document.createElement('h3');
    const image = document.createElement('img');
    const description = document.createElement('p');

    title.textContent = post.title;
    description.textContent = post.description;
    image.src = post.image_url;
    image.alt = post.title;
    image.width = 240;
    image.loading = 'lazy';
    image.style.filter = filters.get(post.filter) ?? 'none';

    card.append(title, image, description);
    return card;
}

async function loadGallery(button) {
    activeRequest?.abort();

    const request = new AbortController();
    activeRequest = request;

    for (const item of buttons) {
        item.setAttribute('aria-pressed', String(item === button));
    }

    heading.textContent = button.textContent.trim();
    statusMessage.textContent = 'Loading images…';
    results.replaceChildren();

    try {
        const response = await fetch(button.dataset.url, {
            headers: { Accept: 'application/json' },
            signal: request.signal,
        });

        if (response.status === 401) {
            statusMessage.textContent = 'Your session ended. Log in again to view these images.';
            return;
        }

        if (!response.ok) {
            throw new Error('The gallery request failed.');
        }

        const { images } = await response.json();

        if (request.signal.aborted) {
            return;
        }

        results.replaceChildren(...images.map(createImageCard));
        statusMessage.textContent = images.length === 0
            ? 'There are no images in this view yet.'
            : `${images.length} ${images.length === 1 ? 'image' : 'images'} shown.`;
    } catch {
        if (!request.signal.aborted) {
            statusMessage.textContent = 'Could not load images. Select a gallery view to retry.';
        }
    }
}

for (const button of buttons) {
    button.addEventListener('click', () => loadGallery(button));
}

loadGallery(buttons[0]);