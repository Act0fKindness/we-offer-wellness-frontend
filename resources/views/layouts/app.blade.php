<html lang="en">
<head>
    @include('partials.head')
</head>
<body class="antialiased">
  <div class="text-ink-800">
      @include('partials.header')
      <main class="">
          @yield('content')
      </main>
      @include('partials.footer')
  </div>

<script>
  window.WOW_MAPS_KEY = window.WOW_MAPS_KEY || @json(env('MAPBOX_API_KEY'));
</script>
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
    // Also open when clicking the whole segment (icon/label area)
    var segWhere = byId('seg-where');
    if(segWhere){ segWhere.addEventListener('click', function(){ openPane('where') }) }

    var whenInput = byId('when');
    if(whenInput){
      whenInput.addEventListener('focus', function(){ openPane('when') });
      whenInput.addEventListener('click', function(){ openPane('when') });
    }
    var segWhen = byId('seg-when');
    if(segWhen){ segWhen.addEventListener('click', function(){ openPane('when') }) }

    var whoSeg = byId('seg-who');
    if(whoSeg){
      whoSeg.addEventListener('click', function(){ openPane('who') });
    }

    // Close when clicking outside
    document.addEventListener('click', function(e){
      try{ if(root && !root.contains(e.target)) hideAll(); }catch(_){ /* no-op */ }
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
    // Ensure panes start closed on load
    try{ hideAll(); } catch(_){ }

    // Submit handler → build /search URL
    try {
      var form = root.closest('form') || root.querySelector('form') || document.querySelector('.wow-ultra form.bar');
      if(form){
        form.addEventListener('submit', function(e){
          try { e.preventDefault(); } catch(_) {}
          var params = new URLSearchParams();
          // what
          var what = byId('what')?.value?.trim();
          if(what) params.set('what', what);
          // where
          var whereHidden = byId('where');
          var whereText = byId('where-editor')?.textContent?.trim();
          var where = (whereHidden && whereHidden.value) ? whereHidden.value : (whereText || '');
          if(where) params.set('where', where);
          // when (as-is string)
          var when = byId('when')?.value?.trim();
          if(when) params.set('when', when);
          // group type
          var groupList = byId('group-type-list') || document.getElementById(prefix + '-group-type-list');
          if(groupList){
            var sel = groupList.querySelector('.item[aria-selected="true"]');
            var gt = sel?.getAttribute('data-group') || sel?.textContent?.trim();
            if(gt){ params.set('group_type', gt.toLowerCase()); }
          }
          // adults count
          var adultsVal = document.getElementById(prefix + '-adults-val');
          var adults = adultsVal ? parseInt(adultsVal.textContent, 10) : NaN;
          if(Number.isFinite(adults) && adults > 0) params.set('adults', String(adults));
          // Mode: Online shortcut
          if(/^(online)$/i.test(where)){ params.set('mode','online'); }
          // Build URL and navigate
          var url = '/search' + (params.toString() ? ('?' + params.toString()) : '');
          try { window.location.assign(url); } catch(_) { window.location.href = url; }
        });
      }
    } catch(err) { /* no-op */ }
  }

  // Initialize bars present on the page
  ['home-template','home-sticky','search-top'].forEach(function(prefix){
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
      function scheduleClose(){ clearTimeout(closeTimer); closeTimer = setTimeout(hideMenu, 400); }
      function cancelClose(){ clearTimeout(closeTimer); }
      // Only close when leaving BOTH header and panel areas
      headerEl.addEventListener('mouseleave', function(e){
        try { if (panel.contains(e.relatedTarget)) return; } catch(_) {}
        scheduleClose();
      });
      headerEl.addEventListener('mouseenter', cancelClose);
      panel.addEventListener('mouseenter', cancelClose);
      panel.addEventListener('mouseleave', function(e){
        try { if (headerEl.contains(e.relatedTarget)) return; } catch(_) {}
        scheduleClose();
      });
      // Defensive: keep open on any movement within panel
      panel.addEventListener('mousemove', cancelClose);
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
