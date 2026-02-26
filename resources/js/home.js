// Minimal interactivity for header mega menu, mobile menu, and ultra search bar panes

function initMegaMenu() {
  const header = document.querySelector('header');
  const panel = document.getElementById('mega-panel');
  const overlay = document.getElementById('mega-overlay');
  if (!header || !panel) return;
  const navLinks = header.querySelectorAll('[data-mega-menu]');
  navLinks.forEach((a) => {
    a.addEventListener('mouseenter', () => {
      const which = a.getAttribute('data-mega-menu');
      panel.setAttribute('data-active', which || '');
      panel.style.display = 'block';
      if (overlay) overlay.style.display = 'block';
    });
    a.addEventListener('focus', () => {
      const which = a.getAttribute('data-mega-menu');
      panel.setAttribute('data-active', which || '');
      panel.style.display = 'block';
      if (overlay) overlay.style.display = 'block';
    });
  });
  // Links without a mega menu should not trigger the frosted overlay.
  try {
    const simpleLinks = header.querySelectorAll('.nav-item > a:not([data-mega-menu])');
    simpleLinks.forEach((a) => {
      a.addEventListener('mouseenter', () => {
        try { panel.style.display = 'none'; } catch(_){}
        try { if (overlay) overlay.style.display = 'none'; } catch(_){}
      });
      a.addEventListener('focus', () => {
        try { panel.style.display = 'none'; } catch(_){}
        try { if (overlay) overlay.style.display = 'none'; } catch(_){}
      });
    });
  } catch (e) {}
  header.addEventListener('mouseleave', () => { panel.style.display = 'none'; if (overlay) overlay.style.display = 'none'; });
  document.addEventListener('click', (e) => {
    if (!header.contains(e.target)) { panel.style.display = 'none'; if (overlay) overlay.style.display = 'none'; }
  });
}

function initMobileMenu() {
  const toggle = document.querySelector('header button[aria-label="Toggle menu"]');
  const drawer = document.getElementById('mobile-menu');
  if (!toggle || !drawer) return;
  toggle.addEventListener('click', () => {
    const isOpen = drawer.style.display === 'block';
    const nextOpen = !isOpen;
    drawer.style.display = nextOpen ? 'block' : 'none';
    toggle.classList.toggle('opened', nextOpen);
    toggle.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
  });
}

function setupUltraSearchBar(prefix) {
  const root = document.querySelector(`#${prefix}-root`) || document;
  function byId(s){ return document.getElementById(prefix + '-' + s); }
  const panes = ['what-pane','where-pane','when-pane','who-pane'];
  function hideAll(){ panes.forEach((id) => { const el = byId(id); if (el) el.classList.add('d-none'); }); const what = byId('what'); if (what) what.setAttribute('aria-expanded','false'); }
  function openPane(which){ hideAll(); const pane = byId(which+'-pane'); if(pane){ pane.classList.remove('d-none'); } if(which==='what'){ const what = byId('what'); if(what) what.setAttribute('aria-expanded','true'); } }
  const whatInput = byId('what'); if(whatInput){ whatInput.addEventListener('focus', ()=>openPane('what')); whatInput.addEventListener('input', ()=>openPane('what')); const segWhat = byId('seg-what'); if(segWhat){ segWhat.addEventListener('click', ()=>openPane('what')); } }
  const whereEditor = byId('where-editor'); if(whereEditor){ whereEditor.addEventListener('focus', ()=>openPane('where')); whereEditor.addEventListener('click', ()=>openPane('where')); }
  const whenInput = byId('when'); if(whenInput){ whenInput.addEventListener('focus', ()=>openPane('when')); whenInput.addEventListener('click', ()=>openPane('when')); }
  const whoSeg = byId('seg-who'); if(whoSeg){ whoSeg.addEventListener('click', ()=>openPane('who')); }
  document.addEventListener('click', (e)=>{ const container = document.querySelector('.wow-ultra'); if(container && !container.contains(e.target)) hideAll(); });
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') hideAll(); });
  const whatList = byId('what-list'); if(whatList && byId('what')){ whatList.addEventListener('click', (e)=>{ const btn = e.target.closest('.item'); if(btn && btn.dataset.value){ byId('what').value = btn.dataset.value; hideAll(); byId('what').blur(); } }); }
  const whereHidden = byId('where'); if(byId('where-list') && whereEditor){ byId('where-list').addEventListener('click', (e)=>{ const btn = e.target.closest('.item'); if(btn && btn.dataset.value){ whereEditor.textContent = btn.dataset.value; if(whereHidden) whereHidden.value = btn.dataset.value; hideAll(); } }); }
  const whoDone = byId('who-done'); if(whoDone){ whoDone.addEventListener('click', ()=>hideAll()); }
}

