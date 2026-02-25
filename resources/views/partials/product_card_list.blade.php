@php
    // Derive URL segment from product_type or tags
    $t = strtolower((string) ($product->product_type ?? ''));
    $tags = strtolower((string) ($product->tags_list ?? ''));
    $seg = 'therapies';
    if (str_contains($t, 'workshop')) $seg = 'workshops';
    elseif (str_contains($t, 'event')) $seg = 'events';
    elseif (str_contains($t, 'class')) $seg = 'classes';
    elseif (str_contains($t, 'retreat')) $seg = 'retreats';
    elseif (str_contains($t, 'gift') || str_contains($tags, 'gift')) $seg = 'gifts';
    $slug = \Illuminate\Support\Str::slug($product->title ?: (string) $product->id);
    $url = url('/'.$seg.'/'.$product->id.'-'.$slug);

    $image = $product->getFirstImageUrl();
    $title = $product->title ?? 'Untitled';
    // Title case: first letter of each word uppercase, rest lowercase
    $toLower = function($s){ return function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s); };
    $ucWords = function($s){ return function_exists('mb_convert_case') ? mb_convert_case($s, MB_CASE_TITLE, 'UTF-8') : ucwords($s); };
    $titleFormatted = $ucWords($toLower($title));
    $type = $product->product_type ?: 'Experience';
    $category = $product->category?->name;
    $priceMin = $product->variants_min_price ?? ($product->price ?? null);
    // Normalise pennies to pounds where needed
    if (is_numeric($priceMin) && $priceMin > 1000 && $priceMin % 100 === 0) { $priceMin = $priceMin / 100; }
    $rating = isset($product->reviews_avg_rating) ? round((float)$product->reviews_avg_rating, 1) : null;
    $reviewCount = (int) ($product->reviews_count ?? 0);

    // Helper: shorten physical address to "Place, City" (or just City)
    $shortLocation2 = function($address){
        try {
            $raw = trim((string)$address);
            if ($raw === '') return $raw;
            $countryRx  = '/\b(United Kingdom|UK|England|Scotland|Wales|Northern Ireland)\b/i';
            $postcodeRx = '/\b[A-Z]{1,2}\d{1,2}[A-Z]?\s*\d[A-Z]{2}\b/i';
            $streetRx   = '/\b(road|rd|street|st|avenue|ave|lane|ln|drive|dr|way|close|cl|place|pl|boulevard|blvd|court|ct|crescent|cresc|terrace|terr|grove|grv)\b/i';

            $tokens = array_map('trim', explode(',', $raw));
            $tokens = array_values(array_filter($tokens, function($t) use($countryRx){ return $t !== '' && !preg_match($countryRx, $t); }));
            $nonStreet = array_values(array_filter($tokens, function($t) use($streetRx,$postcodeRx){
                if (preg_match($postcodeRx, $t)) return false;
                return !preg_match($streetRx, $t);
            }));
            if (count($nonStreet) >= 2) {
                $place = $nonStreet[0];
                $city  = $nonStreet[count($nonStreet)-1];
                return strcasecmp($place,$city) === 0 ? $city : ($place . ', ' . $city);
            }
            if (count($nonStreet) === 1) return $nonStreet[0];
            foreach ($tokens as $t) { if ($t !== '') return $t; }
            return $raw;
        } catch (\Throwable $e) { return (string)$address; }
    };

    $locations = $product->getLocations();
    $hasOnline = in_array('Online', $locations, true);
    $physical = array_values(array_filter($locations, fn($l) => $l !== 'Online'));
    $physicalShort = [];
    $seenShort = [];
    foreach ($physical as $locRaw) {
        $short = $shortLocation2($locRaw);
        $key = mb_strtolower($short ?? '');
        if ($short && !isset($seenShort[$key])) { $seenShort[$key] = true; $physicalShort[] = $short; }
    }
    $preferredLocation = $preferredLocation ?? ($currentLocation ?? (request()->get('location') ?? null));
    $primary = null;
    if ($preferredLocation) {
        $prefKey = mb_strtolower($shortLocation2($preferredLocation));
        foreach ($physicalShort as $ps) {
            if (mb_strtolower($ps) === $prefKey) { $primary = $ps; break; }
        }
    }
    if ($primary === null) { $primary = $physicalShort[0] ?? null; }
    $remainingCount = max(0, count($physicalShort) - ($primary ? 1 : 0));
    
    // Additional fields
    $provider = $product->vendor_name
        ?? (is_object($product->vendor ?? null) ? ($product->vendor->vendor_name ?? null) : null)
        ?? $product->practitioner_name
        ?? $product->provider
        ?? null;
    $providerFormatted = $provider ? $ucWords($toLower($provider)) : null;
    $durationLabel = $product->duration ?? null;
    $nextLabel = $product->next_label ?? $product->next ?? null;
    $fomoText = $product->fomo_text ?? null;
    $compareMin = $product->variants_min_compare ?? ($product->compare_at_price ?? null);
    if (is_numeric($compareMin) && $compareMin > 1000 && $compareMin % 100 === 0) { $compareMin = $compareMin / 100; }
