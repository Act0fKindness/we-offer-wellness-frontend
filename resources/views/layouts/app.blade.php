<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>We Offer Wellness®</title>
    <meta name="description"
          content="Holistic therapy, done right: new classes daily, frequent workshops &amp; events, plus restorative retreats—led by trusted practitioners at We Offer Wellness®.">

    <!-- Fonts: Instrument Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link
        href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,opsz,wght@0,14..32,300..900;1,14..32,300..900&amp;display=swap"
        rel="stylesheet">

    <!-- Built CSS bundle from Vite/Laravel -->
    @vite('resources/css/we-offer-wellness-base-styles.css')

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
        <meta name="description"
          content="Holistic therapy, done right: new classes daily, frequent workshops &amp; events, plus restorative retreats—led by trusted practitioners at We Offer Wellness®."
          inertia="">
    <link rel="canonical" href="/" inertia="">
    <meta property="og:title" content="Holistic Therapy That Works | We Offer Wellness®" inertia="">
    <meta property="og:description"
          content="Holistic therapy, done right: new classes daily, frequent workshops &amp; events, plus restorative retreats—led by trusted practitioners at We Offer Wellness®."
          inertia="">
    <meta property="og:url" content="" inertia="">
    <title inertia="">Holistic Therapy That Works | We Offer Wellness®</title></head>
<body class="font-sans antialiased">
<div id="app"
     data-page="{&quot;component&quot;:&quot;Home&quot;,&quot;props&quot;:{&quot;errors&quot;:{},&quot;auth&quot;:{&quot;user&quot;:null},&quot;mapboxKey&quot;:&quot;&quot;},&quot;url&quot;:&quot;/&quot;,&quot;version&quot;:&quot;static&quot;}"
     data-v-app=""><!---->
    <div class="min-h-screen text-ink-800">
        @include('partials.header')
        <main>
        @yield('content')
        </main>
        @include('partials.footer')
    </div>
 </div>

