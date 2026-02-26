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
    $type = $product->product_type ?: 'Experience';
    $category = $product->category?->name;
    $priceMin = $product->variants_min_price ?? ($product->price ?? null);
    if (is_numeric($priceMin) && $priceMin > 1000 && $priceMin % 100 === 0) { $priceMin = $priceMin / 100; }
    $rating = isset($product->reviews_avg_rating) ? round((float)$product->reviews_avg_rating, 1) : null;
    $reviewCount = (int) ($product->reviews_count ?? 0);
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
    .wow-card-sm .body{ display:flex; flex-direction:column; gap:6px; padding:10px 12px }
    .wow-card-sm .type{ font-size:.82rem; color:var(--muted) }
    .wow-card-sm .title{ font-size:1rem; line-height:1.25; font-weight:600; color:var(--ink); display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden }
    .wow-card-sm .rating{ font-size:.85rem; color:#111827 }
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
    <div class="type">{{ $category ?: $type }}</div>
    <div class="title">{{ $titleFormatted }}</div>
    @if($rating && $reviewCount)
      <div class="rating">★ {{ number_format($rating, 1) }} <small class="text-muted">({{ $reviewCount }})</small></div>
    @endif
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
