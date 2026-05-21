<html lang="en">
<head>
    @include('partials.head')
</head>
<body class="antialiased">

<div id="pwa-boot"
     style="position:fixed;inset:0;display:grid;place-items:center;background:#fff;z-index:2147483647;">
  <div style="text-align:center;padding:18px;">
    <div style="width:76px;height:76px;border-radius:18px;overflow:hidden;background:#fff;
                box-shadow:0 10px 30px rgba(11,18,32,.10);margin:0 auto;">
      <img
        src="https://testing.studio.weofferwellness.co.uk/workspace-favicon.png?v=2"
        alt="We Offer Wellness Studio"
        width="76" height="76"
        loading="eager"
        fetchpriority="high"
        decoding="async"
        style="width:100%;height:100%;object-fit:contain;display:block;background:#fff;"
        onerror="this.style.display='none';document.getElementById('pwa-boot-fallback').style.display='block';"
      />
      <!-- fallback if image fails -->
      <div id="pwa-boot-fallback"
           style="display:none;width:100%;height:100%;display:grid;place-items:center;font:600 12px system-ui;color:#5438ff;">
        WOW
      </div>
    </div>

    <div aria-hidden="true"
         style="width:26px;height:26px;border-radius:999px;border:3px solid rgba(11,18,32,.12);
                border-top-color:#5438ff;margin:16px auto 0;animation:bootSpin .9s linear infinite;">
    </div>
  </div>
</div>

  <div class="text-ink-800">
      @include('partials.header')
      <main>
          @yield('content')
      </main>
      @include('partials.footer')
      @include('partials.cookie-banner')
  </div>


<script>
  (function () {
    const boot = document.getElementById('pwa-boot');
    if (!boot) return;

    let hidden = false;

    const hide = () => {
      if (hidden) return;
      hidden = true;

      boot.style.opacity = '0';
      boot.style.transition = 'opacity 180ms ease';
      setTimeout(() => boot.remove(), 220);
    };

    // Hide as soon as HTML is parsed (much quicker than window.load)
    window.addEventListener('DOMContentLoaded', hide, { once: true });

    // Backup: hide when everything finishes loading
    window.addEventListener('load', hide, { once: true });

    // Failsafe: never trap the user behind it
    setTimeout(hide, 6000);
  })();
</script>

<script>
  window.WOW_MAPS_KEY = window.WOW_MAPS_KEY || @json(env('MAPBOX_API_KEY'));
