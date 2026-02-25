
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>We Offer Wellness®</title>
<meta name="description"
      content="Holistic therapy, done right: new classes daily, frequent workshops &amp; events, plus restorative retreats—led by trusted practitioners at We Offer Wellness®.">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="canonical" href="{{ url()->current() }}">

<!-- Fonts: Manrope (general text, buttons, product headings) and Playfair Display (section headings) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">

<!-- Built assets via Vite (JS only here; keep inline <link rel="stylesheet" href="/css/wow-head-inline.css">
<style>
/* Global font overrides */
body, button, .btn, input, select, textarea { font-family: 'Manrope', var(--bs-font-sans-serif); }
/* Section headings (homepage/landing/product section blocks) */
.wow-section-title, .section-title, .home-section-title, .landing-section-title { font-family: 'Playfair Display', Georgia, serif; }
/* Use Playfair for H2 page titles (e.g., section titles like the example) */
h2 { font-family: 'Playfair Display', Georgia, serif; font-weight: 500; }
/* Parenthetical text in titles uses Manrope */
.paren-manrope {
  font-size: 20px;
  font-family: 'Manrope', var(--bs-font-sans-serif) !important;
  font-weight: 400;
  position: relative;
  top: -5px;
}
@media (max-width: 860px){
  .paren-manrope { font-size: 18px; }
}
@media (max-width: 520px){
  .paren-manrope { font-size: 16px; }
}
</style>
<script>
// Wrap parenthetical parts of titles in a span with Manrope font
document.addEventListener('DOMContentLoaded', function(){
  try {
    var selectors = [
      'h1','h2','h3',
      '.wow-title',
      '.section-title','.home-section-title','.landing-section-title','.wow-section-title'
    ];
    var nodes = document.querySelectorAll(selectors.join(','));
    nodes.forEach(function(el){
      try {
        if (!el) return;
        var html = String(el.innerHTML || '');
        if (html.indexOf('paren-manrope') !== -1) return; // already processed
        // Replace (...) with <span class="paren-manrope">(...)</span>
        var replaced = html.replace(/\(([^()]+)\)/g, function(_, inner){
          return '<span class="paren-manrope">(' + inner + ')</span>';
        });
        if (replaced !== html) el.innerHTML = replaced;
      } catch(_) {}
    });
  } catch(_) {}
});
</script>
<link rel="modulepreload" as="script" crossorigin="" href="/build/assets/Home-PUJfnV1T.js">
<link rel="modulepreload" as="script" crossorigin="" href="/build/assets/SiteLayout-DKnyduE5.js">
<link rel="modulepreload" as="script" crossorigin="" href="/build/assets/_plugin-vue_export-helper-1tPrXgE0.js">
<link rel="stylesheet" crossorigin="" href="/build/assets/SiteLayout-dNJFhRS5.css">
<link rel="modulepreload" as="script" crossorigin="" href="/build/assets/ProductCard-BSjt_v_N.js">
<link rel="modulepreload" as="script" crossorigin="" href="/build/assets/WowButton-CFEh0Oz8.js">
<link rel="stylesheet" crossorigin="" href="/build/assets/ProductCard-CWmZ0eZB.css">
<link rel="modulepreload" as="script" crossorigin="" href="/build/assets/UltraSearchBar-BfOnvyJy.js">
<link rel="modulepreload" as="script" crossorigin=""
      href="/build/assets/UltraSearchBar.vue_vue_type_style_index_0_lang-DRm_99TD.js">
<link rel="stylesheet" crossorigin="" href="/build/assets/UltraSearchBar-NUgbpZRO.css">
<link rel="modulepreload" as="script" crossorigin="" href="/build/assets/ClassSchedule-CsCHxwuF.js">
<link rel="stylesheet" crossorigin="" href="/build/assets/ClassSchedule-CH6Jm1u5.css">
<link rel="stylesheet" crossorigin="" href="/build/assets/Home-DuBmdSoH.css">
<style>
    #nprogress {
        pointer-events: none;
    }

    #nprogress .bar {
        background: #549483;

        position: fixed;
        z-index: 1031;
        top: 0;
        left: 0;

        width: 100%;
        height: 2px;
    }

    #nprogress .peg {
        display: block;
        position: absolute;
        right: 0px;
        width: 100px;
        height: 100%;
        box-shadow: 0 0 10px #549483, 0 0 5px #549483;
        opacity: 1.0;

        transform: rotate(3deg) translate(0px, -4px);
    }

    #nprogress .spinner {
        display: block;
        position: fixed;
        z-index: 1031;
        top: 15px;
        right: 15px;
    }

    #nprogress .spinner-icon {
        width: 18px;
        height: 18px;
        box-sizing: border-box;

        border: solid 2px transparent;
        border-top-color: #549483;
        border-left-color: #549483;
        border-radius: 50%;

        animation: nprogress-spinner 400ms linear infinite;
    }

    .nprogress-custom-parent {
        overflow: hidden;
        position: relative;
    }

    .nprogress-custom-parent #nprogress .spinner,
    .nprogress-custom-parent #nprogress .bar {
        position: absolute;
    }

    @keyframes nprogress-spinner {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
</style>

<!-- Quick override: ensure therapy card media + fomo render consistently -->
<style>
  .wow-therapy-card-scope .media img{
    width:100%;
    height:100%;
    object-fit:cover;
    border:1px solid #eee;
    display:block;
    border-radius:2px;
  }
  .wow-therapy-card-scope .content-bottom .fomo,
  .content-bottom .fomo{
    margin: 0 0 8px;
    font-size: var(--fomo, 12px);
    font-weight: 600;
    color: rgba(11, 18, 32, .84);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  /* Rating row + stars (global + scoped) */
  .wow-therapy-card-scope .rating-row,
  .rating-row{
    display:flex;
    align-items:center;
    gap:8px;
    margin-bottom:10px;
    color: rgba(11,18,32,.80);
    font-weight:400;
    font-size: var(--rating, 13px);
  }
  .wow-therapy-card-scope .stars,
  .stars{
    display:inline-flex;
    align-items:center;
    gap:3px;
    transform: translateY(1px);
  }
  .wow-therapy-card-scope .star,
  .star{
    width: var(--star, 18px);
    height: var(--star, 18px);
    display:inline-block;
    position:relative;
    background: currentColor; /* gold fill via color */
    -webkit-mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20fill%3D%27%23000%27%20d%3D%27M11.083%205.104c.35-.8%201.485-.8%201.834%200l1.752%204.022a1%201%200%200%200%20.84.597l4.463.342c.9.069%201.255%201.2.556%201.771l-3.33%202.723a1%201%200%200%200-.337%201.016l1.03%204.119c.214.858-.71%201.552-1.474%201.106l-3.913-2.281a1%201%200%200%200-1.008%200L7.583%2020.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1%201%200%200%200%206.8%2014.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1%201%200%200%20.84-.597l1.753-4.022Z%27%2F%3E%3C%2Fsvg%3E") center/contain no-repeat;
            mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20fill%3D%27%23000%27%20d%3D%27M11.083%205.104c.35-.8%201.485-.8%201.834%200l1.752%204.022a1%201%200%200%200%20.84.597l4.463.342c.9.069%201.255%201.2.556%201.771l-3.33%202.723a1%201%200%200%200-.337%201.016l1.03%204.119c.214.858-.71%201.552-1.474%201.106l-3.913-2.281a1%201%200%200%200-1.008%200L7.583%2020.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1%201%200%200%200%206.8%2014.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1%201%200%200%20.84-.597l1.753-4.022Z%27%2F%3E%3C%2Fsvg%3E") center/contain no-repeat;
  }
  .wow-therapy-card-scope .star::after,
  .star::after{
    content:"";
    position:absolute;
    inset:0;
    background:#333;
    pointer-events:none;
    -webkit-mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20fill%3D%27none%27%20stroke%3D%27%23000%27%20stroke-width%3D%272%27%20stroke-linejoin%3D%27round%27%20stroke-linecap%3D%27round%27%20d%3D%27M11.083%205.104c.35-.8%201.485-.8%201.834%200l1.752%204.022a1%201%200%200%200%20.84.597l4.463.342c.9.069%201.255%201.2.556%201.771l-3.33%202.723a1%201%200%200%200-.337%201.016l1.03%204.119c.214.858-.71%201.552-1.474%201.106l-3.913-2.281a1%201%200%200%200-1.008%200L7.583%2020.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1%201%200%200%200%206.8%2014.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1%201%200%200%20.84-.597l1.753-4.022Z%27%2F%3E%3C%2Fsvg%3E") center/contain no-repeat;
            mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20fill%3D%27none%27%20stroke%3D%27%23000%27%20stroke-width%3D%272%27%20stroke-linejoin%3D%27round%27%20stroke-linecap%3D%27round%27%20d%3D%27M11.083%205.104c.35-.8%201.485-.8%201.834%200l1.752%204.022a1%201%200%200%200%20.84.597l4.463.342c.9.069%201.255%201.2.556%201.771l-3.33%202.723a1%201%200%200%200-.337%201.016l1.03%204.119c.214.858-.71%201.552-1.474%201.106l-3.913-2.281a1%201%200%200%200-1.008%200L7.583%2020.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1%201%200%200%200%206.8%2014.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1%201%200%200%20.84-.597l1.753-4.022Z%27%2F%3E%3C%2Fsvg%3E") center/contain no-repeat;
  }
  .wow-therapy-card-scope .star--empty,
  .star--empty{ color: transparent; }
  /* Override: use provided star SVG for masks globally */
  .wow-therapy-card-scope .star,
  .star{
    /* Use filled path for base mask so center is gold */
    -webkit-mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20fill%3D%27%23000%27%20d%3D%27M11.083%205.104c.35-.8%201.485-.8%201.834%200l1.752%204.022a1%201%200%200%200%20.84.597l4.463.342c.9.069%201.255%201.2.556%201.771l-3.33%202.723a1%201%200%200%200-.337%201.016l1.03%204.119c.214.858-.71%201.552-1.474%201.106l-3.913-2.281a1%201%200%200%200-1.008%200L7.583%2020.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1%201%200%200%200%206.8%2014.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1%201%200%200%200%20.84-.597l1.753-4.022Z%27/%3E%3C/svg%3E") center/contain no-repeat;
            mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20fill%3D%27%23000%27%20d%3D%27M11.083%205.104c.35-.8%201.485-.8%201.834%200l1.752%204.022a1%201%200%200%200%20.84.597l4.463.342c.9.069%201.255%201.2.556%201.771l-3.33%202.723a1%201%200%200%200-.337%201.016l1.03%204.119c.214.858-.71%201.552-1.474%201.106l-3.913-2.281a1%201%200%200%200-1.008%200L7.583%2020.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1%201%200%200%200%206.8%2014.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1%201%200%200%200%20.84-.597l1.753-4.022Z%27/%3E%3C/svg%3E") center/contain no-repeat;
  }
  .wow-therapy-card-scope .star::after,
  .star::after{
    -webkit-mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20stroke%3D%27%23000%27%20stroke-width%3D%272%27%20d%3D%27M11.083%205.104c.35-.8%201.485-.8%201.834%200l1.752%204.022a1%201%200%200%20 %200%20.84.597l4.463.342c.9.069%201.255%201.2.556%201.771l-3.33%202.723a1%201%200%200%20 %200-.337%201.016l1.03%204.119c.214.858-.71%201.552-1.474%201.106l-3.913-2.281a1%201%200%200%20 %200-1.008%200L7.583%2020.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1%201%200%200%20 %200%206.8%2014.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1%201%200%200%20 .84-.597l1.753-4.022Z%27%2F%3E%3C%2Fsvg%3E") center/contain no-repeat;
            mask: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20stroke%3D%27%23000%27%20stroke-width%3D%272%27%20d%3D%27M11.083%205.104c.35-.8%201.485-.8%201.834%200l1.752%204.022a1%201%200%200%20 %200%20.84.597l4.463.342c.9.069%201.255%201.2.556%201.771l-3.33%202.723a1%201%200%200%20 %200-.337%201.016l1.03%204.119c.214.858-.71%201.552-1.474%201.106l-3.913-2.281a1%201%200%200%20 %200-1.008%200L7.583%2020.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1%201%200%200%20 %200%206.8%2014.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1%201%200%200%20 .84-.597l1.753-4.022Z%27%2F%3E%3C%2Fsvg%3E") center/contain no-repeat;
  }
  /* Adjust kicker spacing site-wide */
  .kicker{
    display: inline-flex;
    align-items: center;
    padding: 0px !important;
    font-size: 13px;
    margin-bottom: 0 !important;
    background: none;
    font-weight: 600;
    border: none;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--ink-700);
    width: -moz-fit-content !important;
    width: fit-content !important;
    max-width: 100%;
    flex: none !important;
  }
</style>

@stack('head')
