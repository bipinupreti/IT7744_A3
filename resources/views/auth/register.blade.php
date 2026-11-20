@extends('layouts.app')

@section('title')
    Create an account
@endsection

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
                       autocomplete="name" required maxlength="255">
                @error('name')
                    <span role="alert">{{ $message }}</span>
                @enderror
            </p>

            <p>
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}"
                       autocomplete="email" required maxlength="255">
                @error('email')
                    <span role="alert">{{ $message }}</span>
                @enderror
            </p>

            <p>
                <label for="password">Password</label>
                <input id="password" name="password" type="password"
                       autocomplete="new-password" required minlength="8">
                @error('password')
                    <span role="alert">{{ $message }}</span>
                @enderror
            </p>

            <p>
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation"
                       type="password" autocomplete="new-password" required minlength="8">
            </p>

            <button type="submit">Register</button>
        </form>
    </main>
@endsection
