    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>We Offer Wellness®</title>
    <meta name="description"
          content="Holistic therapy, done right: new classes daily, frequent workshops &amp; events, plus restorative retreats—led by trusted practitioners at We Offer Wellness®.">

    <!-- Fonts: Instrument Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link
        href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,opsz,wght@0,14..32,300..900;1,14..32,300..900&amp;display=swap"
        rel="stylesheet">

    <!-- Built CSS bundle from Vite/Laravel -->
    @routes
    @php $manifest = public_path('build/manifest.json'); @endphp
    @if (file_exists($manifest))
        @vite('resources/css/we-offer-wellness-base-styles.css')
        @vite('resources/js/app.js')
    @else
        <link rel="stylesheet" href="{{ asset('css/we-offer-wellness-base-styles.css') }}">
    @endif

    <meta name="description"
          content="Holistic therapy, done right: new classes daily, frequent workshops &amp; events, plus restorative retreats—led by trusted practitioners at We Offer Wellness®."
          inertia="">
    <link rel="canonical" href="/" inertia="">
    <meta property="og:title" content="Holistic Therapy That Works | We Offer Wellness®" inertia="">
    <meta property="og:description"
          content="Holistic therapy, done right: new classes daily, frequent workshops &amp; events, plus restorative retreats—led by trusted practitioners at We Offer Wellness®."
          inertia="">
    <meta property="og:url" content="" inertia="">
    @isset($page)
        @inertiaHead
    @endisset
