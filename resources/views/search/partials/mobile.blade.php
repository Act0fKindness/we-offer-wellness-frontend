@php
    $initialCount = (int) ($resultCount ?? ($products?->count() ?? 0));
    $mobileWhat = trim((string) request('what', ''));
    $mobileWhere = trim((string) request('where', ''));
    $mobileWhen = trim((string) request('when', ''));
    $mobileAdults = (int) request('adults', 0);
    $mobileGroupType = trim((string) request('group_type', ''));
    $mobileFilterCount = collect([
        $mobileWhat,
        $mobileWhere,
        $mobileWhen,
        $mobileAdults > 0 ? 'adults' : '',
        $mobileGroupType,
    ])->filter(function ($value) {
        return trim((string) $value) !== '';
    })->count();
    $mobileMapData = $searchMapData ?? [];
@endphp

<section id="wowMobileSearch" class="wow-mobile-search-page" aria-label="Search results">
    <div id="search-v4-root">
        <div class="container-fluid px-3 px-sm-4">
            <div class="wow-search-bottom-row wow-mobile-search-page__summary" aria-label="Search filters">
                <div class="wow-active-chips wow-mobile-search-page__chips" data-chip-list>
                    @if($mobileFilterCount > 0)
                        <button type="button" class="wow-chip wow-chip--clear-all" data-clear-all-filters aria-label="Clear all filters">
                            <strong>Clear all filters</strong>
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M6.7 6.7a1 1 0 0 1 1.4 0L12 10.6l3.9-3.9a1 1 0 1 1 1.4 1.4L13.4 12l3.9 3.9a1 1 0 1 1-1.4 1.4L12 13.4l-3.9 3.9a1 1 0 0 1-1.4-1.4l3.9-3.9-3.9-3.9a1 1 0 0 1 0-1.4Z"></path>
                            </svg>
                        </button>
                    @endif
                    @if($mobileWhat !== '')
                        <span class="wow-chip">
                            <strong>What:</strong>
                            <span>{{ $mobileWhat }}</span>
                            <button type="button" class="wow-chip-remove" aria-label="Remove What">
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path d="M6.7 6.7a1 1 0 0 1 1.4 0L12 10.6l3.9-3.9a1 1 0 1 1 1.4 1.4L13.4 12l3.9 3.9a1 1 0 1 1-1.4 1.4L12 13.4l-3.9 3.9a1 1 0 0 1-1.4-1.4l3.9-3.9-3.9-3.9a1 1 0 0 1 0-1.4Z"></path>
                                </svg>
                            </button>
                        </span>
                    @endif

                    @if($mobileWhen !== '')
                        <span class="wow-chip">
                            <strong>When:</strong>
                            <span>{{ $mobileWhen }}</span>
                            <button type="button" class="wow-chip-remove" aria-label="Remove When">
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path d="M6.7 6.7a1 1 0 0 1 1.4 0L12 10.6l3.9-3.9a1 1 0 1 1 1.4 1.4L13.4 12l3.9 3.9a1 1 0 1 1-1.4 1.4L12 13.4l-3.9 3.9a1 1 0 0 1-1.4-1.4l3.9-3.9-3.9-3.9a1 1 0 0 1 0-1.4Z"></path>
                                </svg>
                            </button>
                        </span>
                    @endif

                    @if($mobileWhere !== '')
                        <span class="wow-chip">
                            <strong>Where:</strong>
                            <span>{{ $mobileWhere }}</span>
                            <button type="button" class="wow-chip-remove" aria-label="Remove Where">
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path d="M6.7 6.7a1 1 0 0 1 1.4 0L12 10.6l3.9-3.9a1 1 0 1 1 1.4 1.4L13.4 12l3.9 3.9a1 1 0 1 1-1.4 1.4L12 13.4l-3.9 3.9a1 1 0 0 1-1.4-1.4l3.9-3.9-3.9-3.9a1 1 0 0 1 0-1.4Z"></path>
                                </svg>
                            </button>
                        </span>
                    @endif

                    @if($mobileAdults > 0 || $mobileGroupType !== '')
                        <span class="wow-chip">
                            <strong>Who:</strong>
                            <span>{{ $mobileAdults > 0 ? $mobileAdults . ' ' . ($mobileAdults === 1 ? 'guest' : 'guests') : $mobileGroupType }}</span>
                            <button type="button" class="wow-chip-remove" aria-label="Remove Who">
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path d="M6.7 6.7a1 1 0 0 1 1.4 0L12 10.6l3.9-3.9a1 1 0 1 1 1.4 1.4L13.4 12l3.9 3.9a1 1 0 1 1-1.4 1.4L12 13.4l-3.9 3.9a1 1 0 0 1-1.4-1.4l3.9-3.9-3.9-3.9a1 1 0 0 1 0-1.4Z"></path>
                                </svg>
                            </button>
                        </span>
                    @endif
                </div>

                <div class="wow-filter-actions">
                    <div class="wow-results-count wow-results-count--compact">
                        <strong id="wowMobileResultsCount">{{ number_format($initialCount) }}</strong>
                        <span>results</span>
                    </div>
                </div>
            </div>

            <div class="wow-mobile-search-page__map-wrap" aria-hidden="true">
                <div id="wowMobileSearchMap" class="wow-mobile-search-page__map"></div>
                <div class="wow-mobile-search-page__map-overlay" aria-hidden="true">
                    <span class="wow-mobile-search-page__map-spinner"></span>
                    <span class="wow-mobile-search-page__map-overlay-text">Updating map</span>
                </div>
            </div>

            <div class="wow-mobile-search-page__results">
                <div class="row g-3" id="wowMobileResultsGrid" data-ghost-count="3" aria-live="polite">
                    @include('search.partials.results_cards', ['products' => $products])
                </div>
            </div>

            <div id="wowMobileResultsPagination" class="wow-mobile-search-page__pagination">
                @if($products instanceof \Illuminate\Pagination\Paginator || $products instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $products->withQueryString()->onEachSide(1)->links('pagination::bootstrap-4') }}
                @endif
            </div>
        </div>
    </div>
