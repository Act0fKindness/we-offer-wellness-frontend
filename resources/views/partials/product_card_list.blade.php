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
    $toLower = function($s){ return function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s); };
    $ucWords = function($s){ return function_exists('mb_convert_case') ? mb_convert_case($s, MB_CASE_TITLE, 'UTF-8') : ucwords($s); };
    $titleFormatted = $ucWords($toLower($title));

    $priceMin = $product->variants_min_price ?? ($product->price ?? null);
    if (is_numeric($priceMin) && $priceMin > 1000 && $priceMin % 100 === 0) { $priceMin = $priceMin / 100; }
    $compareMin = $product->variants_min_compare ?? ($product->compare_at_price ?? null);
    if (is_numeric($compareMin) && $compareMin > 1000 && $compareMin % 100 === 0) { $compareMin = $compareMin / 100; }

    $rating = isset($product->reviews_avg_rating) ? round((float)$product->reviews_avg_rating, 1) : null;
    $reviewCount = (int) ($product->reviews_count ?? 0);

    // Provider
    $provider = $product->vendor_name
        ?? (is_object($product->vendor ?? null) ? ($product->vendor->vendor_name ?? null) : null)
        ?? $product->practitioner_name
        ?? $product->provider
        ?? null;
    $providerFormatted = $provider ? $ucWords($toLower(str_replace('_', ' ', $provider))) : null;

    // Locations
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
        $key = function_exists('mb_strtolower') ? mb_strtolower($short ?? '') : strtolower($short ?? '');
        if ($short && !isset($seenShort[$key])) { $seenShort[$key] = true; $physicalShort[] = $short; }
    }
    $preferredLocation = $preferredLocation ?? ($currentLocation ?? (request()->get('location') ?? null));
    $primary = null;
    if ($preferredLocation) {
        $prefKey = function_exists('mb_strtolower') ? mb_strtolower($shortLocation2($preferredLocation)) : strtolower($shortLocation2($preferredLocation));
        foreach ($physicalShort as $ps) { if ((function_exists('mb_strtolower') ? mb_strtolower($ps) : strtolower($ps)) === $prefKey) { $primary = $ps; break; } }
    }
    if ($primary === null) { $primary = $physicalShort[0] ?? null; }
    $remainingCount = max(0, count($physicalShort) - ($primary ? 1 : 0));
    $locationLabel = ($hasOnline && count($physical)===0) ? 'Online' : ($primary ?: null);

    // Geo (best-effort)
    $lat = $product->lat ?? $product->latitude ?? ($product->geo_lat ?? null);
    $lng = $product->lng ?? $product->longitude ?? ($product->geo_lng ?? null);
