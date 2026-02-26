@extends('layouts.app')

@section('content')
@php
  $items = session('cart.items', []);
  $serverCart = [];
  foreach (($items ?? []) as $id => $it) {
      $p = (float)($it['price'] ?? 0);
      if ($p >= 1000) $p = $p / 100;
      $serverCart[] = [
          'id'    => (string)$id,
          'title' => (string)($it['title'] ?? 'Item'),
          'url'   => (string)($it['url'] ?? '#'),
          'img'   => (string)($it['image'] ?? ''),
          'unit'  => round($p, 2),
          'qty'   => (int)($it['qty'] ?? 1),
      ];
  }
@endphp

<section class="section">
  <div class="container-page">
    <div class="kicker">Your cart</div>
    <h1 class="mb-1">Ready to book</h1>
    <p class="lead-cart">Secure, safe and flexible — you can reschedule when needed.</p>

    <div id="cartGrid" class="cart-grid {{ empty($serverCart) ? 'is-empty' : '' }}">
      <div class="cart-main card glass" id="cartMain">
        <div class="cart-head" id="cartHead">
          <div>Item</div><div>Qty</div><div>Price</div>
        </div>
        <div class="cart-body" id="cartBody"></div>
        <div class="cart-foot" id="cartFoot">
          <a href="/search" class="link-wow">Continue shopping</a>
          <button class="btn-wow btn-wow--outline" id="clearCartBtn" type="button">Clear cart</button>
        </div>
      </div>

      <aside class="cart-side card glass" id="checkout">
        <div class="sum-head" id="sumHeadTitle">Your cart</div>
        <div class="sum-body">
          <div class="panel" id="summaryWrap" style="{{ empty($serverCart) ? 'display:none' : '' }}">
            <div class="sum-row"><span>Subtotal</span><strong id="sum-subtotal">£0.00</strong></div>
            <div class="sum-row"><span>Discounts</span><strong id="sum-discount">-£0.00</strong></div>
            <div class="sum-row muted"><span>Taxes</span><span>Included where applicable</span></div>
            <div class="sum-sep"></div>
            <div class="sum-row total"><span>Total</span><strong id="sum-total">£0.00</strong></div>

            <div class="promo">
              <label for="promo-code">Promo code</label>
              <div class="promo-inline">
                <input id="promo-code" type="text" placeholder="Enter code" value="{{ session('cart_promo_code') }}">
                <button type="button" class="btn-wow btn-wow--outline" id="apply-promo">Apply</button>
              </div>
              <div id="promo-msg" class="promo-msg"></div>
            </div>

            <button class="btn-wow btn-wow--cta w-100" id="checkoutBtn" disabled>Continue to checkout</button>
            <div class="trust-hints">
              <div class="hint"><span class="dot"></span>Secure checkout</div>
              <div class="hint"><span class="dot"></span>Free reschedule window</div>
            </div>

            <div class="upsell" id="upsellFull">
              <div class="upsell-head">
                <strong>Complete your calm</strong>
                <small>Frequently added</small>
              </div>
              <div class="upsell-list" id="upsellListFull"></div>
            </div>
          </div>

          <div class="panel empty-wrap" id="emptyWrap" style="{{ empty($serverCart) ? '' : 'display:none' }}">
            <div class="empty-hero">
              <div class="empty-illu" aria-hidden="true">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                  <path d="M7 8V7a5 5 0 0 1 10 0v1" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                  <path d="M6.5 8h11l1 12.5a2 2 0 0 1-2 2H7.5a2 2 0 0 1-2-2L6.5 8Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"></path>
                </svg>
              </div>
              <div>
                <h2>Your cart is empty</h2>
                <p>Add a therapy and you’re good to go.</p>
                <div class="empty-actions">
                  <a class="btn-wow btn-wow--cta" href="/search">Browse therapies</a>
                </div>
              </div>
            </div>

            <div class="upsell" id="upsellEmpty">
              <div class="upsell-head">
                <strong>Recommended for you</strong>
                <small>Quick add</small>
              </div>
              <div class="upsell-list" id="upsellListEmpty"></div>
            </div>
          </div>
        </div>
      </aside>
    </div>
  </div>
</section>

