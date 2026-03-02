import { gsap } from 'gsap';
import { initSubscriberForms } from './lib/subscriber-forms';

function runIdle(fn) {
  try {
    if (typeof window !== 'undefined' && 'requestIdleCallback' in window) {
      window.requestIdleCallback(fn, { timeout: 300 });
      return;
    }
  } catch (_err) {}
  setTimeout(fn, 1);
}

// Minimal interactivity for header mega menu, mobile menu, and ultra search bar panes

function createHamburgerTimeline(button){
  if (!button || typeof window === 'undefined') return null;
  try {
    const timeline = gsap.timeline({ paused: true });
    timeline.set(button, { '--origin': 'right center' });
    timeline.to(button, {
      '--before-scale': 1,
      '--after-scale': 1,
      duration: 0.1,
      ease: 'power2.out'
    });
    timeline.to(button, {
      '--span-scale': 0,
      '--before-top': '12px',
      '--after-top': '12px',
      duration: 0.15,
      ease: 'power2.inOut'
    }, '<0.1');
    timeline.set(button, { '--origin': 'center center' });
    timeline.to(button, {
      '--before-rot': '45deg',
      '--after-rot': '-45deg',
      duration: 0.15,
      ease: 'power3.out'
    }, '>0.1');
    return timeline;
  } catch(_err) {
    return null;
  }
}

function setupHamburgerController(button){
  const timeline = createHamburgerTimeline(button);
  if (!timeline) return null;
  let state = false;
  const controller = {
    set(open){
      const next = Boolean(open);
      if (next === state) return;
      state = next;
      if (next) { timeline.play(); }
      else { timeline.reverse(); }
    },
    toggle(){ this.set(!state); },
    isOpen(){ return state; }
  };
  try {
    window.__WOWHamburger = controller;
    if (Array.isArray(window.__WOWHamburgerQueue) && window.__WOWHamburgerQueue.length) {
      window.__WOWHamburgerQueue.forEach((queuedState) => {
        try { controller.set(queuedState); } catch(_inner){}
      });
      window.__WOWHamburgerQueue = [];
    }
    window.dispatchEvent(new CustomEvent('wow:hamburger-ready', { detail: controller }));
  } catch(_){ }
  return controller;
}

