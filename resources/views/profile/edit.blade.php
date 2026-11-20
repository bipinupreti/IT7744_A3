@extends('layouts.app')

@section('title', 'Edit profile')
@section('body-class', 'page-form')

@section('content')
    <main>
        <header class="edit-profile-header">
            <div>
                <h1>Edit profile</h1>
                <a href="{{ route('dashboard') }}">Back to dashboard</a>
            </div>

            @if ($user->profile_photo_path)
                <img class="edit-profile-header__photo"
                     src="{{ asset('storage/' . $user->profile_photo_path) }}"
                     alt="Current profile picture"
                     width="96"
                     height="96">
            @endif
        </header>

        @if (session('success'))
            <p role="status">{{ session('success') }}</p>
        @endif

        <form method="POST" action="{{ route('profile.update') }}"
              enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <p>
                <label for="name">Name</label>
                <input id="name" name="name" value="{{ old('name', $user->name) }}"
                       autocomplete="name" required maxlength="255">
                @error('name')
                    <span role="alert">{{ $message }}</span>
                @enderror
            </p>

            <p>
                <label for="email">Email</label>
                <input id="email" name="email" type="email"
                       value="{{ old('email', $user->email) }}"
                       autocomplete="email" required maxlength="255">
                @error('email')
                    <span role="alert">{{ $message }}</span>
                @enderror
            </p>

            <p>
                <label for="location">Location</label>
                <input id="location" name="location"
                       value="{{ old('location', $user->location) }}" maxlength="100">
                @error('location')
                    <span role="alert">{{ $message }}</span>
                @enderror
            </p>

            <p>
                <label for="bio">Bio (up to 500 characters)</label>
                <textarea id="bio" name="bio" rows="4"
                          maxlength="500">{{ old('bio', $user->bio) }}</textarea>
                @error('bio')
                    <span role="alert">{{ $message }}</span>
                @enderror
            </p>

            <p>
                <label for="profile-photo">Profile picture</label>
                <input id="profile-photo" name="profile_photo" type="file"
                       accept="image/jpeg,image/png,image/webp"
                       aria-describedby="profile-photo-help">
                <small id="profile-photo-help">
                    JPG, PNG, or WebP, up to 2 MB. Leave blank to keep your current picture.
                </small>
                @error('profile_photo')
                    <span role="alert">{{ $message }}</span>
                @enderror
            </p>

            <button type="submit">Save changes</button>
        </form>
    </main>
@endsection