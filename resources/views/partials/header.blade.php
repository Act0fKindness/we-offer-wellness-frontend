<style>
    button.md\:hidden.inline-flex.items-center.justify-center.p-2.rounded-md.text-ink-700.hover\:bg-ink-100 {
        border-radius: 40px !important;
    }
    /* GSAP-powered hamburger (mobile) */
    .hamburger{
        --before-scale: 0.6;
        --span-scale: 1;
        --after-scale: 0.6;
        --before-rot: 0deg;
        --after-rot: 0deg;
        --before-top: 0px;
        --after-top: 22px;
        --origin: right;
        border:none;
        background:none;
        width:40px;
        height:24px;
        position:relative;
        padding:0;
        color:#0b1320;
        cursor:pointer;
    }
    .hamburger:hover,
    .hamburger:focus,
    .hamburger:active{ background:none; box-shadow:none; }
    .hamburger:focus-visible{ outline:2px solid currentColor; outline-offset:3px; }
    .hamburger span,
    .hamburger::before,
    .hamburger::after{
        content:"";
        position:absolute;
        right:0;
        width:30px;
        height:2px;
        background: currentColor;
        border-radius:1px;
        transform-origin: var(--origin);
        pointer-events:none;
    }
    .hamburger span{ top:50%; transform: translateY(-50%) scaleX(var(--span-scale)); }
    .hamburger::before{
        top: var(--before-top);
        transform: scaleX(var(--before-scale)) rotate(var(--before-rot));
    }
    .hamburger::after{
        top: var(--after-top);
        transform: scaleX(var(--after-scale)) rotate(var(--after-rot));
    }
    /* hover state inherit existing bg hover */
    .utility-links__secondary .wow-practitioner-trigger{ border:none; background:rgba(16,91,75,.08); padding:6px 16px; border-radius:999px; font-weight:600; color:#0b1320; cursor:pointer; transition:background .2s ease, box-shadow .2s ease, color .2s ease; }
    .utility-links__secondary .wow-practitioner-trigger:hover{ background:#105b4b; color:#fff; box-shadow:0 10px 25px rgba(16,91,75,.25); }
    .utility-links__secondary .wow-practitioner-trigger:focus-visible{ outline:2px solid #105b4b; outline-offset:2px; }
    .practitioner-modal{ position:fixed; inset:0; display:none; align-items:center; justify-content:center; padding:20px; z-index:1300; }
    .practitioner-modal::backdrop{ background:rgba(11,19,32,.72); }
    .practitioner-modal.is-visible{ display:flex; }
    .practitioner-modal[aria-hidden="true"]{ pointer-events:none; }
    .practitioner-modal__backdrop{ position:absolute; inset:0; background:rgba(11,19,32,.72); backdrop-filter:blur(6px); }
    .practitioner-modal__panel{ position:relative; background:#fff; border-radius:3px; width:min(560px, 100%); max-height:96vh; overflow-y:auto; padding:32px; box-shadow:0 24px 70px rgba(11,19,32,.25); animation:practitionerModalFade .25s ease; }
    @keyframes practitionerModalFade{ from{ opacity:0; transform:translateY(12px); } to{ opacity:1; transform:translateY(0); } }
    .practitioner-modal__close{ position:absolute; top:16px; right:16px; border:none; background:#f1f5f9; width:36px; height:36px; border-radius:50%; font-size:20px; color:#0f172a; cursor:pointer; display:flex; align-items:center; justify-content:center; }
    .practitioner-modal__close:hover{ background:#e2e8f0; }
    .practitioner-modal__eyebrow{ font-size:12px; text-transform:uppercase; letter-spacing:.2em; color:#0f766e; font-weight:700; margin-bottom:8px; }
    .practitioner-modal__subtitle{ color:var(--ink-600); margin-top:8px; }
    .practitioner-form{ display:flex; flex-direction:column; gap:18px; margin-top:20px; }
    .practitioner-form .field-row{ display:grid; grid-template-columns:repeat(auto-fit, minmax(200px,1fr)); gap:16px; }
    .practitioner-form .field-group{ display:flex; flex-direction:column; }
    .practitioner-form label{ font-size:14px; font-weight:600; color:var(--ink-700); margin-bottom:6px; display:block; }
    .practitioner-form input[type="text"],
    .practitioner-form input[type="email"]{ width:100%; border:1px solid #d7dee7; border-radius:14px; padding:11px 14px; font-size:16px; transition:border-color .2s ease, box-shadow .2s ease; }
    .practitioner-form input:focus{ outline:none; border-color:#0f766e; box-shadow:0 0 0 3px rgba(15,118,110,.15); }
    .practice-mode{ display:flex; flex-wrap:wrap; gap:12px; }
    .practice-mode__option{ display:flex; align-items:center; gap:10px; border:1px solid transparent; border-radius:18px; padding:10px 16px; background:#f8fafc; font-weight:600; color:var(--ink-700); cursor:pointer; transition:all .2s ease; }
    .practice-mode__option input{ appearance:none; width:16px; height:16px; border:2px solid #0f766e; border-radius:4px; display:inline-block; position:relative; margin:0; flex-shrink:0; }
    .practice-mode__option span{ line-height:1; display:inline-block; }
    .practice-mode__option input:checked{ background:#0f766e; }
    .practice-mode__option input:checked::after{ content:""; position:absolute; inset:3px; background:#fff; border-radius:1px; }
    .practice-mode__option.is-active{ background:rgba(15,118,110,.1); border-color:#0f766e; color:#0b1320; }
    .practice-mode__legend{ font-weight:700; color:var(--ink-800); margin-bottom:8px; }
    .practitioner-form__hint{ font-size:13px; color:var(--ink-500); margin-top:4px; }
    .practitioner-form__message{ border-radius:14px; padding:12px 14px; font-weight:600; font-size:14px; display:none; }
    .practitioner-form__message.is-visible{ display:block; }
    .practitioner-form__message.is-success{ background:#ecfdf5; color:#047857; }
    .practitioner-form__message.is-error{ background:#fef2f2; color:#b91c1c; }
    .practitioner-form__submit{ width:100%; display:inline-flex; justify-content:center; }
    .practitioner-modal__panel h2{ font-size:28px; margin:0; color:var(--ink-900); }
    .practitioner-modal__panel p{ margin:0; }
    @media (max-width: 480px){
        .practice-mode{ flex-direction:column; }
        .practitioner-form .field-row{ grid-template-columns:1fr; }
    }
</style>
<style>
  /* When header becomes fixed after hitting top on desktop */
  @media (min-width: 992px){
    header.is-fixed{ position:fixed; top:0; left:0; right:0; }
  }
  /* (Removed hover animation for header mega state per request) */
</style>
@php
    $headerUser = auth()->user();
    $headerProfile = null;
    if ($headerUser) {
        $headerRawName = trim((string) $headerUser?->name);
        $headerFirst = trim($headerUser?->first_name ?: \Illuminate\Support\Str::of($headerRawName)->before(' '));
        $headerDerivedLast = '';
        if ($headerRawName && str_contains($headerRawName, ' ')) {
            $headerDerivedLast = trim(\Illuminate\Support\Str::of($headerRawName)->after(' '));
        }
        $headerLast = trim($headerUser?->last_name ?: $headerDerivedLast);
        $headerFullName = trim($headerFirst.' '.$headerLast) ?: ($headerRawName ?: 'Customer');
        $headerInitials = mb_strtoupper(mb_substr($headerFirst ?: $headerFullName, 0, 1).mb_substr($headerLast ?: '', 0, 1));
        $headerInitials = trim($headerInitials) !== '' ? $headerInitials : 'YOU';
        $headerProfile = [
            'full_name' => $headerFullName,
            'initials' => $headerInitials,
            'email' => $headerUser?->email,
        ];
    }
@endphp
<!-- Overlay shown behind header mega menu -->
<div id="mega-overlay" class="mega-overlay" style="display:none"></div>
<div class="pointer-events-none fixed inset-0 -z-10"></div>
        <div class="utility-bar hidden md:block">
            <div class="container-page">
                <div class="utility-links">
                    <div class="utility-links__primary"><a href="/reset" style="display:none">Free 7-Day Reset</a><a href="/about">About We
                        Offer Wellness®</a><a href="/help" style="display:none;">Help Centre</a><a href="/safety-and-contraindications">Safety
                        &amp; Contraindications</a></div>
                    <div class="utility-links__secondary"><a href="/for-business" style="display:none">For Business</a><button type="button" class="wow-practitioner-trigger" data-practitioner-trigger aria-haspopup="dialog" aria-controls="wowPractitionerModal">Become WOW Practitioner</button></div>
                </div>
            </div>
        </div>
        <div id="header-sentinel" style="position:relative;height:1px;width:1px"></div>
        <header class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b"
                style="border-bottom: 1px solid rgba(153, 153, 153, 0.4); margin-top: -1px;">
            <div class="container container-page header-inner h-16 flex items-center justify-between">
                <div class="flex items-center gap-4"><a class="flex items-center gap-2 shrink-0" href="/" aria-label="We Offer Wellness">
                    <!-- Inline SVG logo -->
                    <span class="block" style="height:28px; display:inline-flex; align-items:center">
                        <!-- BEGIN: WOW Logo -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1240.46 141.78" height="28" aria-hidden="true">
                          <defs><style>.cls-1-header {fill:#599d91}.cls-2-header {fill:#000}</style></defs>
                          <g><g>
                          <path class="cls-2-header " d="M483.94,63.07v57h-23.01v-56.97l-9.85.03c6.06,20.44.68,44.4-19.84,54.14-13.48,6.41-29.22,6.51-42.6-.12-14.21-7.04-21.27-21.46-21.22-37.09.04-15.11,6.2-29.32,19.58-36.92,21.57-12.27,53.7-6.68,63.84,19.24l.5-16.16,9.59-.25c-.06-9.84,1.82-19.92,9.35-26.56,8.55-7.54,20.13-8.84,31.69-5.75l-3.26,17.23c-4.69-1.25-9.36-1.92-12.18,1.8-2.66,3.51-2.84,8.57-2.6,13.35l23.92.03c-.5-15.95,6.15-31.21,22.9-34.29,6.32-1.16,12.4-.62,19.32.19l-.9,18.22c-5.2-.83-10.13-1.73-13.92,1.15-4.44,3.37-4.68,9.23-4.25,14.65l14.51.18.61,17.91c8.02-13.24,19.98-19.44,34.91-18.99,13.69.42,25.13,8.26,29.2,21.63,2.32,7.62,2.96,15.74,1.21,23.32l-47.37.06c2.16,17.94,28.17,14.65,41.19,11.13l3.05,15.32c-23.3,8.48-58.89,7.46-65.25-22.51-2.28-10.74-1.09-20.65,3.87-30.99l-15.99.03v56.98h-23v-56.99h-24ZM423.77,64.15c-2.54-5.71-7.68-8.95-13.3-8.86-5.3.08-10.43,2.89-13.03,8.24-4.67,9.63-4.76,21.35-.39,31.11,2.56,5.72,7.77,9.04,13.4,9.1,16.2.18,20.01-24.56,13.32-39.59ZM590.93,74.23c-.49-5.48-1.7-8.71-4.81-11.19-4.16-3.31-9.96-3.44-14.72-1.03-4,2.03-6.82,6.66-7.55,12.29l27.08-.07h0Z"/>
                          <path class="cls-2-header " d="M277.89,39.19l-25.1,80.84-24.11.06c-4.85-17.24-9.08-33.79-13.07-51.45l-4.05,17.25-9.65,34.16h-24.17l-23.81-80.87,26.2.07,11.26,58.94,14.84-58.99,20.3-.19,14.28,57.08,11.95-57.05,25.12.15h.01Z"/>
                          <polygon class="cls-2-header " points="804.89 39.19 779.8 120.03 755.69 120.09 742.62 68.64 738.57 85.89 728.92 120.05 704.75 120.05 680.94 39.17 707.14 39.25 718.4 98.18 733.24 39.19 753.54 39.01 767.82 96.09 779.78 39.04 804.89 39.19"/>
                          <path class="cls-2-header " d="M297.33,90.11c1.01,17.52,27.78,14.9,40.9,11.12l3.06,15.33c-12.65,4.55-25.92,5.84-39.07,3.16-24.39-4.96-32.05-30.29-24.66-51.02,5.46-15.3,19.31-23.88,35.39-23.65,13.57.2,25.14,7.16,29.74,20.16,2.78,7.87,3.43,16.08,1.93,24.82l-47.29.08h0ZM323.93,74.23c-.49-5.48-1.7-8.71-4.81-11.19-4.16-3.31-9.96-3.44-14.72-1.03-4,2.03-6.82,6.66-7.55,12.29l27.08-.07Z"/>
                          <path class="cls-2-header " d="M985.84,66.82c-3.99-3.94-9.55-3.65-13.96-1.49-3.06,1.5-6.85,6.15-6.87,10.77l-.11,43.97h-22.94l-.42-73.87,19.55-.18,1.68,10.36c10.12-12.3,27.92-15.52,40.45-5.56,6.05,4.82,9.42,13.4,9.47,21.25l.28,48h-23.02l-.04-42.08c0-3.67-1.36-8.5-4.07-11.17h0Z"/>
                          <path class="cls-2-header " d="M823.33,90.11c1.01,17.52,27.78,14.9,40.9,11.12l3.06,15.34c-12.65,4.55-25.92,5.84-39.07,3.16-24.38-4.97-32.05-30.26-24.65-51.02,5.45-15.3,19.31-23.88,35.38-23.65,13.57.2,25.15,7.16,29.74,20.16,2.78,7.87,3.43,16.08,1.93,24.82l-47.29.08h0ZM849.93,74.23c-.49-5.48-1.7-8.71-4.81-11.19-4.16-3.31-9.96-3.44-14.72-1.03-4,2.03-6.82,6.66-7.55,12.29l27.08-.07h0Z"/>
                          <g>
                          <path class="cls-2-header " d="M1131.76,117.42c-13.93,5.8-29.2,4.36-42.9-1.29l4.08-16.4c7.18,4.02,29.11,9.43,29.96-.17.21-2.4-1.77-5.18-4.17-6.13l-13.21-5.19c-5.74-2.26-11.52-6.86-13.63-12.78-4.08-11.44,2.69-23.03,13.82-27.47,11.74-4.69,24.95-3.65,36.3,1.38l-4.04,15.56c-6.42-2.98-23.07-7.25-24.96.92-.42,1.82,1.49,5.08,3.45,5.87l14.88,5.97c8.38,3.36,13.66,10.61,13.86,18.89.21,8.77-4.25,17.03-13.42,20.84h-.02Z"/>
                          <path class="cls-2-header " d="M1040.33,90.11c1.01,17.52,27.78,14.9,40.9,11.12l3.06,15.34c-12.65,4.55-25.92,5.84-39.07,3.16-24.38-4.96-32.05-30.28-24.66-51.02,5.45-15.3,19.31-23.88,35.39-23.65,13.57.2,25.15,7.16,29.74,20.16,2.78,7.87,3.43,16.08,1.93,24.82l-47.29.08h0ZM1066.93,74.23c-.49-5.48-1.71-8.71-4.81-11.19-4.16-3.31-9.96-3.44-14.72-1.03-4,2.03-6.82,6.66-7.55,12.29l27.08-.07h0Z"/>
                          <path class="cls-2-header " d="M1188.76,117.42c-13.93,5.8-29.2,4.36-42.9-1.29l4.08-16.4c7.18,4.02,29.11,9.43,29.96-.17.21-2.4-1.77-5.18-4.17-6.12l-13.21-5.19c-5.74-2.26-11.52-6.86-13.63-12.78-4.08-11.44,2.69-23.03,13.82-27.47,11.74-4.69,24.95-3.65,36.3,1.38l-4.05,15.56c-6.4-3-23.14-7.21-24.96.9-.43,1.91,1.45,5.08,3.45,5.88l14.87,5.97c8.38,3.36,13.66,10.6,13.86,18.89.21,8.77-4.25,17.02-13.42,20.84h0Z"/></g>
                          <g><rect class="cls-2-header " x="908.93" y="12.07" width="23" height="108"/><rect class="cls-2-header " x="875.93" y="12.07" width="23" height="108"/></g>
                          <path class="cls-2-header " d="M640.9,120.03l-22.94.05-.39-73.97,19.45-.04,1.19,14.28c4.45-10.27,13.04-17.02,25.15-14.92l.08,21.31c-3.87,0-6.43-.04-9.57.19-7.27.53-12.84,6.88-12.86,14.11l-.11,39h0Z"/>
                          <g><path class="cls-1-header " d="M78.77,44.93l-20.39,17.6-20.31-17.61c-5.11-4.43-9.26-9.29-14.08-15.15C27.9,22.42,51.39.46,58.1,0s29.62,21.65,34.78,29.36c-4.34,5.84-8.74,10.94-14.11,15.57h0Z"/>
                          <path class="cls-1-header " d="M56.4,141.78l-17.26-8.91C19.9,122.93-2.69,102.04.26,77.69l13.99,11.01c20.48,16.11,42.29,21.02,42.14,53.08h.01Z"/>
                          <path class="cls-1-header " d="M60.62,141.61c-.68-31.52,21.74-36.99,41.98-52.91l13.99-11c3.11,24.27-20.19,45.94-39.53,55.48-5.26,2.87-9.54,5.15-16.43,8.44h-.01Z"/>
                          <path class="cls-1-header " d="M55.01,113.27c-11.82-10.06-23.53-18.79-36.4-26.93-6.2-3.92-11.4-8.35-17.11-13.24.89-7.19,2.08-13.73,5.56-19.48l35.32,29.93c7.73,7.71,13.81,17.2,12.63,29.71h0Z"/>
                          <path class="cls-1-header " d="M95.85,87.93c-12.15,7.63-22.98,16.17-34.07,25.27-.99-12.69,5.13-22.39,13.22-30.11l35.02-29.61c3,6.16,4.79,12.74,5.19,19.56-6.08,5.67-12.4,10.51-19.36,14.88h0Z"/>
                          <path class="cls-1-header " d="M61.84,90.91c.3-17.28-3.8-21.71,7.79-32.02l27.89-24.79c2.95,4.5,5.67,9.03,8.74,14.41-8.52,10.17-17.85,18.5-28.28,26.77-5.86,4.65-10.38,9.91-16.14,15.62h0Z"/>
                          <path class="cls-1-header " d="M54.91,90.53c-5.71-5.3-10.39-10.83-16.58-15.7-10.12-7.97-18.85-16.06-27.65-26.02,2.66-5.25,5.52-10.06,8.71-14.7l27.84,24.7c3.47,3.08,6.29,6.96,8.24,11.07l-.55,20.65h0Z"/></g>
                          <g class="cls-2-header "><path d="M1224.34,24.61s.42.03.6.04c2.15.15,4.25.69,6.2,1.63,2.69,1.3,4.98,3.3,6.62,5.82s2.56,5.31,2.67,8.26l.02.68c0,.16,0,.31,0,.47l-.04.88c-.27,6.38-4.56,12.13-10.45,14.46-1.82.72-3.72,1.1-5.67,1.18-.43.02-.82.02-1.25,0-2.08-.08-4.13-.52-6.06-1.33-5.72-2.42-9.67-7.81-10.14-14.12-.01-.16-.03-.29-.03-.44v-.14s-.03-.04-.03-.03v-1.38c0-.14.04-.32.05-.48.42-6.36,4.42-11.76,10.19-14.15,1.78-.74,3.65-1.16,5.56-1.29.22-.01.41,0,.6-.04h1.15ZM1236,44.14c.46-2.38.32-4.84-.4-7.15-.51-1.64-1.33-3.16-2.4-4.47-2.19-2.7-5.37-4.31-8.8-4.53s-6.96,1.06-9.49,3.59c-1.63,1.64-2.82,3.7-3.43,5.97-.68,2.54-.65,5.24.09,7.77,1.33,4.53,4.89,7.98,9.46,8.96.97.21,1.94.29,2.94.29,6.06,0,10.89-4.5,12.03-10.44Z"/>
                          <path d="M1230.43,46.15c.27,1.29.54,2.53,1.13,3.75h-3.99c-.34-.4-.64-1.41-.77-1.93l-.53-2.18c-.08-.35-.16-.67-.31-.99-.29-.6-.77-1.03-1.39-1.26-.47-.16-.94-.25-1.44-.25h-2.04s0,6.6,0,6.6h-3.79s0-16.66,0-16.66l2.85-.35c1.53-.19,3.04-.2,4.57-.14,1.74.1,3.38.35,4.75,1.52,1.03.88,1.47,2.22,1.4,3.57-.1,1.95-1.67,3.17-3.37,3.72-.01.07-.01.15,0,.21,1.94.6,2.56,2.54,2.93,4.36ZM1226.77,38c-.06-1.34-.99-2.04-2.2-2.29-1.01-.2-2.4-.14-3.39.09v4.77s1.95,0,1.95,0c1.58,0,3.73-.54,3.64-2.56Z"/></g>
                          </g></g>
                        </svg>
                        <!-- END: WOW Logo -->
                    </span>
                </a>
                    <nav class="hidden md:flex items-center gap-1">
                        <div class="nav-item"><a class="link-wow--nav" data-mega-menu="need" tabindex="0" href="/needs">By Need</a></div>
                        <div class="nav-item"><a class="link-wow--nav" data-mega-menu="therapies" ex="0" href="/therapies">Therapies</a>
                        </div>
                        <div class="nav-item"><a class="link-wow--nav" tabindex="0" href="/classes">Classes</a></div>
                        <div class="nav-item"><a class="link-wow--nav" data-mega-menu="events" tabindex="0" href="/events-workshops">Events
                            &amp; Workshops</a></div>
                        <div class="nav-item"><a class="link-wow--nav" tabindex="0" href="/online-near-me">Online &amp; Near
                            Me</a></div>
                        <div class="nav-item"><a class="link-wow--nav" tabindex="0" href="/mindful-times">Mindful
                            Times</a></div>
                    </nav>
                </div>
                <div class="hidden md:flex items-center gap-2 position-relative">
                    <a class="icon-btn" aria-label="Search" href="/search">
                        <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                             fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-width="2"
                              d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"></path>
                    </svg>
                    </a>
                    <div class="account-wrap">
                        @auth
                            @php
                                $headerFullName = $headerProfile['full_name'] ?? 'Customer';
                                $headerInitials = $headerProfile['initials'] ?? 'YOU';
                            @endphp
                            <button type="button" class="icon-btn account-trigger" aria-haspopup="true" aria-expanded="false">
                                <span class="account-trigger__avatar" aria-hidden="true">{{ $headerInitials }}</span>
                            </button>
                            <div class="account-dropdown" id="accountDropdown" hidden>
                                <div class="account-dropdown__header">
                                    <p class="account-name">{{ $headerFullName }}</p>
                                </div>
                                <div class="account-actions account-actions--authed">
                                    <div class="account-links-stack">
                                        <a class="account-link" href="{{ route('account.dashboard') }}">Overview</a>
                                        <a class="account-link" href="{{ route('account.orders') }}">Orders &amp; receipts</a>
                                        <a class="account-link" href="{{ route('profile.edit') }}">Profile &amp; contact</a>
                                    </div>
                                    <form method="POST" action="{{ route('logout') }}" class="accountdd-logout">
                                        @csrf
                                        <button type="submit" class="btn btn--primary accountdd-logout__btn">Log out</button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <button type="button" class="icon-btn account-trigger" aria-haspopup="true" aria-expanded="false">
                                <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0a8.949 8.949 0 0 0 4.951-1.488A3.987 3.987 0 0 0 13 16h-2a3.987 3.987 0 0 0-3.951 3.512A8.948 8.948 0 0 0 12 21Zm3-11a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                </svg>
                            </button>
                            <div class="account-dropdown" id="accountDropdown" hidden>
                                <p class="account-email">Sign in to manage bookings faster.</p>
                                <div class="account-actions">
                                    <div class="cartdd-foot accountdd-foot">
                                        <a class="btn visit-cart-btn" href="{{ route('login', ['redirect' => '/account']) }}">Log in</a>
                                        <a class="btn btn--primary checkout-btn" href="{{ route('register', ['redirect' => '/account']) }}">Sign up</a>
                                    </div>
                                    <a class="account-link" href="{{ route('password.request') }}">Forgot password?</a>
                                </div>
                            </div>
                        @endauth
                    </div>
                    <div class="cart-wrap position-relative">
                    <a class="icon-btn position-relative cart-link" aria-label="Open cart" href="/cart">
                        <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 4h1.5L9 16m0 0h8m-8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm-8.5-3h9.25L19 7H7.312"></path>
                        </svg>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill cart-badge bg-danger" style="display:none">0</span>
                    </a>
                    <div id="cart-dropdown" class="cart-dropdown2" hidden>
                        <div class="cartdd-head">
                          <div>Your cart <small id="cartCountLabel" style="font-weight:600;color:var(--ink-600);margin-left:6px"></small></div>
                          <small id="freeShipHint" style="font-weight:600;color:var(--ink-600)"></small>
                        </div>
                        <div class="cartdd-body" id="cartdd-body">
                            <div class="cartdd-empty">Your cart is empty</div>
                        </div>
                        <div class="cartdd-subtotal"><span>Subtotal</span><strong id="cartdd-subtotal">£0.00</strong></div>
                        <div class="cartdd-upsell-section" style="padding:10px 12px 0">
                          <div class="cartdd-upsell-head" id="cartdd-upsell-headline">Complete your calm</div>
                          <div class="cartdd-upsell" id="cartdd-upsell"></div>
                        </div>
                        <div class="cartdd-foot">
                          <a href="/cart" class="btn visit-cart-btn">Visit cart</a>
                          <a href="/checkout" class="btn btn--primary checkout-btn">Checkout</a>
                        </div>
                    </div>
                    </div>
                </div><!---->
                <div class="flex items-center gap-3 md:hidden">
                    <a class="icon-btn position-relative cart-link" aria-label="View cart" href="/cart">
                        <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 4h1.5L9 16m0 0h8m-8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm-8.5-3h9.25L19 7H7.312"></path>
                        </svg>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill cart-badge bg-danger" style="display:none">0</span>
                    </a>
                    <button
                        type="button"
                        class="inline-flex items-center justify-center p-2 rounded-md text-ink-700 hamburger"
                        aria-label="Toggle menu" aria-expanded="false">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
            </div><!----><!---->
            <!-- Static mega panel (desktop) -->
            <div id="mega-panel" class="mega-panel" style="display:none">
                <div class="container-page py-4">
                    <!-- By Need -->
                    <div data-menu="need" class="grid md:grid-cols-3 gap-6">
                        <div class="menu-col">
                            <div class="mega-kicker mb-2">How are you feeling?</div>
                            <ul class="list-unstyled m-0 p-0">
                                <li><a class="menu-link" href="/needs/stress-and-anxiety">Stress &amp; anxiety</a></li>
                                <li><a class="menu-link" href="/needs/sleep-issues">Trouble sleeping</a></li>
                                <li><a class="menu-link" href="/needs/low-mood-burnout">Low mood &amp; burnout</a></li>
                                <li><a class="menu-link" href="/needs/overwhelm">Overwhelmed &amp; frazzled</a></li>
                                <li><a class="menu-link" href="/needs/worry">Worry &amp; racing thoughts</a></li>
                            </ul>
                        </div>
                        <div class="menu-col">
                            <div class="mega-kicker mb-2">What do you want?</div>
                            <ul class="list-unstyled m-0 p-0">
                                <li><a class="menu-link" href="/needs/mens-health">Men’s health</a></li>
                                <li><a class="menu-link" href="/needs/digestive-health">Digestive &amp; gut health</a></li>
                                <li><a class="menu-link" href="/needs/fertility-pregnancy">Fertility &amp; pregnancy</a></li>
                                <li><a class="menu-link" href="/needs/pain-relief">Pain relief &amp; tension</a></li>
                                <li><a class="menu-link" href="/needs/trauma-and-nervous-system">Nervous system &amp; trauma support</a></li>
                            </ul>
                        </div>
                        <div class="menu-col">
                            <div class="mega-kicker mb-2">Help me choose</div>
                            <ul class="list-unstyled m-0 p-0">
                                <li><a class="menu-link" href="/help/which-therapy">Match me to a therapy (quiz)</a></li>
                                <li><a class="menu-link" href="/therapies?tag=gentle-beginner">Gentle &amp; beginner‑friendly options</a></li>
                                <li><a class="menu-link" href="/needs">View all needs</a></li>
                            </ul>
                        </div>
                    </div>
                    <!-- Therapies -->
                    <div data-menu="therapies" class="grid md:grid-cols-3 gap-6">
                        <div class="menu-col">
                            <div class="mega-kicker mb-2">Popular therapies</div>
                            <ul class="list-unstyled m-0 p-0">
                                <li><a class="menu-link" href="/therapy/massage">Massage therapy</a></li>
                                <li><a class="menu-link" href="/therapy/reiki">Reiki</a></li>
                                <li><a class="menu-link" href="/therapy/reflexology">Reflexology</a></li>
                                <li><a class="menu-link" href="/therapy/acupuncture">Acupuncture</a></li>
                                <li><a class="menu-link" href="/therapy/breathwork">Breathwork (1:1)</a></li>
                                <li><a class="menu-link" href="/therapy/hypnotherapy">Hypnotherapy</a></li>
                                <li><a class="menu-link" href="/therapy/coaching-and-counselling">Coaching &amp; counselling</a></li>
                            </ul>
                        </div>

                        <div class="menu-col">
                            <div class="mega-kicker mb-2">Guides</div>
                            <ul class="list-unstyled m-0 p-0">
                                <li><a class="menu-link" href="/mindful-times">Mindful Times</a></li>
                                <li><a class="menu-link" href="/help">Help centre</a></li>
                            </ul>
                        </div>
                    </div>
                    <!-- Events & Workshops -->
                    <div data-menu="events" class="grid md:grid-cols-3 gap-6">
                        <div class="menu-col">
                            <div class="mega-kicker mb-2">Highlights</div>
                            <ul class="list-unstyled m-0 p-0">
                                <li><a class="menu-link" href="/events-workshops">Upcoming events &amp; workshops</a></li>
                                <li><a class="menu-link" href="/classes">Classes</a></li>
                            </ul>
                        </div>
                        <div class="menu-col">
                            <div class="mega-kicker mb-2">Explore</div>
                            <ul class="list-unstyled m-0 p-0">
                                <li><a class="menu-link" href="/online-near-me">Online &amp; near me</a></li>
                                <li><a class="menu-link" href="/help">Help centre</a></li>
                            </ul>
                        </div>
                        <div class="menu-col">
                            <div class="mega-kicker mb-2">Guides</div>
                            <ul class="list-unstyled m-0 p-0">
                                <li><a class="menu-link" href="/mindful-times">Mindful Times</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
 </header>
<style>
/* Backdrop for mega menu (below header/utility, above content) */
.mega-overlay{
    position: fixed; inset: 0;
    background: rgba(27,99,168,.12);
    -webkit-backdrop-filter: blur(10px) saturate(1.2);
    backdrop-filter: blur(10px) saturate(1.2);
    z-index: 35; /* below header (40) and utility-bar (41), above content/search (30) */
    pointer-events: none;
}
/* Utility bar: scrolls normally (header overlays it on desktop) */
.utility-bar{ position: relative; z-index: 45; background: rgba(255,255,255,.98); backdrop-filter: blur(8px); }
/* Cart dropdown (desktop hover) */
.cart-wrap{ position: relative; }
.cart-dropdown2{ position:absolute; right:0; top:calc(100% + 10px); width: min(380px, 92vw); background:#fff; border:1px solid rgba(0,0,0,0.15); border-radius:3px; box-shadow: 0 24px 60px rgba(16,24,40,.16); overflow:hidden; z-index: 45; }
.cartdd-head{ padding:10px 14px; font-weight:700; background: linear-gradient(180deg,#fff,#f8fafc); border-bottom:1px solid #eef2f7 }
.cartdd-body{ max-height: 380px; overflow:auto }
.cartdd-empty{ padding:18px; color: var(--ink-600); text-align:center }
.cartdd-item{ display:flex; gap:10px; align-items:center; padding:12px 14px; padding-right:60px; border-bottom:1px solid #f1f5f9; position:relative; }
.cartdd-item:last-child{ border-bottom:0 }
.cartdd-img{ width:54px; height:54px; border-radius:10px; overflow:hidden; border:1px solid #eceff3; background:#fafafa }
.cartdd-img img{ width:100%; height:100%; object-fit:cover; display:block }
.cartdd-info{ flex:1 1 auto; min-width:0 }
.cartdd-title{ display:block; max-width:100%; font-weight:600; color:#0b1323; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; text-decoration:none }
.cartdd-meta{ font-size:.9rem; color:#64748b }
.cartdd-amt{ font-weight:700; color:#0b1323; white-space:nowrap; flex:0 0 auto }
.cartdd-remove{ position:absolute; top:50%; right:14px; width:34px; height:34px; border-radius:50%; border:0; background:#dc2626; color:#fff; display:flex; align-items:center; justify-content:center; opacity:0; transform:translate(10px,-50%); transition:opacity .2s ease, transform .2s ease; cursor:pointer; pointer-events:none; }
.cartdd-remove svg{ width:18px; height:18px; }
.cartdd-item:hover .cartdd-remove,
.cartdd-remove:focus-visible{ opacity:1; transform:translate(0,-50%); pointer-events:auto; }
.cartdd-remove:focus-visible{ outline:2px solid #fff; outline-offset:2px; }
.cartdd-subtotal{ padding:10px 14px; display:flex; align-items:center; justify-content:space-between; border-top:1px solid #eef2f7; border-bottom:1px solid #eef2f7; background:#fff }
.cartdd-subtotal span{ font-size:12px; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:.02em }
.cartdd-upsell .upsell-item{ display:grid; grid-template-columns:46px 1fr auto; gap:10px; align-items:center; padding:8px 10px; border:1px solid #eef2f7; border-radius:10px; background:#fff; margin-bottom:8px }
.cartdd-upsell .upsell-item img{ width:46px; height:46px; object-fit:cover; border-radius:8px; border:1px solid #eceff3 }
.cartdd-upsell .upsell-title{ margin:0; font-size:13px; font-weight:700; color:#0b1323; line-height:1.25; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden }
.cartdd-upsell .upsell-price{ font-size:12px; color:#64748b; font-weight:700; margin-top:4px }
.cartdd-upsell-head{ font-weight:800; letter-spacing:-.01em; margin:0 0 8px; color:#0b1323 }
.cartdd-foot{ display:flex; gap:10px; align-items:center; justify-content:space-between; background:#fff; padding:10px 12px 14px }
/* Buttons styled like product card actions */
.cart-dropdown2 .btn,
.account-dropdown .btn{ height:38px; border-radius:4px; font-size:16px; font-weight:400; border:1px solid rgba(16,24,40,.22); background:#fff !important; color: rgba(11,18,32,.82); cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 10px 22px rgba(16,24,40,.08); text-decoration:none; flex:1 1 auto }
.cart-dropdown2 .btn--primary,
.account-dropdown .btn--primary{ border-color: rgba(0,0,0,.10); color:#fff; background:#549483 !important }
.accountdd-logout{ margin-top:15px; }
.accountdd-logout__btn{ width:100%; }
#visit-link{ font-weight:700; color:#2c6bed; text-decoration:none }
#visit-link:hover{ text-decoration:underline }
.account-links-stack{ display:flex; flex-direction:column; gap:6px; margin-top:4px; }
.account-links-stack .account-link{ font-weight:700; color:var(--ink-800); font-size:14px; }
.accountdd-logout{ margin-top:15px; }
.accountdd-logout__btn{ width:100%; }
.mobile-menu__account{ margin-top:16px; padding-top:16px; border-top:1px solid var(--ink-200); display:flex; flex-direction:column; gap:12px; }
.mobile-account-card{ display:flex; align-items:center; gap:12px; padding:12px; border-radius:14px; border:1px solid rgba(16,24,40,.08); background:#f8fafc; }
.mobile-account-card--guest{ flex-direction:column; align-items:flex-start; }
.mobile-account-avatar{ width:44px; height:44px; border-radius:50%; background:#105b4b; color:#fff; font-weight:700; display:flex; align-items:center; justify-content:center; }
.mobile-account-name{ margin:0; font-size:15px; font-weight:700; color:var(--ink-900); }
.mobile-account-email{ display:block; font-size:13px; color:var(--ink-600); }
.mobile-account-manage{ font-size:13px; font-weight:600; color:var(--wow-green); text-decoration:none; }
.mobile-account-manage:hover{ text-decoration:underline; }
.mobile-account-links{ list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:6px; }
.mobile-account-links a{ display:block; padding:10px 12px; border-radius:10px; background:#f5f7fa; text-decoration:none; color:var(--ink-800); font-weight:600; }
.mobile-account-links a:hover{ background:#e8eef7; color:var(--ink-900); }
.mobile-menu__link--button{ display:block; width:100%; text-align:left; border:none; background:none; padding:10px 12px; border-radius:10px; font-weight:600; color:var(--ink-800); cursor:pointer; }
.mobile-menu__link--button:hover{ background: var(--ink-100); color: var(--ink-900); }
.mobile-menu__link--button:focus-visible{ outline:2px solid currentColor; outline-offset:2px; }
.mobile-account-buttons{ display:flex; flex-direction:column; gap:10px; }
.mobile-account-logout button{ width:100%; }
.mobile-account-guest-title{ margin:0; font-weight:800; letter-spacing:-.01em; color:var(--ink-900); }
.mobile-account-guest-text{ margin:4px 0 0; color:var(--ink-700); font-size:13px; }
</style>
        <!-- Mobile menu (drawer) -->
        <div id="mobile-menu" class="mobile-menu" style="display:none">
            <nav class="mobile-menu__nav">
                <ul class="mobile-menu__list">
                    <li><a class="mobile-menu__link" href="/needs">By Need</a></li>
                    <li><a class="mobile-menu__link" href="/therapies">Therapies</a></li>
                    <li><a class="mobile-menu__link" href="/classes">Classes</a></li>
                    <li><a class="mobile-menu__link" href="/events-workshops">Events &amp; Workshops</a></li>
                    <li><a class="mobile-menu__link" href="/online-near-me">Online &amp; Near Me</a></li>
                    <li><a class="mobile-menu__link" href="/mindful-times">Mindful Times</a></li>
                </ul>
                <div class="mobile-menu__section">
                    <div class="mobile-menu__section-title">Help &amp; Info</div>
                    <ul class="mobile-menu__list">
                        <li><a class="mobile-menu__link" href="/about">About We Offer Wellness®</a></li>
                        <li><a class="mobile-menu__link" href="/safety-and-contraindications">Safety &amp; Contraindications</a></li>
                        <li>
                            <button type="button" class="mobile-menu__link mobile-menu__link--button wow-practitioner-trigger" data-practitioner-trigger aria-haspopup="dialog" aria-controls="wowPractitionerModal">Become WOW Practitioner</button>
                        </li>
                    </ul>
                </div>
                <div class="mobile-menu__account">
                    @auth
                        @php
                            $mobileFullName = $headerProfile['full_name'] ?? 'Customer';
                            $mobileInitials = $headerProfile['initials'] ?? 'YOU';
                        @endphp
                        <div class="mobile-account-card">
                            <span class="mobile-account-avatar" aria-hidden="true">{{ $mobileInitials }}</span>
                            <div class="mobile-account-copy">
                                <p class="mobile-account-name">{{ $mobileFullName }}</p>
                                @if(!empty($headerProfile['email']))
                                    <span class="mobile-account-email">{{ $headerProfile['email'] }}</span>
                                @endif
                                <a class="mobile-account-manage" href="{{ route('account.dashboard') }}">View account</a>
                            </div>
                        </div>
                        <ul class="mobile-account-links">
                            <li><a href="{{ route('account.dashboard') }}">Overview</a></li>
                            <li><a href="{{ route('account.orders') }}">Orders &amp; receipts</a></li>
                            <li><a href="{{ route('profile.edit') }}">Profile &amp; contact</a></li>
                        </ul>
                        <form method="POST" action="{{ route('logout') }}" class="mobile-account-logout">
                            @csrf
                            <button type="submit" class="btn btn--primary">Log out</button>
                        </form>
                    @else
                        <div class="mobile-account-card mobile-account-card--guest">
                            <p class="mobile-account-guest-title">Account</p>
                            <p class="mobile-account-guest-text">Save favourites, manage bookings, and checkout faster.</p>
                        </div>
                        <div class="mobile-account-buttons">
                            <a class="btn" href="{{ route('login', ['redirect' => '/account']) }}">Log in</a>
                            <a class="btn btn--primary" href="{{ route('register', ['redirect' => '/account']) }}">Create account</a>
                        </div>
                    @endauth
                </div>
            </nav>
        </div>

        <div id="wowPractitionerModal" class="practitioner-modal" aria-hidden="true">
            <div class="practitioner-modal__backdrop" data-practitioner-dismiss></div>
            <div class="practitioner-modal__panel" role="dialog" aria-modal="true" aria-labelledby="wowPractitionerTitle" tabindex="-1">
                <button type="button" class="practitioner-modal__close" data-practitioner-dismiss aria-label="Close form">&times;</button>
                <p class="practitioner-modal__eyebrow">For practitioners</p>
                <h2 id="wowPractitionerTitle">Become a WOW Practitioner</h2>
                <p class="practitioner-modal__subtitle">Share a few details so we can keep you updated on onboarding windows, perks and support.</p>
                <form id="wowPractitionerForm" class="practitioner-form" novalidate>
                    <div class="field-row">
                        <div class="field-group">
                            <label for="wowPractitionerFirst">First name</label>
                            <input id="wowPractitionerFirst" name="first_name" type="text" autocomplete="given-name" required>
                        </div>
                        <div class="field-group">
                            <label for="wowPractitionerLast">Last name</label>
                            <input id="wowPractitionerLast" name="last_name" type="text" autocomplete="family-name" required>
                        </div>
                    </div>
                    <div class="field-group">
                        <label for="wowPractitionerEmail">Email</label>
                        <input id="wowPractitionerEmail" name="email" type="email" autocomplete="email" required>
                    </div>
                    <div class="field-group">
                        <label for="wowPractitionerBusiness">Business name</label>
                        <input id="wowPractitionerBusiness" name="business_name" type="text" required placeholder="Enter your solo practice or business name">
                        <p class="practitioner-form__hint">We'll use this as your business name on WOW.</p>
                    </div>
                    <div class="field-group">
                        <div class="practice-mode__legend">How do you currently hold sessions?</div>
                        <div class="practice-mode">
                            <label class="practice-mode__option">
                                <input type="checkbox" name="practice_online" value="1">
                                <span>Online</span>
                            </label>
                            <label class="practice-mode__option">
                                <input type="checkbox" name="practice_in_person" value="1">
                                <span>In-person</span>
                            </label>
                        </div>
                    </div>
                    <div class="field-group" id="wowPractitionerLocationGroup" hidden>
                        <label for="wowPractitionerLocation">Where do you host in-person? (general area)</label>
                        <input id="wowPractitionerLocation" name="in_person_locations" type="text" placeholder="e.g. Bristol, East Sussex or Hampstead">
                        <p class="practitioner-form__hint">Just provide the general location like county or town/city. We will not share this with anyone.</p>
                    </div>
                    <div id="wowPractitionerMessage" class="practitioner-form__message" role="alert" aria-live="polite" hidden></div>
                    <button type="submit" class="btn-wow btn-wow--cta btn-arrow practitioner-form__submit" data-loader-init="1">
                        <span class="btn-label">Send details</span>
                        <span class="btn-spinner" aria-hidden="true"><span class="spin"></span></span>
                    </button>
                </form>
            </div>
        </div>

        <script>
            (function(){
                if (typeof window === 'undefined') return;
                const modal = document.getElementById('wowPractitionerModal');
                const form = document.getElementById('wowPractitionerForm');
                if (!modal || !form) return;
                const triggers = document.querySelectorAll('[data-practitioner-trigger]');
                if (!triggers.length) return;
                const closeTargets = modal.querySelectorAll('[data-practitioner-dismiss]');
                const locationGroup = document.getElementById('wowPractitionerLocationGroup');
                const locationInput = document.getElementById('wowPractitionerLocation');
                const inPersonCheckbox = form.querySelector('input[name="practice_in_person"]');
                const onlineCheckbox = form.querySelector('input[name="practice_online"]');
                const messageEl = document.getElementById('wowPractitionerMessage');
                const submitBtn = form.querySelector('button[type="submit"]');
                const body = document.body;
                const sessionTokenKey = 'wow_subscriber_session_token';
                const sessionStartKey = 'wow_subscriber_session_started_at';
                const overlay = modal.querySelector('.practitioner-modal__backdrop');
                let sessionStart = loadNumber(sessionStartKey);
                if (!sessionStart){
                    sessionStart = Date.now();
                    storeNumber(sessionStartKey, sessionStart);
                }

                const toggleLocation = () => {
                    if (!locationGroup) return;
                    const show = inPersonCheckbox?.checked;
                    locationGroup.hidden = !show;
                    if (!show && locationInput){
                        locationInput.value = '';
                    }
                };

                const updateModeClasses = () => {
                    form.querySelectorAll('.practice-mode__option').forEach(option => {
                        const input = option.querySelector('input[type="checkbox"]');
                        option.classList.toggle('is-active', !!input?.checked);
                    });
                };

                const openModal = () => {
                    modal.classList.add('is-visible');
                    modal.setAttribute('aria-hidden', 'false');
                    body.style.overflow = 'hidden';
                    setTimeout(() => {
                        const firstInput = form.querySelector('input[name="first_name"]');
                        firstInput?.focus();
                    }, 10);
                };

                const closeModal = () => {
                    modal.classList.remove('is-visible');
                    modal.setAttribute('aria-hidden', 'true');
                    body.style.overflow = '';
                    clearMessage();
                };

                const clearMessage = () => {
                    if (!messageEl) return;
                    messageEl.textContent = '';
                    messageEl.classList.remove('is-visible', 'is-success', 'is-error');
                    messageEl.hidden = true;
                };

                const showMessage = (text, isSuccess) => {
                    if (!messageEl) return;
                    messageEl.textContent = text;
                    messageEl.classList.add('is-visible');
                    messageEl.hidden = false;
                    messageEl.classList.toggle('is-success', !!isSuccess);
                    messageEl.classList.toggle('is-error', !isSuccess);
                };

                const loadToken = () => {
                    try { return sessionStorage.getItem(sessionTokenKey); } catch (_) { return null; }
                };

                const storeToken = (token) => {
                    if (!token) return;
                    try { sessionStorage.setItem(sessionTokenKey, token); } catch (_) {}
                };

                const collectMeta = () => {
                    const nav = typeof navigator !== 'undefined' ? navigator : {};
                    const scr = typeof window !== 'undefined' ? (window.screen || {}) : {};
                    let timezone = null;
                    try { timezone = Intl.DateTimeFormat().resolvedOptions().timeZone; } catch (_) {}
                    const languages = Array.isArray(nav.languages) ? nav.languages.join(',') : (nav.language || null);
                    return {
                        timezone,
                        locale: nav.language || null,
                        languages,
                        platform: nav.platform || null,
                        user_agent: nav.userAgent || null,
                        device_memory: typeof nav.deviceMemory === 'number' ? String(nav.deviceMemory) : null,
                        hardware_concurrency: typeof nav.hardwareConcurrency === 'number' ? String(nav.hardwareConcurrency) : null,
                        screen_width: scr.width || null,
                        screen_height: scr.height || null,
                    };
                };

                const durationSeconds = () => {
                    return Math.max(0, Math.round((Date.now() - (sessionStart || Date.now())) / 1000));
                };

                triggers.forEach(btn => {
                    btn.addEventListener('click', (event) => {
                        event.preventDefault();
                        clearMessage();
                        openModal();
                    });
                });

                closeTargets.forEach(btn => btn.addEventListener('click', closeModal));
                overlay?.addEventListener('click', closeModal);

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && modal.classList.contains('is-visible')) {
                        closeModal();
                    }
                });

                inPersonCheckbox?.addEventListener('change', () => {
                    toggleLocation();
                    updateModeClasses();
                });
                form.querySelectorAll('.practice-mode__option input').forEach(input => {
                    input.addEventListener('change', updateModeClasses);
                });
                toggleLocation();
                updateModeClasses();

                form.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    clearMessage();
                    if (!form.reportValidity()) return;
                    const formData = new FormData(form);
                    const online = !!onlineCheckbox?.checked;
                    const inPerson = !!inPersonCheckbox?.checked;
                    if (!online && !inPerson) {
                        showMessage('Select online, in-person or both.', false);
                        return;
                    }
                    let locationValue = '';
                    if (inPerson && locationInput) {
                        locationValue = locationInput.value.trim();
                        if (!locationValue) {
                            showMessage('Share a general in-person location.', false);
                            locationInput.focus();
                            return;
                        }
                    }

                    const firstName = (formData.get('first_name') || '').toString().trim();
                    const lastName = (formData.get('last_name') || '').toString().trim();
                    const email = (formData.get('email') || '').toString().trim();
                    const business = (formData.get('business_name') || '').toString().trim();
                    const name = [firstName, lastName].filter(Boolean).join(' ').trim();
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    const payload = Object.assign({
                        practitioner_interest: true,
                        first_name: firstName,
                        last_name: lastName,
                        name: name || null,
                        email,
                        business_name: business || null,
                        offers_online: online,
                        offers_in_person: inPerson,
                        in_person_locations: inPerson ? locationValue : null,
                        landing_path: 'header:become-wow-practitioner',
                        referrer: document.referrer ? document.referrer.substring(0, 2048) : null,
                        session_token: loadToken(),
                        session_started_at: new Date(sessionStart || Date.now()).toISOString(),
                        session_duration_seconds: durationSeconds(),
                    }, collectMeta());

                    submitBtn.disabled = true;
                    submitBtn.classList.add('is-loading');
                    submitBtn.setAttribute('aria-busy', 'true');
                    try {
                        const response = await fetch('/api/v3-subscribers', {
                            method: 'POST',
                            headers: Object.assign({
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            }, csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
                            credentials: 'same-origin',
                            body: JSON.stringify(payload),
                        });
                        const bodyJson = await response.json().catch(() => ({}));
                        if (!response.ok || bodyJson.error) {
                            const message = extractError(bodyJson) || 'Something went wrong. Please try again in a moment.';
                            throw new Error(message);
                        }
                        if (bodyJson.session_token) {
                            storeToken(bodyJson.session_token);
                        }
                        form.reset();
                        toggleLocation();
                        updateModeClasses();
                        showMessage('Thank you - we will be in touch very soon.', true);
                    } catch (error) {
                        showMessage(error.message || 'Something went wrong. Please try again.', false);
                    } finally {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('is-loading');
                        submitBtn.removeAttribute('aria-busy');
                    }
                });

                function extractError(body){
                    if (!body) return null;
                    if (body.errors) {
                        const first = Object.values(body.errors)[0];
                        if (Array.isArray(first) && first.length) {
                            return first[0];
                        }
                    }
                    return body.error || null;
                }

                function loadNumber(key){
                    try {
                        const raw = sessionStorage.getItem(key);
                        return raw ? Number(raw) : null;
                    } catch (_) {
                        return null;
                    }
                }

                function storeNumber(key, value){
                    try { sessionStorage.setItem(key, String(value)); } catch (_) {}
                }
            })();
        </script>
