<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'We Offer Wellness')</title>
  <style>
    body {
      margin:0;
      padding:0;
      background:#edf2f5;
      font-family:'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      color:#0b1220;
    }
    img { border:0; line-height:100%; outline:none; text-decoration:none; max-width:100%; height:auto; }
    a { color:#0b1220; }

    .wrapper { width:100%; padding:32px 16px; background:radial-gradient(900px 320px at 20% 0%, rgba(74,136,120,.18), transparent 60%),radial-gradient(900px 240px at 80% 0%, rgba(15,23,42,.12), transparent 60%),#edf2f5; }
    .card { width:100%; max-width:640px; border-radius:3px; overflow:hidden; background:#ffffff; box-shadow:0 50px 80px rgba(12,19,34,.17); border:5px solid rgba(0,0,0,.1); }

    .hero-top { position:relative; padding:28px 32px 48px; background-image:url('https://images.pexels.com/photos/5208298/pexels-photo-5208298.jpeg'); background-size:cover; background-position:center; color:#0b1220; }
    .hero-top::after { content:""; position:absolute; inset:0; background:rgba(255,255,255,.85); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); }
    .brand { position:relative; z-index:1; width:170px; height:auto; display:block; }
    .hero-heading { position:relative; z-index:1; font-size:30px; margin:12px 0 0; letter-spacing:-.02em; color:#0b1220; }
    .hero-sub { position:relative; z-index:1; font-size:15px; margin-top:8px; color:#000000; }

    .content { padding:32px; font-size:15px; line-height:1.6; background:#ffffff; }
    h1 { font-size:26px; margin:0 0 12px; letter-spacing:-.01em; }
    .code-box { display:inline-flex; align-items:center; justify-content:center; padding:5px 14px; border-radius:6px; background:#f3f6fb; font-size:30px; font-weight:800; letter-spacing:6px; border:1px solid rgba(15,23,42,.08); margin:8px 0 16px; }
    .muted { color:#667085; }
    .eyebrow { text-transform:uppercase; font-size:12px; letter-spacing:.2em; color:#7f8aa3; font-weight:700; margin-bottom:10px; display:block; }
    .info-card { border-radius:18px; border:1px solid rgba(15,23,42,.08); background:#f7f9fc; padding:18px 20px; margin:22px 0; }
    .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:14px 28px; border-radius:999px; background:#0b1220; color:#fff !important; text-decoration:none; font-weight:700; font-size:15px; letter-spacing:.01em; box-shadow:0 15px 40px rgba(11,18,32,.25); }
    .btn:hover { background:#111d32; }
    .divider { width:100%; height:1px; background:linear-gradient(90deg, transparent, rgba(15,23,42,.14), transparent); margin:30px 0; }

    .footer { padding:22px 32px 32px; text-align:center; background:#f9fafc; font-size:12px; color:#7b8498; }
    .footer a { color:#0b1220; text-decoration:none; }

    @media (max-width:520px){
      .wrapper { padding:20px 10px; }
      .hero-top { padding:24px 20px 40px; }
      .content { padding:24px 20px 28px; }
      .code-box { width:100%; letter-spacing:8px; }
      .btn { width:100%; }
    }
  </style>
</head>
<body>
  <table role="presentation" class="wrapper" cellpadding="0" cellspacing="0" width="100%">
    <tr>
      <td align="center">
        <table role="presentation" class="card" cellpadding="0" cellspacing="0">
          <tr>
            <td class="hero-top">
              <a href="https://weofferwellness.co.uk" target="_blank" rel="noopener" style="display:inline-flex; align-items:center;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1240.46 141.78" height="30" aria-hidden="true">
                  <defs><style>.cls-1-header {fill:#599d91}.cls-2-header {fill:#000}</style></defs>
                  <g><g>
                    <path class="cls-2-header" d="M483.94,63.07v57h-23.01v-56.97l-9.85.03c6.06,20.44.68,44.4-19.84,54.14-13.48,6.41-29.22,6.51-42.6-.12-14.21-7.04-21.27-21.46-21.22-37.09.04-15.11,6.2-29.32,19.58-36.92,21.57-12.27,53.7-6.68,63.84,19.24l.5-16.16,9.59-.25c-.06-9.84,1.82-19.92,9.35-26.56,8.55-7.54,20.13-8.84,31.69-5.75l-3.26,17.23c-4.69-1.25-9.36-1.92-12.18,1.8-2.66,3.51-2.84,8.57-2.6,13.35l23.92.03c-.5-15.95,6.15-31.21,22.9-34.29,6.32-1.16,12.4-.62,19.32.19l-.9,18.22c-5.2-.83-10.13-1.73-13.92,1.15-4.44,3.37-4.68,9.23-4.25,14.65l14.51.18.61,17.91c8.02-13.24,19.98-19.44,34.91-18.99,13.69.42,25.13,8.26,29.2,21.63,2.32,7.62,2.96,15.74,1.21,23.32l-47.37.06c2.16,17.94,28.17,14.65,41.19,11.13l3.05,15.32c-23.3,8.48-58.89,7.46-65.25-22.51-2.28-10.74-1.09-20.65,3.87-30.99l-15.99.03v56.98h-23v-56.99h-24Z"/>
                    <path class="cls-2-header" d="M277.89,39.19l-25.1,80.84-24.11.06c-4.85-17.24-9.08-33.79-13.07-51.45l-4.05,17.25-9.65,34.16h-24.17l-23.81-80.87,26.2.07,11.26,58.94,14.84-58.99,20.3-.19,14.28,57.08,11.95-57.05,25.12.15Z"/>
                    <polygon class="cls-2-header" points="804.89 39.19 779.8 120.03 755.69 120.09 742.62 68.64 738.57 85.89 728.92 120.05 704.75 120.05 680.94 39.17 707.14 39.25 718.4 98.18 733.24 39.19 753.54 39.01 767.82 96.09 779.78 39.04 804.89 39.19"/>
                    <path class="cls-2-header" d="M297.33,90.11c1.01,17.52,27.78,14.9,40.9,11.12l3.06,15.34c-12.65,4.55-25.92,5.84-39.07,3.16-24.38-4.97-32.05-30.26-24.65-51.02,5.45-15.3,19.31-23.88,35.38-23.65,13.57.2,25.15,7.16,29.74,20.16,2.78,7.87,3.43,16.08,1.93,24.82l-47.29.08Z"/>
                    <path class="cls-2-header" d="M985.84,66.82c-3.99-3.94-9.55-3.65-13.96-1.49-3.06,1.5-6.85,6.15-6.87,10.77l-.11,43.97h-22.94l-.42-73.87,19.55-.18,1.68,10.36c10.12-12.3,27.92-15.52,40.45-5.56,6.05,4.82,9.42,13.4,9.47,21.25l.28,48h-23.02l-.04-42.08c0-3.67-1.36-8.5-4.07-11.17Z"/>
                    <path class="cls-2-header" d="M823.33,90.11c1.01,17.52,27.78,14.9,40.9,11.12l3.06,15.34c-12.65,4.55-25.92,5.84-39.07,3.16-24.38-4.97-32.05-30.26-24.65-51.02,5.45-15.3,19.31-23.88,35.38-23.65,13.57.2,25.15,7.16,29.74,20.16,2.78,7.87,3.43,16.08,1.93,24.82l-47.29.08Z"/>
                    <g>
                      <path class="cls-2-header" d="M1131.76,117.42c-13.93,5.8-29.2,4.36-42.9-1.29l4.08-16.4c7.18,4.02,29.11,9.43,29.96-.17.21-2.4-1.77-5.18-4.17-6.13l-13.21-5.19c-5.74-2.26-11.52-6.86-13.63-12.78-4.08-11.44,2.69-23.03,13.82-27.47,11.74-4.69,24.95-3.65,36.3,1.38l-4.04,15.56c-6.42-2.98-23.07-7.25-24.96.92-.42,1.82,1.49,5.08,3.45,5.87l14.88,5.97c8.38,3.36,13.66,10.61,13.86,18.89.21,8.77-4.25,17.03-13.42,20.84Z"/>
                      <path class="cls-2-header" d="M1040.33,90.11c1.01,17.52,27.78,14.9,40.9,11.12l3.06,15.34c-12.65,4.55-25.92,5.84-39.07,3.16-24.38-4.96-32.05-30.28-24.66-51.02,5.45-15.3,19.31-23.88,35.39-23.65,13.57.2,25.15,7.16,29.74,20.16,2.78,7.87,3.43,16.08,1.93,24.82l-47.29.08Z"/>
                      <path class="cls-2-header" d="M1188.76,117.42c-13.93,5.8-29.2,4.36-42.9-1.29l4.08-16.4c7.18,4.02,29.11,9.43,29.96-.17.21-2.4-1.77-5.18-4.17-6.12l-13.21-5.19c-5.74-2.26-11.52-6.86-13.63-12.78-4.08-11.44,2.69-23.03,13.82-27.47,11.74-4.69,24.95-3.65,36.3,1.38l-4.05,15.56c-6.4-3-23.14-7.21-24.96.9-.43,1.91,1.45,5.08,3.45,5.88l14.87,5.97c8.38,3.36,13.66,10.6,13.86,18.89.21,8.77-4.25,17.02-13.42,20.84Z"/>
                    </g>
                    <g><rect class="cls-2-header" x="908.93" y="12.07" width="23" height="108"/><rect class="cls-2-header" x="875.93" y="12.07" width="23" height="108"/></g>
                    <path class="cls-2-header" d="M640.9,120.03l-22.94.05-.39-73.97,19.45-.04,1.19,14.28c4.45-10.27,13.04-17.02,25.15-14.92l.08,21.31c-3.87,0-6.43-.04-9.57.19-7.27.53-12.84,6.88-12.86,14.11l-.11,39Z"/>
                    <g>
                      <path class="cls-1-header" d="M78.77,44.93l-20.39,17.6-20.31-17.61c-5.11-4.43-9.26-9.29-14.08-15.15C27.9,22.42,51.39.46,58.1,0s29.62,21.65,34.78,29.36c-4.34,5.84-8.74,10.94-14.11,15.57Z"/>
                      <path class="cls-1-header" d="M56.4,141.78l-17.26-8.91C19.9,122.93-2.69,102.04.26,77.69l13.99,11.01c20.48,16.11,42.29,21.02,42.14,53.08Z"/>
                      <path class="cls-1-header" d="M60.62,141.61c-.68-31.52,21.74-36.99,41.98-52.91l13.99-11c3.11,24.27-20.19,45.94-39.53,55.48-5.26,2.87-9.54,5.15-16.43,8.44Z"/>
                      <path class="cls-1-header" d="M55.01,113.27c-11.82-10.06-23.53-18.79-36.4-26.93-6.2-3.92-11.4-8.35-17.11-13.24.89-7.19,2.08-13.73,5.56-19.48l35.32,29.93c7.73,7.71,13.81,17.2,12.63,29.71Z"/>
                      <path class="cls-1-header" d="M95.85,87.93c-12.15,7.63-22.98,16.17-34.07,25.27-.99-12.69,5.13-22.39,13.22-30.11l35.02-29.61c3,6.16,4.79,12.74,5.19,19.56-6.08,5.67-12.4,10.51-19.36,14.88Z"/>
                      <path class="cls-1-header" d="M61.84,90.91c.3-17.28-3.8-21.71,7.79-32.02l27.89-24.79c2.95,4.5,5.67,9.03,8.74,14.41-8.52,10.17-17.85,18.5-28.28,26.77-5.86,4.65-10.38,9.91-16.14,15.62Z"/>
                      <path class="cls-1-header" d="M54.91,90.53c-5.71-5.3-10.39-10.83-16.58-15.7-10.12-7.97-18.85-16.06-27.65-26.02,2.66-5.25,5.52-10.06,8.71-14.7l27.84,24.7c3.47,3.08,6.29,6.96,8.24,11.07l-.55,20.65Z"/>
                    </g>
                  </g></g>
                </svg>
              </a>
              <div class="hero-heading">@yield('title', 'We Offer Wellness')</div>
              <p class="hero-sub">Calm technology for your rituals, ready in under a minute.</p>
            </td>
          </tr>
          <tr>
            <td class="content">
              @yield('content')
              <div class="divider"></div>
              <p class="muted" style="margin:0">Need help? Reply to this email or visit <a href="https://weofferwellness.co.uk/help">the Help Centre</a>.</p>
            </td>
          </tr>
          <tr>
            <td class="footer">
              © {{ date('Y') }} We Offer Wellness® · <a href="https://weofferwellness.co.uk/privacy">Privacy</a>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
