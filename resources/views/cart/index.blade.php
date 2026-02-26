@extends('layouts.app')

@section('content')
@php
  $items = session('cart.items', []);
  $subtotal = 0.0;
  foreach ($items as $it) { $p=(float)($it['price']??0); if($p>=1000) $p=$p/100; $subtotal += $p * (int)($it['qty']??1); }
  $discount = 0.0; // apply on promo code server-side later
  $total = max(0, $subtotal - $discount);
  $isEmpty = empty($items);
@endphp

<section class="section">
  <div class="container-page">
    <div class="kicker">Your cart</div>
    <h1 class="mb-1">Ready to book</h1>
    <p class="lead-cart">Secure, safe and flexible — you can reschedule when needed.</p>

    <div id="cartGrid" class="cart-grid {{ $isEmpty ? 'is-empty' : '' }}">
      <div class="cart-main card glass" id="cartMain">
        <div class="cart-head" id="cartHead">
          <div>Item</div><div>Qty</div><div>Price</div>
        </div>
        <div class="cart-body" id="cartBody">
          @foreach($items as $id => $it)
            @php $price = (float)($it['price'] ?? 0); if($price>=1000) $price=$price/100; $qty=(int)($it['qty']??1); @endphp
            <div class="cart-row" data-id="{{ $id }}" data-unit="{{ number_format($price,2,'.','') }}">
              <div class="cart-item">
                <a class="cart-img" href="{{ $it['url'] ?? '#' }}">@if(!empty($it['image']))<img src="{{ $it['image'] }}" alt="{{ $it['title'] ?? '' }}" />@endif</a>
                <div class="cart-info">
                  <a class="title" href="{{ $it['url'] ?? '#' }}">{{ $it['title'] ?? 'Item' }}</a>
                  <div class="meta">
                    <span class="pill"><span class="dot"></span>Unit: £{{ number_format($price,2) }}</span>
                    <span class="pill">Line: £{{ number_format($price*$qty,2) }}</span>
                  </div>
                  <button class="cart-remove" type="button" data-remove="{{ $id }}" aria-label="Remove">Remove</button>
                </div>
              </div>
              <div class="cart-qty">
                <div class="qty">
                  <button type="button" class="js-qdec" aria-label="Decrease">−</button>
                  <input type="number" class="qty-input" min="1" value="{{ $qty }}">
                  <button type="button" class="js-qinc" aria-label="Increase">+</button>
                </div>
              </div>
              <div class="cart-amt">£{{ number_format($price * $qty, 2) }}</div>
            </div>
          @endforeach
        </div>
        <div class="cart-foot" id="cartFoot">
          <a href="/search" class="link-wow">Continue shopping</a>
          <button class="btn-wow btn-wow--outline" id="clearCartBtn" type="button">Clear cart</button>
        </div>
      </div>

      <aside class="cart-side card glass" id="checkout">
        <div class="sum-head" id="sumHeadTitle">{{ $isEmpty ? 'Your cart' : 'Order summary' }}</div>
        <div class="sum-body">
          <div class="panel" id="summaryWrap" style="{{ $isEmpty ? 'display:none' : '' }}">
            <div class="sum-row"><span>Subtotal</span><strong id="sum-subtotal">£{{ number_format($subtotal, 2) }}</strong></div>
            <div class="sum-row"><span>Discounts</span><strong id="sum-discount">-£{{ number_format($discount, 2) }}</strong></div>
            <div class="sum-row muted"><span>Taxes</span><span>Included where applicable</span></div>
            <div class="sum-sep"></div>
            <div class="sum-row total"><span>Total</span><strong id="sum-total">£{{ number_format($total, 2) }}</strong></div>

            <div class="promo">
              <label for="promo-code">Promo code</label>
              <div class="promo-inline">
                <input id="promo-code" type="text" placeholder="Enter code" value="{{ session('cart_promo_code') }}">
                <button type="button" class="btn-wow btn-wow--outline" id="apply-promo">Apply</button>
              </div>
              <div id="promo-msg" class="promo-msg"></div>
            </div>

            <button class="btn-wow btn-wow--cta w-100" id="checkoutBtn" {{ $isEmpty ? 'disabled' : '' }}>Continue to checkout</button>
            <div class="trust-hints">
              <div class="hint"><span class="dot"></span>Secure checkout</div>
              <div class="hint"><span class="dot"></span>Free reschedule window</div>
            </div>
          </div>

          <div class="panel empty-wrap" id="emptyWrap" style="{{ $isEmpty ? '' : 'display:none' }}">
            <div class="empty-hero">
              <div class="empty-illu" aria-hidden="true">🛒</div>
              <div>
                <h2>Your cart is empty</h2>
                <p>Add an experience and you’re good to go.</p>
                <div class="empty-actions">
                  <a class="btn-wow btn-wow--cta" href="/search">Browse experiences</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </aside>
    </div>
  </div>
