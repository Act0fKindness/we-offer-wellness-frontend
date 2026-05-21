{{-- resources/views/locations/index.blade.php --}}
@extends('layouts.app')

@php
  $mapboxKey = config('services.mapbox.token');
  $resolved = $locationSearch ?? null;
  $results = collect($locations ?? []);
  $physicalResults = $results->filter(fn ($location) => !($location['online'] ?? false))->values();
  $onlineResult = $results->first(fn ($location) => ($location['online'] ?? false) === true);
  $mapItems = $physicalResults->take(8)->map(function ($location) {
    return [
      'title' => $location['title'] ?? '',
      'slug' => $location['slug'] ?? '',
      'lat' => $location['lat'] ?? null,
      'lng' => $location['lng'] ?? null,
      'distance' => $location['distance_label'] ?? null,
    ];
  })->values()->all();
@endphp

@push('head')
  <title>{{ $seo['title'] ?? 'Locations | We Offer Wellness®' }}</title>
  @if(!empty($seo['description']))<meta name="description" content="{{ $seo['description'] }}">@endif
  @if(!empty($seo['robots']))<meta name="robots" content="{{ $seo['robots'] }}">@endif
  @if(!empty($seo['canonical']))<link rel="canonical" href="{{ $seo['canonical'] }}">@endif
  <style>
    .wow-locations-page{
      position:relative;
      overflow:hidden;
      background:#fff;
      color:#101828;
      padding:44px 0 72px;
    }
    .wow-page-grid{
      position:absolute;
      inset:0;
      width:min(100% - 40px, 1360px);
      margin:0 auto;
      pointer-events:none;
      border-left:1px solid rgba(17,24,39,.08);
      border-right:1px solid rgba(17,24,39,.08);
      background-image:
        linear-gradient(to right, transparent calc(25% - 1px), rgba(17,24,39,.08) calc(25% - 1px), rgba(17,24,39,.08) 25%, transparent 25%),
        linear-gradient(to right, transparent calc(50% - 1px), rgba(17,24,39,.08) calc(50% - 1px), rgba(17,24,39,.08) 50%, transparent 50%),
        linear-gradient(to right, transparent calc(75% - 1px), rgba(17,24,39,.08) calc(75% - 1px), rgba(17,24,39,.08) 75%, transparent 75%);
    }
    .wow-page-grid::before,
    .wow-page-grid::after{
      content:"";
      position:absolute;
      top:0;
      bottom:0;
      width:1px;
      border-left:1px dashed rgba(17,24,39,.14);
    }
    .wow-page-grid::before{ left:25%; }
    .wow-page-grid::after{ left:75%; }
    .wow-locations-container{
      position:relative;
      z-index:1;
      width:min(100% - 40px, 1360px);
      margin:0 auto;
      font-family:'Manrope', system-ui, sans-serif;
    }
    .wow-locations-hero{
      display:grid;
      grid-template-columns:minmax(0,1.1fr) minmax(290px,.6fr);
      gap:30px;
      align-items:end;
      margin-bottom:24px;
    }
    .wow-kicker{
      margin:0 0 10px;
      color:#344054;
      font-size:13px;
      font-weight:700;
      letter-spacing:.16em;
      text-transform:uppercase;
    }
    .wow-locations-hero h1,
    .wow-locations-section h2,
    .wow-location-card h3{
      margin:0;
      color:#101828;
      font-family:'Playfair Display', Georgia, serif;
      font-weight:500;
      letter-spacing:-.05em;
    }
    .wow-locations-hero h1{
      font-size:clamp(46px, 5.6vw, 78px);
      line-height:.96;
      max-width:860px;
    }
    .wow-locations-hero p{
      max-width:760px;
      margin:16px 0 0;
      color:#596275;
      font-size:18px;
      line-height:1.58;
    }
    .wow-search-panel{
      background:rgba(255,255,255,.98);
      border:1px solid #dfe4ea;
      border-radius:4px;
      box-shadow:0 12px 34px rgba(16,24,40,.035);
      padding:18px;
    }
    .wow-search-panel__label{
      display:block;
      margin-bottom:8px;
      color:#344054;
      font-size:13px;
      font-weight:700;
      text-transform:uppercase;
      letter-spacing:.1em;
    }
    .wow-search-panel__row{
      display:grid;
      grid-template-columns:1fr auto;
      gap:10px;
      align-items:start;
    }
    .wow-search-panel input{
      width:100%;
      height:46px;
      border:1px solid #d0d5dd;
      border-radius:4px;
      background:#fff;
      color:#111827;
      padding:0 14px;
      font-size:15px;
      outline:none;
    }
    .wow-search-panel input:focus{
      border-color:#4f9381;
      box-shadow:0 0 0 3px rgba(79,147,129,.14);
    }
    .wow-search-panel button{
      min-height:46px;
      padding:0 20px;
      border-radius:4px;
      border:1px solid #4f9381;
      background:#4f9381;
      color:#fff;
      font-weight:700;
      cursor:pointer;
    }
    .wow-search-panel button:hover{
      background:#417c6d;
      border-color:#417c6d;
    }
    .wow-search-panel__helper{
      margin-top:10px;
      color:#667085;
      font-size:13px;
      line-height:1.45;
    }
    .wow-search-panel__menu{
      position:relative;
    }
    .wow-search-panel__dropdown{
      position:absolute;
      left:0;
      right:0;
      top:calc(100% + 6px);
      z-index:20;
      background:#fff;
      border:1px solid #dfe4ea;
      border-radius:4px;
      box-shadow:0 16px 36px rgba(16,24,40,.08);
      overflow:hidden;
    }
    .wow-search-panel__dropdown button{
      width:100%;
      border:0;
      border-bottom:1px solid #edf0f2;
      background:#fff;
      text-align:left;
      color:#101828;
      padding:12px 14px;
      min-height:auto;
      border-radius:0;
      display:block;
    }
    .wow-search-panel__dropdown button:hover{
      background:#f8fafc;
    }
    .wow-search-panel__dropdown strong{
      display:block;
      font-size:14px;
      line-height:1.4;
      margin-bottom:2px;
    }
    .wow-search-panel__dropdown span{
      display:block;
      color:#667085;
      font-size:12px;
      line-height:1.35;
    }
    .wow-locations-layout{
      display:grid;
      grid-template-columns:minmax(0,.72fr) minmax(0,1.28fr);
      gap:24px;
      margin-top:26px;
    }
    .wow-locations-sidebar{
      display:flex;
      flex-direction:column;
      gap:16px;
    }
    .wow-locations-map,
    .wow-search-summary,
    .wow-location-card,
    .wow-online-card,
    .wow-empty-card,
    .wow-directory-card{
      background:rgba(255,255,255,.98);
      border:1px solid #dfe4ea;
      border-radius:4px;
      box-shadow:0 12px 34px rgba(16,24,40,.035);
    }
    .wow-search-summary{
      padding:18px;
    }
    .wow-search-summary p{
      margin:0;
      color:#596275;
      font-size:15px;
      line-height:1.55;
    }
    .wow-search-summary strong{
      display:block;
      margin-bottom:6px;
      color:#101828;
      font-size:18px;
      font-family:'Playfair Display', Georgia, serif;
      font-weight:500;
    }
    .wow-search-summary__meta{
      display:flex;
      flex-wrap:wrap;
      gap:8px;
      margin-top:14px;
    }
    .wow-pill{
      min-height:30px;
      display:inline-flex;
      align-items:center;
      padding:0 10px;
      border:1px solid #d9dee7;
      border-radius:999px;
      background:#fff;
      color:#344054;
      font-size:12px;
      font-weight:700;
    }
    .wow-locations-map{
      overflow:hidden;
      min-height:480px;
      position:relative;
    }
    .wow-locations-map__canvas{
      width:100%;
      height:480px;
      min-height:480px;
    }
    .wow-locations-section{
      margin-top:28px;
    }
    .wow-locations-section h2{
      font-size:clamp(28px, 3.2vw, 44px);
      line-height:1;
      margin-bottom:14px;
    }
    .wow-locations-section__copy{
      color:#596275;
      font-size:16px;
      line-height:1.55;
      max-width:860px;
      margin:0 0 18px;
    }
    .wow-location-list{
      display:grid;
      grid-template-columns:repeat(2, minmax(0, 1fr));
      gap:16px;
    }
    .wow-location-card{
      display:flex;
      flex-direction:column;
      justify-content:space-between;
      min-height:240px;
      padding:20px;
    }
    .wow-location-card__top{
      display:flex;
      justify-content:space-between;
      gap:12px;
      margin-bottom:18px;
    }
    .wow-location-card__title{
      font-size:32px;
      line-height:.98;
    }
    .wow-location-card__label{
      align-self:flex-start;
      min-height:30px;
      display:inline-flex;
      align-items:center;
      padding:0 10px;
      border-radius:4px;
      background:#e8f0ff;
      color:#254a85;
      border:1px solid #c7d8fb;
      font-size:12px;
      font-weight:700;
      white-space:nowrap;
    }
    .wow-location-card__copy{
      margin:14px 0 0;
      color:#596275;
      font-size:15px;
      line-height:1.55;
    }
    .wow-location-card__meta{
      display:flex;
      flex-wrap:wrap;
      gap:8px;
      margin-top:18px;
    }
    .wow-location-card__footer{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      margin-top:18px;
      padding-top:16px;
      border-top:1px solid #edf0f2;
    }
    .wow-location-card__footer small{
      color:#667085;
      font-size:13px;
      line-height:1.45;
    }
    .wow-online-card{
      padding:20px;
      border-left:4px solid #4f9381;
    }
    .wow-online-card h3{
      font-size:36px;
      line-height:.98;
    }
    .wow-online-card p{
      margin:12px 0 0;
      color:#596275;
      font-size:15px;
      line-height:1.55;
    }
    .wow-directory-grid{
      display:grid;
      grid-template-columns:repeat(3, minmax(0, 1fr));
      gap:16px;
    }
    .wow-directory-card{
      padding:20px;
      text-decoration:none;
      color:inherit;
    }
    .wow-directory-card h3{
      font-size:28px;
      line-height:1;
      margin-bottom:12px;
    }
    .wow-directory-card p{
      margin:0;
      color:#596275;
      font-size:15px;
      line-height:1.55;
    }
    .wow-directory-card span{
      display:inline-flex;
      margin-top:18px;
      color:#4f9381;
      font-size:14px;
      font-weight:700;
    }
    .wow-empty-card{
      padding:24px;
    }
    .wow-empty-card p{
      margin:0;
      color:#596275;
      font-size:15px;
      line-height:1.55;
    }
    @media (max-width: 1080px){
      .wow-locations-hero,
      .wow-locations-layout{
        grid-template-columns:1fr;
      }
      .wow-directory-grid,
      .wow-location-list{
        grid-template-columns:1fr;
      }
    }
    @media (max-width: 640px){
      .wow-locations-page{ padding:32px 0 56px; }
      .wow-locations-container,
      .wow-page-grid{ width:min(100% - 28px, 1360px); }
      .wow-search-panel__row{
        grid-template-columns:1fr;
      }
      .wow-search-panel button,
      .wow-location-card__footer .btn-wow{
        width:100%;
      }
      .wow-location-card__title,
      .wow-online-card h3{
        font-size:30px;
      }
    }
  </style>
