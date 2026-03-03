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
    $typeMap = [
        'therapies' => 'Therapy',
        'workshops' => 'Workshop',
        'events' => 'Event',
        'classes' => 'Class',
        'retreats' => 'Retreat',
        'gifts' => 'Gift',
    ];
    $typeRaw = trim((string) ($product->product_type ?? ''));
    if ($typeRaw === '') {
        $typeRaw = $typeMap[$seg] ?? 'Experience';
    }
    $typeLabel = $ucWords($toLower($typeRaw));

    $categoryRaw = $product->category?->name
        ?? ($product->category_name ?? null)
        ?? ($product->category_label ?? null)
        ?? ((is_string($product->category ?? null)) ? $product->category : null);
    if (is_array($categoryRaw)) {
        $categoryRaw = $categoryRaw['name'] ?? reset($categoryRaw) ?? null;
    }
    $categoryLabel = $categoryRaw ? $ucWords($toLower(str_replace(['_', '-'], ' ', $categoryRaw))) : null;
    $categoryBadgeLabel = $categoryLabel ?? $typeLabel;
    $priceMin = $product->variants_min_price ?? ($product->price ?? null);
    // Normalise pennies to pounds where needed
    if (is_numeric($priceMin) && $priceMin > 1000 && $priceMin % 100 === 0) { $priceMin = $priceMin / 100; }
    $rating = isset($product->reviews_avg_rating) ? round((float)$product->reviews_avg_rating, 1) : null;
    $reviewCount = (int) ($product->reviews_count ?? 0);

    // Helper: shorten physical address to "Place, City" (or just City)
    $shortLocation = function($address){
        try {
            $s = trim((string)$address);
            if ($s === '') return $s;
            // Remove country labels
            $s = preg_replace('/\b(United Kingdom|UK|England|Scotland|Wales|Northern Ireland)\b/i', '', $s);
            // Remove UK postcodes (e.g., SE24 0PA)
            $s = preg_replace('/\b[A-Z]{1,2}\d{1,2}[A-Z]?\s*\d[A-Z]{2}\b/i', '', $s);
            // Collapse whitespace
            $s = preg_replace('/\s+/', ' ', $s);
            // Split by comma and trim
            $parts = array_values(array_filter(array_map('trim', explode(',', $s)), fn($p) => $p !== ''));
            if (count($parts) >= 2) {
                $place = $parts[0];
                $city = null;
                // pick last non-street-like segment as city
                for ($i = count($parts) - 1; $i >= 1; $i--) {
                    $p = $parts[$i];
                    if (!preg_match('/\b(rd|road|street|st|avenue|ave|lane|ln|drive|dr|way|close|cl|place|pl|boulevard|blvd)\b/i', $p)) {
                        $city = $p; break;
                    }
                }
                if ($city === null) { $city = end($parts); }
                if (strcasecmp($place, $city) === 0) return $city;
                return trim($place . ', ' . $city);
            }
            if (count($parts) === 1) return $parts[0];
            return $s;
        } catch (\Throwable $e) {
            return (string)$address;
        }
    };

    // Improved: robust shortening with street/postcode/country handling
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
    // Normalize list and derive availability flags
    $hasOnline = in_array('Online', $locations, true);
    $isOnline = $hasOnline; // Back-compat with existing variable naming
    $physical = array_values(array_filter($locations, fn($l) => $l !== 'Online'));
    // Build short unique labels for physical locations
    $physicalShort = [];
    $seenShort = [];
    foreach ($physical as $locRaw) {
        $short = $shortLocation2($locRaw);
        $key = mb_strtolower($short ?? '');
        if ($short && !isset($seenShort[$key])) { $seenShort[$key] = true; $physicalShort[] = $short; }
    }
    // Determine preferred/primary location if provided via context
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
    $mode = $isOnline && count($physical) === 0 ? 'Online' : (count($physical) ? 'In-person' : null);
    $locBadge = $mode ?: ($categoryLabel ?? null);


    // Additional fields for exact card details
    $provider = $product->vendor_name
        ?? (is_object($product->vendor ?? null) ? ($product->vendor->vendor_name ?? null) : null)
        ?? $product->practitioner_name
        ?? $product->provider
        ?? null;
    if ($provider) {
        $provider = str_replace('_', ' ', $provider);
        // Title-case provider: first letter of each word uppercase
        $providerFormatted = $ucWords($toLower($provider));
    } else {
        $providerFormatted = null;
    }
    $durationLabel = $product->duration ?? null;
    // Back-compat single label (used elsewhere) – first choice or Online-only
    $locationLabel = ($isOnline && count($physical)===0)
        ? 'Online'
        : ($primary ?: null);
    $nextLabel = $product->next_label ?? $product->next ?? null;
    $benefitText = $product->benefit ?? ($product->summary ?? null);
    $fomoText = $product->fomo_text ?? null;
    $compareMin = $product->variants_min_compare ?? ($product->compare_at_price ?? null);
    if (is_numeric($compareMin) && $compareMin > 1000 && $compareMin % 100 === 0) { $compareMin = $compareMin / 100; }
