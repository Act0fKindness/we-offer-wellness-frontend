{{-- resources/views/home/sections/hero-slider-2.blade.php --}}
{{-- Slide 2 = OUR VIBE poster-style hero (background image, top-centre logo, centre image, bottom-centre text/buttons) --}}

<style>
  /* Scoped to slide 2 only */
  .whero.whero--s2{
    position: relative;
    overflow: hidden;
    min-height: clamp(520px, 62vh, 760px);
    display: flex;
    align-items: stretch;

    background-image: url('https://testing.studio.weofferwellness.co.uk/storage/uploads/images/127a52bb-264f-4246-8e1d-52fda86ecddb.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
  }

  .whero.whero--s2::before{
    content:"";
    position:absolute;
    inset:0;
    background: linear-gradient(180deg, rgba(255,255,255,.10) 0%, rgba(255,255,255,.06) 40%, rgba(255,255,255,.10) 100%);
    pointer-events:none;
    z-index: 0;
  }

  .ourvibe-wrap{
    position: relative;
    width: 100%;
    z-index: 1;
    padding: clamp(14px, 3.2vw, 32px) 0;
  }

  /* Make the container behave like a vertical poster: top / middle / bottom */
  .ourvibe-shell{
    min-height: clamp(520px, 62vh, 760px);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 14px;
    position: relative;
  }

  /* TOP BRAND (centre + top) */
  .ourvibe-top{
    text-align: center;
    display:flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding-top: 6px;
    position: relative;
    z-index: 2;
  }
  .ourvibe-top .brand{
    width: 100%;
    display:flex;
    justify-content:center;
    align-items:center;
  }
  .ourvibe-top .brand svg{
    display:block;
    height: 30px;
    max-width: min(760px, 92vw);
  }
  .ourvibe-top .present{
    font-weight: 800;
    letter-spacing: .46em;
    text-transform: uppercase;
    font-size: 12px;
    color: rgba(11,15,25,.86);
  }

  /* Glow signs (desktop only) */
  .glow-signs{
    position: absolute;
    left: 24px;
    top: 110px;
    z-index: 3;
    pointer-events: none;
  }
  .glow-signs .glow-sign{
    position: absolute;
    left: 0;
    top: 0;
    width: clamp(140px, 16vw, 240px);
  }
  .glow-signs .glow-sign img{
    width: 100%;
    height: auto;
    display: block;
    filter: drop-shadow(0 18px 40px rgba(0,0,0,.22));
  }
  /* stack on top of each other with slight offset for depth */
  .glow-signs .glow-sign--1{ top: 0; }
  .glow-signs .glow-sign--2{ top: 26px; left: 10px; opacity: .98; }

  /* Hide glow signs on tablet/mobile */
  @media (max-width: 991px){
    .glow-signs{ display:none !important; }
  }

  /* MIDDLE IMAGE (centre middle) */
  .ourvibe-mid{
    flex: 1;
    display:flex;
    align-items: center;
    justify-content: center;
    padding: clamp(8px, 2.2vw, 16px) 0;
    position: relative;
    z-index: 2;
  }
  .ourvibe-mid img{
    display:block;
    height: auto;
    max-height: 35vh;
    max-width: min(820px, 92vw);
    filter: drop-shadow(0 24px 60px rgba(0,0,0,.20));
  }

  /* RIGHT FLOATING CALLOUTS */
  .ourvibe-floats{
    position:absolute;
    inset: 0;
    pointer-events: none;
    z-index: 2;
  }

  .ourvibe-sticker{
    position:absolute;
    top: clamp(88px, 10vh, 120px);
    right: clamp(12px, 2.2vw, 22px);
    width: clamp(120px, 12vw, 170px);
    height: clamp(120px, 12vw, 170px);
    border-radius: 999px;
    background: #0b0f19;
    color: #42b649;
    display:flex;
    align-items:center;
    justify-content:center;
    text-align:center;
    padding: 14px;
    font-weight: 1000;
    text-transform: uppercase;
    letter-spacing: .06em;
    line-height: 1.05;
    font-size: clamp(11px, 1.1vw, 14px);
    transform: rotate(15deg);
    box-shadow: 0 22px 60px rgba(0,0,0,.30);
  }

  .ourvibe-box{
    position:absolute;
    right: clamp(12px, 2.2vw, 22px);
    background: rgba(255,255,255,.96);
    border: 6px solid #0b0f19;
    border-radius: 10px;
    box-shadow: 0 22px 60px rgba(0,0,0,.22);
    padding: 16px 18px;
    color:#0b0f19;
    font-weight: 1000;
    text-transform: uppercase;
    letter-spacing: .22em;
    font-size: clamp(12px, 1.05vw, 14px);
    line-height: 1.25;
    pointer-events: none;
  }

  .ourvibe-box.box-1{
    top: clamp(240px, 28vh, 310px);
    width: min(280px, 78vw);
  }
  .ourvibe-box.box-2{
    top: clamp(420px, 48vh, 510px);
    width: min(340px, 84vw);
  }

  .ourvibe-box .spaced{
    letter-spacing: .5em;
    display:inline-block;
    margin-left: .18em;
  }

  /* BOTTOM (text + buttons bottom centre) */
  .ourvibe-bottom{
    text-align: center;
    font-family: "Brush Script MT", "Segoe Script", "Comic Sans MS", cursive;
    font-size: clamp(18px, 1.9vw, 30px);
    color: rgba(11,15,25,.92);
    text-shadow: 0 10px 22px rgba(0,0,0,.10);
    line-height: 1.15;
    padding-bottom: 6px;
    position: relative;
    z-index: 2;
  }

  .ourvibe-actions{
    margin-top: 10px;
    display:flex;
    justify-content:center;
    gap: 12px;
    flex-wrap:wrap;
    font-family: 'Manrope', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  }

  .ourvibe-buy{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    padding: 10px 16px;
    border-radius: 999px;
    background: #0b0f19;
    color: #fff;
    font-size: 12px;
    text-decoration:none;
    font-weight: 900;
    letter-spacing: .04em;
    box-shadow: 0 18px 40px rgba(0,0,0,.22);
  }
  .ourvibe-buy:hover{ opacity:.92; }

  .ourvibe-link{
    display:inline-flex;
    align-items:center;
    padding: 10px 14px;
    border-radius: 999px;
    background: rgba(255,255,255,.85);
    border: 2px solid rgba(11,15,25,.18);
    color: rgba(11,15,25,.92);
    text-decoration:none;
    font-weight: 900;
    letter-spacing: .08em;
    text-transform: uppercase;
    font-size: 11px;
  }

  /* Responsive adjustments */
  @media (max-width: 991px){
    .ourvibe-box.box-1{ top: auto; bottom: 210px; }
    .ourvibe-box.box-2{ top: auto; bottom: 110px; }
    .ourvibe-sticker{ top: 86px; }
    .ourvibe-mid img{ max-height: 48vh; }
  }

  @media (max-width: 575px){
    .ourvibe-top .present{ letter-spacing: .30em; }
    .ourvibe-box{ border-width: 5px; padding: 14px 14px; }
    .ourvibe-box.box-1{ bottom: 220px; }
    .ourvibe-box.box-2{ bottom: 118px; }
  }
</style>

<section data-v-f43bb09d="" class="whero whero--s2">

  <div class="ourvibe-wrap">
    <div data-v-f43bb09d="" class="container whero-pad ourvibe-shell" style="padding-top: 50px !important;">

      {{-- TOP: logo centred --}}
      <div class="ourvibe-top">
        <div class="brand">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1240.46 141.78" aria-hidden="true">
            <defs><style>.cls-1-header {fill:#599d91}.cls-2-header {fill:#000}</style></defs>
            <g><g>
              {{-- (your SVG paths stay exactly as you pasted) --}}
              <path class="cls-2-header " d="M483.94,63.07v57h-23.01v-56.97l-9.85.03c6.06,20.44.68,44.4-19.84,54.14-13.48,6.41-29.22,6.51-42.6-.12-14.21-7.04-21.27-21.46-21.22-37.09.04-15.11,6.2-29.32,19.58-36.92,21.57-12.27,53.7-6.68,63.84,19.24l.5-16.16,9.59-.25c-.06-9.84,1.82-19.92,9.35-26.56,8.55-7.54,20.13-8.84,31.69-5.75l-3.26,17.23c-4.69-1.25-9.36-1.92-12.18,1.8-2.66,3.51-2.84,8.57-2.6,13.35l23.92.03c-.5-15.95,6.15-31.21,22.9-34.29,6.32-1.16,12.4-.62,19.32.19l-.9,18.22c-5.2-.83-10.13-1.73-13.92,1.15-4.44,3.37-4.68,9.23-4.25,14.65l14.51.18.61,17.91c8.02-13.24,19.98-19.44,34.91-18.99,13.69.42,25.13,8.26,29.2,21.63,2.32,7.62,2.96,15.74,1.21,23.32l-47.37.06c2.16,17.94,28.17,14.65,41.19,11.13l3.05,15.32c-23.3,8.48-58.89,7.46-65.25-22.51-2.28-10.74-1.09-20.65,3.87-30.99l-15.99.03v56.98h-23v-56.99h-24ZM423.77,64.15c-2.54-5.71-7.68-8.95-13.3-8.86-5.3.08-10.43,2.89-13.03,8.24-4.67,9.63-4.76,21.35-.39,31.11,2.56,5.72,7.77,9.04,13.4,9.1,16.2.18,20.01-24.56,13.32-39.59ZM590.93,74.23c-.49-5.48-1.7-8.71-4.81-11.19-4.16-3.31-9.96-3.44-14.72-1.03-4,2.03-6.82,6.66-7.55,12.29l27.08-.07h0Z"></path>
              {{-- ... keep all remaining SVG paths exactly as in your snippet ... --}}
            </g></g>
          </svg>
        </div>
        <div class="present">PROUD TO PRESENT</div>
      </div>

      {{-- Glow signs (desktop only, left, stacked) --}}
      <div class="glow-signs" aria-hidden="true">
        <div class="glow-sign glow-sign--1">
          <img src="https://testing.studio.weofferwellness.co.uk/storage/uploads/images/0f843692-6f17-48ca-9944-726e1e0bcd69.png" alt="">
        </div>
        <div class="glow-sign glow-sign--2">
          <img src="https://testing.studio.weofferwellness.co.uk/storage/uploads/images/6b12e6e9-c88c-4490-91a3-9e4cfcb4498a.png" alt="">
        </div>
      </div>

      {{-- MIDDLE: poster image centred --}}
      <div class="ourvibe-mid">
        <img
          src="https://testing.studio.weofferwellness.co.uk/storage/uploads/images/3c759f8e-fa97-43eb-bed0-41652086adf9.png"
          alt="OUR VIBE — Sound Healing & Meditation Festival"
          loading="eager"
        >
      </div>

      {{-- BOTTOM: text + buttons centred --}}
      <div class="ourvibe-bottom">
        Workshops · Sound Healing · Qigong · Yoga · Meditation · Gong Baths · Kirtan ·
        Tuning Forks · Drumming

        <div class="ourvibe-actions">
          <a class="ourvibe-buy" href="https://link.weofferwellness.co.uk/ourvibe">
            Buy Now <i class="bi bi-arrow-right"></i>
          </a>

          <a class="ourvibe-link" href="https://link.weofferwellness.co.uk/ourvibe">
            link.weofferwellness.co.uk/ourvibe
          </a>
        </div>
      </div>

    </div>
  </div>
</section>
