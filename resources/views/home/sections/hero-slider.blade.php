{{-- resources/views/home/sections/hero-slider.blade.php --}}

  {{-- Swiper CSS --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

  <style>
    /* Keep your existing whero styling intact. This only styles the slider wrapper + controls. */
    .wow-hero-swiper { position: relative; }
    .wow-hero-swiper .swiper-slide { height: auto; } /* important so content defines height */

    .wow-hero-nav{
      position:absolute;
      left:0; right:0;
      bottom: 18px;
      z-index: 10;
      pointer-events:none;
    }
    .wow-hero-nav .container-page{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:14px;
    }

    .wow-hero-btn{
      pointer-events:auto;
      width: 44px;
      height: 44px;
      border-radius: 999px;
      border: 1px solid rgba(255,255,255,.30);
      background: rgba(255,255,255,.16);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      box-shadow: 0 12px 30px rgba(16,24,40,.18);
      display:inline-flex;
      align-items:center;
      justify-content:center;
      cursor:pointer;
      color: rgba(16,24,40,.85);
    }
    .wow-hero-btn:focus{ outline:none; box-shadow: 0 0 0 4px rgba(68,76,231,.20), 0 12px 30px rgba(16,24,40,.18); }

    .wow-hero-pagination{
      pointer-events:auto;
      display:flex;
      align-items:center;
      justify-content:center;
      gap:8px;
    }

    /* Swiper pagination bullets -> slick pill dots */
    .wow-hero-pagination .swiper-pagination-bullet{
      width: 10px; height:10px;
      border-radius: 999px;
      opacity: 1;
      background: rgba(255,255,255,.22);
      border: 1px solid rgba(255,255,255,.45);
      transition: width .18s ease, background .18s ease, border-color .18s ease;
      margin: 0 !important;
    }
    .wow-hero-pagination .swiper-pagination-bullet-active{
      width: 26px;
      background: rgba(255,255,255,.65);
      border-color: rgba(255,255,255,.65);
    }

    /* =========================
       Per-slide BACKGROUNDS
       =========================
       We keep .whero-radial but override its background per slide.
       (No class renames, only add modifiers)
    */

    /* Slide 1: keep your current rainbow rays vibe */
    .whero.whero--s1 .whero-radial{
      background:
        conic-gradient(from 120deg at 58% 52%,
          rgba(255,164,198,.85),
          rgba(255,214,165,.85),
          rgba(255,248,184,.85),
          rgba(180,255,196,.85),
          rgba(146,232,255,.85),
          rgba(174,174,255,.85),
          rgba(214,170,255,.85),
          rgba(255,164,198,.85)
        );
      opacity: .95;
    }

    /* Slide 2: cooler mint/sky burst */
    .whero.whero--s2 .whero-radial{
      background:
        conic-gradient(from 135deg at 60% 50%,
          rgba(120,255,230,.85),
          rgba(135,220,255,.85),
          rgba(164,180,255,.85),
          rgba(200,255,220,.85),
          rgba(120,255,230,.85)
        );
      opacity: .92;
    }

    /* Slide 3: warm sunset / festival energy */
    .whero.whero--s3 .whero-radial{
      background:
        conic-gradient(from 110deg at 58% 52%,
          rgba(255,168,124,.88),
          rgba(255,216,142,.88),
          rgba(255,176,220,.88),
          rgba(196,170,255,.88),
          rgba(150,220,255,.88),
          rgba(255,168,124,.88)
        );
      opacity: .93;
    }

    /* Optional: slide-specific text tweaks hooks (if you want them later) */
    .whero--s2 .whero-title { /* e.g. */ }
    .whero--s3 .whero-title { /* e.g. */ }

    @media (max-width: 575px){
      .wow-hero-nav{ bottom: 12px; }
      .wow-hero-btn{ width:40px; height:40px; }
    }
  </style>

{{-- Keep your sticky bar OUTSIDE the slider (so IDs stay unique and not duplicated) --}}
<div data-v-f43bb09d="" class="hidden lg:block fixed left-0 right-0 z-30 transition-all"
     style="top: 65px; display: none;">
    <div data-v-f43bb09d="" class="container-page py-2">
        <div data-v-f43bb09d="" class="wow-ultra">
            <form class="bar bar-compact" role="search">
                <div class="seg" id="home-sticky-seg-what"><i class="bi bi-stars fs-5 text-muted"></i>
                    <div class="flex-grow-1">
                        <div class="seg-label">What</div>
                        <input id="home-sticky-what" type="text" autocomplete="off"
                               placeholder="Massage, yoga, breathwork…" aria-expanded="false"
                               aria-controls="home-sticky-what-pane"></div><!---->
                    <div id="home-sticky-what-pane" class="pane narrow d-none" role="listbox"
                         aria-label="What suggestions">
                        <div id="home-sticky-what-list" class="listy">
                            <div class="section-title">Experiences</div>
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
                <div class="seg" id="home-sticky-seg-where"><i class="bi bi-geo-alt fs-5 text-muted"></i>
                    <div class="flex-grow-1">
                        <div class="seg-label">Where</div>
                        <div id="home-sticky-where-editor" class="where-editor" contenteditable="true"
                             data-placeholder="City, region, or 'Online'"></div>
                        <input id="home-sticky-where" type="hidden"></div>
                    <div id="home-sticky-where-pane" class="pane narrow d-none" role="listbox"
                         aria-label="Trending places">
                        <div class="section-title">Trending destinations</div>
                        <div class="listy" id="home-sticky-where-list">
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
                <div class="seg" id="home-sticky-seg-when"><i class="bi bi-calendar3 fs-5 text-muted"></i>
                    <div class="flex-grow-1">
                        <div class="seg-label">When</div>
                        <input id="home-sticky-when" type="text" placeholder="Select dates" readonly=""
                               aria-haspopup="dialog"></div>
                    <div id="home-sticky-when-pane" class="pane d-none" aria-label="Calendar">
                        <div class="cal-head">
                            <button type="button" class="cal-col active" id="home-sticky-tab-calendar"
                                    aria-pressed="true"> Calendar
                            </button>
                            <button type="button" class="cal-col" id="home-sticky-tab-flex"
                                    aria-pressed="false"> I'm flexible
                            </button>
                        </div>
                        <div class="cal-body">
                            <div id="home-sticky-calendarMount"></div>
                            <div class="flexible-pane" style="display: none;"><p class="mb-2">We’ll look
                                across the next few weeks so you see more options.</p>
                                <p class="text-muted m-0">Switch back to Calendar for exact dates.</p></div>
                        </div>
                        <div class="cal-foot">
                            <button type="button" class="chip chip-sm primary" id="home-sticky-chip-exact">
                                Exact dates
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
                <div class="seg" id="home-sticky-seg-who"><i class="bi bi-person fs-5 text-muted"></i>
                    <div class="flex-grow-1">
                        <div class="seg-label">Who</div>
                        <div id="home-sticky-who-summary" class="summary">2 adults · Couple</div>
                    </div>
                    <div id="home-sticky-who-pane" class="pane narrow d-none" aria-label="Guests">
                        <div class="section-title">Guests</div>
                        <div class="listy">
                            <div class="item" style="justify-content: space-between;">
                                <div>
                                    <div class="fw-semibold">Adults</div>
                                    <small class="text-muted">18+</small></div>
                                <div class="counter">
                                    <button type="button" class="btn btn-counter" data-dec="adults"
                                            aria-label="Decrease adults"><i class="bi bi-dash"></i></button>
                                    <span id="home-sticky-adults-val" class="fw-semibold">2</span>
                                    <button type="button" class="btn btn-counter" data-inc="adults"
                                            aria-label="Increase adults"><i class="bi bi-plus"></i></button>
                                </div>
                            </div>
                            <div class="section-title">Group type</div>
                            <div id="home-sticky-group-type-list">
                                <button type="button" class="item" data-group="Solo" aria-selected="false">
                                    <i class="bi bi-person"></i><span class="title">Solo</span></button>
                                <button type="button" class="item" data-group="Couple" aria-selected="true">
                                    <i class="bi bi-heart"></i><span class="title">Couple</span></button>
                                <button type="button" class="item" data-group="Group" aria-selected="false">
                                    <i class="bi bi-people"></i><span class="title">Group</span></button>
                            </div>
                        </div>
                        <div class="text-end p-3">
                            <button type="button" class="btn btn-primary btn-sm" id="home-sticky-who-done">
                                Done
                            </button>
                        </div>
                    </div>
                </div><!---->
                <button class="btn-wow is-squarish btn-xl" data-loader-init="1"><span class="btn-label">Search</span><span
                    class="btn-spinner" aria-hidden="true"><span class="spin"></span></span></button>
            </form>
        </div>
    </div>
</div>

{{-- Swiper slider --}}
<div class="swiper wow-hero-swiper" data-hero-swiper>
  <div class="swiper-wrapper">
    <div class="swiper-slide">
      @include('home.sections.hero-slider-2')
    </div>
  </div>

  {{-- Controls overlay --}}
  <div class="wow-hero-nav">
    <div class="container-page">
      <button type="button" class="wow-hero-btn wow-hero-prev" aria-label="Previous slide">
        <i class="bi bi-chevron-left"></i>
      </button>

      <div class="wow-hero-pagination"></div>

      <button type="button" class="wow-hero-btn wow-hero-next" aria-label="Next slide">
        <i class="bi bi-chevron-right"></i>
      </button>
    </div>
  </div>
</div>

  {{-- Swiper JS --}}
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const el = document.querySelector('[data-hero-swiper]');
      if (!el) return;

      new Swiper(el, {
        loop: true,
        speed: 650,
        autoHeight: true,
        effect: 'slide',

        autoplay: {
          delay: 7000,
          disableOnInteraction: false,
          pauseOnMouseEnter: true,
        },

        navigation: {
          nextEl: '.wow-hero-next',
          prevEl: '.wow-hero-prev',
        },

        pagination: {
          el: '.wow-hero-pagination',
          clickable: true,
        },

        keyboard: {
          enabled: true,
          onlyInViewport: true,
        },

        a11y: {
          enabled: true,
        },
      });
    });
  </script>
