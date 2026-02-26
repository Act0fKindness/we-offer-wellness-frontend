<section data-v-f43bb09d="" class="py-4">
    <div data-v-f43bb09d="" class="container">
        <div data-v-f43bb09d="" class="wow-ultra">
            <form class="bar" role="search">
                <div class="seg" id="home-template-seg-what"><i class="bi bi-stars fs-5 text-muted"></i>
                    <div class="flex-grow-1">
                        <div class="seg-label">What</div>
                        <input id="home-template-what" type="text" autocomplete="off"
                               placeholder="Massage, yoga, breathwork…" aria-expanded="false"
                               aria-controls="home-template-what-pane"></div><!---->
                    <div id="home-template-what-pane" class="pane narrow d-none" role="listbox"
                         aria-label="What suggestions">
                        <div id="home-template-what-list" class="listy">
                            <div class="section-title">Therapies</div>
                            <div>
                                <button type="button" class="item" role="option" data-value="Sound Bath">
                                    <i class="bi bi-dot"></i>
                                    <span class="title">Sound Bath</span>
                                    <span class="type">Group</span>
                                </button>
                                <button type="button" class="item" role="option" data-value="Ice Bath">
                                    <i class="bi bi-dot"></i>
                                    <span class="title">Ice Bath</span>
                                    <span class="type">Workshop</span>
                                </button>
                                <button type="button" class="item" role="option" data-value="Forest Walk">
                                    <i class="bi bi-dot"></i>
                                    <span class="title">Forest Walk</span>
                                    <span class="type">Nature</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="seg" id="home-template-seg-where"><i class="bi bi-geo-alt fs-5 text-muted"></i>
                    <div class="flex-grow-1">
                        <div class="seg-label">Where</div>
                        <div id="home-template-where-editor" class="where-editor" contenteditable="true"
                             data-placeholder="City, region, or 'Online'"></div>
                        <input id="home-template-where" type="hidden"></div>
                    <div id="home-template-where-pane" class="pane narrow d-none" role="listbox"
                         aria-label="Trending places">
                        <div class="section-title">Trending destinations</div>
                        <div class="listy" id="home-template-where-list">
                            <button type="button" class="item" data-value="Online"><i
                                class="bi bi-wifi"></i><span class="title">Online</span><span
                                class="text-muted ms-2">Virtual</span></button>
                            <button type="button" class="item" data-value="London"><i
                                class="bi bi-geo-alt"></i><span class="title">London</span><span
                                class="text-muted ms-2">United Kingdom</span></button>
                            <button type="button" class="item" data-value="Manchester"><i
                                class="bi bi-geo-alt"></i><span class="title">Manchester</span><span
                                class="text-muted ms-2">United Kingdom</span></button>
                            <button type="button" class="item" data-value="Brighton &amp; Hove"><i
                                class="bi bi-geo-alt"></i><span
                                class="title">Brighton &amp; Hove</span><span class="text-muted ms-2">United Kingdom</span>
                            </button>
                            <button type="button" class="item" data-value="Kent"><i
                                class="bi bi-geo-alt"></i><span class="title">Kent</span><span
                                class="text-muted ms-2">United Kingdom</span></button>
                        </div>
                    </div>
                </div>
                <div class="seg" id="home-template-seg-when"><i class="bi bi-calendar3 fs-5 text-muted"></i>
                    <div class="flex-grow-1">
                        <div class="seg-label">When</div>
                        <input id="home-template-when" type="text" placeholder="Select dates" readonly=""
                               aria-haspopup="dialog"></div>
                    <div id="home-template-when-pane" class="pane d-none" aria-label="Calendar">
                        <div class="cal-head">
                            <button type="button" class="cal-col active" id="home-template-tab-calendar"
                                    aria-pressed="true"> Calendar
                            </button>
                            <button type="button" class="cal-col" id="home-template-tab-flex"
                                    aria-pressed="false"> I'm flexible
                            </button>
                        </div>
                        <div class="cal-body">
                            <div id="home-template-calendarMount"></div>
                            <div class="flexible-pane" style="display: none;"><p class="mb-2">We’ll look
                                across the next few weeks so you see more options.</p>
                                <p class="text-muted m-0">Switch back to Calendar for exact dates.</p></div>
                        </div>
                        <div class="cal-foot">
                            <button type="button" class="chip chip-sm primary"
                                    id="home-template-chip-exact">Exact dates
                            </button>
                            <button type="button" class="chip chip-sm dur" data-days="1"><i
                                class="bi bi-plus-lg"></i>1 day
                            </button>
                            <button type="button" class="chip chip-sm dur" data-days="2"><i
                                class="bi bi-plus-lg"></i>2 days
                            </button>
                            <button type="button" class="chip chip-sm dur" data-days="3"><i
                                class="bi bi-plus-lg"></i>3 days
                            </button>
                            <button type="button" class="chip chip-sm dur" data-days="7"><i
                                class="bi bi-plus-lg"></i>7 days
                            </button>
                        </div>
                    </div>
                </div>
                <div class="seg" id="home-template-seg-who"><i class="bi bi-person fs-5 text-muted"></i>
                    <div class="flex-grow-1">
                        <div class="seg-label">Who</div>
                        <div id="home-template-who-summary" class="summary">2 adults · Couple</div>
                    </div>
                    <div id="home-template-who-pane" class="pane narrow d-none" aria-label="Guests">
                        <div class="section-title">Guests</div>
                        <div class="listy">
                            <div class="item" style="justify-content: space-between;">
                                <div>
                                    <div class="fw-semibold">Adults</div>
                                    <small class="text-muted">18+</small></div>
                                <div class="counter">
                                    <button type="button" class="btn btn-counter" data-dec="adults"
                                            aria-label="Decrease adults"><i class="bi bi-dash"></i></button>
                                    <span id="home-template-adults-val" class="fw-semibold">2</span>
                                    <button type="button" class="btn btn-counter" data-inc="adults"
                                            aria-label="Increase adults"><i class="bi bi-plus"></i></button>
                                </div>
                            </div>
                            <div class="section-title">Group type</div>
                            <div id="home-template-group-type-list">
                                <button type="button" class="item" data-group="Solo" aria-selected="false">
                                    <i class="bi bi-person"></i><span class="title">Solo</span></button>
                                <button type="button" class="item" data-group="Couple" aria-selected="true">
                                    <i class="bi bi-heart"></i><span class="title">Couple</span></button>
                                <button type="button" class="item" data-group="Group" aria-selected="false">
                                    <i class="bi bi-people"></i><span class="title">Group</span></button>
                            </div>
                        </div>
                        <div class="text-end p-3">
                            <button type="button" class="btn btn-primary btn-sm"
                                    id="home-template-who-done">Done
                            </button>
                        </div>
                    </div>
                </div><!---->
                <button class="btn-wow is-squarish btn-xl" data-loader-init="1">
                    <span class="btn-label">Search</span>
                    <span class="btn-icon" aria-hidden="true">
                        <svg class="icon-search" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                          <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/>
                        </svg>
                    </span>
                    <span class="btn-spinner" aria-hidden="true"><span class="spin"></span></span>
                </button>
            </form>
        </div>
    </div>
