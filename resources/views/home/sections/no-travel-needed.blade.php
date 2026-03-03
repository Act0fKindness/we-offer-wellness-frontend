<section data-v-f43bb09d="" id="comfort-section" class="section" aria-labelledby="comfort-title">
    <div data-v-f43bb09d="" class="container-page">
        <div data-v-f43bb09d="" class="mb-6">
            <div data-v-f43bb09d="" class="kicker">No travel needed</div>
            <h2 data-v-f43bb09d="" id="comfort-title">From the comfort of your own home</h2>
            <p data-v-f43bb09d="" class="text-ink-600 mt-2 max-w-2xl"> When your mind won’t slow down,
                soften into rituals that meet you where you are — gentle sessions that bring calm, clarity
                and care right to your space. </p></div>
        <div data-v-f43bb09d="" class="flex items-center justify-between gap-4 mb-4 flex-wrap">
            <div data-v-f43bb09d="" class="flex items-center gap-4 flex-wrap">
                <div data-v-f43bb09d="" class="flex items-center gap-2"><span data-v-f43bb09d=""
                                                                              class="font-semibold text-ink-800">Under</span>
                    <div data-v-f43bb09d="" class="seg-group" role="tablist" aria-label="Under price">
                        <button data-v-f43bb09d="" class="seg active" role="tab" aria-selected="true" data-price="50">£50</button>
                        <button data-v-f43bb09d="" class="seg" role="tab" aria-selected="false" data-price="100">£100</button>
                        <button data-v-f43bb09d="" class="seg" role="tab" aria-selected="false" data-price="500">£500</button>
                    </div>
                </div>
                <div data-v-f43bb09d="" class="flex items-center gap-2"><span data-v-f43bb09d=""
                                                                              class="font-semibold text-ink-800">For</span>
                    <div data-v-f43bb09d="" class="seg-group" role="tablist" aria-label="For">
                        <button data-v-f43bb09d="" class="seg active" role="tab" aria-selected="true" data-group="solo">Solo</button>
                        <button data-v-f43bb09d="" class="seg" role="tab" aria-selected="false" data-group="couple">Couple</button>
                    </div>
                </div>
            </div>
            <div data-v-f43bb09d="" class="hidden sm:flex items-center gap-2 ml-auto">
                <button data-v-f43bb09d="" id="comfort-prev" class="carousel-arrow" aria-label="Previous">
                    <svg data-v-f43bb09d="" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2">
                        <path data-v-f43bb09d="" d="M15 18l-6-6 6-6"></path>
                    </svg>
                </button>
                <button data-v-f43bb09d="" id="comfort-next" class="carousel-arrow" aria-label="Next">
                    <svg data-v-f43bb09d="" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2">
                        <path data-v-f43bb09d="" d="M9 6l6 6-6 6"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div data-v-f43bb09d="">
            <div data-v-f43bb09d="" id="comfort-cards"
                 class="flex gap-6 overflow-x-auto overflow-y-visible no-scrollbar snap-x snap-mandatory pt-2 pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 bg-transparent">
                @forelse(($onlineUnder50 ?? []) as $product)
                    @include('partials.product_card', ['product' => $product])
                @empty
                    <div class="text-muted">No online options under £50 right now. <a class="link-wow" href="/search?price_max=50&amp;format=online">See all under £50</a>.</div>
                @endforelse
            </div>
            <div data-v-f43bb09d="" class="mt-4 text-right"><a data-v-f43bb09d="" id="comfort-cta"
                                                               href="/search?price_max=50&amp;format=online"
                                                               class="btn-wow btn-wow--outline btn-sm btn-arrow"
                                                               data-loader-init="1"><span data-v-f43bb09d=""
                                                                                          class="btn-label">See all under £50 (solo)</span><span
                 data-v-f43bb09d="" class="btn-icon-wrap" aria-hidden="true"><svg data-v-f43bb09d=""
                                                                                 class="btn-icon-hover"
                                                                                 xmlns="http://www.w3.org/2000/svg"
                                                                                 viewBox="0 0 24 24"><path
                 data-v-f43bb09d="" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                 stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"></path></svg><svg data-v-f43bb09d=""
                                                                                 class="btn-icon-default"
                                                                                 xmlns="http://www.w3.org/2000/svg"
                                                                                 viewBox="0 0 24 24"><path
                 data-v-f43bb09d="" fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round"
                 stroke-width="2" d="M15 12l-4 4m4-4-4-4"></path></svg></span><span data-v-f43bb09d=""
                                                                                   class="btn-spinner"
                                                                                   aria-hidden="true"><span
                data-v-f43bb09d="" class="spin"></span></span></a></div>
        </div>
    </div>
</section>

