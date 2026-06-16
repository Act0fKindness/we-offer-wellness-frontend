@php
    $initialCount = (int) ($resultCount ?? ($products?->count() ?? 0));
@endphp

<section id="wowMobileSearch" class="wow-mobile-search-page" aria-label="Search results">
    <div class="container-fluid px-3 px-sm-4">
        <div class="wow-mobile-search-page__header">
            <h2 id="wowMobileResultsCount">{{ number_format($initialCount) }} results</h2>
            <p>Live results update as you adjust What, When, Where and Who.</p>
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
            padding-bottom: 32px;
            position: relative;
            z-index: 1;
        }

        .wow-mobile-search-page__header{
            padding: 0 2px 12px;
        }

        .wow-mobile-search-page__header h2{
            margin: 0;
            color: #0f172a;
            font-size: 20px;
            font-weight: 800;
            line-height: 1.15;
        }

        .wow-mobile-search-page__header p{
            margin: 4px 0 0;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.4;
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
            display: block !important;
        }

        .wow-mobile-search-page #wowMobileResultsGrid .result-view-list .wow-card.md{
            width: 100%;
            max-width: none;
            margin-inline: 0;
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

        window.addEventListener('wow:searchbar-v4:query-change', handleQueryChange);
        window.addEventListener('popstate', handlePopState);
        bindOffsetObservers();
        dispatchResultsUpdated(currentCountFromText(lastCountText), lastCountText);
    })();
</script>