document.addEventListener('DOMContentLoaded', () => {
  try { initMegaMenu(); } catch (e) {}
  try { initMobileMenu(); } catch (e) {}
  try { ['home-template','home-sticky'].forEach(prefix => setupUltraSearchBar(prefix)); } catch (e) {}

  // Cart dropdown: hover on desktop shows mini cart; mobile click navigates
  const wrap = document.querySelector('.cart-wrap');
  const link = document.querySelector('.cart-wrap .cart-link');
  const panel = document.getElementById('cart-dropdown');
  if (wrap && link && panel) {
    function isDesktop(){ try { return window.matchMedia('(min-width: 992px)').matches } catch(_) { return true } }
    let loaded = false; let hideTimer = null;
    function money(n){ try{ var x=Number(n); if(x>=1000) x=x/100; return '£'+x.toFixed(2) }catch(_){ return '£0.00' } }
    function updateTotals(items){
      try{
        var sub = 0, count = 0;
        (items||[]).forEach(function(it){ var p = Number(it.price||0); if(p>=1000) p=p/100; var q = Number(it.qty||1)||1; sub += p*q; count += q; });
        var subEl = panel.querySelector('#cartdd-subtotal'); if(subEl) subEl.textContent = money(sub);
        var label = panel.querySelector('#cartCountLabel'); if(label) label.textContent = count>0 ? (count===1?'1 item':(count+' items')) : '';
        var hint = panel.querySelector('#freeShipHint'); if(hint){ try{ var left = Math.max(0, 50 - sub); hint.textContent = left>0 ? ('Add '+money(left)+' more for free delivery*') : 'You\u2019ve unlocked free delivery*'; }catch(_e){} }
      }catch(_){ }
    }
    function renderItems(items){
      try{
        const body = panel.querySelector('#cartdd-body');
        if(!body) return;
        if(!Array.isArray(items) || items.length===0){ body.innerHTML = '<div class="cartdd-empty">Your cart is empty</div>'; updateTotals([]); updateBadgeFrom([]); return; }
        body.innerHTML = items.map(function(it){
          var img = it.image ? '<div class="cartdd-img"><img src="'+String(it.image).replace(/"/g,'&quot;')+'" alt=""></div>' : '<div class="cartdd-img"></div>';
          var title = String(it.title||'').replace(/[&<>"']/g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[c]));
          var qty = Number(it.qty||1);
          var price = (typeof it.price!== 'undefined') ? money(it.price) : '';
          var amt = (it.price!=null) ? money((Number(it.price)||0) * qty) : '';
          return '<div class="cartdd-item" data-id="'+String(it.id||'')+'">'+ img +'<div class="cartdd-info"><a class="cartdd-title" href="'+(it.url||'#')+'">'+title+'</a><div class="cartdd-meta">Qty '+qty+(price?(' • '+price+' each'):'')+' <button class="remove-btn js-remove" type="button" data-remove="'+String(it.id||'')+'">Remove</button></div></div><div class="cartdd-amt">'+amt+'</div></div>';
        }).join('');
        updateTotals(items);
        updateBadgeFrom(items);
      }catch(_){ /* no-op */ }
    }
    function updateBadgeFrom(items){
      try{ var b=document.querySelector('.cart-badge'); if(!b) return; var c=0; (items||[]).forEach(function(it){ c += Number(it.qty||1)||1; }); b.textContent=String(c); b.style.display=c>0?'inline-block':'none'; }catch(_){ }
    }
    function readLocalCart(){
      try{
        var raw = localStorage.getItem('wow_cart'); if(!raw) return [];
        var data = JSON.parse(raw);
        var items = data && (data.items||data.cart||data) || [];
        if(!Array.isArray(items)) return [];
        return items.map(function(x){ return { title:x.title||x.name, qty:Number(x.qty||x.quantity||1), price:x.price_min||x.price, image:x.image||x.img, url:x.url||x.href, id:x.id } });
      }catch(_){ return []; }
    }
    function writeLocalCart(items){ try{ var bag={ items: items||[] }; (items||[]).forEach(function(it){ if(it && typeof it.id!=='undefined'){ bag[String(it.id)] = it; } }); localStorage.setItem('wow_cart', JSON.stringify(bag)); }catch(_){ } }
    function removeFromLocalCart(id){ try{ var items=readLocalCart().filter(function(it){ return String(it.id)!==String(id); }); writeLocalCart(items); return items; }catch(_){ return []; } }
    function loadMini(){
      if (loaded) return; loaded = true;
      fetch('/api/cart/mini?t='+Date.now(), { headers:{ 'Accept':'text/html' }, credentials:'same-origin' })
        .then(r=>r.ok?r.text():Promise.reject())
        .then(html => {
          // Heuristic: if server returned a full page/shell, ignore and render from local cart
          var bad = !html || html.length>50000 || /<html|<head|<meta\s|<header[\s>]/i.test(html);
          if (bad) { renderItems(readLocalCart()); return; }
          // Try to extract just the mini-cart fragment if the response wrapped it
          var frag = html;
          try{
            var tmp = document.createElement('div'); tmp.innerHTML = html;
            var mini = tmp.querySelector('[data-mini-cart], .mini-cart, #mini-cart, .cartdd-body');
            if (mini) { frag = mini.innerHTML; }
          }catch(_e){}
          var body = panel.querySelector('#cartdd-body'); if(body) body.innerHTML = frag;
        })
        .catch(() => { renderItems(readLocalCart()); });
    }
    function show(){ if(!isDesktop()) return; loadMini(); panel.hidden = false; }
    function hide(){ panel.hidden = true; }
    wrap.addEventListener('mouseenter', () => { if (hideTimer) { clearTimeout(hideTimer); hideTimer=null; } show(); });
    wrap.addEventListener('mouseleave', () => { hideTimer = setTimeout(hide, 120); });
    // Prevent default on desktop clicks to keep dropdown open; on mobile it navigates
    link.addEventListener('click', (e) => { if (isDesktop()) { e.preventDefault(); show(); } });
    // Expose helpers for external triggers (e.g., add-to-cart)
    try { window.__cartDropdownShow = show; } catch(_){ }
    try { window.__cartDropdownRender = function(items){ try{ renderItems(items); panel.hidden=false; }catch(_){ } } } catch(_){ }
    // Remove handler inside dropdown
    try{
      panel.addEventListener('click', function(e){
        var btn = e.target.closest('.js-remove,[data-remove]'); if(!btn) return;
        e.preventDefault(); e.stopPropagation();
        var id = btn.getAttribute('data-remove') || btn.dataset.remove || btn.getAttribute('data-id'); if(!id) return;
        var next = removeFromLocalCart(id); renderItems(next);
        // server remove in background
        try { var token=(document.querySelector('meta[name="csrf-token"]')?.content)||decodeURIComponent((document.cookie.split('; ').find(r=>r.startsWith('XSRF-TOKEN='))||'').split('=')[1]||''); fetch('/api/cart/remove', { method:'POST', headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':token }, credentials:'same-origin', body: JSON.stringify({ id:id }) }); } catch(_){ }
      });
    }catch(_){ }
    // Upsell loader
    try{
      var upsellLoaded = false;
      var upsellPool = [];
      var upsellIndex = 0;
      var headlineIndex = 0;
      function esc(s){ return String(s||'').replace(/[&<>"']/g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[c])); }
      function ensureHeadlineEl(){
        try{
          var el = panel.querySelector('#cartdd-upsell-headline');
          if(!el){
            el = document.createElement('div');
            el.id = 'cartdd-upsell-headline';
            el.className = 'cartdd-upsell-headline';
            el.style.fontWeight = '600';
            el.style.color = 'var(--ink-700)';
            el.style.padding = '8px 12px 0';
            var subtotal = panel.querySelector('.cartdd-subtotal');
            if (subtotal && subtotal.parentNode) {
              subtotal.insertAdjacentElement('afterend', el);
            } else {
              var ups = panel.querySelector('#cartdd-upsell');
              if (ups) ups.insertAdjacentElement('afterbegin', el); else panel.appendChild(el);
            }
          }
          return el;
        }catch(_){ return null; }
      }
      function sliceAndRender(pool){
        // rotate 3 items each time
        if (!Array.isArray(pool) || !pool.length) { renderUpsell([]); return; }
        var idsInCart = (readLocalCart()||[]).map(function(it){ return String(it.id||''); });
        var filtered = pool.filter(function(p){ return idsInCart.indexOf(String(p.id||''))===-1; });
        if (!filtered.length) filtered = pool.slice();
        var start = upsellIndex % filtered.length;
        var view = [];
        for (var i=0;i<Math.min(3, filtered.length);i++) view.push(filtered[(start+i)%filtered.length]);
        upsellIndex = (upsellIndex + 3) % filtered.length;
        renderUpsell(view);
      }
      function renderUpsell(list){
        try{
          var wrapU = panel.querySelector('#cartdd-upsell'); if(!wrapU) return;
          if(!Array.isArray(list) || !list.length){ wrapU.innerHTML = ''; return; }
          wrapU.innerHTML = list.map(function(it){
            var p = Number(it.price_min ?? it.price ?? 0); if(p>=1000) p=p/100;
            var img = it.image || (it.images && it.images[0]) || '';
            var url = it.url || ('/therapies/'+it.id);
            var title = esc(it.title||'');
            return '<div class="upsell-item">'
              + (img?('<img src="'+img+'" alt="">'):'<div style="width:46px;height:46px;border-radius:8px;background:#f3f5f7;border:1px solid #eceff3"></div>')
              + '<div><p class="upsell-title">'+title+'</p><div class="upsell-price">'+money(p)+'</div></div>'
              + '<button class="btn-wow btn-wow--outline btn-sm js-add-to-cart" data-id="'+it.id+'" data-title="'+title+'" data-price="'+p.toFixed(2)+'" data-image="'+img+'" data-url="'+url+'">Add</button>'
            + '</div>';
          }).join('');
        }catch(_){ }
      }
      function deriveTypeFromCart(){
        try{
          var items = readLocalCart(); if(!items || !items.length) return '';
          var url = String(items[0].url||'');
          var m = url.match(/\/([a-z-]+)\//i); return m?m[1]:'';
        }catch(_){ return ''; }
      }
      function cartComposition(){
        try{
          var items = readLocalCart(); if(!items || !items.length) return 'unknown';
          var flags = items.map(function(it){ var t=(it.title||'')+ ' '+ (it.url||''); return /online/i.test(t); });
          var anyOnline = flags.some(Boolean); var anyOffline = flags.some(function(f){ return !f; });
          if (anyOnline && !anyOffline) return 'online-only';
          if (!anyOnline && anyOffline) return 'in-person-only';
          if (anyOnline && anyOffline) return 'mixed';
          return 'unknown';
        }catch(_){ return 'unknown'; }
      }
      function chooseHeadline(){
        var mode = cartComposition();
        var sets = {
          'online-only': [
            'Popular online picks right now',
            'Instant calm, no travel required',
            'More online favourites you’ll actually use',
            'Pair it with a quick reset',
            'Top-rated online sessions',
            'Online best-sellers this week',
            'Most booked online experiences',
            'Finish strong: add an online upgrade'
          ],
          'in-person-only': [
            'Popular near you',
            'Most booked in your area',
            'Wellness people nearby love',
            'Nearby favourites to match your booking',
            'Make a day of it',
            'Limited spots near you',
            'New in your area',
            'Top-rated nearby practitioners'
          ],
          'mixed': [
            'Complete the set: online + in-person',
            'Balance your week',
            'Before & after: prep online, go in-person',
            'Your calm, but smarter',
            'Most paired with what’s in your basket'
          ],
          'unknown': [ 'Complete your calm' ]
        };
        var list = sets[mode] || sets['unknown'];
        var text = list[ headlineIndex % list.length ];
        headlineIndex = (headlineIndex + 1) % list.length;
        return text;
      }
      function setHeadline(){ try{ var h = ensureHeadlineEl(); if(h){ h.textContent = chooseHeadline(); } }catch(_){ } }
      function loadUpsell(){ if(upsellLoaded) return; upsellLoaded=true;
        var seg = deriveTypeFromCart();
        var endpoint = seg ? ('/api/products?limit=12&sort=popular&type='+encodeURIComponent(seg)) : '/api/products?limit=12&sort=popular';
        fetch(endpoint, { headers:{ 'Accept':'application/json' }})
          .then(r=>r.json())
          .then(function(list){ upsellPool = Array.isArray(list)?list:[]; setHeadline(); sliceAndRender(upsellPool); })
          .catch(function(){ renderUpsell([]); });
      }
      // Load on first open; on subsequent opens rotate the pool and headline
      wrap.addEventListener('mouseenter', function(){ if(!upsellLoaded) loadUpsell(); else { setHeadline(); sliceAndRender(upsellPool); } });
    }catch(_){ }
  }
});