function initMegaMenu() {
  const header = document.querySelector('header');
  const panel = document.getElementById('mega-panel');
  const overlay = document.getElementById('mega-overlay');
  if (!header || !panel) return;

  const focusableSelector = 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';
  const delayOpen = 320;
  const delayClose = 200;
  let openTimer = null;
  let closeTimer = null;
  let activeMenu = '';
  let lastTrigger = null;

  const escapeKey = (key) => {
    if (!key) return '';
    try {
      return typeof CSS !== 'undefined' && CSS.escape ? CSS.escape(key) : key;
    } catch (_) {
      return key;
    }
  };

  const getMenuBlock = (key) => {
    if (!key) return null;
    return panel.querySelector(`[data-menu="${escapeKey(key)}"]`);
  };

  const showPanel = (key) => {
    activeMenu = key;
    panel.setAttribute('data-active', key || '');
    panel.style.display = 'block';
    if (overlay) overlay.style.display = 'block';
    if (lastTrigger) {
      try { lastTrigger.setAttribute('aria-expanded', 'true'); } catch (_) {}
    }
  };

  const hidePanel = (focusTrigger = false) => {
    activeMenu = '';
    panel.removeAttribute('data-active');
    panel.style.display = 'none';
    if (overlay) overlay.style.display = 'none';
    if (lastTrigger) {
      try { lastTrigger.setAttribute('aria-expanded', 'false'); } catch (_) {}
    }
    if (focusTrigger && lastTrigger) {
      try { lastTrigger.focus(); } catch (_) {}
    }
  };

  const clearTimers = () => {
    if (openTimer) { window.clearTimeout(openTimer); openTimer = null; }
    if (closeTimer) { window.clearTimeout(closeTimer); closeTimer = null; }
  };

  const scheduleClose = (immediate = false) => {
    clearTimers();
    if (immediate) {
      hidePanel();
    } else {
      closeTimer = window.setTimeout(() => hidePanel(), delayClose);
    }
  };

  const focusFirstItem = (key) => {
    const block = getMenuBlock(key);
    if (!block) return;
    const target = block.querySelector(focusableSelector);
    if (target) {
      try { target.focus(); } catch (_) {}
    }
  };

  const openMenu = (key, trigger, { immediate = false, focusContent = false } = {}) => {
    const block = getMenuBlock(key);
    if (!block) return;
    lastTrigger = trigger || lastTrigger;
    clearTimers();
    const run = () => {
      showPanel(key);
      if (focusContent) {
        focusFirstItem(key);
      }
    };
    if (immediate) {
      run();
    } else {
      openTimer = window.setTimeout(run, delayOpen);
    }
  };

  const navLinks = header.querySelectorAll('[data-mega-menu]');
  navLinks.forEach((link) => {
    const key = link.getAttribute('data-mega-menu');
    if (!getMenuBlock(key)) return;

    link.setAttribute('aria-haspopup', 'true');
    link.setAttribute('aria-expanded', 'false');

    link.addEventListener('mouseenter', () => openMenu(key, link));
    link.addEventListener('focus', () => openMenu(key, link, { immediate: true }));
    link.addEventListener('mouseleave', () => scheduleClose());

    link.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        openMenu(key, link, { immediate: true, focusContent: true });
      } else if (event.key === 'ArrowDown') {
        event.preventDefault();
        openMenu(key, link, { immediate: true, focusContent: true });
      } else if (event.key === 'Escape') {
        event.preventDefault();
        clearTimers();
        hidePanel(true);
      }
    });
  });

  // Links without a mega menu should not trigger the frosted overlay.
  try {
    const simpleLinks = header.querySelectorAll('.nav-item > a:not([data-mega-menu])');
    simpleLinks.forEach((a) => {
      a.addEventListener('mouseenter', () => scheduleClose(true));
      a.addEventListener('focus', () => scheduleClose(true));
    });
  } catch (_) {}

  const panelKeyHandler = (event) => {
    if (!activeMenu) return;
    if (event.key === 'Escape') {
      event.preventDefault();
      clearTimers();
      hidePanel(true);
      return;
    }
    if (!['ArrowDown', 'ArrowUp', 'ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) {
      return;
    }
    const block = getMenuBlock(activeMenu);
    if (!block) return;
    const focusable = Array.from(block.querySelectorAll(focusableSelector));
    if (!focusable.length) return;
    const currentIndex = focusable.indexOf(document.activeElement);
    let nextIndex = currentIndex;
    if (event.key === 'ArrowDown' || event.key === 'ArrowRight') {
      nextIndex = currentIndex + 1;
    } else if (event.key === 'ArrowUp' || event.key === 'ArrowLeft') {
      nextIndex = currentIndex - 1;
    } else if (event.key === 'Home') {
      nextIndex = 0;
    } else if (event.key === 'End') {
      nextIndex = focusable.length - 1;
    }
    if (nextIndex < 0) nextIndex = focusable.length - 1;
    if (nextIndex >= focusable.length) nextIndex = 0;
    const target = focusable[nextIndex];
    if (target) {
      event.preventDefault();
      try { target.focus(); } catch (_) {}
    }
  };

  panel.addEventListener('mouseenter', () => {
    if (closeTimer) { window.clearTimeout(closeTimer); closeTimer = null; }
  });
  panel.addEventListener('mouseleave', () => scheduleClose());
  panel.addEventListener('keydown', panelKeyHandler);

  header.addEventListener('mouseleave', () => scheduleClose());
  document.addEventListener('click', (e) => {
    if (!header.contains(e.target)) {
      scheduleClose(true);
    }
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && activeMenu) {
      clearTimers();
      hidePanel(true);
    }
  });
}

function initMobileMenu() {
  const toggle = document.querySelector('header button[aria-label="Toggle menu"]');
  if (!toggle) return;
  setupHamburgerController(toggle);
}