@endpush

@section('content')
<main class="wow-locations-page">
  <div class="wow-page-grid" aria-hidden="true"></div>

  <div class="wow-locations-container">
    <header class="wow-locations-hero">
      <div>
        <p class="wow-kicker">Find</p>
        <h1>Search by location and we’ll sort the nearest wellness options for you.</h1>
        <p>Start typing a town, city or region and Mapbox will suggest the right place. We’ll then rank therapies, classes, events and practitioners by distance, with online shown when it’s the better fit.</p>
      </div>

      <div class="wow-search-panel">
        <form method="get" action="{{ url('/locations') }}" id="wowLocationSearchForm" autocomplete="off">
          <label class="wow-search-panel__label" for="wowLocationQuery">Location</label>
          <div class="wow-search-panel__menu">
            <div class="wow-search-panel__row">
              <input
                id="wowLocationQuery"
                name="place"
                type="search"
                value="{{ $locationQuery ?? '' }}"
                placeholder="Start typing a city, town or area"
                required
                aria-autocomplete="list"
                aria-expanded="false"
              >
              <button type="submit" class="btn-wow btn-wow--primary">Search</button>
            </div>
            <div id="wowLocationDropdown" class="wow-search-panel__dropdown" hidden></div>
          </div>
          <div class="wow-search-panel__helper">
            Try “Maidstone”, “London”, “Cardiff” or a postcode. We’ll resolve the county, city, town and country for you.
          </div>
          <input type="hidden" name="postcode" id="wowLocationPostcode" value="">
          <input type="hidden" name="town" id="wowLocationTown" value="">
          <input type="hidden" name="city" id="wowLocationCity" value="">
          <input type="hidden" name="county" id="wowLocationCounty" value="">
          <input type="hidden" name="country" id="wowLocationCountry" value="">
          <input type="hidden" name="lat" id="wowLocationLat" value="">
          <input type="hidden" name="lng" id="wowLocationLng" value="">
        </form>
      </div>
    </header>

    @if($resolved)
      <section class="wow-search-summary">
        <strong>Showing results for {{ $resolved['label'] ?? $locationQuery }}</strong>
        <p>
          @if(!empty($resolved['town']) || !empty($resolved['county']) || !empty($resolved['country']))
            {{ collect([$resolved['town'] ?? null, $resolved['county'] ?? null, $resolved['country'] ?? null])->filter()->join(', ') }}
          @else
            We found the closest wellness results based on your search.
          @endif
        </p>
        <div class="wow-search-summary__meta">
          @if(!empty($resolved['place']))<span class="wow-pill">{{ $resolved['place'] }}</span>@endif
          @if(!empty($resolved['town']))<span class="wow-pill">{{ $resolved['town'] }}</span>@endif
          @if(!empty($resolved['city']) && $resolved['city'] !== ($resolved['town'] ?? null))<span class="wow-pill">{{ $resolved['city'] }}</span>@endif
          @if(!empty($resolved['county']))<span class="wow-pill">{{ $resolved['county'] }}</span>@endif
          @if(!empty($resolved['region']) && $resolved['region'] !== ($resolved['county'] ?? null))<span class="wow-pill">{{ $resolved['region'] }}</span>@endif
          @if(!empty($resolved['country']))<span class="wow-pill">{{ $resolved['country'] }}</span>@endif
        </div>
      </section>

      <section class="wow-locations-layout">
        <div class="wow-locations-sidebar">
          @if($onlinePreferred && $onlineResult)
            <div class="wow-online-card">
              <span class="wow-location-card__label">Online available</span>
              <h3>{{ $onlineResult['title'] }}</h3>
              <p>There aren’t strong physical matches nearby, so online support is highlighted first.</p>
              <div class="wow-location-card__footer">
                <small>{{ $onlineResult['distance_label'] ?? 'Available anywhere' }}</small>
                <a class="btn-wow btn-wow--cta" href="{{ route('locations.show', ['slug' => $onlineResult['slug'] ?? 'online']) }}">View online</a>
              </div>
            </div>
          @endif

          <div class="wow-empty-card">
            <p>Results are ordered by distance. Online stays available as a fallback when nearby physical options are limited.</p>
          </div>
        </div>

        <div class="wow-locations-map">
          <div id="wowLocationsMap" class="wow-locations-map__canvas" data-center-lat="{{ $resolved['lat'] ?? 51.5072 }}" data-center-lng="{{ $resolved['lng'] ?? -0.1276 }}"></div>
        </div>
      </section>

      <section class="wow-locations-section">
        <h2>Nearest results</h2>
        <p class="wow-locations-section__copy">These are ranked by distance from your selected location. If there’s a better local option later, the list updates automatically when you search again.</p>

        <div class="wow-location-list">
          @foreach($physicalResults as $location)
            <article class="wow-location-card">
              <div>
                <div class="wow-location-card__top">
                  <span class="wow-location-card__label">{{ $location['distance_label'] ?? 'Nearby' }}</span>
                  <span class="wow-pill">{{ $location['slug'] ?? 'location' }}</span>
                </div>
                <h3 class="wow-location-card__title">{{ $location['title'] }}</h3>
                <p class="wow-location-card__copy">
                  {{ $location['seo_description'] ?? ('Explore holistic health and wellness in ' . $location['title'] . '.') }}
                </p>
                <div class="wow-location-card__meta">
                  @if(!empty($location['distance_miles']))<span class="wow-pill">{{ number_format((float) $location['distance_miles'], (float) $location['distance_miles'] < 10 ? 1 : 0) }} miles away</span>@endif
                  @if(!empty($location['lat']) && !empty($location['lng']))<span class="wow-pill">On map</span>@endif
                </div>
              </div>
              <div class="wow-location-card__footer">
                <small>{{ $location['distance_label'] ?? 'Available nearby' }}</small>
                <a class="btn-wow btn-wow--primary" href="{{ route('locations.show', ['slug' => $location['slug']]) }}">View location</a>
              </div>
            </article>
          @endforeach
        </div>
      </section>
    @else
      <section class="wow-locations-section">
        <h2>Browse our main locations</h2>
        <p class="wow-locations-section__copy">Pick a location to see the nearest in-person and online wellness options for that area.</p>
        <div class="wow-directory-grid">
          @foreach($results as $loc)
            <a href="{{ route('locations.show', ['slug' => $loc['slug']]) }}" class="wow-directory-card">
              <h3>{{ $loc['title'] }}</h3>
              <p>{{ $loc['seo_description'] ?? ('Discover wellness support in ' . $loc['title'] . '.') }}</p>
              <span>View location →</span>
            </a>
          @endforeach
        </div>
      </section>
    @endif
  </div>