</section>

@once
    <style>
        .wow-mobile-search-page{
            --wow-mobile-results-offset: 38px;
            --wow-mobile-map-height: 220px;
            --wow-mobile-map-width: calc(100vw - 24px);
            padding-top: 40px;
            margin-top: 50px;
            padding-bottom: 32px;
            position: relative;
            z-index: 1;
        }

        .wow-mobile-search-page__summary{
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap:12px;
            margin-bottom:14px;
            padding:0 26px 4px;
            position: relative;
            z-index: 0 !important;
        }
        body.wow-search-pane-open .wow-mobile-search-page__summary,
        body.wow-search-pane-open .wow-mobile-search-page__chips,
        body.wow-search-pane-open .wow-mobile-search-page__summary .wow-filter-actions{
            z-index: 0 !important;
            pointer-events: none;
        }
        body.wow-search-pane-open #search-v4-root,
        body.wow-search-pane-open .wow-mobile-search-page{
            position: relative;
            z-index: 60000 !important;
            isolation: isolate;
        }
        body.wow-search-pane-open .wow-search-filter .pane{
            z-index: 60010 !important;
        }

        .wow-mobile-search-page__map-wrap{
            position: sticky;
            top: 82px;
            left:0;
            right:0;
            transform:none;
            z-index:4;
            pointer-events:auto;
            width: calc(var(--wow-mobile-map-width) - 15px);
            max-width: calc(100vw - 39px);
            margin: 0 auto;
            overflow: hidden;
            border-radius:22px;
            transition: width .24s ease, border-radius .24s ease, box-shadow .24s ease;
        }

        .wow-mobile-search-page__map{
            width:100%;
            height:var(--wow-mobile-map-height);
            border-radius:22px;
            overflow:hidden;
            border:1px solid rgba(16,24,40,.12);
            box-shadow:0 16px 42px rgba(16,24,40,.14);
            background:#fff;
            pointer-events:auto;
            transition: border-radius .24s ease, box-shadow .24s ease;
        }

        .wow-mobile-search-page__map-overlay{
            position:absolute;
            inset:0;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:10px;
            background:rgba(17,24,39,.76);
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            border-radius:22px;
            opacity:0;
            visibility:hidden;
            transition: opacity .16s ease, visibility .16s ease;
            pointer-events:none;
            z-index:6;
        }

        .wow-mobile-search-page__map-wrap.is-loading .wow-mobile-search-page__map-overlay{
            opacity:1;
            visibility:visible;
        }

        .wow-mobile-search-page__map-spinner{
            width:28px;
            height:28px;
            border-radius:50%;
            border:3px solid rgba(255,255,255,.22);
            border-top-color:#fff;
            animation:wowMobileMapSpin .8s linear infinite;
            flex:0 0 auto;
        }

        .wow-mobile-search-page__map-overlay-text{
            color:#fff;
            font-size:13px;
            font-weight:700;
            letter-spacing:-0.01em;
            text-shadow:none;
        }

        @keyframes wowMobileMapSpin{
            to{ transform: rotate(360deg); }
        }

        .wow-mobile-search-page__map .mapboxgl-canvas,
        .wow-mobile-search-page__map .mapboxgl-canvas-container{
            pointer-events:auto;
        }

        .wow-mobile-search-page .mapboxgl-ctrl-bottom-left,
        .wow-mobile-search-page .mapboxgl-ctrl-bottom-right{
            display:none !important;
        }

        .wow-mobile-search-page .wow-marker{
            position:relative;
            cursor:pointer;
            z-index:5;
            transform-origin:bottom center;
        }

        .wow-mobile-search-page .wow-marker__price{
            position:relative;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            min-width:76px;
            height:36px;
            padding:0 13px;
            border-radius:999px;
            background:#fff;
            color:#222;
            font-size:13px;
            line-height:1;
            font-weight:800;
            letter-spacing:-0.02em;
            white-space:nowrap;
            box-shadow:0 4px 12px rgba(15, 23, 42, 0.18), 0 1px 3px rgba(15, 23, 42, 0.12);
            border:1px solid rgba(15, 23, 42, 0.12);
            transition: background 0.18s ease, color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }

        .wow-mobile-search-page .wow-marker__price::after{
            content:"";
            position:absolute;
            left:50%;
            bottom:-5px;
            transform:translateX(-50%) rotate(45deg);
            width:11px;
            height:11px;
            background:#fff;
            border-right:1px solid rgba(15, 23, 42, 0.12);
            border-bottom:1px solid rgba(15, 23, 42, 0.12);
            transition: background 0.18s ease, border-color 0.18s ease;
        }

        .wow-mobile-search-page .wow-marker.is-active{
            z-index:50 !important;
        }

        .wow-mobile-search-page .wow-marker.is-active .wow-marker__price{
            background:#222;
            color:#fff;
            border-color:#222;
            transform: translateY(-2px) scale(1.05);
            box-shadow:0 8px 22px rgba(0,0,0,0.32), 0 2px 6px rgba(0,0,0,0.22);
        }

        .wow-mobile-search-page .wow-marker.is-active .wow-marker__price::after{
            background:#222;
            border-color:#222;
        }

        .wow-mobile-search-page .mapboxgl-popup{
            z-index: 999;
        }

        .wow-mobile-search-page .mapboxgl-popup-content{
            border-radius: 14px;
            box-shadow: 0 18px 40px rgba(16,24,40,.18);
        }

        .wow-mobile-search-page__results[aria-busy="true"]{
            opacity: .72;
            transition: opacity .15s ease;
        }

        .wow-mobile-search-page__results{
            margin-top: 30px;
            background: #fff;
            border: 1px solid #aaa;
            padding: 50px 0 30px;
            border-radius: 20px 20px 0 0;
            position: relative;
            z-index: 5;
            border-bottom: none;
            width: min(426px, calc(100% - 24px));
            max-width: 426px;
            margin-left: auto;
            margin-right: auto;
        }

        .wow-mobile-search-page__results::before{
            content: "";
            position: absolute;
            top: 19px;
            left: 50%;
            transform: translateX(-50%);
            width: 90px;
            height: 8px;
            background: #ddd;
            border-radius: 999px;
            z-index: 5;
            pointer-events: none;
        }

        .wow-mobile-search-page__pagination{
            margin-top: 20px;
        }

        .wow-mobile-search-page #wowMobileResultsGrid > *{
            display: flex;
            justify-content: center;
        }

        .wow-mobile-search-page #wowMobileResultsGrid > .col-12{
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .wow-mobile-search-page #wowMobileResultsGrid .wow-card-sm-wrap{
            width: min(100%, 560px);
            margin-inline: auto;
        }

        .wow-mobile-search-page #wowMobileResultsGrid .result-view-map{
            display: none !important;
        }

        .wow-mobile-search-page #wowMobileResultsGrid .result-view-list{
            display: flex !important;
            justify-content: center;
        }

        .wow-mobile-search-page #wowMobileResultsGrid .result-view-list .wow-card.md{
            width: 100%;
            max-width: none;
            margin-inline: 0;
        }

        .wow-mobile-search-page #wowMobileResultsGrid .result-view-list .wow-card.md.wow-event-card-v4{
            width: 280px;
            max-width: 280px;
            flex: 0 0 280px;
            margin-inline: auto;
        }

        .wow-mobile-search-page #wowMobileResultsGrid .product-v4-1-card-scope{
            width: min(100%, 560px);
            margin-inline: auto;
        }

        .wow-mobile-search-page #wowMobileResultsGrid .product-v4-1-ghost-card-scope{
            display: flex;
            justify-content: center;
            width: min(100%, 560px);
            margin-inline: auto;
        }

        .wow-mobile-search-page #wowMobileResultsGrid .product-v4-1-ghost-card{
            margin-inline: auto;
        }

        .wow-mobile-search-page #wowMobileResultsGrid .product-v4-1-ghost-card__body,
        .wow-mobile-search-page #wowMobileResultsGrid .product-v4-1-ghost-card__footer{
            align-items:center;
        }

        .wow-mobile-search-page #wowMobileResultsGrid .product-v4-1-ghost-card__rating,
        .wow-mobile-search-page #wowMobileResultsGrid .product-v4-1-ghost-card__availability-top,
        .wow-mobile-search-page #wowMobileResultsGrid .product-v4-1-ghost-card__actions{
            justify-content:center;
        }

        .wow-mobile-search-page #wowMobileResultsGrid .product-v4-1-ghost-card__summary,
        .wow-mobile-search-page #wowMobileResultsGrid .product-v4-1-ghost-card__meta,
        .wow-mobile-search-page #wowMobileResultsGrid .product-v4-1-ghost-card__price{
            width: 100%;
            justify-items:center;
            justify-content:center;
            margin-inline:auto;
        }

        .wow-mobile-search-page #wowMobileResultsGrid .product-v4-1-ghost-card__summary .product-v4-1-ghost-card__line,
        .wow-mobile-search-page #wowMobileResultsGrid .product-v4-1-ghost-card__meta .product-v4-1-ghost-card__chip,
        .wow-mobile-search-page #wowMobileResultsGrid .product-v4-1-ghost-card__price .product-v4-1-ghost-card__price-label,
        .wow-mobile-search-page #wowMobileResultsGrid .product-v4-1-ghost-card__price .product-v4-1-ghost-card__price-value{
            margin-inline: auto;
        }

        .wow-mobile-search-page #wowMobileResultsGrid .product-v4-1-card{
            margin-inline: auto;
        }

        .wow-mobile-search-page .wow-mobile-search-page__chips{
            display:flex !important;
            flex-wrap:wrap;
            gap:8px;
            min-width:0;
            position: relative;
            z-index: 0 !important;
        }

        .wow-mobile-search-page .wow-mobile-search-page__chips .wow-chip:not(.wow-chip--clear-all){
            display:none !important;
        }

        .wow-mobile-search-page .wow-chip{
            display:inline-flex;
            align-items:center;
            gap:6px;
        }

        .wow-mobile-search-page .wow-chip--clear-all{
            appearance:none;
            border:1px solid #dcebe5;
            cursor:pointer;
            padding-inline:14px;
            font-weight:700;
            color:#215447;
            background:#f6fbf9;
            gap:8px;
        }

        .wow-mobile-search-page .wow-chip--clear-all svg{
            width:14px;
            height:14px;
            flex:0 0 auto;
            fill:currentColor;
        }

        .wow-mobile-search-page .wow-chip-remove{
            display:inline-flex;
            align-items:center;
            justify-content:center;
        }

        .wow-mobile-search-page .wow-chip-remove svg{
            width:14px;
            height:14px;
        }

        .wow-mobile-search-page .wow-filter-actions{
            flex:0 0 auto;
            position: relative;
            z-index: 0 !important;
        }

        @media (min-width: 1041px){
            .wow-mobile-search-page{
                display: none;
            }
        }
    </style>