function setupUltraSearchBar(prefix) {
  // Find the specific ultra-search container for this prefix
  const root = (
    document.querySelector(`#${prefix}-root`) ||
    document.querySelector(`#${prefix}-seg-what`)?.closest('.wow-ultra') ||
    document.getElementById(`${prefix}-what`)?.closest('.wow-ultra') ||
    null
  );
  function byId(s){ return document.getElementById(prefix + '-' + s); }
  const panes = ['what-pane','where-pane','when-pane','who-pane'];
  function hideAll(){ panes.forEach((id) => { const el = byId(id); if (el) el.classList.add('d-none'); }); const what = byId('what'); if (what) what.setAttribute('aria-expanded','false'); }
  function openPane(which){ hideAll(); const pane = byId(which+'-pane'); if(pane){ pane.classList.remove('d-none'); } if(which==='what'){ const what = byId('what'); if(what) what.setAttribute('aria-expanded','true'); } }
  const whatInput = byId('what'); if(whatInput){ whatInput.addEventListener('focus', ()=>openPane('what')); whatInput.addEventListener('input', ()=>openPane('what')); const segWhat = byId('seg-what'); if(segWhat){ segWhat.addEventListener('click', ()=>openPane('what')); } }
  const whereEditor = byId('where-editor'); if(whereEditor){ whereEditor.addEventListener('focus', ()=>openPane('where')); whereEditor.addEventListener('click', ()=>openPane('where')); }
  const whenInput = byId('when'); if(whenInput){ whenInput.addEventListener('focus', ()=>openPane('when')); whenInput.addEventListener('click', ()=>openPane('when')); }
  const whoSeg = byId('seg-who'); if(whoSeg){ whoSeg.addEventListener('click', ()=>openPane('who')); }
  // Close only when clicking outside this specific bar
  document.addEventListener('click', (e)=>{ if(root && !root.contains(e.target)) hideAll(); });
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') hideAll(); });
  const whatList = byId('what-list'); if(whatList && byId('what')){ whatList.addEventListener('click', (e)=>{ const btn = e.target.closest('.item'); if(btn && btn.dataset.value){ byId('what').value = btn.dataset.value; hideAll(); byId('what').blur(); } }); }
  const whereHidden = byId('where'); if(byId('where-list') && whereEditor){ byId('where-list').addEventListener('click', (e)=>{ const btn = e.target.closest('.item'); if(btn && btn.dataset.value){ whereEditor.textContent = btn.dataset.value; if(whereHidden) whereHidden.value = btn.dataset.value; hideAll(); } }); }
  const whoDone = byId('who-done'); if(whoDone){ whoDone.addEventListener('click', ()=>hideAll()); }
}

