<section class="py-6 md:py-10">
  <div class="search-results-shell container-fluid space-y-4">
    <div class="row gx-4 search-layout">
      <div class="col-12 col-lg-6 col-results">
        <div class="results-scroll" id="searchResultsScroll" aria-live="polite">
    <div class="row g-3" id="searchResultsGrid" data-ghost-count-map="6" data-ghost-count-list="8">
            @include('search.partials.results_cards', [
              'products' => $products,
              'ghostCount' => request('view') === 'list' ? 8 : 6,
            ])
          </div>

          <div id="searchResultsPagination" class="mt-4">
            @if($products instanceof \Illuminate\Pagination\Paginator || $products instanceof \Illuminate\Pagination\LengthAwarePaginator)
              {{ $products->withQueryString()->onEachSide(1)->links('pagination::bootstrap-4') }}
            @endif
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-6 col-map">
        <div class="map-wrap">
          <div id="search-map" class="map"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="d-none" aria-hidden="true">
  @include('partials.product_card_v4_1_ghost')
</div>

<template id="searchResultsGhostTemplate">
  <div class="col-12 col-md-6">
    <div class="wow-card-sm-wrap">
      <div class="result-view-map">
        @include('partials.product_card_v4_1_ghost')
      </div>
      <div class="result-view-list">
        @include('partials.product_card_v4_1_ghost')
      </div>
    </div>
  </div>
</template>

