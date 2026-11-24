@extends('layouts.app')

@section('title', 'Log in')
@section('body-class', 'page-form')

@section('content')
    <main>
        <h1>Welcome to Image Gallery</h1>
        <p>Log in to your account.</p>

        @if (session('success'))
            <p role="status">{{ session('success') }}</p>
        @endif

        <form method="POST" action="{{ route('login.store') }}">
            @csrf

            <p>
                <label for="username">Username</label>
                <input id="username" name="username" value="{{ old('username') }}"
                       autocomplete="username" maxlength="32" required>
                @error('username')
                    <span role="alert">{{ $message }}</span>
                @enderror
            </p>

            <p>
                <label for="password">Password</label>
                <input id="password" name="password" type="password"
                       autocomplete="current-password" required>
                @error('password')
                    <span role="alert">{{ $message }}</span>
                @enderror
            </p>

            <button type="submit">Log in</button>
        </form>

        <p>
            Don’t have an account yet?
            <a href="{{ route('register') }}">Create an account</a>
        </p>
    </main>
@endsection