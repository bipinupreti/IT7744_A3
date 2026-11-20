@extends('layouts.app')

@section('title')
    {{ $user->exists ? 'Edit User' : 'Create User' }}
@endsection

@section('body-class', 'page-form')

@section('content')
    <main>
        <h1>{{ $user->exists ? 'Edit User' : 'Create User' }}</h1>

        <p><a href="{{ route('admin.users.index') }}">Back to Manage Users</a></p>

        <form method="POST"
              action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
            @csrf

            @if ($user->exists)
                @method('PATCH')
            @endif

            <p>
                <label for="name">Name</label>
                <input id="name" name="name" value="{{ old('name', $user->name) }}"
                       required maxlength="255">
                @error('name')
                    <span role="alert">{{ $message }}</span>
                @enderror
            </p>

            <p>
                <label for="email">Email</label>
                <input id="email" name="email" type="email"
                       value="{{ old('email', $user->email) }}" required maxlength="255">
                @error('email')
                    <span role="alert">{{ $message }}</span>
                @enderror
            </p>

            @unless ($user->exists)
                <p>
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password"
                           autocomplete="new-password" required minlength="8">
                    @error('password')
                        <span role="alert">{{ $message }}</span>
                    @enderror
                </p>

                <p>
                    <label for="password-confirmation">Confirm password</label>
                    <input id="password-confirmation" name="password_confirmation"
                           type="password" autocomplete="new-password" required minlength="8">
                </p>
            @endunless

            <button type="submit">{{ $user->exists ? 'Save changes' : 'Create User' }}</button>
        </form>
    </main>
@endsection