@endphp


    <style>
        :root{
            --bg:#f4f5f7;
            --card:#fff;
            --shadow:0 18px 55px rgba(16,24,40,.10);
            --text:#0b1220;
            --muted:rgba(11,18,32,.62);

            --badgeWarmBg:#ffe7c2;
            --badgeWarmText:#6b4b12;
            --badgeCoolBg:#dfe9ff;
            --badgeCoolText:#1f3a77;

            --radius:14px;
            --borderW:1px;

            /* ✅ 3 columns — make column 2 the widest by design */
            --col1Min: 240px;   /* image column minimum */
            --col3Min: 320px;   /* price/actions column minimum */
            --col1Flex: 0.9fr;  /* image share */
            --col2Flex: 2.6fr;  /* content share (BIGGEST) */
            --col3Flex: 1.1fr;  /* aside share */

            --pad:14px;

            --badgeH:32px;
            --badgePx:10px;
            --badgeFont:12px;

            --save:36px;
            --saveR:10px;
            --saveIcon:18px;

            --title:22px;
            --provider:13px;
            --rating:13px;
            --star:18px;

            --meta:12px;
            --metaIcon:14px;

            --btnH:40px;
            --btnR:12px;
            --btnFont:14px;

            --from:12px;
            --priceNow:22px;
            --was:12px;

            --mapRadius:18px;
            --mapBorder:rgba(16,24,40,.12);
        }

        body{
            background:var(--bg);
            color:var(--text);
            font-family: Manrope, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .wow-shell{
            max-width:1480px;
            margin:0 auto;
            padding:18px 16px 26px;
        }

        .wow-topbar{
            display:flex;
            align-items:flex-end;
            justify-content:space-between;
            gap:12px;
            margin-bottom:14px;
        }

        .wow-title{
            margin:0;
            font-size:22px;
            letter-spacing:-.02em;
            font-weight:800;
        }

        .wow-sub{
            margin:6px 0 0;
            color:var(--muted);
            font-size:13px;
        }

        .wow-map-wrap{
            position:sticky;
            top:14px;
        }

        #map{
            width:100%;
            height:calc(100vh - 44px);
            min-height:520px;
            border-radius:var(--mapRadius);
            border:1px solid var(--mapBorder);
            overflow:hidden;
            box-shadow:0 18px 55px rgba(16,24,40,.12);
            background:#e9edf3;
        }

        /* ====== Row card: 3 columns (col2 is widest) ====== */
        .wow-row-card{
            background:var(--card);
            border:var(--borderW) solid rgba(16,24,40,.16);
            border-radius:var(--radius);
            box-shadow:var(--shadow);
            overflow:hidden;

            display:grid;
            grid-template-columns:
        minmax(var(--col1Min), var(--col1Flex))
        minmax(0, var(--col2Flex))
        minmax(var(--col3Min), var(--col3Flex));

            transition:transform .14s ease, box-shadow .14s ease, border-color .14s ease;
            position:relative;
        }

        .wow-row-card:hover{
            transform:translateY(-1px);
            box-shadow:0 22px 70px rgba(16,24,40,.14);
            border-color:rgba(16,24,40,.22);
        }

        .wow-row-card[data-url]{ cursor:pointer; }

        /* Col 1: image touches top/left/bottom edges */
        .wow-row-media{
            grid-column:1;
            background:#f2f3f5;
            height:100%;
            min-height:210px;
        }
        .wow-row-media img{
            width:100%;
            height:100%;
            object-fit:cover;
            display:block;
        }

        /* Col 2: main content */
        .wow-row-body{
            grid-column:2;
            min-width:0;
            display:flex;
            flex-direction:column;
            padding:var(--pad);
            gap:12px;
        }

        .wow-row-top{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:10px;
            min-width:0;
        }

        .wow-badges{
            display:flex;
            align-items:center;
            gap:8px;
            overflow:hidden;
            white-space:nowrap;
            min-width:0;
        }

        .wow-badge{
            height:var(--badgeH);
            display:inline-flex;
            align-items:center;
            gap:6px;
            padding:0 var(--badgePx);
            border-radius:999px;
            border:1px solid rgba(16,24,40,.10);
            font-weight:800;
            font-size:var(--badgeFont);
            line-height:1;
            flex:0 0 auto;
        }

        .wow-badge--warm{ background:var(--badgeWarmBg); color:var(--badgeWarmText); }
        .wow-badge--cool{ background:var(--badgeCoolBg); color:var(--badgeCoolText); }

        .wow-badge svg{
            width:18px;height:18px;
            color:#d59d4c;
            transform:translateY(1px);
            flex:0 0 auto;
        }

        .wow-save{
            width:var(--save);
            height:var(--save);
            border-radius:var(--saveR);
            border:1px solid rgba(16,24,40,.18);
            background:#fff;
            display:grid;
            place-items:center;
            box-shadow:0 10px 22px rgba(16,24,40,.08);
            transition:transform .12s ease, box-shadow .12s ease;
            flex:0 0 auto;
        }
        .wow-save:hover{ transform:translateY(-1px); box-shadow:0 14px 28px rgba(16,24,40,.12); }
        .wow-save:active{ transform:translateY(0) scale(.99); }
        .wow-save svg{ width:var(--saveIcon); height:var(--saveIcon); color:rgba(11,18,32,.72); }

        .wow-row-title{
            font-size:var(--title);
            line-height:1.12;
            letter-spacing:-.015em;
            font-weight:900;
            margin:0;
            display:-webkit-box;
            -webkit-line-clamp:2;
            -webkit-box-orient:vertical;
            overflow:hidden;
        }

        .wow-row-provider{
            margin:6px 0 0;
            color:var(--muted);
            font-size:var(--provider);
        }

        .wow-rating{
            display:flex;
            align-items:center;
            gap:10px;
            color:rgba(11,18,32,.84);
            font-size:var(--rating);
            margin-top:8px;
        }
        .wow-stars{
            display:inline-flex;
            align-items:center;
            gap:3px;
            transform:translateY(1px);
        }

        /* Star: gold fill + grey 2px outline */
        .wow-star{
            width:var(--star);
            height:var(--star);
            display:inline-block;
            position:relative;
            background:currentColor;
            -webkit-mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23000' d='M11.083 5.104c.35-.8 1.485-.8 1.834 0l1.752 4.022a1 1 0 0 0 .84.597l4.463.342c.9.069 1.255 1.2.556 1.771l-3.33 2.723a1 1 0 0 0-.337 1.016l1.03 4.119c.214.858-.71 1.552-1.474 1.106l-3.913-2.281a1 1 0 0 0-1.008 0L7.583 20.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1 1 0 0 0 6.8 14.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1 1 0 0 0 .84-.597l1.753-4.022Z'/%3E%3C/svg%3E") center/contain no-repeat;
            mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23000' d='M11.083 5.104c.35-.8 1.485-.8 1.834 0l1.752 4.022a1 1 0 0 0 .84.597l4.463.342c.9.069 1.255 1.2.556 1.771l-3.33 2.723a1 1 0 0 0-.337 1.016l1.03 4.119c.214.858-.71 1.552-1.474 1.106l-3.913-2.281a1 1 0 0 0-1.008 0L7.583 20.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1 1 0 0 0 6.8 14.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1 1 0 0 0 .84-.597l1.753-4.022Z'/%3E%3C/svg%3E") center/contain no-repeat;
        }
        .wow-star::after{
            content:"";
            position:absolute;
            inset:0;
            background:#333;
            pointer-events:none;
            -webkit-mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='none' stroke='%23000' stroke-width='2' stroke-linejoin='round' stroke-linecap='round' d='M11.083 5.104c.35-.8 1.485-.8 1.834 0l1.752 4.022a1 1 0 0 0 .84.597l4.463.342c.9.069 1.255 1.2.556 1.771l-3.33 2.723a1 1 0 0 0-.337 1.016l1.03 4.119c.214.858-.71 1.552-1.474 1.106l-3.913-2.281a1 1 0 0 0-1.008 0L7.583 20.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1 1 0 0 0 6.8 14.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1 1 0 0 0 .84-.597l1.753-4.022Z'/%3E%3C/svg%3E") center/contain no-repeat;
            mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='none' stroke='%23000' stroke-width='2' stroke-linejoin='round' stroke-linecap='round' d='M11.083 5.104c.35-.8 1.485-.8 1.834 0l1.752 4.022a1 1 0 0 0 .84.597l4.463.342c.9.069 1.255 1.2.556 1.771l-3.33 2.723a1 1 0 0 0-.337 1.016l1.03 4.119c.214.858-.71 1.552-1.474 1.106l-3.913-2.281a1 1 0 0 0-1.008 0L7.583 20.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1 1 0 0 0 6.8 14.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1 1 0 0 0 .84-.597l1.753-4.022Z'/%3E%3C/svg%3E") center/contain no-repeat;
        }
        .wow-star--empty{ background:transparent; }
        .wow-star--empty::after{ background:#333; opacity:.55; }

        .wow-meta{
            display:flex;
            align-items:center;
            flex-wrap:wrap;
            gap:8px 12px;
            color:rgba(11,18,32,.62);
            font-size:var(--meta);
            margin-top:6px;
        }
        .wow-meta .item{
            display:flex;
            align-items:center;
            gap:6px;
            white-space:nowrap;
        }
        .wow-meta svg{
            width:var(--metaIcon);
            height:var(--metaIcon);
            color:rgba(11,18,32,.58);
        }
        .wow-meta .label{
            max-width:320px;
            overflow:hidden;
            text-overflow:ellipsis;
        }

        /* Col 3: your snippet */
        .wow-row-bottom{
            grid-column:3;
            padding:var(--pad);
            border-left:1px solid rgba(16,24,40,.10);
            background:#fff;

            display:flex;
            flex-direction:column;
            justify-content:space-between;
            gap:14px;
        }

        .wow-fomo{
            margin:0 0 6px;
            font-size:12px;
            font-weight:800;
            color:rgba(11,18,32,.84);
        }

        .wow-price{
            display:flex;
            align-items:baseline;
            gap:8px;
            margin:0;
        }
        .wow-price .from{ font-size:var(--from); color:rgba(11,18,32,.70); }
        .wow-price .now{ font-size:var(--priceNow); font-weight:900; letter-spacing:-.02em; }
        .wow-price .was{ font-size:var(--was); color:rgba(11,18,32,.75); }

        .wow-actions{
            display:grid;
            grid-template-columns:1fr;
            gap:10px;
            width:100%;
            min-width:0;
        }

        .wow-btn{
            height:var(--btnH);
            border-radius:var(--btnR);
            font-size:var(--btnFont);
            font-weight:800;
            border:1px solid rgba(16,24,40,.18);
            background:#fff;
            color:rgba(11,18,32,.84);
            box-shadow:0 10px 22px rgba(16,24,40,.08);
            display:flex;
            align-items:center;
            justify-content:center;
            user-select:none;
        }
        .wow-btn:hover{ background:#f7f7f7; border-color:rgba(16,24,40,.24); }
        .wow-btn--primary{
            background:linear-gradient(180deg,#549483,#3f7f6f);
            color:#fff;
            border-color:rgba(0,0,0,.08);
        }
        .wow-btn--primary:hover{ filter:brightness(.98); }

        .wow-list{
            display:flex;
            flex-direction:column;
            gap:14px;
        }
        .wow-filters{
            display:flex;
            gap:10px;
            flex-wrap:wrap;
        }
        .wow-pill{
            border:1px solid rgba(16,24,40,.14);
            background:#fff;
            border-radius:999px;
            padding:8px 12px;
            font-size:13px;
            color:rgba(11,18,32,.84);
            box-shadow:0 10px 22px rgba(16,24,40,.06);
        }

        /* Responsive */
        @media (max-width: 1199.98px){
            :root{
                --col1Min: 220px;
                --col3Min: 300px;
                --col1Flex: 0.85fr;
                --col2Flex: 2.8fr;  /* keep col2 dominant */
                --col3Flex: 1.05fr;
            }
            #map{ height:560px; }
            .wow-map-wrap{ position:relative; top:0; }
        }

        @media (max-width: 991.98px){
            :root{
                --col1Min: 210px;
                --col3Min: 290px;
                --col1Flex: 0.85fr;
                --col2Flex: 3fr;    /* even more dominance */
                --col3Flex: 1fr;
            }
            #map{ height:520px; }
        }

        @media (max-width: 767.98px){
            .wow-row-card{ grid-template-columns: 1fr; }
            .wow-row-media{ grid-column:1; height:220px; }
            .wow-row-body{ grid-column:1; }
            .wow-row-bottom{
                grid-column:1;
                border-left:0;
                border-top:1px solid rgba(16,24,40,.10);
            }
            .wow-actions{ grid-template-columns: 1fr 1.1fr; }
        }

        @media (prefers-reduced-motion: reduce){
            .wow-row-card{ transition:none; }
            .wow-save{ transition:none; }
        }

        /* Map marker */
        .wow-marker{
            width:18px;
            height:18px;
            border-radius:999px;
            background:#0b1220;
            border:3px solid #fff;
            box-shadow:0 10px 18px rgba(16,24,40,.18);
        }
        .wow-marker.is-active{
            background:#549483;
            transform:scale(1.08);
        }
    </style>

@once
  <style>
    /* Scoped 3-column row card styles (avoid globals). */
    .wow-row-card{
      /* Scoped design tokens */
      --card:#fff; --shadow:0 18px 55px rgba(16,24,40,.10); --text:#0b1220; --muted:rgba(11,18,32,.62);
      --badgeWarmBg:#ffe7c2; --badgeWarmText:#6b4b12; --badgeCoolBg:#dfe9ff; --badgeCoolText:#1f3a77;
      --radius:14px; --borderW:1px; --pad:14px;
      --col1Min:240px; --col3Min:320px; --col1Flex:0.9fr; --col2Flex:2.6fr; --col3Flex:1.1fr;
      --badgeH:32px; --badgePx:10px; --badgeFont:12px; --save:36px; --saveR:10px; --saveIcon:18px;
      --title:22px; --provider:13px; --rating:13px; --star:18px; --meta:12px; --metaIcon:14px;
      --btnH:40px; --btnR:12px; --btnFont:14px; --from:12px; --priceNow:22px; --was:12px;

      background:var(--card);
      border:var(--borderW) solid rgba(16,24,40,.16);
      border-radius:var(--radius);
      box-shadow:var(--shadow);
      overflow:hidden;
      display:grid;
      grid-template-columns:
        minmax(var(--col1Min), var(--col1Flex))
        minmax(0, var(--col2Flex))
        minmax(var(--col3Min), var(--col3Flex));
      transition:transform .14s ease, box-shadow .14s ease, border-color .14s ease;
      position:relative;
    }
    .wow-row-card:hover{ transform:translateY(-1px); box-shadow:0 22px 70px rgba(16,24,40,.14); border-color:rgba(16,24,40,.22); }
    .wow-row-card[data-url]{ cursor:pointer; }
    /* Col 1 */
    .wow-row-media{ grid-column:1; background:#f2f3f5; height:100%; min-height:210px; }
    .wow-row-media img{ width:100%; height:100%; object-fit:cover; display:block; }
    /* Col 2 */
    .wow-row-body{ grid-column:2; min-width:0; display:flex; flex-direction:column; padding:var(--pad); gap:12px; }
    .wow-row-top{ display:flex; align-items:center; justify-content:space-between; gap:10px; min-width:0; }
    .wow-row-card .wow-badges{ display:flex; align-items:center; gap:8px; overflow:hidden; white-space:nowrap; min-width:0; position:static; top:auto; left:auto; }
    .wow-row-card .wow-badge{ height:var(--badgeH); display:inline-flex; align-items:center; gap:6px; padding:0 var(--badgePx); border-radius:999px; border:1px solid rgba(16,24,40,.10); font-weight:800; font-size:var(--badgeFont); line-height:1; flex:0 0 auto; }
    .wow-row-card .wow-badge--warm{ background:var(--badgeWarmBg); color:var(--badgeWarmText); }
    .wow-row-card .wow-badge--cool{ background:var(--badgeCoolBg); color:var(--badgeCoolText); }
    .wow-row-card .wow-badge svg{ width:18px; height:18px; color:#d59d4c; transform:translateY(1px); flex:0 0 auto; }
    .wow-save{ width:var(--save); height:var(--save); border-radius:var(--saveR); border:1px solid rgba(16,24,40,.18); background:#fff; display:grid; place-items:center; box-shadow:0 10px 22px rgba(16,24,40,.08); transition:transform .12s ease, box-shadow .12s ease; flex:0 0 auto; }
    .wow-save:hover{ transform:translateY(-1px); box-shadow:0 14px 28px rgba(16,24,40,.12); }
    .wow-save:active{ transform:translateY(0) scale(.99); }
    .wow-save svg{ width:var(--saveIcon); height:var(--saveIcon); color:rgba(11,18,32,.72); }
    .wow-row-title{ font-size:var(--title); line-height:1.12; letter-spacing:-.015em; font-weight:900; margin:0; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
    .wow-row-provider{ margin:6px 0 0; color:var(--muted); font-size:var(--provider); }
    .wow-rating{ display:flex; align-items:center; gap:10px; color:rgba(11,18,32,.84); font-size:var(--rating); margin-top:8px; }
    .wow-stars{ display:inline-flex; align-items:center; gap:3px; transform:translateY(1px); }
    .wow-star{ width:var(--star); height:var(--star); display:inline-block; position:relative; background:currentColor; -webkit-mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23000' d='M11.083 5.104c.35-.8 1.485-.8 1.834 0l1.752 4.022a1 1 0 0 0 .84.597l4.463.342c.9.069 1.255 1.2.556 1.771l-3.33 2.723a1 1 0 0 0-.337 1.016l1.03 4.119c.214.858-.71 1.552-1.474 1.106l-3.913-2.281a1 1 0 0 0-1.008 0L7.583 20.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1 1 0 0 0 6.8 14.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1 1 0 0 0 .84-.597l1.753-4.022Z'/%3E%3C/svg%3E") center/contain no-repeat; mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23000' d='M11.083 5.104c.35-.8 1.485-.8 1.834 0l1.752 4.022a1 1 0 0 0 .84.597l4.463.342c.9.069 1.255 1.2.556 1.771l-3.33 2.723a1 1 0 0 0-.337 1.016l1.03 4.119c.214.858-.71 1.552-1.474 1.106l-3.913-2.281a1 1 0 0 0-1.008 0L7.583 20.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1 1 0 0 0 6.8 14.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1 1 0 0 0 .84-.597l1.753-4.022Z'/%3E%3C/svg%3E") center/contain no-repeat; }
    .wow-star::after{ content:""; position:absolute; inset:0; background:#333; pointer-events:none; -webkit-mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='none' stroke='%23000' stroke-width='2' stroke-linejoin='round' stroke-linecap='round' d='M11.083 5.104c.35-.8 1.485-.8 1.834 0l1.752 4.022a1 1 0 0 0 .84.597l4.463.342c.9.069 1.255 1.2.556 1.771l-3.33 2.723a1 1 0 0 0-.337 1.016l1.03 4.119c.214.858-.71 1.552-1.474 1.106l-3.913-2.281a1 1 0 0 0-1.008 0L7.583 20.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1 1 0 0 0 6.8 14.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1 1 0 0 0 .84-.597l1.753-4.022Z'/%3E%3C/svg%3E") center/contain no-repeat; mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='none' stroke='%23000' stroke-width='2' stroke-linejoin='round' stroke-linecap='round' d='M11.083 5.104c.35-.8 1.485-.8 1.834 0l1.752 4.022a1 1 0 0 0 .84.597l4.463.342c.9.069 1.255 1.2.556 1.771l-3.33 2.723a1 1 0 0 0-.337 1.016l1.03 4.119c.214.858-.71 1.552-1.474 1.106l-3.913-2.281a1 1 0 0 0-1.008 0L7.583 20.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1 1 0 0 0 6.8 14.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1 1 0 0 0 .84-.597l1.753-4.022Z'/%3E%3C/svg%3E") center/contain no-repeat; }
    .wow-star--empty{ background:transparent; }
    .wow-star--empty::after{ background:#333; opacity:.55; }
    .wow-meta{ display:flex; align-items:center; flex-wrap:wrap; gap:8px 12px; color:rgba(11,18,32,.62); font-size:var(--meta); margin-top:6px; }
    .wow-meta .item{ display:flex; align-items:center; gap:6px; white-space:nowrap; }
    .wow-meta svg{ width:var(--metaIcon); height:var(--metaIcon); color:rgba(11,18,32,.58); }
    .wow-meta .label{ max-width:320px; overflow:hidden; text-overflow:ellipsis; }
    /* Col 3 */
    .wow-row-bottom{ grid-column:3; padding:var(--pad); border-left:1px solid rgba(16,24,40,.10); background:#fff; display:flex; flex-direction:column; justify-content:space-between; gap:14px; }
    .wow-fomo{ margin:0 0 6px; font-size:12px; font-weight:800; color:rgba(11,18,32,.84); }
    .wow-price{ display:flex; align-items:baseline; gap:8px; margin:0; }
    .wow-price .from{ font-size:var(--from); color:rgba(11,18,32,.70); }
    .wow-price .now{ font-size:var(--priceNow); font-weight:900; letter-spacing:-.02em; }
    .wow-price .was{ font-size:var(--was); color:rgba(11,18,32,.75); }
    .wow-actions{ display:grid; grid-template-columns:1fr; gap:10px; width:100%; min-width:0; }
    .wow-btn{ height:var(--btnH); border-radius:var(--btnR); font-size:var(--btnFont); font-weight:800; border:1px solid rgba(16,24,40,.18); background:#fff; color:rgba(11,18,32,.84); box-shadow:0 10px 22px rgba(16,24,40,.08); display:flex; align-items:center; justify-content:center; user-select:none; }
    .wow-btn:hover{ background:#f7f7f7; border-color:rgba(16,24,40,.24); }
    .wow-btn--primary{ background:linear-gradient(180deg,#549483,#3f7f6f); color:#fff; border-color:rgba(0,0,0,.08); }
    .wow-btn--primary:hover{ filter:brightness(.98); }
    /* Responsive */
    @media (max-width: 767.98px){
      .wow-row-card{ grid-template-columns: 1fr; }
      .wow-row-media{ grid-column:1; height:220px; }
      .wow-row-body{ grid-column:1; }
      .wow-row-bottom{ grid-column:1; border-left:0; border-top:1px solid rgba(16,24,40,.10); }
      .wow-actions{ grid-template-columns: 1fr 1.1fr; }
    }
  </style>
@endonce

<div class="wow-row-card"
     data-id="{{ $product->id }}"
     data-url="{{ $url }}"
     @if(is_numeric($lat ?? null) && is_numeric($lng ?? null)) data-lat="{{ $lat }}" data-lng="{{ $lng }}" @endif>

  <!-- Col 1 -->
  <div class="wow-row-media">
      @if($image)
          <img src="{{ $image }}" alt="{{ e($titleFormatted) }}" loading="lazy">
      @endif
  </div>

  <!-- Col 2 -->
  <div class="wow-row-body">
      <div class="wow-row-top">
          <div class="wow-badges" aria-label="Badges">
              @if($rating && $rating >= 4.7)
                  <span class="wow-badge wow-badge--cool">Top Rated</span>
              @endif
          </div>

          <button class="wow-save" type="button" aria-label="Save" aria-pressed="false" title="Save">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="18" height="18">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.01 6.001C6.5 1 1 8 5.782 13.001L12.011 20l6.23-7C23 8 17.5 1 12.01 6.002Z"/>
              </svg>
          </button>
      </div>

      <div>
          <h2 class="wow-row-title">{{ $titleFormatted }}</h2>
          @if($providerFormatted)
              <p class="wow-row-provider">with {{ $providerFormatted }}</p>
          @endif

          @if($rating)
              <div class="wow-rating" aria-label="Rating {{ number_format($rating,1) }} out of 5">
                  <span class="wow-stars" aria-hidden="true">
                      @php
                          $full = (int) floor($rating);
                          $empties = 5 - $full;
                      @endphp
                      @for($i=0; $i<$full; $i++)
                          <span class="wow-star" style="color:#f5c84b;"></span>
                      @endfor
                      @for($i=0; $i<$empties; $i++)
                          <span class="wow-star wow-star--empty"></span>
                      @endfor
                  </span>
                  <span class="text-muted">{{ number_format($rating,1) }} ({{ $reviewCount }})</span>
              </div>
          @endif

          <div class="wow-meta">
              @if($locationLabel)
                  <span class="item">
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.8 13.938h-.011a7 7 0 1 0-11.464.144h-.016l.14.171c.1.127.2.251.3.371L12 21l5.13-6.248c.194-.209.374-.429.54-.659l.13-.155Z"/></svg>
                    <span class="label">{{ $locationLabel }}@if($remainingCount>0) (+{{ $remainingCount }})@endif</span>
                  </span>
              @endif
          </div>
      </div>
  </div>

  <!-- Col 3 -->
  <div class="wow-row-bottom">
      <p class="wow-fomo">Click to find out more information</p>
      @if($priceMin)
          <p class="wow-price">
              <span class="from">From</span>
              <span class="now">£{{ number_format((float)$priceMin, 2) }}</span>
              @if($compareMin && $compareMin > $priceMin)
                  <span class="was">(was £{{ number_format((float)$compareMin, 2) }})</span>
              @endif
          </p>
      @endif

      <div class="wow-actions">
          <button type="button" class="wow-btn js-add-to-cart js-open-cart"
                  data-id="{{ $product->id }}"
                  data-title="{{ e($titleFormatted) }}"
                  data-price="{{ is_numeric($priceMin) ? number_format((float)$priceMin, 2, '.', '') : '0' }}"
                  data-image="{{ $image }}"
                  data-url="{{ $url }}"
          >Add to cart</button>
          <span class="wow-btn wow-btn--primary js-buy-now" role="button" tabindex="0" data-id="{{ $product->id }}">Book now</span>
      </div>
  </div>

</div>
