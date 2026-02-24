{{-- resources/views/home/partials/hero-slider.blade.php --}}

@push('head')
<style>
  /* HERO SLIDER (adds only new classes, does not touch existing whero styles) */
  .wow-hero-slider{
    position: relative;
  }
  .wow-hero-slide{
    display: none;
  }
  .wow-hero-slide.is-active{
    display: block;
  }

  .wow-hero-slider-nav{
    position: absolute;
    left: 0;
    right: 0;
    bottom: 18px;
    z-index: 5;
    pointer-events: none;
  }
  .wow-hero-slider-nav .container{
    display:flex;
    align-items:center;
    justify-content: space-between;
    gap: 14px;
  }

  .wow-hero-nav-group{
    display:flex;
    align-items:center;
    gap: 10px;
    pointer-events: auto;
  }

  .wow-hero-nav-btn{
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
  }
  .wow-hero-nav-btn:focus{ outline: none; box-shadow: 0 0 0 4px rgba(68,76,231,.20), 0 12px 30px rgba(16,24,40,.18); }

  .wow-hero-dots{
    display:flex;
    align-items:center;
    gap: 8px;
    pointer-events: auto;
  }
  .wow-hero-dot{
    width: 10px;
    height: 10px;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,.45);
    background: rgba(255,255,255,.22);
    cursor: pointer;
    padding: 0;
  }
  .wow-hero-dot[aria-current="true"]{
    width: 26px;
    background: rgba(255,255,255,.65);
    border-color: rgba(255,255,255,.65);
  }

  /* Make sure nav doesn’t collide on small screens */
  @media (max-width: 575px){
    .wow-hero-slider-nav{ bottom: 10px; }
    .wow-hero-nav-btn{ width: 40px; height: 40px; }
  }

  /* Respect reduced motion */
  @media (prefers-reduced-motion: reduce){
    .wow-hero-slide{ transition: none !important; }
  }
</style>
@endpush

{{-- Sticky search bar (UNCHANGED, appears once, keeps all IDs intact) --}}
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

{{-- Slider wrapper (NEW classes only) --}}
<div class="wow-hero-slider" data-hero-slider aria-roledescription="carousel" aria-label="Homepage hero">

  <div class="wow-hero-slide is-active" data-hero-slide aria-hidden="false">
    @include('home.sections.hero-slider-1')
  </div>

  <div class="wow-hero-slide" data-hero-slide aria-hidden="true">
    @include('home.sections.hero-slider-2')
  </div>

  <div class="wow-hero-slide" data-hero-slide aria-hidden="true">
    @include('home.sections.hero-slider-3')
  </div>

  {{-- Nav --}}
  <div class="wow-hero-slider-nav" aria-hidden="false">
    <div class="container">
      <div class="wow-hero-nav-group">
        <button type="button" class="wow-hero-nav-btn" data-hero-prev aria-label="Previous slide">
          <i class="bi bi-chevron-left"></i>
        </button>
        <button type="button" class="wow-hero-nav-btn" data-hero-next aria-label="Next slide">
          <i class="bi bi-chevron-right"></i>
        </button>
      </div>

      <div class="wow-hero-dots" role="tablist" aria-label="Choose a hero slide">
        <button type="button" class="wow-hero-dot" data-hero-dot="0" aria-label="Slide 1" aria-current="true"></button>
        <button type="button" class="wow-hero-dot" data-hero-dot="1" aria-label="Slide 2" aria-current="false"></button>
        <button type="button" class="wow-hero-dot" data-hero-dot="2" aria-label="Slide 3" aria-current="false"></button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
(() => {
  const root = document.querySelector('[data-hero-slider]');
  if (!root) return;

  const slides = Array.from(root.querySelectorAll('[data-hero-slide]'));
  const dots   = Array.from(root.querySelectorAll('[data-hero-dot]'));
  const btnPrev = root.querySelector('[data-hero-prev]');
  const btnNext = root.querySelector('[data-hero-next]');

  if (slides.length <= 1) return;

  let i = 0;
  let timer = null;

  const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function setActive(next){
    i = (next + slides.length) % slides.length;

    slides.forEach((el, idx) => {
      const on = idx === i;
      el.classList.toggle('is-active', on);
      el.setAttribute('aria-hidden', on ? 'false' : 'true');
    });

    dots.forEach((d, idx) => {
      d.setAttribute('aria-current', idx === i ? 'true' : 'false');
    });
  }

  function next(){ setActive(i + 1); }
  function prev(){ setActive(i - 1); }

  function start(){
    if (prefersReduced) return;
    stop();
    timer = setInterval(next, 8000); // 8s autoplay
  }

  function stop(){
    if (timer) { clearInterval(timer); timer = null; }
  }

  btnNext && btnNext.addEventListener('click', () => { next(); start(); });
  btnPrev && btnPrev.addEventListener('click', () => { prev(); start(); });

  dots.forEach(d => {
    d.addEventListener('click', () => {
      const n = parseInt(d.getAttribute('data-hero-dot') || '0', 10);
      setActive(n);
      start();
    });
  });

  // Pause on hover/focus (desktop)
  root.addEventListener('mouseenter', stop);
  root.addEventListener('mouseleave', start);
  root.addEventListener('focusin', stop);
  root.addEventListener('focusout', start);

  // Keyboard
  root.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') { prev(); start(); }
    if (e.key === 'ArrowRight') { next(); start(); }
  });

  start();
})();
</script>
@endpush
