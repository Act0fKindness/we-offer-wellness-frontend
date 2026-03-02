@php
    use Illuminate\Support\Str;

    $mapboxKey = config('services.mapbox.token');

    $shortLocationMobile = function ($address) {
        try {
            $raw = trim((string) $address);
            if ($raw === '') {
                return $raw;
            }
            $raw = preg_replace('/\b(United Kingdom|UK|England|Scotland|Wales|Northern Ireland)\b/i', '', $raw);
            $raw = preg_replace('/\b[A-Z]{1,2}\d{1,2}[A-Z]?\s*\d[A-Z]{2}\b/i', '', $raw);
            $raw = preg_replace('/\s+/', ' ', $raw);
            $parts = array_values(array_filter(array_map('trim', explode(',', $raw)), static fn ($p) => $p !== ''));
            if (count($parts) >= 2) {
                $place = $parts[0];
                $city = $parts[count($parts) - 1];
                if (strcasecmp($place, $city) === 0) {
                    return $city;
                }
                return trim($place . ', ' . $city);
            }
            return $parts[0] ?? $raw;
        } catch (Throwable $e) {
            return (string) $address;
        }
    };

    $deriveProductUrl = function ($product) {
        $t = strtolower((string) ($product->product_type ?? ''));
        $tags = strtolower((string) ($product->tags_list ?? ''));
        $seg = 'therapies';
        if (str_contains($t, 'workshop')) {
            $seg = 'workshops';
        } elseif (str_contains($t, 'event')) {
            $seg = 'events';
        } elseif (str_contains($t, 'class')) {
            $seg = 'classes';
        } elseif (str_contains($t, 'retreat')) {
            $seg = 'retreats';
        } elseif (str_contains($t, 'gift') || str_contains($tags, 'gift')) {
            $seg = 'gifts';
        }
        $slug = Str::slug($product->title ?: (string) $product->id);
        return url('/' . $seg . '/' . $product->id . '-' . $slug);
    };

    $buildLocationLabel = function ($product) use ($shortLocationMobile) {
        if (method_exists($product, 'getLocations')) {
            $locations = $product->getLocations();
            $physical = array_values(array_filter($locations, static fn ($loc) => $loc !== 'Online'));
            if (!empty($physical)) {
                return $shortLocationMobile($physical[0]);
            }
            if (in_array('Online', $locations, true)) {
                return 'Online';
            }
        }
        $raw = $product->location ?? $product->vendor_city ?? null;
        return $raw ? $shortLocationMobile($raw) : null;
    };

    $extractCoords = function ($product) {
        $lat = $product->lat ?? $product->latitude ?? ($product->geo_lat ?? null);
        $lng = $product->lng ?? $product->longitude ?? ($product->geo_lng ?? null);
        if (is_numeric($lat) && is_numeric($lng)) {
            return [(float) $lat, (float) $lng];
        }
        $vendor = $product->vendor ?? null;
        $locations = [];
        if ($vendor && $vendor->relationLoaded('locations')) {
            $locations = $vendor->locations;
        }
        foreach ($locations as $loc) {
            $vLat = $loc->lat ?? null;
            $vLng = $loc->lng ?? null;
            if (is_numeric($vLat) && is_numeric($vLng)) {
                return [(float) $vLat, (float) $vLng];
            }
        }
        $meta = $product->meta_json ?? [];
        if (is_string($meta)) {
            $meta = json_decode($meta, true) ?: [];
        }
        $mLat = $meta['lat'] ?? null;
        $mLng = $meta['lng'] ?? null;
        if (is_numeric($mLat) && is_numeric($mLng)) {
            return [(float) $mLat, (float) $mLng];
        }
        return [null, null];
    };

    $mobileListings = [];
    foreach (($products ?? collect()) as $product) {
        $priceMin = $product->variants_min_price ?? ($product->price ?? null);
        if (is_numeric($priceMin) && $priceMin > 1000 && $priceMin % 100 === 0) {
            $priceMin = $priceMin / 100;
        }
        $compareMin = $product->variants_min_compare ?? ($product->compare_at_price ?? null);
        if (is_numeric($compareMin) && $compareMin > 1000 && $compareMin % 100 === 0) {
            $compareMin = $compareMin / 100;
        }
        $rating = isset($product->reviews_avg_rating) ? round((float) $product->reviews_avg_rating, 2) : null;
        $reviewCount = (int) ($product->reviews_count ?? 0);
        $image = $product->getFirstImageUrl();
        [$lat, $lng] = $extractCoords($product);
        $mobileListings[] = [
            'pid' => $product->id,
            'title' => $product->title ?? 'Untitled',
            'address' => $buildLocationLabel($product),
            'lat' => $lat,
            'lng' => $lng,
            'price' => is_numeric($priceMin) ? (float) $priceMin : null,
            'compare_at' => is_numeric($compareMin) ? (float) $compareMin : null,
            'rating' => $rating,
            'reviews' => $reviewCount,
            'image' => $image,
            'url' => $deriveProductUrl($product),
            'price_note' => $product->duration_label ?? null,
        ];
    }
