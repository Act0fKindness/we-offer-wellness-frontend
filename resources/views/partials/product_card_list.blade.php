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
                  <span class="badge-chip">Top Rated</span>
              @endif
          </div>

          <button class="icon-btn" type="button" aria-label="Save" aria-pressed="false" title="Save">
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
              <div class="rating-row" aria-label="Rating {{ number_format($rating,1) }} out of 5">
                  <span class="stars" aria-hidden="true">
                      @php
                          $full = (int) floor($rating);
                          $empties = 5 - $full;
                      @endphp
                      @for($i=0; $i<$full; $i++)
                          <span class="star" style="color:#f5c84b;"></span>
                      @endfor
                      @for($i=0; $i<$empties; $i++)
                          <span class="star star--empty"></span>
                      @endfor
                  </span>
                  <span class="text-muted">{{ number_format($rating,1) }} ({{ $reviewCount }})</span>
              </div>
          @endif

          <div class="wow-meta">
              @if($locationLabel)
                  <span class="chip">
                      <i class="bi bi-geo-alt"></i>
                      <span class="label">{{ $locationLabel }}@if($remainingCount>0) (+{{ $remainingCount }})@endif</span>
                  </span>
              @endif
          </div>
      </div>
  </div>

  <!-- Col 3 -->
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

      <div class="wow-actions">
          <button type="button" class="btn js-add-to-cart js-open-cart"
                  data-id="{{ $product->id }}"
                  data-title="{{ e($titleFormatted) }}"
                  data-price="{{ is_numeric($priceMin) ? number_format((float)$priceMin, 2, '.', '') : '0' }}"
                  data-image="{{ $image }}"
                  data-url="{{ $url }}"
          >Add to cart</button>
          <span class="btn btn--primary js-buy-now" role="button" tabindex="0" data-id="{{ $product->id }}">Book now</span>
      </div>
  </div>

</div>
