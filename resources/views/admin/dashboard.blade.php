@extends('layouts.app')

@section('title', 'Admin dashboard')
@section('body-class', 'page-admin-home')

@section('content')
    <main>
        <h1>Admin dashboard</h1>

        <nav aria-label="Admin navigation">
            <ul>
                <li><a href="{{ route('admin.users.index') }}">Manage Users</a></li>
                <li><a href="{{ route('admin.posts.index') }}">Manage Posts</a></li>
                <li><a href="{{ route('dashboard') }}">Back to dashboard</a></li>
            </ul>
        </nav>
    </main>
@endsection