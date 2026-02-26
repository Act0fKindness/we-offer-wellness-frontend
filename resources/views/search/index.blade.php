@extends('layouts.app')

@section('content')

<section class="pt-4 pb-2 bg-transparent">
  <div class="container-page">
    <div class="wow-ultra">
      <form class="bar" role="search">
        <div class="seg" id="search-top-seg-what"><i class="bi bi-stars fs-5 text-muted"></i>
          <div class="flex-grow-1">
            <div class="seg-label">What</div>
            <input id="search-top-what" type="text" autocomplete="off" placeholder="Massage, yoga, breathwork…" aria-expanded="false" aria-controls="search-top-what-pane"></div>
          <div id="search-top-what-pane" class="pane narrow d-none" role="listbox" aria-label="What suggestions">
            <div id="search-top-what-list" class="listy">
              <div class="section-title">Experiences</div>
              <div>
                <button type="button" class="item" role="option" data-value="Sound Bath"><i class="bi bi-dot"></i><span class="title">Sound Bath</span><span class="type">Group</span></button>
                <button type="button" class="item" role="option" data-value="Massage"><i class="bi bi-dot"></i><span class="title">Massage</span><span class="type">Therapy</span></button>
                <button type="button" class="item" role="option" data-value="Breathwork"><i class="bi bi-dot"></i><span class="title">Breathwork</span><span class="type">Workshop</span></button>
              </div>
            </div>
          </div>
        </div>
        <div class="seg" id="search-top-seg-where"><i class="bi bi-geo-alt fs-5 text-muted"></i>
          <div class="flex-grow-1">
            <div class="seg-label">Where</div>
            <div id="search-top-where-editor" class="where-editor" contenteditable="true" data-placeholder="City, region, or 'Online'"></div>
            <input id="search-top-where" type="hidden"></div>
          <div id="search-top-where-pane" class="pane narrow d-none" role="listbox" aria-label="Trending places">
            <div class="section-title">Trending destinations</div>
            <div class="listy" id="search-top-where-list">
              <button type="button" class="item" data-value="Online"><i class="bi bi-wifi"></i><span class="title">Online</span><span class="text-muted ms-2">Virtual</span></button>
              <button type="button" class="item" data-value="London"><i class="bi bi-geo-alt"></i><span class="title">London</span><span class="text-muted ms-2">United Kingdom</span></button>
              <button type="button" class="item" data-value="Manchester"><i class="bi bi-geo-alt"></i><span class="title">Manchester</span><span class="text-muted ms-2">United Kingdom</span></button>
            </div>
          </div>
        </div>
        <div class="seg" id="search-top-seg-when"><i class="bi bi-calendar3 fs-5 text-muted"></i>
          <div class="flex-grow-1">
            <div class="seg-label">When</div>
            <input id="search-top-when" type="text" placeholder="Select dates" readonly aria-haspopup="dialog"></div>
          <div id="search-top-when-pane" class="pane d-none" aria-label="Calendar">
            <div class="cal-head">
              <button type="button" class="cal-col active" id="search-top-tab-calendar" aria-pressed="true">Calendar</button>
              <button type="button" class="cal-col" id="search-top-tab-flex" aria-pressed="false">I'm flexible</button>
            </div>
            <div class="cal-body">
              <div id="search-top-calendarMount"></div>
              <div class="flexible-pane" style="display:none;"><p class="mb-2">We’ll look across the next few weeks so you see more options.</p><p class="text-muted m-0">Switch back to Calendar for exact dates.</p></div>
            </div>
            <div class="cal-foot">
              <button type="button" class="chip chip-sm primary" id="search-top-chip-exact">Exact dates</button>
              <button type="button" class="chip chip-sm dur" data-days="1"><i class="bi bi-plus-lg"></i>1 day</button>
              <button type="button" class="chip chip-sm dur" data-days="2"><i class="bi bi-plus-lg"></i>2 days</button>
              <button type="button" class="chip chip-sm dur" data-days="3"><i class="bi bi-plus-lg"></i>3 days</button>
            </div>
          </div>
        </div>
        <div class="seg" id="search-top-seg-who"><i class="bi bi-person fs-5 text-muted"></i>
          <div class="flex-grow-1">
            <div class="seg-label">Who</div>
            <div id="search-top-who-summary" class="summary">2 adults · Couple</div>
          </div>
          <div id="search-top-who-pane" class="pane narrow d-none" aria-label="Guests">
            <div class="section-title">Guests</div>
            <div class="listy">
              <div class="item" style="justify-content: space-between;">
                <div>
                  <div class="fw-semibold">Adults</div>
                  <small class="text-muted">18+</small></div>
                <div class="counter">
                  <button type="button" class="btn btn-counter" data-dec="adults" aria-label="Decrease adults"><i class="bi bi-dash"></i></button>
                  <span id="search-top-adults-val" class="fw-semibold">2</span>
                  <button type="button" class="btn btn-counter" data-inc="adults" aria-label="Increase adults"><i class="bi bi-plus"></i></button>
                </div>
              </div>
              <div class="section-title">Group type</div>
              <div id="search-top-group-type-list">
                <button type="button" class="item" data-group="Solo" aria-selected="false"><i class="bi bi-person"></i><span class="title">Solo</span></button>
                <button type="button" class="item" data-group="Couple" aria-selected="true"><i class="bi bi-heart"></i><span class="title">Couple</span></button>
                <button type="button" class="item" data-group="Group" aria-selected="false"><i class="bi bi-people"></i><span class="title">Group</span></button>
              </div>
            </div>
            <div class="text-end p-3"><button type="button" class="btn btn-primary btn-sm" id="search-top-who-done">Done</button></div>
          </div>
        </div>
        <button class="btn-wow is-squarish btn-xl">
          <span class="btn-label">Search</span>
          <span class="btn-icon" aria-hidden="true">
            <svg class="icon-search" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
              <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/>
            </svg>
          </span>
          <span class="btn-spinner" aria-hidden="true"><span class="spin"></span></span>
        </button>
      </form>
      <!-- Controls moved under search bar -->
      <div class="search-controls d-flex align-items-center justify-content-between gap-2 mb-3 mt-2">
        <div class="d-flex align-items-center gap-2">
          <span class="font-semibold text-ink-800">View</span>
          <div class="seg-group" role="tablist" aria-label="List or Map">
              <button class="seg active" role="tab" aria-selected="true" data-view="map">Map</button>
              <button class="seg" role="tab" aria-selected="false" data-view="list">List</button>
          </div>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span class="font-semibold text-ink-800">Mode</span>
          <div class="seg-group" role="tablist" aria-label="Map Mode">
            <button class="seg" role="tab" aria-selected="false" data-mode="2d">2D</button>
            <button class="seg active" role="tab" aria-selected="true" data-mode="3d">3D</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-6 md:py-10">
  <div class="container-page space-y-4">
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
      <div>
        <div class="kicker mb-1 text-ink-600">Search results</div>
        <h1 class="text-ink-900" style="font-size:1.75rem;font-weight:700;">{{ $resultCount }} results</h1>
      </div>
      <div class="d-flex flex-wrap align-items-center gap-2" id="sr-tags"></div>
    </div>

    <div class="row gx-4 search-layout">
      <div class="col-12 col-lg-7 col-results">
        <div class="results-scroll">
          @if(isset($products) && $products->count())
            <div class="row g-3">
              @foreach($products as $product)
                <div class="col-12" data-pid="{{ $product->id }}">
                  <div class="wow-card-sm-wrap">
                    <div class="result-view-map">
                        @include('partials.product_card_list', ['product' => $product])
                    </div>
                    <div class="result-view-list">
                      @include('partials.product_card', ['product' => $product])
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="card p-6 text-ink-600">No results matched your filters. Try widening your search.</div>
          @endif
        </div>
      </div>
      <div class="col-12 col-lg-5 col-map">
        <div class="map-wrap">
          <div id="search-map" class="map"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
