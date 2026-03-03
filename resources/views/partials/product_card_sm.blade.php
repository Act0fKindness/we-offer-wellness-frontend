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
    // Title case: first letter of each word uppercase
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
    $typeRaw = trim((string) ($product->product_type ?? $product->type_name ?? ''));
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
    $priceMin = $product->variants_min_price ?? ($product->price ?? null);
    if (is_numeric($priceMin) && $priceMin > 1000 && $priceMin % 100 === 0) { $priceMin = $priceMin / 100; }
    $rating = isset($product->reviews_avg_rating) ? round((float)$product->reviews_avg_rating, 1) : null;
    $reviewCount = (int) ($product->reviews_count ?? 0);
    $vendorReviewCount = (int) (
        data_get($product, 'vendor_reviews_count')
        ?? data_get($product, 'vendor.reviews_count')
        ?? 0
    );
    $vendorRating = data_get($product, 'vendor_reviews_avg_rating')
        ?? data_get($product, 'vendor.reviews_avg_rating');
@endphp

@php
    // Hide zero-priced products globally (temporary request)
    $__priceVal = is_numeric($priceMin) ? (float)$priceMin : null;
    if ($__priceVal === null || $__priceVal <= 0.0) { return; }
@endphp

@once
  @push('head')
    <style>
    /* Small product card — compact height + slimmer layout */
    .wow-card-sm-wrap{ --border:#e5e7eb; --ink:#0b1323; --muted:#64748b; --shadow:0 10px 24px rgba(2,8,23,.06); }
    .wow-card-sm{ display:grid; grid-template-columns: 120px 1fr; gap:12px; align-items:stretch; width:100%; text-decoration: none; color:inherit; background:#fff; border:1px solid var(--border); border-radius:12px; overflow:hidden; box-shadow: var(--shadow); }
    .wow-card-sm .thumb{ height:100%; min-height:120px; background:#f8fafc; border-right:1px solid var(--border); }
    .wow-card-sm .thumb img{ width:100%; height:100%; object-fit:cover; display:block }
    .wow-card-sm .body{ display:flex; flex-direction:column; gap:8px; padding:10px 12px }
    .wow-card-sm .badges{ display:flex; align-items:center; gap:6px; flex-wrap:wrap; margin-bottom:2px; }
    .wow-card-sm .badge{ height:24px; display:inline-flex; align-items:center; gap:4px; padding:0 8px; border-radius:999px; border:1px solid rgba(11,18,32,.12); font-size:.72rem; font-weight:600; line-height:1; color:#0f1e2e; }
    .wow-card-sm .badge--warm{ background:#ffe7c2; color:#6b4b12; }
    .wow-card-sm .badge--warm.badge--long{ background:#fff2e0; color:#5a350a; }
    .wow-card-sm .badge--cool{ background:#dfe9ff; color:#1f3a77; }
    .wow-card-sm .badge--trust{ background:#e7f5ff; color:#0f3d62; border-color:rgba(15,61,98,.25); }
    .wow-card-sm .badge--trust.badge--trust-trending{ background:#ffe0e7; color:#b21c4f; border-color:rgba(178,28,79,.25); }
    .wow-card-sm .badge svg{ width:14px; height:14px; color:#d59d4c; }
    .wow-card-sm .title{ font-size:1rem; line-height:1.25; font-weight:600; color:var(--ink); display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden }
    .wow-card-sm .bottom{ margin-top:auto; display:flex; align-items:center; justify-content:space-between; gap:8px }
    .wow-card-sm .price{ font-size:1rem; font-weight:600; color:var(--ink) }
    .wow-card-sm .price small{ font-weight:500; color:var(--muted) }
    .wow-card-sm .cta{ color:#0f1e2e; text-decoration: none; font-weight:600 }
    @media (max-width: 575.98px){ .wow-card-sm{ grid-template-columns: 100px 1fr } .wow-card-sm .thumb{ min-height:100px } }
    </style>
  @endpush
@endonce

<a href="{{ $url }}" class="wow-card-sm" aria-label="{{ $titleFormatted }}">
  <div class="thumb">
    @if($image)
      <img src="{{ $image }}" alt="{{ $titleFormatted }}">
    @endif
  </div>
  <div class="body">
    @include('components.product.card_badges', [
        'product' => $product,
        'categoryLabel' => $categoryLabel,
        'typeLabel' => $typeLabel,
        'activeTaxonomy' => $activeTaxonomy ?? null,
        'seg' => $seg,
        'tags' => $tags,
        'rating' => $rating,
        'reviewCount' => $reviewCount,
    ])
    <div class="title">{{ $titleFormatted }}</div>
    @include('components.product.card_rating', [
        'rating' => $rating,
        'reviews' => $reviewCount,
        'vendorRating' => $vendorRating,
        'vendorReviews' => $vendorReviewCount,
    ])
    <div class="bottom">
      @if($priceMin)
        <div class="price">£{{ number_format((float)$priceMin, 2) }} <small>from</small></div>
      @else
        <div></div>
      @endif
      <span class="cta">View</span>
    </div>
  </div>
</a>
