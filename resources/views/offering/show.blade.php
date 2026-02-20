@extends('layouts.app')

@section('content')

@php
  $p = $product ?? [];
  $title = $p['title'] ?? 'Experience';
  $type = $p['type'] ?? 'experience';
  $rating = $p['rating'] ?? null;
  $reviewCount = $p['review_count'] ?? 0;
  $priceMin = $p['price_min'] ?? ($p['price'] ?? null);
  if (is_numeric($priceMin) && $priceMin >= 1000) { $priceMin = $priceMin / 100; }
  $images = $p['images'] ?? ($p['image'] ? [ $p['image'] ] : []);
  $summary = trim((string)($p['summary'] ?? ''));
  $body = trim((string)($p['body_html'] ?? ''));
  $what = trim((string)($p['what_to_expect'] ?? ''));
  $included = trim((string)($p['included'] ?? ''));
  $safety = trim((string)($p['safety_notes'] ?? ''));
  $contra = trim((string)($p['contraindications'] ?? ''));
  $variants = $p['variants'] ?? [];
  $options = $p['options'] ?? [];
@endphp

<section class="section">
  <div class="container-page">
    <div class="row g-4">
      <div class="col-12 col-lg-7">
        <div class="card p-2">
          @if(!empty($images))
            <div class="d-flex gap-2 flex-wrap">
              @foreach ($images as $img)
                <div class="flex-shrink-0" style="width: 160px; height: 120px; overflow: hidden; border-radius: 10px; border:1px solid var(--ink-200)">
                  <img src="{{ $img }}" alt="{{ $title }}" style="width:100%;height:100%;object-fit:cover">
                </div>
              @endforeach
            </div>
          @else
            <div class="ratio ratio-16x9 bg-ink-100 rounded"></div>
          @endif
        </div>

        @if($body !== '')
        <div class="card p-4 mt-4">
          <h3 class="h5">About this {{ strtolower($type) }}</h3>
          <div class="prose" style="max-width: 70ch;">{!! $body !!}</div>
        </div>
        @endif

        @if($what !== '')
        <div class="card p-4 mt-4">
          <h3 class="h6 m-0">What to expect</h3>
          <div class="mt-2 text-ink-700">{!! nl2br(e($what)) !!}</div>
        </div>
        @endif

        @if($included !== '')
        <div class="card p-4 mt-4">
          <h3 class="h6 m-0">What’s included</h3>
          <div class="mt-2 text-ink-700">{!! nl2br(e($included)) !!}</div>
        </div>
        @endif

        @if($safety !== '' || $contra !== '')
        <div class="card p-4 mt-4">
          <h3 class="h6 m-0">Safety & contraindications</h3>
          @if($safety!=='')<div class="mt-2 text-ink-700">{!! nl2br(e($safety)) !!}</div>@endif
          @if($contra!=='')<div class="mt-2 text-ink-700">{!! nl2br(e($contra)) !!}</div>@endif
        </div>
        @endif
      </div>

      <div class="col-12 col-lg-5">
        <style>
          .buybox{position:sticky;top:24px;max-width:420px;margin-left:auto;margin-right:0}
          .buybox .card{background:#fff;border:1px solid #ddd;border-radius:11px;box-shadow:0 4px 10px rgba(0,0,0,.06)}
          .mode-note{font-size:.85rem;color:#6b7280}
          .rating-row{display:flex;align-items:center;gap:.4rem}
          .rating-row .stars i{color:#549483}
          .stepper button{border:0;background:transparent;height:36px;font-size:20px}
          .stepper input{border:0;background:transparent;height:36px;text-align:center;font-weight:700;width:48px}
          .pills{display:flex;gap:.5rem;flex-wrap:wrap}
          .pill{border:1px solid #e5e7eb;background:#fff;border-radius:999px;padding:.3rem .7rem;cursor:pointer}
          .pill[aria-checked="true"]{outline:2px solid #549483;border-color:#549483}
          .btn-main{background:#549483;color:#fff;border:none}
          .btn-basket{background:#f1f3f5;color:#111827;border:1px solid #d0d5dd}
        </style>

        <aside class="buybox" id="buybox">
          <div class="card p-3 p-md-4">
            <div class="kicker mb-1">{{ ucfirst($type) }}</div>
            <h1 class="h4 m-0">{{ $title }}</h1>
            <div class="rating-row mt-2">
              <div class="stars" id="bbStars"></div>
              @if($rating)
                <div class="text-secondary small">{{ number_format((float)$rating,1) }}</div>
              @endif
              @if($reviewCount)
                <div class="text-secondary small">({{ $reviewCount }})</div>
              @endif
            </div>

            <div class="d-flex align-items-baseline gap-2 my-2">
              <div class="h4 m-0" id="bbPrice">£{{ $priceMin!==null ? number_format((float)$priceMin,2) : '0.00' }}</div>
              <div class="text-muted" id="bbCompare"></div>
            </div>
            @if(!empty($p['mode']))
              <div class="mode-note mb-2">{{ $p['mode']==='Online' ? 'Instant email delivery' : 'In-person session' }}</div>
            @endif

            <div id="bbOptions"></div>

            <div class="mb-3 d-flex align-items-center gap-3 mt-2">
              <label class="text-secondary small">Qty</label>
              <div class="stepper" aria-label="Quantity">
                <button type="button" id="bbDec">−</button>
                <input id="bbQty" value="1" inputmode="numeric">
                <button type="button" id="bbInc">+</button>
              </div>
            </div>

            <div class="d-grid gap-2 mb-2" id="bbCtas">
              <button class="btn btn-basket btn-lg" id="bbAdd">Add to basket</button>
              <button class="btn btn-main btn-lg" id="bbBuy">Buy now</button>
            </div>

            <div class="meta mb-2 mt-2 d-flex flex-wrap gap-3 text-muted">
              <div class="item d-flex align-items-center gap-2 small"><i class="bi bi-patch-check"></i><span>90-day validity</span></div>
              <div class="item d-flex align-items-center gap-2 small"><i class="bi bi-arrow-left-right"></i><span>Free exchanges</span></div>
            </div>
          </div>
        </aside>

        @if(!empty($p['reviews']))
          <div class="card p-4 mt-4">
            <h3 class="h6 m-0">Client reviews</h3>
            <div class="mt-3 d-grid gap-3">
              @foreach($p['reviews'] as $r)
                <div class="p-3 rounded border">
                  <div class="small text-muted">★ {{ (int)($r['rating'] ?? 0) }}/5</div>
                  <div class="mt-1">{{ $r['review'] ?? '' }}</div>
                </div>
              @endforeach
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</section>

@endsection

@push('scripts')
<script>
(function(){
  var opts = @json($options);
  var variants = @json($variants);
  var title = @json($title);
  function norm(v){ var n=Number(v); if(!Number.isFinite(n)) return null; if(n>=1000) n=n/100; return n }
  function fmtGBP(n){ try{ return new Intl.NumberFormat('en-GB',{style:'currency',currency:'GBP'}).format(n) }catch(e){ return '£'+Number(n||0).toFixed(2) } }

  var selected = (opts||[]).map(function(o){ return (o.values&&o.values[0]&& (o.values[0].value||o.values[0])) || '' });
  var qtyEl = document.getElementById('bbQty');
  var incEl = document.getElementById('bbInc');
  var decEl = document.getElementById('bbDec');
  var priceEl = document.getElementById('bbPrice');
  var compareEl = document.getElementById('bbCompare');
  var starsEl = document.getElementById('bbStars');
  var addBtn = document.getElementById('bbAdd');
  var buyBtn = document.getElementById('bbBuy');

  function renderStars(){
    if(!starsEl) return;
    starsEl.innerHTML='';
    var full = Math.round({{ (float)($rating ?? 0) }});
    for(var i=1;i<=5;i++){
      var iEl=document.createElement('i'); iEl.className = i<=full ? 'bi bi-star-fill' : 'bi bi-star';
      starsEl.appendChild(iEl);
    }
  }

  function findVariant(){
    var v = (variants||[]).find(function(v){
      var arr = v.options || [];
      return selected.every(function(val, idx){ return (arr[idx]||'') === val });
    });
    return v || (variants||[])[0] || null;
  }

  function updatePrice(){
    var v = findVariant();
    var unit = norm(v && v.price);
    var cmp = norm(v && v.compare);
    var qty = Math.max(1, parseInt(qtyEl.value||'1',10) || 1);
    if(unit!=null){ priceEl.textContent = fmtGBP(unit*qty); }
    else { priceEl.textContent = fmtGBP({{ $priceMin!==null ? (float)$priceMin : 0 }}*qty); }
    if(cmp && cmp>unit){ compareEl.textContent = fmtGBP(cmp*qty); compareEl.style.display='inline'; }
    else { compareEl.textContent=''; compareEl.style.display='none'; }
  }

  function buildOptions(){
    var wrap = document.getElementById('bbOptions'); if(!wrap) return; wrap.innerHTML='';
    (opts||[]).forEach(function(opt, idx){
      var lab = document.createElement('div'); lab.className='text-secondary small mb-1'; lab.textContent = opt.name || ('Option '+(idx+1)); wrap.appendChild(lab);
      var row = document.createElement('div'); row.className='pills mb-2'; wrap.appendChild(row);
      (opt.values||[]).forEach(function(val){
        var txt = (typeof val==='object' && val && 'value' in val) ? (val.value||'') : String(val||'');
        var b=document.createElement('button'); b.type='button'; b.className='pill'; b.setAttribute('aria-checked', selected[idx]===txt ? 'true':'false'); b.textContent=txt;
        b.addEventListener('click', function(){ selected[idx]=txt; row.querySelectorAll('.pill').forEach(function(p){p.setAttribute('aria-checked','false')}); b.setAttribute('aria-checked','true'); updatePrice(); });
        row.appendChild(b);
      });
    });
  }

  function wireQty(){
    if(incEl) incEl.addEventListener('click', function(){ var v = Math.max(1,(parseInt(qtyEl.value||'1',10)||1)+1); qtyEl.value=String(v); updatePrice(); });
    if(decEl) decEl.addEventListener('click', function(){ var v = Math.max(1,(parseInt(qtyEl.value||'1',10)||1)-1); qtyEl.value=String(v); updatePrice(); });
    if(qtyEl) qtyEl.addEventListener('input', function(){ this.value = this.value.replace(/[^0-9]/g,''); if(this.value==='') this.value='1'; updatePrice(); });
  }

  function wireCtas(){
    function toast(msg){ try{ alert(msg) }catch(e){} }
    if(addBtn) addBtn.addEventListener('click', function(e){ e.preventDefault(); toast('Added to your basket'); });
    if(buyBtn) buyBtn.addEventListener('click', function(e){ e.preventDefault(); toast('Proceeding to checkout…'); });
  }

  renderStars();
  buildOptions();
  wireQty();
  wireCtas();
  updatePrice();
})();
</script>
@endpush
