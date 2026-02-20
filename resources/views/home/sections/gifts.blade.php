
<section data-v-f43bb09d="" class="section">
    <div class="container-page">
        <div class="mb-6 flex items-end justify-between">
            <div>
                <div class="kicker">Thoughtful ways to nourish someone you love</div>
                <h2>Gifts that glow (under £50)</h2></div>
            <div class="flex items-center gap-2">
                <button class="hidden sm:inline-flex carousel-arrow" id="gifts-prev" aria-label="Previous">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2">
                        <path d="M15 18l-6-6 6-6"></path>
                    </svg>
                </button>
                <button class="hidden sm:inline-flex carousel-arrow" id="gifts-next" aria-label="Next">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2">
                        <path d="M9 6l6 6-6 6"></path>
                    </svg>
                </button>
                <a href="/search?tag=Gift&amp;price_max=50"
                   class="btn-wow btn-wow--outline btn-sm btn-arrow" data-loader-init="1"><span
                    class="btn-label">Find a thoughtful gift</span><span class="btn-icon-wrap"
                                                                         aria-hidden="true"><svg
                    class="btn-icon-hover" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path
                    stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 12H5m14 0-4 4m4-4-4-4"></path></svg><svg class="btn-icon-default"
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    viewBox="0 0 24 24"><path fill="none"
                                                                                              stroke="#fff"
                                                                                              stroke-linecap="round"
                                                                                              stroke-linejoin="round"
                                                                                              stroke-width="2"
                                                                                              d="M15 12l-4 4m4-4-4-4"></path></svg></span><span
                    class="btn-spinner" aria-hidden="true"><span class="spin"></span></span></a></div>
        </div>
        <div id="gifts-cards" class="flex gap-6 overflow-x-auto overflow-y-visible no-scrollbar snap-x snap-mandatory pt-2 pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 bg-transparent">
            @forelse(($giftsUnder50 ?? []) as $product)
                @include('partials.product_card', ['product' => $product])
            @empty
                <div class="text-muted">No gifts found under £50 right now. <a class="link-wow" href="/gifts">Browse all gifts</a>.</div>
            @endforelse
        </div>
    </div>
</section>

<script>
(function(){
  var list = document.getElementById('gifts-cards');
  if(!list) return;
  var DEBUG = true;
  var hasSSR = !!list.querySelector('.wow-card');
  if(DEBUG){
    try{
      var ssrCards = list.querySelectorAll('.wow-card');
      console.log('[Gifts] SSR cards count:', ssrCards.length);
      if(ssrCards.length){
        var rows = [];
        ssrCards.forEach(function(card){
          rows.push({
            title: (card.querySelector('.wow-title')?.textContent||'').trim(),
            price: (card.querySelector('.price')?.textContent||'').trim(),
            href: card.getAttribute('href')
          });
        });
        console.table(rows);
      }
    }catch(e){}
  }
  if(!hasSSR){ list.innerHTML = '<div class="text-muted">Loading gifts…</div>'; }

  function esc(s){ return String(s||'').replace(/[&<>"']/g, function(c){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c]); }); }
  function normalizePrice(v){
    var n = Number(v);
    if(!isFinite(n)) return null;
    if(n >= 1000) n = n / 100; // treat as pennies only for large values
    return n;
  }
  function render(items){
    if(hasSSR) return;
    if(!Array.isArray(items) || items.length===0){ list.innerHTML = '<div class="text-muted">No gifts found under £50 right now. <a class="link-wow" href="/gifts">Browse all gifts</a>.</div>'; return; }
    list.innerHTML = '';
    if(DEBUG){
      try {
        console.groupCollapsed('Gifts under £50 render');
        console.table(items.map(function(x){
          var p = normalizePrice(x.price_min ?? x.price);
          return { id:x.id, title:x.title, raw_price:x.price, price_min:x.price_min, norm:p, url:x.url };
        }));
        console.groupEnd();
      } catch(e) {}
    }
    items.forEach(function(it){
      var priceMin = normalizePrice(it.price_min);
      if(priceMin != null && priceMin > 50) return; // keep under £50 only
      var a = document.createElement('a');
      a.href = it.url || '#';
      a.className = 'wow-card md';
      a.innerHTML =
        '<div class="wow-media">' + (it.image ? '<img src="' + esc(it.image) + '" alt="' + esc(it.title) + '">' : '') + '</div>' +
        '<div class="wow-body">' +
          '<div class="wow-type text-muted">' + esc(it.type || 'Experience') + '</div>' +
          '<div class="wow-title">' + esc(it.title) + '</div>' +
          (it.rating ? ('<div class="rating-text">★ ' + Number(it.rating).toFixed(1) + (it.review_count ? ' <small class="text-muted">(' + it.review_count + ')</small>' : '') + '</div>') : '') +
        '</div>' +
        '<div class="wow-bottom">' +
          (priceMin != null ? ('<div class="price">£' + Number(priceMin).toFixed(2) + ' <small>from</small></div>') : '') +
          '<div class="actions"><span class="link-wow">View</span></div>' +
        '</div>';
      list.appendChild(a);
    });
  }

  if(!hasSSR){
    var url1 = '/api/products?tag=Gift&price_max=50&limit=12&sort=popular';
    if(DEBUG) try{ console.log('Gifts fetch 1:', url1); }catch(e){}
    fetch(url1, { headers: { 'Accept':'application/json' }})
      .then(function(r){ return r.json(); })
      .then(function(items){
        if(DEBUG) try{ console.log('Gifts resp 1 count:', Array.isArray(items)?items.length:items); }catch(e){}
        if(Array.isArray(items) && items.length){ render(items); return; }
        // Fallback without tag if none returned
        var url2 = '/api/products?price_max=50&limit=12&sort=popular';
        if(DEBUG) try{ console.log('Gifts fetch 2 (fallback):', url2); }catch(e){}
        return fetch(url2, { headers: { 'Accept':'application/json' }})
          .then(function(r){ return r.json(); })
          .then(function(items2){
            if(DEBUG) try{ console.log('Gifts resp 2 count:', Array.isArray(items2)?items2.length:items2); }catch(e){}
            render(items2);
          });
      })
      .catch(function(){ list.innerHTML = '<div class="text-muted">Could not load gifts.</div>'; });
  }

  // Carousel arrows
  try {
    var prev = document.getElementById('gifts-prev');
    var next = document.getElementById('gifts-next');
    function scrollByStep(dir){
      var step = Math.max(280, Math.floor(list.clientWidth * 0.9));
      list.scrollBy({ left: dir * step, behavior: 'smooth' });
    }
    if(prev){ prev.addEventListener('click', function(){ scrollByStep(-1); }); }
    if(next){ next.addEventListener('click', function(){ scrollByStep(1); }); }
  } catch(e) {}
})();
</script>
