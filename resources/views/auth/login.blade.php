@extends('layouts.app')

@section('title')
    Log in
@endsection

@section('body-class', 'page-form')

@section('content')
    <main>
        <h1>Log in</h1>

        @if (session('success'))
            <p role="status">{{ session('success') }}</p>
        @endif

        <form method="POST" action="{{ route('login.store') }}">
            @csrf

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
                       autocomplete="current-password" required>
                @error('password')
                    <span role="alert">{{ $message }}</span>
                @enderror
            </p>

            <button type="submit">Log in</button>
        </form>

        <p><a href="{{ route('register') }}">Create an account</a></p>
    </main>
@endsection
