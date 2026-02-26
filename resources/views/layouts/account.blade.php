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
        .brand-mark {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 6px;
            user-select: none;
        }
        .brand-pill {
            width: 34px;
            height: 34px;
            border-radius: 11px;
            background: rgba(74,136,120,.10);
            border: 1px solid rgba(74,136,120,.18);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .brand-mark b {
            font-weight: 800;
            letter-spacing: -.01em;
            font-size: 16px;
            line-height: 1.1;
        }
        .brand-mark b span { color: var(--wow-green); }
        .auth-title { margin: 0; font-size: clamp(26px, 2.4vw, 34px); letter-spacing: -.02em; line-height: 1.12; }
        .auth-sub { margin: -6px 0 6px; color: var(--muted); font-size: 13px; max-width: 46ch; line-height: 1.5; font-family: var(--ui); }
        .account-auth-form { display: flex; flex-direction: column; gap: 12px; max-width: 420px; }
        .account-auth-field-group { display:flex; flex-direction:column; gap:6px; }
        .account-auth-field { position: relative; border-radius: 12px; border: 1px solid rgba(16,24,40,.14); background: #fff; box-shadow: 0 10px 18px rgba(16,24,40,.06); transition: box-shadow .15s ease, transform .15s ease, border-color .15s ease; }
        .account-auth-field:focus-within { border-color: rgba(74,136,120,.35); box-shadow: var(--focus), 0 12px 22px rgba(16,24,40,.08); transform: translateY(-1px); }
        .account-auth-label { display:block; font-family: var(--ui); font-size: 12px; color: rgba(11,18,32,.62); margin: 0 0 6px; }
        .account-auth-input { width:100%; border:0; outline:0; background:transparent; padding: 12px 42px 12px 12px; font-family: var(--ui); font-size: 14px; color: var(--ink); }
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
        .review { position:absolute; left: 33px; bottom: 33px; width: min(360px, calc(100% - 36px)); border-radius: 16px; border: 1px solid rgba(16,24,40,.12); background: rgba(255,255,255,.86); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); box-shadow: 0 18px 34px rgba(16,24,40,.12); padding: 12px; display:flex; flex-direction:column; gap: 8px; }
        .reviewTop { display:flex; align-items:flex-start; justify-content:space-between; gap: 10px; }
        .stars { display:flex; gap: 4px; color: rgba(11,18,32,.82); letter-spacing: .06em; font-size: 12px; font-family: var(--ui); }
        .pill { font-family: var(--ui); font-size: 11px; font-weight: 700; padding: 6px 8px; border-radius: 999px; background: rgba(74,136,120,.10); border: 1px solid rgba(74,136,120,.18); color: rgba(11,18,32,.72); white-space:nowrap; }
        .reviewTitle { font-family: var(--ui); font-weight: 800; font-size: 13px; margin: 0; letter-spacing: -.01em; color: rgba(11,18,32,.90); }
        .reviewText { margin: 0; font-family: var(--ui); font-size: 12.5px; line-height: 1.45; color: rgba(11,18,32,.72); }
        .reviewMeta { display:flex; align-items:center; justify-content:space-between; gap: 10px; font-family: var(--ui); font-size: 12px; color: rgba(11,18,32,.55); margin-top: 2px; }
        @media (prefers-reduced-motion: reduce){ *{ transition:none !important; animation:none !important; } }
    </style>
    @stack('styles')
</head>
<body class="account-auth-body">
<main class="account-auth-window">
    <section class="account-auth-left" aria-label="Authentication form">
        <div class="brand-mark">
            <span class="brand-pill" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="18" height="18" fill="none">
                    <circle cx="16" cy="16" r="15" stroke="var(--wow-green)" stroke-width="2" fill="rgba(74,136,120,.08)"></circle>
                    <path d="M8 20l2.8-8h1.9l2.4 6.2 2.4-6.2h1.9l2.8 8h-2l-1.8-5.4-2.3 5.4h-1.2l-2.3-5.4L10 20H8z" fill="var(--wow-green)"></path>
                </svg>
            </span>
            <b>We Offer <span>Wellness</span></b>
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
                    <b style="display:block; font-weight:800; color:rgba(11,18,32,.85); margin-bottom:6px;">Showcase your experience</b>
                    Replace this placeholder with a marketplace screenshot to reinforce trust.
                </div>
            </div>
        </div>
        <div class="account-auth-fine" style="margin-top:-6px;">
            Tip: swap the image on the right to a WOW marketplace screenshot for instant “premium product” energy.
        </div>
    </aside>
</main>
@stack('scripts')
</body>
</html>
