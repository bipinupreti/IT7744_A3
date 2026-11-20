@extends('layouts.app')

@section('title', $post->exists ? 'Edit Post' : 'Create Post')
@section('body-class', 'page-form')

@section('content')
    <main>
        <h1>{{ $post->exists ? 'Edit Post' : 'Create Post' }}</h1>
        <p><a href="{{ route('admin.posts.index') }}">Back to Manage Posts</a></p>

        <form method="POST"
              action="{{ $post->exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}"
              enctype="multipart/form-data">
            @csrf

            @if ($post->exists)
                @method('PATCH')
            @endif

            <p>
                <label for="title">Title</label>
                <input id="title" name="title" value="{{ old('title', $post->title) }}"
                       required maxlength="120">
                @error('title')
                    <span role="alert">{{ $message }}</span>
                @enderror
            </p>

            <p>
                <label for="description">Description</label>
                <textarea id="description" name="description" required
                          maxlength="1000">{{ old('description', $post->description) }}</textarea>
                @error('description')
                    <span role="alert">{{ $message }}</span>
                @enderror
            </p>

            <p>
                <label for="filter">Filter</label>
                <select id="filter" name="filter" required>
                    @foreach (['none' => 'None', 'grayscale' => 'Grayscale', 'sepia' => 'Sepia'] as $value => $label)
                        <option value="{{ $value }}"
                                @selected(old('filter', $post->filter ?? 'none') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('filter')
                    <span role="alert">{{ $message }}</span>
                @enderror
            </p>

            @unless ($post->exists)
                <p>
                    <label for="image">Image (JPG, PNG or WebP, up to 2 MB)</label>
                    <input id="image" name="image" type="file"
                           accept="image/jpeg,image/png,image/webp" required>
                    @error('image')
                        <span role="alert">{{ $message }}</span>
                    @enderror
                </p>
            @endunless

            <button type="submit">{{ $post->exists ? 'Save changes' : 'Create Post' }}</button>
        </form>
    </main>
@endsection