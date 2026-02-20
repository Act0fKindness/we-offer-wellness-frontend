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
    <link rel="modulepreload" as="script" crossorigin="" href="/build/assets/Home-PUJfnV1T.js">
    <link rel="modulepreload" as="script" crossorigin="" href="/build/assets/SiteLayout-DKnyduE5.js">
    <link rel="modulepreload" as="script" crossorigin="" href="/build/assets/_plugin-vue_export-helper-1tPrXgE0.js">
    <link rel="stylesheet" crossorigin="" href="/build/assets/SiteLayout-dNJFhRS5.css">
    <link rel="modulepreload" as="script" crossorigin="" href="/build/assets/ProductCard-BSjt_v_N.js">
    <link rel="modulepreload" as="script" crossorigin="" href="/build/assets/WowButton-CFEh0Oz8.js">
    <link rel="stylesheet" crossorigin="" href="/build/assets/ProductCard-CWmZ0eZB.css">
    <link rel="modulepreload" as="script" crossorigin="" href="/build/assets/UltraSearchBar-BfOnvyJy.js">
    <link rel="modulepreload" as="script" crossorigin=""
          href="/build/assets/UltraSearchBar.vue_vue_type_style_index_0_lang-DRm_99TD.js">
    <link rel="stylesheet" crossorigin="" href="/build/assets/UltraSearchBar-NUgbpZRO.css">
    <link rel="modulepreload" as="script" crossorigin="" href="/build/assets/ClassSchedule-CsCHxwuF.js">
    <link rel="stylesheet" crossorigin="" href="/build/assets/ClassSchedule-CH6Jm1u5.css">
    <link rel="stylesheet" crossorigin="" href="/build/assets/Home-DuBmdSoH.css">
    <meta name="description"
          content="Holistic therapy, done right: new classes daily, frequent workshops &amp; events, plus restorative retreats—led by trusted practitioners at We Offer Wellness®."
          inertia="">
    <link rel="canonical" href="/" inertia="">
    <meta property="og:title" content="Holistic Therapy That Works | We Offer Wellness®" inertia="">
    <meta property="og:description"
          content="Holistic therapy, done right: new classes daily, frequent workshops &amp; events, plus restorative retreats—led by trusted practitioners at We Offer Wellness®."
          inertia="">
    <meta property="og:url" content="" inertia="">
    <title inertia="">Holistic Therapy That Works | We Offer Wellness®</title>