</main>
@endsection

@push('scripts')
<script>
(function () {
  const token = @json($mapboxKey);
  const form = document.getElementById('wowLocationSearchForm');
  const input = document.getElementById('wowLocationQuery');
  const dropdown = document.getElementById('wowLocationDropdown');
  const postcodeInput = document.getElementById('wowLocationPostcode');
  const townInput = document.getElementById('wowLocationTown');
  const cityInput = document.getElementById('wowLocationCity');
  const countyInput = document.getElementById('wowLocationCounty');
  const countryInput = document.getElementById('wowLocationCountry');
  const latInput = document.getElementById('wowLocationLat');
  const lngInput = document.getElementById('wowLocationLng');
  const mapEl = document.getElementById('wowLocationsMap');
  const canMap = !!(mapEl && token);

  let timer = null;
  let results = [];
  let selected = null;

  function contextLabel(place, prefix) {
    const context = Array.isArray(place?.context) ? place.context : [];
    const match = context.find((item) => String(item?.id || '').startsWith(prefix + '.'));
    return match && match.text ? String(match.text) : '';
  }

  function hideDropdown() {
    if (!dropdown) return;
    dropdown.hidden = true;
    dropdown.innerHTML = '';
    input?.setAttribute('aria-expanded', 'false');
  }

  function showDropdown(items) {
    if (!dropdown) return;
    if (!items.length) {
      hideDropdown();
      return;
    }

    dropdown.hidden = false;
    dropdown.innerHTML = items.map((item, index) => {
      const main = item.text || item.place_name || '';
      const secondary = [
        contextLabel(item, 'place'),
        contextLabel(item, 'region'),
        contextLabel(item, 'country')
      ].filter(Boolean).join(', ') || item.place_name || '';
      return `
        <button type="button" data-index="${index}">
          <strong>${main}</strong>
          <span>${secondary}</span>
        </button>
      `;
    }).join('');
    input?.setAttribute('aria-expanded', 'true');
  }

  function setSelection(item) {
    selected = item;
    const place = item.text || item.place_name || input.value;
    input.value = place;
    postcodeInput.value = place;
    townInput.value = contextLabel(item, 'place') || item.text || '';
    cityInput.value = townInput.value;
    countyInput.value = contextLabel(item, 'region') || contextLabel(item, 'district') || '';
    countryInput.value = contextLabel(item, 'country') || '';
    latInput.value = item.center?.[1] ?? item.geometry?.coordinates?.[1] ?? '';
    lngInput.value = item.center?.[0] ?? item.geometry?.coordinates?.[0] ?? '';
    hideDropdown();
  }

  async function searchPlaces() {
    if (!token) return;
    const query = (input.value || '').trim();
    if (query.length < 2) {
      results = [];
      hideDropdown();
      return;
    }

    clearTimeout(timer);
    timer = setTimeout(async () => {
      try {
        const url = new URL(`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json`);
        url.searchParams.set('access_token', token);
        url.searchParams.set('autocomplete', 'true');
        url.searchParams.set('limit', '6');
        url.searchParams.set('types', 'place,postcode,locality,region,district,country');
        url.searchParams.set('country', 'gb');

        const res = await fetch(url.toString());
        if (!res.ok) throw new Error('mapbox geocode failed');
        const data = await res.json();
        results = Array.isArray(data.features) ? data.features : [];
        showDropdown(results);
      } catch (error) {
        console.warn('[locations] mapbox search failed', error);
        results = [];
        hideDropdown();
      }
    }, 220);
  }

  if (input) {
    input.addEventListener('input', function () {
      selected = null;
      postcodeInput.value = '';
      townInput.value = '';
      cityInput.value = '';
      countyInput.value = '';
      countryInput.value = '';
      latInput.value = '';
      lngInput.value = '';
      searchPlaces();
    });

    input.addEventListener('focus', function () {
      if (results.length) showDropdown(results);
    });
  }

  if (dropdown) {
    dropdown.addEventListener('mousedown', function (event) {
      const button = event.target.closest('button[data-index]');
      if (!button) return;
      const index = Number(button.getAttribute('data-index'));
      if (!Number.isFinite(index) || !results[index]) return;
      event.preventDefault();
      setSelection(results[index]);
      form.submit();
    });
  }

  if (form) {
    form.addEventListener('submit', function (event) {
      const query = (input.value || '').trim();
      if (!query) {
        event.preventDefault();
        return;
      }

      if (selected && selected.text && !postcodeInput.value) {
        postcodeInput.value = selected.text;
      }
    });
  }

  document.addEventListener('click', function (event) {
    if (!dropdown) return;
    if (!form.contains(event.target)) {
      hideDropdown();
      return;
    }
    if (!dropdown.contains(event.target) && event.target !== input) {
      hideDropdown();
    }
  });

  async function loadMapbox() {
    if (!canMap || !window.mapboxgl) return;
    if (!mapEl) return;

    const items = @json($mapItems);

    const center = [
      Number(mapEl.dataset.centerLng || -0.1276),
      Number(mapEl.dataset.centerLat || 51.5072),
    ];

    window.mapboxgl.accessToken = token;
    const map = new window.mapboxgl.Map({
      container: mapEl,
      style: 'mapbox://styles/mapbox/streets-v12',
      center,
      zoom: 8.6,
    });

    map.addControl(new window.mapboxgl.NavigationControl(), 'top-right');

    const bounds = new window.mapboxgl.LngLatBounds();
    let markerCount = 0;

    items.forEach((item) => {
      if (item.lat === null || item.lng === null) return;
      const el = document.createElement('div');
      el.className = 'wow-map-marker';
      el.style.cssText = 'width:16px;height:16px;border-radius:999px;background:#4f9381;border:2px solid #fff;box-shadow:0 0 0 4px rgba(79,147,129,.15);';
      new window.mapboxgl.Marker({ element: el, anchor: 'bottom' })
        .setLngLat([Number(item.lng), Number(item.lat)])
        .setPopup(new window.mapboxgl.Popup({ offset: 8 }).setHTML('<div style="font-weight:600">' + (item.title || '') + '</div>' + (item.distance ? '<div style="font-size:12px;color:#667085">' + item.distance + '</div>' : '')))
        .addTo(map);
      bounds.extend([Number(item.lng), Number(item.lat)]);
      markerCount++;
    });

    if (markerCount > 0) {
      map.fitBounds(bounds, { padding: 56, maxZoom: 11, duration: 0 });
    }
  }

  function ensureMapbox(cb) {
    if (!canMap) return;
    if (window.mapboxgl && window.mapboxgl.Map) {
      cb();
      return;
    }

    if (!document.querySelector('link[href*="mapbox-gl.css"]')) {
      const link = document.createElement('link');
      link.rel = 'stylesheet';
      link.href = 'https://api.mapbox.com/mapbox-gl-js/v3.6.0/mapbox-gl.css';
      document.head.appendChild(link);
    }

    const script = document.createElement('script');
    script.src = 'https://api.mapbox.com/mapbox-gl-js/v3.6.0/mapbox-gl.js';
    script.async = true;
    script.defer = true;
    script.onload = cb;
    document.head.appendChild(script);
  }

  ensureMapbox(loadMapbox);
})();
</script>
@endpush
