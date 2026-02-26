@extends('layouts.app')

@section('content')
<section class="section">
  <div class="container-page">
    <div class="kicker">Your cart</div>
    <h1 class="mb-1">Review and checkout</h1>
    <p class="text-ink-600 mt-0 mb-4">Secure, safe and flexible — you can reschedule when needed.</p>
    @php
      $items = session('cart.items', []);
      $subtotal = 0.0;
      foreach ($items as $it) { $p=(float)($it['price']??0); if($p>=1000) $p=$p/100; $subtotal += $p * (int)($it['qty']??1); }
      $discount = 0.0; // apply on promo code server-side later
      $total = max(0, $subtotal - $discount);
    @endphp
    @if(empty($items))
      <div class="cart-empty">
        <div class="cart-empty-card">
          <div class="icon">🛒</div>
          <h3>Your cart is empty</h3>
          <p>Discover experiences tailored to you.</p>
          <a class="btn-wow btn-wow--cta" href="/search">Browse listings</a>
        </div>
      </div>
    @else
      <div class="cart-grid">
        <div class="cart-main card p-0 cart-main--elev">
          <div class="cart-head">
            <div>Item</div>
            <div>Qty</div>
            <div>Price</div>
          </div>
          <div class="cart-body">
          @foreach($items as $id => $it)
            @php $price = (float)($it['price'] ?? 0); if($price>=1000) $price=$price/100; $qty=(int)($it['qty']??1); @endphp
            <div class="cart-row" data-id="{{ $id }}" data-unit="{{ number_format($price,2,'.','') }}">
              <div class="cart-item">
                <a class="cart-img" href="{{ $it['url'] ?? '#' }}">@if(!empty($it['image']))<img src="{{ $it['image'] }}" alt="{{ $it['title'] ?? '' }}" />@endif</a>
                <div class="cart-info">
                  <a class="title" href="{{ $it['url'] ?? '#' }}">{{ $it['title'] ?? 'Item' }}</a>
                  <div class="meta">Unit: £{{ number_format($price,2) }}</div>
                  <button class="cart-remove mt-1" type="button" data-remove="{{ $id }}" aria-label="Remove">Remove</button>
                </div>
              </div>
              <div class="cart-qty">
                <div class="qty">
                  <button type="button" class="btn btn-sm js-qdec" aria-label="Decrease">−</button>
                  <input type="number" class="qty-input" min="1" value="{{ $qty }}" />
                  <button type="button" class="btn btn-sm js-qinc" aria-label="Increase">+</button>
                </div>
              </div>
              <div class="cart-amt">£{{ number_format($price * $qty, 2) }}</div>
            </div>
          @endforeach
          </div>
          <div class="cart-foot">
            <a href="/search" class="link-wow">Continue shopping</a>
          </div>
        </div>

        <aside class="cart-side card p-0 cart-side--elev" id="checkout">
          <div class="sum-head">Order summary</div>
          <div class="sum-body">
            <div class="sum-row"><span>Subtotal</span><strong id="sum-subtotal">£{{ number_format($subtotal, 2) }}</strong></div>
            <div class="sum-row"><span>Discounts</span><strong id="sum-discount">-£{{ number_format($discount, 2) }}</strong></div>
            <div class="sum-row muted"><span>Taxes</span><span>Included where applicable</span></div>

            <div class="sum-sep"></div>
            <div class="sum-row total"><span>Total</span><strong id="sum-total">£{{ number_format($total, 2) }}</strong></div>

            <div class="promo">
              <label for="promo-code">Promo code</label>
              <div class="promo-inline">
                <input id="promo-code" type="text" placeholder="Enter code" value="{{ session('cart_promo_code') }}" />
                <button type="button" class="btn-wow btn-wow--outline btn-sm" id="apply-promo">Apply</button>
              </div>
              <div id="promo-msg" class="promo-msg"></div>
            </div>

            <button class="btn-wow btn-wow--cta w-100" id="checkoutBtn">Proceed to checkout</button>
            <div class="trust-hints">
              <div class="hint"><span class="dot"></span>Secure checkout</div>
              <div class="hint"><span class="dot"></span>Free reschedule window</div>
            </div>
          </div>
        </aside>
      </div>
    @endif
  </div>
  </section>