@endphp

<section id="wowMobileSearch" class="wow-mobile">
    <div class="wow-mobile__map">
        <div id="wowMobileMap"></div>
        <div class="wow-mobile__map-loading" id="wowMobileMapLoading">Loading map&hellip;</div>
    </div>
    <button type="button" class="wow-mobile__locate" id="wowMobileLocate" aria-label="Re-centre map">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 2v3M12 19v3M2 12h3M19 12h3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <circle cx="12" cy="12" r="6.5" stroke="currentColor" stroke-width="2"/>
            <circle cx="12" cy="12" r="1.6" fill="currentColor"/>
        </svg>
    </button>

    <div class="wow-mobile__preview" id="wowMobilePreview" hidden>
        <button type="button" class="wow-mobile__preview-close" id="wowMobilePreviewClose" aria-label="Close preview">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </button>
        <a class="wow-mobile__preview-card" id="wowMobilePreviewLink" href="#">
            <div class="wow-mobile__preview-media">
                <img id="wowMobilePreviewImage" alt="" src="" loading="lazy">
            </div>
            <div class="wow-mobile__preview-body">
                <div class="wow-mobile__preview-title" id="wowMobilePreviewTitle"></div>
                <p class="wow-mobile__preview-sub" id="wowMobilePreviewAddress"></p>
                <div class="wow-mobile__preview-meta">
                    <span id="wowMobilePreviewRating"></span>
                    <span id="wowMobilePreviewReviews"></span>
                </div>
                <div class="wow-mobile__preview-price" id="wowMobilePreviewPrice"></div>
            </div>
        </a>
    </div>

    <section class="wow-mobile__sheet" aria-label="Search results">
        <div class="wow-mobile__sheet-inner" id="wowMobileSheetInner">
            <button class="wow-mobile__sheet-handle" type="button" id="wowMobileSheetHandle" aria-label="Toggle results panel"><span></span></button>
            <div class="wow-mobile__sheet-head">
                <h2 id="wowMobileResultsCount">{{ number_format($resultCount ?? ($products?->count() ?? 0)) }} results</h2>
                <p>Tap a pin or open the list to explore experiences.</p>
            </div>
            <div class="wow-mobile__sheet-results" id="wowMobileResults" role="list">
                @forelse(($products ?? collect()) as $product)
                    <article class="wow-mobile__result" data-pid="{{ $product->id }}" role="listitem">
                        @include('partials.product_card', ['product' => $product])
                    </article>
                @empty
                    <div class="wow-mobile__empty">No results matched your filters. Try widening your search.</div>
                @endforelse
            </div>
        </div>
    </section>

    <button type="button" class="wow-mobile__toggle" id="wowMobileToggle" aria-label="Toggle map/list">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        <span id="wowMobileToggleText">Show list</span>
    </button>
</section>

