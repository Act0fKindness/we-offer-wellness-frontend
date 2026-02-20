    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'We Offer Wellness®')</title>
    <meta name="description" content="@yield('meta_description', 'Safe, trusted therapies and wellness services that help you feel better — today.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    @stack('styles')
    @stack('scripts_head')

    <!-- Fonts: Instrument Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link
        href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,opsz,wght@0,14..32,300..900;1,14..32,300..900&amp;display=swap"
        rel="stylesheet">

    <!-- Built CSS bundle from Vite/Laravel -->

    {{-- Home head CSS (migrated from inline) --}}
    @php $manifest = public_path('build/manifest.json'); @endphp
    @if (file_exists($manifest))
        @vite('resources/css/site.css')
    @else
        <link rel="stylesheet" href="{{ asset('css/site.css') }}">
    @endif
    {{-- Removed static modulepreload/style links and duplicate static meta/title from export --}}
