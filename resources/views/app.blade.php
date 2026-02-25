<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>
        @php
          $appName = config('app.name', 'We Offer Wellness');
          $defaultDesc = 'Book trusted therapies, classes, and wellness sessions that actually help: massage, reiki, breathwork, sound therapy and more — online or in‑studio.';
          $defaultOg = env('OG_IMAGE_URL', '//www.weofferwellness.co.uk/cdn/shop/files/wow-og-default.jpg');
          $canonical = url()->current();
          $gtmId = env('GTM_ID') ?: env('VITE_GTM_ID');
          $gaId = env('GA_ID') ?: env('VITE_GA_ID');
        @endphp
        <link rel="canonical" href="{{ $canonical }}" />
        <meta name="description" content="{{ $defaultDesc }}" />

        <!-- Open Graph defaults -->
        <meta property="og:type" content="website" />
        <meta property="og:title" content="{{ $appName }}" />
        <meta property="og:description" content="{{ $defaultDesc }}" />
        <meta property="og:url" content="{{ $canonical }}" />
        <meta property="og:image" content="{{ $defaultOg }}" />
        <meta property="og:site_name" content="{{ $appName }}" />

        <!-- Twitter Card defaults -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="{{ $appName }}" />
        <meta name="twitter:description" content="{{ $defaultDesc }}" />
        <meta name="twitter:image" content="{{ $defaultOg }}" />

        <!-- Google Tag Manager (optional via env) -->
        @if ($gtmId)
        <script>
          (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
          new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
          j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
          'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
          })(window,document,'script','dataLayer','{{ $gtmId }}');
        </script>
        @endif

        <!-- Google Analytics 4 (optional via env) -->
        @if ($gaId)
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments)}
          gtag('js', new Date());
          gtag('config', '{{ $gaId }}', { 'send_page_view': false });
        </script>
        @endif
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-MZMQNETBYH"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());
          gtag('config', 'G-MZMQNETBYH');
        </script>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="https://testing.studio.weofferwellness.co.uk/workspace-favicon.png">
        <link rel="shortcut icon" href="https://testing.studio.weofferwellness.co.uk/workspace-favicon.png">

        <!-- Fonts: Instrument Sans -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,opsz,wght@0,14..32,300..900;1,14..32,300..900&display=swap" rel="stylesheet">

        <!-- WOW V3 Typography Deck (source of truth) -->
        <link rel="stylesheet" href="/css/wow-typography.css">

        <!-- Scripts -->
        @routes
        {{-- Load the app bundle only; page chunk is dynamically imported by Inertia --}}
        @php $manifest = public_path('build/manifest.json'); @endphp
        @if (file_exists($manifest))
            @vite('resources/js/app.js')
        @else
            <!-- Vite manifest missing; temporarily skip assets to avoid 500. Build assets via `npm run build`. -->
        @endif
        <script>
          window.WOW_MAPS_KEY = @json(env('MAPBOX_API_KEY'));
          window.WOW_APP_NAME = @json($appName);
        </script>
        
        <!-- Organization JSON-LD -->
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
        @inertiaHead
    </head>
    <body class="antialiased">
        @if ($gtmId)
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        @endif
        @inertia

        <script>
          // Basic analytics bridge for SPA navigations and key commerce events
          (function(){
            window.dataLayer = window.dataLayer || [];
            function pushEvent(name, params){ try{ window.dataLayer.push({ event:name, ...params }); }catch(e){} }
            // Inertia page view
            document.addEventListener('inertia:success', function(ev){
              try {
                const url = location.pathname + location.search + location.hash;
                pushEvent('page_view', { page_location: url, page_title: document.title });
              } catch {}
            });
            // Cart events (custom)
            window.addEventListener('wow:add-to-cart', function(e){
              pushEvent('add_to_cart', { item_id: e?.detail?.id || null });
            });
          })();
        </script>
    </body>
</html>
