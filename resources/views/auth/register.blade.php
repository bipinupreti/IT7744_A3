@extends('layouts.app')

@section('title', 'Create an account')
@section('body-class', 'page-form')

@section('content')
    <main>
        <h1>Create an account</h1>

        @if (session('success'))
            <p role="status">{{ session('success') }}</p>
        @endif

        <form method="POST" action="{{ route('register.store') }}">
            @csrf

            <p>
                <label for="name">Name</label>
                <input id="name" name="name" value="{{ old('name') }}"
                       autocomplete="name" maxlength="255" required>
                @error('name')
                    <span role="alert">{{ $message }}</span>
                @enderror
            </p>

            <p>
                <label for="username">Username</label>
                <input id="username" name="username" value="{{ old('username') }}"
                       autocomplete="username" minlength="3" maxlength="32"
                       pattern="[a-z0-9_\-]+" aria-describedby="username-help" required>
                <small id="username-help">Use 3–32 lowercase letters, numbers, underscores or hyphens.</small>
                @error('username')
                    <span role="alert">{{ $message }}</span>
                @enderror
            </p>

            <p>
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}"
                       autocomplete="email" maxlength="255" required>
                @error('email')
                    <span role="alert">{{ $message }}</span>
                @enderror
            </p>

            <p>
                <label for="password">Password</label>
                <input id="password" name="password" type="password"
                       autocomplete="new-password" minlength="8" required>
                @error('password')
                    <span role="alert">{{ $message }}</span>
                @enderror
            </p>

            <p>
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation"
                       type="password" autocomplete="new-password" minlength="8" required>
            </p>

            <button type="submit">Register</button>
        </form>

        <p><a href="{{ route('login') }}">Back to log in</a></p>
    </main>
@endsection