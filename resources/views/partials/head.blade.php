@php
  $appName = config('app.name', 'We Offer Wellness®');
  $defaultDesc = 'Holistic therapy, done right: new classes daily, frequent workshops & events, plus restorative retreats — led by trusted practitioners at We Offer Wellness®.';
  $canonical = url()->current();
@endphp

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ $appName }}</title>
<link rel="canonical" href="{{ $canonical }}" />
<meta name="description" content="{{ $defaultDesc }}" />

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- WOW Typography base (served from public) -->
<link rel="stylesheet" href="/css/wow-typography.css">

<!-- App assets via Vite (hash-safe across builds) -->
@routes
@php $manifest = public_path('build/manifest.json'); @endphp
@if (file_exists($manifest))
  @vite([
    'resources/css/we-offer-wellness-base-styles.css',
    'resources/js/app.js',
  ])
@else
  <!-- Vite manifest missing; build assets with `npm run build` to enable full styling. -->
@endif

<!-- Page-specific head injections (titles, meta, etc.) -->
@stack('head')