function initAccountDropdown() {
  const wrap = document.querySelector('.account-wrap');
  const trigger = wrap?.querySelector('.account-trigger');
  const panel = wrap?.querySelector('.account-dropdown');
  if (!wrap || !trigger || !panel) return;

  const isDesktop = () => {
    try { return window.matchMedia('(min-width: 992px)').matches; } catch (_) { return true; }
  };

  let hideTimer = null;

  function openPanel() {
    panel.hidden = false;
    panel.classList.add('show');
    trigger.setAttribute('aria-expanded', 'true');
  }

  function closePanel() {
    panel.hidden = true;
    panel.classList.remove('show');
    trigger.setAttribute('aria-expanded', 'false');
  }

  wrap.addEventListener('mouseenter', () => {
    if (!isDesktop()) return;
    if (hideTimer) { clearTimeout(hideTimer); hideTimer = null; }
    openPanel();
  });

  wrap.addEventListener('mouseleave', () => {
    if (!isDesktop()) return;
    hideTimer = setTimeout(closePanel, 120);
  });

  trigger.addEventListener('click', (e) => {
    e.preventDefault();
    if (isDesktop()) {
      openPanel();
    } else {
      panel.hidden ? openPanel() : closePanel();
    }
  });

  document.addEventListener('click', (e) => {
    if (!wrap.contains(e.target)) {
      closePanel();
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closePanel();
    }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  runIdle(() => { try { initMegaMenu(); } catch (e) {} });
  runIdle(() => { try { initMobileMenu(); } catch (e) {} });
  runIdle(() => { try { ['home-template','home-sticky'].forEach(prefix => setupUltraSearchBar(prefix)); } catch (e) {} });
  runIdle(() => { try { initAccountDropdown(); } catch (e) {} });
  runIdle(() => { try { initSubscriberForms(); } catch (e) {} });

  // Cart dropdown: hover on desktop shows mini cart; mobile click navigates
  const wrap = document.querySelector('.cart-wrap');
  const link = document.querySelector('.cart-wrap .cart-link');
  const panel = document.getElementById('cart-dropdown');
  if (wrap && link && panel) {
    function isDesktop(){ try { return window.matchMedia('(min-width: 992px)').matches } catch(_) { return true } }
    let loaded = false; let hideTimer = null;
    function money(n){ try{ var x=Number(n); if(x>=1000) x=x/100; return '£'+x.toFixed(2) }catch(_){ return '£0.00' } }
      function updateTotals(items){
        try{
          var sub = 0, count = 0;
          (items||[]).forEach(function(it){ var p = Number(it.price||0); if(p>=1000) p=p/100; var q = Number(it.qty||1)||1; sub += p*q; count += q; });
          var subEl = panel.querySelector('#cartdd-subtotal'); if(subEl) subEl.textContent = money(sub);
          var label = panel.querySelector('#cartCountLabel'); if(label) label.textContent = count>0 ? (count===1?'1 item':(count+' items')) : '';
          var hint = panel.querySelector('#freeShipHint'); if(hint){ try{ hint.textContent = 'Instant delivery'; }catch(_e){} }
        }catch(_){ }
      }
    function renderItems(items){
      try{
        const body = panel.querySelector('#cartdd-body');
        if(!body) return;
        if(!Array.isArray(items) || items.length===0){ body.innerHTML = '<div class="cartdd-empty mini-cart__empty">Your cart is empty</div>'; updateTotals([]); updateBadgeFrom([]); return; }
        body.innerHTML = items.map(function(it){
          var img = it.image ? '<div class="cartdd-img"><img src="'+String(it.image).replace(/"/g,'&quot;')+'" alt=""></div>' : '<div class="cartdd-img"></div>';
          var title = String(it.title||'').replace(/[&<>"']/g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[c]));
          var qty = Number(it.qty||1);
          var price = (typeof it.price!== 'undefined') ? money(it.price) : '';
          var amt = (it.price!=null) ? money((Number(it.price)||0) * qty) : '';
          var removeBtn = '<button class="cartdd-remove remove-btn js-remove" type="button" aria-label="Remove item" data-remove="'+String(it.id||'')+'">'
            + '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">'
            +   '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6" />'
            + '</svg>'
            + '</button>';
          return '<div class="cartdd-item" data-id="'+String(it.id||'')+'">'
            + img
            + '<div class="cartdd-info"><a class="cartdd-title" href="'+(it.url||'#')+'">'+title+'</a><div class="cartdd-meta">Qty '+qty+(price?(' • '+price+' each'):'')+'</div></div>'
            + '<div class="cartdd-amt">'+amt+'</div>'
            + removeBtn
            + '</div>';
        }).join('');
        updateTotals(items);
        updateBadgeFrom(items);
      }catch(_){ /* no-op */ }
    }
    function updateBadgeFrom(items){
      try{
        var badges = document.querySelectorAll('.cart-badge');
        if(!badges.length) return;
        var c=0; (items||[]).forEach(function(it){ c += Number(it.qty||1)||1; });
        badges.forEach(function(b){
          try{
            b.textContent=String(c);
            b.style.display=c>0?'inline-block':'none';
          }catch(_inner){}
        });
      }catch(_){ }
    }
    function readLocalCart(){
      try{
        var raw = localStorage.getItem('wow_cart'); if(!raw) return [];
        var data = JSON.parse(raw);
        var items = data && (data.items||data.cart||data) || [];
        if(!Array.isArray(items)) return [];
        return items.map(function(x){ return { title:x.title||x.name, qty:Number(x.qty||x.quantity||1), price:x.price_min||x.price, image:x.image||x.img, url:x.url||x.href, id:x.id } });
      }catch(_){ return []; }
    }
    function writeLocalCart(items){
      try{
        var bag={ items: items||[] };
        (items||[]).forEach(function(it){ if(it && typeof it.id!=='undefined'){ bag[String(it.id)] = it; } });
        localStorage.setItem('wow_cart', JSON.stringify(bag));
      }catch(_){ }
      try {
        var cookieObj = {};
        (items||[]).forEach(function(it){
          if(!it || typeof it.id === 'undefined') return;
          var id = String(it.id);
          cookieObj[id] = {
            id: it.id,
            title: it.title || '',
            price: Number(it.price || it.unit || 0),
            qty: Number(it.qty || 1) || 1,
            image: it.image || it.img || '',
            url: it.url || '#'
          };
        });
        document.cookie = 'wow_cart=' + encodeURIComponent(JSON.stringify(cookieObj)) + '; Path=/; Max-Age=' + (60*60*24*30) + '; SameSite=Lax';
      } catch(_){ }
      try { window.dispatchEvent(new CustomEvent('wow:cart:change', { detail:{ items: items||[], source:'header:write' } })); } catch(_){ }
    }
    function removeFromLocalCart(id){
      try{
        var items=readLocalCart().filter(function(it){ return String(it.id)!==String(id); });
        writeLocalCart(items);
        try { window.dispatchEvent(new CustomEvent('wow:cart:change', { detail:{ items: items||[], source:'header:remove' } })); } catch(_){ }
        return items;
      }catch(_){ return []; }
    }
    function loadMini(){
      if (loaded) return; loaded = true;
      fetch('/api/cart/mini?t='+Date.now(), { headers:{ 'Accept':'application/json' }, credentials:'same-origin' })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
          var items = Array.isArray(data?.items) ? data.items : [];
          writeLocalCart(items);
          renderItems(items);
        })
        .catch(() => { renderItems(readLocalCart()); });
    }
      function show(){ if(!isDesktop()) return; loadMini(); try{ var hint = panel.querySelector('#freeShipHint'); if(hint) hint.textContent = 'Instant delivery'; }catch(_){} panel.hidden = false; }
    function hide(){ panel.hidden = true; }
    // Defer showing/rotation to the upsell-aware handler below
    wrap.addEventListener('mouseenter', () => { if (hideTimer) { clearTimeout(hideTimer); hideTimer=null; } });
    wrap.addEventListener('mouseleave', () => { hideTimer = setTimeout(hide, 120); });
    // Prevent default on desktop clicks to keep dropdown open; on mobile it navigates
    link.addEventListener('click', (e) => { if (isDesktop()) { e.preventDefault(); show(); } });
    // Expose helpers for external triggers (e.g., add-to-cart)
    try { window.__cartDropdownShow = show; } catch(_){ }
    try { window.__cartDropdownRender = function(items){ try{ renderItems(items); panel.hidden=false; }catch(_){ } } } catch(_){ }
    // Remove handler inside dropdown
    try{
      panel.addEventListener('click', function(e){
        var btn = e.target.closest('.js-remove,[data-remove]'); if(!btn) return;
        e.preventDefault(); e.stopPropagation();
        var id = btn.getAttribute('data-remove') || btn.dataset.remove || btn.getAttribute('data-id'); if(!id) return;
        var next = removeFromLocalCart(id); renderItems(next);
        // server remove in background
        try {
          var token=(document.querySelector('meta[name="csrf-token"]')?.content)||window.__csrfToken||'';
          fetch('/api/cart/remove', {
            method:'POST',
            headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':token },
            credentials:'same-origin',
            body: JSON.stringify({ id:id })
          }).then(() => fetchCountAndUpdateBadge()).catch(()=>{});
        } catch(_){ }
      });
    }catch(_){ }
    // Upsell loader
    try{
      var upsellLoaded = false;
      var upsellPool = [];
      var upsellIndex = 0;
      var headlineIndex = 0;
      function esc(s){ return String(s||'').replace(/[&<>"']/g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[c])); }
      function ensureHeadlineEl(){
        try{
          var el = panel.querySelector('#cartdd-upsell-headline');
          if(!el){
            el = document.createElement('div');
            el.id = 'cartdd-upsell-headline';
            el.className = 'cartdd-upsell-headline';
            el.style.fontWeight = '600';
            el.style.color = 'var(--ink-700)';
            el.style.padding = '8px 12px 0';
            var subtotal = panel.querySelector('.cartdd-subtotal');
            if (subtotal && subtotal.parentNode) {
              subtotal.insertAdjacentElement('afterend', el);
            } else {
              var ups = panel.querySelector('#cartdd-upsell');
              if (ups) ups.insertAdjacentElement('afterbegin', el); else panel.appendChild(el);
            }
          }
          return el;
        }catch(_){ return null; }
      }
      function sliceAndRender(pool){
        // rotate 3 items each time
        if (!Array.isArray(pool) || !pool.length) { renderUpsell([]); return; }
        var idsInCart = (readLocalCart()||[]).map(function(it){ return String(it.id||''); });
        var filtered = pool.filter(function(p){ return idsInCart.indexOf(String(p.id||''))===-1; });
        if (!filtered.length) filtered = pool.slice();
        var start = upsellIndex % filtered.length;
        var view = [];
        for (var i=0;i<Math.min(3, filtered.length);i++) view.push(filtered[(start+i)%filtered.length]);
        upsellIndex = (upsellIndex + 3) % filtered.length;
        renderUpsell(view);
      }
      function renderUpsell(list){
        try{
          var wrapU = panel.querySelector('#cartdd-upsell'); if(!wrapU) return;
          if(!Array.isArray(list) || !list.length){ wrapU.innerHTML = ''; return; }
          wrapU.innerHTML = list.map(function(it){
            var p = Number(it.price_min ?? it.price ?? 0); if(p>=1000) p=p/100;
            var img = it.image || (it.images && it.images[0]) || '';
            var url = it.url || ('/therapies/'+it.id);
            var title = esc(it.title||'');
            return '<div class="upsell-item">'
              + (img?('<img src="'+img+'" alt="">'):'<div style="width:46px;height:46px;border-radius:8px;background:#f3f5f7;border:1px solid #eceff3"></div>')
              + '<div><p class="upsell-title">'+title+'</p><div class="upsell-price">'+money(p)+'</div></div>'
              + '<button class="btn-wow btn-wow--outline btn-sm js-add-to-cart" data-id="'+it.id+'" data-title="'+title+'" data-price="'+p.toFixed(2)+'" data-image="'+img+'" data-url="'+url+'">Add</button>'
            + '</div>';
          }).join('');
        }catch(_){ }
      }
      function deriveTypeFromCart(){
        try{
          var items = readLocalCart(); if(!items || !items.length) return '';
          var url = String(items[0].url||'');
          var m = url.match(/\/([a-z-]+)\//i); return m?m[1]:'';
        }catch(_){ return ''; }
      }
      function cartComposition(){
        try{
          var items = readLocalCart(); if(!items || !items.length) return 'unknown';
          var flags = items.map(function(it){ var t=(it.title||'')+ ' '+ (it.url||''); return /online/i.test(t); });
          var anyOnline = flags.some(Boolean); var anyOffline = flags.some(function(f){ return !f; });
          if (anyOnline && !anyOffline) return 'online-only';
          if (!anyOnline && anyOffline) return 'in-person-only';
          if (anyOnline && anyOffline) return 'mixed';
          return 'unknown';
        }catch(_){ return 'unknown'; }
      }
      function chooseHeadline(){
        var mode = cartComposition();
        var sets = {
          'online-only': [
            'Popular online picks right now',
            'Instant calm, no travel required',
            'More online favourites you’ll actually use',
            'Pair it with a quick reset',
            'Top-rated online sessions',
            'Online best-sellers this week',
              'Most booked online therapies',
            'Finish strong: add an online upgrade'
          ],
          'in-person-only': [
            'Popular near you',
            'Most booked in your area',
            'Wellness people nearby love',
            'Nearby favourites to match your booking',
            'Make a day of it',
            'Limited spots near you',
            'New in your area',
            'Top-rated nearby practitioners'
          ],
          'mixed': [
            'Complete the set: online + in-person',
            'Balance your week',
            'Before & after: prep online, go in-person',
            'Your calm, but smarter',
            'Most paired with what’s in your basket'
          ],
          'unknown': [ 'Complete your calm' ]
        };
        var list = sets[mode] || sets['unknown'];
        var text = list[ headlineIndex % list.length ];
        headlineIndex = (headlineIndex + 1) % list.length;
        return text;
      }
      function setHeadline(){ try{ var h = ensureHeadlineEl(); if(h){ h.textContent = chooseHeadline(); } }catch(_){ } }
      function loadUpsell(){ if(upsellLoaded) return; upsellLoaded=true;
        var seg = deriveTypeFromCart();
        var endpoint = seg ? ('/api/products?limit=12&sort=popular&type='+encodeURIComponent(seg)) : '/api/products?limit=12&sort=popular';
        fetch(endpoint, { headers:{ 'Accept':'application/json' }})
          .then(r=>r.json())
          .then(function(list){ upsellPool = Array.isArray(list)?list:[]; setHeadline(); sliceAndRender(upsellPool); })
          .catch(function(){ renderUpsell([]); });
      }
      // Only rotate when dropdown transitions from closed -> open.
      wrap.addEventListener('mouseenter', function(){
        var wasClosed = !!panel.hidden;
        show();
        if(!upsellLoaded){ loadUpsell(); return; }
        if (wasClosed) { setHeadline(); sliceAndRender(upsellPool); }
      });
    }catch(_){ }
  }

});
