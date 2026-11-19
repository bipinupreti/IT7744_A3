<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
</head>
<body>
    <main>
        <h1>Dashboard</h1>
        <p>Welcome, {{ auth()->user()->name }}.</p>

        <p><a href="{{ route('profile.edit') }}">Edit profile</a></p>

        @can('access-admin')
            <p><a href="{{ route('admin.dashboard') }}">Admin dashboard</a></p>
        @endcan

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Log out</button>
        </form>

        <section>
            <h2>Publish an image</h2>

            <form id="publish-image"
                  method="POST"
                  action="{{ route('image-posts.store') }}"
                  enctype="multipart/form-data"
                  data-upload-token="{{ $uploadToken }}">
                @csrf

                <p>
                    <label for="image-title">Title</label>
                    <input id="image-title" name="title" required maxlength="120">
                </p>

                <p>
                    <label for="image-description">Description</label>
                    <textarea id="image-description" name="description"
                              required maxlength="1000"></textarea>
                </p>

                <p>
                    <label for="image-filter">Filter</label>
                    <select id="image-filter" name="filter">
                        <option value="none">None</option>
                        <option value="grayscale">Grayscale</option>
                        <option value="sepia">Sepia</option>
                    </select>
                </p>

                <p>
                    <label for="image-file">Image (JPG, PNG or WebP, up to 2 MB)</label>
                    <input id="image-file" type="file" name="image"
                           accept="image/jpeg,image/png,image/webp" required>
                </p>

                <button type="submit">Publish</button>
            </form>

            <p id="publish-status" role="status"></p>
        </section>

        <section>
            <h2>My Images</h2>

            <div id="my-images">
                @forelse ($posts as $post)
                    <article>
                        <h3>{{ $post->title }}</h3>
                        <img src="{{ asset('storage/' . $post->image_path) }}"
                             alt="{{ $post->title }}" width="240"
                             style="filter: {{ match ($post->filter) {
                                 'grayscale' => 'grayscale(1)',
                                 'sepia' => 'sepia(1)',
                                 default => 'none',
                             } }}">
                        <p>{{ $post->description }}</p>
                    </article>
                @empty
                    <p id="no-images">You haven’t published any images yet.</p>
                @endforelse
            </div>
        </section>
    </main>

    <script src="{{ asset('js/image-publishing.js') }}" defer></script>
</body>
</html>