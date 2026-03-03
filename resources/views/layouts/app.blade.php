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
        src="https://testing.studio.weofferwellness.co.uk/storage/uploads/images/fef435d7-4888-4cbe-b961-ba7e31bc183d.png?v=3"
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

    var whoSummaryEl = byId('who-summary');
    var adultsValEl = byId('adults-val');
    var whoPane = byId('who-pane');
    var groupList = document.getElementById(prefix + '-group-type-list');
    var whoState = { adults: 0, group: null, explicitGroup: false };

    (function initWhoState(){
      try {
        var params = new URLSearchParams(window.location.search || '');
        var adultsParam = parseInt(params.get('adults'), 10);
        if(Number.isFinite(adultsParam) && adultsParam > 0){ whoState.adults = adultsParam; }
        var groupParam = params.get('group_type');
        if(groupParam){
          whoState.group = normalizeGroup(groupParam);
          whoState.explicitGroup = !!whoState.group;
        }
      } catch(_err) {}
    })();

    function normalizeGroup(group){
      if(!group) return null;
      var key = String(group).trim().toLowerCase();
      if(!key) return null;
      if(key === 'solo') return 'Solo';
      if(key === 'couple') return 'Couple';
      return 'Group';
    }

    function autoGroupFromAdults(){
      if(!whoState.adults || whoState.adults <= 0) return null;
      if(whoState.adults === 1) return 'Solo';
      if(whoState.adults === 2) return 'Couple';
      return 'Group';
    }

    function currentGroup(){
      if(whoState.explicitGroup && whoState.group) return whoState.group;
      return autoGroupFromAdults();
    }

    function syncGroupButtons(active){
      if(!groupList) return;
      groupList.querySelectorAll('.item').forEach(function(btn){
        var isActive = !!active && normalizeGroup(btn.getAttribute('data-group')) === active;
        btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
      });
    }

    function updateWhoSummary(){
      var group = currentGroup();
      syncGroupButtons(group);
      if(adultsValEl){ adultsValEl.textContent = String(whoState.adults || 0); }
      if(!whoSummaryEl) return;
      if(whoState.adults && whoState.adults > 0){
        var text = whoState.adults + ' adult' + (whoState.adults > 1 ? 's' : '');
        if(group){ text += ' · ' + group; }
        whoSummaryEl.textContent = text;
        whoSummaryEl.classList.remove('is-placeholder');
      } else {
        whoSummaryEl.textContent = 'Add guests';
        whoSummaryEl.classList.add('is-placeholder');
      }
    }

    function setAdults(next){
      var value = Math.max(0, Number(next) || 0);
      whoState.adults = value;
      if(value === 0){
        whoState.explicitGroup = false;
        whoState.group = null;
      }
      updateWhoSummary();
    }

    function setGroup(group, explicit){
      var normalized = normalizeGroup(group);
      whoState.group = normalized;
      whoState.explicitGroup = !!(explicit && normalized);
      updateWhoSummary();
    }

    if(groupList){
      groupList.addEventListener('click', function(e){
        var btn = e.target.closest('.item');
        if(!btn) return;
        e.preventDefault();
        setGroup(btn.getAttribute('data-group'), true);
      });
    }

    if(whoPane){
      whoPane.addEventListener('click', function(e){
        var inc = e.target.closest('[data-inc="adults"]');
        var dec = e.target.closest('[data-dec="adults"]');
        if(inc){
          e.preventDefault();
          setAdults((whoState.adults || 0) + 1);
        } else if(dec){
          e.preventDefault();
          setAdults((whoState.adults || 0) - 1);
        }
      });
    }

    updateWhoSummary();

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
      function openMobile(){ mobile.style.display = 'block'; burger.classList.add('opened'); burger.setAttribute('aria-expanded','true'); setBodyScroll(true); syncHamburger(true); open = true; }
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
