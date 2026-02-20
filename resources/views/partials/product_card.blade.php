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
    $type = $product->product_type ?: 'Experience';
    $category = $product->category?->name;
    $priceMin = $product->variants_min_price ?? ($product->price ?? null);
    // Normalise pennies to pounds where needed
    if (is_numeric($priceMin) && $priceMin > 1000 && $priceMin % 100 === 0) { $priceMin = $priceMin / 100; }
    $rating = isset($product->reviews_avg_rating) ? round((float)$product->reviews_avg_rating, 1) : null;
    $reviewCount = (int) ($product->reviews_count ?? 0);

    $locations = $product->getLocations();
    $isOnline = in_array('Online', $locations, true);
    $physical = array_values(array_filter($locations, fn($l) => $l !== 'Online'));
    $mode = $isOnline && count($physical) === 0 ? 'Online' : (count($physical) ? 'In-person' : null);
    $locBadge = $mode ?: ($category ?: null);
@endphp

<a href="{{ $url }}" class="wow-card md {{ str_contains(strtolower($type),'retreat') ? 'retreat' : '' }}">
    <div class="wow-media">
        <img src="{{ $image }}" alt="{{ $title }}" loading="lazy">
        @if($locBadge)
            <div class="badge-chip">{{ $locBadge }}</div>
        @endif
    </div>
    <div class="wow-body">
        <div class="wow-type text-muted">{{ $type }}</div>
        <div class="wow-title">{{ $title }}</div>
        @if($rating)
            <div class="rating-text">★ {{ number_format($rating,1) }} @if($reviewCount) <small class="text-muted">({{ $reviewCount }})</small>@endif</div>
        @endif
    </div>
    <div class="wow-bottom">
        @if($priceMin)
            <div class="price">£{{ number_format((float)$priceMin, 2) }} <small>from</small></div>
        @endif
        <div class="actions">
            <span class="link-wow">View</span>
        </div>
    </div>
    @if(str_contains(strtolower($type),'retreat'))
      <div class="retreat-strip" aria-hidden="true"></div>
    @endif
</a>

