@php
    $locationsList = $locationsList ?? [];
    $displayLocations = collect($locationsList)
        ->map(function ($loc) {
            if (is_array($loc)) {
                $loc = $loc['value'] ?? reset($loc) ?? '';
            }
            return trim((string) $loc);
        })
        ->filter(function ($loc) {
            if ($loc === '') {
                return false;
            }
            return strcasecmp($loc, 'online') !== 0;
        })
        ->unique()
        ->values()
        ->all();
@endphp
@if(!empty($displayLocations))
  <div class="wow-acc-item">
    <button class="wow-acc-header" type="button" data-bs-toggle="collapse" data-bs-target="#wowLocation" aria-expanded="false" aria-controls="wowLocation">
      <div class="wow-acc-left"><h4 class="wow-acc-title m-0">Location</h4></div>
      <div class="wow-acc-icon" data-icon-for="#wowLocation">+</div>
    </button>
    <div id="wowLocation" class="collapse">
      <div class="wow-acc-body">
        <div class="wow-loc-meta">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
            <path d="M12 21s7-4.4 7-11a7 7 0 0 0-14 0c0 6.6 7 11 7 11Z"></path>
            <circle cx="12" cy="10" r="2"></circle>
          </svg>
          <span><span id="wowLocCount">{{ count($displayLocations) }}</span> Locations</span>
        </div>
        <div class="wow-loc-panel">
          <div class="row g-0">
            <div class="col-12 col-lg-5">
              <ul class="wow-loc-list" id="wowLocList">
                @foreach($displayLocations as $idx => $location)
                  @php
                    $parts = array_values(array_filter(array_map('trim', explode(',', $location))));
                    $primary = array_shift($parts) ?? $location;
                    $secondary = implode(', ', $parts);
                  @endphp
                  <li>
                    <button type="button" class="wow-loc-item" data-index="{{ $idx }}" data-location="{{ e($location) }}" aria-pressed="{{ $idx === 0 ? 'true' : 'false' }}">
                      <span class="wow-loc-pill" aria-hidden="true"></span>
                      <span class="wow-loc-copy">
                        <span class="wow-loc-title">{{ $primary }}</span>
                        @if($secondary !== '')
                          <small class="wow-loc-sub">{{ $secondary }}</small>
                        @endif
                      </span>
                      <span class="wow-loc-chevron" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                          <path d="M9 18l6-6-6-6" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                      </span>
                    </button>
                  </li>
                @endforeach
              </ul>
            </div>
            <div class="col-12 col-lg-7">
              <div class="wow-map" id="wowLocMap">
                <div class="wow-map-placeholder">
                  Tap a location to drop a pin
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  @php $mapboxKey = config('services.mapbox.token'); @endphp
  @push('scripts')
    <script>
      document.addEventListener('DOMContentLoaded', function(){
        const LOCATIONS = @json($displayLocations);
        if (!Array.isArray(LOCATIONS) || !LOCATIONS.length) return;
        const mapContainer = document.getElementById('wowLocMap');
        const listItems = Array.from(document.querySelectorAll('#wowLocList .wow-loc-item'));
        const token = window.WOW_MAPS_KEY || @json($mapboxKey);
        if (!mapContainer || !listItems.length || !token) return;

        function loadMapbox(cb){
          if (window.mapboxgl && window.mapboxgl.Map) { cb(); return; }
          window.__wowMapboxQueue = window.__wowMapboxQueue || [];
          window.__wowMapboxQueue.push(cb);
          if (window.__wowMapboxLoading) return;
          window.__wowMapboxLoading = true;
          if (!document.querySelector('link[href*="mapbox-gl.css"]')){
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://api.mapbox.com/mapbox-gl-js/v3.6.0/mapbox-gl.css';
            document.head.appendChild(link);
          }
          const script = document.createElement('script');
          script.src = 'https://api.mapbox.com/mapbox-gl-js/v3.6.0/mapbox-gl.js';
          script.async = true;
          script.onload = function(){
            window.__wowMapboxLoading = false;
            const queue = (window.__wowMapboxQueue || []).splice(0);
            queue.forEach(fn => { try { fn(); } catch (e) {} });
          };
          script.onerror = function(){ window.__wowMapboxLoading = false; window.__wowMapboxQueue = []; };
          document.body.appendChild(script);
        }

        function geocodeLocation(address){
          const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(address)}.json?access_token=${token}&limit=1`;
          return fetch(url)
            .then(res => res.ok ? res.json() : Promise.reject())
            .then(data => {
              if (data && data.features && data.features[0]) {
                return data.features[0].center;
              }
              return null;
            })
            .catch(() => null);
        }

        loadMapbox(initMap);

        function initMap(){
          if (!(window.mapboxgl && window.mapboxgl.Map)) return;
          mapboxgl.accessToken = token;
          const map = new mapboxgl.Map({
            container: mapContainer,
            style: 'mapbox://styles/mapbox/light-v11',
            center: [-0.118092, 51.509865],
            zoom: LOCATIONS.length === 1 ? 7.5 : 4.7,
            attributionControl: false
          });
          map.addControl(new mapboxgl.NavigationControl({ showCompass: false }), 'bottom-right');
          const bounds = new mapboxgl.LngLatBounds();
          const markers = new Array(LOCATIONS.length).fill(null);

          Promise.all(LOCATIONS.map(loc => geocodeLocation(loc))).then(coordsList => {
            coordsList.forEach((coords, idx) => {
              if (!coords) return;
              const el = document.createElement('div');
              el.className = 'wow-pin';
              const marker = new mapboxgl.Marker({ element: el, anchor: 'bottom' }).setLngLat(coords).addTo(map);
              markers[idx] = { marker, coords, el };
              bounds.extend(coords);
            });
            if (!bounds.isEmpty() && LOCATIONS.length > 1) {
              map.fitBounds(bounds, { padding: 60, duration: 900 });
            } else if (!bounds.isEmpty()) {
              map.setCenter(bounds.getCenter());
              map.setZoom(8.5);
            }
            mapContainer.classList.add('is-ready');
            mapContainer.querySelector('.wow-map-placeholder')?.remove();

            function setActive(index){
              listItems.forEach((item, i) => {
                const active = i === index;
                item.classList.toggle('active', active);
                item.setAttribute('aria-pressed', active ? 'true' : 'false');
              });
              markers.forEach((m, i) => {
                if (!m || !m.el) return;
                m.el.classList.toggle('is-active', i === index);
              });
              const target = markers[index];
              if (target && target.coords) {
                map.easeTo({ center: target.coords, zoom: Math.max(map.getZoom(), 9), duration: 600 });
              }
            }

            listItems.forEach((btn, idx) => {
              btn.addEventListener('click', () => setActive(idx));
              btn.addEventListener('keydown', (evt) => {
                if (evt.key === 'Enter' || evt.key === ' ' || evt.key === 'Spacebar') {
                  evt.preventDefault();
                  setActive(idx);
                }
              });
            });

            if (listItems.length) {
              setActive(0);
            }

            const collapse = document.getElementById('wowLocation');
            if (collapse) {
              collapse.addEventListener('shown.bs.collapse', () => {
                try { map.resize(); } catch (e) {}
              });
            }
          });
        }
      });
    </script>
  @endpush
@endif