<style>
/* Desktop split: page scrolls the list; map stays sticky */
@media (min-width: 992px){
  .results-scroll{ padding-right: 6px; }
  .search-layout.sr-list-only .results-scroll{
    padding-right: 0px;
  }
  .col-map{
    position: sticky;
    top: 127px;
    align-self: flex-start;
  }
  .search-layout.sr-list-only .results-scroll .row{
    justify-content: center;
  }
  .map-wrap{ position: relative; border-radius: 13px; overflow: hidden; }
  /* Adjust height to account for header + search bar */
  .map{ width: 100%; height: calc(100vh - 80px - 67px); border: 1px solid var(--ink-200); border-radius: 3px; overflow: hidden; }
}
.wow-ultra .bar{
  z-index: 4900;
}
.wow-ultra .pane,
.wow-ultra #search-top-what-pane,
.wow-ultra #search-top-where-pane,
.wow-ultra #search-top-when-pane,
.wow-ultra #search-top-who-pane,
.wow-ultra #search-top-group-pane{
  z-index: 20000 !important;
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
.wow-marker{
  position:relative;
  cursor:pointer;
  z-index:5;
  transform-origin:bottom center;
}
.wow-marker__price{
  position:relative;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-width:88px;
  height:42px;
  padding:0 16px;
  border-radius:999px;
  background:#fff;
  color:#222;
  font-size:15px;
  line-height:1;
  font-weight:800;
  letter-spacing:-0.02em;
  white-space:nowrap;
  box-shadow:0 4px 12px rgba(15, 23, 42, 0.18), 0 1px 3px rgba(15, 23, 42, 0.12);
  border:1px solid rgba(15, 23, 42, 0.12);
  transition: background 0.18s ease, color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
}
.wow-marker__price::after{
  content:"";
  position:absolute;
  left:50%;
  bottom:-6px;
  transform:translateX(-50%) rotate(45deg);
  width:13px;
  height:13px;
  background:#fff;
  border-right:1px solid rgba(15, 23, 42, 0.12);
  border-bottom:1px solid rgba(15, 23, 42, 0.12);
  transition: background 0.18s ease, border-color 0.18s ease;
}
.wow-marker.is-active{
  z-index:50 !important;
}
.wow-marker.is-active .wow-marker__price{
  background:#222;
  color:#fff;
  border-color:#222;
  transform: translateY(-2px) scale(1.05);
  box-shadow:0 8px 22px rgba(0,0,0,0.32), 0 2px 6px rgba(0,0,0,0.22);
}
.wow-marker.is-active .wow-marker__price::after{
  background:#222;
  border-color:#222;
}
.mapboxgl-marker{ pointer-events: auto; z-index: 5; }
.mapboxgl-popup{ z-index: 999; }
.mapboxgl-popup-content{
  padding: 0;
  background: transparent;
  box-shadow: none;
  border-radius: 0;
}
.mapboxgl-popup-tip{
  display: none;
}
.search-layout .mapboxgl-ctrl-bottom-left,
.search-layout .mapboxgl-ctrl-bottom-right{
  display:none !important;
}
.mapboxgl-popup-close-button{
  background-color: transparent;
  border: 0;
  border-radius: 0 3px 0 0;
  cursor: pointer;
  font-size: 30px;
  background: #fff;
  border-radius: 20px;
  height: 30px;
  width: 30px;
  margin-top: -35px;
  position: absolute;
  right: 0;
  top: 0;
  border: 1px solid #aaa;
}
.wow-map-popup{
  width: min(420px, calc(100vw - 40px));
  background:#fff;
  border-radius:24px;
  overflow:hidden;
  box-shadow: 0 20px 45px rgba(15, 23, 42, .18), 0 4px 16px rgba(15, 23, 42, .12);
  border: 1px solid rgba(15, 23, 42, .08);
}
.wow-map-popup__image-wrap{
  position:relative;
  width:100%;
  height:180px;
  background:#e5e7eb;
  overflow:hidden;
}
.wow-map-popup__image{
  width:100%;
  height:100%;
  object-fit:cover;
  display:block;
}
.wow-map-popup__body{
  padding:16px 18px 18px;
  background:#fff;
}
.wow-map-popup__top{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:12px;
}
.wow-map-popup__body{
  padding: 16px 18px 18px;
  background: #fff;
}
.wow-map-popup__title{
  margin: 0 0 6px;
  color:#222;
  font-size: 18px;
  line-height: 1.18;
  font-weight: 800;
  letter-spacing: -0.03em;
}
.wow-map-popup__footer{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap: 10px;
}
.wow-map-popup__rating{
  display:flex;
  align-items:center;
  gap:5px;
  flex:0 0 auto;
  color:#222;
  font-size:14px;
  line-height:1;
  font-weight:700;
  letter-spacing:-0.02em;
  white-space:nowrap;
}
.wow-map-popup__rating svg{
  width:16px;
  height:16px;
  fill:currentColor;
  transform:translateY(-1px);
}
.wow-map-popup__link{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  padding: 8px 12px;
  border-radius: 999px;
  background:#222;
  color:#fff;
  font-size: 12px;
  font-weight: 700;
  text-decoration:none;
  white-space: nowrap;
  transition: background-color .18s ease, transform .18s ease;
}
.wow-map-popup__link:hover{
  background:#444;
  transform: translateY(-1px);
}
/* Desktop-only temporary glass styling for search bar */
@media (min-width: 992px){
  .wow-ultra .bar{
    background: rgba(255,255,255,.14);
    border-radius: 19px;
    border:3px solid rgba(0,0,0,0.1);
    position: fixed;
    top: 126px;
    z-index: 2000;
    left: 50%; transform: translateX(-50%);
    width: min(1200px, calc(100vw - 32px));
    -webkit-backdrop-filter: blur(14px);
    backdrop-filter: blur(14px);
    box-shadow: 0 14px 40px rgba(16,24,40,.14);
    transition: top .2s ease, width .18s ease;
    /* Allow dropdown panes to render outside the bar */
    overflow: visible;
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
  /* Reserve space so content sits in original position under fixed bar */
  .wow-ultra{ padding-top: 74px; }
  /* When page is scrolled, compact the search bar upward but keep it below the fixed header */
  .search-compact .wow-ultra .bar{ top: 126px; }
  /* Compact state: shrink width and softly fade Where/When/Who */
  .search-compact .wow-ultra .bar{ width: 400px; height: 74px; overflow: hidden; }
  .wow-ultra .seg{ transition: opacity .12s ease; }
  .search-compact .wow-ultra #search-top-seg-where,
  .search-compact .wow-ultra #search-top-seg-when,
  .search-compact .wow-ultra #search-top-seg-who{ opacity: 0; pointer-events: none; }
  /* Expand back on hover or focus within (desktop) */
  .search-compact .wow-ultra:hover .bar,
  .search-compact .wow-ultra:focus-within .bar{ width: min(1200px, calc(100vw - 32px)); overflow: visible; }
  .search-compact .wow-ultra:hover #search-top-seg-where,
  .search-compact .wow-ultra:hover #search-top-seg-when,
  .search-compact .wow-ultra:hover #search-top-seg-who,
  .search-compact .wow-ultra:focus-within #search-top-seg-where,
  .search-compact .wow-ultra:focus-within #search-top-seg-when,
  .search-compact .wow-ultra:focus-within #search-top-seg-who{ opacity: 1; pointer-events: auto; }
  /* Hide the round Search button in compact; fade back on hover/focus */
  .wow-ultra .btn-wow.is-squarish.btn-xl{ transition: opacity .12s ease; }
  .search-compact .wow-ultra .btn-wow.is-squarish.btn-xl{ opacity: 0; pointer-events: none; }
  .search-compact .wow-ultra:hover .btn-wow.is-squarish.btn-xl,
  .search-compact .wow-ultra:focus-within .btn-wow.is-squarish.btn-xl{ opacity: 1; pointer-events: auto; }
}
/* Active teardrop pin removed per request */
  .wow-marker__price{
    min-width:76px;
    height:36px;
    padding:0 13px;
    font-size:13px;
  }
.wow-marker__price::after{
  bottom:-5px;
  width:11px;
  height:11px;
}
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
  .search-layout.sr-list-only .col-results{
    flex: 0 0 100%;
    max-width: 100%;
  }
/* Toggle which card is shown per view */
.search-layout .result-view-map{ display:block; }
.search-layout .result-view-list{ display:none; }
.search-layout.sr-list-only .result-view-map{ display:none; }
.search-layout.sr-list-only .result-view-list{ display:block; }
.search-layout .result-view-map,
.search-layout .result-view-list{
  text-align:center;
}
.search-layout .result-view-map .product-v4-1-card-scope,
.search-layout .result-view-list .product-v4-1-card-scope,
.search-layout .result-view-map .product-v4-1-ghost-card-scope,
.search-layout .result-view-list .product-v4-1-ghost-card-scope{
  display:inline-block;
  text-align:left;
}
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
    background: rgba(255, 255, 255, .14);
    border-radius: 33px;
    border: none;
    border-top: 1px solid rgba(255, 255, 255, 0.5);
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    position: fixed;
    top: 84px;
    z-index: 30;
    left: 12px;
    right: 12px;
    -webkit-backdrop-filter: blur(14px);
    backdrop-filter: blur(14px);
    box-shadow: 0 14px 40px rgba(16, 24, 40, .14);
  }
  .wow-ultra .bar::before{ content:""; position:absolute; inset:0; border-radius: inherit; pointer-events:none; background: linear-gradient(180deg, rgba(255,255,255,.28), rgba(255,255,255,.08)); opacity:.55; }
  .wow-ultra .bar > *{ position: relative; z-index: 1; }
  /* Remove section-level sticky; bar itself is sticky on mobile */
  /* section.pt-4.pb-2.bg-transparent{ position: sticky; top:50px; z-index: 1000; } */
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
  /* Reserve vertical space under fixed bar on mobile */
  .wow-ultra{ padding-top: 58px; }
}
/* Search-only card sizing */
.search-layout .result-view-map .wow-card.md{
  width: 280px;
  max-width: 280px;
  height: 609px;
  margin: 0 auto !important;
}
.search-layout .result-view-map .therapy-card{
  width: 280px;
}
.search-layout .result-view-map .product-v4-ghost-card-scope .wow-card.md{
  width: 280px;
  max-width: 280px;
  flex: 0 0 280px;
  margin-inline: auto;
}
.search-layout .result-view-map .product-v4-ghost-card-scope .product-v4-ghost-card{
  width: 280px;
}
.search-layout .result-view-list .wow-card.md{
  --card-h: 530px;
  width: 100%;
  max-width: none;
  margin-inline: 0;
}
.search-layout .result-view-list .wow-card.md.wow-event-card-v4{
  width: 280px;
  max-width: 280px;
  flex: 0 0 280px;
  margin-inline: auto;
}
.search-layout .result-view-list .therapy-card{
  width: 100%;
}
.search-layout.sr-list-only .result-view-list .product-v4-ghost-card-scope .product-v4-ghost-card{
  width: 100%;
}
@media (min-width: 1600px){
  /* Ultra-wide map view: 3 columns only above xxl */
  .search-layout:not(.sr-list-only) .results-scroll .row > div{
    flex:0 0 33.333333%;
    max-width:33.333333%;
  }
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
/* Nudge Where label down slightly */
#search-top-seg-where .seg-label{ margin-top: 5px; }
/* Ensure Who pane never overflows right edge and has constrained height */
/* Who pane: clamp within viewport, max height 304px, hide scrollbars */
.wow-ultra #search-top-who-pane{
  left:auto !important; right:0 !important;
  width: min(560px, 96vw); max-width:96vw;
  height:auto; max-height:304px; overflow:auto;
  -ms-overflow-style: none; scrollbar-width: none;
}
.wow-ultra #search-top-who-pane::-webkit-scrollbar{ width:0; height:0 }
/* Requested narrow pane sizing */
.wow-ultra .pane.narrow{
  z-index: 2100;
  left: 0px !important;
  right: 0px !important;
  width: min(560px, 96vw);
  max-width: 96vw;
  height: auto;
  max-height: 304px;
  overflow: auto;
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.wow-ultra .pane.narrow::-webkit-scrollbar{ width:0; height:0 }
/* Constrain scrollable list */
/* Avoid inner list scrollbars in Who pane */
.wow-ultra #search-top-who-pane .listy{ max-height: none; overflow: visible; }

/* Search ghost cards */
.wow-search-card-ghost-scope{
  position:relative;
  overflow:hidden;
  pointer-events:none;
}
.wow-search-card-ghost-scope .wow-card-search{
  cursor:default;
}
.wow-search-card-ghost-scope .wow-row-card{
  background:rgba(255,255,255,.98);
}
.wow-search-card-ghost-scope .wow-row-media-inner,
.wow-search-card-ghost-scope .wow-row-body,
.wow-search-card-ghost-scope .wow-row-bottom{
  position:relative;
}
.wow-search-card-ghost-scope .wow-row-media-inner{
  background:
    radial-gradient(circle at 24% 28%, rgba(84,148,131,.12), transparent 30%),
    radial-gradient(circle at 72% 64%, rgba(36,78,145,.08), transparent 32%),
    linear-gradient(135deg, #eef2f4 0%, #f8fbfd 100%);
}
.wow-search-card-ghost-scope .wow-search-card-ghost__signal,
.wow-search-card-ghost-scope .wow-search-card-ghost__premium,
.wow-search-card-ghost-scope .wow-search-card-ghost__badge,
.wow-search-card-ghost-scope .wow-search-card-ghost__line,
.wow-search-card-ghost-scope .wow-search-card-ghost__chip,
.wow-search-card-ghost-scope .wow-search-card-ghost__star,
.wow-search-card-ghost-scope .wow-search-card-ghost__button{
  position:relative;
  overflow:hidden;
  background:linear-gradient(90deg, #e7edf3 0%, #f1f5f9 50%, #e7edf3 100%);
  background-size:220% 100%;
  animation:searchCardGhostShimmer 1.5s ease-in-out infinite;
}
.wow-search-card-ghost-scope .wow-search-card-ghost__signal::before,
.wow-search-card-ghost-scope .wow-search-card-ghost__premium::before,
.wow-search-card-ghost-scope .wow-search-card-ghost__badge::before,
.wow-search-card-ghost-scope .wow-search-card-ghost__line::before,
.wow-search-card-ghost-scope .wow-search-card-ghost__chip::before,
.wow-search-card-ghost-scope .wow-search-card-ghost__star::before,
.wow-search-card-ghost-scope .wow-search-card-ghost__button::before{
  content:"";
  position:absolute;
  inset:0;
  background:linear-gradient(90deg, transparent 0%, rgba(255,255,255,.72) 50%, transparent 100%);
  transform:translateX(-130%);
  animation:searchCardGhostSweep 1.55s ease-in-out infinite;
}
.wow-search-card-ghost-scope .wow-search-card-ghost__signal{
  position:absolute;
  top:9px;
  left:9px;
  width:118px;
  height:28px;
  border-radius:999px;
  box-shadow:0 8px 18px rgba(16,24,40,.08);
}
.wow-search-card-ghost-scope .wow-search-card-ghost__premium{
  position:absolute;
  left:18px;
  bottom:18px;
  width:40px;
  height:40px;
  border-radius:999px;
  box-shadow:0 8px 18px rgba(16,24,40,.08);
}
.wow-search-card-ghost-scope .wow-search-card-ghost__badge{
  min-height:28px;
  border-radius:5px;
  box-shadow:0 8px 18px rgba(16,24,40,.05);
}
.wow-search-card-ghost-scope .wow-search-card-ghost__badge--warm{
  width:122px;
}
.wow-search-card-ghost-scope .wow-search-card-ghost__badge--cool{
  width:96px;
}
.wow-search-card-ghost-scope .wow-search-card-ghost__line{
  display:block;
  border-radius:999px;
  margin-top:10px;
}
.wow-search-card-ghost-scope .wow-search-card-ghost__title{
  min-height:22px;
}
.wow-search-card-ghost-scope .wow-search-card-ghost__title--one{ width:86%; }
.wow-search-card-ghost-scope .wow-search-card-ghost__title--two{ width:72%; }
.wow-search-card-ghost-scope .wow-search-card-ghost__provider{ width:44%; min-height:18px; margin-top:8px; }
.wow-search-card-ghost-scope .wow-search-card-ghost__rating-copy{ width:170px; min-height:18px; margin-left:8px; }
.wow-search-card-ghost-scope .wow-search-card-ghost__summary{ min-height:18px; }
.wow-search-card-ghost-scope .wow-search-card-ghost__summary--one{ width:92%; }
.wow-search-card-ghost-scope .wow-search-card-ghost__summary--two{ width:80%; }
.wow-search-card-ghost-scope .wow-search-card-ghost__chip{
  min-height:28px;
  border-radius:999px;
}
.wow-search-card-ghost-scope .wow-search-card-ghost__chip--online{ width:118px; }
.wow-search-card-ghost-scope .wow-search-card-ghost__chip--location{ width:96px; }
.wow-search-card-ghost-scope .wow-search-card-ghost__stars{
  display:inline-flex;
  align-items:center;
  gap:4px;
}
.wow-search-card-ghost-scope .wow-search-card-ghost__star{
  width:10px;
  height:10px;
  border-radius:50%;
  display:inline-block;
}
.wow-search-card-ghost-scope .wow-search-card-ghost__star--empty{
  opacity:.55;
}
.wow-search-card-ghost-scope .wow-search-card-ghost__availability-title{
  width:128px;
  min-height:16px;
}
.wow-search-card-ghost-scope .wow-search-card-ghost__availability-note{
  width:156px;
  min-height:14px;
  margin-left:19px;
}
.wow-search-card-ghost-scope .wow-search-card-ghost__price-label{
  width:42px;
  min-height:16px;
}
.wow-search-card-ghost-scope .wow-search-card-ghost__price-value{
  width:92px;
  min-height:30px;
  margin-top:6px;
}
.wow-search-card-ghost-scope .wow-search-card-ghost__price-sub{
  width:118px;
  min-height:14px;
}
.wow-search-card-ghost-scope .wow-search-card-ghost__price-note{
  width:168px;
  min-height:14px;
}
.wow-search-card-ghost-scope .wow-search-card-ghost__button{
  width:100%;
  height:40px;
  border-radius:4px;
  box-shadow:0 10px 22px rgba(16,24,40,.08);
}

@keyframes searchCardGhostShimmer{
  0%{ background-position:200% 0; }
  100%{ background-position:-200% 0; }
}

@keyframes searchCardGhostSweep{
  0%{ transform:translateX(-130%); opacity:0; }
  30%{ opacity:1; }
  100%{ transform:translateX(130%); opacity:0; }
}
</style>

<script>
(function initSearchPage() {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSearchPage, { once: true });
    return;
  }

  if (window.matchMedia && window.matchMedia('(max-width: 1040px)').matches) {
    return;
  }

  try {
    var layout = document.querySelector('.search-layout');
    var resultsShell = document.querySelector('.search-results-shell');
    var resultsScroll = document.getElementById('searchResultsScroll');
    var grid = document.getElementById('searchResultsGrid');
    var countEl = document.getElementById('searchResultsCount');
    var paginationEl = document.getElementById('searchResultsPagination');
    var template = document.getElementById('searchResultsGhostTemplate');
    var tagsEl = document.getElementById('sr-tags');
    var mapEl = document.getElementById('search-map');

    var initialMapData = @json($searchMapData ?? []);
    var mapboxToken = @json(config('services.mapbox.token'));
    var fallbackMapToken = @json($mapsKey ?? '');
    var mapWrap = document.querySelector('.map-wrap');

    var currentView = 'map';
    var currentMapMode = '2d';
    var currentRequestId = 0;
    var activeAbortController = null;
    var lastGridHtml = grid ? grid.innerHTML : '';
    var lastPaginationHtml = paginationEl ? paginationEl.innerHTML : '';
    var lastCountText = countEl ? countEl.textContent : '';
    var initialGhostHtml = '';

    var mapState = {
      map: null,
      ready: false,
      pendingData: Array.isArray(initialMapData) ? initialMapData.slice() : [],
      markersByPid: {},
      activePid: '',
      activePopupPid: '',
    };
    var lastResultsScrollTop = resultsScroll ? resultsScroll.scrollTop : 0;
    var hoverCenterTimer = null;
    var hoverCenterPid = '';

    function escapeHtml(value) {
      return String(value || '').replace(/[&<>"']/g, function (char) {
        return ({
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#39;',
        })[char] || char;
      });
    }

    function normalizeText(value) {
      return String(value || '')
        .toLowerCase()
        .replace(/[\u2018\u2019\u201c\u201d]/g, "'")
        .replace(/&/g, ' and ')
        .replace(/[^a-z0-9]+/g, ' ')
        .trim();
    }

    function currentUrlParams(url) {
      try {
        return new URL(url || window.location.href, window.location.origin).searchParams;
      } catch (_err) {
        return new URLSearchParams(window.location.search || '');
      }
    }

    function syncHistory(url) {
      try {
        window.history.replaceState({}, '', url);
      } catch (_err) {}
    }

    function renderTagsFromUrl(url) {
      if (!tagsEl) return;
      var params = currentUrlParams(url);
      var tags = [];
      var seen = new Set();
      var what = (params.get('what') || '').trim();
      var where = (params.get('where') || '').trim();
      var mode = (params.get('mode') || '').trim().toLowerCase();
      var groupType = (params.get('group_type') || '').trim();
      var adults = (params.get('adults') || '').trim();
      var when = (params.get('when') || '').trim();
      var sort = (params.get('sort') || '').trim();
      var priceMax = (params.get('price_max') || '').trim();
      var rating = (params.get('rating') || '').trim();
      var type = (params.get('type') || '').trim();
      var anytime = (params.get('anytime') || '').trim();

      if (what) tags.push(what);
      if (mode === 'online') {
        tags.push('Online only');
      } else if (where) {
        tags.push(where);
      }
      if (when) tags.push(when);
      if (groupType) tags.push(groupType.charAt(0).toUpperCase() + groupType.slice(1));
      if (adults && Number(adults) > 1) tags.push(adults + ' guests');
      if (sort && sort !== 'popular') {
        if (sort === 'rating_desc') tags.push('Highest rated');
        else if (sort === 'price_asc') tags.push('Price: low to high');
        else if (sort === 'price_desc') tags.push('Price: high to low');
        else if (sort === 'newest') tags.push('Newest');
        else tags.push(sort);
      }
      if (priceMax) tags.push('Under £' + priceMax);
      if (rating) {
        if (rating === 'reviewed') tags.push('Reviewed only');
        else tags.push(rating + '+ stars');
      }
      if (type) {
        tags.push(type);
      }
      if (anytime) {
        tags.push('Anytime');
      }

      tags = tags.filter(function (tag) {
        var key = normalizeText(tag);
        if (!key || seen.has(key)) return false;
        seen.add(key);
        return true;
      });

      tagsEl.innerHTML = tags.map(function (tag) {
        return '<span class="badge badge--cool">' + escapeHtml(tag) + '</span>';
      }).join('');
    }

    function setResultsShellView(view) {
      if (!resultsShell) return;
      var isList = view === 'list';
      resultsShell.classList.toggle('container', isList);
      resultsShell.classList.toggle('container-fluid', !isList);
    }

    function setColsForView(view) {
      if (!layout) return;
      var resultsCol = layout.querySelector('.col-results');
      var mapCol = layout.querySelector('.col-map');

      if (!resultsCol || !mapCol) return;

      if (view === 'list') {
        resultsCol.classList.remove('col-lg-6');
        resultsCol.classList.add('col-lg-12');
        mapCol.classList.remove('col-lg-6');
        mapCol.classList.add('col-lg-12');
      } else {
        resultsCol.classList.remove('col-lg-12');
        resultsCol.classList.add('col-lg-6');
        mapCol.classList.remove('col-lg-12');
        mapCol.classList.add('col-lg-6');
      }
    }

    function setItemCols(view) {
      if (!grid) return;
      var items = Array.from(grid.children || []).filter(function (child) {
        if (!child || !child.tagName) return false;
        if (child.hasAttribute('data-pid')) return true;
        return !!child.querySelector('.wow-card-sm-wrap');
      });

      items.forEach(function (item) {
        if (view === 'list') {
          item.classList.remove('col-12', 'col-md-6', 'col-lg-4', 'col-xl-4', 'col-xxl-4');
          item.classList.add('col-md-6', 'col-sm-6', 'col-lg-3');
        } else {
          item.classList.remove('col-sm-6', 'col-md-6', 'col-lg-3', 'col-lg-4', 'col-xl-4', 'col-xxl-4');
          item.classList.add('col-12', 'col-md-6');
        }
      });
    }

    function refreshMapLayout() {
      if (!mapState.map || !mapState.ready || currentView !== 'map') return;

      var map = mapState.map;

      window.requestAnimationFrame(function () {
        window.requestAnimationFrame(function () {
          if (!mapState.map || map !== mapState.map || currentView !== 'map') return;

          try {
            map.resize();
          } catch (_err) {}

          renderMapData(mapState.pendingData);

          try {
            map.triggerRepaint();
          } catch (_err) {}
        });
      });
    }

    function scheduleMapResize() {
      if (!mapState.map) return;

      window.requestAnimationFrame(function () {
        try { mapState.map.resize(); } catch (_err) {}
      });

      window.setTimeout(function () {
        try { mapState.map.resize(); } catch (_err) {}
      }, 280);
    }

    function closeAllMarkerPopups() {
      Object.keys(mapState.markersByPid || {}).forEach(function (key) {
        (mapState.markersByPid[key] || []).forEach(function (markerEntry) {
          try {
            var popup = markerEntry && markerEntry.popup ? markerEntry.popup : null;
            if (!popup && markerEntry && markerEntry.marker && markerEntry.marker.getPopup) {
              popup = markerEntry.marker.getPopup();
            }
            if (popup) popup.remove();
          } catch (_err) {}
        });
      });
      try {
        document.querySelectorAll('.mapboxgl-popup').forEach(function (popupEl) {
          try {
            popupEl.remove();
          } catch (_err) {}
        });
      } catch (_err) {}
      mapState.activePopupPid = '';
    }

    function setMapPanelExpanded() {
      scheduleMapResize();
    }

    function syncPaginationViewState() {
      if (!paginationEl) return;

      var links = paginationEl.querySelectorAll('a[href]');
      if (!links || !links.length) return;

      links.forEach(function (link) {
        try {
          var url = new URL(link.getAttribute('href'), window.location.origin);
          url.searchParams.set('view', currentView);
          url.searchParams.set('map_mode', currentMapMode);
          link.setAttribute('href', url.toString());
        } catch (_err) {}
      });
    }

    function setViewMode(view, options) {
      options = options || {};
      currentView = view === 'list' ? 'list' : 'map';
      if (layout) {
        layout.classList.toggle('sr-list-only', currentView === 'list');
      }
      setResultsShellView(currentView);
      setColsForView(currentView);
      setItemCols(currentView);
      syncPaginationViewState();

      if (currentView === 'map') {
        refreshMapLayout();
      }
    }

    function setMapMode(mode) {
      currentMapMode = mode === '3d' ? '3d' : '2d';
      if (!mapState.map) return;

      try {
        mapState.map.easeTo({
          pitch: currentMapMode === '3d' ? 60 : 0,
          bearing: currentMapMode === '3d' ? -17 : 0,
          duration: 600,
        });
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

      lastCountText = countEl ? countEl.textContent : '';

      try {
        window.dispatchEvent(new CustomEvent('wow:searchbar-v4:results-updated', {
          detail: {
            count: typeof count === 'number' ? count : null,
            countText: typeof countText === 'string' ? countText : lastCountText,
          },
        }));
      } catch (_err) {}
    }

    function buildGhostHtml() {
      if (!template || !template.innerHTML) return '';
      var ghostCount = currentView === 'list' ? 8 : 6;
      return Array.from({ length: ghostCount }, function () {
        return template.innerHTML.trim();
      }).join('');
    }

    function setLoadingState(isLoading) {
      if (!grid) return;

      if (isLoading) {
        if (!initialGhostHtml) {
          initialGhostHtml = buildGhostHtml();
        }
        if (initialGhostHtml) {
          grid.innerHTML = initialGhostHtml;
          setItemCols(currentView);
        }
        grid.setAttribute('aria-busy', 'true');
        if (paginationEl) paginationEl.setAttribute('aria-busy', 'true');
        return;
      }

      grid.removeAttribute('aria-busy');
      if (paginationEl) paginationEl.removeAttribute('aria-busy');
    }

    function renderGridHtml(html) {
      if (!grid || typeof html !== 'string') return;
      grid.innerHTML = html;
      setItemCols(currentView);
    }

    function renderPaginationHtml(html) {
      if (!paginationEl || typeof html !== 'string') return;
      paginationEl.innerHTML = html;
      syncPaginationViewState();
    }

    function clearMarkers() {
      Object.keys(mapState.markersByPid || {}).forEach(function (pid) {
        var group = mapState.markersByPid[pid] || [];
        group.forEach(function (markerEntry) {
          try {
            markerEntry.marker.remove();
          } catch (_err) {}
        });
      });
      mapState.markersByPid = {};
      window.__wowMarkersByPid = mapState.markersByPid;
    }

    function setActiveMarker(pid) {
      Object.keys(mapState.markersByPid || {}).forEach(function (key) {
        (mapState.markersByPid[key] || []).forEach(function (markerEntry) {
          markerEntry.el.classList.toggle('is-active', String(key) === String(pid));
        });
      });
      mapState.activePid = String(pid || '');
    }

    function centerOnPid(pid, options) {
      options = options || {};
      if (!mapState.map) return;

      var group = (mapState.markersByPid || {})[String(pid)] || [];
      if (!group.length) return;

      try {
        var ll = group[0].marker.getLngLat();
        mapState.map.easeTo({
          center: [ll.lng, ll.lat],
          zoom: Math.max(mapState.map.getZoom(), options.zoom || 14),
          duration: options.duration || 450,
        });
      } catch (_err) {}
    }

    function buildPopupHtml(item, priceText) {
      var popupUrl = item && item.url ? escapeHtml(item.url) : '#';
      var popupTitle = escapeHtml((item && item.title) || '');
      var popupPrice = escapeHtml(priceText || (item && item.price_label) || 'View');
      var popupImage = item && item.image ? escapeHtml(item.image) : '';
      var rating = item && item.rating;
      var reviewCount = Number(item && item.review_count ? item.review_count : 0);
      var ratingMarkup = '';

      if (rating !== null && rating !== undefined && rating !== '') {
        ratingMarkup = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2.5 2.9 6 6.6.9-4.8 4.7 1.2 6.6L12 17.6l-5.9 3.1 1.2-6.6-4.8-4.7 6.6-.9L12 2.5Z"></path></svg><span>' + escapeHtml(Number(rating).toFixed(1)) + (reviewCount > 0 ? ' (' + reviewCount + ')' : '') + '</span>';
      } else {
        ratingMarkup = '<span>Be the first to review</span>';
      }

      return (
        '<div class="wow-map-popup">' +
          '<div class="wow-map-popup__image-wrap">' +
            (popupImage ? '<img class="wow-map-popup__image" src="' + popupImage + '" alt="' + popupTitle + '">' : '') +
          '</div>' +
          '<div class="wow-map-popup__body">' +
            '<div class="wow-map-popup__top">' +
              '<div>' +
                '<h3 class="wow-map-popup__title">' + popupTitle + '</h3>' +
              '</div>' +
              '<div class="wow-map-popup__rating">' + ratingMarkup + '</div>' +
            '</div>' +
            '<div class="wow-map-popup__footer">' +
              '<div class="wow-map-popup__rating">' + popupPrice + '</div>' +
              '<a href="' + popupUrl + '" class="wow-map-popup__link">View offering</a>' +
            '</div>' +
          '</div>' +
        '</div>'
      );
    }

    function openMarkerPopup(pid, options) {
      options = options || {};
      if (!mapState.map) return;

      var group = (mapState.markersByPid || {})[String(pid)] || [];
      if (!group.length) return;

      var entry = group[0];
      var item = entry.item || {};
      var el = entry.el;
      var popup = entry.popup || null;
      if (!popup) return;

      try {
        closeAllMarkerPopups();
        var priceText = '';
        if (el) {
          priceText = (el.querySelector('.wow-marker__price') || {}).textContent || '';
        }
        popup.setHTML(buildPopupHtml(item, priceText));
        popup.setLngLat([Number(item.lng), Number(item.lat)]).addTo(mapState.map);
        mapState.activePopupPid = String(pid || '');
      } catch (_err) {}

      setActiveMarker(pid);

      if (options.center) {
        centerOnPid(pid, { zoom: options.zoom || 14, duration: options.duration || 450 });
      }
    }

    function clearHoverCenterTimer() {
      if (hoverCenterTimer) {
        clearTimeout(hoverCenterTimer);
        hoverCenterTimer = null;
      }
      hoverCenterPid = '';
    }

    function bindHoverTracking() {
      if (!resultsScroll) return;

      resultsScroll.addEventListener('mouseover', function (event) {
        var item = event.target.closest('[data-pid]');
        if (!item) return;
        var pid = item.getAttribute('data-pid');
        if (!pid) return;
        if (hoverCenterPid === pid) return;
        clearHoverCenterTimer();
        hoverCenterPid = pid;
        hoverCenterTimer = setTimeout(function () {
          if (hoverCenterPid !== pid) return;
          centerOnPid(pid, { zoom: 13, duration: 300 });
        }, 2000);
      });

      resultsScroll.addEventListener('mouseout', function (event) {
        var item = event.target.closest('[data-pid]');
        if (!item) return;
        var related = event.relatedTarget;
        if (related && item.contains(related)) return;
        var pid = item.getAttribute('data-pid');
        if (pid && hoverCenterPid === pid) {
          clearHoverCenterTimer();
        }
        if (pid && String(mapState.activePopupPid || '') !== String(pid) && String(mapState.activePid || '') === String(pid)) {
          setActiveMarker('');
        }
      });

      resultsScroll.addEventListener('focusin', function (event) {
        var item = event.target.closest('[data-pid]');
        if (!item) return;
        var pid = item.getAttribute('data-pid');
        if (!pid) return;
        clearHoverCenterTimer();
        centerOnPid(pid, { zoom: 13, duration: 300 });
      });

      resultsScroll.addEventListener('scroll', function () {
        if (currentView !== 'map') return;
        if (!mapState.map) return;

        var currentScrollTop = resultsScroll.scrollTop;
        var scrollingDown = currentScrollTop > lastResultsScrollTop;
        lastResultsScrollTop = currentScrollTop;

        var containerRect = resultsScroll.getBoundingClientRect();
        var items = Array.from(resultsScroll.querySelectorAll('[data-pid]'));
        if (!items.length) return;

        var closest = null;
        var bestDelta = Infinity;
        items.forEach(function (item) {
          var rect = item.getBoundingClientRect();
          var delta = Math.abs(rect.top - containerRect.top);
          if (delta < bestDelta) {
            bestDelta = delta;
            closest = item;
          }
        });

        if (!closest) return;
        var pid = String(closest.getAttribute('data-pid') || '');
        if (!pid) return;
        centerOnPid(pid, { zoom: 13, duration: 250 });
      }, { passive: true });
    }

    function buildMarkerEntry(item, map) {
      var el = document.createElement('div');
      el.className = 'wow-marker';
      el.title = item.title || '';
      el.style.zIndex = '5';
      el.style.cursor = 'pointer';
      el.setAttribute('role', 'button');
      el.setAttribute('tabindex', '0');
      el.setAttribute('aria-label', 'Map marker');
      el.innerHTML = '<span class="wow-marker__price">' + escapeHtml(item.price_label || 'View') + '</span>';
      var popup = new mapboxgl.Popup({ offset: 8, maxWidth: 'none' });
      var popupId = String(item.pid || item.id || '');

      var marker = new mapboxgl.Marker({ element: el, anchor: 'bottom' })
        .setLngLat([Number(item.lng), Number(item.lat)])
        .addTo(map);

      var pid = String(item.pid || item.id || '');
      if (!mapState.markersByPid[pid]) {
        mapState.markersByPid[pid] = [];
      }
      mapState.markersByPid[pid].push({ marker: marker, el: el, popup: popup, item: item });

      el.addEventListener('click', function (event) {
        try { event.stopPropagation(); } catch (_err) {}
        setMapPanelExpanded();
        closeAllMarkerPopups();
        openMarkerPopup(pid);
        var itemEl = document.querySelector('[data-pid="' + pid + '"]');
        if (itemEl) {
          itemEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
          itemEl.classList.add('is-active');
          setTimeout(function () {
            try { itemEl.classList.remove('is-active'); } catch (_err) {}
          }, 1200);
        }
      });

      el.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
          try { event.preventDefault(); } catch (_err) {}
          el.click();
        }
      });

      popup.on('close', function () {
        if (String(mapState.activePid || '') === popupId) {
          mapState.activePid = '';
        }
        if (String(mapState.activePopupPid || '') === popupId) {
          mapState.activePopupPid = '';
        }
        setActiveMarker('');
      });

      return { marker: marker, el: el };
    }

    function renderMapData(data) {
      mapState.pendingData = Array.isArray(data) ? data.slice() : [];
      if (!mapState.map || !mapState.ready) return;

      clearMarkers();

      if (!Array.isArray(mapState.pendingData) || mapState.pendingData.length === 0) {
        return;
      }

      var bounds = new mapboxgl.LngLatBounds();
      var added = 0;

      mapState.pendingData.forEach(function (item) {
        if (!isFinite(Number(item.lat)) || !isFinite(Number(item.lng))) return;
        buildMarkerEntry(item, mapState.map);
        bounds.extend([Number(item.lng), Number(item.lat)]);
        added += 1;
      });

      window.__wowMarkersByPid = mapState.markersByPid;

      if (added > 1) {
        try {
          mapState.map.fitBounds(bounds, { padding: 100, maxZoom: 13, duration: 500 });
        } catch (_err) {}
      } else if (added === 1) {
        var only = mapState.pendingData[0];
        try {
          mapState.map.setCenter([Number(only.lng), Number(only.lat)]);
          mapState.map.setZoom(14);
        } catch (_err) {}
      }

      try {
        var firstItem = document.querySelector('.results-scroll [data-pid]');
        if (firstItem) {
          centerOnPid(firstItem.getAttribute('data-pid'), { zoom: 14, duration: 300 });
        }
      } catch (_err) {}
    }

    function initMapbox(token) {
      if (!mapEl) return;
      if (!token) {
        try {
          mapEl.innerHTML = '<div style="padding:12px;color:#334155;font-size:14px;">Map unavailable: missing MAPBOX_API_KEY. Set it in .env.</div>';
        } catch (_err) {}
        return;
      }

      mapboxgl.accessToken = token;

      var center = mapState.pendingData.length
        ? [Number(mapState.pendingData[0].lng), Number(mapState.pendingData[0].lat)]
        : [-0.1276, 51.5072];

      var map = new mapboxgl.Map({
        container: mapEl,
        style: 'mapbox://styles/mapbox/streets-v12',
        center: center,
        zoom: 13,
        pitch: 0,
        bearing: 0,
        antialias: true,
        fadeDuration: 0,
        attributionControl: false,
      });
      map.scrollZoom.disable();

      map.on('load', function () {
        var layers = map.getStyle().layers || [];
        var labelLayerId = null;

        for (var i = 0; i < layers.length; i += 1) {
          if (layers[i].type === 'symbol' && layers[i].layout && layers[i].layout['text-field']) {
            labelLayerId = layers[i].id;
            break;
          }
        }

        try {
          map.addLayer({
            id: '3d-buildings',
            source: 'composite',
            'source-layer': 'building',
            filter: ['==', 'extrude', 'true'],
            type: 'fill-extrusion',
            minzoom: 15,
            paint: {
              'fill-extrusion-color': '#aaa',
              'fill-extrusion-height': ['get', 'height'],
              'fill-extrusion-base': ['get', 'min_height'],
              'fill-extrusion-opacity': 0.6,
            },
          }, labelLayerId || undefined);
        } catch (_err) {}

        mapState.map = map;
        mapState.ready = true;
        window.__wowMap = map;
        window.__centerOnPid = centerOnPid;
        window.__wowSetMapData = renderMapData;
        window.__wowSetMapMode = setMapMode;

        renderMapData(mapState.pendingData);
        setMapMode(currentMapMode);
        refreshMapLayout();
      });
    }

    function ensureMapboxAssets(token) {
      if (!mapEl) return;

      var hasCss = !!document.querySelector('link[href*="mapbox-gl.css"]');
      if (!hasCss) {
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://api.mapbox.com/mapbox-gl-js/v3.6.0/mapbox-gl.css';
        document.head.appendChild(link);
      }

      if (!window.mapboxgl) {
        var script = document.createElement('script');
        script.src = 'https://api.mapbox.com/mapbox-gl-js/v3.6.0/mapbox-gl.js';
        script.async = true;
        script.defer = true;
        script.onload = function () {
          initMapbox(token);
        };
        document.head.appendChild(script);
      } else {
        initMapbox(token);
      }
    }

    function applyUrlState(url) {
      var params = currentUrlParams(url);
      setViewMode(params.get('view') === 'list' ? 'list' : 'map');
      setMapMode(params.get('map_mode') === '3d' ? '3d' : '2d');
      renderTagsFromUrl(url);
    }

    function fetchSearchResults(url, options) {
      options = options || {};
      if (!grid) return;

      var nextUrl = String(url || window.location.href);
      var requestId = ++currentRequestId;

      if (activeAbortController) {
        try { activeAbortController.abort(); } catch (_err) {}
      }
      activeAbortController = new AbortController();

      lastGridHtml = grid.innerHTML;
      lastPaginationHtml = paginationEl ? paginationEl.innerHTML : lastPaginationHtml;
      lastCountText = countEl ? countEl.textContent : lastCountText;

      setViewMode(options.viewMode || currentView);
      setMapMode(options.mapMode || currentMapMode);
      syncHistory(nextUrl);
      renderTagsFromUrl(nextUrl);
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
        .then(function (response) {
          if (!response.ok) {
            throw new Error('Search API request failed: ' + response.status);
          }
          return response.json();
        })
        .then(function (payload) {
          if (requestId !== currentRequestId) return;

          if (payload && typeof payload.grid_html === 'string') {
            renderGridHtml(payload.grid_html);
            lastGridHtml = grid.innerHTML;
          }

          if (payload && typeof payload.pagination_html === 'string') {
            renderPaginationHtml(payload.pagination_html);
            lastPaginationHtml = paginationEl ? paginationEl.innerHTML : lastPaginationHtml;
          }

          if (payload) {
            updateCount(
              typeof payload.count === 'number' ? payload.count : null,
              typeof payload.count_text === 'string' ? payload.count_text : null
            );
          }

          if (payload && Array.isArray(payload.map_data)) {
            renderMapData(payload.map_data);
          } else {
            renderMapData([]);
          }

          if (payload && typeof payload.url === 'string') {
            renderTagsFromUrl(payload.url);
          }
        })
        .catch(function (error) {
          if (error && error.name === 'AbortError') return;
          console.warn('[search] api refresh failed', error);
          if (grid) grid.innerHTML = lastGridHtml;
          if (paginationEl) paginationEl.innerHTML = lastPaginationHtml;
          if (countEl) countEl.textContent = lastCountText;
          setItemCols(currentView);
        })
        .finally(function () {
          if (requestId !== currentRequestId) return;
          setLoadingState(false);
        });
    }

    function handleQueryChange(event) {
      var detail = event && event.detail ? event.detail : {};
      if (!detail.url) return;
      var params = currentUrlParams(detail.url);
      applyUrlState(detail.url);
      fetchSearchResults(detail.url, {
        viewMode: params.get('view') === 'list' ? 'list' : 'map',
        mapMode: params.get('map_mode') === '3d' ? '3d' : '2d',
      });
    }

    function handleLayoutChange(event) {
      var detail = event && event.detail ? event.detail : {};
      if (!detail.url) return;
      applyUrlState(detail.url);
      syncHistory(detail.url);
      if (detail.state && detail.state.mapMode) {
        setMapMode(detail.state.mapMode);
      }
      if (detail.state && detail.state.viewMode) {
        setViewMode(detail.state.viewMode);
      }
      renderTagsFromUrl(detail.url);
    }

    function handlePopState() {
      var url = window.location.href;
      applyUrlState(url);
      fetchSearchResults(url, {
        viewMode: currentView,
        mapMode: currentMapMode,
      });
    }

    function bindUtilityClicks() {
      document.addEventListener('click', function (event) {
        var add = event.target.closest('.result-view-map .js-add-to-cart');
        if (add) {
          try { event.stopPropagation(); } catch (_err) {}
        }

        var buy = event.target.closest('.result-view-map .js-buy-now');
        if (buy) {
          try { event.stopPropagation(); } catch (_err) {}
          var url = buy.getAttribute('data-url');
          if (url) {
            window.location.href = url;
          }
        }

        var marker = event.target.closest('.wow-marker');
        var popup = event.target.closest('.mapboxgl-popup');
        if (!marker && !popup) {
          closeAllMarkerPopups();
          setActiveMarker('');
        }
      }, true);
    }

    function ensureSearchBarStateFromUrl() {
      applyUrlState(window.location.href);
      renderTagsFromUrl(window.location.href);
      setItemCols(currentView);
    }

    function boot() {
      ensureSearchBarStateFromUrl();
      bindHoverTracking();
      bindUtilityClicks();

      var token = mapboxToken || window.WOW_MAPS_KEY || fallbackMapToken || '';
      ensureMapboxAssets(token);

      window.addEventListener('wow:searchbar-v4:query-change', handleQueryChange);
      window.addEventListener('wow:searchbar-v4:layout-change', handleLayoutChange);
      window.addEventListener('popstate', handlePopState);

      window.requestAnimationFrame(function () {
        fetchSearchResults(window.location.href, {
          viewMode: currentView,
          mapMode: currentMapMode,
        });
      });
    }

    boot();
  } catch (error) {
    console.warn('[search] bootstrap failed', error);
  }
})();
</script>
