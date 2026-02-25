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
        <button class="btn-wow is-squarish btn-xl"><span class="btn-label">Search</span><span class="btn-spinner" aria-hidden="true"><span class="spin"></span></span></button>
      </form>
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
                <div class="col-12">
                  <div class="wow-card-sm-wrap">
                    @include('partials.product_card_sm', ['product' => $product])
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
        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
          <div class="d-flex align-items-center gap-2">
            <span class="font-semibold text-ink-800">View</span>
            <div class="seg-group" role="tablist" aria-label="List or Map">
              <button class="seg active" role="tab" aria-selected="true" data-view="list">List</button>
              <button class="seg" role="tab" aria-selected="false" data-view="map">Map</button>
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
        <div class="map-wrap">
          <div id="search-map" class="map"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
/* Desktop split: results (7 cols) scrollable; map (5 cols) sticky */
@media (min-width: 992px){
  .results-scroll{ max-height: calc(100vh - 220px); overflow-y: auto; padding-right: 6px; }
  .map-wrap{ position: sticky; top: 84px; height: calc(100vh - 100px); }
  .map{ width: 100%; height: 100%; border: 1px solid var(--ink-200); border-radius: 12px; overflow: hidden; }
}
@media (max-width: 991.98px){
  .search-layout.sr-map-only .col-results{ display:none }
  .search-layout.sr-list-only .col-map{ display:none }
  .col-map .map{ height: 60vh }
}
</style>

<script>
(function(){
  try { (window.setupUltraSearchBar||function(){})('search-top') } catch(e){}
  // Tags from query for visual context
  try{
    var tagsEl = document.getElementById('sr-tags');
    function esc(s){ return String(s||'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c])); }
    var p = new URLSearchParams(window.location.search||''); var tags=[];
    if(p.get('where')) tags.push(p.get('where'));
    if(p.get('mode')) tags.push(p.get('mode').toLowerCase()==='online'?'Online only':'In person');
    if(p.get('type')) tags.push(p.get('type'));
    if(p.get('tag')) tags.push('#'+p.get('tag'));
    if(p.get('price_max')) tags.push('Under £'+p.get('price_max'));
    if(tagsEl) tagsEl.innerHTML = tags.map(t => '<span class="chip">'+esc(t)+'</span>').join('');
  }catch{}

  // Map (Mapbox GL JS with 3D buildings)
  try {
    @php
      $mapData = [];
      foreach (($products ?? collect()) as $p) {
          $m = $p->meta_json ?? [];
          $lat = $m['lat'] ?? null; $lng = $m['lng'] ?? null;
          if (is_numeric($lat) && is_numeric($lng)) {
              $mapData[] = [
                  'id' => $p->id,
                  'title' => $p->title,
                  'lat' => (float) $lat,
                  'lng' => (float) $lng,
                  'url' => url('/therapies/'.$p->id.'-'.\Illuminate\Support\Str::slug($p->title ?: (string)$p->id)),
              ];
          }
      }
      $mapboxKey = env('MAPBOX_API_KEY');
    @endphp
    var data = @json($mapData);
    var token = @json($mapboxKey);
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
            zoom: 11,
            pitch: 60,
            bearing: -17,
            antialias: true
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
            // Markers
            data.forEach(function(p){
              var m = new mapboxgl.Marker().setLngLat([Number(p.lng), Number(p.lat)]).setPopup(new mapboxgl.Popup({ offset: 8 }).setHTML('<div style="font-weight:600">'+(p.title||'')+'</div>')).addTo(map);
            });
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
          // Ensure initial UI reflects 3D default
          try {
            var btn3d = document.querySelector('[data-mode="3d"]');
            var btn2d = document.querySelector('[data-mode="2d"]');
            if (btn3d && btn2d) {
              btn2d.classList.remove('active'); btn2d.setAttribute('aria-selected','false');
              btn3d.classList.add('active'); btn3d.setAttribute('aria-selected','true');
            }
          } catch(e){}
        }catch(e){ console.warn('mapbox init failed', e) }
      }
      // load mapbox css/js if needed
      if (!window.mapboxgl) {
        var l = document.createElement('link'); l.rel='stylesheet'; l.href='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css'; document.head.appendChild(l)
        var s = document.createElement('script'); s.src='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js'; s.async=true; s.defer=true; s.onload=initMapbox; document.head.appendChild(s)
      } else { initMapbox() }
    }
  } catch (e) { console.warn('map skipped', e) }

  // List/Map toggle (primarily for mobile)
  try {
    var layout = document.querySelector('.search-layout');
    document.querySelectorAll('[data-view]')?.forEach(function(btn){
      btn.addEventListener('click', function(){
        document.querySelectorAll('[data-view]')?.forEach(b=>{ b.classList.remove('active'); b.setAttribute('aria-selected','false') })
        btn.classList.add('active'); btn.setAttribute('aria-selected','true')
        var v = btn.getAttribute('data-view');
        if (!layout) return;
        layout.classList.remove('sr-map-only','sr-list-only');
        if (v === 'map') layout.classList.add('sr-map-only'); else layout.classList.add('sr-list-only');
      })
    })
  } catch {}
})();
</script>

@endsection
