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

<style>
/* Scoped new WOW product card (prefixed wowx- to avoid conflicts) */
.wowx-card{
  --card:#fff; --shadow:0 18px 55px rgba(16,24,40,.10); --text:#0b1220; --muted: rgba(11,18,32,.62);
  --radius:7px; --borderW:2px; --imgH:150px; --padX:15px; --padTop:15px; --padContentX:15px; --padContentY:15px;
  --title:22px; --provider:13px; --priceNow:22px; --btnH:38px; --btnR:4px; --btnFont:16px; --cardH:540px;
  display:flex; flex-direction:column; min-width:0; background:var(--card); border: var(--borderW) solid rgba(16,24,40,.18);
  border-radius: var(--radius); box-shadow: var(--shadow); overflow:hidden; height: var(--cardH); position:relative;
}
.wowx-top{ display:flex; align-items:center; justify-content:space-between; gap:10px; padding: var(--padTop) var(--padX) 10px; background:#fff; }
.wowx-badges{ display:flex; align-items:center; gap:8px; white-space:nowrap; overflow:hidden }
.wowx-badge{ display:inline-flex; align-items:center; gap:6px; height:28px; padding:0 10px; border-radius:3px; border:1px solid rgba(16,24,40,.10); font-size:12px; font-weight:600 }
.wowx-media{ height: var(--imgH); overflow:hidden; flex:0 0 auto; padding:0 15px }
.wowx-media img{ width:100%; height:100%; object-fit:cover; display:block; border-radius:3px }
.wowx-content{ padding: var(--padContentY) var(--padContentX); display:flex; flex-direction:column; min-height:0; flex:1 1 auto }
.wowx-type{ margin:0 0 6px; color: var(--muted); font-size: 12px; font-weight:600 }
.wowx-title{ margin:0 0 6px; font-size: var(--title); font-weight:400; line-height:1.12; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden }
.wowx-rating{ margin: 0 0 10px; color: rgba(11,18,32,.80); font-size:13px; font-weight:400 }
.wowx-bottom{ margin-top:auto; padding-top:10px; border-top:1px solid rgba(16,24,40,.10); background:#fff }
.wowx-price{ display:flex; align-items:baseline; gap:8px; margin:0 0 12px }
.wowx-price .now{ font-size: var(--priceNow); font-weight:600; letter-spacing:-.02em; color: rgba(11,18,32,.92) }
.wowx-actions{ display:grid; grid-template-columns: 1fr 1.15fr; gap:10px }
.wowx-btn{ height: var(--btnH); border-radius: var(--btnR); font-size: var(--btnFont); font-weight:400; border:1px solid rgba(16,24,40,.22); background:#fff; color: rgba(11,18,32,.82); cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 10px 22px rgba(16,24,40,.08) }
.wowx-btn--primary{ background: linear-gradient(180deg, #1f7a4a, #17643d); border-color: rgba(0,0,0,.10); color:#fff }
@media (max-width: 768px){ .wowx-card{ border-radius:20px; width:300px } }
</style>

<a href="{{ $url }}" class="wow-card md wowx-card">
  <div class="wowx-top">
    <div class="wowx-badges">
      @if($locBadge)
        <span class="wowx-badge">{{ $locBadge }}</span>
      @endif
      @if($rating && $rating >= 4.8)
        <span class="wowx-badge">Top Rated</span>
      @endif
    </div>
  </div>
  <div class="wowx-media">
    <img src="{{ $image }}" alt="{{ $title }}" loading="lazy">
  </div>
  <div class="wowx-content">
    <div class="wowx-type">{{ $type }}</div>
    <h3 class="wowx-title">{{ $title }}</h3>
    @if($rating)
      <div class="wowx-rating">★ {{ number_format($rating,1) }} @if($reviewCount) <small class="text-muted">({{ $reviewCount }})</small>@endif</div>
    @endif
    <div class="wowx-bottom">
      @if($priceMin)
        <div class="wowx-price"><span class="from text-muted small">From</span> <span class="now">£{{ number_format((float)$priceMin, 2) }}</span></div>
      @endif
      <div class="wowx-actions">
        <span class="wowx-btn">View</span>
        <span class="wowx-btn wowx-btn--primary">Book now</span>
      </div>
    </div>
  </div>
</a>
