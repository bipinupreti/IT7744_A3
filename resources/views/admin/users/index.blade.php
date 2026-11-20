@extends('layouts.app')

@section('title')
    Manage Users
@endsection

@section('body-class', 'page-admin-list')

@section('content')
    <main>
        <h1>Manage Users</h1>
        <p><a href="{{ route('admin.dashboard') }}">Back to admin dashboard</a></p>
        <p><a href="{{ route('admin.users.create') }}">Create User</a></p>

        @if (session('status'))
            <p role="status">{{ session('status') }}</p>
        @endif

        <table aria-label="User accounts">
            <thead>
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Role</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->role }}</td>
                        <td>
                            <a href="{{ route('admin.users.edit', $user) }}">Edit</a>

                            @if ($user->role === 'user')
                                <form method="POST"
                                      action="{{ route('admin.users.destroy', $user) }}"
                                      onsubmit="return window.confirm('Delete this user account?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $users->links() }}
    </main>
@endsection
