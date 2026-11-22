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

    <main id="image-gallery"
          data-logged-in="{{ auth()->check() ? 'true' : 'false' }}"
          data-latest-url="{{ route('image-posts.latest') }}"
          data-mine-url="{{ route('image-posts.mine') }}"
          data-others-url="{{ route('image-posts.others') }}">
        <h1>Image Gallery</h1>

        <nav aria-label="Gallery views">
            <button v-for="view in views"
                    :key="view.name"
                    type="button"
                    :aria-pressed="active === view.name ? 'true' : 'false'"
                    v-on:click="loadGallery(view)"
                    v-text="view.label"></button>
        </nav>

        <section aria-labelledby="gallery-heading">
            <h2 id="gallery-heading" v-text="heading">Latest Images</h2>
            <p id="gallery-status" role="status" v-text="statusMessage"></p>

            <div id="gallery-results">
                <article v-for="post in images" :key="post.id">
                    <h3 v-text="post.title"></h3>
                    <img :src="post.image_url"
                         :alt="post.title"
                         :style="{ filter: filterStyle(post.filter) }"
                         width="240"
                         loading="lazy">
                    <p v-text="post.description"></p>
                </article>
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('js/vue.global.prod.js') }}" defer></script>
    <script src="{{ asset('js/image-gallery.js') }}" defer></script>
@endpush