<script>
(function(){
  function cookie(name){ try{ return document.cookie.split('; ').find(r=>r.startsWith(name+'='))?.split('=')[1]||'' }catch(e){ return '' } }
  function post(url, data){ var token=decodeURIComponent(cookie('XSRF-TOKEN')||''); return fetch(url, { method:'POST', headers:{ 'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest','X-XSRF-TOKEN':token }, body: JSON.stringify(data||{}), credentials:'same-origin' }).then(r=>r.json()); }
  function money(n){ try{ return '£'+Number(n).toFixed(2) }catch(_){ return '£0.00' } }
  // Client cart util (same as header, subset)
  var CartClient = (function(){ try{ return window.CartClient || (function(){ var key='wow_cart'; function load(){ try{ var raw=localStorage.getItem(key)||decodeURIComponent(document.cookie.split('; ').find(r=>r.startsWith(key+'='))?.split('=')[1]||''); return raw?JSON.parse(raw):{} }catch(_){ return {} } } function list(){ var bag=load(), out=[]; Object.keys(bag).forEach(function(k){ out.push(bag[k]) }); return out } function syncServer(){ var bag=load(); Object.keys(bag).forEach(function(k){ var it=bag[k]; post('/api/cart/add', { id: it.id, qty: it.qty||1 }); }); } return { load:load, list:list, syncServer:syncServer }; })(); }catch(_){ return { load:function(){return{}}, list:function(){return[]}, syncServer:function(){} } } })();
  // If no rows rendered but client cart has items, bootstrap UI and sync
  try{
    if(!document.querySelector('.cart-row')){
      var items = CartClient.list();
      if(items.length){
        var body = document.querySelector('.cart-body');
        var head = document.querySelector('.cart-head');
        if(body && head){
          var html='';
          items.forEach(function(it){ var unit=Number(it.price||0); var qty=Number(it.qty||1); html += '<div class="cart-row" data-id="'+it.id+'" data-unit="'+unit.toFixed(2)+'">' + '<div class="cart-item">' + '<a class="cart-img" href="'+(it.url||'#')+'">'+(it.image?('<img src="'+it.image+'" alt="">'):'')+'</a>' + '<div class="cart-info">' + '<a class="title" href="'+(it.url||'#')+'">'+(it.title||'Item')+'</a>' + '<div class="meta">Unit: £'+unit.toFixed(2)+'</div>' + '<button class="cart-remove mt-1" type="button" data-remove="'+it.id+'" aria-label="Remove">Remove</button>' + '</div></div>' + '<div class="cart-qty"><div class="qty">' + '<button type="button" class="btn btn-sm js-qdec" aria-label="Decrease">−</button>' + '<input type="number" class="qty-input" min="1" value="'+qty+'" />' + '<button type="button" class="btn btn-sm js-qinc" aria-label="Increase">+</button>' + '</div></div>' + '<div class="cart-amt">£'+(unit*qty).toFixed(2)+'</div>' + '</div>'; });
          body.innerHTML = html;
          CartClient.syncServer();
        }
      }
    }
  }catch(_){ }
  function recalc(){
    var rows = document.querySelectorAll('.cart-row'); var sub=0; rows.forEach(function(row){ var unit = parseFloat(row.getAttribute('data-unit')||'0'); var qty = parseInt(row.querySelector('.qty-input')?.value||'1',10)||1; sub += unit*qty; var amt=row.querySelector('.cart-amt'); if(amt) amt.textContent = money(unit*qty); });
    document.getElementById('sum-subtotal')?.replaceChildren(document.createTextNode(money(sub)));
    var disc = document.getElementById('sum-discount'); var dValText = disc?.textContent?.replace('£','').replace('-','')||'0'; var dVal = parseFloat(dValText)||0; var tot = Math.max(0, sub - dVal); document.getElementById('sum-total')?.replaceChildren(document.createTextNode(money(tot)));
  }
  document.addEventListener('click', function(e){
    var row = e.target.closest('.cart-row');
    if(row && (e.target.classList.contains('js-qdec') || e.target.classList.contains('js-qinc'))){
      var id = row.getAttribute('data-id'); var inp = row.querySelector('.qty-input'); var v = parseInt(inp.value||'1',10); v = isFinite(v)?v:1; v += (e.target.classList.contains('js-qinc')?1:-1); if(v<1) v=1; inp.value = v; recalc(); post('/api/cart/update', { id:id, qty:v }); return;
    }
    if(e.target.matches('[data-remove]')){
      var id = e.target.getAttribute('data-remove'); post('/api/cart/remove', { id:id }).then(function(){ var r=document.querySelector('.cart-row[data-id="'+id+'"]'); if(r){ r.remove(); recalc(); if(!document.querySelector('.cart-row')){ window.location.reload(); } } }); return;
    }
  });
  document.getElementById('apply-promo')?.addEventListener('click', function(){
    var code = (document.getElementById('promo-code')?.value||'').trim(); var msg=document.getElementById('promo-msg');
    post('/api/cart/promo', { code: code }).then(function(){ msg.textContent = code? ('Code \''+code+'\' applied') : 'Code cleared'; msg.className='promo-msg ok'; }).catch(function(){ msg.textContent='Could not apply code'; msg.className='promo-msg err'; });
  });
  // Initial calc
  recalc();
})();
</script>

