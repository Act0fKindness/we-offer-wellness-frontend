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
@endphp

<section id="wowMobileSearch" class="wow-mobile-search-page" aria-label="Search results">
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
</section>

@once
    <style>
        .wow-mobile-search-page{
            --wow-mobile-results-offset: 38px;
            padding-top: var(--wow-mobile-results-offset);
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
        }

        .wow-mobile-search-page__results[aria-busy="true"]{
            opacity: .72;
            transition: opacity .15s ease;
        }

        .wow-mobile-search-page__pagination{
            margin-top: 20px;
        }

        .wow-mobile-search-page #wowMobileResultsGrid > [data-pid]{
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

        .wow-mobile-search-page #wowMobileResultsGrid .product-v4-1-card{
            margin-inline: auto;
        }

        .wow-mobile-search-page .wow-mobile-search-page__chips{
            display:flex !important;
            flex-wrap:wrap;
            gap:8px;
            min-width:0;
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
        const searchBar = document.querySelector('.wow-search-filter');
        const clearAllButton = root.querySelector('[data-clear-all-filters]');

        if (!grid) return;

        let currentRequestId = 0;
        let activeAbortController = null;
        let lastGridHtml = grid.innerHTML;
        let lastPaginationHtml = paginationEl ? paginationEl.innerHTML : '';
        let lastCountText = countEl ? countEl.textContent : '';
        let resizeObserver = null;
        let resizeFrame = null;

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
            window.addEventListener('wow:searchbar-v4:layout-change', scheduleOffsetUpdate);
            window.addEventListener('wow:searchbar-v4:results-updated', scheduleOffsetUpdate);
            scheduleOffsetUpdate();
        }

        if (clearAllButton) {
            clearAllButton.addEventListener('click', clearAllFilters);
        }

        window.addEventListener('wow:searchbar-v4:query-change', handleQueryChange);
        window.addEventListener('popstate', handlePopState);
        bindOffsetObservers();
        releasePageScroll();
        dispatchResultsUpdated(currentCountFromText(lastCountText), lastCountText);

        window.requestAnimationFrame(function () {
            fetchSearchResults(window.location.href);
        });
    })();
</script>
