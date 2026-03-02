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
