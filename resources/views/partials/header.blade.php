<style>
    button.md\:hidden.inline-flex.items-center.justify-center.p-2.rounded-md.text-ink-700.hover\:bg-ink-100 {
        border-radius: 40px !important;
    }
    /* Burger menu button (mobile) */
    .burgermenu{ border:none; background:none; width:34px; height:26px; position:relative; padding:0; color:#0b1320; }
    .burgermenu span,
    .burgermenu span::before,
    .burgermenu span::after{ content:""; position:absolute; left:0; right:0; height:2px; background: currentColor; transition: transform .35s ease, opacity .25s ease; }
    .burgermenu span{ top:50%; transform: translateY(-50%); }
    .burgermenu span::before{ top:-8px }
    .burgermenu span::after{ top:8px }
    .burgermenu.opened span{ opacity:0 }
    .burgermenu.opened span::before{ transform: translateY(8px) rotate(45deg) }
    .burgermenu.opened span::after{ transform: translateY(-8px) rotate(-45deg) }
    /* hover state inherit existing bg hover */
</style>
<style>
  /* When header becomes fixed after hitting top on desktop */
  @media (min-width: 992px){
    header.is-fixed{ position:fixed; top:0; left:0; right:0; }
  }
  /* (Removed hover animation for header mega state per request) */
</style>
<!-- Overlay shown behind header mega menu -->
<div id="mega-overlay" class="mega-overlay" style="display:none"></div>
<div class="pointer-events-none fixed inset-0 -z-10"></div>
        <div class="utility-bar hidden md:block">
            <div class="container-page">
                <div class="utility-links">
                    <div class="utility-links__primary"><a href="/reset" style="display:none">Free 7-Day Reset</a><a href="/about">About We
                        Offer Wellness®</a><a href="/help">Help Centre</a><a href="/safety-and-contraindications">Safety
                        &amp; Contraindications</a></div>
                    <div class="utility-links__secondary"><a href="/for-business" style="display:none">For Business</a><a href="https://studio.weofferwellness.co.uk" style="display:none">Become a Practitioner</a></div>
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
                                $headerUser = auth()->user();
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
                                $headerAvatar = $headerUser?->profile_picture;
                                if ($headerAvatar && !\Illuminate\Support\Str::startsWith($headerAvatar, ['http://', 'https://'])) {
                                    $headerAvatar = \Illuminate\Support\Facades\Storage::disk('public')->url($headerAvatar);
                                }
                            @endphp
                            <button type="button" class="icon-btn account-trigger" aria-haspopup="true" aria-expanded="false">
                                <span class="account-trigger__avatar" aria-hidden="true">
                                    @if($headerAvatar)
                                        <img src="{{ $headerAvatar }}" alt="">
                                    @else
                                        {{ $headerInitials }}
                                    @endif
                                </span>
                                <span class="sr-only">Open account menu</span>
                            </button>
                            <div class="account-dropdown" id="accountDropdown" hidden>
                                <div class="account-dropdown__header">
                                    <p class="account-name">{{ $headerFullName }}</p>
                                    <p class="account-email">{{ $headerUser->email }}</p>
                                </div>
                                <div class="account-actions account-actions--authed">
                                    <a class="account-link" href="{{ route('account.dashboard') }}">Account overview</a>
                                    <a class="account-link" href="{{ route('account.orders') }}">Orders &amp; receipts</a>
                                    <a class="account-link" href="{{ route('profile.edit') }}">Profile &amp; contact</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="btn-wow btn-wow--cta">Log out</button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <button type="button" class="icon-btn account-trigger" aria-haspopup="true" aria-expanded="false">
                                <span class="sr-only">Open account options</span>
                                <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0a8.949 8.949 0 0 0 4.951-1.488A3.987 3.987 0 0 0 13 16h-2a3.987 3.987 0 0 0-3.951 3.512A8.948 8.948 0 0 0 12 21Zm3-11a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                </svg>
                            </button>
                            <div class="account-dropdown" id="accountDropdown" hidden>
                                <p class="account-email">Sign in to manage bookings faster.</p>
                                <div class="account-actions">
                                    <a class="btn-wow btn-wow--cta" href="{{ route('login', ['redirect' => '/account']) }}">Log in</a>
                                    <a class="btn-wow btn-wow--outline" href="{{ route('register', ['redirect' => '/account']) }}">Create account</a>
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
                <button
                    class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-ink-700 hover:bg-ink-100 burgermenu"
                    aria-label="Toggle menu" aria-expanded="false">
                    <span></span>
                </button>
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
.cart-dropdown2{ position:absolute; right:0; top:calc(100% + 10px); width: min(380px, 92vw); background:#fff; border:1px solid var(--ink-200); border-radius:14px; box-shadow: 0 24px 60px rgba(16,24,40,.16); overflow:hidden; z-index: 45; }
.cartdd-head{ padding:10px 14px; font-weight:700; background: linear-gradient(180deg,#fff,#f8fafc); border-bottom:1px solid #eef2f7 }
.cartdd-body{ max-height: 380px; overflow:auto }
.cartdd-empty{ padding:18px; color: var(--ink-600); text-align:center }
.cartdd-item{ display:flex; gap:10px; align-items:center; padding:12px 14px; border-bottom:1px solid #f1f5f9; }
.cartdd-item:last-child{ border-bottom:0 }
.cartdd-img{ width:54px; height:54px; border-radius:10px; overflow:hidden; border:1px solid #eceff3; background:#fafafa }
.cartdd-img img{ width:100%; height:100%; object-fit:cover; display:block }
.cartdd-info{ flex:1 1 auto; min-width:0 }
.cartdd-title{ display:block; max-width:100%; font-weight:600; color:#0b1323; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; text-decoration:none }
.cartdd-meta{ font-size:.9rem; color:#64748b }
.cartdd-amt{ font-weight:700; color:#0b1323; white-space:nowrap; flex:0 0 auto }
.cartdd-subtotal{ padding:10px 14px; display:flex; align-items:center; justify-content:space-between; border-top:1px solid #eef2f7; border-bottom:1px solid #eef2f7; background:#fff }
.cartdd-subtotal span{ font-size:12px; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:.02em }
.cartdd-upsell .upsell-item{ display:grid; grid-template-columns:46px 1fr auto; gap:10px; align-items:center; padding:8px 10px; border:1px solid #eef2f7; border-radius:10px; background:#fff; margin-bottom:8px }
.cartdd-upsell .upsell-item img{ width:46px; height:46px; object-fit:cover; border-radius:8px; border:1px solid #eceff3 }
.cartdd-upsell .upsell-title{ margin:0; font-size:13px; font-weight:700; color:#0b1323; line-height:1.25; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden }
.cartdd-upsell .upsell-price{ font-size:12px; color:#64748b; font-weight:700; margin-top:4px }
.cartdd-upsell-head{ font-weight:800; letter-spacing:-.01em; margin:0 0 8px; color:#0b1323 }
.cartdd-foot{ display:flex; gap:10px; align-items:center; justify-content:space-between; background:#fff; padding:10px 12px 14px }
/* Buttons styled like product card actions */
.cart-dropdown2 .btn{ height:38px; border-radius:4px; font-size:16px; font-weight:400; border:1px solid rgba(16,24,40,.22); background:#fff !important; color: rgba(11,18,32,.82); cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 10px 22px rgba(16,24,40,.08); text-decoration:none; flex:1 1 auto }
.cart-dropdown2 .btn--primary{ border-color: rgba(0,0,0,.10); color:#fff; background:#549483 !important }
.cartdd-foot{ padding:10px 14px; border-top:1px solid #eef2f7; background:#fff; text-align:right }
.visit-link{ font-weight:700; color:#2c6bed; text-decoration:none }
.visit-link:hover{ text-decoration:underline }
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
                        <li><a class="mobile-menu__link" href="/help">Help Centre</a></li>
                        <li><a class="mobile-menu__link" href="/safety-and-contraindications">Safety &amp; Contraindications</a></li>
                    </ul>
                </div>
            </nav>
        </div>
