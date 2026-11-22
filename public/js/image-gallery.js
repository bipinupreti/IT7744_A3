(() => {
    const root = document.querySelector('#image-gallery');
    if (!root) return;

    if (!window.Vue) {
        root.textContent = 'The gallery could not start. Check that vue.global.prod.js loads first.';
        return;
    }

    const { createApp, h, ref, computed, onMounted } = window.Vue;

    const filters = new Map([
        ['none', 'none'],
        ['grayscale', 'grayscale(1)'],
        ['sepia', 'sepia(1)'],
    ]);

    const latest = {
        name: 'latest',
        label: 'Latest Images',
        url: root.dataset.latestUrl,
    };

    createApp({
        setup() {
            const signedIn = ref(root.dataset.loggedIn === 'true');
            const active = ref('latest');
            const images = ref([]);
            const status = ref('Loading images…');
            let currentRequest;

            const views = computed(() => signedIn.value ? [
                latest,
                { name: 'mine', label: 'My Images', url: root.dataset.mineUrl },
                { name: 'others', label: 'Other Users’ Images', url: root.dataset.othersUrl },
            ] : [latest]);

            async function loadGallery(view) {
                currentRequest?.abort();
                const request = new AbortController();
                currentRequest = request;

                active.value = view.name;
                images.value = [];
                status.value = 'Loading images…';

                try {
                    if (!view.url) throw new Error('Missing gallery URL.');

                    const response = await fetch(view.url, {
                        headers: { Accept: 'application/json' },
                        signal: request.signal,
                    });

                    if (request.signal.aborted) return;

                    if (response.status === 401) {
                        signedIn.value = false;
                        active.value = null;
                        status.value = 'Your session ended. Sign in again or select Latest Images.';
                        return;
                    }

                    if (!response.ok) {
                        throw new Error(`Gallery request returned ${response.status}.`);
                    }

                    const data = await response.json();
                    if (request.signal.aborted) return;
                    if (!Array.isArray(data.images)) throw new Error('Invalid gallery response.');

                    images.value = data.images;
                    status.value = images.value.length
                        ? `${images.value.length} ${images.value.length === 1 ? 'image' : 'images'} shown.`
                        : 'There are no images in this view yet.';
                } catch (error) {
                    if (!request.signal.aborted) {
                        console.error('Could not load gallery:', error);
                        status.value = 'Could not load images. Select a gallery view to retry.';
                    }
                }
            }

            onMounted(() => loadGallery(latest));

            return () => [
                h('h1', 'Image Gallery'),
                h('nav', { 'aria-label': 'Gallery views' }, views.value.map((view) =>
                    h('button', {
                        key: view.name,
                        type: 'button',
                        'aria-pressed': String(active.value === view.name),
                        onClick: () => loadGallery(view),
                    }, view.label),
                )),
                h('section', { 'aria-labelledby': 'gallery-heading' }, [
                    h('h2', { id: 'gallery-heading' },
                        views.value.find((view) => view.name === active.value)?.label ?? 'Browse images'),
                    h('p', { id: 'gallery-status', role: 'status' }, status.value),
                    h('div', { id: 'gallery-results' }, images.value.map((post) =>
                        h('article', { key: post.id }, [
                            h('h3', post.title),
                            h('img', {
                                src: post.image_url,
                                alt: post.title,
                                width: 240,
                                loading: 'lazy',
                                style: { filter: filters.get(post.filter) ?? 'none' },
                            }),
                            h('p', post.description),
                        ]),
                    )),
                ]),
            ];
        },
    }).mount(root);
})();