@once
    <style>
        .wow-mobile{ position:relative; isolation:isolate; min-height: calc(100vh - 80px); background:#f8fafc; }
        .wow-mobile__map{ position:relative; width:100%; height:60vh; background:#e2e8f0; }
        .wow-mobile__map #wowMobileMap{ position:absolute; inset:0; }
        .wow-mobile__map-loading{ position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font-weight:700; color:#0f172a; background:linear-gradient(180deg,rgba(255,255,255,.9),rgba(248,250,252,.95)); z-index:2; }
        .wow-mobile__locate{ position:absolute; left:16px; bottom:200px; width:52px; height:52px; border-radius:999px; border:1px solid rgba(15,23,42,.15); background:#fff; box-shadow:0 18px 44px rgba(15,23,42,.15); display:grid; place-items:center; color:#0f172a; z-index:5; }
        .wow-mobile__locate svg{ width:20px; height:20px; }
        .wow-mobile__preview{ position:absolute; left:16px; right:16px; bottom:120px; z-index:6; transition:opacity .2s ease, transform .2s ease; }
        .wow-mobile__preview[hidden]{ display:none; }
        .wow-mobile__preview-card{ display:grid; grid-template-columns:72px 1fr; min-height:72px; text-decoration:none; color:inherit; border-radius:14px; border:1px solid rgba(15,23,42,.1); background:rgba(255,255,255,.96); box-shadow:0 18px 45px rgba(15,23,42,.18); overflow:hidden; }
        .wow-mobile__preview-media{ position:relative; overflow:hidden; background:#cbd5f5; height:72px; }
        .wow-mobile__preview-media img{ width:100%; height:100%; object-fit:cover; display:block; }
        .wow-mobile__preview-body{ padding:8px 12px; display:flex; flex-direction:column; gap:4px; }
        .wow-mobile__preview-title{ font-weight:800; font-size:15px; line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .wow-mobile__preview-sub{ margin:0; color:#475569; font-weight:600; font-size:12px; line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .wow-mobile__preview-meta{ display:flex; gap:6px; font-weight:700; font-size:12px; color:#0f172a; }
        .wow-mobile__preview-price{ font-weight:800; font-size:13px; }
        .wow-mobile__preview-close{ position:absolute; right:26px; top:-12px; width:36px; height:36px; border-radius:999px; border:1px solid rgba(15,23,42,.12); background:#fff; box-shadow:0 12px 30px rgba(15,23,42,.18); display:grid; place-items:center; z-index:7; }
        .wow-mobile__preview-close svg{ width:16px; height:16px; }

        .wow-mobile__sheet{ position:relative; z-index:10; }
        .wow-mobile__sheet-inner{ position:relative; background:#fff; border-radius:20px 20px 0 0; margin-top:-90px; box-shadow:0 -10px 40px rgba(15,23,42,.1); overflow:hidden; will-change:transform; transform:translateY(0); transition:transform .3s ease; }
        .wow-mobile__sheet-handle{ width:100%; padding:12px 0 6px; display:flex; justify-content:center; background:linear-gradient(180deg,#fff,rgba(255,255,255,.92)); border:none; }
        .wow-mobile__sheet-handle span{ width:54px; height:6px; border-radius:999px; background:rgba(15,23,42,.15); }
        .wow-mobile__sheet-head{ padding:10px 18px 6px; border-bottom:1px solid rgba(15,23,42,.08); }
        .wow-mobile__sheet-head h2{ margin:0; font-size:20px; font-weight:800; }
        .wow-mobile__sheet-head p{ margin:4px 0 8px; color:#475569; font-weight:600; font-size:13px; }
        .wow-mobile__sheet-results{ max-height:60vh; overflow:auto; padding:16px; background:rgba(248,250,252,.98); }
        .wow-mobile__result + .wow-mobile__result{ margin-top:18px; }
        .wow-mobile__result.is-active{ position:relative; }
        .wow-mobile__result.is-active::after{ content:''; position:absolute; inset:0; border:2px solid #0f766e; border-radius:12px; pointer-events:none; }
        .wow-mobile__empty{ padding:16px; border-radius:12px; border:1px dashed rgba(15,23,42,.2); background:#f8fafc; font-weight:600; color:#475569; text-align:center; }

        .wow-mobile__toggle{ position:fixed; left:50%; transform:translateX(-50%); bottom:18px; display:flex; align-items:center; gap:10px; border:none; border-radius:999px; padding:12px 20px; background:#0f172a; color:#fff; font-weight:700; box-shadow:0 18px 50px rgba(15,23,42,.25); z-index:20; }
        .wow-mobile__toggle svg{ width:18px; height:18px; }

        .wow-mobile__marker{ font-weight:800; font-size:14px; border-radius:999px; border:1px solid rgba(15,23,42,.12); background:#fff; padding:8px 12px; box-shadow:0 12px 30px rgba(15,23,42,.2); cursor:pointer; }
        .wow-mobile__marker.is-active{ background:#0f172a; color:#fff; }

        @media(min-width: 992px){
            .wow-mobile{ display:none; }
        }
    </style>
@endonce

<script>
    (function(){
        const root = document.getElementById('wowMobileSearch');
        if (!root) return;
        const listings = @json($mobileListings);
        const mapToken = @json($mapboxKey ?? '');
        const mq = window.matchMedia('(max-width: 991px)');
        let initialized = false;

        const ensureInit = () => {
            if (initialized || !mq.matches) return;
            initialized = true;
            initMobileExperience();
        };

        mq.addEventListener('change', ensureInit);
        ensureInit();

        function initMobileExperience(){
            const sheetInner = root.querySelector('#wowMobileSheetInner');
            const toggleBtn = root.querySelector('#wowMobileToggle');
            const toggleText = root.querySelector('#wowMobileToggleText');
            const handleBtn = root.querySelector('#wowMobileSheetHandle');
            const resultsEl = root.querySelector('#wowMobileResults');
            const countEl = root.querySelector('#wowMobileResultsCount');
            const cards = Array.from(root.querySelectorAll('.wow-mobile__result'));
            const preview = root.querySelector('#wowMobilePreview');
            const previewLink = root.querySelector('#wowMobilePreviewLink');
            const previewImage = root.querySelector('#wowMobilePreviewImage');
            const previewTitle = root.querySelector('#wowMobilePreviewTitle');
            const previewAddress = root.querySelector('#wowMobilePreviewAddress');
            const previewRating = root.querySelector('#wowMobilePreviewRating');
            const previewReviews = root.querySelector('#wowMobilePreviewReviews');
            const previewPrice = root.querySelector('#wowMobilePreviewPrice');
            const previewClose = root.querySelector('#wowMobilePreviewClose');
            const mapEl = root.querySelector('#wowMobileMap');
            const loadingEl = root.querySelector('#wowMobileMapLoading');
            const locateBtn = root.querySelector('#wowMobileLocate');
            let sheetOpen = false;
            let activeId = null;
            let map = null;
            const markersById = new Map();
            let collapsedTranslate = 0;
            let currentTranslate = 0;
            let dragSession = null;
            const touchState = { context: null };
            const PEEK_VISIBLE = 180;

            if (countEl) {
                const count = listings.length || (Number(countEl.textContent.replace(/\D/g, '')) || 0);
                countEl.textContent = `${count.toLocaleString()} results`;
            }

            function clampTranslate(value){
                return Math.max(0, Math.min(collapsedTranslate, value));
            }

            function measureSheet(keepPosition = true){
                if (!sheetInner) return;
                const height = sheetInner.offsetHeight || 0;
                const visible = Math.min(Math.max(PEEK_VISIBLE, 160), height);
                collapsedTranslate = Math.max(0, height - visible);
                if (!keepPosition) {
                    currentTranslate = sheetOpen ? 0 : collapsedTranslate;
                    sheetInner.style.transform = `translateY(${currentTranslate}px)`;
                } else {
                    currentTranslate = clampTranslate(currentTranslate);
                    sheetInner.style.transform = `translateY(${currentTranslate}px)`;
                }
            }

            function setSheetState(open, options = {}){
                sheetOpen = open;
                if (!sheetInner) {
                    if (toggleText) {
                        toggleText.textContent = open ? 'Show map' : 'Show list';
                    }
                    return;
                }
                measureSheet();
                const target = open ? 0 : collapsedTranslate;
                if (options.animate === false) {
                    sheetInner.style.transition = 'none';
                } else {
                    sheetInner.style.transition = '';
                }
                currentTranslate = target;
                sheetInner.style.transform = `translateY(${target}px)`;
                if (options.animate === false) {
                    requestAnimationFrame(() => { if (sheetInner) sheetInner.style.transition = ''; });
                }
                if (toggleText) {
                    toggleText.textContent = open ? 'Show map' : 'Show list';
                }
                updatePreviewOffset();
            }

            function beginDrag(clientY){
                measureSheet();
                dragSession = { startY: clientY, startTranslate: currentTranslate };
                sheetInner.style.transition = 'none';
            }

            function moveDrag(clientY){
                if (!dragSession) return;
                const delta = clientY - dragSession.startY;
                currentTranslate = clampTranslate(dragSession.startTranslate + delta);
                sheetInner.style.transform = `translateY(${currentTranslate}px)`;
                sheetOpen = currentTranslate <= collapsedTranslate * 0.5;
                updatePreviewOffset();
            }

            function endDrag(){
                if (!dragSession) return;
                sheetInner.style.transition = '';
                const shouldOpen = currentTranslate <= collapsedTranslate * 0.5;
                setSheetState(shouldOpen);
                dragSession = null;
            }

            function handleTouchStart(event){
                if (!event.touches || event.touches.length !== 1) return;
                const touch = event.touches[0];
                const withinResults = resultsEl?.contains(event.target) || false;
                touchState.context = {
                    startY: touch.clientY,
                    lastY: touch.clientY,
                    targetInResults: withinResults,
                    dragging: false
                };
                if (!sheetOpen || !withinResults) {
                    event.preventDefault();
                    touchState.context.dragging = true;
                    beginDrag(touch.clientY);
                } else {
                    touchState.context.startScroll = resultsEl?.scrollTop || 0;
                }
            }

            function handleTouchMove(event){
                const ctx = touchState.context;
                if (!ctx || !event.touches || event.touches.length !== 1) return;
                const touch = event.touches[0];
                const totalDelta = touch.clientY - ctx.startY;
                const stepDelta = touch.clientY - (ctx.lastY ?? ctx.startY);
                ctx.lastY = touch.clientY;
                if (ctx.dragging) {
                    event.preventDefault();
                    moveDrag(touch.clientY);
                    return;
                }
                const scrollTop = resultsEl?.scrollTop || 0;
                const pullingDown = totalDelta > 10;
                const pushingUp = totalDelta < -10;
                if (!sheetOpen && pushingUp) {
                    ctx.dragging = true;
                    event.preventDefault();
                    beginDrag(touch.clientY);
                    return;
                }
                if (sheetOpen && pullingDown && scrollTop <= 0) {
                    ctx.dragging = true;
                    event.preventDefault();
                    beginDrag(touch.clientY);
                    return;
                }

                const nearBottom = resultsEl && (scrollTop + resultsEl.clientHeight >= resultsEl.scrollHeight - 4);
                if (sheetOpen && ctx.targetInResults && nearBottom && stepDelta < -2) {
                    event.preventDefault();
                    window.scrollBy(0, Math.abs(stepDelta));
                }
            }
            }

            function handleTouchEnd(){
                if (touchState.context?.dragging) {
                    endDrag();
                }
                touchState.context = null;
            }

            handleBtn?.addEventListener('click', () => setSheetState(!sheetOpen));
            toggleBtn?.addEventListener('click', () => setSheetState(!sheetOpen));
            sheetInner?.addEventListener('touchstart', handleTouchStart, { passive: false });
            sheetInner?.addEventListener('touchmove', handleTouchMove, { passive: false });
            sheetInner?.addEventListener('touchend', handleTouchEnd);
            sheetInner?.addEventListener('touchcancel', handleTouchEnd);
            setSheetState(false, { animate: false });

            function formatPrice(value){
                if (typeof value !== 'number' || !isFinite(value)) return 'Price on request';
                return '£' + Math.round(value).toLocaleString();
            }

            function updatePreview(item){
                if (!preview) return;
                if (!item) {
                    preview.hidden = true;
                    return;
                }
                preview.hidden = false;
                previewLink.href = item.url || '#';
                previewImage.src = item.image || '';
                previewImage.alt = item.title || '';
                previewTitle.textContent = item.title || 'Experience';
                previewAddress.textContent = item.address || 'Location on map';
                previewRating.textContent = item.rating ? `★ ${item.rating.toFixed(2)}` : 'Unrated';
                previewReviews.textContent = item.reviews ? `(${item.reviews})` : '';
                const note = item.price_note ? ` · ${item.price_note}` : '';
                previewPrice.textContent = `${formatPrice(item.price)}${note}`;
                updatePreviewOffset();
            }

            previewClose?.addEventListener('click', () => {
                updatePreview(null);
                setActiveMarker(null);
                setActiveCard(null);
            });

            function setActiveCard(id){
                cards.forEach(card => {
                    if (!id) {
                        card.classList.remove('is-active');
                    } else {
                        card.classList.toggle('is-active', String(card.dataset.pid) === String(id));
                    }
                });
            }

            function setActiveMarker(id){
                markersById.forEach(({ el }) => {
                    if (!id) {
                        el.classList.remove('is-active');
                    } else {
                        el.classList.toggle('is-active', String(el.dataset.pid) === String(id));
                    }
                });
            }

            function scrollCardIntoView(id){
                const card = cards.find(c => String(c.dataset.pid) === String(id));
                if (!card) return;
                const top = card.offsetTop - 12;
                resultsEl?.scrollTo({ top, behavior: 'smooth' });
            }

            function selectListing(id, opts = {}){
                const item = listings.find(list => String(list.pid) === String(id));
                if (!item) return;
                activeId = String(id);
                setActiveCard(activeId);
                setActiveMarker(activeId);
                updatePreview(item);
                if (opts.source === 'marker') {
                    scrollCardIntoView(activeId);
                    setSheetState(true);
                }
                if (map && opts.fly !== false && typeof item.lat === 'number' && typeof item.lng === 'number') {
                    map.flyTo({ center: [item.lng, item.lat], zoom: Math.max(map.getZoom(), 13), speed: 1.2 });
                }
            }

            cards.forEach(card => {
                card.addEventListener('click', () => {
                    selectListing(card.dataset.pid || '', { source: 'card', fly: true });
                });
            });

            function ensureMapboxAssets(onReady){
                if (window.mapboxgl) {
                    onReady();
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
                script.onload = onReady;
                document.head.appendChild(script);
            }

            function initMap(){
                if (!mapEl || !mapToken) {
                    if (loadingEl) loadingEl.textContent = mapToken ? 'Map unavailable' : 'Map key missing';
                    return;
                }
                ensureMapboxAssets(() => {
                    try {
                        mapboxgl.accessToken = mapToken;
                        const initial = listings.find(item => typeof item.lng === 'number' && typeof item.lat === 'number');
                        map = new mapboxgl.Map({
                            container: mapEl,
                            style: 'mapbox://styles/mapbox/light-v11',
                            center: initial ? [initial.lng, initial.lat] : [-0.1276, 51.5072],
                            zoom: initial ? 12 : 10,
                            attributionControl: false
                        });
                        map.on('load', () => {
                            addMarkers();
                            loadingEl?.setAttribute('hidden', 'hidden');
                            const first = listings.find(item => typeof item.lng === 'number' && typeof item.lat === 'number');
                            if (first) {
                                selectListing(first.pid, { fly: false });
                            }
                        });
                        map.on('click', () => {
                            updatePreview(null);
                            setActiveCard(null);
                            setActiveMarker(null);
                        });
                    } catch (err) {
                        if (loadingEl) loadingEl.textContent = 'Map failed to load';
                    }
                });
            }

            function addMarkers(){
                if (!map) return;
                const bounds = new mapboxgl.LngLatBounds();
                markersById.clear();
                listings.forEach(item => {
                    if (typeof item.lat !== 'number' || typeof item.lng !== 'number') return;
                    bounds.extend([item.lng, item.lat]);
                    const el = document.createElement('button');
                    el.className = 'wow-mobile__marker';
                    el.type = 'button';
                    el.textContent = formatPrice(item.price);
                    el.dataset.pid = item.pid;
                    el.addEventListener('click', (event) => {
                        event.stopPropagation();
                        selectListing(item.pid, { source: 'marker', fly: true });
                    });
                    const marker = new mapboxgl.Marker({ element: el, anchor: 'bottom' })
                        .setLngLat([item.lng, item.lat])
                        .addTo(map);
                    markersById.set(String(item.pid), { marker, el });
                });
                if (!bounds.isEmpty()) {
                    map.fitBounds(bounds, {
                        padding: { top: 80, bottom: sheetOpen ? 280 : 140, left: 40, right: 40 },
                        maxZoom: 13
                    });
                }
            }

            locateBtn?.addEventListener('click', () => {
                if (!map || markersById.size === 0) return;
                const bounds = new mapboxgl.LngLatBounds();
                markersById.forEach(({ marker }) => bounds.extend(marker.getLngLat()));
                if (!bounds.isEmpty()) {
                    map.fitBounds(bounds, { padding: { top: 80, bottom: sheetOpen ? 280 : 140, left: 40, right: 40 }, maxZoom: 13 });
                }
            });

            function updatePreviewOffset(){
                if (!preview || preview.hidden) return;
                const rect = sheetInner?.getBoundingClientRect();
                const offset = rect ? Math.max(120, window.innerHeight - rect.top + 12) : 140;
                preview.style.bottom = `${offset}px`;
            }

            window.addEventListener('resize', () => {
                const wasOpen = sheetOpen;
                setSheetState(wasOpen, { animate: false });
            });
            resultsEl?.addEventListener('scroll', updatePreviewOffset, { passive: true });

            initMap();
        }
    })();
</script>