@endphp

@php
    // Hide zero-priced products globally (temporary request)
    $__priceVal = is_numeric($priceMin) ? (float)$priceMin : null;
    if ($__priceVal === null || $__priceVal <= 0.0) { return; }
@endphp

@once
  <style>
  /* Scoped EXACT card styles (prefixed with .wow-therapy-card-scope to avoid global bleed) */
  .wow-therapy-card-scope{ --bg:#f4f5f7; --card:#fff; --shadow:0 18px 55px rgba(16,24,40,.10); --text:#0b1220; --muted:rgba(11,18,32,.62); --wowGreen:#1f7a4a; --wowGreenDark:#17643d; --badgeWarmBg:#ffe7c2; --badgeWarmText:#6b4b12; --badgeCoolBg:#dfe9ff; --badgeCoolText:#1f3a77; --container:1280px; --gap:16px; --radius:7px; --borderW:1px; --imgH:150px; --padTop:15px; --padX:15px; --padContentX:15px; --padContentY:15px; --badgeH:32px; --badgePx:10px; --badgeFont:12px; --badgeIcon:18px; --save:34px; --saveR:3px; --saveIcon:18px; --title:22px; --fomo: 12px; --provider:13px; --rating:13px; --star:18px; --meta:12px; --metaIcon:14px; --benefit:12px; --fomo:12px; --from:13px; --priceNow:22px; --was:13px; --btnH:38px; --btnR:4px; --btnFont:16px; --cardH:580px; --cta-bg: linear-gradient(180deg, var(--wowGreen), var(--wowGreenDark)); }
  .wow-therapy-card-scope .therapy-card{ background: var(--card); border: var(--borderW) solid rgba(16,24,40,.18); border-radius: var(--radius); box-shadow: var(--shadow); overflow:hidden; position:relative; min-width:0; height: var(--cardH); display:flex; flex-direction:column }
  .wow-therapy-card-scope .card-top{ display:flex; align-items:center; justify-content:space-between; gap:10px; padding: var(--padTop) var(--padX) 10px; background:#fff; flex:0 0 auto }
  .wow-therapy-card-scope .card-top-left{ flex:1 1 auto; min-width:0; display:flex; align-items:center; gap:10px; overflow:hidden }
  .wow-therapy-card-scope .badges{ flex:1 1 auto; min-width:0; display:flex; align-items:center; gap:8px; flex-wrap:nowrap; white-space:nowrap; overflow:hidden }
  .wow-therapy-card-scope .badge{ height: var(--badgeH); display:inline-flex; align-items:center; gap:4px; padding:0 var(--badgePx); border-radius:3px; border:1px solid rgba(16,24,40,.10); font-weight:600; font-size: var(--badgeFont); line-height:1; white-space:nowrap; flex:0 0 auto }
  .wow-therapy-card-scope .badge--warm{ background: var(--badgeWarmBg); color: var(--badgeWarmText) }
  .wow-therapy-card-scope .badge--cool{ background: var(--badgeCoolBg); color: var(--badgeCoolText) }
  .wow-therapy-card-scope .badge svg{
    color: #d59d4c;
    width: 18px;
    height: 18px;
    margin-right: 0 !important;
    margin-bottom: 4px;
    transform: translateY(1px);
  }
  .wow-therapy-card-scope .save{ width: var(--save); height: var(--save); border-radius: var(--saveR); border:1px solid rgba(16,24,40,.20); background:#fff; display:grid; place-items:center; cursor:pointer; box-shadow:0 10px 22px rgba(16,24,40,.08); transition: transform .12s ease, box-shadow .12s ease, background .12s ease, border-color .12s ease; flex:0 0 auto }
  .wow-therapy-card-scope .save:hover{ transform: translateY(-1px); box-shadow:0 14px 28px rgba(16,24,40,.12) }
  .wow-therapy-card-scope .save:active{ transform: translateY(0) scale(.99) }
  .wow-therapy-card-scope .save svg{ width: var(--saveIcon); height: var(--saveIcon); color: rgba(11,18,32,.72) }
  .wow-therapy-card-scope .media{ height: var(--imgH); overflow:hidden; flex:0 0 auto; padding:0 15px; border-radius:3px }
  .wow-therapy-card-scope .media img{ width:100%; height:100%; object-fit:cover; border:1px solid #eee; display:block; border-radius:2px }
  .wow-therapy-card-scope .content{ padding: 0px; flex:1 1 auto; display:flex; flex-direction:column; min-height:0 }
  .rating-row{
              display:flex;
              align-items:center;
              gap: 8px;
              margin-bottom: 10px;
              color: rgba(11,18,32,.80);
              font-weight: 400;
              font-size: var(--rating);
          }

          .stars{
              display:inline-flex;
              align-items:center;
              gap: 3px;
              transform: translateY(1px);
          }

          .star{
              width: var(--star);
              height: var(--star);
              display: inline-block;
              position: relative;

              /* gold fill (set via color on the element) */
              background: currentColor;

              /* FILLED mask */
              -webkit-mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20fill%3D%27%23000%27%20d%3D%27M11.083%205.104c.35-.8%201.485-.8%201.834%200l1.752%204.022a1%201%200%200%200%20.84.597l4.463.342c.9.069%201.255%201.2.556%201.771l-3.33%202.723a1%201%200%200%200-.337%201.016l1.03%204.119c.214.858-.71%201.552-1.474%201.106l-3.913-2.281a1%201%200%200%200-1.008%200L7.583%2020.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1%201%200%200%200%206.8%2014.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1%201%200%200%200%20.84-.597l1.753-4.022Z%27%2F%3E%3C%2Fsvg%3E") center/contain no-repeat;
              mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20fill%3D%27%23000%27%20d%3D%27M11.083%205.104c.35-.8%201.485-.8%201.834%200l1.752%204.022a1%201%200%200%200%20.84.597l4.463.342c.9.069%201.255%201.2.556%201.771l-3.33%202.723a1%201%200%200%200-.337%201.016l1.03%204.119c.214.858-.71%201.552-1.474%201.106l-3.913-2.281a1%201%200%200%200-1.008%200L7.583%2020.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1%201%200%200%200%206.8%2014.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1%201%200%200%200%20.84-.597l1.753-4.022Z%27%2F%3E%3C%2Fsvg%3E") center/contain no-repeat;
          }

          /* grey outline (NOW 2px) */
          .star::after{
              content:"";
              position:absolute;
              inset:0;
              background:#333;
              pointer-events:none;

              -webkit-mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20fill%3D%27none%27%20stroke%3D%27%23000%27%20stroke-width%3D%272%27%20stroke-linejoin%3D%27round%27%20stroke-linecap%3D%27round%27%20d%3D%27M11.083%205.104c.35-.8%201.485-.8%201.834%200l1.752%204.022a1%201%200%200%200%20.84.597l4.463.342c.9.069%201.255%201.2.556%201.771l-3.33%202.723a1%201%200%200%200-.337%201.016l1.03%204.119c.214.858-.71%201.552-1.474%201.106l-3.913-2.281a1%201%200%200%200-1.008%200L7.583%2020.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1%201%200%200%200%206.8%2014.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1%201%200%200%200%20.84-.597l1.753-4.022Z%27%2F%3E%3C%2Fsvg%3E") center/contain no-repeat;
              mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20fill%3D%27none%27%20stroke%3D%27%23000%27%20stroke-width%3D%272%27%20stroke-linejoin%3D%27round%27%20stroke-linecap%3D%27round%27%20d%3D%27M11.083%205.104c.35-.8%201.485-.8%201.834%200l1.752%204.022a1%201%200%200%200%20.84.597l4.463.342c.9.069%201.255%201.2.556%201.771l-3.33%202.723a1%201%200%200%200-.337%201.016l1.03%204.119c.214.858-.71%201.552-1.474%201.106l-3.913-2.281a1%201%200%200%200-1.008%200L7.583%2020.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1%201%200%200%200%206.8%2014.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1%201%200%200%200%20.84-.597l1.753-4.022Z%27%2F%3E%3C%2Fsvg%3E") center/contain no-repeat;
          }

          .star--empty{
              color: transparent;
          }

  .wow-therapy-card-scope .content-top{ flex:1 1 auto; padding: var(--padContentY) var(--padContentX); min-height:0; overflow:hidden }
  .wow-therapy-card-scope .fomo {     margin: 0 0 8px;
                                      font-size: var(--fomo);
                                      font-weight: 600;
                                      color: rgba(11, 18, 32, .84);
                                      display: -webkit-box;
                                      -webkit-line-clamp: 2;
                                      -webkit-box-orient: vertical;
                                      overflow: hidden; }
  .wow-therapy-card-scope .title{ margin:0 0 6px; font-size: var(--title); font-weight:400 !important; line-height:1.12; margin-top:10px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; font-family: 'Manrope', var(--bs-font-sans-serif) !important; text-transform: capitalize }
  .wow-therapy-card-scope .provider{ margin:0 0 8px; color: var(--muted); font-size: var(--provider); font-weight:400 }
  .wow-therapy-card-scope .rating-row{ display:flex; align-items:center; gap:8px; margin-bottom:10px; color: rgba(11,18,32,.80); font-weight:400; font-size: var(--rating) }
  .wow-therapy-card-scope .stars{ display:inline-flex; align-items:center; gap:3px; transform: translateY(1px) }
  .wow-therapy-card-scope .star{ width: 18px; height: 18px; display:inline-block; position:relative; background: currentColor; -webkit-mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20fill%3D%27%23000%27%20d%3D%27M11.083%205.104c.35-.8%201.485-.8%201.834%200l1.752%204.022a1%201%200%200%200%20.84.597l4.463.342c.9.069%201.255%201.2.556%201.771l-3.33%202.723a1%201%200%200%200-.337%201.016l1.03%204.119c.214.858-.71%201.552-1.474%201.106l-3.913-2.281a1%201%200%200%200-1.008%200L7.583%2020.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1%201%200%200%200%206.8%2014.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1%201%200%200%20.84-.597l1.753-4.022Z%27%2F%3E%3C%2Fsvg%3E") center/contain no-repeat; mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20fill%3D%27%23000%27%20d%3D%27M11.083%205.104c.35-.8%201.485-.8%201.834%200l1.752%204.022a1%201%200%200%200%20.84.597l4.463.342c.9.069%201.255%201.2.556%201.771l-3.33%202.723a1%201%200%200%200-.337%201.016l1.03%204.119c.214.858-.71%201.552-1.474%201.106l-3.913-2.281a1%201%200%200%200-1.008%200L7.583%2020.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1%201%200%200%200%206.8%2014.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1%201%200%200%20.84-.597l1.753-4.022Z%27%2F%3E%3C%2Fsvg%3E") center/contain no-repeat }
  .wow-therapy-card-scope .star.star--empty{ background: transparent }
  .wow-therapy-card-scope .star::after{ content:""; position:absolute; inset:0; background:#333; pointer-events:none; -webkit-mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20fill%3D%27none%27%20stroke%3D%27%23000%27%20stroke-width%3D%272%27%20stroke-linejoin%3D%27round%27%20stroke-linecap%3D%27round%27%20d%3D%27M11.083%205.104c.35-.8%201.485-.8%201.834%200l1.752%204.022a1%201%200%200%200%20.84.597l4.463.342c.9.069%201.255%201.2.556%201.771l-3.33%202.723a1%201%200%200%200-.337%201.016l1.03%204.119c.214.858-.71%201.552-1.474%201.106l-3.913-2.281a1%201%200%200%200-1.008%200L7.583%2020.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1%201%200%200%200%206.8%2014.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1%201%200%200%20.84-.597l1.753-4.022Z%27%2F%3E%3C%2Fsvg%3E") center/contain no-repeat; mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20fill%3D%27none%27%20stroke%3D%27%23000%27%20stroke-width%3D%272%27%20stroke-linejoin%3D%27round%27%20stroke-linecap%3D%27round%27%20d%3D%27M11.083%205.104c.35-.8%201.485-.8%201.834%200l1.752%204.022a1%201%200%200%200%20.84.597l4.463.342c.9.069%201.255%201.2.556%201.771l-3.33%202.723a1%201%200%200%200-.337%201.016l1.03%204.119c.214.858-.71%201.552-1.474%201.106l-3.913-2.281a1%201%200%200%200-1.008%200L7.583%2020.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1%201%200%200%200%206.8%2014.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1%201%200%200%20.84-.597l1.753-4.022Z%27%2F%3E%3C%2Fsvg%3E") center/contain no-repeat }
  .wow-therapy-card-scope .meta{ display:flex; align-items:center; flex-wrap:wrap; gap:8px 10px; margin-bottom:8px; color: rgba(11,18,32,.62); font-size: var(--meta); font-weight:400 }
  .wow-therapy-card-scope .meta .item{ display:flex; align-items:center; gap:6px; white-space:nowrap }
  .wow-therapy-card-scope .meta .label{
    display: inline-block;
    max-width: 140px;
    letter-spacing: normal;
    margin-left: 0px;
    font-size: 12px;
    padding-left: 0px;
    font-weight: normal;
    text-transform: capitalize;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .wow-therapy-card-scope .meta .chip{
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10px;
    margin-left: -15px;
    margin-top: -1px;
    padding: 3px 5px;
    cursor: pointer;
    color: rgba(11,18,32,.72);
  }
  .wow-therapy-card-scope .meta .chip:hover{ color: rgba(11,18,32,.92) }
  .wow-therapy-card-scope .meta .chip svg{ width: var(--metaIcon); height: var(--metaIcon) }
  /* Popover for (+N) overflow */
  .wow-therapy-card-scope .loc-overflow{ position:relative; }
  /* Invisible hover bridge so moving from chip to popover doesn't close it */
  .wow-therapy-card-scope .loc-overflow::before{ content:""; position:absolute; left:-8px; right:-8px; top:100%; height:10px; }
  .wow-therapy-card-scope .loc-popover{ position:absolute; top: calc(100% + 2px); left:0; width:240px; max-width:80vw; background:#fff; border:1px solid rgba(16,24,40,.12); box-shadow:0 10px 30px rgba(16,24,40,.12); border-radius:6px; padding:10px; z-index:60; display:none }
  .wow-therapy-card-scope .loc-popover h4{ margin:0 0 8px; font-size:12px; font-weight:700; color:#0b1220 }
  .wow-therapy-card-scope .loc-popover .list{ display:flex; flex-wrap:wrap; gap:6px }
  .wow-therapy-card-scope .loc-popover .pill{ border:1px solid rgba(16,24,40,.14); border-radius:999px; padding:4px 8px; font-size:12px; color:#0b1220; display:inline-flex; align-items:center; gap:4px }
  .wow-therapy-card-scope .loc-popover .link{ display:block; margin-top:8px; font-size:12px; color:#1f3a77; text-decoration:underline }
  /* Show popover on hover/focus for desktop */
  @media (hover:hover){ .wow-therapy-card-scope .loc-overflow:hover .loc-popover, .wow-therapy-card-scope .loc-overflow:focus-within .loc-popover{ display:block } }
  /* Ensure popover can overflow the content area */
  .wow-therapy-card-scope .content-top{ overflow: visible }
  .wow-therapy-card-scope .content-bottom{ flex:0 0 auto; margin-top:auto; padding:15px; border-top:1px solid rgba(16,24,40,.10); background:#fff }
  .wow-therapy-card-scope .fomo{ margin:0 0 8px; font-size: var(--fomo); font-weight:600; color: rgba(11,18,32,.84); display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden }
  .wow-therapy-card-scope .price{ display:flex; align-items:baseline; gap:8px; margin:0 0 12px }
  .wow-therapy-card-scope .price .from{ font-size: var(--from); font-weight:400; color: rgba(11,18,32,.70) }
  .wow-therapy-card-scope .price .now{ font-size: var(--priceNow); font-weight:600; letter-spacing:-.02em; color: rgba(11,18,32,.92) }
  .wow-therapy-card-scope .price .was{ font-size: var(--was); font-weight:400; color: rgba(11,18,32,.75) }
  .wow-therapy-card-scope .actions{ display:grid; grid-template-columns: 1fr 1.15fr; gap:10px }
  .wow-therapy-card-scope .btn{
    height: var(--btnH);
    border-radius: var(--btnR);
    font-size: var(--btnFont);
    font-weight: 400;
    border: 1px solid rgba(16,24,40,.22);
    background: #fff !important;
    color: rgba(11,18,32,.82);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 22px rgba(16,24,40,.08);
  }
  .wow-therapy-card-scope .btn--primary{
    border-color: rgba(0,0,0,.10);
    color: #fff;
    background: #549483 !important;
  }
  .wow-therapy-card-scope .btn:hover,
  .wow-therapy-card-scope .btn:focus{
    background: #f7f7f7 !important;
    color: rgba(11,18,32,.90);
    border-color: rgba(0,0,0,.18);
  }
  @media (max-width: 768px){ .wow-therapy-card-scope .therapy-card{ border-radius:20px; width:300px } }
  /* Override: use provided SVG path for star masks (base and outline) */
  .wow-therapy-card-scope .star,
  .star{
    -webkit-mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20stroke%3D%27%23000%27%20stroke-width%3D%272%27%20d%3D%27M11.083%205.104c.35-.8%201.485-.8%201.834%200l1.752%204.022a1%201%200%200%20%200%20.84.597l4.463.342c.9.069%201.255%201.2.556%201.771l-3.33%202.723a1%201%200%200%20%200-.337%201.016l1.03%204.119c.214.858-.71%201.552-1.474%201.106l-3.913-2.281a1%201%200%200%20%200-1.008%200L7.583%2020.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1%201%200%200%20%200%206.8%2014.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1%201%200%200%20.84-.597l1.753-4.022Z%27%2F%3E%3C%2Fsvg%3E") center/contain no-repeat;
            mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20stroke%3D%27%23000%27%20stroke-width%3D%272%27%20d%3D%27M11.083%205.104c.35-.8%201.485-.8%201.834%200l1.752%204.022a1%201%200%200%20%200%20.84.597l4.463.342c.9.069%201.255%201.2.556%201.771l-3.33%202.723a1%201%200%200%20%200-.337%201.016l1.03%204.119c.214.858-.71%201.552-1.474%201.106l-3.913-2.281a1%201%200%200%20 %200-1.008%200L7.583%2020.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1%201%200%200%20 %200%206.8%2014.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1%201%200%200%20 .84-.597l1.753-4.022Z%27%2F%3E%3C%2Fsvg%3E") center/contain no-repeat;
  }
  .wow-therapy-card-scope .star::after,
  .star::after{
    -webkit-mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20stroke%3D%27%23000%27%20stroke-width%3D%272%27%20d%3D%27M11.083%205.104c.35-.8%201.485-.8%201.834%200l1.752%204.022a1%201%200%200%20 %200%20.84.597l4.463.342c.9.069%201.255%201.2.556%201.771l-3.33%202.723a1%201%200%200%20 %200-.337%201.016l1.03%204.119c.214.858-.71%201.552-1.474%201.106l-3.913-2.281a1%201%200%200%20 %200-1.008%200L7.583%2020.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1%201%200%200%20 %200%206.8%2014.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1%201%200%200%20 .84-.597l1.753-4.022Z%27%2F%3E%3C%2Fsvg%3E") center/contain no-repeat;
            mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20stroke%3D%27%23000%27%20stroke-width%3D%272%27%20d%3D%27M11.083%205.104c.35-.8%201.485-.8%201.834%200l1.752%204.022a1%201%200%200%20 %200%20.84.597l4.463.342c.9.069%201.255%201.2.556%201.771l-3.33%202.723a1%201%200%200%20 %200-.337%201.016l1.03%204.119c.214.858-.71%201.552-1.474%201.106l-3.913-2.281a1%201%200%200%20 %200-1.008%200L7.583%2020.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1%201%200%200%20 %200%206.8%2014.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1%201%200%200%20 .84-.597l1.753-4.022Z%27%2F%3E%3C%2Fsvg%3E") center/contain no-repeat;
  }
  </style>
@endonce

<style>
/* Ensure filled gold center by using filled-path mask (wins by order) */
.wow-therapy-card-scope .star{
  -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23000' d='M11.083 5.104c.35-.8 1.485-.8 1.834 0l1.752 4.022a1 1 0 0 0 .84.597l4.463.342c.9.069 1.255 1.2.556 1.771l-3.33 2.723a1 1 0 0 0-.337 1.016l1.03 4.119c.214.858-.71 1.552-1.474 1.106l-3.913-2.281a1 1 0 0 0-1.008 0L7.583 20.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1 1 0 0 0 6.8 14.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1 1 0 0 0 .84-.597l1.753-4.022Z'/%3E%3C/svg%3E") center/contain no-repeat;
          mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23000' d='M11.083 5.104c.35-.8 1.485-.8 1.834 0l1.752 4.022a1 1 0 0 0 .84.597l4.463.342c.9.069 1.255 1.2.556 1.771l-3.33 2.723a1 1 0 0 0-.337 1.016l1.03 4.119c.214.858-.71 1.552-1.474 1.106l-3.913-2.281a1 1 0 0 0-1.008 0L7.583 20.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1 1 0 0 0 6.8 14.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1 1 0 0 0 .84-.597l1.753-4.022Z'/%3E%3C/svg%3E") center/contain no-repeat;
}
</style>

<div class="wow-therapy-card-scope">
  <a href="{{ $url }}" class="wow-card md">
    <article class="therapy-card" aria-label="Therapy card {{ $product->id }}">
      <header class="card-top">
        <div class="card-top-left">
          <div class="badges">
            <span class="badge badge--warm">
              <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.122 17.645a7.185 7.185 0 0 1-2.656 2.495 7.06 7.06 0 0 1-3.52.853 6.617 6.617 0 0 1-3.306-.718 6.73 6.73 0 0 1-2.54-2.266c-2.672-4.57.287-8.846.887-9.668A4.448 4.448 0 0 0 8.07 6.31 4.49 4.49 0 0 0 7.997 4c1.284.965 6.43 3.258 5.525 10.631 1.496-1.136 2.7-3.046 2.846-6.216 1.43 1.061 3.985 5.462 1.754 9.23Z"/>
              </svg>
              {{ $categoryBadgeLabel }}
            </span>
            <span class="badge badge--cool">{{ $typeLabel }}</span>
          </div>
        </div>
        <button class="save" type="button" aria-label="Save" aria-pressed="false" title="Save">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.01 6.001C6.5 1 1 8 5.782 13.001L12.011 20l6.23-7C23 8 17.5 1 12.01 6.002Z"/></svg>
        </button>
      </header>

      <div class="media">
        <img src="{{ $image }}" alt="{{ $title }}" loading="lazy" />
</div>

{{-- Removed inline JS bindings to avoid unintended click handlers from card markup --}}

      <div class="content">
        <div class="content-top">
          <h2 class="title">{{ $titleFormatted }}</h2>
          @if($providerFormatted)<p class="provider">with {{ $providerFormatted }}</p>@endif
          <div class="rating-row" aria-label="Rating 4.8 out of 5 from 231 reviews">
              <span class="stars" aria-hidden="true">
                <span class="star" style="color:#f5c84b;"></span>
                <span class="star" style="color:#f5c84b;"></span>
                <span class="star" style="color:#f5c84b;"></span>
                <span class="star" style="color:#f5c84b;"></span>
                <span class="star star--empty"></span>
              </span>
              <span></span>
            </div>
          <div class="meta">
            @if($durationLabel)
              <span class="item">{{ $durationLabel }}</span>
            @endif

            {{-- Location tokens: max two tokens + overflow --}}
            @if($hasOnline && $primary === null)
              {{-- A) Online only --}}
              <span class="item">
                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M4.37 7.657c2.063.528 2.396 2.806 3.202 3.87 1.07 1.413 2.075 1.228 3.192 2.644 1.805 2.289 1.312 5.705 1.312 6.705M20 15h-1a4 4 0 0 0-4 4v1M8.587 3.992c0 .822.112 1.886 1.515 2.58 1.402.693 2.918.351 2.918 2.334 0 .276 0 2.008 1.972 2.008 2.026.031 2.026-1.678 2.026-2.008 0-.65.527-.9 1.177-.9H20M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <span class="label">Online</span>
              </span>
            @elseif(!$hasOnline && $primary && $remainingCount === 0)
              {{-- B) One in-person location only --}}
              <span class="item">
                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.8 13.938h-.011a7 7 0 1 0-11.464.144h-.016l.14.171c.1.127.2.251.3.371L12 21l5.13-6.248c.194-.209.374-.429.54-.659l.13-.155Z"/></svg>
                <span class="label">{{ $primary }}</span>
              </span>
            @else
              {{-- C, D, E: combinations with Online and/or many locations --}}
              @if($hasOnline)
                <span class="item">
                  <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M4.37 7.657c2.063.528 2.396 2.806 3.202 3.87 1.07 1.413 2.075 1.228 3.192 2.644 1.805 2.289 1.312 5.705 1.312 6.705M20 15h-1a4 4 0 0 0-4 4v1M8.587 3.992c0 .822.112 1.886 1.515 2.58 1.402.693 2.918.351 2.918 2.334 0 .276 0 2.008 1.972 2.008 2.026.031 2.026-1.678 2.026-2.008 0-.65.527-.9 1.177-.9H20M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                  <span class="label">Online</span>
                </span>
              @endif
              @if($primary)
                <span class="item">
                  <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.8 13.938h-.011a7 7 0 1 0-11.464.144h-.016l.14.171c.1.127.2.251.3.371L12 21l5.13-6.248c.194-.209.374-.429.54-.659l.13-.155Z"/></svg>
                  <span class="label">{{ $primary }}</span>
                </span>
              @endif
              @if($remainingCount > 0)
                <span class="item loc-overflow">
                  <span class="chip" role="link" tabindex="0" data-href="{{ $url }}#locations" aria-haspopup="dialog" aria-expanded="false">+{{ $remainingCount }}</span>
                  <div class="loc-popover" role="dialog" aria-label="Available locations">
                    <h4>Available locations</h4>
                    <div class="list">
                      @if($hasOnline)
                        <span class="pill">
                          <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M4.37 7.657c2.063.528 2.396 2.806 3.202 3.87 1.07 1.413 2.075 1.228 3.192 2.644 1.805 2.289 1.312 5.705 1.312 6.705M20 15h-1a4 4 0 0 0-4 4v1M8.587 3.992c0 .822.112 1.886 1.515 2.58 1.402.693 2.918.351 2.918 2.334 0 .276 0 2.008 1.972 2.008 2.026.031 2.026-1.678 2.026-2.008 0-.65.527-.9 1.177-.9H20M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                          Online
                        </span>
                      @endif
                      @php $shown = 0; @endphp
                      @foreach($physicalShort as $ps)
                        @continue(mb_strtolower($ps) === mb_strtolower($primary))
                        @php if ($shown++ >= 6) break; @endphp
                        <span class="pill">
                          <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.8 13.938h-.011a7 7 0 1 0-11.464.144h-.016l.14.171c.1.127.2.251.3.371L12 21l5.13-6.248c.194-.209.374-.429.54-.659l.13-.155Z"/></svg>
                          {{ $ps }}
                        </span>
                      @endforeach
                    </div>
                    <span class="link" role="link" tabindex="0" data-href="{{ $url }}#locations">View all locations</span>
                  </div>
                </span>
              @endif
            @endif

            @if($nextLabel)
              <span class="item">Next: {{ $nextLabel }}</span>
            @endif
          </div>

        </div>
        <div class="content-bottom">
          <p class="fomo">Click to find out more information</p>
          @if($priceMin)
            <div class="price">
              <span class="from">From</span>
              <span class="now">£{{ number_format((float)$priceMin, 2) }}</span>
              @if($compareMin && $compareMin > $priceMin)
                <span class="was">(was £{{ number_format((float)$compareMin, 2) }})</span>
              @endif
            </div>
          @endif
          <div class="actions">
            <button type="button" class="btn js-add-to-cart js-open-cart"
              data-id="{{ $product->id }}"
              data-product-id="{{ $product->id }}"
              data-title="{{ e($titleFormatted) }}"
              data-price="{{ is_numeric($priceMin) ? number_format((float)$priceMin, 2, '.', '') : '0' }}"
              data-image="{{ $image }}"
              data-url="{{ $url }}"
            >Add to cart</button>
            <button type="button" class="btn btn--primary js-buy-now"
              data-id="{{ $product->id }}"
              data-product-id="{{ $product->id }}"
              data-title="{{ e($titleFormatted) }}"
              data-price="{{ is_numeric($priceMin) ? number_format((float)$priceMin, 2, '.', '') : '0' }}"
              data-image="{{ $image }}"
              data-url="{{ $url }}"
              data-qty="1"
            >Book now</button>
          </div>
        </div>
      </div>
    </article>
  </a>
</div>