<script>
(function(){
  function cookie(name){
    try{
      var match = document.cookie.split(';').map(function(row){ return row.trim(); }).find(function(row){ return row.startsWith(name+'='); });
      return match ? match.slice(name.length + 1) : '';
    }catch(e){
      return '';
    }
  }
  function csrfToken(){
    try {
      return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.__csrfToken || ''
    } catch(_){ return window.__csrfToken || '' }
  }
  function post(url, data){
    var token = csrfToken();
    return fetch(url, {
      method:'POST',
      headers:{ 'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':token },
      body: JSON.stringify(data||{}),
      credentials:'same-origin'
    }).then(r=>r.json());
  }
  function money(n){ try{ return '£'+Number(n||0).toFixed(2) }catch(_){ return '£0.00' } }
  function readLocalCart(){
    try{
      var raw = localStorage.getItem('wow_cart');
      if (raw){
        var data = JSON.parse(raw);
        var items = (data && (data.items||data.cart||data)) || [];
        if (Array.isArray(items)) return items.map(function(x){ return { id:String(x.id), title:x.title||x.name, url:x.url||x.href||'#', img:x.image||x.img||'', unit: ((Number(x.price_min ?? x.price ?? 0)>=1000)? Number(x.price_min ?? x.price ?? 0)/100 : Number(x.price_min ?? x.price ?? 0)), qty: Number(x.qty||x.quantity||1) } });
      }
      var c = decodeURIComponent(cookie('wow_cart')||'');
      if (c){ var obj = JSON.parse(c); var arr = Array.isArray(obj) ? obj : Object.values(obj||{}); return (arr||[]).filter(Boolean).map(function(x){ var p=Number(x.price||0); if(p>=1000) p=p/100; return { id:String(x.id), title:x.title||'Item', url:x.url||'#', img:x.image||'', unit:p, qty:Number(x.qty||1) } }); }
    }catch(_){ }
    return [];
  }

  var cart = readLocalCart();
  try{ if(!cart.length){ cart = @json($serverCart) } }catch(_){ }
  var promo = { code:"", pct:0 };

  var grid = document.getElementById('cartGrid');
  var cartBody = document.getElementById('cartBody');
  var cartHead = document.getElementById('cartHead');
  var cartFoot = document.getElementById('cartFoot');
  var sumHeadTitle = document.getElementById('sumHeadTitle');
  var sumSubtotal = document.getElementById('sum-subtotal');
  var sumDiscount = document.getElementById('sum-discount');
  var sumTotal = document.getElementById('sum-total');
  var upsellListFull = document.getElementById('upsellListFull');
  var upsellListEmpty = document.getElementById('upsellListEmpty');
  var upsellLoaded = false; var upsellPool = [];

  function subtotal(){ return cart.reduce(function(s,it){ return s + (Number(it.unit||0) * Number(it.qty||1)); }, 0); }
  function discountAmount(){ return subtotal() * (promo.pct||0); }
  function total(){ return Math.max(0, subtotal() - discountAmount()); }

  function writeLocalFromCart(){
    try{
      var items = (cart||[]).map(function(it){ return { id:String(it.id), title:String(it.title||''), qty:Number(it.qty||1)||1, price:Number(it.unit||0), image:it.img||'', url:it.url||'#' }; });
      // LocalStorage schema (array + keyed for convenience)
      var bag = { items: items };
      items.forEach(function(it){ bag[String(it.id)] = it; });
      localStorage.setItem('wow_cart', JSON.stringify(bag));
      // Mirror cookie used by server to restore cart between requests
      var cookieObj = {};
      items.forEach(function(it){ cookieObj[String(it.id)] = { id: it.id, title: it.title, price: it.price, qty: it.qty, image: it.image, url: it.url }; });
      try{ document.cookie = 'wow_cart='+encodeURIComponent(JSON.stringify(cookieObj))+'; Path=/; Max-Age='+(60*60*24*30)+'; SameSite=Lax'; }catch(_){ }
      try { window.dispatchEvent(new CustomEvent('wow:cart:change', { detail:{ items: items, source:'cart:page' } })); } catch(_){ }
    }catch(_){ }
  }

  function serializeCartForCheckout(){
    var map = {};
    try{
      cart.forEach(function(it){
        var id = (it && it.id != null) ? String(it.id) : '';
        if(!id) return;
        map[id] = {
          id: it.id,
          title: it.title || '',
          price: Number(it.unit || it.price || 0),
          qty: Number(it.qty || 1) || 1,
          image: it.img || it.image || '',
          url: it.url || '#'
        };
      });
    }catch(_){ }
    return map;
  }

  function renderSummary(){
    sumSubtotal.textContent = money(subtotal());
    sumDiscount.textContent = '-' + money(discountAmount());
    sumTotal.textContent = money(total());
    var checkoutBtn = document.getElementById('checkoutBtn');
    checkoutBtn.disabled = cart.length === 0;
    checkoutBtn.style.opacity = cart.length === 0 ? '.55' : '1';
    checkoutBtn.style.cursor = cart.length === 0 ? 'not-allowed' : 'pointer';
  }
  function escapeHtml(str){ return String(str||"").replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'",'&#039;'); }
  function renderUpsellTarget(target, list){
    if(!target) return;
    if(!Array.isArray(list) || !list.length){ target.innerHTML = ''; return; }
    target.innerHTML = list.map(function(it){
      var p = Number(it.price_min ?? it.price ?? 0); if(p>=1000) p=p/100;
      var img = it.image || (it.images && it.images[0]) || '';
      var url = it.url || ('/therapies/'+it.id);
      var title = escapeHtml(it.title||'');
      return '<div class="upsell-item" data-upsell="'+it.id+'">'
        + (img?('<img src="'+img+'" alt="">'):'<div style="width:52px;height:52px;border-radius:14px;background:#f3f5f7;border:1px solid #eceff3"></div>')
        + '<div><p class="upsell-title">'+title+'</p><div class="upsell-price">'+money(p)+'</div></div>'
        + '<button class="upsell-add" type="button" data-add="'+it.id+'">Add</button>'
      + '</div>';
    }).join('');
  }

  function renderUpsells(){
    if(!upsellLoaded) return;
    var list = Array.isArray(upsellPool)?upsellPool.slice():[];
    renderUpsellTarget(upsellListFull, list.slice(0,3));
    renderUpsellTarget(upsellListEmpty, list.slice(3,6).length?list.slice(3,6):list.slice(0,3));
  }

  function deriveSegFromCart(){
    try{ if(!cart || !cart.length) return ''; var url = String(cart[0].url||''); var m=url.match(/\/([a-z-]+)\//i); return m?m[1]:''; }catch(_){ return ''; }
  }

  function loadUpsell(){
    if(upsellLoaded) return; upsellLoaded=true;
    var seg = deriveSegFromCart();
    var endpoint = seg ? ('/api/products?limit=12&sort=popular&type='+encodeURIComponent(seg)) : '/api/products?limit=12&sort=popular';
    fetch(endpoint, { headers:{ 'Accept':'application/json' }})
      .then(function(r){ return r.json(); })
      .then(function(list){ upsellPool = Array.isArray(list)?list:[]; renderUpsells(); })
      .catch(function(){ upsellPool=[]; renderUpsells(); });
  }

  function renderCart(){
    var isEmpty = cart.length===0;
    grid.classList.toggle('is-empty', isEmpty);
    cartHead.classList.toggle('hidden', isEmpty);
    cartFoot.classList.toggle('hidden', isEmpty);
    cartBody.classList.toggle('hidden', isEmpty);
    document.getElementById('summaryWrap').style.display = isEmpty ? 'none' : '';
    document.getElementById('emptyWrap').style.display = isEmpty ? '' : 'none';
    sumHeadTitle.textContent = isEmpty ? 'Your cart' : 'Order summary';
    if (isEmpty){ cartBody.innerHTML = ''; renderSummary(); return; }
    cartBody.innerHTML = cart.map(function(it){ var line = Number(it.unit||0) * Number(it.qty||1); return (
      '<div class="cart-row" data-id="'+escapeHtml(String(it.id))+'">'
      + '<div class="cart-item">'
        + '<a class="cart-img" href="'+escapeHtml(it.url||'#')+'">'+(it.img?('<img src="'+escapeHtml(it.img)+'" alt="">'):'')+'</a>'
        + '<div class="cart-info">'
          + '<a class="title" href="'+escapeHtml(it.url||'#')+'">'+escapeHtml(it.title||'')+'</a>'
          + '<div class="meta">'
            + '<span class="pill"><span class="dot"></span>Unit: '+money(it.unit)+'</span>'
            + '<span class="pill">Line: '+money(line)+'</span>'
          + '</div>'
          + '<button class="cart-remove" type="button" data-remove="'+escapeHtml(String(it.id))+'" aria-label="Remove">Remove</button>'
        + '</div>'
      + '</div>'
      + '<div class="cart-qty">'
        + '<div class="qty">'
          + '<button type="button" class="js-qdec" aria-label="Decrease">−</button>'
          + '<input type="number" class="qty-input" min="1" value="'+Number(it.qty||1)+'">'
          + '<button type="button" class="js-qinc" aria-label="Increase">+</button>'
        + '</div>'
      + '</div>'
      + '<div class="cart-amt">'+money(line)+'</div>'
    + '</div>' ); }).join('');
    renderSummary();
    renderUpsells();
    writeLocalFromCart();
  }

  // Listen for cart changes from header dropdown/minicart and other add-to-cart actions
  try{
    var suppressChange = false;
    function safeRender(){ suppressChange = true; try{ renderCart(); } finally { suppressChange = false; } }
    window.addEventListener('wow:cart:change', function(e){ if (suppressChange) return; try { cart = readLocalCart(); safeRender(); } catch(_){ } });
  }catch(_){ }

  document.addEventListener('click', function(e){
    var row = e.target.closest('.cart-row');
    if(row && (e.target.closest('.js-qinc') || e.target.closest('.js-qdec'))){ var id=row.getAttribute('data-id'); var item=cart.find(function(x){return String(x.id)===String(id)}); if(!item) return; item.qty=Math.max(1,Number(item.qty||1)+(e.target.closest('.js-qinc')?1:-1)); suppressChange=true; renderCart(); suppressChange=false; post('/api/cart/update',{id:id,qty:item.qty}); return; }
    var rem = e.target.closest('[data-remove]');
    if(rem){ var id=rem.getAttribute('data-remove'); cart=cart.filter(function(x){return String(x.id)!==String(id)}); suppressChange=true; renderCart(); suppressChange=false; post('/api/cart/remove',{id:id}); return; }
    if(e.target && e.target.id==='clearCartBtn'){
      cart = [];
      suppressChange=true; renderCart(); suppressChange=false;
      // Update server (best-effort)
      post('/api/cart/clear',{}).catch(function(_){});
      // Also clear cookie immediately so refresh reflects empty state
      try{ document.cookie = 'wow_cart='+encodeURIComponent(JSON.stringify({}))+'; Path=/; Max-Age='+(60*60*24*30)+'; SameSite=Lax'; }catch(_){ }
      return;
    }
    if(e.target && e.target.id==='apply-promo'){ var code=(document.getElementById('promo-code')?.value||'').trim(); var msg=document.getElementById('promo-msg'); post('/api/cart/promo',{code:code}).then(function(){ msg.textContent=code?("Code '"+code+"' applied"):'Code cleared'; }).catch(function(){ msg.textContent='Could not apply code'; }); return; }

    // Checkout via Stripe Checkout Session
    if(e.target && e.target.id==='checkoutBtn'){
      if (cart.length === 0) return;
      var btn = e.target; var prev = btn.textContent; btn.disabled = true; btn.style.opacity='.65'; btn.textContent = 'Redirecting…';
      post('/checkout/session', { items: serializeCartForCheckout() })
        .then(function(res){ if(res && res.url){ window.location.assign(res.url); return; } throw new Error('no url'); })
        .catch(function(){ alert('Could not start checkout. Please try again.'); btn.disabled=false; btn.style.opacity='1'; btn.textContent=prev; });
      return;
    }

    var add = e.target.closest('[data-add]');
    if (add){
      var uid = add.getAttribute('data-add');
      var u = (upsellPool||[]).find(function(x){ return String(x.id)===String(uid) }); if(!u) return;
      var pRaw = Number(u.price_min ?? u.price ?? 0); var unit = pRaw>=1000 ? pRaw/100 : pRaw;
      var ex = cart.find(function(x){ return String(x.id)===String(uid) });
      if(ex){ ex.qty = Math.max(1, Number(ex.qty||1)+1); }
      else { cart.unshift({ id:String(uid), title:String(u.title||''), url:(u.url||('/therapies/'+uid)), img:(u.image||(u.images&&u.images[0])||''), unit:unit, qty:1 }); }
      try{ post('/api/cart/add', { id: Number(uid)||uid, qty:1 }); }catch(_){}
      add.classList.add('is-added'); add.textContent='Added'; setTimeout(function(){ add.textContent='Add'; add.classList.remove('is-added'); }, 700);
      renderCart();
      return;
    }
  });
  document.addEventListener('input', function(e){ var inp=e.target.closest('.qty-input'); if(!inp) return; var row=e.target.closest('.cart-row'); if(!row) return; var id=row.getAttribute('data-id'); var item=cart.find(function(x){return String(x.id)===String(id)}); if(!item) return; var v=parseInt(inp.value||'1',10); if(!Number.isFinite(v)||v<1) v=1; item.qty=v; renderCart(); post('/api/cart/update',{id:id,qty:v}); });

  loadUpsell();
  renderCart();
})();
</script>

<style>
.lead-cart{ margin:0 0 18px; color: var(--ink-600); font-size:14px; font-weight:600; }
.card.glass{ position:relative; border-radius:16px; border:1px solid rgba(255,255,255,.55); background: rgba(255,255,255,.85); box-shadow: 0 18px 55px rgba(16,24,40,.10); overflow:hidden; }
.card.glass:before{ content:""; position:absolute; inset:0; background: rgba(255,255,255,.35); -webkit-backdrop-filter: blur(14px); backdrop-filter: blur(14px); pointer-events:none; }
.card.glass > *{ position:relative; }

.cart-grid{ display:flex; align-items:flex-start; gap: var(--gap); --gap:14px; --sideBasis:34.5%; --ease:cubic-bezier(.2,.8,.2,1); --dur:.42s; transition: gap var(--dur) var(--ease); }
.cart-main{ flex:1 1 auto; min-width:0; max-width:100%; opacity:1; transform: translateX(0) scale(1); transition:max-width var(--dur) var(--ease), transform var(--dur) var(--ease), opacity .22s var(--ease); }
.cart-side{ flex:0 1 auto; flex-basis: var(--sideBasis); min-width:0; position:sticky; transition:flex-basis var(--dur) var(--ease), transform var(--dur) var(--ease); }
.cart-grid.is-empty{ --gap:0px; --sideBasis:100%; }
.cart-grid.is-empty .cart-main{ max-width:0; opacity:0; transform: translateX(-10px) scale(.98); pointer-events:none; overflow:hidden; }
@media (max-width: 991.98px){ .cart-grid{ flex-direction:column; gap:14px; } .cart-side{ position:static; } .cart-grid.is-empty .cart-main{ max-width:100%; opacity:1; transform:none; pointer-events:auto; overflow:visible; } }

.cart-head{ display:grid; grid-template-columns: 1fr 150px 120px; gap:12px; padding:14px 16px; font-size:12px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; color: var(--ink-600); border-bottom:1px solid rgba(16,24,40,.10); background: linear-gradient(180deg, rgba(255,255,255,.80), rgba(255,255,255,.52)); }
.cart-body{ padding:10px; display:flex; flex-direction:column; gap:10px; }
.cart-row{ display:grid; grid-template-columns: 1fr 150px 120px; gap:12px; align-items:center; padding:12px; border-radius:18px; border:1px solid var(--ink-200); background: rgba(255,255,255,.86); box-shadow: 0 12px 26px rgba(16,24,40,.06); }
.cart-item{ display:grid; grid-template-columns: 76px 1fr; gap:12px; align-items:center; min-width:0; }
.cart-img{ width:76px; height:76px; border-radius:18px; overflow:hidden; display:block; border:1px solid var(--ink-200); background:#f3f5f7; }
.cart-img img{ width:100%; height:100%; object-fit:cover; display:block; }
.title{ margin:0; font-weight:800; font-size:14px; letter-spacing:-.01em; line-height:1.22; color: var(--ink-900); text-decoration:none; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.title:hover{ text-decoration:underline; }
.meta{ margin-top:6px; font-size:12px; color: var(--ink-600); font-weight:650; display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
.pill{ display:inline-flex; align-items:center; gap:6px; padding: 5px 10px; border-radius:999px; border:1px solid var(--ink-200); background: rgba(255,255,255,.9); box-shadow: 0 10px 18px rgba(16,24,40,.05); font-size:12px; font-weight:750; color: var(--ink-700); }
.pill .dot{ width:8px;height:8px;border-radius:999px; background: var(--accent-600); }
.cart-remove{ margin-top:8px; border:0; background:transparent; color: var(--ink-600); font-weight:750; font-size:12px; cursor:pointer; padding:0; text-decoration: underline; text-underline-offset: 3px; }
.cart-remove:hover{ color: var(--ink-800); }
.cart-qty{ display:flex; justify-content:flex-start; }
.qty{ display:inline-flex; align-items:center; border-radius:999px; border:1px solid var(--ink-200); background: rgba(255,255,255,.92); box-shadow: 0 10px 18px rgba(16,24,40,.06); overflow:hidden; height:38px; }
.qty button{ width:36px; height:38px; border:0; background:transparent; cursor:pointer; font-weight:900; font-size:16px; color: var(--ink-800); }
.qty button:hover{ background: rgba(16,24,40,.05); }
.qty input{ width:46px; height:38px; border:0; outline:0; text-align:center; font-weight:900; font-size:13px; background:transparent; color: var(--ink-900); -moz-appearance:textfield; }
.qty input::-webkit-outer-spin-button, .qty input::-webkit-inner-spin-button{ -webkit-appearance:none; margin:0; }
.cart-amt{ text-align:right; font-weight:900; letter-spacing:-.01em; font-size:14px; color: var(--ink-900); }
.cart-foot{ padding: 12px 16px 16px; display:flex; align-items:center; justify-content:space-between; gap:10px; border-top: 1px solid rgba(16,24,40,.10); }
.link-wow{ color: var(--ink-800); font-weight:800; text-decoration: none; border-bottom: 1px solid var(--ink-300); padding-bottom: 2px; }
.link-wow:hover{ color: var(--ink-900); border-bottom-color: var(--ink-500); }

.sum-head{ padding:14px 16px; font-weight:900; letter-spacing:-.02em; border-bottom:1px solid rgba(16,24,40,.10); background: linear-gradient(180deg, rgba(255,255,255,.80), rgba(255,255,255,.52)); }
.sum-body{ padding:14px 16px 16px; position:relative; }
.panel{ transition: opacity .22s cubic-bezier(.2,.8,.2,1), transform .42s cubic-bezier(.2,.8,.2,1), max-height .42s cubic-bezier(.2,.8,.2,1); overflow:hidden; }
.sum-row{ display:flex; justify-content:space-between; align-items:baseline; gap:10px; font-size:13px; color: var(--ink-700); font-weight:650; }
.sum-row strong{ font-weight:900; letter-spacing:-.01em; color: var(--ink-900); }
.sum-row.muted{ color: var(--ink-600); font-weight:600; font-size:12px; }
.sum-sep{ height:1px; background: rgba(16,24,40,.10); margin: 2px 0; }
.sum-row.total{ font-size:14px; font-weight:900; letter-spacing:-.01em; }
.sum-row.total strong{ font-size:18px; }
.promo{ margin-top:4px; padding:12px; border-radius:18px; border:1px solid var(--ink-200); background: rgba(255,255,255,.86); box-shadow: 0 12px 26px rgba(16,24,40,.06); }
.promo label{ display:block; font-size:12px; font-weight:900; letter-spacing:.08em; text-transform:uppercase; color: var(--ink-600); margin-bottom:8px; }
.promo-inline{ display:flex; gap:10px; align-items:center; }
.promo-inline input{ flex:1 1 auto; height:42px; border-radius:14px; border:1px solid var(--ink-200); background: rgba(255,255,255,.95); padding:0 12px; font-weight:800; outline:none; }
.promo-inline input:focus{ border-color: var(--accent-400); box-shadow: 0 0 0 4px color-mix(in srgb, var(--accent-600) 25%, transparent); }
.promo-msg{ margin-top:8px; font-size:12px; font-weight:750; color: var(--ink-700); }
.trust-hints{ display:flex; flex-direction:column; gap:8px; margin-top:8px; color: var(--ink-600); font-size:12px; font-weight:650; }
.hint{ display:flex; align-items:center; gap:10px; }
.hint .dot{ width:10px;height:10px;border-radius:999px; background: var(--accent-600); box-shadow: 0 10px 18px color-mix(in srgb, var(--accent-600) 25%, transparent); }

.empty-wrap{ padding:6px 0 0; display:grid; grid-template-columns: 1fr; gap:14px; }
.empty-hero{ padding:16px; border-radius:22px; border:1px solid var(--ink-200); background: rgba(255,255,255,.86); box-shadow: 0 12px 26px rgba(16,24,40,.06); display:flex; gap:14px; align-items:flex-start; }
.empty-illu{
  width: 54px;
  height: 54px;
  border-radius: 18px;
  display: grid;
  place-items: center;
  background: linear-gradient(180deg, rgba(84, 148, 131, .18), rgba(84, 148, 131, .06));
  border: 1px solid rgba(84, 148, 131, .22);
  box-shadow: 0 14px 30px rgba(84, 148, 131, .14);
  flex: 0 0 auto;
  color: rgba(11, 18, 32, .85);
}
.empty-hero h2{ margin:0; font-size:18px; letter-spacing:-.02em; font-weight:900; font-family:'Manrope', system-ui, -apple-system, Segoe UI, Roboto, Helvetica Neue, Arial, sans-serif; }
.sum-head, .title, .upsell-title{ font-family:'Manrope', system-ui, -apple-system, Segoe UI, Roboto, Helvetica Neue, Arial, sans-serif; }
.empty-hero p{ margin:6px 0 0; color: var(--ink-600); font-weight:650; font-size:13px; line-height:1.45; }
.empty-actions{ margin-top:12px; display:flex; gap:10px; flex-wrap:wrap; }

@media (max-width: 767.98px){ .cart-head{ display:none; } .cart-row{ grid-template-columns: 1fr; gap:10px; } .cart-amt{ text-align:left; } .cart-qty{ justify-content:flex-start; } .cart-item{ grid-template-columns: 70px 1fr; } .cart-img{ width:70px;height:70px; } }
@media (prefers-reduced-motion: reduce){ *{ transition:none !important; } }

/* Upsell block styling (align with template) */
.upsell{ margin-top:12px; padding:12px; border-radius:20px; border:1px solid var(--ink-200); background: rgba(255,255,255,.86); box-shadow: 0 12px 26px rgba(16,24,40,.06); }
.upsell-head{ display:flex; align-items:baseline; justify-content:space-between; gap:10px; margin-bottom:10px; }
.upsell-head strong{ font-weight:950; letter-spacing:-.01em; }
.upsell-head small{ color: var(--ink-600); font-weight:800; font-size:12px; }
.upsell-list{ display:flex; flex-direction:column; gap:10px; }
.upsell-item{ display:grid; grid-template-columns:52px 1fr auto; gap:10px; align-items:center; padding:10px; border-radius:16px; border:1px solid var(--ink-200); background: rgba(255,255,255,.95); }
.upsell-item img{ width:52px; height:52px; border-radius:14px; object-fit:cover; border:1px solid var(--ink-200); background:#f3f5f7; }
.upsell-title{ font-size:13px; font-weight:900; margin:0; line-height:1.2; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.upsell-price{ font-size:12px; color: var(--ink-700); font-weight:900; margin-top:4px; }
.upsell-add{ height:34px; border-radius:999px; border:1px solid var(--ink-200); background: rgba(255,255,255,.92); font-weight:950; font-size:12px; padding:0 12px; cursor:pointer; box-shadow: 0 10px 18px rgba(16,24,40,.06); white-space:nowrap; }
.upsell-add:hover{ background: rgba(247,248,250,.95); border-color: var(--ink-300); }
.upsell-add.is-added{ background: color-mix(in srgb, var(--accent-600) 12%, white); border-color: color-mix(in srgb, var(--accent-600) 45%, transparent); }
</style>
@endsection
