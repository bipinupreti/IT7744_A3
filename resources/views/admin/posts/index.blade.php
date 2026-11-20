@extends('layouts.app')

@section('title')
    Manage Posts
@endsection

@section('body-class', 'page-admin-list')

@section('content')
    <main>
        <h1>Manage Posts</h1>
        <p><a href="{{ route('admin.dashboard') }}">Back to admin dashboard</a></p>
        <p><a href="{{ route('admin.posts.create') }}">Create Post</a></p>

        @if (session('status'))
            <p role="status">{{ session('status') }}</p>
        @endif

        <table aria-label="Image posts">
            <thead>
                <tr>
                    <th scope="col">Image</th>
                    <th scope="col">Title</th>
                    <th scope="col">Owner ID</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($posts as $post)
                    <tr>
                        <td>
                            <img src="{{ asset('storage/' . $post->image_path) }}"
                                 alt="{{ $post->title }}" width="100" loading="lazy">
                        </td>
                        <td>{{ $post->title }}</td>
                        <td>{{ $post->user_id }}</td>
                        <td>
                            <a href="{{ route('admin.posts.edit', $post) }}">Edit</a>

                            <form method="POST"
                                  action="{{ route('admin.posts.destroy', $post) }}"
                                  onsubmit="return window.confirm('Delete this image post?')">
                                @csrf
                                @method('DELETE')

                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">No image posts yet.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $posts->links() }}
    </main>
@endsection