</script>
<script>
(function(){
  var WOW_ULTRA_SEARCH_SOURCE_PROMISE = null;
  var WOW_ULTRA_SEARCH_SOURCE_CACHE = null;

  function wowUltraNormalize(value){
    return String(value || '')
      .toLowerCase()
      .replace(/[\u2018\u2019\u201c\u201d]/g, "'")
      .replace(/[^a-z0-9]+/g, ' ')
      .trim();
  }

  function wowUltraEscapeHtml(value){
    return String(value ?? '').replace(/[&<>"']/g, function(ch){
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[ch];
    });
  }

  function wowUltraCanonicalPlanKey(value){
    var normalized = String(value || '').toLowerCase().trim().replace(/[_ ]+/g, '-');
    switch (normalized) {
      case 'community':
      case 'starter':
      case 'standard':
      case 'free-starter':
      case 'starter-package':
        return 'starter';
      case 'core':
      case 'business-accelerator':
      case 'business-accelerator-package':
      case 'businessaccelerator':
        return 'business-accelerator';
      case 'premium':
      case 'premium-accelerator':
      case 'premiumaccelerator':
        return 'premium-accelerator';
      case 'become-partner':
      case 'partner':
        return 'become-partner';
      default:
        return normalized;
    }
  }

  function wowUltraPlanTitle(value){
    switch (wowUltraCanonicalPlanKey(value)) {
      case 'starter': return 'Starter';
      case 'business-accelerator': return 'Business Accelerator';
      case 'premium-accelerator': return 'Premium Accelerator';
      case 'become-partner': return 'Become Partner';
      default:
        return String(value || '')
          .replace(/[-_]+/g, ' ')
          .replace(/\b\w/g, function(m){ return m.toUpperCase(); }) || 'Plan';
    }
  }

  function wowUltraTypeLabel(value){
    var x = String(value || '').toLowerCase();
    if (x.indexOf('class') !== -1) return 'Class';
    if (x.indexOf('workshop') !== -1) return 'Workshop';
    if (x.indexOf('event') !== -1) return 'Event';
    if (x.indexOf('retreat') !== -1) return 'Retreat';
    if (x.indexOf('gift') !== -1) return 'Gift';
    return 'Therapy';
  }

  function wowUltraGroupLabel(value){
    var x = String(value || '').toLowerCase();
    if (x.indexOf('class') !== -1) return 'Classes';
    if (x.indexOf('workshop') !== -1) return 'Workshops';
    if (x.indexOf('event') !== -1) return 'Events';
    if (x.indexOf('retreat') !== -1) return 'Retreats';
    if (x.indexOf('gift') !== -1) return 'Gifts';
    return 'Therapies';
  }

  function wowUltraMatches(query, item){
    return wowUltraSearchScore(query, item) < 999;
  }

  function wowUltraSearchScore(query, item){
    var q = wowUltraNormalize(query);
    if (!q) return 999;
    var hay = [
      item.title,
      item.cat,
      item.type,
      item.vendor_name,
      item.plan_label,
      item.subtitle,
    ].map(wowUltraNormalize).join(' ');
    var tokens = q.split(/\s+/).filter(Boolean);
    var title = wowUltraNormalize(item.title);
    if (title === q) return 0;
    if (title.indexOf(q) === 0) return 1;
    if (title.split(/\s+/).some(function(token){ return token.indexOf(q) === 0; })) return 2;
    if (title.indexOf(q) !== -1) return 3;
    if (hay.indexOf(q) !== -1) return 4;
    if (tokens.length && tokens.every(function(token){ return hay.indexOf(token) !== -1; })) return 5;
    return 999;
  }

  function wowUltraBuildSearchSource(payload){
    var categories = Array.isArray(payload) ? payload : (payload && Array.isArray(payload.categories) ? payload.categories : []);
    var offerings = Array.isArray(payload && payload.offerings) ? payload.offerings : [];
    var categoryMap = new Map();

    function addUnique(map, key, item){
      var normalized = wowUltraNormalize(key);
      if (!normalized || map.has(normalized)) return;
      map.set(normalized, item);
    }

    categories.forEach(function(cat){
      var catName = (cat && cat.name ? String(cat.name) : '').trim();
      if (catName) {
        addUnique(categoryMap, catName, {
          cat: 'Categories',
          title: catName,
          type: 'Category',
          value: catName,
          search: catName
        });
      }

      (cat && Array.isArray(cat.products) ? cat.products : []).forEach(function(product){
        var title = (product && product.category && product.category.name ? String(product.category.name).trim() : '');
        if (!title) return;
        addUnique(categoryMap, title, {
          cat: 'Categories',
          title: title,
          type: 'Category',
          value: title,
          search: title,
        });
      });
    });

    offerings.forEach(function(offering){
      var offeringCategory = (offering && offering.category && offering.category.name ? String(offering.category.name).trim() : '');
      if (offeringCategory) {
        addUnique(categoryMap, offeringCategory, {
          cat: 'Categories',
          title: offeringCategory,
          type: 'Category',
          value: offeringCategory,
          search: offeringCategory
        });
      }
    });

    return {
      categories: Array.from(categoryMap.values()).sort(function(a, b){ return a.title.localeCompare(b.title); }),
    };
  }

  function wowUltraLoadSearchSource(){
    if (WOW_ULTRA_SEARCH_SOURCE_CACHE) return Promise.resolve(WOW_ULTRA_SEARCH_SOURCE_CACHE);
    if (WOW_ULTRA_SEARCH_SOURCE_PROMISE) return WOW_ULTRA_SEARCH_SOURCE_PROMISE;

    WOW_ULTRA_SEARCH_SOURCE_PROMISE = fetch('/api/catalog?all=true&product_limit=250', { cache: 'no-store' })
      .then(function(res){ if (!res.ok) throw new Error('catalog ' + res.status); return res.json(); })
      .then(function(payload){
        WOW_ULTRA_SEARCH_SOURCE_CACHE = wowUltraBuildSearchSource(payload);
        return WOW_ULTRA_SEARCH_SOURCE_CACHE;
      })
      .catch(function(){
        WOW_ULTRA_SEARCH_SOURCE_CACHE = { categories: [] };
        return WOW_ULTRA_SEARCH_SOURCE_CACHE;
      });

    return WOW_ULTRA_SEARCH_SOURCE_PROMISE;
  }

  // Warm the cache immediately so the autocomplete feels instant once users start typing.
  wowUltraLoadSearchSource();

  function setupUltraSearchBar(prefix){
    var root = document.querySelector('[id^="'+prefix+'-seg-"]')?.closest('.wow-ultra') || document.querySelector('#'+prefix+'-seg-what')?.closest('.wow-ultra');
    // If structure not found, bail
    if(!root) return;
    if (root.dataset.wowUltraBound === '1') return;
    root.dataset.wowUltraBound = '1';

    function byId(s){ return document.getElementById(prefix + '-' + s) }
    var panes = ['what-pane','where-pane','when-pane','who-pane'];
    var WHAT_LIMIT_PER_SECTION = 6;

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
    var whatSource = null;
    var whatSourceReady = false;

    function renderWhat(qs){
      var list = byId('what-list');
      if (!list) return false;

      var query = (qs || '').trim();
      if (!query) {
        list.innerHTML = '';
        return false;
      }

      if (!whatSourceReady) {
        list.innerHTML = '';
        return false;
      }

      var categories = (whatSource && whatSource.categories ? whatSource.categories : []);
      categories = categories
        .map(function(item){ return { item: item, score: wowUltraSearchScore(query, item) }; })
        .filter(function(row){ return row.score < 999; })
        .sort(function(a, b){
          if (a.score !== b.score) return a.score - b.score;
          return String(a.item.title || '').localeCompare(String(b.item.title || ''));
        })
        .slice(0, WHAT_LIMIT_PER_SECTION)
        .map(function(row){ return row.item; });

      if (!categories.length) {
        list.innerHTML = '';
        hideAll();
        return false;
      }

      var html = '<div class="section-title">Categories</div><div>';
      categories.forEach(function(item){
        html += '<button type="button" class="item" role="option" data-value="' + wowUltraEscapeHtml(item.value || item.title || '') + '">'
          + '<i class="bi bi-tag"></i>'
          + '<span class="title">' + wowUltraEscapeHtml(item.title || '') + '</span>'
          + '<span class="text-muted ms-2">Category</span>'
          + '</button>';
      });
      html += '</div>';
      list.innerHTML = html;
      return true;
    }

    function refreshWhat(){
      var qs = whatInput ? whatInput.value : '';
      var hasResults = renderWhat(qs);
      if (hasResults) {
        openPane('what');
      } else {
        hideAll();
      }
    }

    wowUltraLoadSearchSource().then(function(source){
      whatSource = source;
      whatSourceReady = true;
      if (whatInput && (whatInput.value || '').trim() && renderWhat(whatInput.value) && document.activeElement === whatInput) {
        openPane('what');
      }
    });

    if(whatInput){
      whatInput.addEventListener('focus', function(e){ refreshWhat(); });
      whatInput.addEventListener('input', function(e){ refreshWhat(); });
      var segWhat = byId('seg-what');
      if(segWhat){ segWhat.addEventListener('click', function(){ refreshWhat(); }) }
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

    // Shared Who controls: adults counter + group type selection
    (function initWhoControls(){
      var pane = byId('who-pane');
      var adultsEl = byId('adults-val');
      var groupList = byId('group-type-list') || byId('group-type-list') || document.getElementById(prefix + '-group-type-list');
      var summaryEl = byId('who-summary');
      if (!pane || !adultsEl || pane.dataset.wowWhoBound === '1') return;

      var groupTouched = false;

      function clampAdults(n){
        var num = Number(n);
        if (!Number.isFinite(num)) return 0;
        return Math.max(0, Math.min(20, Math.round(num)));
      }

      function getAdults(){
        return clampAdults((adultsEl.textContent || adultsEl.value || '0').trim());
      }

      function setGroupSelection(name){
        if (!groupList) return;
        var target = name == null ? '' : String(name || '');
        Array.from(groupList.querySelectorAll('[data-group]')).forEach(function(btn){
          var isMatch = String(btn.getAttribute('data-group')) === target;
          btn.setAttribute('aria-selected', isMatch ? 'true' : 'false');
        });
      }

      function groupForAdults(n){
        if (n <= 0) return '';
        if (n === 1) return 'Solo';
        if (n === 2) return 'Couple';
        return 'Group';
      }

      function getSelectedGroup(){
        if (!groupList) return '';
        var sel = groupList.querySelector('[data-group][aria-selected="true"]');
        return (sel?.getAttribute?.('data-group') || '').trim();
      }

      function updateSummary(){
        if (!summaryEl) return;
        var adults = getAdults();
        var group = getSelectedGroup();
        var parts = [];
        if (adults > 0) parts.push(adults + ' ' + (adults === 1 ? 'adult' : 'adults'));
        if (group) parts.push(group);
        summaryEl.textContent = parts.length ? parts.join(' · ') : 'Add guests';
      }

      function applyAdults(n){
        var next = clampAdults(n);
        adultsEl.textContent = String(next);
        if (!groupTouched) setGroupSelection(groupForAdults(next));
        updateSummary();
      }

      pane.addEventListener('click', function(event){
        var dec = event.target.closest('[data-dec="adults"]');
        var inc = event.target.closest('[data-inc="adults"]');
        if (!dec && !inc) return;
        event.preventDefault();
        var current = getAdults();
        applyAdults(current + (inc ? 1 : -1));
      });

      if (groupList) {
        groupList.addEventListener('click', function(event){
          var btn = event.target.closest('[data-group]');
          if (!btn) return;
          var group = (btn.getAttribute('data-group') || '').trim();
          if (!group) return;
          groupTouched = true;
          if (group === 'Solo') {
            setGroupSelection('Solo');
            applyAdults(1);
          } else if (group === 'Couple') {
            setGroupSelection('Couple');
            applyAdults(2);
          } else {
            setGroupSelection('Group');
            applyAdults(Math.max(3, getAdults() || 3));
          }
        });
      }

      updateSummary();
      pane.dataset.wowWhoBound = '1';
    })();
    // Ensure panes start closed on load
    try{ hideAll(); } catch(_){ }

    // Submit handler → build /search URL
    try {
      var form = root.closest('form') || root.querySelector('form') || document.querySelector('.wow-ultra form.bar');
      if(form){
        form.addEventListener('submit', function(e){
          try { e.preventDefault(); } catch(_) {}
          var whatEl = byId('what');
          var what = (whatEl && whatEl.value ? whatEl.value : '').trim();
          if(!what){
            try {
              if (whatEl) {
                whatEl.setCustomValidity('Please enter what you want to search for.');
                whatEl.reportValidity();
                whatEl.focus();
                whatEl.setCustomValidity('');
              }
            } catch(_e) {}
            return;
          }
          var params = new URLSearchParams();
          // what
          if(what) params.set('what', what);
          // where
          var whereHidden = byId('where');
          var whereText = byId('where-editor')?.textContent?.trim();
          var where = (whereHidden && whereHidden.value) ? whereHidden.value : (whereText || '');
          if(where) params.set('where', where);
          // when (as-is string)
          var whenEl = byId('when');
          var when = whenEl?.value?.trim();
          if(when) params.set('when', when);
          if (whenEl?.dataset?.rangeStart) params.set('when_start', whenEl.dataset.rangeStart);
          if (whenEl?.dataset?.rangeEnd) params.set('when_end', whenEl.dataset.rangeEnd);
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

  try { window.setupUltraSearchBar = setupUltraSearchBar; } catch (_) {}

  // Initialize bars present on the page
  ['home-template','home-sticky','search-top','header-search'].forEach(function(prefix){
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
      function syncHamburger(state){
        try{
          if (window.__WOWHamburger && typeof window.__WOWHamburger.set === 'function') {
            window.__WOWHamburger.set(state);
          } else {
            window.__WOWHamburgerQueue = window.__WOWHamburgerQueue || [];
            window.__WOWHamburgerQueue.push(state);
          }
        }catch(_err){}
      }
      var open = false;
      function closeMobile(){ mobile.style.display = 'none'; burger.classList.remove('opened'); burger.setAttribute('aria-expanded','false'); setBodyScroll(false); syncHamburger(false); open = false; }
      function openMobile(){
        try {
          if (typeof window.__WOWCloseMobileSearch === 'function') {
            window.__WOWCloseMobileSearch();
          }
          const searchDrawer = document.getElementById('mobile-search-drawer');
          if (searchDrawer) {
            searchDrawer.classList.remove('is-visible');
            searchDrawer.setAttribute('aria-hidden', 'true');
          }
          const searchTrigger = document.querySelector('[data-mobile-search-trigger]');
          if (searchTrigger) {
            searchTrigger.classList.remove('is-open');
            searchTrigger.setAttribute('aria-expanded', 'false');
            searchTrigger.setAttribute('aria-label', 'Search');
            const searchIcon = searchTrigger.querySelector('.mobile-search-trigger__icon--search');
            const closeIcon = searchTrigger.querySelector('.mobile-search-trigger__icon--close');
            if (searchIcon) searchIcon.hidden = false;
            if (closeIcon) closeIcon.hidden = true;
          }
        } catch(_err){}
        mobile.style.display = 'block';
        burger.classList.add('opened');
        burger.setAttribute('aria-expanded','true');
        setBodyScroll(true);
        syncHamburger(true);
        open = true;
      }
      burger.addEventListener('click', function(){ open ? closeMobile() : openMobile(); });
      document.addEventListener('keydown', function(e){ if(e.key==='Escape' && open){ closeMobile(); }});
      mobile.addEventListener('click', function(e){ var a = e.target.closest('a'); if(a){ closeMobile(); }});
      // Close if window resized to desktop
      window.addEventListener('resize', function(){ if(window.innerWidth >= 768 && open){ closeMobile(); }});
    }
  } catch {}
})();
</script>
<script>
(function(){
  const COOKIE_KEY = 'wow_cookie_preferences';
  const NEED_HISTORY_KEY = 'wow_need_history';
  const THERAPY_HISTORY_KEY = 'wow_therapy_history';
  const THERAPY_SAVED_KEY = 'wow_saved_therapies';

  const needNodes = {
    popular: document.querySelector('[data-need-default-popular]'),
    trending: document.querySelector('[data-need-default-trending]'),
    defaultBlock: document.querySelector('[data-need-default-block]'),
    personalizedBlock: document.querySelector('[data-need-personalized-block]'),
    continueList: document.querySelector('[data-need-continue]'),
    recommendedList: document.querySelector('[data-need-recommended]')
  };

  const therapyNodes = {
    popular: document.querySelector('[data-therapy-popular-list]'),
    defaultBlock: document.querySelector('[data-therapy-default-block]'),
    personalizedBlock: document.querySelector('[data-therapy-personalized-block]'),
    defaultPopular: document.querySelector('[data-therapy-default-popular]'),
    recentList: document.querySelector('[data-therapy-recent]'),
    savedList: document.querySelector('[data-therapy-saved]')
  };

  const needDefaults = {
    popular: [
      { slug: 'stress-and-anxiety', title: 'Stress & anxiety', url: '/needs/stress-and-anxiety' },
      { slug: 'sleep-issues', title: 'Sleep issues', url: '/needs/sleep-issues' },
      { slug: 'low-mood-burnout', title: 'Low mood & burnout', url: '/needs/low-mood-burnout' },
      { slug: 'overwhelm', title: 'Overwhelm & frazzled feelings', url: '/needs/overwhelm' },
      { slug: 'worry', title: 'Worry & racing thoughts', url: '/needs/worry' },
      { slug: 'pain-management', title: 'Pain, tension & tightness', url: '/needs/pain-management' },
    ],
    trending: [
      { slug: 'online-breathwork', title: 'Trending: Online breathwork', url: '/needs/breathwork' },
      { slug: 'guided-meditation', title: 'Guided meditation & sound', url: '/needs/guided-meditation' },
      { slug: 'corporate-wellbeing', title: 'Corporate wellbeing boosters', url: '/needs/corporate-wellbeing' },
    ]
  };

  const therapyDefaults = {
    pinned: [
      { title: 'Massage therapy', url: '/therapy/massage', id: null },
      { title: 'Reiki', url: '/therapy/reiki', id: null },
      { title: 'Reflexology', url: '/therapy/reflexology', id: null },
      { title: 'Acupuncture', url: '/therapy/acupuncture', id: null },
      { title: 'Breathwork (1:1)', url: '/therapy/breathwork', id: null },
      { title: 'Hypnotherapy', url: '/therapy/hypnotherapy', id: null },
      { title: 'Coaching & counselling', url: '/therapy/coaching-and-counselling', id: null },
      { title: 'Sound healing', url: '/therapy/sound-healing', id: null },
    ],
    rotation: [
      { title: 'Somatic experiencing', url: '/therapy/somatic-experiencing', id: null },
      { title: 'Craniosacral therapy', url: '/therapy/craniosacral-therapy', id: null },
      { title: 'Lymphatic drainage', url: '/therapy/lymphatic-drainage', id: null },
      { title: 'Corporate desk reset', url: '/therapy/corporate-wellness', id: null },
    ],
    defaultColumn: [
      { title: 'Massage therapy', url: '/therapy/massage', id: null },
      { title: 'Reiki', url: '/therapy/reiki', id: null },
      { title: 'Reflexology', url: '/therapy/reflexology', id: null },
      { title: 'Acupuncture', url: '/therapy/acupuncture', id: null },
      { title: 'Breathwork (1:1)', url: '/therapy/breathwork', id: null },
    ]
  };

  function readStorageArray(key) {
    try {
      const raw = localStorage.getItem(key);
      const data = raw ? JSON.parse(raw) : [];
      return Array.isArray(data) ? data : [];
    } catch (_err) {
      return [];
    }
  }

  function readCookiePrefs() {
    try {
      const raw = localStorage.getItem(COOKIE_KEY);
      const data = raw ? JSON.parse(raw) : null;
      return (data && typeof data === 'object') ? data : null;
    } catch (_err) {
      return null;
    }
  }

  function canPersonalize() {
    const prefs = readCookiePrefs();
    return !!(prefs && prefs.personalization === true);
  }

  function renderLinks(target, items, options = {}) {
    if (!target) return;
    const fallback = options.fallback || 'No suggestions yet';
    if (!items || !items.length) {
      target.innerHTML = `<li><span class="menu-link menu-link--disabled">${fallback}</span></li>`;
      return;
    }
    const cartIds = options.cartIds || new Set();
    target.innerHTML = items.map((item) => {
      if (!item || !item.title) return '';
      const id = deriveId(item);
      const badge = (options.showBasket && id && cartIds.has(id)) ? '<span class="menu-pill">In basket</span>' : '';
      return `<li><a class="menu-link" href="${item.url}">${item.title}${badge}</a></li>`;
    }).join('');
  }

  function deriveId(entry) {
    if (!entry) return null;
    if (entry.id) return String(entry.id);
    if (entry.url) {
      const match = entry.url.match(/\/([0-9]+)-/);
      if (match) return match[1];
    }
    return null;
  }

  function readCartIds() {
    try {
      const cookie = document.cookie.split(';').map(row => row.trim()).find(row => row.startsWith('wow_cart='));
      if (!cookie) return new Set();
      const payload = JSON.parse(decodeURIComponent(cookie.split('=')[1] || '[]'));
      const ids = new Set();
      if (Array.isArray(payload)) {
        payload.forEach((item) => {
          if (item && item.id) ids.add(String(item.id));
        });
      } else if (payload && typeof payload === 'object') {
        Object.keys(payload).forEach((key) => {
          const line = payload[key];
          const id = line && (line.id || key);
          if (id) ids.add(String(id));
        });
      }
      return ids;
    } catch (_err) {
      return new Set();
    }
  }

  function updateNeedColumn() {
    if (!needNodes.popular) return;
    renderLinks(needNodes.popular, needDefaults.popular);
    renderLinks(needNodes.trending, needDefaults.trending);
    const history = readStorageArray(NEED_HISTORY_KEY);
    const allowPersonalization = canPersonalize() && history.length;
    if (!allowPersonalization) {
      if (needNodes.defaultBlock) needNodes.defaultBlock.hidden = false;
      if (needNodes.personalizedBlock) {
        needNodes.personalizedBlock.hidden = true;
        needNodes.personalizedBlock.setAttribute('aria-hidden', 'true');
      }
      return;
    }

    if (needNodes.defaultBlock) needNodes.defaultBlock.hidden = true;
    if (needNodes.personalizedBlock) {
      needNodes.personalizedBlock.hidden = false;
      needNodes.personalizedBlock.setAttribute('aria-hidden', 'false');
    }

    const continueItems = history.slice(0, 3);
    renderLinks(needNodes.continueList, continueItems);
    const recommendedPool = needDefaults.popular.concat(needDefaults.trending);
    const recommended = recommendedPool.filter(item => continueItems.every(entry => entry.slug !== item.slug)).slice(0, 3);
    renderLinks(needNodes.recommendedList, (recommended.length ? recommended : needDefaults.popular.slice(0, 3)));
  }

  function buildTherapyPopular() {
    const list = therapyNodes.popular;
    if (!list) return;
    const base = therapyDefaults.pinned.slice(0, 6);
    const pool = therapyDefaults.rotation.length ? therapyDefaults.rotation : therapyDefaults.pinned.slice(6);
    const seed = pool.length ? Math.floor(Date.now() / (1000 * 60 * 60 * 24)) : 0;
    const rotation = [];
    for (let i = 0; i < 2; i++) {
      if (!pool.length) break;
      rotation.push(pool[(seed + i) % pool.length]);
    }
    const combined = base.concat(rotation);
    renderLinks(list, combined, { cartIds: readCartIds(), showBasket: true });
  }

  function updateTherapyColumn() {
    if (!therapyNodes.defaultBlock) return;
    buildTherapyPopular();
    renderLinks(therapyNodes.defaultPopular, therapyDefaults.defaultColumn, { cartIds: readCartIds(), showBasket: true });
    const history = readStorageArray(THERAPY_HISTORY_KEY);
    const saved = readStorageArray(THERAPY_SAVED_KEY).slice(0, 4);
    const allowPersonalization = canPersonalize() && (history.length || saved.length);
    if (!allowPersonalization) {
      if (therapyNodes.defaultBlock) therapyNodes.defaultBlock.hidden = false;
      if (therapyNodes.personalizedBlock) {
        therapyNodes.personalizedBlock.hidden = true;
        therapyNodes.personalizedBlock.setAttribute('aria-hidden', 'true');
      }
      return;
    }

    if (therapyNodes.defaultBlock) therapyNodes.defaultBlock.hidden = true;
    if (therapyNodes.personalizedBlock) {
      therapyNodes.personalizedBlock.hidden = false;
      therapyNodes.personalizedBlock.setAttribute('aria-hidden', 'false');
    }

    const cartIds = readCartIds();
    renderLinks(therapyNodes.recentList, history.slice(0, 4), { cartIds, showBasket: true, fallback: 'No history yet' });
    renderLinks(therapyNodes.savedList, saved.slice(0, 4), { cartIds, showBasket: true, fallback: 'No saved therapies' });
  }

  function runAll(){
    updateNeedColumn();
    updateTherapyColumn();
  }

  document.addEventListener('wow:cookie-preferences', runAll);
  document.addEventListener('wow:need-history', runAll);
  document.addEventListener('wow:therapy-history', runAll);
  document.addEventListener('wow:saved-therapies', runAll);
  document.addEventListener('wow:cart-updated', runAll);
  window.addEventListener('storage', runAll);
  window.addEventListener('focus', runAll);
  runAll();
})();
</script>
@stack('scripts')

</body>
</html>
