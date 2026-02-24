{{-- resources/views/home/sections/hero-slider-2.blade.php --}}

@push('head')
<style>
  /* Slide 2: OUR VIBE flyer styling (scoped so it won't touch slide 1/3) */
  .whero.whero--s2{ position: relative; }
  .whero.whero--s2 .whero-radial{
    /* background image via CSS var set on the section */
    background-image:
      linear-gradient(180deg, rgba(10,12,18,.55) 0%, rgba(10,12,18,.35) 40%, rgba(10,12,18,.62) 100%),
      var(--hero-bg);
    background-size: cover;
    background-position: center;
    opacity: 1; /* override any previous opacity assumptions */
    filter: saturate(1.05) contrast(1.02);
  }

  .whero--s2 .festival-panel{
    position: relative;
    max-width: 560px;
    padding: 18px 18px 16px;
    border-radius: 22px;
    background: rgba(255,255,255,.10);
    border: 1px solid rgba(255,255,255,.18);
    box-shadow: 0 22px 70px rgba(0,0,0,.25);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    color: rgba(255,255,255,.96);
  }

  .whero--s2 .festival-topline{
    display:flex;
    flex-wrap:wrap;
    gap: 10px 12px;
    align-items:center;
    margin-bottom: 14px;
  }

  .whero--s2 .festival-brand{
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    font-size: 12px;
    opacity: .95;
  }
  .whero--s2 .festival-present{
    font-weight: 700;
    letter-spacing: .14em;
    text-transform: uppercase;
    font-size: 11px;
    opacity: .85;
    padding-left: 12px;
    border-left: 1px solid rgba(255,255,255,.25);
  }

  .whero--s2 .festival-big{
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .04em;
    line-height: 1.02;
    font-size: clamp(28px, 3.6vw, 44px);
    margin: 10px 0 12px;
  }

  .whero--s2 .festival-title{
    margin: 12px 0 4px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .06em;
    font-size: clamp(28px, 4vw, 48px);
    line-height: 1.0;
  }

  .whero--s2 .festival-subtitle{
    font-weight: 700;
    font-size: clamp(16px, 2vw, 20px);
    opacity: .95;
    line-height: 1.2;
    margin-bottom: 14px;
  }

  .whero--s2 .festival-block{
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    line-height: 1.05;
    font-size: clamp(16px, 2.2vw, 22px);
    margin: 12px 0 14px;
  }

  .whero--s2 .festival-loc{
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .12em;
    font-size: 12px;
    opacity: .92;
    margin: 10px 0 6px;
  }

  .whero--s2 .festival-date{
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .06em;
    font-size: 24px;
    line-height: 1.05;
    margin: 6px 0 14px;
  }
  .whero--s2 .festival-date small{
    display:block;
    font-size: 16px;
    font-weight: 800;
    opacity: .92;
    letter-spacing: .10em;
    margin-top: 2px;
  }

  .whero--s2 .festival-sessions{
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .08em;
    line-height: 1.05;
    font-size: 14px;
    opacity: .95;
    margin: 10px 0 14px;
  }
  .whero--s2 .festival-spaced{
    letter-spacing: .35em;
    display:inline-block;
    margin-left: .15em;
  }

  .whero--s2 .festival-cta{
    display:flex;
    align-items:center;
    gap: 10px;
    margin: 12px 0 14px;
  }
  .whero--s2 .festival-cta .btn-wow{
    border-radius: 999px;
    box-shadow: 0 18px 40px rgba(0,0,0,.22);
  }

  .whero--s2 .festival-tags{
    font-size: 12px;
    line-height: 1.35;
    opacity: .92;
    margin-top: 8px;
  }

  .whero--s2 .festival-link{
    display:inline-block;
    margin-top: 12px;
    font-weight: 800;
    letter-spacing: .10em;
    text-transform: uppercase;
    font-size: 11px;
    color: rgba(255,255,255,.92);
    text-decoration: none;
    border-bottom: 1px dashed rgba(255,255,255,.40);
  }
  .whero--s2 .festival-link:hover{ border-bottom-style: solid; }

  /* mobile breathing room */
  @media (max-width: 575px){
    .whero--s2 .festival-panel{ padding: 16px; border-radius: 18px; }
    .whero--s2 .festival-present{ border-left: none; padding-left: 0; }
  }
</style>
@endpush

<section
  data-v-f43bb09d=""
  class="whero whero--s2"
  style="--hero-bg: url('https://testing.studio.weofferwellness.co.uk/storage/uploads/images/127a52bb-264f-4246-8e1d-52fda86ecddb.jpg');"
>
  <div data-v-f43bb09d="" class="whero-radial" aria-hidden="true"></div>

  <div data-v-f43bb09d="" class="container whero-pad">
    <div data-v-f43bb09d="" class="row align-items-center g-5">
      <div data-v-f43bb09d="" class="col-12 col-lg-7">
        <div class="festival-panel">
          <div class="festival-topline">
            <div class="festival-brand">we offer wellness®</div>
            <div class="festival-present">PROUD TO PRESENT</div>
          </div>

          <div class="festival-big">
            DJs AND LIVE<br>
            MUSICIANS<br>
            PLAYING CHILLED<br>
            BEATS IN OUR<br>
            CHILL ZONE
          </div>

          <div class="festival-title">OUR VIBE</div>
          <div class="festival-subtitle">Sound Healing &amp;<br>Meditation Festival</div>

          <div class="festival-block">
            BEAUTIFUL<br>
            WELLNESS<br>
            PRODUCTS TO<br>
            BUY, THERAPIES<br>
            TO TRY &amp;<br>
            READINGS IN<br>
            OUR READERS’<br>
            R O O M
          </div>

          <div class="festival-loc">BILSINGTON PRIORY,<br>ASHFORD, KENT</div>

          <div class="festival-date">
            11th &amp; 12th
            <small>July 2026</small>
          </div>

          <div class="festival-sessions">
            35 SOUND HEALING<br>
            SESSIONS, TALKS &amp;<br>
            <span class="festival-spaced">W O R K S H O P S</span>
          </div>

          <div class="festival-cta">
            <a
              href="https://link.weofferwellness.co.uk/ourvibe"
              class="btn-wow btn-md btn-arrow"
              data-loader-init="1"
            >
              <span class="btn-label">Buy Now</span>
              <span class="btn-icon-wrap" aria-hidden="true">
                <svg class="btn-icon-hover" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"></path>
                </svg>
                <svg class="btn-icon-default" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                  <path fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12l-4 4m4-4-4-4"></path>
                </svg>
              </span>
              <span class="btn-spinner" aria-hidden="true"><span class="spin"></span></span>
            </a>
          </div>

          <div class="festival-tags">
            Workshops · Sound Healing · Qigong · Yoga · Meditation · Gong Baths · Kirtan ·
            Tuning Forks · Drumming
          </div>

          <a class="festival-link" href="https://link.weofferwellness.co.uk/ourvibe">
            LINK.WEOFFERWELLNESS.CO.UK/OURVIBE
          </a>
        </div>
      </div>

      <div data-v-f43bb09d="" class="col-12 col-lg-5">
        {{-- Leave empty for this slide, or paste your right-panel markup here if you want it --}}
      </div>
    </div>
  </div>
</section>
