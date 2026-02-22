@php
  // Unified cart storage: cart.items
  $items = session('cart.items', []);
  $count = array_sum(array_map(fn($it)=> (int)($it['qty'] ?? 0), $items));
  $total = 0.0;
  foreach ($items as $it) {
    $price = (float)($it['price'] ?? 0);
    if ($price >= 1000) $price = $price/100; // pennies → pounds
    $total += $price * (int)($it['qty'] ?? 1);
  }
@endphp
<div class="mini-cart">
  @if($count===0)
    <div class="mini-cart__empty">Your cart is empty.</div>
  @else
    <div class="mini-cart__items">
      @foreach($items as $id => $it)
        @php
          $price = (float)($it['price'] ?? 0);
          if ($price >= 1000) $price = $price/100;
        @endphp
        <div class="mini-cart__row" data-id="{{ $id }}">
          <a class="mini-cart__img" href="{{ $it['url'] ?? '#' }}">
            @if(!empty($it['image']))<img src="{{ $it['image'] }}" alt="{{ $it['title'] ?? '' }}" />@endif
          </a>
          <div class="mini-cart__info">
            <a class="mini-cart__title" href="{{ $it['url'] ?? '#' }}">{{ $it['title'] ?? 'Item' }}</a>
            <div class="mini-cart__meta">Qty {{ (int)($it['qty'] ?? 1) }} • £{{ number_format($price, 2) }}</div>
          </div>
          <button class="mini-cart__remove" type="button" data-remove="{{ $id }}" aria-label="Remove">×</button>
        </div>
      @endforeach
    </div>
    <div class="mini-cart__foot">
      <div class="mini-cart__total"><span>Total</span><strong>£{{ number_format($total, 2) }}</strong></div>
      <div class="mini-cart__actions">
        <a class="btn-wow btn-wow--outline btn-sm" href="/cart">View cart</a>
        <a class="btn-wow btn-wow--cta btn-sm" href="/cart#checkout">Checkout</a>
      </div>
    </div>
  @endif
</div>

<style>
.mini-cart{ min-width:280px; max-width:360px; background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 12px 32px rgba(2,8,23,.22); overflow:hidden }
.mini-cart__empty{ padding:14px; color:#475569; font-size:.95rem }
.mini-cart__items{ display:flex; flex-direction:column; max-height:320px; overflow:auto }
.mini-cart__row{ display:grid; grid-template-columns:56px 1fr auto; gap:10px; align-items:center; padding:10px 12px; border-bottom:1px solid #f1f5f9 }
.mini-cart__row:last-child{ border-bottom:0 }
.mini-cart__img{ display:block; width:56px; height:56px; border-radius:8px; overflow:hidden; border:1px solid #e5e7eb; background:#fafafa }
.mini-cart__img img{ width:100%; height:100%; object-fit:cover; display:block }
.mini-cart__title{ display:block; color:#0b1323; font-weight:600; line-height:1.2; text-decoration:none }
.mini-cart__meta{ color:#64748b; font-size:.875rem }
.mini-cart__remove{ width:28px; height:28px; border-radius:999px; border:1px solid #e2e8f0; background:#fff; color:#0f172a; display:grid; place-items:center; cursor:pointer }
.mini-cart__remove:hover{ background:#f8fafc }
.mini-cart__foot{ padding:10px 12px; background:#fff }
.mini-cart__total{ display:flex; align-items:center; justify-content:space-between; margin-bottom:8px; color:#0b1323 }
.mini-cart__actions{ display:flex; gap:8px; justify-content:flex-end }
</style>