@endphp

@once
  @push('head')
    <style>
      .wow-therapy-card-scope{ --bg:#f4f5f7; --card:#fff; --shadow:0 18px 55px rgba(16,24,40,.10); --text:#0b1220; --muted:rgba(11,18,32,.62); --wowGreen:#1f7a4a; --wowGreenDark:#17643d; --badgeWarmBg:#ffe7c2; --badgeWarmText:#6b4b12; --badgeCoolBg:#dfe9ff; --badgeCoolText:#1f3a77; --radius:7px; --borderW:1px; --imgW:260px; --imgH:170px; --pad:15px; --badgeH:32px; --badgePx:10px; --badgeFont:12px; --save:34px; --saveR:3px; --saveIcon:18px; --title:22px; --provider:13px; --rating:13px; --meta:12px; --metaIcon:14px; --btnH:38px; --btnR:4px; --btnFont:16px; --from:13px; --priceNow:22px; --was:13px; }
      .wow-therapy-card-scope .therapy-card.therapy-card--list{ background:var(--card); border:var(--borderW) solid rgba(16,24,40,.18); border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden; position:relative; display:flex; align-items:stretch; transition: transform .14s ease, box-shadow .14s ease, border-color .14s ease; }
      .wow-therapy-card-scope .therapy-card.therapy-card--list:hover{ transform: translateY(-1px); box-shadow:0 22px 70px rgba(16,24,40,.12); border-color: rgba(16,24,40,.22); }
      .wow-therapy-card-scope .therapy-card.therapy-card--list[data-url]{ cursor:pointer }
      .wow-therapy-card-scope .list-media{ width: var(--imgW); flex: 0 0 var(--imgW); padding: var(--pad); padding-right:0 }
      .wow-therapy-card-scope .list-media .media{ height: var(--imgH); border-radius:3px; overflow:hidden; border:1px solid #eee; background:#f7f7f7 }
      .wow-therapy-card-scope .list-media img{ width:100%; height:100%; object-fit:cover; display:block }
      .wow-therapy-card-scope .list-main{ flex:1 1 auto; min-width:0; padding: var(--pad); display:flex; flex-direction:column; gap:10px }
      .wow-therapy-card-scope .card-top{ display:flex; align-items:center; justify-content:space-between; gap:10px }
      .wow-therapy-card-scope .badges{ flex:1 1 auto; min-width:0; display:flex; gap:8px; white-space:nowrap; overflow:hidden }
      .wow-therapy-card-scope .badge{ height: var(--badgeH); display:inline-flex; align-items:center; gap:4px; padding:0 var(--badgePx); border-radius:3px; border:1px solid rgba(16,24,40,.10); font-weight:600; font-size: var(--badgeFont); line-height:1; flex:0 0 auto }
      .wow-therapy-card-scope .badge--warm{ background: var(--badgeWarmBg); color: var(--badgeWarmText) }
      .wow-therapy-card-scope .badge--cool{ background: var(--badgeCoolBg); color: var(--badgeCoolText) }
      .wow-therapy-card-scope .badge svg{ color:#d59d4c; width:18px; height:18px; margin-bottom:4px; transform: translateY(1px) }
      .wow-therapy-card-scope .save{ width: var(--save); height: var(--save); border-radius: var(--saveR); border:1px solid rgba(16,24,40,.20); background:#fff; display:grid; place-items:center; box-shadow:0 10px 22px rgba(16,24,40,.08); transition: transform .12s ease, box-shadow .12s ease, background .12s ease, border-color .12s ease; flex:0 0 auto }
      .wow-therapy-card-scope .save:hover{ transform: translateY(-1px); box-shadow:0 14px 28px rgba(16,24,40,.12) }
      .wow-therapy-card-scope .save:active{ transform: translateY(0) scale(.99) }
      .wow-therapy-card-scope .save svg{ width: var(--saveIcon); height: var(--saveIcon); color: rgba(11,18,32,.72) }
      .wow-therapy-card-scope .title-link{ text-decoration:none; color:inherit; display:inline-block }
      .wow-therapy-card-scope .title{ margin:0 0 6px; font-size: var(--title); font-weight:400 !important; line-height:1.12; font-family:'Manrope', var(--bs-font-sans-serif) !important; text-transform:capitalize; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden }
      .wow-therapy-card-scope .provider{ margin:0 0 8px; color: var(--muted); font-size: var(--provider); font-weight:400 }
      .wow-therapy-card-scope .rating-row{ display:flex; align-items:center; gap:8px; margin-bottom:10px; color: rgba(11,18,32,.80); font-weight:400; font-size: var(--rating) }
      .wow-therapy-card-scope .stars{ display:inline-flex; align-items:center; gap:3px; transform: translateY(1px) }
      .wow-therapy-card-scope .meta{ display:flex; align-items:center; flex-wrap:wrap; gap:8px 10px; margin-bottom:4px; color: rgba(11,18,32,.62); font-size: var(--meta); font-weight:400 }
      .wow-therapy-card-scope .meta .item{ display:flex; align-items:center; gap:6px; white-space:nowrap }
      .wow-therapy-card-scope .meta .label{ display:inline-block; max-width:180px; overflow:hidden; text-overflow:ellipsis; letter-spacing:normal; font-size:12px; font-weight:normal; text-transform:capitalize }
      .wow-therapy-card-scope .meta .chip{ display:inline-flex; align-items:center; gap:4px; font-size:10px; padding:3px 5px; cursor:pointer; color: rgba(11,18,32,.72); border-radius:999px; border:1px solid rgba(16,24,40,.10); background:#fff }
      .wow-therapy-card-scope .meta .chip:hover{ color: rgba(11,18,32,.92); border-color: rgba(16,24,40,.18) }
      .wow-therapy-card-scope .meta .chip svg{ width: var(--metaIcon); height: var(--metaIcon) }
      .wow-therapy-card-scope .loc-overflow{ position:relative }
      .wow-therapy-card-scope .loc-overflow::before{ content:""; position:absolute; left:-8px; right:-8px; top:100%; height:10px }
      .wow-therapy-card-scope .loc-popover{ position:absolute; top: calc(100% + 6px); left:0; width:240px; max-width:80vw; background:#fff; border:1px solid rgba(16,24,40,.12); box-shadow:0 10px 30px rgba(16,24,40,.12); border-radius:6px; padding:10px; z-index:60; display:none }
      .wow-therapy-card-scope .loc-popover h4{ margin:0 0 8px; font-size:12px; font-weight:700; color:#0b1220 }
      .wow-therapy-card-scope .loc-popover .list{ display:flex; flex-wrap:wrap; gap:6px }
      .wow-therapy-card-scope .loc-popover .pill{ border:1px solid rgba(16,24,40,.14); border-radius:999px; padding:4px 8px; font-size:12px; color:#0b1220; display:inline-flex; align-items:center; gap:4px }
      .wow-therapy-card-scope .loc-popover .link{ display:block; margin-top:8px; font-size:12px; color:#1f3a77; text-decoration:underline }
      .wow-therapy-card-scope .loc-overflow.is-open .loc-popover{ display:block }
      .wow-therapy-card-scope .list-aside{ width:300px; flex:0 0 300px; border-left:1px solid rgba(16,24,40,.10); background:#fff; padding: var(--pad); display:flex; flex-direction:column; justify-content:space-between; gap:12px }
      .wow-therapy-card-scope .fomo{ margin:0; font-size:12px; font-weight:600; color: rgba(11,18,32,.84); display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden }
      .wow-therapy-card-scope .price{ display:flex; align-items:baseline; gap:8px; margin:0 }
      .wow-therapy-card-scope .price .from{ font-size: var(--from); font-weight:400; color: rgba(11,18,32,.70) }
      .wow-therapy-card-scope .price .now{ font-size: var(--priceNow); font-weight:600; letter-spacing:-.02em; color: rgba(11,18,32,.92) }
      .wow-therapy-card-scope .price .was{ font-size: var(--was); font-weight:400; color: rgba(11,18,32,.75) }
      .wow-therapy-card-scope .actions{ display:grid; grid-template-columns:1fr; gap:10px }
      /* Hide original middle column info when using list layout variant */
      .wow-therapy-card-scope .list-main .content-top{ display:none }
      .wow-therapy-card-scope .btn{ height: var(--btnH); border-radius: var(--btnR); font-size: var(--btnFont); font-weight:400; border:1px solid rgba(16,24,40,.22); background:#fff !important; color: rgba(11,18,32,.82); display:flex; align-items:center; justify-content:center; box-shadow:0 10px 22px rgba(16,24,40,.08) }
      .wow-therapy-card-scope .btn--primary{ border-color: rgba(0,0,0,.10); color:#fff; background:#549483 !important }
      .wow-therapy-card-scope .btn:hover,.wow-therapy-card-scope .btn:focus{ background:#f7f7f7 !important; color: rgba(11,18,32,.90); border-color: rgba(0,0,0,.18) }
      @media (max-width: 991.98px){ .wow-therapy-card-scope{ --imgW: 220px; --imgH: 160px } .wow-therapy-card-scope .list-aside{ width:260px; flex-basis:260px } }
      @media (max-width: 767.98px){ .wow-therapy-card-scope .therapy-card.therapy-card--list{ flex-direction:column } .wow-therapy-card-scope .list-media{ width:100%; flex-basis:auto; padding-right: var(--pad) } .wow-therapy-card-scope .list-media .media{ height:190px } .wow-therapy-card-scope .list-aside{ width:100%; flex-basis:auto; border-left:0; border-top:1px solid rgba(16,24,40,.10) } .wow-therapy-card-scope .actions{ grid-template-columns: 1fr 1.15fr } }
    </style>
    <script>
      document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('.wow-therapy-card-scope .therapy-card--list[data-url]')?.forEach(function(card){
          card.addEventListener('click', function(e){
            var t = e.target; if (t.closest('button, a, input, select, textarea, [role="button"], .loc-popover')) return;
            var u = card.getAttribute('data-url'); if (u) window.location.href = u;
          })
        })
        document.querySelectorAll('.wow-therapy-card-scope .save')?.forEach(function(btn){
          btn.addEventListener('click', function(e){ e.preventDefault(); e.stopPropagation(); var p = btn.getAttribute('aria-pressed')==='true'; btn.setAttribute('aria-pressed', p?'false':'true') })
        })
        document.querySelectorAll('.wow-therapy-card-scope .loc-overflow .chip')?.forEach(function(chip){
          chip.addEventListener('click', function(e){ e.preventDefault(); e.stopPropagation(); var wrap = chip.closest('.loc-overflow'); if(!wrap) return; wrap.classList.toggle('is-open'); chip.setAttribute('aria-expanded', wrap.classList.contains('is-open')?'true':'false') })
        })
        document.addEventListener('click', function(){ document.querySelectorAll('.wow-therapy-card-scope .loc-overflow.is-open')?.forEach(function(w){ w.classList.remove('is-open'); var c=w.querySelector('.chip'); if(c) c.setAttribute('aria-expanded','false') }) })
      })
    </script>
  @endpush
@endonce

@php
  // Rating -> stars
  $r = is_numeric($rating ?? null) ? max(0, min(5, (float)$rating)) : null;
  $fullStars = $r !== null ? (int) floor($r) : 0;
@endphp

<div class="wow-therapy-card-scope">
  <article class="therapy-card therapy-card--list" data-url="{{ $url }}" aria-label="Listing {{ $product->id }}">
    <div class="list-media">
      <a href="{{ $url }}" class="d-block text-decoration-none" aria-label="View {{ e($titleFormatted) }}">
        <h2 class="title mb-2">{{ $titleFormatted }}</h2>
        <div class="media">
          @if($image)
            <img src="{{ $image }}" alt="{{ e($titleFormatted) }}" loading="lazy" />
          @endif
        </div>
      </a>
    </div>
    <div class="list-main">
      <header class="card-top">
        <div class="badges" aria-label="Badges">
          <span class="badge badge--warm">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.122 17.645a7.185 7.185 0 0 1-2.656 2.495 7.06 7.06 0 0 1-3.52.853 6.617 6.617 0 0 1-3.306-.718 6.73 6.73 0 0 1-2.54-2.266c-2.672-4.57.287-8.846.887-9.668A4.448 4.448 0 0 0 8.07 6.31 4.49 4.49 0 0 0 7.997 4c1.284.965 6.43 3.258 5.525 10.631 1.496-1.136 2.7-3.046 2.846-6.216 1.43 1.061 3.985 5.462 1.754 9.23Z"/></svg>
            Filling Fast
          </span>
          <span class="badge badge--cool">Top Rated</span>
        </div>
        <button class="save" type="button" aria-label="Save" aria-pressed="false" title="Save">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.01 6.001C6.5 1 1 8 5.782 13.001L12.011 20l6.23-7C23 8 17.5 1 12.01 6.002Z"/></svg>
        </button>
      </header>
      <div class="content-top">
        <a href="{{ $url }}" class="title-link"><h2 class="title mb-0">{{ $titleFormatted }}</h2></a>
        @if($providerFormatted)
        <p class="provider">with {{ $providerFormatted }}</p>
        @endif
        @if($r !== null && $reviewCount > 0)
        <div class="rating-row" aria-label="Rating {{ $r }} out of 5 from {{ $reviewCount }} reviews">
          <span class="stars" aria-hidden="true">
            @for($i=1;$i<=5;$i++)
              @php $filled = $i <= $fullStars; @endphp
              <span class="star {{ $filled ? '' : 'star--empty' }}" style="{{ $filled ? 'color:#f5c84b;' : '' }}"></span>
            @endfor
          </span>
          <span>{{ number_format($r, 1) }} <span class="text-muted">({{ $reviewCount }})</span></span>
        </div>
        @endif
        <div class="meta">
          @if($durationLabel)
            <span class="item">{{ $durationLabel }}</span>
          @endif
          @if($hasOnline && $primary === null)
            <span class="item"><svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M4.37 7.657c2.063.528 2.396 2.806 3.202 3.87 1.07 1.413 2.075 1.228 3.192 2.644 1.805 2.289 1.312 5.705 1.312 6.705M20 15h-1a4 4 0 0 0-4 4v1M8.587 3.992c0 .822.112 1.886 1.515 2.58 1.402.693 2.918.351 2.918 2.334 0 .276 0 2.008 1.972 2.008 2.026.031 2.026-1.678 2.026-2.008 0-.65.527-.9 1.177-.9H20M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg><span class="label">Online</span></span>
          @elseif(!$hasOnline && $primary && $remainingCount === 0)
            <span class="item"><svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.8 13.938h-.011a7 7 0 1 0-11.464.144h-.016l.14.171c.1.127.2.251.3.371L12 21l5.13-6.248c.194-.209.374-.429.54-.659l.13-.155Z"/></svg><span class="label">{{ $primary }}</span></span>
          @else
            @if($hasOnline)
              <span class="item"><svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M4.37 7.657c2.063.528 2.396 2.806 3.202 3.87 1.07 1.413 2.075 1.228 3.192 2.644 1.805 2.289 1.312 5.705 1.312 6.705M20 15h-1a4 4 0 0 0-4 4v1M8.587 3.992c0 .822.112 1.886 1.515 2.58 1.402.693 2.918.351 2.918 2.334 0 .276 0 2.008 1.972 2.008 2.026.031 2.026-1.678 2.026-2.008 0-.65.527-.9 1.177-.9H20M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg><span class="label">Online</span></span>
            @endif
            @if($primary)
              <span class="item"><svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.8 13.938h-.011a7 7 0 1 0-11.464.144h-.016l.14.171c.1.127.2.251.3.371L12 21l5.13-6.248c.194-.209.374-.429.54-.659l.13-.155Z"/></svg><span class="label">{{ $primary }}</span></span>
            @endif
            @if($remainingCount > 0)
              <span class="item loc-overflow">
                <span class="chip" role="button" tabindex="0" data-href="{{ $url }}#locations" aria-haspopup="dialog" aria-expanded="false">+{{ $remainingCount }}</span>
                <div class="loc-popover" role="dialog" aria-label="Available locations">
                  <h4>Available locations</h4>
                  <div class="list">
                    @if($hasOnline)
                      <span class="pill"><svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M4.37 7.657c2.063.528 2.396 2.806 3.202 3.87 1.07 1.413 2.075 1.228 3.192 2.644 1.805 2.289 1.312 5.705 1.312 6.705M20 15h-1a4 4 0 0 0-4 4v1M8.587 3.992c0 .822.112 1.886 1.515 2.58 1.402.693 2.918.351 2.918 2.334 0 .276 0 2.008 1.972 2.008 2.026.031 2.026-1.678 2.026-2.008 0-.65.527-.9 1.177-.9H20M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        Online</span>
                    @endif
                    @php $shown = 0; @endphp
                    @foreach($physicalShort as $ps)
                      @continue(mb_strtolower($ps) === mb_strtolower($primary))
                      @php if ($shown++ >= 6) break; @endphp
                      <span class="pill"><svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.8 13.938h-.011a7 7 0 1 0-11.464.144h-.016l.14.171c.1.127.2.251.3.371L12 21l5.13-6.248c.194-.209.374-.429.54-.659l.13-.155Z"/></svg>
                        {{ $ps }}</span>
                    @endforeach
                  </div>
                  <a class="link" href="{{ $url }}#locations">View all locations</a>
                </div>
              </span>
            @endif
          @endif
          @if($nextLabel)
            <span class="item">Next: {{ $nextLabel }}</span>
          @endif
        </div>
      </div>
    </div>
    <aside class="list-aside">
      <div>
        @if($providerFormatted)
          <p class="provider">with {{ $providerFormatted }}</p>
        @endif
        @if($r !== null && $reviewCount > 0)
          <div class="rating-row" aria-label="Rating {{ $r }} out of 5 from {{ $reviewCount }} reviews">
            <span class="stars" aria-hidden="true">
              @for($i=1;$i<=5;$i++)
                @php $filled = $i <= $fullStars; @endphp
                <span class="star {{ $filled ? '' : 'star--empty' }}" style="{{ $filled ? 'color:#f5c84b;' : '' }}"></span>
              @endfor
            </span>
            <span>{{ number_format($r, 1) }} <span class="text-muted">({{ $reviewCount }})</span></span>
          </div>
        @endif
        <div class="meta">
          @if($durationLabel)
            <span class="item">{{ $durationLabel }}</span>
          @endif
          @if($hasOnline && $primary === null)
            <span class="item"><svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M4.37 7.657c2.063.528 2.396 2.806 3.202 3.87 1.07 1.413 2.075 1.228 3.192 2.644 1.805 2.289 1.312 5.705 1.312 6.705M20 15h-1a4 4 0 0 0-4 4v1M8.587 3.992c0 .822.112 1.886 1.515 2.58 1.402.693 2.918.351 2.918 2.334 0 .276 0 2.008 1.972 2.008 2.026.031 2.026-1.678 2.026-2.008 0-.65.527-.9 1.177-.9H20M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg><span class="label">Online</span></span>
          @elseif(!$hasOnline && $primary && $remainingCount === 0)
            <span class="item"><svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.8 13.938h-.011a7 7 0 1 0-11.464.144h-.016l.14.171c.1.127.2.251.3.371L12 21l5.13-6.248c.194-.209.374-.429.54-.659l.13-.155Z"/></svg><span class="label">{{ $primary }}</span></span>
          @else
            @if($hasOnline)
              <span class="item"><svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M4.37 7.657c2.063.528 2.396 2.806 3.202 3.87 1.07 1.413 2.075 1.228 3.192 2.644 1.805 2.289 1.312 5.705 1.312 6.705M20 15h-1a4 4 0 0 0-4 4v1M8.587 3.992c0 .822.112 1.886 1.515 2.58 1.402.693 2.918.351 2.918 2.334 0 .276 0 2.008 1.972 2.008 2.026.031 2.026-1.678 2.026-2.008 0-.65.527-.9 1.177-.9H20M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg><span class="label">Online</span></span>
            @endif
            @if($primary)
              <span class="item"><svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.8 13.938h-.011a7 7 0 1 0-11.464.144h-.016l.14.171c.1.127.2.251.3.371L12 21l5.13-6.248c.194-.209.374-.429.54-.659l.13-.155Z"/></svg><span class="label">{{ $primary }}</span></span>
            @endif
            @if($remainingCount > 0)
              <span class="item loc-overflow">
                <span class="chip" role="button" tabindex="0" data-href="{{ $url }}#locations" aria-haspopup="dialog" aria-expanded="false">+{{ $remainingCount }}</span>
                <div class="loc-popover" role="dialog" aria-label="Available locations">
                  <h4>Available locations</h4>
                  <div class="list">
                    @if($hasOnline)
                      <span class="pill"><svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M4.37 7.657c2.063.528 2.396 2.806 3.202 3.87 1.07 1.413 2.075 1.228 3.192 2.644 1.805 2.289 1.312 5.705 1.312 6.705M20 15h-1a4 4 0 0 0-4 4v1M8.587 3.992c0 .822.112 1.886 1.515 2.58 1.402.693 2.918.351 2.918 2.334 0 .276 0 2.008 1.972 2.008 2.026.031 2.026-1.678 2.026-2.008 0-.65.527-.9 1.177-.9H20M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        Online</span>
                    @endif
                    @php $shown = 0; @endphp
                    @foreach($physicalShort as $ps)
                      @continue(mb_strtolower($ps) === mb_strtolower($primary))
                      @php if ($shown++ >= 6) break; @endphp
                      <span class="pill"><svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.8 13.938h-.011a7 7 0 1 0-11.464.144h-.016l.14.171c.1.127.2.251.3.371L12 21l5.13-6.248c.194-.209.374-.429.54-.659l.13-.155Z"/></svg>
                        {{ $ps }}</span>
                    @endforeach
                  </div>
                  <a class="link" href="{{ $url }}#locations">View all locations</a>
                </div>
              </span>
            @endif
          @endif
          @if($nextLabel)
            <span class="item">Next: {{ $nextLabel }}</span>
          @endif
        </div>
        <p class="fomo mt-2">{{ $fomoText ?: 'Click to find out more information' }}</p>
        @if($priceMin)
          <div class="price">
            <span class="from">From</span>
            <span class="now">£{{ number_format((float)$priceMin, 2) }}</span>
            @if($compareMin && $compareMin > $priceMin)
              <span class="was">(was £{{ number_format((float)$compareMin, 2) }})</span>
            @endif
          </div>
        @endif
      </div>
      <div class="actions">
        <a href="{{ $url }}" class="btn btn--primary" role="button">View</a>
      </div>
    </aside>
  </article>
</div>