/* Desktop split: page scrolls the list; map stays sticky */
@media (min-width: 992px){
  .results-scroll{ padding-right: 6px; }
  .map-wrap{ position: sticky; top: 80px; }
  /* Adjust height to account for increased sticky top */
  .map{ width: 100%; height: calc(100vh - 80px - 24px); border: 1px solid var(--ink-200); border-radius: 3px; overflow: hidden; }
}
/* Segmented controls (search controls only) */
.search-controls .seg-group{ display:inline-flex; background:#f8fafc; border:1px solid var(--ink-200); border-radius:999px; padding:2px }
.search-controls .seg{ appearance:none; border:0; background:transparent; padding:6px 12px; border-radius:999px; color: var(--ink-700); font-weight:600; font-size:.9rem; transition: all .15s ease; }
.search-controls .seg:hover{ background:#eef2f7 }
.search-controls .seg.active, .search-controls .seg[aria-selected="true"]{ background: linear-gradient(180deg, #549483, #3b7768); color:#fff; box-shadow: 0 1px 0 rgba(255,255,255,.4) inset }
.search-controls .seg-group > .seg:first-of-type{ margin-right: 5px; }
/* removed global .wow-ultra override to prevent conflicts */
/* .wow-ultra .seg{ */
  flex: 1 1 220px;
  display: flex;
  gap: 10px;
  align-items: center;
  background: #fff;
  border: 1px solid #eeee;
  border-radius: 40px;
  padding: 5px 10px;
  position: relative;
  max-height: 58px;
}
/* Ensure active state wins over base .wow-ultra .seg background */
/* .wow-ultra .seg.active,
 .seg[aria-selected="true"]{
  background: linear-gradient(180deg, #549483, #3b7768) !important;
  color:#fff !important;
  box-shadow: 0 1px 0 rgba(255,255,255,.4) inset;
  border-color: transparent;
}*/
/* Custom map markers */
.wow-marker{ width: 34px; height: 34px; border-radius: 999px; background:#fff; border:1px solid rgba(16,24,40,.18); box-shadow: 0 14px 34px rgba(16,24,40,.18); display:flex; align-items:center; justify-content:center; position: relative; transform-origin: bottom center; will-change: transform; cursor: pointer; }
.mapboxgl-marker{ pointer-events: auto; z-index: 5; }
.wow-marker::after{ content:""; width:10px; height:10px; border-radius:999px; background:#549483; box-shadow: 0 0 0 5px rgba(84,56,255,.18); }
/* Desktop-only temporary glass styling for search bar */
@media (min-width: 992px){
  .wow-ultra .bar{
    background: rgba(255,255,255,.14);
    border: none;
    border-top: 1px solid rgba(255,255,255,.50);
    border-bottom: 1px solid rgba(0,0,0,.08);
    position: relative;
    -webkit-backdrop-filter: blur(14px);
    backdrop-filter: blur(14px);
    box-shadow: 0 14px 40px rgba(16,24,40,.14);
  }
  .wow-ultra .bar::before{
    content:"";
    position:absolute;
    inset:0;
    border-radius: inherit;
    pointer-events:none;
    background: linear-gradient(180deg, rgba(255,255,255,.28), rgba(255,255,255,.08));
    opacity:.55;
  }
  .wow-ultra .bar > *{ position: relative; z-index: 1; }
}
/* Active teardrop pin removed per request */
  .wow-marker.is-active{ transform: scale(1.06); border-color: rgba(84,56,255,.45); box-shadow: 0 18px 54px rgba(84,56,255,.24); }
/* Desktop default: show text label, hide icon on Search button */
.btn-wow.is-squarish.btn-xl .btn-label{ display:inline; }
.btn-wow.is-squarish.btn-xl .btn-icon{ display:none; }

/* Mobile: hide Where, When, Who segments; keep What + Search visible */
@media (max-width: 991.98px){
  #search-top-seg-where,
  #search-top-seg-when,
  #search-top-seg-who{ display: none !important; }
}
/* Hide/show columns for list/map view at all widths */
/* Map view shows both columns; List view hides map */
.search-layout.sr-list-only .col-map{ display:none; }
/* Toggle which card is shown per view */
.search-layout .result-view-map{ display:block; }
.search-layout .result-view-list{ display:none; }
.search-layout.sr-list-only .result-view-map{ display:none; }
.search-layout.sr-list-only .result-view-list{ display:block; }
/* Disabled seg buttons */
.seg[disabled], .seg[aria-disabled="true"]{ opacity: .5; cursor: not-allowed; }
@media (max-width: 991.98px){
  .search-layout.sr-map-only .col-results{ display:none }
  .search-layout.sr-list-only .col-map{ display:none }
  .col-map .map{ height: 60vh }
  /* Hide view/mode segmented controls on mobile */
  .search-controls{ display:none !important; }
  /* Mobile search bar tweaks */
  .wow-ultra .bar{
    background: rgba(255,255,255,.14);
    border-radius:40px;
    border:none;
    border-top: 1px solid rgba(255,255,255,0.5);
    border-bottom: 1px solid rgba(0,0,0,0.08);
    position: relative;
    -webkit-backdrop-filter: blur(14px);
    backdrop-filter: blur(14px);
    box-shadow: 0 14px 40px rgba(16,24,40,.14);
  }
  .wow-ultra .bar::before{ content:""; position:absolute; inset:0; border-radius: inherit; pointer-events:none; background: linear-gradient(180deg, rgba(255,255,255,.28), rgba(255,255,255,.08)); opacity:.55; }
  .wow-ultra .bar > *{ position: relative; z-index: 1; }
  /* Keep the whole search section anchored ~100px from the top for mobile */
  section.pt-4.pb-2.bg-transparent{ position: sticky; top:50px; z-index: 1000; }
  .wow-ultra .seg{ border-radius:40px; }
  .btn-wow.is-squarish.btn-xl{
    border-radius:50% !important;
    position:absolute; right:11px; top:50%; transform: translateY(-50%);
    display:inline-flex; align-items:center; justify-content:center;
    width:45px; height:45px; min-width:45px; min-height:45px; max-width:45px; max-height:45px;
    padding:0 !important; line-height:45px; overflow:hidden;
  }
  .btn-wow.is-squarish.btn-xl .btn-label{ display:none; }
  .btn-wow.is-squarish.btn-xl .btn-icon{ display:inline-flex; }
  .btn-wow.is-squarish.btn-xl .icon-search{ width:24px; height:24px; color:#fff; }
}
/* Search-only overrides for legacy wow-card md sizing */
.search-layout .wow-card.md{
  --card-h: 530px;
  width: auto;
  max-width: 309px;
}
/* Search result tags styled like product badges */
#sr-tags{ display:flex; flex-wrap:wrap; gap:8px; }
#sr-tags .badge{ height:32px; display:inline-flex; align-items:center; gap:4px; padding:0 10px; border-radius:3px; border:1px solid rgba(16,24,40,.10); font-weight:600; font-size:12px; line-height:1; white-space:nowrap; margin-bottom:10px; }
#sr-tags .badge--warm{ background:#ffe7c2; color:#6b4b12 }
#sr-tags .badge--cool{ background:#dfe9ff; color:#1f3a77 }
/* Unify search inputs typography to match "What" */
.wow-ultra #search-top-what,
.wow-ultra #search-top-where-editor,
.wow-ultra #search-top-when,
.wow-ultra #search-top-who-summary{
  font-family: 'Manrope', var(--bs-font-sans-serif) !important;
  font-weight: 500;
  color: var(--ink-900);
  font-size: 12px;
  line-height: 1.25;
}
</style>

<script>
(function(){
  try { (window.setupUltraSearchBar||function(){})('search-top') } catch(e){}
  // Mobile-specific search bar behavior
  try{
    var mq = window.matchMedia('(max-width: 991.98px)');
    function initMobileBar(){
      if (!mq.matches) return;
      // Ensure the What suggestions panel is closed by default on mobile
      try{
        var what = document.getElementById('search-top-what');
        var pane = document.getElementById('search-top-what-pane');
        if (what) what.setAttribute('aria-expanded','false');
        if (pane) pane.classList.add('d-none');
      }catch(_e){}
      // Default Where to Online if empty
      var whereEd = document.getElementById('search-top-where-editor');
      var whereInp = document.getElementById('search-top-where');
      var cur = (whereEd && whereEd.textContent || '').trim();
      if (whereEd && whereInp && cur === ''){
        whereEd.textContent = 'Online';
        whereInp.value = 'Online';
      }
    }
    initMobileBar();
    // Re-evaluate on viewport changes
    try{ mq.addEventListener ? mq.addEventListener('change', initMobileBar) : mq.addListener(initMobileBar); }catch(e){}
    // Close What pane on outside click (mobile)
    try{
      document.addEventListener('click', function(e){
        if (!mq.matches) return;
        var seg = document.getElementById('search-top-seg-what');
        var pane = document.getElementById('search-top-what-pane');
        var input = document.getElementById('search-top-what');
        if (!seg || !pane) return;
        if (!seg.contains(e.target)){
          // click outside -> close
          pane.classList.add('d-none');
          if (input) input.setAttribute('aria-expanded','false');
        }
      });
      // Open on focus
      var input = document.getElementById('search-top-what');
      var pane = document.getElementById('search-top-what-pane');
      if (input && pane){
        input.addEventListener('focus', function(){ if (!mq.matches) return; pane.classList.remove('d-none'); input.setAttribute('aria-expanded','true'); });
      }
    }catch(_e){}
  }catch(e){}
  // Tags from query for visual context
  try{
    var tagsEl = document.getElementById('sr-tags');
    function esc(s){ return String(s||'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c])); }
    var p = new URLSearchParams(window.location.search||'');
    var tags = [];
    var where = (p.get('where')||'').trim();
    var mode = (p.get('mode')||'').trim().toLowerCase();
    // Prefer a single "Online only" badge when mode=online, avoid duplicate with where=Online
    if (mode === 'online') {
      tags.push('Online only');
    } else if (where) {
      tags.push(where);
    }
    if (p.get('type')) tags.push(p.get('type'));
    if (p.get('tag')) tags.push('#' + p.get('tag'));
    if (p.get('price_max')) tags.push('Under £' + p.get('price_max'));
    // Dedupe ignoring case
    var seen = new Set();
    tags = tags.filter(function(t){ var k = String(t).toLowerCase(); if (seen.has(k)) return false; seen.add(k); return true; });
    if (tagsEl) tagsEl.innerHTML = tags.map(function(t){ return '<span class="badge badge--cool">'+esc(t)+'</span>'; }).join('');
  }catch{}

  // Map (Mapbox GL JS with 3D buildings)
  try {
    @php
      $mapData = [];
      foreach (($products ?? collect()) as $p) {
          // Prefer vendor locations (multiple pins)
          $vendor = $p->vendor ?? null;
          $locs = $vendor && $vendor->relationLoaded('locations') ? $vendor->locations : [];
          $count = 0;
          foreach ($locs as $vl) {
              $lat = $vl->lat ?? null; $lng = $vl->lng ?? null;
              if (is_numeric($lat) && is_numeric($lng)) {
                  $mapData[] = [
                      'pid' => $p->id,
                      'title' => $p->title,
                      'lat' => (float) $lat,
                      'lng' => (float) $lng,
                      'label' => trim(($vl->city ?? '') . ', ' . ($vl->address ?? '')),
                      'url' => url('/therapies/'.$p->id.'-'.\Illuminate\Support\Str::slug($p->title ?: (string)$p->id)),
                  ];
                  $count++;
              }
          }
          // Fallback to single meta lat/lng if no vendor pins
          if ($count === 0) {
              $m = $p->meta_json ?? [];
              $lat = $m['lat'] ?? null; $lng = $m['lng'] ?? null;
              if (is_numeric($lat) && is_numeric($lng)) {
                  $mapData[] = [
                      'pid' => $p->id,
                      'title' => $p->title,
                      'lat' => (float) $lat,
                      'lng' => (float) $lng,
                      'label' => $p->category->name ?? 'Location',
                      'url' => url('/therapies/'.$p->id.'-'.\Illuminate\Support\Str::slug($p->title ?: (string)$p->id)),
                  ];
              }
          }
      }
      $mapboxKey = config('services.mapbox.token');
    @endphp
    var data = @json($mapData);
    var token = @json($mapboxKey) || window.WOW_MAPS_KEY || '';
    var mapEl = document.getElementById('search-map');
    if (mapEl && !token) {
      try { mapEl.innerHTML = '<div style="padding:12px;color:#334155;font-size:14px;">Map unavailable: missing MAPBOX_API_KEY. Set it in .env.</div>'; } catch(e){}
    }
    if (mapEl && token) {
      function initMapbox(){
        try{
          mapboxgl.accessToken = token;
          var center = data.length ? [Number(data[0].lng), Number(data[0].lat)] : [-0.1276, 51.5072];
          var map = new mapboxgl.Map({
            container: mapEl,
            style: 'mapbox://styles/mapbox/streets-v12',
            center: center,
            zoom: 13,
            pitch: 0,
            bearing: 0,
            antialias: true,
            fadeDuration: 0
          });
          map.on('load', function(){
            // 3D buildings layer
            var layers = map.getStyle().layers;
            var labelLayerId;
            for (var i = 0; i < layers.length; i++) {
              if (layers[i].type === 'symbol' && layers[i].layout['text-field']) { labelLayerId = layers[i].id; break; }
            }
            map.addLayer({
              'id': '3d-buildings',
              'source': 'composite',
              'source-layer': 'building',
              'filter': ['==', 'extrude', 'true'],
              'type': 'fill-extrusion',
              'minzoom': 15,
              'paint': {
                'fill-extrusion-color': '#aaa',
                'fill-extrusion-height': ['get', 'height'],
                'fill-extrusion-base': ['get', 'min_height'],
                'fill-extrusion-opacity': 0.6
              }
            }, labelLayerId);
            // Markers (grouped by product id for hover effects and navigation)
            window.__wowMarkersByPid = {};
            var bounds = new mapboxgl.LngLatBounds();
            var added = 0;
            data.forEach(function(p){
              var el = document.createElement('div');
              el.className = 'wow-marker';
              el.title = p.title || '';
              try{ el.style.zIndex = '5'; el.style.cursor = 'pointer'; }catch(_e){}
              var marker = new mapboxgl.Marker({ element: el, anchor: 'bottom' }).setLngLat([Number(p.lng), Number(p.lat)]).setPopup(new mapboxgl.Popup({ offset: 8 }).setHTML('<div style="font-weight:600">'+(p.title||'')+'</div>')).addTo(map);
              var pid = String(p.pid||p.id||'');
              (window.__wowMarkersByPid[pid] = window.__wowMarkersByPid[pid] || []).push({ marker: marker, el: el });
              // Click marker -> scroll to corresponding list item and lightly zoom
              try {
                el.addEventListener('click', function(e){
                  try{ e.stopPropagation(); }catch(_e){}
                  var item = document.querySelector('[data-pid="'+pid+'"]');
                  if (item) {
                    item.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    // Optional: add a transient highlight
                    item.classList.add('is-active');
                    setTimeout(function(){ try{ item.classList.remove('is-active'); }catch(_){ } }, 1200);
                  }
                  try{ map.easeTo({ center: [Number(p.lng), Number(p.lat)], zoom: Math.max(map.getZoom(), 14), duration: 500 }); }catch(_e){}
                  try {
                    // Toggle marker active state
                    Object.keys(window.__wowMarkersByPid||{}).forEach(function(k){
                      (window.__wowMarkersByPid[k]||[]).forEach(function(m){ m.el.classList.toggle('is-active', k===pid); });
                    });
                  } catch(_e){}
                });
              } catch(_e){}
              try { bounds.extend([Number(p.lng), Number(p.lat)]); added++; } catch(e){}
            });
            // Expose map so other handlers can reference it
            window.__wowMap = map;
            // Helper: center map on pid's first marker
            function centerOnPid(pid, opts){
              try{
                var group = (window.__wowMarkersByPid||{})[String(pid)]||[];
                if (!group.length) return;
                var ll = group[0].marker.getLngLat();
                map.easeTo({ center: [ll.lng, ll.lat], zoom: Math.max(map.getZoom(), (opts&&opts.zoom)||14), duration: (opts&&opts.duration)||500 });
                // toggle active state
                Object.keys(window.__wowMarkersByPid||{}).forEach(function(k){
                  (window.__wowMarkersByPid[k]||[]).forEach(function(m){ m.el.classList.toggle('is-active', k===String(pid)); });
                });
              }catch(_e){}
            }
            try {
              if (added > 1) {
                map.fitBounds(bounds, { padding: 100, maxZoom: 13, duration: 500 });
              } else if (added === 1) {
                var only = data[0];
                map.setCenter([Number(only.lng), Number(only.lat)]);
                map.setZoom(14);
              }
            } catch(e){}
            // After markers are added, center near the first product in the list
            try{
              var firstItem = document.querySelector('.results-scroll [data-pid]');
              if (firstItem) { centerOnPid(firstItem.getAttribute('data-pid'), { zoom: 14, duration: 300 }); }
            }catch(_e){}

          });
          // Mode toggle (2D/3D)
          document.querySelectorAll('[data-mode]')?.forEach(function(btn){
            btn.addEventListener('click', function(){
              document.querySelectorAll('[data-mode]')?.forEach(b=>{ b.classList.remove('active'); b.setAttribute('aria-selected','false') })
              btn.classList.add('active'); btn.setAttribute('aria-selected','true')
              var mode = btn.getAttribute('data-mode');
              if (mode === '3d') { map.easeTo({ pitch: 60, bearing: -17, duration: 600 }) }
              else { map.easeTo({ pitch: 0, bearing: 0, duration: 600 }) }
            })
          })
          // Ensure initial UI reflects 2D default
          try {
            var btn3d = document.querySelector('[data-mode="3d"]');
            var btn2d = document.querySelector('[data-mode="2d"]');
            if (btn3d && btn2d) {
              btn3d.classList.remove('active'); btn3d.setAttribute('aria-selected','false');
              btn2d.classList.add('active'); btn2d.setAttribute('aria-selected','true');
            }
          } catch(e){}
        }catch(e){ console.warn('mapbox init failed', e) }
      }
      // Ensure Mapbox CSS is present, then load/init JS
      (function ensureCssThenInit(){
        var hasCss = !!document.querySelector('link[href*="mapbox-gl.css"]');
        if (!hasCss) {
          var l = document.createElement('link'); l.rel='stylesheet'; l.href='https://api.mapbox.com/mapbox-gl-js/v3.6.0/mapbox-gl.css'; document.head.appendChild(l)
        }
        if (!window.mapboxgl) {
          var s = document.createElement('script'); s.src='https://api.mapbox.com/mapbox-gl-js/v3.6.0/mapbox-gl.js'; s.async=true; s.defer=true; s.onload=initMapbox; document.head.appendChild(s)
        } else { initMapbox() }
      })();
    }
  } catch (e) { console.warn('map skipped', e) }

  // List/Map toggle + disable mode when list
  try {
    var layout = document.querySelector('.search-layout');
    var modeButtons = Array.from(document.querySelectorAll('[data-mode]'));
    var resultsCol = document.querySelector('.search-layout .col-results');
    var mapCol = document.querySelector('.search-layout .col-map');
    function setModeEnabled(enabled){
      modeButtons.forEach(function(b){
        if (enabled) { b.removeAttribute('disabled'); b.setAttribute('aria-disabled','false'); }
        else { b.setAttribute('disabled','disabled'); b.setAttribute('aria-disabled','true'); }
      });
    }
    function setColsForView(view){
      if (!resultsCol || !mapCol) return;
      if (view === 'list') {
        // results full width
        resultsCol.classList.remove('col-lg-7');
        resultsCol.classList.add('col-lg-12');
      } else {
        // side by side: 7/5 split
        resultsCol.classList.remove('col-lg-12');
        resultsCol.classList.add('col-lg-7');
      }
    }
    function setItemCols(view){
      var items = document.querySelectorAll('.results-scroll .row > div');
      items.forEach(function(it){
        if (view === 'list') {
          it.classList.remove('col-12');
          it.classList.add('col-sm-6','col-lg-3');
        } else {
          it.classList.remove('col-sm-6','col-lg-3');
          it.classList.add('col-12');
        }
      });
    }
    // Hover markers when hovering product items
    function bindHover(){
      var container = document.querySelector('.results-scroll');
      if(!container) return;
      container.addEventListener('mouseover', function(e){
        var item = e.target.closest('[data-pid]');
        if(!item) return;
        var pid = item.getAttribute('data-pid');
        var group = (window.__wowMarkersByPid||{})[String(pid)]||[];
        group.forEach(function(m){ m.el.classList.add('is-active'); });
        // Center map on hovered product's marker
        try{
          var map = window.__wowMap;
          if (map && group.length){
            var ll = group[0].marker.getLngLat();
            map.easeTo({ center: [ll.lng, ll.lat], zoom: Math.max(map.getZoom(), 13), duration: 300 });
          }
        }catch(_e){}
      });
      container.addEventListener('mouseout', function(e){
        var item = e.target.closest('[data-pid]');
        if(!item) return;
        var pid = item.getAttribute('data-pid');
        var group = (window.__wowMarkersByPid||{})[String(pid)]||[];
        group.forEach(function(m){ m.el.classList.remove('is-active'); });
      });
    }
    // Ensure map view buttons work and don't bubble to any parent click areas
    try{
      document.addEventListener('click', function(e){
        var add = e.target.closest('.result-view-map .js-add-to-cart');
        if (add) { try{ e.stopPropagation(); }catch(_e){} }
        var buy = e.target.closest('.result-view-map .js-buy-now');
        if (buy) {
          try{ e.stopPropagation(); }catch(_e){}
          var url = buy.getAttribute('data-url');
          if (url) { window.location.href = url; }
        }
      }, true);
    }catch(_e){}
    // Track scroll: keep map focused on product at top of list
    function bindScrollTracking(){
      var cont = document.querySelector('.results-scroll');
      if (!cont) return;
      var ticking = false, lastPid = null;
      function onScroll(){
        if (ticking) return; ticking = true;
        requestAnimationFrame(function(){
          try{
            var rect = cont.getBoundingClientRect();
            var items = Array.from(cont.querySelectorAll('[data-pid]'));
            var topItem = null; var topDelta = Infinity;
            items.forEach(function(it){
              var r = it.getBoundingClientRect();
              var d = Math.abs(r.top - rect.top);
              if (d < topDelta){ topDelta = d; topItem = it; }
            });
            if (topItem){
              var pid = String(topItem.getAttribute('data-pid'));
              if (pid !== lastPid){
                lastPid = pid;
                // Center map on this pid and mark active
                try{ (window.centerOnPid||window.__centerOnPid||function(){
                  var group=(window.__wowMarkersByPid||{})[pid]||[]; if(!group.length) return; var ll=group[0].marker.getLngLat(); var map=window.__wowMap; if(map){ map.easeTo({ center:[ll.lng,ll.lat], zoom: Math.max(map.getZoom(), 13), duration: 400 }); } Object.keys(window.__wowMarkersByPid||{}).forEach(function(k){ (window.__wowMarkersByPid[k]||[]).forEach(function(m){ m.el.classList.toggle('is-active', k===pid); }); });
                })(); }catch(_e){}

              }
            }
          }catch(_e){}
          ticking = false;
        });
      }
      cont.addEventListener('scroll', onScroll, { passive: true });
      // Run once to sync
      try{ onScroll(); }catch(_e){}
    }
    // Initial state: Map active -> enable mode, show both columns
    setModeEnabled(true);
    setColsForView('map');
    setItemCols('map');
    // Ensure buttons reflect Map active
    try{
      var viewBtns = document.querySelectorAll('[data-view]');
      viewBtns.forEach(function(b){ b.classList.remove('active'); b.setAttribute('aria-selected','false'); });
      var mapBtn = document.querySelector('[data-view="map"]');
      if(mapBtn){ mapBtn.classList.add('active'); mapBtn.setAttribute('aria-selected','true'); }
      layout.classList.remove('sr-list-only');
    }catch(e){}
    document.querySelectorAll('[data-view]')?.forEach(function(btn){
      btn.addEventListener('click', function(){
        document.querySelectorAll('[data-view]')?.forEach(b=>{ b.classList.remove('active'); b.setAttribute('aria-selected','false') })
        btn.classList.add('active'); btn.setAttribute('aria-selected','true')
        var v = btn.getAttribute('data-view');
        if (!layout) return;
        layout.classList.remove('sr-list-only');
        if (v === 'map') {
          // Map view: show both columns
          setModeEnabled(true);
          setColsForView('map');
          setItemCols('map');
        } else {
          // List view: hide map column
          layout.classList.add('sr-list-only');
          setModeEnabled(false);
          setColsForView('list');
          setItemCols('list');
        }
      })
    })
    bindHover();
    bindScrollTracking();
  } catch {}
})();
</script>

@endsection
