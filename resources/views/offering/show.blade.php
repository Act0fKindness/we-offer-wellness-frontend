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
  // Extract Location(s) list from options
  $locationValues = [];
  foreach (($options ?? []) as $opt) {
    $name = isset($opt['name']) ? strtolower($opt['name']) : '';
    if (str_contains($name, 'location')) {
      foreach (($opt['values'] ?? []) as $v) {
        $val = is_array($v) ? ($v['value'] ?? '') : (string) $v;
        $val = trim($val);
        if ($val !== '') { $locationValues[] = $val; }
      }
    }
  }
  // Make unique while preserving order
  $seen = [];
  $locationsList = [];
  foreach ($locationValues as $lv) {
    $k = strtolower($lv);
    if (!isset($seen[$k])) { $seen[$k] = true; $locationsList[] = $lv; }
  }
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
          <h3 class="h5">About this experiences</h3>
          <div class="prose" style="max-width: 70ch;">{!! $body !!}</div>
        </div>
        @endif

        @if(!empty($locationsList))
        <div class="card p-4 mt-4">
          <h3 class="h5">Locations</h3>
          <div class="row g-3 mt-1">
            <div class="col-12 col-lg-5">
              <div id="locList" class="d-grid gap-2">
                @foreach($locationsList as $i => $loc)
                  <button type="button" class="btn btn-outline-secondary text-start loc-item {{ $i===0 ? 'active' : '' }}" data-loc="{{ $loc }}">{{ $loc }}</button>
                @endforeach
              </div>
            </div>
            <div class="col-12 col-lg-7">
              <div class="ratio ratio-4x3 rounded border overflow-hidden">
                <iframe id="locMap" src="" style="border:0;width:100%;height:100%" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
              </div>
            </div>
          </div>
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
        @include('offering.partials.advanced_buybox')
        @include('offering.partials.variant_helper')

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
  try{
    var list = document.getElementById('locList');
    var map = document.getElementById('locMap');
    if(!list || !map) return;
    function setActive(btn){
      list.querySelectorAll('.loc-item').forEach(function(b){ b.classList.remove('active'); });
      btn.classList.add('active');
    }
    function isOnline(val){ return String(val||'').trim().toLowerCase()==='online'; }
    function norm(s){ return String(s||'').trim().toLowerCase().replace(/[^a-z0-9\s]+/g,''); }
    function tokens(s){ return norm(s).split(/\s+/).filter(Boolean); }
    function overlap(a,b){ var A=new Set(tokens(a)), B=new Set(tokens(b)); var c=0; A.forEach(t=>{ if(B.has(t)) c++; }); return c; }
    function looseEq(a,b){ var A=norm(a), B=norm(b); if(!A||!B) return A===B; if(A.includes('online')||B.includes('online')) return A.includes('online') && B.includes('online'); if(A.includes(B)||B.includes(A)) return true; return overlap(a,b)>=2; }
    function updateMap(loc){
      if(!map) return;
      if(isOnline(loc)){
        // Show a generic world map or message
        map.src = 'https://www.google.com/maps?q=World&output=embed';
      } else {
        var q = encodeURIComponent(String(loc||''));
        map.src = 'https://www.google.com/maps?q='+q+'&output=embed';
      }
    }
    // Init
    var first = list.querySelector('.loc-item');
    if(first){ updateMap(first.dataset.loc||first.textContent||''); }
    list.addEventListener('click', function(e){
      var btn = e.target.closest('.loc-item'); if(!btn) return;
      setActive(btn);
      updateMap(btn.dataset.loc||btn.textContent||'');
      // Also push selection to Buy Box via event so price/variant match if location affects variants
      try{
        var locVal = String(btn.dataset.loc||btn.textContent||'');
        document.dispatchEvent(new CustomEvent('wow:setLocation', { detail: { location: locVal } }));
      }catch(e){}
    });

    // Keep Locations list in sync when selection changes elsewhere
    document.addEventListener('wow:selected', function(ev){
      try {
        var opts = ev?.detail?.options || [];
        if(!Array.isArray(opts) || !opts.length) return;
        var buttons = list.querySelectorAll('.loc-item'); if(!buttons.length) return;
        // Try to pick the option that looks like a location (not numeric, not Online)
        var locCandidate = null;
        for (var i=0; i<opts.length; i++) {
          var s = String(opts[i]||''); var n = norm(s);
          if (n && n!=='online' && !/^\d+$/.test(n)) { locCandidate = s; break; }
        }
        // Score buttons by overlap with the candidate (or with any opt if not found)
        var bestBtn=null, bestScore=-1;
        buttons.forEach(function(b){
          var loc = b.dataset.loc||b.textContent||'';
          if (locCandidate) {
            var sc = overlap(loc, locCandidate);
            if (sc>bestScore) { bestScore=sc; bestBtn=b; }
          } else {
            opts.forEach(function(o){ var sc = overlap(loc, o); if(sc>bestScore){ bestScore=sc; bestBtn=b; } });
          }
        });
        if(bestBtn){ setActive(bestBtn); updateMap(bestBtn.dataset.loc||bestBtn.textContent||''); }
      } catch(e){}
    });
  }catch(e){}
})();
</script>
@endpush