@endonce

<script>
    (function () {
        const root = document.getElementById('wowMobileSearch');
        if (!root) return;
        if (!window.matchMedia || !window.matchMedia('(max-width: 1040px)').matches) return;

        const grid = root.querySelector('#wowMobileResultsGrid');
        const paginationEl = root.querySelector('#wowMobileResultsPagination');
        const countEl = root.querySelector('#wowMobileResultsCount');
        const mapEl = root.querySelector('#wowMobileSearchMap');
        const mapWrap = root.querySelector('.wow-mobile-search-page__map-wrap');
        const searchBar = document.querySelector('.wow-search-filter');
        const clearAllButton = root.querySelector('[data-clear-all-filters]');
        const mapboxToken = @json(config('services.mapbox.token'));
        const initialMapData = @json($mobileMapData);

        if (!grid) return;

        let currentRequestId = 0;
        let activeAbortController = null;
        let lastGridHtml = grid.innerHTML;
        let lastPaginationHtml = paginationEl ? paginationEl.innerHTML : '';
        let lastCountText = countEl ? countEl.textContent : '';
        let resizeObserver = null;
        let resizeFrame = null;
        let mapWidthFrame = null;
        let mapWrapBaseTop = null;
        let mapBusyTimer = null;
        let mapboxgl = window.mapboxgl || null;
        let mapRenderToken = 0;
        const mapState = {
            map: null,
            ready: false,
            pendingData: Array.isArray(initialMapData) ? initialMapData.slice() : [],
            markersByPid: {},
            activePid: '',
        };

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, (match) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
            })[match]);
        }

        function clearMapMarkers() {
            Object.keys(mapState.markersByPid || {}).forEach((pid) => {
                (mapState.markersByPid[pid] || []).forEach((entry) => {
                    try { entry.marker.remove(); } catch (_err) {}
                });
            });
            mapState.markersByPid = {};
        }

        function scheduleMapResize() {
            if (!mapState.map) return;

            setMobileMapBusy(true);

            window.requestAnimationFrame(() => {
                try { mapState.map.resize(); } catch (_err) {}
            });

            window.clearTimeout(mapBusyTimer);
            mapBusyTimer = window.setTimeout(() => {
                try { mapState.map.resize(); } catch (_err) {}
                setMobileMapBusy(false);
            }, 280);
        }

        function setMobileMapBusy(isBusy) {
            if (!mapWrap) return;
            mapWrap.classList.toggle('is-loading', !!isBusy);
        }

        function closeMapPopups() {
            Object.keys(mapState.markersByPid || {}).forEach((pid) => {
                (mapState.markersByPid[pid] || []).forEach((entry) => {
                    try {
                        const popup = entry && entry.marker && entry.marker.getPopup ? entry.marker.getPopup() : null;
                        if (popup) popup.remove();
                    } catch (_err) {}
                });
            });
        }

        function setActiveMarker(pid) {
            Object.keys(mapState.markersByPid || {}).forEach((key) => {
                (mapState.markersByPid[key] || []).forEach((entry) => {
                    try {
                        entry.el.classList.toggle('is-active', String(key) === String(pid));
                    } catch (_err) {}
                });
            });
            mapState.activePid = String(pid || '');
        }

        function setMapPanelExpanded() {
            scheduleMapResize();
        }

        function clamp(value, min, max) {
            return Math.max(min, Math.min(max, value));
        }

        function captureMapWrapBaseline() {
            if (!mapWrap) return;
            if (mapWrapBaseTop !== null) return;

            const currentScrollY = window.scrollY || window.pageYOffset || 0;
            mapWrapBaseTop = mapWrap.getBoundingClientRect().top + currentScrollY;
        }

        function updateMobileMapWidth() {
            if (!mapWrap) return;
            captureMapWrapBaseline();

            const viewportWidth = window.innerWidth || document.documentElement.clientWidth || 0;
            const fullWidth = Math.max(0, viewportWidth - 24);
            const minWidth = Math.min(316, fullWidth);
            const currentScrollY = window.scrollY || window.pageYOffset || 0;
            const stickyTop = Math.max(
                0,
                Math.round(parseFloat((window.getComputedStyle(mapWrap).top || '72px')) || 72)
            );
            const collapseStartY = Math.max(0, Math.round((mapWrapBaseTop || 0) - stickyTop));
            const shrinkAmount = Math.max(0, currentScrollY - collapseStartY);
            const width = Math.max(minWidth, Math.round(fullWidth - shrinkAmount));
            const nextWidth = `${width}px`;

            if (root.style.getPropertyValue('--wow-mobile-map-width').trim() !== nextWidth) {
                root.style.setProperty('--wow-mobile-map-width', nextWidth);
                scheduleMapResize();
            }
        }

        function scheduleMobileMapWidthUpdate() {
            if (mapWidthFrame) {
                cancelAnimationFrame(mapWidthFrame);
            }

            mapWidthFrame = window.requestAnimationFrame(() => {
                mapWidthFrame = null;
                updateMobileMapWidth();
            });
        }

        function buildMarkerEntry(item, map) {
            const el = document.createElement('div');
            el.className = 'wow-marker';
            el.setAttribute('role', 'button');
            el.setAttribute('tabindex', '0');
            el.setAttribute('aria-label', 'Map marker');
            el.title = item.title || '';
            el.innerHTML = '<span class="wow-marker__price">' + escapeHtml(item.price_label || 'View') + '</span>';
            const popup = new mapboxgl.Popup({ offset: 8 });
            const renderPopupHtml = () => {
                let priceText = '';
                try {
                    priceText = el.querySelector('.wow-marker__price')?.textContent || '';
                } catch (_err) {}
                return (
                    `<div style="font-weight:600">${escapeHtml(item.title || '')}</div>` +
                    (priceText ? `<div style="margin-top:4px;color:#334155;font-size:13px;">${escapeHtml(priceText)}</div>` : '')
                );
            };

            const marker = new mapboxgl.Marker({ element: el, anchor: 'bottom' })
                .setLngLat([Number(item.lng), Number(item.lat)])
                .addTo(map);

            const pid = String(item.pid || item.id || '');
            if (!mapState.markersByPid[pid]) {
                mapState.markersByPid[pid] = [];
            }
            mapState.markersByPid[pid].push({ marker, el, popup, item });

            el.addEventListener('click', function (event) {
                try { event.stopPropagation(); } catch (_err) {}
                closeMapPopups();
                setActiveMarker(pid);
                setMapPanelExpanded();
                try { popup.setHTML(renderPopupHtml()); } catch (_err) {}
                try {
                    popup.setLngLat([Number(item.lng), Number(item.lat)]).addTo(map);
                } catch (_err) {}
            });

            el.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    try { event.preventDefault(); } catch (_err) {}
                    el.click();
                }
            });

            popup.on('close', () => {
                if (String(mapState.activePid || '') === pid) {
                    setActiveMarker('');
                }
            });
        }

        function renderMapData(data) {
            mapState.pendingData = Array.isArray(data) ? data.slice() : [];
            if (!mapState.map || !mapState.ready) return;

            setMobileMapBusy(true);

            const token = ++mapRenderToken;
            clearMapMarkers();

            if (!mapState.pendingData.length) {
                setMobileMapBusy(false);
                return;
            }

            const bounds = new mapboxgl.LngLatBounds();
            let added = 0;

            mapState.pendingData.forEach((item) => {
                if (!Number.isFinite(Number(item.lat)) || !Number.isFinite(Number(item.lng))) return;
                buildMarkerEntry(item, mapState.map);
                bounds.extend([Number(item.lng), Number(item.lat)]);
                added += 1;
            });

            if (token !== mapRenderToken) return;

            if (added > 1) {
                try {
                    mapState.map.fitBounds(bounds, { padding: 80, maxZoom: 13, duration: 500 });
                } catch (_err) {}
            } else if (added === 1) {
                const only = mapState.pendingData[0];
                try {
                    mapState.map.setCenter([Number(only.lng), Number(only.lat)]);
                    mapState.map.setZoom(13);
                } catch (_err) {}
            }

            window.clearTimeout(mapBusyTimer);
            mapBusyTimer = window.setTimeout(() => {
                setMobileMapBusy(false);
            }, 180);
        }

        function initMapbox(token) {
            if (!mapEl) return;
            if (!token) {
                mapEl.innerHTML = '<div style="padding:12px;color:#334155;font-size:14px;">Map unavailable: missing MAPBOX_API_KEY. Set it in .env.</div>';
                return;
            }

            const center = mapState.pendingData.length
                ? [Number(mapState.pendingData[0].lng), Number(mapState.pendingData[0].lat)]
                : [-0.1276, 51.5072];

            mapboxgl.accessToken = token;
            mapState.map = new mapboxgl.Map({
                container: mapEl,
                style: 'mapbox://styles/mapbox/streets-v12',
                center,
                zoom: 12,
                pitch: 0,
                bearing: 0,
                antialias: true,
                fadeDuration: 0,
                attributionControl: false,
            });
            mapState.map.scrollZoom.disable();
            mapState.map.dragPan.disable();
            mapState.map.touchZoomRotate.disable();
            mapState.map.doubleClickZoom.disable();
            mapState.map.boxZoom.disable();
            mapState.map.keyboard.disable();

            mapState.map.on('load', () => {
                try {
                    const canvas = mapState.map.getCanvas ? mapState.map.getCanvas() : null;
                    const canvasContainer = mapState.map.getCanvasContainer ? mapState.map.getCanvasContainer() : null;
                    if (canvas) {
                        canvas.style.touchAction = '';
                        canvas.style.webkitTouchAction = '';
                        canvas.style.msTouchAction = '';
                    }
                    if (canvasContainer) {
                        canvasContainer.style.touchAction = '';
                        canvasContainer.style.webkitTouchAction = '';
                        canvasContainer.style.msTouchAction = '';
                    }
                } catch (_err) {}
                mapState.ready = true;
                renderMapData(mapState.pendingData);
                setMobileMapBusy(false);
            });
        }

        function ensureMapboxAssets(token) {
            if (!mapEl) return;

            if (!document.querySelector('link[href*="mapbox-gl.css"]')) {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://api.mapbox.com/mapbox-gl-js/v3.6.0/mapbox-gl.css';
                document.head.appendChild(link);
            }

            if (!mapboxgl) {
                const script = document.createElement('script');
                script.src = 'https://api.mapbox.com/mapbox-gl-js/v3.6.0/mapbox-gl.js';
                script.async = true;
                script.defer = true;
                script.onload = () => {
                    mapboxgl = window.mapboxgl || null;
                    if (mapboxgl) initMapbox(token);
                };
                document.head.appendChild(script);
                return;
            }

            initMapbox(token);
        }

        function normalizeUrl(url) {
            try {
                return new URL(url || window.location.href, window.location.origin).toString();
            } catch (_err) {
                return String(url || window.location.href || '');
            }
        }

        function currentCountFromText(text) {
            const match = String(text || '').match(/^(\d+)/);
            return match ? Number(match[1]) || 0 : 0;
        }

        function syncHistory(url) {
            try {
                window.history.replaceState({}, '', url);
            } catch (_err) {}
        }

        function dispatchResultsUpdated(count, countText) {
            try {
                window.dispatchEvent(new CustomEvent('wow:searchbar-v4:results-updated', {
                    detail: {
                        count: typeof count === 'number' ? count : null,
                        countText: typeof countText === 'string' && countText.trim() !== ''
                            ? countText
                            : lastCountText,
                    },
                }));
            } catch (_err) {}
        }

        function updateCount(count, countText) {
            if (countEl) {
                if (typeof countText === 'string' && countText.trim() !== '') {
                    countEl.textContent = countText;
                } else if (typeof count === 'number') {
                    countEl.textContent = count + ' results';
                }
            }

            lastCountText = countEl ? countEl.textContent : lastCountText;
            dispatchResultsUpdated(
                typeof count === 'number' ? count : currentCountFromText(lastCountText),
                lastCountText
            );
        }

        function renderGridHtml(html) {
            if (typeof html !== 'string') return;
            grid.innerHTML = html;
        }

        function renderPaginationHtml(html) {
            if (!paginationEl || typeof html !== 'string') return;
            paginationEl.innerHTML = html;
        }

        function setLoadingState(isLoading) {
            grid.setAttribute('aria-busy', isLoading ? 'true' : 'false');
            if (paginationEl) {
                paginationEl.setAttribute('aria-busy', isLoading ? 'true' : 'false');
            }
        }

        function releasePageScroll() {
            try {
                document.body.style.overflow = '';
                document.documentElement.style.overflow = '';
            } catch (_err) {}
        }

        function clearAllFilters() {
            const nextUrl = normalizeUrl(window.location.href);
            const url = new URL(nextUrl);
            ['what', 'where', 'when', 'when_start', 'when_end', 'adults', 'group_type', 'sort', 'price_max', 'rating', 'type', 'mode', 'anytime'].forEach((key) => {
                url.searchParams.delete(key);
            });
            const cleanUrl = url.toString();

            try {
                window.history.replaceState({}, '', cleanUrl);
            } catch (_err) {}

            try {
                window.dispatchEvent(new CustomEvent('wow:searchbar-v4:query-change', {
                    detail: {
                        reason: 'clear_all',
                        url: cleanUrl,
                    },
                }));
            } catch (_err) {}
        }

        function updateSearchBarOffset() {
            if (!searchBar) return;
            const rect = searchBar.getBoundingClientRect();
            const bottom = Math.max(0, Math.round(rect.bottom || 0));
            const offset = Math.max(38, bottom + 20);
            root.style.setProperty('--wow-mobile-results-offset', `${offset}px`);
        }

        function scheduleOffsetUpdate() {
            if (resizeFrame) {
                cancelAnimationFrame(resizeFrame);
            }

            resizeFrame = window.requestAnimationFrame(() => {
                resizeFrame = null;
                updateSearchBarOffset();
                updateMobileMapWidth();
            });
        }

        function fetchSearchResults(url) {
            const nextUrl = normalizeUrl(url);
            const requestId = ++currentRequestId;

            if (activeAbortController) {
                try {
                    activeAbortController.abort();
                } catch (_err) {}
            }

            activeAbortController = new AbortController();
            lastGridHtml = grid.innerHTML;
            lastPaginationHtml = paginationEl ? paginationEl.innerHTML : lastPaginationHtml;
            lastCountText = countEl ? countEl.textContent : lastCountText;

            syncHistory(nextUrl);
            releasePageScroll();
            setLoadingState(true);

            fetch(nextUrl, {
                cache: 'no-store',
                credentials: 'same-origin',
                signal: activeAbortController.signal,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Search API request failed: ' + response.status);
                    }
                    return response.json();
                })
                .then((payload) => {
                    if (requestId !== currentRequestId) return;

                    if (payload && typeof payload.grid_html === 'string') {
                        renderGridHtml(payload.grid_html);
                        lastGridHtml = grid.innerHTML;
                    }

                    if (payload && typeof payload.pagination_html === 'string') {
                        renderPaginationHtml(payload.pagination_html);
                        lastPaginationHtml = paginationEl ? paginationEl.innerHTML : lastPaginationHtml;
                    }

                    updateCount(
                        payload && typeof payload.count === 'number' ? payload.count : null,
                        payload && typeof payload.count_text === 'string' ? payload.count_text : null
                    );

                    if (payload && Array.isArray(payload.map_data)) {
                        renderMapData(payload.map_data);
                    } else {
                        renderMapData([]);
                    }

                    scheduleOffsetUpdate();
                })
                .catch((error) => {
                    if (error && error.name === 'AbortError') return;

                    console.warn('[search-mobile] api refresh failed', error);
                    grid.innerHTML = lastGridHtml;
                    if (paginationEl) {
                        paginationEl.innerHTML = lastPaginationHtml;
                    }
                    if (countEl) {
                        countEl.textContent = lastCountText;
                    }
                    dispatchResultsUpdated(currentCountFromText(lastCountText), lastCountText);
                })
                .finally(() => {
                    if (requestId !== currentRequestId) return;
                    releasePageScroll();
                    setLoadingState(false);
                });
        }

        function handleQueryChange(event) {
            const detail = event && event.detail ? event.detail : {};
            if (!detail.url) return;
            fetchSearchResults(detail.url);
        }

        function handlePopState() {
            fetchSearchResults(window.location.href);
        }

        function bindOffsetObservers() {
            if (searchBar && typeof ResizeObserver !== 'undefined') {
                resizeObserver = new ResizeObserver(() => {
                    scheduleOffsetUpdate();
                });
                resizeObserver.observe(searchBar);
            }

            window.addEventListener('resize', scheduleOffsetUpdate, { passive: true });
            window.addEventListener('scroll', scheduleMobileMapWidthUpdate, { passive: true });
            window.addEventListener('wow:searchbar-v4:layout-change', scheduleOffsetUpdate);
            window.addEventListener('wow:searchbar-v4:results-updated', scheduleOffsetUpdate);
            document.addEventListener('click', (event) => {
                const marker = event.target.closest('.wow-marker');
                const popup = event.target.closest('.mapboxgl-popup');
                if (!marker && !popup) {
                    closeMapPopups();
                    setActiveMarker('');
                }
            }, true);
            captureMapWrapBaseline();
            scheduleOffsetUpdate();
        }

        if (clearAllButton) {
            clearAllButton.addEventListener('click', clearAllFilters);
        }

        window.addEventListener('wow:searchbar-v4:query-change', handleQueryChange);
        window.addEventListener('popstate', handlePopState);
        bindOffsetObservers();
        ensureMapboxAssets(mapboxToken || window.WOW_MAPS_KEY || '');
        releasePageScroll();
        dispatchResultsUpdated(currentCountFromText(lastCountText), lastCountText);

        window.requestAnimationFrame(function () {
            fetchSearchResults(window.location.href);
        });
    })();
</script>
