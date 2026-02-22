@extends('layouts.app')

@section('content')
<section class="section">
  <div class="container-page">
    <div class="kicker">Your cart</div>
    <h1 class="mb-3">Review and checkout</h1>
    @php
      $items = session('cart.items', []);
      $total = 0.0;
      foreach ($items as $it) { $p=(float)($it['price']??0); if($p>=1000) $p=$p/100; $total += $p * (int)($it['qty']??1); }
    @endphp
    @if(empty($items))
      <div class="card p-6 text-ink-700">Your cart is empty. <a class="link-wow" href="/search">Browse listings</a>.</div>
    @else
      <div class="cart-grid">
        <div class="cart-main card p-4">
          @foreach($items as $id => $it)
            @php $price = (float)($it['price'] ?? 0); if($price>=1000) $price=$price/100; @endphp
            <div class="cart-row" data-id="{{ $id }}">
              <a class="cart-img" href="{{ $it['url'] ?? '#' }}">@if(!empty($it['image']))<img src="{{ $it['image'] }}" alt="{{ $it['title'] ?? '' }}" />@endif</a>
              <div class="cart-info">
                <a class="title" href="{{ $it['url'] ?? '#' }}">{{ $it['title'] ?? 'Item' }}</a>
                <div class="meta">Unit: £{{ number_format($price,2) }}</div>
                <div class="qty">
                  <button type="button" class="btn btn-sm js-qdec" aria-label="Decrease">−</button>
                  <input type="number" class="qty-input" min="1" value="{{ (int)($it['qty'] ?? 1) }}" />
                  <button type="button" class="btn btn-sm js-qinc" aria-label="Increase">+</button>
                </div>
              </div>
              <div class="cart-amt">£{{ number_format($price * (int)($it['qty'] ?? 1), 2) }}</div>
              <button class="cart-remove" type="button" data-remove="{{ $id }}" aria-label="Remove">Remove</button>
            </div>
          @endforeach
        </div>
        <aside class="cart-side card p-4" id="checkout">
          <div class="total-row"><span>Total</span><strong>£{{ number_format($total, 2) }}</strong></div>
          <button class="btn-wow btn-wow--cta w-100">Proceed to checkout</button>
          <p class="text-ink-600 mt-2 mb-0" style="font-size:.9rem">We’ll confirm details before payment.</p>
        </aside>
      </div>
    @endif
  </div>
</section>

<script>
(function(){
  function cookie(name){ try{ return document.cookie.split('; ').find(r=>r.startsWith(name+'='))?.split('=')[1]||'' }catch(e){ return '' } }
  function post(url, data){ var token=decodeURIComponent(cookie('XSRF-TOKEN')||''); return fetch(url, { method:'POST', headers:{ 'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest','X-XSRF-TOKEN':token }, body: JSON.stringify(data||{}), credentials:'same-origin' }).then(r=>r.json()); }
  document.addEventListener('click', function(e){
    var row = e.target.closest('.cart-row'); if(!row) return;
    var id = row.getAttribute('data-id');
    if(e.target.matches('[data-remove]')){
      post('/api/cart/remove', { id:id }).then(()=>{ window.location.reload(); });
    }
    if(e.target.classList.contains('js-qdec') || e.target.classList.contains('js-qinc')){
      var inp = row.querySelector('.qty-input'); var v = parseInt(inp.value||'1',10); v = isFinite(v)?v:1; v += (e.target.classList.contains('js-qinc')?1:-1); if(v<1) v=1; inp.value = v; post('/api/cart/update', { id:id, qty:v }).then(()=>{ window.location.reload(); });
    }
  });
})();
</script>

<style>
.cart-grid{ display:grid; grid-template-columns: 1fr; gap:16px }
@media (min-width: 992px){ .cart-grid{ grid-template-columns: 2fr 1fr } }
.cart-row{ display:grid; grid-template-columns:80px 1fr auto auto; gap:12px; align-items:center; padding:12px 0; border-bottom:1px solid #e5e7eb }
.cart-row:last-child{ border-bottom:0 }
.cart-img{ display:block; width:80px; height:80px; border-radius:10px; overflow:hidden; border:1px solid #e5e7eb; background:#fafafa }
.cart-img img{ width:100%; height:100%; object-fit:cover; display:block }
.cart-info .title{ font-weight:700; color:#0b1323; text-decoration:none }
.cart-info .meta{ color:#64748b }
.qty{ display:inline-grid; grid-template-columns: 32px 54px 32px; align-items:center; gap:6px; margin-top:6px }
.qty-input{ width:54px; height:32px; text-align:center; border:1px solid #e2e8f0; border-radius:6px }
.cart-amt{ font-weight:700; color:#0b1323 }
.cart-remove{ background:none; border:0; color:#ef4444; cursor:pointer }
.cart-side .total-row{ display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; font-size:1.1rem }
.w-100{ width:100% }
</style>
@endsection