</section>

<script>
(function(){
  function cookie(name){ try{ return document.cookie.split('; ').find(r=>r.startsWith(name+'='))?.split('=')[1]||'' }catch(e){ return '' } }
  function post(url, data){ var token=decodeURIComponent(cookie('XSRF-TOKEN')||''); return fetch(url, { method:'POST', headers:{ 'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest','X-XSRF-TOKEN':token }, body: JSON.stringify(data||{}), credentials:'same-origin' }).then(r=>r.json()); }
  function money(n){ try{ return '£'+Number(n).toFixed(2) }catch(_){ return '£0.00' } }

  function recalc(){
    var rows = Array.from(document.querySelectorAll('.cart-row'));
    var sub = 0; rows.forEach(function(r){ var u = parseFloat(r.getAttribute('data-unit')||'0')||0; var q = parseInt(r.querySelector('.qty-input')?.value||'1',10); if(!isFinite(q) || q<1) q=1; sub += u*q; r.querySelector('.cart-amt')?.replaceChildren(document.createTextNode(money(u*q))); });
    document.getElementById('sum-subtotal')?.replaceChildren(document.createTextNode(money(sub)));
    var discEl = document.getElementById('sum-discount'); var dValText = discEl?.textContent?.replace('£','').replace('-','')||'0'; var dVal = parseFloat(dValText)||0; var tot = Math.max(0, sub - dVal); document.getElementById('sum-total')?.replaceChildren(document.createTextNode(money(tot)));
    var grid = document.getElementById('cartGrid'); var head=document.getElementById('sumHeadTitle'); var has = rows.length>0; grid.classList.toggle('is-empty', !has);
    document.getElementById('summaryWrap').style.display = has ? '' : 'none';
    document.getElementById('emptyWrap').style.display = has ? 'none' : '';
    document.getElementById('cartHead').classList.toggle('hidden', !has);
    document.getElementById('cartFoot').classList.toggle('hidden', !has);
    document.getElementById('cartBody').classList.toggle('hidden', !has);
    head.textContent = has ? 'Order summary' : 'Your cart';
    var checkoutBtn = document.getElementById('checkoutBtn'); if(checkoutBtn){ checkoutBtn.disabled = !has; checkoutBtn.style.opacity = has? '1' : '.55'; checkoutBtn.style.cursor = has? 'pointer' : 'not-allowed'; }
  }

  document.addEventListener('click', function(e){
    var row = e.target.closest('.cart-row');
    if(row && (e.target.closest('.js-qdec') || e.target.closest('.js-qinc'))){
      var id = row.getAttribute('data-id'); var inp = row.querySelector('.qty-input'); var v = parseInt(inp.value||'1',10); v = isFinite(v)?v:1; v += (e.target.closest('.js-qinc')?1:-1); if(v<1) v=1; inp.value = v; recalc(); post('/api/cart/update', { id:id, qty:v }); return;
    }
    if(e.target.matches('[data-remove]')){
      var id = e.target.getAttribute('data-remove');
      try{ var raw = localStorage.getItem('wow_cart'); var data = raw?JSON.parse(raw):{}; var arr = Array.isArray(data.items)?data.items:[]; data.items = arr.filter(function(it){ return String(it.id)!==String(id) }); localStorage.setItem('wow_cart', JSON.stringify(data)); }catch(_){ }
      post('/api/cart/remove', { id:id }).then(function(){ var r=document.querySelector('.cart-row[data-id="'+id+'"]'); if(r){ r.remove(); recalc(); } }); return;
    }
    if (e.target && e.target.id === 'clearCartBtn'){
      var ids = Array.from(document.querySelectorAll('.cart-row')).map(function(r){ return r.getAttribute('data-id'); });
      ids.forEach(function(id){ post('/api/cart/remove', { id:id }); });
      document.getElementById('cartBody').innerHTML=''; recalc();
    }
  });

  document.addEventListener('input', function(e){
    var inp = e.target.closest('.qty-input'); if(!inp) return; var row = e.target.closest('.cart-row'); if(!row) return; var id = row.getAttribute('data-id'); var v = parseInt(inp.value||'1',10); if(!isFinite(v)||v<1) v=1; inp.value=v; recalc(); post('/api/cart/update', { id:id, qty:v });
  });

  document.getElementById('apply-promo')?.addEventListener('click', function(){
    var code = (document.getElementById('promo-code')?.value||'').trim(); var msg=document.getElementById('promo-msg');
    post('/api/cart/promo', { code: code }).then(function(){ msg.textContent = code? ('Code \''+code+'\' applied') : 'Code cleared'; msg.className='promo-msg ok'; }).catch(function(){ msg.textContent='Could not apply code'; msg.className='promo-msg err'; });
  });

  recalc();
})();
</script>

