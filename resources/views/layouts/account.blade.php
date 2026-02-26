<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page-title', 'We Offer Wellness')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Manrope:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --wow-green:#4A8878;
            --wow-green-hover:#3F7F71;
            --wow-green-press:#376F63;
            --bg:#ECEFF3;
            --card:#FFFFFF;
            --ink:#0B1220;
            --muted:rgba(11,18,32,.66);
            --muted2:rgba(11,18,32,.50);
            --border:rgba(16,24,40,.12);
            --border2:rgba(16,24,40,.10);
            --shadow: 0 24px 70px rgba(16,24,40,.14);
            --shadow2: 0 12px 28px rgba(16,24,40,.10);
            --radius-xl: 3px;
            --radius-lg: 3px;
            --radius-md: 3px;
            --font: 'Manrope', system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            --ui: 'Instrument Sans', system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            --focus: 0 0 0 4px rgba(74,136,120,.18);
            --max: 1120px;
            --pad: clamp(18px, 2.2vw, 32px);
        }
        * { box-sizing: border-box; }
        html, body { height: 100%; }
        body.account-auth-body {
            margin: 0;
            font-family: var(--font);
            color: var(--ink);
            background:
                radial-gradient(900px 700px at 16% 18%, rgba(74,136,120,.16), transparent 60%),
                radial-gradient(900px 700px at 86% 18%, rgba(11,18,32,.08), transparent 60%),
                linear-gradient(180deg, #F5F7FA, var(--bg));
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 26px;
            overflow-x: hidden;
        }
        body.account-auth-body::before {
            content:"";
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: .08;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='220' height='220'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='3'/%3E%3C/filter%3E%3Crect width='220' height='220' filter='url(%23n)' opacity='.40'/%3E%3C/svg%3E");
            mix-blend-mode: multiply;
        }
        .account-auth-window {
            width: min(var(--max), 100%);
            min-height: min(680px, calc(100vh - 52px));
            background: var(--card);
            border: 1px solid rgba(255,255,255,.65);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow);
            overflow: hidden;
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            isolation: isolate;
        }
        .account-auth-left {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: clamp(40px, 3.4vw, 76px);
            gap: 16px;
        }
        @media (max-width: 980px) {
            .account-auth-window { grid-template-columns: 1fr; }
            .account-auth-right { border-left: 0; border-top: 1px solid rgba(16,24,40,.08); }
        }
        @media (max-width: 767.98px) {
            .account-auth-right { display: none !important; }
        }
        .brand-mark { margin-bottom: 6px; user-select: none; }
        .auth-title { margin: 0; font-size: clamp(26px, 2.4vw, 34px); letter-spacing: -.02em; line-height: 1.12; }
        .auth-sub { margin: -6px 0 6px; color: var(--muted); font-size: 13px; max-width: 46ch; line-height: 1.5; font-family: var(--ui); }
        .account-auth-form { display: flex; flex-direction: column; gap: 12px; max-width: 420px; }
        .account-auth-field-group { display:flex; flex-direction:column; gap:6px; }
        .account-auth-field { position: relative; border-radius: 3px; border: 1px solid rgba(16,24,40,.14); background: #fff; box-shadow: 0 10px 18px rgba(16,24,40,.06); transition: box-shadow .15s ease, transform .15s ease, border-color .15s ease; }
        .account-auth-field:focus-within { border-color: rgba(74,136,120,.35); box-shadow: var(--focus), 0 12px 22px rgba(16,24,40,.08); transform: translateY(-1px); }
        .account-auth-label { display:block; font-family: var(--ui); font-size: 12px; color: rgba(11,18,32,.62); margin: 0 0 6px; }
        .account-auth-input { width:100%; border:0; outline:0; background:transparent; padding: 12px 42px 12px 12px; font-family: var(--ui); font-size: 14px; border-radius:3px; color: var(--ink); }
        .account-auth-toggle { position:absolute; right: 10px; top: 50%; transform: translateY(-50%); width: 30px; height: 30px; border-radius: 10px; border: 1px solid rgba(16,24,40,.10); background: rgba(255,255,255,.95); display:flex; align-items:center; justify-content:center; cursor:pointer; transition: transform .15s ease, background .15s ease; }
        .account-auth-toggle:hover { transform: translateY(-50%) scale(1.04); }
        .account-auth-toggle:active { transform: translateY(-50%) scale(.98); }
        .account-auth-row { display:flex; align-items:center; justify-content:space-between; gap: 12px; font-family: var(--ui); font-size: 13px; color: rgba(11,18,32,.78); margin-top: 2px; }
        .account-auth-row a { color: rgba(11,18,32,.78); text-decoration:none; }
        .account-auth-row a:hover { text-decoration:underline; }
        .account-auth-check { display:flex; align-items:center; gap:10px; user-select:none; }
        .account-auth-check input { width:18px; height:18px; accent-color: var(--wow-green); }
        .account-auth-btn { width:100%; border: 1px solid transparent; border-radius: 12px; padding: 12px 14px; font-family: var(--ui); font-weight: 700; font-size: 14px; cursor:pointer; transition: transform .15s ease, background .15s ease, box-shadow .15s ease; display:inline-flex; align-items:center; justify-content:center; gap: 10px; min-height: 46px; user-select:none; }
        .account-auth-btn.primary { background: var(--wow-green); color:#fff; box-shadow: 0 18px 30px rgba(74,136,120,.22); }
        .account-auth-btn.primary:hover { background: var(--wow-green-hover); transform: translateY(-1px); }
        .account-auth-btn.primary:active { background: var(--wow-green-press); transform: translateY(0); }
        .account-auth-btn.ghost { background:#fff; border-color: rgba(16,24,40,.16); color: rgba(11,18,32,.80); box-shadow: 0 12px 18px rgba(16,24,40,.06); }
        .account-auth-btn.ghost:hover { transform: translateY(-1px); }
        .account-auth-btn.ghost:active { transform: translateY(0); }
        .account-auth-inline { margin-top: 10px; font-family: var(--ui); font-size: 13px; color: rgba(11,18,32,.72); }
        .account-auth-inline a { color: var(--wow-green); text-decoration:none; font-weight:700; }
        .account-auth-inline a:hover { text-decoration:underline; }
        .account-auth-fine { margin-top: 10px; font-family: var(--ui); font-size: 12px; color: rgba(11,18,32,.56); line-height:1.45; }
        .account-auth-alert { display:none; border-radius: 12px; border: 1px solid rgba(217,45,32,.20); background: rgba(217,45,32,.08); color: rgba(160,28,19,.95); padding: 10px 12px; font-family: var(--ui); font-size: 13px; line-height:1.35; max-width: 420px; }
        .account-auth-alert.show { display:block; }
        .account-auth-btn.is-loading { pointer-events:none; opacity:.85; }
        .account-auth-btn .spinner { width: 16px; height: 16px; border-radius: 999px; border: 2px solid rgba(255,255,255,.45); border-top-color: rgba(255,255,255,1); display:none; animation: spin .75s linear infinite; }
        .account-auth-btn.is-loading .spinner { display:inline-block; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .account-auth-right { position:relative; border-left: 1px solid rgba(16,24,40,.08); background: linear-gradient(180deg, rgba(245,247,250,.92), rgba(245,247,250,.72)); padding: calc(var(--pad) + 18px) var(--pad) var(--pad); display:flex; flex-direction:column; justify-content:space-between; gap: 18px; overflow:hidden; }
        .account-auth-quote { max-width:46ch; margin-left:auto; text-align:left; font-family: var(--ui); }
        .account-auth-quote p { margin:0; color: rgba(11,18,32,.78); font-size: 13px; line-height:1.55; }
        .account-auth-quote b { display:block; margin-top: 10px; font-size: 12px; color: rgba(11,18,32,.70); }
        .account-auth-quote span { display:block; font-size: 12px; color: rgba(11,18,32,.52); margin-top: 2px; }
        .media { position:relative; border-radius: 18px; border: 1px solid rgba(16,24,40,.10); background: radial-gradient(900px 500px at 30% 10%, rgba(74,136,120,.16), transparent 60%), linear-gradient(180deg, #ffffff, rgba(255,255,255,.85)); box-shadow: var(--shadow2); overflow:hidden; aspect-ratio: 16/11; display:flex; align-items:center; justify-content:center; padding: 16px; }
        .mediaPlaceholder { width:100%; height:100%; border-radius: 14px; border: 1px dashed rgba(16,24,40,.18); background: linear-gradient(135deg, rgba(74,136,120,.10), rgba(74,136,120,0)), linear-gradient(180deg, rgba(11,18,32,.06), rgba(11,18,32,0)); display:flex; align-items:center; justify-content:center; text-align:center; padding: 18px; color: rgba(11,18,32,.62); font-family: var(--ui); font-size: 13px; line-height:1.45; }
        .review { position:absolute; left: 33px; bottom: 33px; width: min(360px, calc(100% - 36px)); border-radius: 16px; border: 1px solid rgba(16,24,40,.12); background: rgba(255,255,255,.86); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); box-shadow: 0 18px 34px rgba(16,24,40,.12); padding: 12px; display:flex; flex-direction:column; gap: 8px; transform: translateZ(0); }
        .reviewTop { display:flex; align-items:flex-start; justify-content:space-between; gap: 10px; }
        .stars { display:flex; gap: 4px; color: rgba(11,18,32,.82); letter-spacing: .06em; font-size: 12px; font-family: var(--ui); }
        .pill { font-family: var(--ui); font-size: 11px; font-weight: 700; padding: 6px 8px; border-radius: 999px; background: rgba(74,136,120,.10); border: 1px solid rgba(74,136,120,.18); color: rgba(11,18,32,.72); white-space:nowrap; }
        .reviewTitle { font-family: var(--ui); font-weight: 800; font-size: 13px; margin: 0; letter-spacing: -.01em; color: rgba(11,18,32,.90); }
        .reviewText { margin: 0; font-family: var(--ui); font-size: 12.5px; line-height: 1.45; color: rgba(11,18,32,.72); }
        .reviewMeta { display:flex; align-items:center; justify-content:space-between; gap: 10px; font-family: var(--ui); font-size: 12px; color: rgba(11,18,32,.55); margin-top: 2px; }
        .fadeOut{ animation: fadeOut .22s ease forwards; }
        .fadeIn{ animation: fadeIn .28s ease forwards; }
        .review { position:absolute; left: 33px; bottom: 33px; width: min(360px, calc(100% - 36px)); border-radius: 16px; border: 1px solid rgba(16,24,40,.12); background: rgba(255,255,255,.86); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); box-shadow: 0 18px 34px rgba(16,24,40,.12); padding: 12px; display:flex; flex-direction:column; gap: 8px; }
        .reviewTop { display:flex; align-items:flex-start; justify-content:space-between; gap: 10px; }
        .stars { display:flex; gap: 4px; color: rgba(11,18,32,.82); letter-spacing: .06em; font-size: 12px; font-family: var(--ui); }
        .pill { font-family: var(--ui); font-size: 11px; font-weight: 700; padding: 6px 8px; border-radius: 999px; background: rgba(74,136,120,.10); border: 1px solid rgba(74,136,120,.18); color: rgba(11,18,32,.72); white-space:nowrap; }
        .reviewTitle { font-family: var(--ui); font-weight: 800; font-size: 13px; margin: 0; letter-spacing: -.01em; color: rgba(11,18,32,.90); }
        .reviewText { margin: 0; font-family: var(--ui); font-size: 12.5px; line-height: 1.45; color: rgba(11,18,32,.72); }
        .reviewMeta { display:flex; align-items:center; justify-content:space-between; gap: 10px; font-family: var(--ui); font-size: 12px; color: rgba(11,18,32,.55); margin-top: 2px; }
        @media (prefers-reduced-motion: reduce){ *{ transition:none !important; animation:none !important; } }
        @keyframes fadeOut{ to{ opacity:0; transform: translateY(4px); } }
        @keyframes fadeIn{ from{ opacity:0; transform: translateY(4px);} to{ opacity:1; transform: translateY(0);} }
    </style>
    @stack('styles')
</head>
<body class="account-auth-body">
<main class="account-auth-window">
    <section class="account-auth-left" aria-label="Authentication form">
        <div class="brand-mark" aria-label="We Offer Wellness">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1240.46 141.78" height="32" aria-hidden="true">
                <defs><style>.cls-1-header {fill:#599d91}.cls-2-header {fill:#0b1220}</style></defs>
                <g><g>
                    <path class="cls-2-header" d="M483.94,63.07v57h-23.01v-56.97l-9.85.03c6.06,20.44.68,44.4-19.84,54.14-13.48,6.41-29.22,6.51-42.6-.12-14.21-7.04-21.27-21.46-21.22-37.09.04-15.11,6.2-29.32,19.58-36.92,21.57-12.27,53.7-6.68,63.84,19.24l.5-16.16,9.59-.25c-.06-9.84,1.82-19.92,9.35-26.56,8.55-7.54,20.13-8.84,31.69-5.75l-3.26,17.23c-4.69-1.25-9.36-1.92-12.18,1.8-2.66,3.51-2.84,8.57-2.6,13.35l23.92.03c-.5-15.95,6.15-31.21,22.9-34.29,6.32-1.16,12.4-.62,19.32.19l-.9,18.22c-5.2-.83-10.13-1.73-13.92,1.15-4.44,3.37-4.68,9.23-4.25,14.65l14.51.18.61,17.91c8.02-13.24,19.98-19.44,34.91-18.99,13.69.42,25.13,8.26,29.2,21.63,2.32,7.62,2.96,15.74,1.21,23.32l-47.37.06c2.16,17.94,28.17,14.65,41.19,11.13l3.05,15.32c-23.3,8.48-58.89,7.46-65.25-22.51-2.28-10.74-1.09-20.65,3.87-30.99l-15.99.03v56.98h-23v-56.99h-24Z"/>
                    <path class="cls-2-header" d="M277.89,39.19l-25.1,80.84-24.11.06c-4.85-17.24-9.08-33.79-13.07-51.45l-4.05,17.25-9.65,34.16h-24.17l-23.81-80.87,26.2.07,11.26,58.94,14.84-58.99,20.3-.19,14.28,57.08,11.95-57.05,25.12.15h.01Z"/>
                    <polygon class="cls-2-header" points="804.89 39.19 779.8 120.03 755.69 120.09 742.62 68.64 738.57 85.89 728.92 120.05 704.75 120.05 680.94 39.17 707.14 39.25 718.4 98.18 733.24 39.19 753.54 39.01 767.82 96.09 779.78 39.04 804.89 39.19"/>
                    <path class="cls-2-header" d="M985.84,66.82c-3.99-3.94-9.55-3.65-13.96-1.49-3.06,1.5-6.85,6.15-6.87,10.77l-.11,43.97h-22.94l-.42-73.87,19.55-.18,1.68,10.36c10.12-12.3,27.92-15.52,40.45-5.56,6.05,4.82,9.42,13.4,9.47,21.25l.28,48h-23.02l-.04-42.08c0-3.67-1.36-8.5-4.07-11.17h0Z"/>
                    <g><rect class="cls-2-header" x="908.93" y="12.07" width="23" height="108"/><rect class="cls-2-header" x="875.93" y="12.07" width="23" height="108"/></g>
                    <path class="cls-1-header" d="M78.77,44.93l-20.39,17.6-20.31-17.61c-5.11-4.43-9.26-9.29-14.08-15.15C27.9,22.42,51.39.46,58.1,0s29.62,21.65,34.78,29.36c-4.34,5.84-8.74,10.94-14.11,15.57h0Z"/>
                    <path class="cls-1-header" d="M56.4,141.78l-17.26-8.91C19.9,122.93-2.69,102.04.26,77.69l13.99,11.01c20.48,16.11,42.29,21.02,42.14,53.08h.01Z"/>
                    <path class="cls-1-header" d="M60.62,141.61c-.68-31.52,21.74-36.99,41.98-52.91l13.99-11c3.11,24.27-20.19,45.94-39.53,55.48-5.26,2.87-9.54,5.15-16.43,8.44h-.01Z"/>
                </g></g>
            </svg>
        </div>
        @hasSection('auth-heading')
            <h1 class="auth-title">@yield('auth-heading')</h1>
        @endif
        @hasSection('auth-subheading')
            <p class="auth-sub">@yield('auth-subheading')</p>
        @endif
        @yield('auth-alert')
        @yield('auth-form')
        @yield('auth-meta')
    </section>
    <aside class="account-auth-right" aria-label="Customer stories">
        <div class="account-auth-quote">
            <p>“The easiest way to book a proper reset — without the endless scrolling and decision fatigue.”</p>
            <b>Customers, basically</b>
            <span>Verified bookings • Real reviews</span>
        </div>
        <div class="media" aria-label="Preview image">
            <div class="mediaPlaceholder">
                <div>
                    <b style="display:block; font-weight:800; color:rgba(11,18,32,.85); margin-bottom:6px;">Drop your image here</b>
                    Replace this placeholder with a marketplace screenshot (right panel). Reviews will overlay automatically.
                </div>
            </div>
            <div class="review fadeIn" id="authReviewCard" aria-live="polite">
                <div class="reviewTop">
                    <div class="stars" id="authReviewStars">★★★★★</div>
                    <div class="pill">Verified booking</div>
                </div>
                <p class="reviewTitle" id="authReviewTitle">Genuinely effortless</p>
                <p class="reviewText" id="authReviewText">The filters actually helped. Booked an online session in under two minutes.</p>
                <div class="reviewMeta">
                    <span id="authReviewName">Hannah • Manchester</span>
                    <span id="authReviewWhen">Today</span>
                </div>
            </div>
        </div>
    </aside>
</main>
<script>
(function(){
  const card = document.getElementById('authReviewCard');
  if (!card) return;
  const starsEl = document.getElementById('authReviewStars');
  const titleEl = document.getElementById('authReviewTitle');
  const textEl = document.getElementById('authReviewText');
  const nameEl = document.getElementById('authReviewName');
  const whenEl = document.getElementById('authReviewWhen');
  const reviews = [
    { rating:5, title:'Genuinely effortless', text:'The filters actually helped. Booked an online session in under two minutes.', name:'Hannah', location:'Manchester', when:'Today' },
    { rating:5, title:'Premium experience', text:'Clean layout, clear pricing, and no weird surprises at checkout.', name:'James', location:'Kent', when:'1 day ago' },
    { rating:5, title:'Found the right thing fast', text:'I didn’t have to open 14 tabs to compare. That alone deserves 5 stars.', name:'Aisha', location:'London', when:'3 days ago' },
    { rating:4, title:'Loved the selection', text:'So many great options nearby — and the reviews helped me choose with confidence.', name:'Ella', location:'Bristol', when:'This week' },
    { rating:5, title:'Booked as a gift', text:'Bought for my partner and it went down extremely well. I look thoughtful now.', name:'Tom', location:'Leeds', when:'Last week' }
  ];
  const starString = (n) => Array.from({length:5}).map((_,i)=> i < n ? '★' : '☆').join('');
  const applyReview = (review) => {
    starsEl.textContent = starString(review.rating);
    titleEl.textContent = review.title;
    textEl.textContent = review.text;
    nameEl.textContent = `${review.name} • ${review.location}`;
    whenEl.textContent = review.when;
  };
  applyReview(reviews[0]);
  setInterval(() => {
    card.classList.remove('fadeIn');
    card.classList.add('fadeOut');
    setTimeout(() => {
      const next = reviews[Math.floor(Math.random() * reviews.length)];
      applyReview(next);
      card.classList.remove('fadeOut');
      card.classList.add('fadeIn');
    }, 220);
  }, 5200);
})();
</script>
@stack('scripts')
</body>
</html>