<style>
.cart-empty{ display:grid; place-items:center; padding:40px 0 }
.cart-empty-card{ width:min(640px, 96vw); border:1px solid #e6ebf2; border-radius:16px; background:linear-gradient(180deg,#ffffff, #f8fafc); box-shadow: 0 30px 80px rgba(16,24,40,.12); padding:32px; text-align:center }
.cart-empty-card .icon{ font-size:3rem; line-height:1; margin-bottom:8px }
.cart-empty-card h3{ margin:6px 0 4px; font-size:1.5rem; font-weight:800; color:#0b1323 }
.cart-empty-card p{ margin:0 0 16px; color:#64748b; font-size:1rem }
.cart-main--elev, .cart-side--elev{ border-radius:16px; overflow:hidden; border:1px solid #e6ebf2; box-shadow: 0 20px 60px rgba(16,24,40,.10) }
.cart-grid{ display:grid; grid-template-columns: 1fr; gap:16px }
@media (min-width: 1024px){ .cart-grid{ grid-template-columns: 1.6fr 1fr } }
.cart-head{ display:grid; grid-template-columns: 1fr 140px 120px; gap:10px; padding:14px 16px; border-bottom:1px solid #e5e7eb; background:linear-gradient(180deg,#fff,#f9fafb) }
.cart-body{ display:block }
.cart-row{ display:grid; grid-template-columns: 1fr 140px 120px; gap:10px; align-items:center; padding:14px 16px; border-bottom:1px solid #f1f5f9 }
.cart-row:last-child{ border-bottom:0 }
.cart-item{ display:flex; align-items:center; gap:12px }
.cart-img{ display:block; width:72px; height:72px; border-radius:12px; overflow:hidden; border:1px solid #e5e7eb; background:#fafafa }
.cart-img img{ width:100%; height:100%; object-fit:cover; display:block }
.cart-info .title{ font-weight:700; color:#0b1323; text-decoration:none }
.cart-info .meta{ color:#64748b; font-size:.9rem }
.cart-remove{ background:none; border:0; color:#ef4444; cursor:pointer; padding:0 }
.cart-qty{ display:flex; align-items:center; justify-content:center }
.qty{ display:inline-grid; grid-template-columns: 34px 56px 34px; align-items:center; gap:6px }
.qty-input{ width:56px; height:36px; text-align:center; border:1px solid #e2e8f0; border-radius:8px }
.cart-amt{ font-weight:800; color:#0b1323; text-align:right }
.cart-foot{ padding:12px 16px; background:#fff }

.cart-side .sum-head{ padding:14px 16px; border-bottom:1px solid #e5e7eb; background:linear-gradient(180deg,#fff,#f9fafb); font-weight:700 }
.cart-side .sum-body{ padding:14px 16px }
.sum-row{ display:flex; align-items:center; justify-content:space-between; gap:10px; padding:8px 0 }
.sum-row.muted{ color:#64748b; font-size:.9rem }
.sum-row.total strong{ font-size:1.25rem }
.sum-sep{ height:1px; background:#e5e7eb; margin:8px 0 }
.promo{ margin-top:8px }
.promo-inline{ display:flex; gap:8px; margin-top:6px }
.promo-inline input{ flex:1; height:38px; border:1px solid #e2e8f0; border-radius:8px; padding:0 10px }
.promo-msg{ margin-top:6px; font-size:.9rem; color:#64748b }
.promo-msg.ok{ color:#166534 }
.promo-msg.err{ color:#b91c1c }
.trust-hints{ display:flex; align-items:center; gap:10px; margin-top:10px; color:#64748b; font-size:.9rem }
.trust-hints .dot{ width:6px; height:6px; background:#549483; border-radius:999px; display:inline-block; margin-right:6px }
.w-100{ width:100% }
</style>
@endsection
