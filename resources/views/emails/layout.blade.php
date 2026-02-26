<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'AtEase')</title>
  <style>
    /* Reset + base */
    body { margin:0; padding:0; background:#f6f6f6; color:#202123; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; }
    img { border:0; line-height:100%; outline:none; text-decoration:none; max-width:100%; height:auto; }
    a { color:#111214; }

    /* Wrapper */
    .wrapper { width:100%; background:#f6f6f6; padding:24px 12px; }
    .card { width:100%; max-width:640px; background:#ffffff; border:1px solid #e6e7eb; border-radius:14px; overflow:hidden; box-shadow:0 8px 24px rgba(0,0,0,.05); }

    /* Header */
    .header { padding:20px 20px 0 20px; text-align:center; }
    .brand { height:auto; width:160px; max-width:70%; }

    /* Content */
    .content { padding:20px; font-size:15px; line-height:1.6; }
    h1, h2, h3 { margin:0 0 12px; font-weight:700; letter-spacing:-.01em; }
    h1 { font-size:22px; }
    .btn { display:inline-block; padding:12px 18px; background:#111214; color:#ffffff !important; text-decoration:none; border-radius:999px; font-weight:600; letter-spacing:.01em; }
    .muted { color:#6e6e80; }

    /* Footer */
    .footer { padding:16px 20px 22px; text-align:center; border-top:1px solid #f0f1f4; color:#6e6e80; font-size:12px; }

    @media (max-width:480px){
      .wrapper { padding:16px 8px; }
      .content { padding:16px; font-size:14px; }
      .brand { width:140px; }
      .footer { font-size:11px; }
    }
  </style>
</head>
<body>
  <table role="presentation" class="wrapper" cellpadding="0" cellspacing="0" width="100%">
    <tr>
      <td align="center">
        <table role="presentation" class="card" cellpadding="0" cellspacing="0">
          <tr>
            <td class="header">
              <a href="https://atease.weofferwellness.co.uk" target="_blank" rel="noopener">
                <img class="brand" src="https://cdn.shopify.com/s/files/1/0820/3947/2469/files/atease-v3-logo.png?v=1757545036" alt="AtEase" width="160" style="width:160px; max-width:70%; height:auto;">
              </a>
            </td>
          </tr>
          <tr>
            <td class="content">
              @yield('content')
            </td>
          </tr>
          <tr>
            <td class="footer">
              © {{ date('Y') }} AtEase by We Offer Wellness®
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
