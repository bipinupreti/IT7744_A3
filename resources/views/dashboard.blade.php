<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
</head>
<body>
    <main>
        <h1>Dashboard</h1>
        <p>Welcome, {{ auth()->user()->name }}.</p>

        <p><a href="{{ route('profile.edit') }}">Edit profile</a></p>

        @can('access-admin')
            <p><a href="{{ route('admin.dashboard') }}">Admin dashboard</a></p>
        @endcan

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Log out</button>
        </form>
    </main>
</body>
</html>