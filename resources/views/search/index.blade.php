@extends('layouts.app')

<style>
/* Desktop split: page scrolls the list; map stays sticky */
@media (min-width: 992px){
  .results-scroll{ padding-right: 6px; }
  .map-wrap{ position: sticky; top: 127px; }
  /* Adjust height to account for header + search bar */
  .map{ width: 100%; height: calc(100vh - 80px - 67px); border: 1px solid var(--ink-200); border-radius: 3px; overflow: hidden; }
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
    position: fixed; top: 115px; z-index: 30;
    border-radius: 18px;
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
  /* When page is scrolled, compact the search bar upward to 80px */
  .search-compact .wow-ultra .bar{ top: 80px; }
  /* Compact state: shrink width and softly fade Where/When/Who */
  .search-compact .wow-ultra .bar{ width: 400px; height: 70px; overflow: hidden; }
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
.wow-ultra .pane.narrow{ width: min(560px, 96vw); right:auto; height:auto; max-height:304px; }
/* Constrain scrollable list */
/* Avoid inner list scrollbars in Who pane */
.wow-ultra #search-top-who-pane .listy{ max-height: none; overflow: visible; }
</style>

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
              <div class="section-title">Therapies</div>
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
  </div>
</section>


<div class="search-content-wrapper">
    @include('search.partials.desktop')
</div>

@endsection
