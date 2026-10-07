<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Woven')</title>
    <link rel="stylesheet" href="{{ asset('css/woven.css') }}">
</head>
<body class="auth-body">
    <div class="auth-shell">
        <div class="auth-brand">
            <div class="brand">Woven <span>Education</span></div>
            <p>Learning & support platform</p>
        </div>
        <div class="auth-card">
            @yield('content')
        </div>
    </div>
</body>
</html>
