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

    $locations = $product->getLocations();
    $isOnline = in_array('Online', $locations, true);
    $physical = array_values(array_filter($locations, fn($l) => $l !== 'Online'));
    $mode = $isOnline && count($physical) === 0 ? 'Online' : (count($physical) ? 'In-person' : null);
    $locBadge = $mode ?: ($category ?: null);

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
    $locationLabel = ($isOnline && count($physical)===0) ? 'Online' : (count($physical) ? ($physical[0] ?? null) : null);
    $nextLabel = $product->next_label ?? $product->next ?? null;
    $benefitText = $product->benefit ?? ($product->summary ?? null);
    $fomoText = $product->fomo_text ?? null;
    $compareMin = $product->variants_min_compare ?? ($product->compare_at_price ?? null);
    if (is_numeric($compareMin) && $compareMin > 1000 && $compareMin % 100 === 0) { $compareMin = $compareMin / 100; }
@endphp

@once
  <style>
  /* Scoped EXACT card styles (prefixed with .wow-therapy-card-scope to avoid global bleed) */
  .wow-therapy-card-scope{ --bg:#f4f5f7; --card:#fff; --shadow:0 18px 55px rgba(16,24,40,.10); --text:#0b1220; --muted:rgba(11,18,32,.62); --wowGreen:#1f7a4a; --wowGreenDark:#17643d; --badgeWarmBg:#ffe7c2; --badgeWarmText:#6b4b12; --badgeCoolBg:#dfe9ff; --badgeCoolText:#1f3a77; --container:1280px; --gap:16px; --radius:7px; --borderW:2px; --imgH:150px; --padTop:15px; --padX:15px; --padContentX:15px; --padContentY:15px; --badgeH:32px; --badgePx:10px; --badgeFont:12px; --badgeIcon:18px; --save:34px; --saveR:3px; --saveIcon:18px; --title:22px; --provider:13px; --rating:13px; --star:18px; --meta:12px; --metaIcon:14px; --benefit:12px; --fomo:12px; --from:13px; --priceNow:22px; --was:13px; --btnH:38px; --btnR:4px; --btnFont:16px; --cardH:580px; }
  .wow-therapy-card-scope .therapy-card{ background: var(--card); border: var(--borderW) solid rgba(16,24,40,.18); border-radius: var(--radius); box-shadow: var(--shadow); overflow:hidden; position:relative; min-width:0; height: var(--cardH); display:flex; flex-direction:column }
  .wow-therapy-card-scope .card-top{ display:flex; align-items:center; justify-content:space-between; gap:10px; padding: var(--padTop) var(--padX) 10px; background:#fff; flex:0 0 auto }
  .wow-therapy-card-scope .card-top-left{ flex:1 1 auto; min-width:0; display:flex; align-items:center; gap:10px; overflow:hidden }
  .wow-therapy-card-scope .badges{ flex:1 1 auto; min-width:0; display:flex; align-items:center; gap:8px; flex-wrap:nowrap; white-space:nowrap; overflow:hidden }
  .wow-therapy-card-scope .badge{ height: var(--badgeH); display:inline-flex; align-items:center; gap:4px; padding:0 var(--badgePx); border-radius:3px; border:1px solid rgba(16,24,40,.10); font-weight:600; font-size: var(--badgeFont); line-height:1; white-space:nowrap; flex:0 0 auto }
  .wow-therapy-card-scope .badge--warm{ background: var(--badgeWarmBg); color: var(--badgeWarmText) }
  .wow-therapy-card-scope .badge--cool{ background: var(--badgeCoolBg); color: var(--badgeCoolText) }
  .wow-therapy-card-scope .badge svg{ width: var(--badgeIcon); height: var(--badgeIcon) }
  .wow-therapy-card-scope .save{ width: var(--save); height: var(--save); border-radius: var(--saveR); border:1px solid rgba(16,24,40,.20); background:#fff; display:grid; place-items:center; cursor:pointer; box-shadow:0 10px 22px rgba(16,24,40,.08); transition: transform .12s ease, box-shadow .12s ease, background .12s ease, border-color .12s ease; flex:0 0 auto }
  .wow-therapy-card-scope .save:hover{ transform: translateY(-1px); box-shadow:0 14px 28px rgba(16,24,40,.12) }
  .wow-therapy-card-scope .save:active{ transform: translateY(0) scale(.99) }
  .wow-therapy-card-scope .save svg{ width: var(--saveIcon); height: var(--saveIcon); color: rgba(11,18,32,.72) }
  .wow-therapy-card-scope .media{ height: var(--imgH); overflow:hidden; flex:0 0 auto; padding:0 15px; border-radius:3px }
  .wow-therapy-card-scope .media img{ width:100%; height:100%; object-fit:cover; display:block; border-radius:2px }
  .wow-therapy-card-scope .content{ padding: 0px; flex:1 1 auto; display:flex; flex-direction:column; min-height:0 }
  .wow-therapy-card-scope .content-top{ flex:1 1 auto; padding: var(--padContentY) var(--padContentX); min-height:0; overflow:hidden }
  .wow-therapy-card-scope .title{ margin:0 0 6px; font-size: var(--title); font-weight:400 !important; line-height:1.12; margin-top:10px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; font-family: 'Manrope', var(--bs-font-sans-serif) !important; text-transform: capitalize }
  .wow-therapy-card-scope .provider{ margin:0 0 8px; color: var(--muted); font-size: var(--provider); font-weight:400 }
  .wow-therapy-card-scope .rating-row{ display:flex; align-items:center; gap:8px; margin-bottom:10px; color: rgba(11,18,32,.80); font-weight:400; font-size: var(--rating) }
  .wow-therapy-card-scope .stars{ display:inline-flex; align-items:center; gap:3px; transform: translateY(1px) }
  .wow-therapy-card-scope .star{ width: var(--star); height: var(--star); display:inline-block; position:relative; background: currentColor; -webkit-mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20fill%3D%27%23000%27%20d%3D%27M11.083%205.104c.35-.8%201.485-.8%201.834%200l1.752%204.022a1%201%200%200%200%20.84.597l4.463.342c.9.069%201.255%201.2.556%201.771l-3.33%202.723a1%201%200%200%200-.337%201.016l1.03%204.119c.214.858-.71%201.552-1.474%201.106l-3.913-2.281a1%201%200%200%200-1.008%200L7.583%2020.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1%201%200%200%200%206.8%2014.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1%201%200%200%20.84-.597l1.753-4.022Z%27%2F%3E%3C%2Fsvg%3E") center/contain no-repeat; mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20fill%3D%27%23000%27%20d%3D%27M11.083%205.104c.35-.8%201.485-.8%201.834%200l1.752%204.022a1%201%200%200%200%20.84.597l4.463.342c.9.069%201.255%201.2.556%201.771l-3.33%202.723a1%201%200%200%200-.337%201.016l1.03%204.119c.214.858-.71%201.552-1.474%201.106l-3.913-2.281a1%201%200%200%200-1.008%200L7.583%2020.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1%201%200%200%200%206.8%2014.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1%201%200%200%20.84-.597l1.753-4.022Z%27%2F%3E%3C%2Fsvg%3E") center/contain no-repeat }
  .wow-therapy-card-scope .star::after{ content:""; position:absolute; inset:0; background:#333; pointer-events:none; -webkit-mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20fill%3D%27none%27%20stroke%3D%27%23000%27%20stroke-width%3D%272%27%20stroke-linejoin%3D%27round%27%20stroke-linecap%3D%27round%27%20d%3D%27M11.083%205.104c.35-.8%201.485-.8%201.834%200l1.752%204.022a1%201%200%200%200%20.84.597l4.463.342c.9.069%201.255%201.2.556%201.771l-3.33%202.723a1%201%200%200%200-.337%201.016l1.03%204.119c.214.858-.71%201.552-1.474%201.106l-3.913-2.281a1%201%200%200%200-1.008%200L7.583%2020.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1%201%200%200%200%206.8%2014.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1%201%200%200%20.84-.597l1.753-4.022Z%27%2F%3E%3C%2Fsvg%3E") center/contain no-repeat; mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20fill%3D%27none%27%20stroke%3D%27%23000%27%20stroke-width%3D%272%27%20stroke-linejoin%3D%27round%27%20stroke-linecap%3D%27round%27%20d%3D%27M11.083%205.104c.35-.8%201.485-.8%201.834%200l1.752%204.022a1%201%200%200%200%20.84.597l4.463.342c.9.069%201.255%201.2.556%201.771l-3.33%202.723a1%201%200%200%200-.337%201.016l1.03%204.119c.214.858-.71%201.552-1.474%201.106l-3.913-2.281a1%201%200%200%200-1.008%200L7.583%2020.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1%201%200%200%200%206.8%2014.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1%201%200%200%20.84-.597l1.753-4.022Z%27%2F%3E%3C%2Fsvg%3E") center/contain no-repeat }
  .wow-therapy-card-scope .meta{ display:flex; align-items:center; flex-wrap:wrap; gap:8px 10px; margin-bottom:8px; color: rgba(11,18,32,.62); font-size: var(--meta); font-weight:400 }
  .wow-therapy-card-scope .meta .item{ display:flex; align-items:center; gap:6px; white-space:nowrap }
  .wow-therapy-card-scope .content-bottom{ flex:0 0 auto; margin-top:auto; padding:15px; border-top:1px solid rgba(16,24,40,.10); background:#fff }
  .wow-therapy-card-scope .price{ display:flex; align-items:baseline; gap:8px; margin:0 0 12px }
  .wow-therapy-card-scope .price .from{ font-size: var(--from); font-weight:400; color: rgba(11,18,32,.70) }
  .wow-therapy-card-scope .price .now{ font-size: var(--priceNow); font-weight:600; letter-spacing:-.02em; color: rgba(11,18,32,.92) }
  .wow-therapy-card-scope .price .was{ font-size: var(--was); font-weight:400; color: rgba(11,18,32,.75) }
  .wow-therapy-card-scope .actions{ display:grid; grid-template-columns: 1fr 1.15fr; gap:10px }
  .wow-therapy-card-scope .btn{ height: var(--btnH); border-radius: var(--btnR); font-size: var(--btnFont); font-weight:400; border:1px solid rgba(16,24,40,.22); background:#fff; color: rgba(11,18,32,.82); cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow: 0 10px 22px rgba(16,24,40,.08) }
  .wow-therapy-card-scope .btn--primary{ background: linear-gradient(180deg, var(--wowGreen), var(--wowGreenDark)); border-color: rgba(0,0,0,.10); color:#fff }
  @media (max-width: 768px){ .wow-therapy-card-scope .therapy-card{ border-radius:20px; width:300px } }
  </style>
@endonce

<div class="wow-therapy-card-scope">
  <a href="{{ $url }}" class="wow-card md">
    <article class="therapy-card" aria-label="Therapy card {{ $product->id }}">
      <header class="card-top">
        <div class="card-top-left">
          <div class="badges">
            <span class="badge badge--warm">Filling Fast</span>
            <span class="badge badge--cool">Top Rated</span>
          </div>
        </div>
        <button class="save" type="button" aria-label="Save" aria-pressed="false" title="Save">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.01 6.001C6.5 1 1 8 5.782 13.001L12.011 20l6.23-7C23 8 17.5 1 12.01 6.002Z"/></svg>
        </button>
      </header>

      <div class="media">
        <img src="{{ $image }}" alt="{{ $title }}" loading="lazy" />
</div>

@once
  <script>
  (function(){
    function addToLocalCart(item){
      try{
        var key='wow_cart_v1';
        var raw=localStorage.getItem(key);
        var obj; try{ obj=raw?JSON.parse(raw):null }catch(e){ obj=null }
        if(!obj||!Array.isArray(obj.items)) obj={items:[]};
        var id=String(item.id||''); if(!id) return;
        var existing=obj.items.find(function(it){ return String(it.id)===id });
        if(existing){ existing.qty=(Number(existing.qty)||1)+1; }
        else {
          obj.items.push({ id:id, title:item.title||'', price:Number(item.price)||0, image:item.image||null, url:item.url||('#/products/'+id), qty:1, meta:{} });
        }
        localStorage.setItem(key, JSON.stringify(obj));
      }catch(e){}
    }
    function handleClick(e){
      var btn = e.target.closest('.js-add-to-cart'); if(!btn) return;
      e.preventDefault(); e.stopPropagation();
      var item={ id: btn.dataset.id, title: btn.dataset.title, price: btn.dataset.price, image: btn.dataset.image, url: btn.dataset.url };
      addToLocalCart(item);
      try{ window.dispatchEvent(new CustomEvent('wow:add-to-cart', { detail:{ id: item.id } })); }catch(e){}
      // Optional tiny feedback
      try{ btn.textContent='Added'; setTimeout(function(){ btn.textContent='Add to cart'; }, 1200) }catch(e){}
    }
    document.addEventListener('click', handleClick);
  })();
  </script>
@endonce

      <div class="content">
        <div class="content-top">
          <h2 class="title">{{ $titleFormatted }}</h2>
          @if($providerFormatted)<p class="provider">with {{ $providerFormatted }}</p>@endif
          @if($reviewCount)
            <div class="rating-row"><span>({{ $reviewCount }})</span></div>
          @endif
          <div class="meta">
            @if($durationLabel)<span class="item">{{ $durationLabel }}</span>@endif
            @if($locationLabel)<span class="item">{{ $locationLabel }}</span>@endif
            @if($nextLabel)<span class="item">Next: {{ $nextLabel }}</span>@endif
          </div>

        </div>
        <div class="content-bottom">
          <p class="fomo">Filling fast — only 2 slots left this week</p>
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
            <button type="button" class="btn js-add-to-cart"
              data-id="{{ $product->id }}"
              data-title="{{ e($titleFormatted) }}"
              data-price="{{ is_numeric($priceMin) ? number_format((float)$priceMin, 2, '.', '') : '0' }}"
              data-image="{{ $image }}"
              data-url="{{ $url }}"
            >Add to cart</button>
            <span class="btn btn--primary">Book now</span>
          </div>
        </div>
      </div>
    </article>
  </a>
</div>
