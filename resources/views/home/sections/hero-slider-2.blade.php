{{-- resources/views/home/sections/hero-slider-2.blade.php --}}
{{-- Slide 2 = OUR VIBE poster-style hero (no whero-radial, background is the image) --}}

<style>
  /* Scoped to slide 2 only */
  .whero.whero--s2{
    position: relative;
    overflow: hidden;
    /* set a consistent hero height that matches the poster aspect */
    min-height: clamp(520px, 62vh, 760px);
    display: flex;
    align-items: stretch;

    /* Background image (replace URL if needed) */
    background-image: url('https://testing.studio.weofferwellness.co.uk/storage/uploads/images/127a52bb-264f-4246-8e1d-52fda86ecddb.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
  }

  /* soft readability wash without "radial" element */
  .whero.whero--s2::before{
    content:"";
    position:absolute;
    inset:0;
    background: linear-gradient(180deg, rgba(255,255,255,.08) 0%, rgba(255,255,255,.06) 40%, rgba(255,255,255,.10) 100%);
    pointer-events:none;
  }

  .ourvibe-wrap{
    position: relative;
    width: 100%;
    z-index: 1;
    padding: clamp(18px, 4vw, 42px) 0;
  }

  /* TOP BRAND */
  .ourvibe-top{
    text-align: center;
    margin-bottom: clamp(12px, 2vw, 18px);
  }
  .ourvibe-top .brand{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap: 10px;
    font-weight: 900;
    font-size: clamp(26px, 2.8vw, 44px);
    letter-spacing: -0.02em;
    color: #0b0f19;
  }
  .ourvibe-top .leaf{
    width: clamp(30px, 2.5vw, 44px);
    height: auto;
    display:block;
  }
  .ourvibe-top .present{
    margin-top: 6px;
    font-weight: 800;
    letter-spacing: .46em;
    text-transform: uppercase;
    font-size: 12px;
    color: rgba(11,15,25,.86);
  }

  /* MAIN GRID */
  .ourvibe-grid{
    display:grid;
    grid-template-columns: 1.15fr .85fr;
    gap: clamp(16px, 2.6vw, 28px);
    align-items: start;
  }

  /* LEFT */
  .ourvibe-left{ position: relative; }

  .ourvibe-wave{
    height: 26px;
    width: min(320px, 70%);
    margin: 6px 0 14px;
    background:
      radial-gradient(circle at 10% 50%, rgba(255,90,90,.95) 0 6px, transparent 7px),
      radial-gradient(circle at 20% 40%, rgba(255,165,60,.95) 0 5px, transparent 6px),
      radial-gradient(circle at 30% 55%, rgba(255,220,80,.95) 0 5px, transparent 6px),
      radial-gradient(circle at 45% 45%, rgba(120,220,90,.95) 0 6px, transparent 7px),
      radial-gradient(circle at 60% 55%, rgba(60,180,255,.95) 0 5px, transparent 6px),
      radial-gradient(circle at 75% 45%, rgba(150,120,255,.95) 0 6px, transparent 7px),
      radial-gradient(circle at 88% 55%, rgba(255,120,210,.95) 0 5px, transparent 6px);
    filter: drop-shadow(0 8px 20px rgba(0,0,0,.10));
    opacity: .95;
    border-radius: 999px;
  }

  .ourvibe-title{
    font-weight: 1000;
    text-transform: uppercase;
    letter-spacing: -0.01em;
    line-height: .92;
    font-size: clamp(58px, 7vw, 120px);

    /* gradient fill */
    background: linear-gradient(90deg, #ffb300 0%, #ff8a00 22%, #7CFF00 55%, #00C2FF 78%, #8A5CFF 100%);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;

    /* thick outline */
    -webkit-text-stroke: 10px rgba(24, 20, 34, .90);
    paint-order: stroke fill;
    filter: drop-shadow(0 18px 30px rgba(0,0,0,.18));
  }

  .ourvibe-script{
    margin-top: 10px;
    font-family: "Brush Script MT", "Segoe Script", "Comic Sans MS", cursive;
    font-weight: 500;
    line-height: 1.05;
    color: #0b0f19;
    font-size: clamp(32px, 3.3vw, 56px);
    text-shadow: 0 10px 24px rgba(0,0,0,.10);
  }
  .ourvibe-script small{
    display:block;
    font-size: clamp(22px, 2.2vw, 40px);
    margin-top: 2px;
    opacity: .95;
  }

  .ourvibe-loc-badge{
    display:inline-block;
    margin-top: 14px;
    padding: 10px 14px;
    border-radius: 14px;
    background: rgba(20,18,30,.88);
    border: 4px solid rgba(20,18,30,.95);
    color: #ffe55c;
    font-weight: 1000;
    text-transform: uppercase;
    letter-spacing: .06em;
    line-height: 1.05;
    font-size: clamp(16px, 1.8vw, 26px);
    text-shadow: 0 2px 0 rgba(0,0,0,.25);
    box-shadow: 0 18px 45px rgba(0,0,0,.22);
  }

  .ourvibe-date{
    margin-top: clamp(18px, 3.2vw, 30px);
    font-family: "Brush Script MT", "Segoe Script", "Comic Sans MS", cursive;
    color: #0b0f19;
    text-shadow: 0 12px 22px rgba(0,0,0,.12);
    line-height: 1.0;
  }
  .ourvibe-date .d1{
    font-size: clamp(34px, 3.8vw, 60px);
    font-weight: 600;
  }
  .ourvibe-date .d2{
    font-size: clamp(42px, 4.8vw, 78px);
    font-weight: 700;
    margin-top: 6px;
  }

  /* RIGHT */
  .ourvibe-right{
    position: relative;
    min-height: 360px;
  }

  .ourvibe-sticker{
    position:absolute;
    top: 8px;
    right: 0;
    width: clamp(120px, 12vw, 170px);
    height: clamp(120px, 12vw, 170px);
    border-radius: 999px;
    background: #0b0f19;
    color: #ffd400;
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
    transform: rotate(-10deg);
    box-shadow: 0 22px 60px rgba(0,0,0,.30);
  }

  .ourvibe-box{
    position:absolute;
    right: 0;
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
  }

  .ourvibe-box.box-1{ top: clamp(110px, 14vw, 160px); width: min(280px, 100%); }
  .ourvibe-box.box-2{ top: clamp(300px, 30vw, 390px); width: min(340px, 100%); }

  .ourvibe-box .spaced{
    letter-spacing: .5em;
    display:inline-block;
    margin-left: .18em;
  }

  /* BOTTOM LINE */
  .ourvibe-bottom{
    margin-top: clamp(14px, 2.4vw, 26px);
    text-align: center;
    font-family: "Brush Script MT", "Segoe Script", "Comic Sans MS", cursive;
    font-size: clamp(18px, 1.9vw, 30px);
    color: rgba(11,15,25,.92);
    text-shadow: 0 10px 22px rgba(0,0,0,.10);
    line-height: 1.15;
  }

  .ourvibe-actions{
    margin-top: 10px;
    display:flex;
    justify-content:center;
    gap: 12px;
    flex-wrap:wrap;
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

  /* Responsive: stack right column below */
  @media (max-width: 991px){
    .ourvibe-grid{ grid-template-columns: 1fr; }
    .ourvibe-right{ min-height: 320px; }
    .ourvibe-sticker{ right: 0; }
    .ourvibe-box{ right: 0; left: 0; margin-left:auto; }
    .ourvibe-box.box-1{ top: 110px; width: min(360px, 100%); }
    .ourvibe-box.box-2{ top: 270px; width: min(420px, 100%); }
  }

  @media (max-width: 575px){
    .ourvibe-top .present{ letter-spacing: .30em; }
    .ourvibe-title{ -webkit-text-stroke: 8px rgba(24, 20, 34, .90); }
  }
</style>

<section data-v-f43bb09d="" class="whero whero--s2">
  <div class="ourvibe-wrap">
    <div data-v-f43bb09d="" class="container whero-pad">

      <div class="ourvibe-top">
        <div class="brand">
          {{-- Swap src to your actual leaf/logo asset --}}
          <img class="leaf" src="/images/leaf.svg" alt="" aria-hidden="true">
          <span>we offer wellness®</span>
        </div>
        <div class="present">PROUD TO PRESENT</div>
      </div>

      <div class="ourvibe-grid">
        <div class="ourvibe-left">
          <div class="ourvibe-wave" aria-hidden="true"></div>

          <div class="ourvibe-title">OURVIBE</div>

          <div class="ourvibe-script">
            Sound Healing &amp;<br>
            <small>Meditation Festival</small>
          </div>

          <div class="ourvibe-loc-badge">
            BILSINGTON PRIORY,<br>ASHFORD, KENT
          </div>

          <div class="ourvibe-date">
            <div class="d1">11th &amp; 12th</div>
            <div class="d2">July 2026</div>
          </div>
        </div>

        <div class="ourvibe-right">
          <div class="ourvibe-sticker">
            DJs AND LIVE<br>
            MUSICIANS<br>
            PLAYING CHILLED<br>
            BEATS IN OUR<br>
            CHILL ZONE
          </div>

          <div class="ourvibe-box box-1">
            BEAUTIFUL<br>
            WELLNESS<br>
            PRODUCTS TO<br>
            BUY, THERAPIES<br>
            TO TRY &amp;<br>
            READINGS IN<br>
            OUR READERS’<br>
            <span class="spaced">R O O M</span>
          </div>

          <div class="ourvibe-box box-2">
            35 SOUND HEALING<br>
            SESSIONS, TALKS &amp;<br>
            <span class="spaced">W O R K S H O P S</span>
          </div>
        </div>
      </div>

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
