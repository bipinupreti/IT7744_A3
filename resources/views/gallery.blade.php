@extends('layouts.app')

@section('title', 'Image Gallery')
@section('body-class', 'page-gallery')

@section('content')
    <header>
        <nav aria-label="Main navigation">
            @auth
                <a href="{{ route('dashboard') }}">Dashboard</a>
            @else
                <a href="{{ route('login') }}">Log in</a>
                <a href="{{ route('register') }}">Create an account</a>
            @endauth
        </nav>
    </header>

    <main id="image-gallery">
        <h1>Image Gallery</h1>

        <nav aria-label="Gallery views">
            <button type="button" data-url="{{ route('image-posts.latest') }}"
                    aria-pressed="true">Latest Images</button>

            @auth
                <button type="button" data-url="{{ route('image-posts.mine') }}"
                        aria-pressed="false">My Images</button>
                <button type="button" data-url="{{ route('image-posts.others') }}"
                        aria-pressed="false">Other Users’ Images</button>
            @endauth
        </nav>

        <section aria-labelledby="gallery-heading">
            <h2 id="gallery-heading">Latest Images</h2>
            <p id="gallery-status" role="status"></p>
            <div id="gallery-results"></div>
        </section>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('js/image_gallery.js') }}" defer></script>
@endpush