<style>
.lead-cart{ margin:0 0 18px; color: var(--ink-600); font-size:14px; font-weight:600; }
.card.glass{ position:relative; border-radius:16px; border:1px solid rgba(255,255,255,.55); background: rgba(255,255,255,.85); box-shadow: 0 18px 55px rgba(16,24,40,.10); overflow:hidden; }
.card.glass:before{ content:""; position:absolute; inset:0; background: rgba(255,255,255,.35); -webkit-backdrop-filter: blur(14px); backdrop-filter: blur(14px); pointer-events:none; }
.card.glass > *{ position:relative; }

.cart-grid{ display:flex; gap:14px; align-items:flex-start; }
.cart-main{ flex:1 1 auto; min-width:0; max-width:100%; transition:max-width .42s cubic-bezier(.2,.8,.2,1), transform .42s cubic-bezier(.2,.8,.2,1), opacity .22s; }
.cart-side{ flex:0 0 34.5%; min-width:0; position:sticky; top:14px; }
.cart-grid.is-empty{ gap:0; }
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
.empty-illu{ width:54px; height:54px; border-radius:18px; display:grid; place-items:center; background: linear-gradient(180deg, color-mix(in srgb, var(--accent-600) 18%, white), #fff); border: 1px solid color-mix(in srgb, var(--accent-600) 35%, transparent); box-shadow: 0 14px 30px color-mix(in srgb, var(--accent-600) 25%, transparent); flex:0 0 auto; color: var(--ink-800); }
.empty-hero h2{ margin:0; font-size:18px; letter-spacing:-.02em; font-weight:900; }
.empty-hero p{ margin:6px 0 0; color: var(--ink-600); font-weight:650; font-size:13px; line-height:1.45; }
.empty-actions{ margin-top:12px; display:flex; gap:10px; flex-wrap:wrap; }

@media (max-width: 767.98px){ .cart-head{ display:none; } .cart-row{ grid-template-columns: 1fr; gap:10px; } .cart-amt{ text-align:left; } .cart-qty{ justify-content:flex-start; } .cart-item{ grid-template-columns: 70px 1fr; } .cart-img{ width:70px;height:70px; } }
@media (prefers-reduced-motion: reduce){ *{ transition:none !important; } }
</style>
@endsection