<script>
(function(){
  var root = document.getElementById('comfort-section');
  if(!root) return;
  var cardsEl = root.querySelector('#comfort-cards');
  var cta = root.querySelector('#comfort-cta');
  var ctaLabel = cta ? cta.querySelector('.btn-label') : null;
  var price = 50;
  var group = 'solo';
  var DEBUG = true;
  var cache = Object.create(null);
  var ssrHTML = cardsEl ? cardsEl.innerHTML : '';
  var ssrHasCards = !!(cardsEl && cardsEl.querySelector('.wow-card'));
  if(DEBUG){
    try{
      var ssrCards = cardsEl ? cardsEl.querySelectorAll('.wow-card') : [];
      console.log('[Comfort] SSR cards count:', ssrCards.length);
      if(ssrCards && ssrCards.length){
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

  function setActive(btn){
    var groupEl = btn.closest('.seg-group');
    if(!groupEl) return;
    groupEl.querySelectorAll('.seg').forEach(function(b){ b.classList.remove('active'); b.setAttribute('aria-selected','false'); });
    btn.classList.add('active');
    btn.setAttribute('aria-selected','true');
  }

  function esc(s){ return String(s||'').replace(/[&<>"']/g, function(c){ return ({'&':'&amp;','<':'&lt;','>':'&gt','"':'&quot;','\'':'&#39;'}[c]); }); }

  function updateCta(){
    if(!cta) return;
    var href = '/search?price_max=' + encodeURIComponent(price) + '&format=online' + (group==='couple' ? '&q=couples' : '');
    cta.href = href;
    if(ctaLabel){ ctaLabel.textContent = 'See all under £' + price + ' (' + group + ')'; }
  }

  function normalizePrice(v){ var n = Number(v); if(!isFinite(n)) return null; if(n >= 1000) n = n/100; return n; }
  function buildStarsHTML(r){
    var full = Math.floor(Number(r)||0);
    var out = '';
    for(var i=1;i<=5;i++){
      out += (i<=full)
        ? '<span class="star" style="color:#f5c84b;"></span>'
        : '<span class="star star--empty"></span>';
    }
    return out;
  }

  function renderCardsHTML(html){
    if(!cardsEl) return;
    if(typeof html !== 'string' || html.trim()===''){
      cardsEl.innerHTML = '<div class="text-muted">No options found. <a class="link-wow" href="' + cta.href + '">See all</a>.</div>';
      return;
    }
    cardsEl.innerHTML = html;
    try { cache[String(price)] = html; } catch(e){}
  }

  function fetchAndRender(){
    if(!cardsEl) return;
    cardsEl.innerHTML = '<div class="text-muted">Loading…</div>';
    var url = '/api/product-cards?mode=online&price_max=' + encodeURIComponent(price) + '&limit=12&sort=popular';
    if(DEBUG) try{ console.log('Comfort fetch:', url); }catch(e){}
    fetch(url, { headers: { 'Accept': 'text/html' }}).then(function(r){ return r.text(); }).then(function(html){
      renderCardsHTML(html);
    }).catch(function(){ cardsEl.innerHTML = '<div class="text-muted">Could not load options. Try again.</div>'; });
  }

  // Event bindings
  root.querySelectorAll('[data-price]').forEach(function(btn){
    btn.addEventListener('click', function(){
      price = parseInt(btn.getAttribute('data-price'), 10) || 50;
      setActive(btn);
      updateCta();
      // If £50 and SSR exists, restore SSR markup for consistency
      if(price === 50 && ssrHasCards){
        cardsEl.innerHTML = ssrHTML;
        if(DEBUG){ try{ console.log('[Comfort] Restored SSR for £50'); }catch(e){} }
        return;
      }
      // Use cache if available
      var cached = cache[String(price)];
      if(typeof cached === 'string' && cached){ renderCardsHTML(cached); return; }
      fetchAndRender();
    });
  });
  root.querySelectorAll('[data-group]').forEach(function(btn){
    btn.addEventListener('click', function(){
      group = btn.getAttribute('data-group') || 'solo';
      setActive(btn);
      updateCta();
      // Group selection does not change the listing; only the CTA
    });
  });

  // Initialize CTA on load
  updateCta();

  // Carousel controls
  try {
    var prev = root.querySelector('#comfort-prev');
    var next = root.querySelector('#comfort-next');
    function scrollByStep(dir){
      if(!cardsEl) return;
      var step = Math.max(280, Math.floor(cardsEl.clientWidth * 0.9));
      cardsEl.scrollBy({ left: dir * step, behavior: 'smooth' });
    }
    if(prev){ prev.addEventListener('click', function(){ scrollByStep(-1); }); }
    if(next){ next.addEventListener('click', function(){ scrollByStep(1); }); }
  } catch(e) {}
})();
</script>
