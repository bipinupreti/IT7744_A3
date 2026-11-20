<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Image Gallery')</title>
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
</head>
<body class="@yield('body-class')">
    @yield('content')
    @stack('scripts')
</body>
</html>