<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

@php
  $defaultTitle = 'Holistic Therapy That Works | We Offer Wellness®';
  $defaultDesc  = 'Holistic therapy, done right: new classes daily, frequent workshops & events, plus restorative retreats—led by trusted practitioners at We Offer Wellness®.';
  $title = $seo['title'] ?? ($pageTitle ?? $defaultTitle);
  $desc  = $seo['description'] ?? ($metaDescription ?? $defaultDesc);
  $canonicalUrl = $seo['canonical'] ?? ($canonical ?? url()->current());
  $ogImage = $seo['og_image'] ?? (config('app.og_image') ?: env('OG_IMAGE_URL'));
@endphp

<title>{{ $title }}</title>
<meta name="description" content="{{ $desc }}">
<link rel="canonical" href="{{ $canonicalUrl }}">

<!-- Favicons -->
<link rel="icon" type="image/png" href="https://testing.studio.weofferwellness.co.uk/workspace-favicon.png?v=2">
<link rel="shortcut icon" href="https://testing.studio.weofferwellness.co.uk/workspace-favicon.png?v=2">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $desc }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
@if(!empty($ogImage))
<meta property="og:image" content="{{ $ogImage }}">
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">

<!-- Vite assets (CSS and JS) -->
@routes
@vite('resources/js/app.js')

<!-- Inline <style> blocks removed; all styles are bundled via Vite. -->
