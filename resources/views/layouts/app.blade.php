<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'We Offer Wellness®')</title>
    <meta name="description" content="@yield('meta_description', 'Safe, trusted therapies and wellness services that help you feel better — today.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,opsz,wght@0,14..32,300..900;1,14..32,300..900&display=swap" rel="stylesheet">
    @stack('styles')
    @stack('scripts_head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="min-h-screen text-ink-800 antialiased">
<div class="min-h-screen text-ink-800">
    @include('partials.header')
    <main>
        @yield('content')
    </main>
    @include('partials.footer')
</div>
@stack('scripts')
</body>
</html>

