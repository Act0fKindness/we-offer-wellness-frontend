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
    .card { width:100%; max-width:640px; border-radius:24px; overflow:hidden; background:#ffffff; box-shadow:0 50px 80px rgba(12,19,34,.17); border:1px solid rgba(255,255,255,.8); }

    .hero-top { position:relative; padding:28px 32px 48px; background-image:url('https://images.pexels.com/photos/5208298/pexels-photo-5208298.jpeg'); background-size:cover; background-position:center; color:#0b1220; }
    .hero-top::after { content:""; position:absolute; inset:0; background:rgba(255,255,255,.85); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); }
    .brand { position:relative; z-index:1; width:170px; height:auto; display:block; }
    .hero-heading { position:relative; z-index:1; font-size:30px; margin:12px 0 0; letter-spacing:-.02em; color:#0b1220; }
    .hero-sub { position:relative; z-index:1; font-size:15px; margin-top:8px; color:#4f566c; }

    .content { padding:32px; font-size:15px; line-height:1.6; background:#ffffff; }
    h1 { font-size:26px; margin:0 0 12px; letter-spacing:-.01em; }
    .code-box { display:inline-flex; align-items:center; justify-content:center; padding:14px 26px; border-radius:16px; background:#f3f6fb; font-size:30px; font-weight:800; letter-spacing:6px; border:1px solid rgba(15,23,42,.08); box-shadow: inset 0 0 0 1px rgba(255,255,255,.6); margin:8px 0 16px; }
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
              <a href="https://weofferwellness.co.uk" target="_blank" rel="noopener">
                <img class="brand" src="https://cdn.shopify.com/s/files/1/0820/3947/2469/files/logo.png?v=1738109013" alt="We Offer Wellness" width="170" style="width:170px;max-width:80%;height:auto;">
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
