
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

<!-- Built assets via Vite (JS only here; keep inline <style> below intact) -->
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

@stack('head')
