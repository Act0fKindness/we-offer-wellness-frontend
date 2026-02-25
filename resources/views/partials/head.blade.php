@php
  $appName = config('app.name', 'We Offer Wellness');
  $seo = $seo ?? [];
  $title = trim($__env->yieldContent('title', $seo['title'] ?? $appName));
  $desc = trim($__env->yieldContent('meta_description', $seo['description'] ?? 'Book trusted therapies, classes, workshops and retreats — online or near you.'));
  $robots = trim($__env->yieldContent('meta_robots', $seo['robots'] ?? 'index,follow'));
  $canonical = $seo['canonical'] ?? url()->current();
  $ogImage = $seo['image'] ?? env('OG_IMAGE_URL', '//www.weofferwellness.co.uk/cdn/shop/files/wow-og-default.jpg');
  $gtmId = env('GTM_ID') ?: env('VITE_GTM_ID');
  $gaId = env('GA_ID') ?: env('VITE_GA_ID');
@endphp

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ $title ?: $appName }}</title>
<meta name="description" content="{{ $desc }}">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonical }}">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $appName }}">
<meta property="og:title" content="{{ $title ?: $appName }}">
<meta property="og:description" content="{{ $desc }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $ogImage }}">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title ?: $appName }}">
<meta name="twitter:description" content="{{ $desc }}">
<meta name="twitter:image" content="{{ $ogImage }}">

<!-- Fonts: Manrope (general), Playfair Display (section headings), Instrument Sans (UI) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- Font Awesome (for icon replacements in formatted content) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" referrerpolicy="no-referrer" />

<!-- WOW V3 Typography Deck (source of truth) -->
<link rel="stylesheet" href="/css/wow-typography.css">

<!-- Safety: Bootstrap CSS CDN fallback to keep homepage/cards styled even if Vite CSS fails -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

@stack('head')

<!-- Google Tag Manager (optional via env) -->
@if ($gtmId)
<script>
  (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
  new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
  j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
  'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
  })(window,document,'script','dataLayer','{{ $gtmId }}');
  window.WOW_GTM_ID = '{{ $gtmId }}';
</script>
@endif

<!-- Google Analytics 4 (optional via env) -->
@if ($gaId)
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments)}
  gtag('js', new Date());
  gtag('config', '{{ $gaId }}');
  window.WOW_GA_ID = '{{ $gaId }}';
</script>
@endif

<!-- Vite bundle (CSS is imported via JS); guard if manifest missing -->
@php $manifest = public_path('build/manifest.json'); @endphp
@if (file_exists($manifest))
  @vite('resources/js/app.js')
@endif

<!-- JSON-LD: Organization -->
@php
  $orgLd = [
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => $appName,
    'url' => url('/'),
    'logo' => '//www.weofferwellness.co.uk/cdn/shop/files/logo-google-icon_05080e3a-98e5-42cd-b479-3b443028308c.png',
    'sameAs' => [
      'https://www.instagram.com/weofferwellness',
      'https://www.tiktok.com/@weofferwellness',
      'https://www.linkedin.com/company/weofferwellness',
      'https://www.facebook.com/WeOfferWellness',
    ],
  ];
@endphp
<script type="application/ld+json">{!! json_encode($orgLd, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