</section><!---->

<style>
/* Ensure search suggestion panes overlay content but stay under the header */
.wow-ultra{ position: relative; z-index: 30; }
.wow-ultra .pane{ z-index: 39; }
.wow-ultra .pane.narrow{ z-index: 39; }
/* Desktop default: show text label, hide icon on Search button */
.btn-wow.is-squarish.btn-xl .btn-label{ display:inline; }
.btn-wow.is-squarish.btn-xl .btn-icon{ display:none; }
@media (max-width: 991.98px){
  /* Clamp What suggestions pane within viewport on mobile */
  #home-template-what-pane{
    left: auto !important;
    right: 0 !important;
    width: min(560px, calc(100vw - 24px));
    max-width: calc(100vw - 24px);
  }
  .wow-ultra .bar{
    background: rgba(255,255,255,.14);
    border-radius:40px;
    border:none;
    border-top: 1px solid rgba(255,255,255,0.5);
    border-bottom: 1px solid rgba(0,0,0,0.08);
    position: sticky; top: 84px; z-index: 30;
    -webkit-backdrop-filter: blur(14px);
    backdrop-filter: blur(14px);
    box-shadow: 0 14px 40px rgba(16,24,40,.14);
  }
  .wow-ultra .bar::before{ content:""; position:absolute; inset:0; border-radius: inherit; pointer-events:none; background: linear-gradient(180deg, rgba(255,255,255,.28), rgba(255,255,255,.08)); opacity:.55; }
  .wow-ultra .bar > *{ position: relative; z-index: 1; }
  .wow-ultra .seg{ border-radius:40px; }
  /* Hide Where/When/Who on mobile */
  #home-template-seg-where,
  #home-template-seg-when,
  #home-template-seg-who{ display:none !important; }
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
}
</style>
