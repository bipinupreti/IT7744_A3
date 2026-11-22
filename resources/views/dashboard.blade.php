@extends('layouts.app')

@section('title', 'Dashboard')
@section('body-class', 'page-dashboard')

@section('content')
    <main>
        @php
            $profileUser = auth()->user();
            $displayName = trim($profileUser->name ?? '');
            $displayName = explode('@', $displayName, 2)[0] ?: 'Member';
        @endphp

        <header class="dashboard-header">
            <div class="dashboard-header__main">
                <h1>Dashboard</h1>

                <nav class="dashboard-header__actions" aria-label="Account actions">
                    <a href="{{ route('gallery') }}">Gallery</a>
                    <a href="{{ route('profile.edit') }}">Edit profile</a>

                    @can('access-admin')
                        <a href="{{ route('admin.dashboard') }}">Admin dashboard</a>
                    @endcan

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Log out</button>
                    </form>
                </nav>
            </div>

            <div class="dashboard-header__identity">
                <div class="dashboard-header__avatar" aria-hidden="true">
                    @if ($profileUser->profile_photo_path)
                        <img src="{{ asset('storage/' . $profileUser->profile_photo_path) }}"
                             alt="" width="96" height="96">
                    @else
                        <span>{{ mb_strtoupper(mb_substr($displayName, 0, 1)) }}</span>
                    @endif
                </div>

                <p><strong>Welcome, <span>{{ $displayName }}</span></strong></p>
            </div>
        </header>

        <section aria-labelledby="publish-heading">
            <h2 id="publish-heading">Publish an image</h2>

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

        <section aria-labelledby="my-images-heading">
            <h2 id="my-images-heading">My Images</h2>

            @if (session('image_status'))
                <p role="status">{{ session('image_status') }}</p>
            @endif

            @foreach ($errors->getBag('imagePostEdit')->all() as $message)
                <p role="alert">{{ $message }}</p>
            @endforeach

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

                        <details>
                            <summary>Edit</summary>

                            <form method="POST"
                                  action="{{ route('image-posts.update', $post) }}">
                                @csrf
                                @method('PATCH')

                                <p>
                                    <label for="edit-title-{{ $post->id }}">Title</label>
                                    <input id="edit-title-{{ $post->id }}" name="title"
                                           value="{{ $post->title }}" required maxlength="120">
                                </p>

                                <p>
                                    <label for="edit-description-{{ $post->id }}">Description</label>
                                    <textarea id="edit-description-{{ $post->id }}"
                                              name="description" required
                                              maxlength="1000">{{ $post->description }}</textarea>
                                </p>

                                <p>
                                    <label for="edit-filter-{{ $post->id }}">Filter</label>
                                    <select id="edit-filter-{{ $post->id }}" name="filter">
                                        <option value="none" @selected($post->filter === 'none')>
                                            None
                                        </option>
                                        <option value="grayscale" @selected($post->filter === 'grayscale')>
                                            Grayscale
                                        </option>
                                        <option value="sepia" @selected($post->filter === 'sepia')>
                                            Sepia
                                        </option>
                                    </select>
                                </p>

                                <button type="submit">Save changes</button>
                            </form>
                        </details>

                        <form method="POST"
                              action="{{ route('image-posts.destroy', $post) }}"
                              data-async-delete>
                            @csrf
                            @method('DELETE')

                            <button type="submit">Delete</button>
                        </form>
                    </article>
                @empty
                    <p id="no-images">You haven’t published any images yet.</p>
                @endforelse
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('js/image-publishing.js') }}" defer></script>
@endpush