<script>
(function(){
  function setupUltraSearchBar(prefix){
    var root = document.querySelector('[id^="'+prefix+'-seg-"]')?.closest('.wow-ultra') || document.querySelector('#'+prefix+'-seg-what')?.closest('.wow-ultra');
    // If structure not found, bail
    if(!root) return;

    function byId(s){ return document.getElementById(prefix + '-' + s) }
    var panes = ['what-pane','where-pane','when-pane','who-pane'];

    function hideAll(){
      panes.forEach(function(id){ var el = byId(id); if(el) el.classList.add('d-none') })
      var what = byId('what'); if(what) what.setAttribute('aria-expanded','false');
    }
    function openPane(which){
      hideAll();
      var pane = byId(which+'-pane');
      if(pane){ pane.classList.remove('d-none') }
      if(which==='what'){ var what = byId('what'); if(what) what.setAttribute('aria-expanded','true') }
    }

    // Open on clicks/focus
    var whatInput = byId('what');
    if(whatInput){
      whatInput.addEventListener('focus', function(e){ openPane('what') });
      whatInput.addEventListener('input', function(e){ openPane('what') });
      var segWhat = byId('seg-what');
      if(segWhat){ segWhat.addEventListener('click', function(){ openPane('what') }) }
    }

    var whereEditor = byId('where-editor');
    if(whereEditor){
      whereEditor.addEventListener('focus', function(){ openPane('where') });
      whereEditor.addEventListener('click', function(){ openPane('where') });
    }

    var whenInput = byId('when');
    if(whenInput){
      whenInput.addEventListener('focus', function(){ openPane('when') });
      whenInput.addEventListener('click', function(){ openPane('when') });
    }

    var whoSeg = byId('seg-who');
    if(whoSeg){
      whoSeg.addEventListener('click', function(){ openPane('who') });
    }

    // Close when clicking outside
    document.addEventListener('click', function(e){
      if(!root.contains(e.target)) hideAll();
    });
    // ESC closes
    document.addEventListener('keydown', function(e){ if(e.key==='Escape') hideAll() });

    // Item selections
    var whatList = byId('what-list');
    if(whatList && whatInput){
      whatList.addEventListener('click', function(e){
        var btn = e.target.closest('.item');
        if(btn && btn.dataset.value){ whatInput.value = btn.dataset.value; hideAll(); whatInput.blur(); }
      });
    }
    var whereHidden = byId('where');
    if(byId('where-list') && whereEditor){
      byId('where-list').addEventListener('click', function(e){
        var btn = e.target.closest('.item');
        if(btn && btn.dataset.value){
          whereEditor.textContent = btn.dataset.value;
          if(whereHidden) whereHidden.value = btn.dataset.value;
          hideAll();
        }
      });
    }
    var whoDone = byId('who-done');
    if(whoDone){ whoDone.addEventListener('click', function(){ hideAll() }) }
  }

  // Initialize bars present on the page
  ['home-template','home-sticky'].forEach(function(prefix){
    try { setupUltraSearchBar(prefix) } catch(err) { /* no-op */ }
  });

  // Hide any nav/menu link labelled "Recordings" (temporary)
  try {
    var navLinks = document.querySelectorAll('header .nav-item a, .mega-panel a, nav a');
    navLinks.forEach(function(a){
      if(/recordings/i.test((a.textContent||'').trim())){
        var hideEl = a.closest('.nav-item') || a;
        hideEl.style.display = 'none';
      }
    });
  } catch {}

  // Hide any section/cards that promote on-demand or recorded content (temporary)
  try {
    var phrases = [/on\s?-?\s?demand/i, /recorded/i, /recordings/i, /replay/i];
    var headings = document.querySelectorAll('h1, h2, h3, h4, h5, h6, .kicker');
    headings.forEach(function(h){
      var text = (h.textContent || '').trim();
      if(phrases.some(function(rx){ return rx.test(text) })){
        var container = h.closest('section') || h.closest('.section') || h.closest('.card') || h.closest('.container-page');
        if(container){ container.style.display = 'none'; }
      }
    });
  } catch {}

  // Mega menu: show/hide + switch content
  try {
    var headerEl = document.querySelector('header');
    var panel = document.getElementById('mega-panel');
    if (headerEl && panel) {
      function showMenu(key){
        if(!key){ hideMenu(); return }
        panel.style.display = 'block';
        panel.setAttribute('data-active', key);
      }
      function hideMenu(){ panel.style.display = 'none'; panel.removeAttribute('data-active'); }

      // Attach to nav links via data-mega-menu attribute (e.g., data-mega-menu="need").
      // If a link has no mega menu, hovering it will close any open panel.
      headerEl.querySelectorAll('.nav-item > a.link-wow--nav').forEach(function(a){
        var key = a.getAttribute('data-mega-menu');
        a.addEventListener('mouseenter', function(){ key ? showMenu(key) : hideMenu(); });
        a.addEventListener('focus', function(){ key ? showMenu(key) : hideMenu(); });
      });
      // Keep open when hovering panel; close on leaving header+panel area
      var closeTimer;
      function scheduleClose(){ clearTimeout(closeTimer); closeTimer = setTimeout(hideMenu, 120); }
      function cancelClose(){ clearTimeout(closeTimer); }
      headerEl.addEventListener('mouseleave', scheduleClose);
      panel.addEventListener('mouseenter', cancelClose);
      panel.addEventListener('mouseleave', scheduleClose);
      document.addEventListener('keydown', function(e){ if(e.key==='Escape') hideMenu() });
    }
  } catch {}

  // Mobile menu toggle
  try {
    var burger = document.querySelector('button[aria-label="Toggle menu"]');
    var mobile = document.getElementById('mobile-menu');
    if (burger && mobile){
      function setBodyScroll(disabled){ try{ document.body.style.overflow = disabled ? 'hidden' : ''; }catch{} }
      function closeMobile(){ mobile.style.display = 'none'; burger.setAttribute('aria-expanded','false'); setBodyScroll(false); }
      function openMobile(){ mobile.style.display = 'block'; burger.setAttribute('aria-expanded','true'); setBodyScroll(true); }
      var open = false;
      burger.addEventListener('click', function(){ open ? closeMobile() : openMobile(); open = !open; });
      document.addEventListener('keydown', function(e){ if(e.key==='Escape' && open){ closeMobile(); open=false; }});
      mobile.addEventListener('click', function(e){ var a = e.target.closest('a'); if(a){ closeMobile(); open=false; }});
      // Close if window resized to desktop
      window.addEventListener('resize', function(){ if(window.innerWidth >= 768 && open){ closeMobile(); open=false; }});
    }
  } catch {}
})();
</script>
</body>
</html>
