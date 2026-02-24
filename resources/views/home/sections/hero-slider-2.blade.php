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

          <div class="ourvibe-title">
              <?xml version="1.0" encoding="UTF-8"?>
              <svg id="OUR_VIBE_LOGO_Image" data-name="OUR VIBE LOGO Image" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 522.87 129.08">
                <defs>
                  <style>
                    .cls-1 {
                      fill: #43b54a;
                    }

                    .cls-2 {
                      fill: #f9a21c;
                    }

                    .cls-3 {
                      fill: #fbcd0d;
                    }

                    .cls-4 {
                      fill: #3f1a4e;
                    }

                    .cls-5 {
                      fill: #b8d448;
                    }
                  </style>
                </defs>
                <g>
                  <path class="cls-4" d="M250.14,0l9.64.47,12.43.14c4.93.06,10.14,2.56,11.57,7.59l6.53,22.99,3.67-20.71c.9-5.09,7.09-8.89,12.31-8.88l30.93.04c3,0,4.82,3.44,6.04,5.76l18.12-.24,41.59.32c9.71.07,22.62,3.05,30.38,9.15.52.41,1.99,1.01,2.25.31.59-4.39,3.79-8.04,8.52-7.93l62.97,1.58c3.49.09,7.15,3.18,7.24,6.61.25,9.01.23,17.68,0,26.52-.08,3.24-2.9,5.67-5.64,5.83-.28.02-.69.09-.62.15l.37.34c.52.47,1.56,1.34,1.53,2.15l-.77,23.18c-.13,4-3.52,6.28-3.04,6.33l.57.05c3.62.35,6.83.62,10,2.47,3.63,2.11,6.85,6.12,6.02,10.85-1.63,9.27-2.81,18.72-4.81,27.85-1.25,5.71-7.89,6.42-12.63,6.06l-59.72-4.53c-4.62-.35-7.78-5.17-8.43-9.14-.03-.17-.43-.3-.56-.24-6.66,2.84-17.33,6.66-24.71,6.49l-37.92-.85c-2.4-.05-4.51-1.15-5.97-2.75-1.72,1.49-3.7,2.21-5.98,2.12l-20.89-.81c-3.33-.13-6.45-1.74-8.84-3.86-2.56-2.28-3.83-5.45-3.99-8.87-.33-7.13.42-14.04-.4-21.44l-9.12,27.95c-1.1,3.38-4.28,6.28-7.94,6.21l-32.13-.62c-4.78-.09-9.05-1.94-11.97-5.97-1.62,2.95-4.22,5.14-7.66,5.14h-30.3c-2.03,0-4.19-.17-5.96-.98-3.71-1.7-7.26-10.85-8.66-14.55-.26.06-.68.7-.64.98.46,3.38.55,6.54-.65,9.75-.98,2.63-3.38,5.5-6.45,5.5h-21.53c-4.19,0-7.77-2.9-9.95-6.24-1.67-2.56-2.19-5.25-2.91-8.39-6.85,9.39-17.99,14.18-28.98,15.65-16.88,2.45-36.17-3.16-44.48-19.52-9.43,18.79-28.17,26.8-48.24,26.07-7.58-.28-14.76-2.43-21.49-5.84-8.97-4.54-16.06-11.55-20.86-20.33C1.69,88.46.19,76.4,0,63.53c-.09-6.04.73-11.62,2.2-17.43,1.58-6.23,4.02-11.81,7.58-17.14C18.59,15.79,32.23,7.29,47.96,5.05s30.56,1.18,42.22,12.12c1.55-2.73,4.26-6.58,7.47-6.67l26.09-.73c3.81-.11,8.69.48,10.87,4.26,2.99-2.83,4.73-5.06,9.38-5.2l29.42-.9,33.59-.45c8.12-.11,15.75,2.24,23.52,4.39-.07-6.28,1.56-10.05,7.42-11.89h12.19ZM294.74,59.67l-3.15,9.25-3.09-10.12-12.91-44.38c-.68-2.35-1.25-4.38-2.65-6.34-.58-.81-1.74-1.94-2.86-1.94h-26.1c-.89.85-1.66,2.4-1.22,3.68l16.62,48.06c1.73,5,3.19,10.04,5.3,15.14l11.2,27.11c1,2.43,3.12,4.2,5.88,4.27l13.87.35,12.81.38c1.25.04,2.58-.52,2.94-1.96l11.6-46.73,10.64-46.65c.41-1.78-.76-3.52-2.61-3.59l-22.26-.74c-1.73-.06-3.19,2.35-3.47,3.89l-4.79,26.12c-1.51,8.24-3.5,16.1-5.75,24.21ZM7.91,62.36c-.16,1.62-.08,3.27.14,5.05.88,7.13,2.64,13.89,5.83,20.26,6.09,12.13,17.41,20.11,30.95,21.75,19.56,1.04,38.6-5.71,46.8-24.81,3.85-8.97,4.45-17.1,4.08-26.97.02-13.14-2.92-27.72-12.62-37.51-6.87-6.94-16.18-10.18-25.86-10.26-4.7-.04-9.05-.04-13.69.86C23.49,14.61,12.76,31.17,8.85,50.03c-.85,4.12-1.24,8.06-.94,12.33ZM178.21,54.89l1.41,41.84c.13,3.81,2.28,6.78,6.33,6.78l15.3-.03c1.29,0,2.64-.52,3.78-.94,1.05-.79,1.77-1.74,1.7-3.1l-1.44-26.5,19.58,25.59c1.69,2.2,3.51,5.15,6.55,5.58,9.33-.05,18.53-.26,27.8-.79,2.3-.13,2.39-4.04,1.07-5.53l-27.96-31.72c3.82-1.62,6.86-4.15,9.13-7.55,2.12-3.18,3.86-6.55,4.32-10.7s1.06-8.78-.31-12.9c-3.62-10.89-12.99-18.62-24.01-21.11-4.22-.96-8.07-1.09-12.38-.97l-29.65.78c-1.4.04-2.77,1.43-2.72,2.74l1.49,38.54ZM145.91,60.02c-.22,4.32.64,6.86-.74,11.47s-5.24,6.96-9.8,6.52c-2.88-.28-5.22-1.85-6.51-4.49-1.19-2.44-1.45-5.14-1.61-7.91l-.2-3.59-1.44-44.72c-.05-1.54-2.3-2.3-3.47-2.29l-10.26.11c-3.76.04-7.38.48-11.15.82-1.17.11-2.11,1.83-2.07,3.1l1.38,40.35c.15,4.53-.02,9.06.72,13.71.79,4.93,1.99,9.53,4.21,13.99,6.14,12.31,18.03,18.27,31.6,18.03,4.46-.08,8.66-.45,12.9-1.69,16.03-4.71,22.1-20.29,21.59-35.75l-.41-12.47-.54-37.95c-.02-1.48-1.08-2.93-2.75-2.93h-20.93c-1.03-.01-1.95,1.23-1.92,2.34l1.44,43.35ZM372.63,58.53c-.27.5-.13,1.14-.11,1.86l.29,16.67.26,16.36c.05,3.11.11,6.12.38,9.22.13,1.49,2.71,2.22,3.87,2.27l7.95.34,17.3.62,16.63.08c3.99-.69,7.65-1.66,11.22-3.37,8.58-4.1,13.58-12.37,13.65-21.85.04-5.05-.92-9.79-3.89-14-1.87-2.65-4.05-5.25-6.87-7-1.38-1.38-3.13-2.38-5.09-3.52,7.34-2.28,9.96-13.66,9.55-20.3-.55-8.99-8-15.7-16.13-18.66-4.45-1.62-9.04-2.93-13.91-2.93l-33.89-.05c-1.06,0-2.06,1.34-2.04,2.47l.84,41.8ZM338.42,57.65l.14,31.59.34,13.32c.72,1.96,3.4,2.41,5.16,2.47l19.1.67c1.09-.61,1.79-2.24,1.76-3.65l-.89-45.04-.67-38.82c-.02-1.02-.65-2.12-1.19-2.6-.89-.8-2.07-.65-3.22-.65l-18.26.05c-3.43,0-2.7,5.14-2.69,6.17l.41,36.48ZM446.84,59.14c-.29,1.5,0,3.06.02,4.71l.3,17.01.22,16.71c.03,2.1.25,4.23.16,6.31-.11,2.48,1.33,4.08,3.79,4.34l20.31,2.17,16.07,1.49,19.03,1.54c1.06.09,2.17-.44,2.35-1.57l2.96-18.38c.36-2.26-1.04-4.53-3.61-4.76l-35.86-3.09-.05-14.12,3.32.22,4.13.31,4.16.29,4.46.28,5.68.38c.62.04,1.54-.05,2.02-.42.42-.33.79-1.24.83-1.91l.31-5.6,1.06-12.54c.11-1.33-1.73-1.82-2.78-2.1l-24.9-.02.05-12.78,29.92.74c1.75.09,2.17-1.06,2.28-2.49l1.49-18.69c.09-1.1-1.15-2.05-2.37-2.05h-53.95c-1.24,0-2.14.92-2.12,2.12l.73,41.88Z"/>
                  <path class="cls-2" d="M170.61,55.21l.41,12.47c.51,15.46-5.55,31.04-21.59,35.75-4.24,1.24-8.43,1.61-12.9,1.69-13.57.24-25.46-5.72-31.6-18.03-2.22-4.46-3.42-9.06-4.21-13.99-.75-4.65-.57-9.18-.72-13.71l1.3.44c7.45,2.52,17.97,2.3,25.72,2.18l.2,3.59c.16,2.77.42,5.47,1.61,7.91,1.29,2.64,3.63,4.21,6.51,4.49,4.56.44,8.48-2.12,9.8-6.52s.52-7.14.74-11.47c7.77-.07,17.05-3.14,24.7-4.81Z"/>
                  <path class="cls-3" d="M127.04,62.01c-7.76.11-18.27.33-25.72-2.18l-1.3-.44-1.38-40.35c-.04-1.27.9-3,2.07-3.1,3.78-.34,7.39-.78,11.15-.82l10.26-.11c1.17-.01,3.42.75,3.47,2.29l1.44,44.72Z"/>
                  <path class="cls-3" d="M170.61,55.21c-7.64,1.67-16.92,4.74-24.7,4.81l-1.44-43.35c-.04-1.11.88-2.36,1.92-2.36h20.93c1.67.02,2.73,1.46,2.75,2.94l.54,37.95Z"/>
                  <g>
                    <path class="cls-4" d="M71.77,51.59c0,.15.06.5.09.65.02.1.23.09.36.09-.05.64-.5,1.79-.46,2.48-.05.87,0,.91-.06.96l-.18,2.73c-.32,4.83-.91,9.41-2.9,13.76-3.08,6.74-9.4,10.52-16.85,10.3-5.75-.17-10.92-3.09-13.86-8.11-2.25-3.84-2.96-8.13-3.19-12.55l-.16-3.09c-.09-1.65-.18-3.61.13-5.37.8-4.64,2.84-8.92,5.78-12.49,4.45-5.4,11.4-8.14,18.16-6.84,8.16,1.57,13.21,10.13,13.14,17.5Z"/>
                    <g>
                      <path class="cls-2" d="M71.77,54.8l5.31.69,8.03.92,4.47.55,6.15.66c.37,9.87-.23,18-4.08,26.97-8.2,19.1-27.24,25.86-46.8,24.81-13.54-1.64-24.86-9.62-30.95-21.75-3.2-6.37-4.95-13.13-5.83-20.26-.22-1.78-.3-3.43-.14-5.05,8.38,1.08,18.56-1.23,26.64-3.56l.16,3.09c.24,4.42.94,8.71,3.19,12.55,2.94,5.02,8.11,7.94,13.86,8.11,7.45.22,13.77-3.56,16.85-10.3,1.99-4.35,2.58-8.93,2.9-13.76l.18-2.73c.02-.26.06-.66.06-.96Z"/>
                      <path class="cls-3" d="M95.71,57.63l-6.15-.66-4.47-.55-8.03-.92-5.31-.69c.05-.69,0-1.2.05-1.83v-.41c.01-.17-.04-.25-.05-.96.06-7.36-4.98-15.92-13.14-17.5-6.76-1.3-13.71,1.43-18.16,6.84-2.94,3.57-4.98,7.86-5.78,12.49-.3,1.75-.21,3.72-.13,5.37-8.09,2.33-18.26,4.64-26.64,3.56-.3-4.27.08-8.21.94-12.33C12.76,31.17,23.49,14.61,43.55,10.72c4.64-.9,8.99-.89,13.69-.86,9.68.07,18.99,3.32,25.86,10.26,9.7,9.79,12.64,24.37,12.62,37.51Z"/>
                    </g>
                  </g>
                  <path class="cls-4" d="M221.31,46.42c-2.34,5.6-12.33,6.63-17.72,6.22-.48-5.58-.72-10.98-.64-16.69,4.01-1.19,8.05-1.18,12.08-.3,4.96,1.08,8.31,5.91,6.28,10.77Z"/>
                  <g>
                    <path class="cls-2" d="M241.47,58.51c-2.27,3.41-5.31,5.93-9.13,7.55l27.96,31.72c1.32,1.5,1.23,5.4-1.07,5.53-9.27.53-18.47.74-27.8.79-3.04-.43-4.86-3.38-6.55-5.58l-19.58-25.59,1.44,26.5c.07,1.36-.65,2.32-1.7,3.1-1.15.42-2.5.94-3.78.94l-15.3.03c-4.05,0-6.2-2.97-6.33-6.78l-1.41-41.84,4.45-.32,2.41-.18c2.76-.21,5.5-.14,8.28,0l6.26.32,6.54.3,8.22.66,7.81.73,5.37.58,8.63.99c1.84.21,3.56.56,5.28.55Z"/>
                    <path class="cls-3" d="M241.47,58.51c-1.72.02-3.44-.33-5.28-.55l-8.63-.99-5.37-.58-7.81-.73-8.22-.66-6.54-.3-6.26-.32c-2.78-.14-5.52-.21-8.28,0l-2.41.18-4.45.32-1.49-38.54c-.05-1.31,1.32-2.7,2.72-2.74l29.65-.78c4.31-.11,8.16.02,12.38.97,11.02,2.49,20.39,10.22,24.01,21.11,1.37,4.12.77,8.76.31,12.9s-2.2,7.52-4.32,10.7ZM221.31,46.42c2.03-4.86-1.32-9.69-6.28-10.77-4.03-.88-8.07-.89-12.08.3-.08,5.71.16,11.12.64,16.69,5.39.42,15.38-.62,17.72-6.22Z"/>
                  </g>
                </g>
                <g>
                  <g>
                    <path class="cls-1" d="M322.97,56.43l-11.6,46.73c-.36,1.43-1.68,1.99-2.94,1.96l-12.81-.38-13.87-.35c-2.76-.07-4.87-1.84-5.88-4.27l-11.2-27.11c-2.11-5.1-3.57-10.15-5.3-15.14,5.92.85,12.55,1.8,18.79,1.37l7.34-.5c1.11-.08,2.11-.3,2.99.06l3.09,10.12,3.15-9.25c3.49.94,13.41-.33,17.29-1.1l9-1.79c.71-.14,1.35-.28,1.94-.35Z"/>
                    <path class="cls-5" d="M288.5,58.79c-.89-.36-1.88-.13-2.99-.06l-7.34.5c-6.24.43-12.87-.52-18.79-1.37l-16.62-48.06c-.44-1.27.33-2.82,1.22-3.66h26.1c1.12-.01,2.28,1.11,2.86,1.92,1.4,1.96,1.97,4,2.65,6.34l12.91,44.38Z"/>
                    <path class="cls-5" d="M322.97,56.43c-.6.08-1.23.21-1.94.35l-9,1.79c-3.87.77-13.8,2.04-17.29,1.1,2.25-8.11,4.24-15.98,5.75-24.21l4.79-26.12c.28-1.54,1.75-3.95,3.47-3.89l22.26.74c1.85.06,3.01,1.81,2.61,3.59l-10.64,46.65Z"/>
                  </g>
                  <g>
                    <path class="cls-1" d="M364.04,57.02l.89,45.04c.03,1.4-.67,3.04-1.76,3.65l-19.1-.67c-1.76-.06-4.45-.51-5.16-2.47l-.34-13.32-.14-31.59,5.56-.15,8-.34,12.07-.14Z"/>
                    <path class="cls-5" d="M364.04,57.02l-12.07.14-8,.34-5.56.15-.41-36.48c-.01-1.03-.74-6.16,2.69-6.17l18.26-.05c1.14,0,2.33-.15,3.22.65.54.48,1.17,1.58,1.19,2.6l.67,38.82Z"/>
                  </g>
                  <g>
                    <path class="cls-1" d="M433.3,59.73c2.82,1.75,5.01,4.35,6.87,7,2.96,4.2,3.93,8.95,3.89,14-.07,9.47-5.07,17.75-13.65,21.85-3.57,1.71-7.23,2.68-11.22,3.37l-16.63-.08-17.3-.62-7.95-.34c-1.16-.05-3.74-.79-3.87-2.27-.27-3.09-.33-6.1-.38-9.22l-.26-16.36-.29-16.67c-.01-.71-.15-1.35.11-1.86.43.06.92.18,1.44.36,6.9,2.47,14.07,3.69,21.47,4.13l2.71.16c3.17.19,6.29.14,9.45-.01l3.3-.16c2.05-.1,3.96-.22,5.98-.5l7.1-.98c3.21-.44,6.25-1.12,9.21-1.81ZM419.76,77.45c.59-5.14-9.25-6.8-12.89-7.15l-3.28-.32c-1.83-.18-3.55-.27-5.36,0l.02,14.83c6.48.08,11.61.62,17.34-2.17,2.12-1.03,3.89-2.78,4.17-5.18Z"/>
                    <path class="cls-5" d="M433.3,59.73c-2.96.69-6,1.37-9.21,1.81l-7.1.98c-2.02.28-3.93.4-5.98.5l-3.3.16c-3.16.15-6.29.2-9.45.01l-2.71-.16c-7.4-.44-14.58-1.66-21.47-4.13-.52-.19-1.01-.31-1.44-.36l-.84-41.8c-.02-1.13.98-2.47,2.04-2.47l33.89.05c4.87,0,9.46,1.31,13.91,2.93,8.13,2.96,15.58,9.67,16.13,18.66.4,6.64-2.22,18.01-9.55,20.3,1.96,1.14,3.71,2.14,5.09,3.52ZM413.24,45.53c2.42-2.72,2.06-6.44-.57-8.61-4.17-3.44-11.65-3.54-16.74-3.22-.06,5.32.14,10.05.65,15.2,5.67-.14,12.53,1.28,16.67-3.37Z"/>
                    <path class="cls-4" d="M419.76,77.45c-.28,2.4-2.05,4.16-4.17,5.18-5.73,2.79-10.86,2.25-17.34,2.17l-.02-14.83c1.81-.26,3.53-.17,5.36,0l3.28.32c3.65.35,13.49,2.01,12.89,7.15Z"/>
                    <path class="cls-4" d="M413.24,45.53c-4.14,4.65-10.99,3.23-16.67,3.37-.51-5.16-.71-9.88-.65-15.2,5.09-.32,12.57-.22,16.74,3.22,2.64,2.18,2.99,5.9.57,8.61Z"/>
                  </g>
                  <g>
                    <path class="cls-1" d="M497.44,65.07l-.31,5.6c-.04.66-.41,1.58-.83,1.91-.48.37-1.4.46-2.02.42l-5.68-.38-4.46-.28-4.16-.29-4.13-.31-3.32-.22.05,14.12,35.86,3.09c2.58.22,3.98,2.5,3.61,4.76l-2.96,18.38c-.18,1.13-1.29,1.65-2.35,1.57l-19.03-1.54-16.07-1.49-20.31-2.17c-2.47-.26-3.91-1.87-3.79-4.34.09-2.08-.13-4.21-.16-6.31l-.22-16.71-.3-17.01c-.03-1.64-.31-3.21-.02-4.71l9.34,2.41,4.06,1,2.73.59,4.17.85c2.2.45,7.17,1.12,9.58,1.37l5.34.54c3.99.4,11.51-.02,15.38-.82Z"/>
                    <path class="cls-5" d="M497.44,65.07c-3.87.79-11.39,1.22-15.38.82l-5.34-.54c-2.41-.24-7.38-.92-9.58-1.37l-4.17-.85-2.73-.59-4.06-1-9.34-2.41-.73-41.88c-.02-1.2.88-2.13,2.12-2.13h53.95c1.21,0,2.45.96,2.37,2.06l-1.49,18.69c-.11,1.43-.54,2.58-2.28,2.49l-29.92-.74-.05,12.78,24.9.02c1.06.28,2.9.78,2.78,2.1l-1.06,12.54Z"/>
                  </g>
                </g>
              </svg>
          </div>

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
