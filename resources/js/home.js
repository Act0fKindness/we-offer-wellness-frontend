import { gsap } from 'gsap';
import { createApp } from 'vue';
import ui from '@nuxt/ui/vue-plugin';
import { initSubscriberForms } from './lib/subscriber-forms';
import SearchRangeCalendar from './Components/SearchRangeCalendar.vue';
import SearchBarV4 from './Components/SearchBarV4.vue';
import { fetchWhatCategories } from './services/whatCategories';
import { fetchLocations } from './services/locations';

function runIdle(fn) {
  try {
    if (typeof window !== 'undefined' && 'requestIdleCallback' in window) {
      window.requestIdleCallback(fn, { timeout: 300 });
      return;
    }
  } catch (_err) {}
  setTimeout(fn, 1);
}

function onDocumentReady(fn) {
  if (typeof document === 'undefined') return;
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', fn, { once: true });
    return;
  }
  fn();
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
  const nav = document.getElementById('desktopNav');
  const layer = document.getElementById('megaLayer');
  const shell = document.getElementById('mega-panel');
  const arrow = document.getElementById('megaArrow');
  const track = document.getElementById('megaTrack');
  const underline = document.getElementById('navUnderline');
  const overlay = document.getElementById('mega-overlay');
  if (!nav || !layer || !shell || !arrow || !track) return;

  const navItems = Array.from(nav.querySelectorAll('.link-wow--nav'));
  const dropdownItems = navItems.filter((item) => item.dataset.megaMenu);
  const panes = Array.from(shell.querySelectorAll('.wow-mega-pane[data-menu]'));
  const menuOrder = panes.map((pane) => pane.dataset.menu);
  const focusableSelector = 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';

  let activeMenu = null;
  let activeTrigger = null;
  let closeTimer = null;
  let hasOpenedOnce = false;

  const clamp = (value, min, max) => Math.min(Math.max(value, min), max);
  const getPane = (name) => panes.find((pane) => pane.dataset.menu === name);
  const getTrigger = (name) => dropdownItems.find((item) => item.dataset.megaMenu === name);

  const getMegaWidth = () => {
    const maxWidth = parseFloat(window.getComputedStyle(layer).getPropertyValue('--mega-max-width')) || 1160;
    const edgeGap = parseFloat(window.getComputedStyle(layer).getPropertyValue('--mega-edge-gap')) || 18;
    return Math.min(maxWidth, window.innerWidth - edgeGap * 2);
  };

  const clearActiveItems = () => {
    navItems.forEach((item) => {
      item.classList.remove('is-active');
      item.setAttribute('aria-expanded', 'false');
    });
  };

  const updateUnderline = (item) => {
    if (!underline || !item) return;
    const navRect = nav.getBoundingClientRect();
    const itemRect = item.getBoundingClientRect();
    underline.style.opacity = '1';
    underline.style.width = `${Math.max(0, itemRect.width - 28)}px`;
    underline.style.transform = `translateX(${itemRect.left - navRect.left + 14}px)`;
  };

  const hideUnderline = () => {
    if (!underline) return;
    underline.style.opacity = '0';
    underline.style.width = '0';
  };

  const positionArrow = (item) => {
    const width = getMegaWidth();
    const itemRect = item.getBoundingClientRect();
    const shellLeft = (window.innerWidth - width) / 2;
    const itemCenter = itemRect.left + itemRect.width / 2;
    shell.style.width = `${width}px`;
    arrow.style.left = `${clamp(itemCenter - shellLeft, 32, width - 32)}px`;
  };

  const updateHeight = (pane) => {
    const styles = window.getComputedStyle(shell);
    const borderTop = parseFloat(styles.borderTopWidth) || 0;
    const borderBottom = parseFloat(styles.borderBottomWidth) || 0;
    shell.style.height = `${pane.scrollHeight + borderTop + borderBottom}px`;
  };

  const openMega = (menuName, focusContent = false) => {
    const trigger = getTrigger(menuName);
    const pane = getPane(menuName);
    if (!trigger || !pane) return;

    window.clearTimeout(closeTimer);
    clearActiveItems();
    trigger.classList.add('is-active');
    trigger.setAttribute('aria-expanded', 'true');
    activeTrigger = trigger;

    updateUnderline(trigger);
    positionArrow(trigger);
    updateHeight(pane);

    const index = menuOrder.indexOf(menuName);
    if (!hasOpenedOnce) {
      track.style.transition = 'none';
      track.style.transform = `translateX(-${index * 100}%)`;
      window.requestAnimationFrame(() => {
        track.style.transition = '';
      });
    } else {
      track.style.transform = `translateX(-${index * 100}%)`;
    }

    activeMenu = menuName;
    hasOpenedOnce = true;
    shell.classList.add('is-open');
    shell.setAttribute('aria-hidden', 'false');
    if (overlay) overlay.style.display = 'block';

    if (focusContent) {
      const first = pane.querySelector(focusableSelector);
      if (first) first.focus();
    }
  };

  const closeMega = (immediate = false, focusTrigger = false) => {
    const run = () => {
      activeMenu = null;
      clearActiveItems();
      hideUnderline();
      shell.classList.remove('is-open');
      shell.setAttribute('aria-hidden', 'true');
      if (overlay) overlay.style.display = 'none';
      hasOpenedOnce = false;
      if (focusTrigger && activeTrigger) activeTrigger.focus();
    };
    window.clearTimeout(closeTimer);
    if (immediate) {
      run();
    } else {
      closeTimer = window.setTimeout(run, 140);
    }
  };

  const cancelClose = () => window.clearTimeout(closeTimer);

  navItems.forEach((item, index) => {
    const menuName = item.dataset.megaMenu;
    item.setAttribute('aria-haspopup', menuName ? 'true' : 'false');
    item.setAttribute('aria-expanded', 'false');
    item.addEventListener('mouseenter', () => menuName ? openMega(menuName) : closeMega(true));
    item.addEventListener('focus', () => menuName ? openMega(menuName) : closeMega(true));
    item.addEventListener('keydown', (event) => {
      if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') {
        event.preventDefault();
        const nextIndex = event.key === 'ArrowRight'
          ? (index + 1) % navItems.length
          : (index - 1 + navItems.length) % navItems.length;
        navItems[nextIndex]?.focus();
      } else if ((event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') && menuName) {
        event.preventDefault();
        openMega(menuName, true);
      } else if (event.key === 'Escape') {
        event.preventDefault();
        closeMega(true, true);
      }
    });
  });

  nav.addEventListener('mouseenter', cancelClose);
  nav.addEventListener('mouseleave', () => closeMega());
  layer.addEventListener('mouseenter', cancelClose);
  layer.addEventListener('mouseleave', () => closeMega());

  shell.addEventListener('keydown', (event) => {
    if (!activeMenu) return;
    if (event.key === 'Escape') {
      event.preventDefault();
      closeMega(true, true);
      return;
    }
    if (!['ArrowDown', 'ArrowUp', 'ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
    const pane = getPane(activeMenu);
    const focusable = pane ? Array.from(pane.querySelectorAll(focusableSelector)) : [];
    if (!focusable.length) return;
    const currentIndex = focusable.indexOf(document.activeElement);
    let nextIndex = currentIndex;
    if (event.key === 'ArrowDown' || event.key === 'ArrowRight') nextIndex += 1;
    if (event.key === 'ArrowUp' || event.key === 'ArrowLeft') nextIndex -= 1;
    if (event.key === 'Home') nextIndex = 0;
    if (event.key === 'End') nextIndex = focusable.length - 1;
    if (nextIndex < 0) nextIndex = focusable.length - 1;
    if (nextIndex >= focusable.length) nextIndex = 0;
    event.preventDefault();
    focusable[nextIndex]?.focus();
  });

  window.addEventListener('resize', () => {
    if (!activeMenu) return;
    const trigger = getTrigger(activeMenu);
    const pane = getPane(activeMenu);
    if (!trigger || !pane) return;
    positionArrow(trigger);
    updateHeight(pane);
    updateUnderline(trigger);
  });

  document.addEventListener('click', (event) => {
    if (!activeMenu) return;
    if (nav.contains(event.target) || shell.contains(event.target)) return;
    closeMega(true);
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && activeMenu) closeMega(true, true);
  });
}

function initMobileMenu() {
  const toggle = document.querySelector('[data-wow-mobile-toggle]') || document.querySelector('header button[aria-label="Menu"]');
  if (!toggle || !toggle.classList.contains('hamburger')) return;
  setupHamburgerController(toggle);
}

function setupUltraSearchBar(prefix) {
  if (typeof window !== 'undefined' && window.setupUltraSearchBar && window.setupUltraSearchBar !== setupUltraSearchBar) {
    try { return window.setupUltraSearchBar(prefix); } catch (_) {}
  }
  // Find the specific ultra-search container for this prefix
  const root = (
    document.querySelector(`#${prefix}-root`) ||
    document.querySelector(`#${prefix}-seg-what`)?.closest('.wow-ultra') ||
    document.getElementById(`${prefix}-what`)?.closest('.wow-ultra') ||
    null
  );
  if (root && root.dataset && root.dataset.wowUltraBound === '1') return;
  function byId(s){ return document.getElementById(prefix + '-' + s); }
  const panes = ['what-pane','where-pane','when-pane','who-pane'];
  function hideAll(){ panes.forEach((id) => { const el = byId(id); if (el) el.classList.add('d-none'); }); const what = byId('what'); if (what) what.setAttribute('aria-expanded','false'); }
  function openPane(which){ hideAll(); const pane = byId(which+'-pane'); if(pane){ pane.classList.remove('d-none'); } if(which==='what'){ const what = byId('what'); if(what) what.setAttribute('aria-expanded','true'); } }
  function normalizeText(value) {
    return String(value || '')
      .toLowerCase()
      .replace(/[\u2018\u2019\u201c\u201d]/g, "'")
      .replace(/[^a-z0-9]+/g, ' ')
      .trim();
  }
  let whereSource = [];
  let whereSourceReady = false;
  let whatSource = [];
  let whatSourceReady = false;
  function renderWhere(qs) {
    const list = byId('where-list');
    if (!list) return false;
    const query = String(qs || '').trim();
    if (!whereSourceReady) {
      list.innerHTML = '<button type="button" class="item" aria-disabled="true"><span class="title">Loading trending destinations…</span></button>';
      return false;
    }
    const needle = normalizeText(query);

    let items = (whereSource || []).slice()
    if (needle) {
      items = items.filter((item) => normalizeText([item.title, item.label, item.search, item.country, item.county, item.region].join(' ')).includes(needle));
    } else {
      items = items.slice(0, 5)
    }
    if (!items.length) {
      list.innerHTML = '<button type="button" class="item" aria-disabled="true"><span class="title">No locations found</span></button>';
      return false;
    }
    list.innerHTML = items.slice(0, needle ? 12 : 5).map((item) => {
      const icon = item?.online ? '<i class="bi bi-wifi"></i>' : '<i class="bi bi-geo-alt"></i>';
      const sub = item?.subtitle ? `<span class="text-muted ms-2">${item.subtitle}</span>` : '';
      return `<button type="button" class="item" role="option" data-value="${item.value || item.title || ''}">${icon}<span class="title">${item.title || ''}</span>${sub}</button>`;
    }).join('');
    return true;
  }
  function searchScore(query, item) {
    const q = normalizeText(query);
    if (!q) return 999;
    const title = normalizeText(item?.title || '');
    const hay = normalizeText([item?.title, item?.cat, item?.type, item?.search, item?.subtitle].filter(Boolean).join(' '));
    const tokens = q.split(/\s+/).filter(Boolean);
    if (title === q) return 0;
    if (title.indexOf(q) === 0) return 1;
    if (title.split(/\s+/).some((token) => token.indexOf(q) === 0)) return 2;
    if (title.indexOf(q) !== -1) return 3;
    if (hay.indexOf(q) !== -1) return 4;
    if (tokens.length && tokens.every((token) => hay.indexOf(token) !== -1)) return 5;
    return 999;
  }
  function renderWhat(qs) {
    const list = byId('what-list');
    if (!list) return false;
    const query = String(qs || '').trim();
    if (!whatSourceReady) {
      list.innerHTML = '<button type="button" class="item" aria-disabled="true"><span class="title">Loading modalities…</span></button>';
      return false;
    }

    const items = query.length < 2
      ? (whatSource || []).slice(0, 5)
      : (whatSource || [])
        .map((item) => ({ item, score: searchScore(query, item) }))
        .filter((row) => row.score < 999)
        .sort((a, b) => {
          if (a.score !== b.score) return a.score - b.score;
          return String(a.item.title || '').localeCompare(String(b.item.title || ''));
        })
        .slice(0, 5)
        .map((row) => row.item);

    if (!items.length) {
      list.innerHTML = '';
      return false;
    }

    list.innerHTML = `<div class="section-title">${query.length < 2 ? 'Trending modalities' : 'Modalities'}</div><div>` + items.map((item) => {
      const title = String(item?.title || '')
      const subtitle = String(item?.subtitle || (Number(item?.counts?.total || 0) ? `${Number(item.counts.total)} offerings` : 'Modality')).replace(/\bproducts?\b/gi, 'offerings')
      return `<button type="button" class="item" role="option" data-value="${title}"><i class="bi bi-tag"></i><span class="title">${title}</span><span class="text-muted ms-2">${subtitle || 'Modality'}</span></button>`
    }).join('') + '</div>'
    return true;
  }
  const whatInput = byId('what');
  if(whatInput){
    const refreshWhat = () => {
      if (renderWhat(whatInput.value || '')) openPane('what');
      else hideAll();
    };
    whatInput.addEventListener('focus', refreshWhat);
    whatInput.addEventListener('input', refreshWhat);
    const segWhat = byId('seg-what');
    if(segWhat){ segWhat.addEventListener('click', refreshWhat); }
  }
  const whereEditor = byId('where-editor'); if(whereEditor){ whereEditor.addEventListener('focus', ()=>{ renderWhere(whereEditor.textContent || ''); openPane('where'); }); whereEditor.addEventListener('click', ()=>{ renderWhere(whereEditor.textContent || ''); openPane('where'); }); whereEditor.addEventListener('input', ()=>{ renderWhere(whereEditor.textContent || ''); openPane('where'); }); }
  const whenInput = byId('when'); if(whenInput){ whenInput.addEventListener('focus', ()=>openPane('when')); whenInput.addEventListener('click', ()=>openPane('when')); }
  const whoSeg = byId('seg-who'); if(whoSeg){ whoSeg.addEventListener('click', ()=>openPane('who')); }
  // Close only when clicking outside this specific bar
  document.addEventListener('click', (e)=>{ if(root && !root.contains(e.target)) hideAll(); });
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') hideAll(); });
  const whatList = byId('what-list'); if(whatList && byId('what')){ whatList.addEventListener('click', (e)=>{ const btn = e.target.closest('.item'); if(btn && btn.dataset.value){ byId('what').value = btn.dataset.value; hideAll(); byId('what').blur(); } }); }
  const whereHidden = byId('where'); if(byId('where-list') && whereEditor){ byId('where-list').addEventListener('click', (e)=>{ const btn = e.target.closest('.item'); if(btn && btn.dataset.value){ whereEditor.textContent = btn.dataset.value; if(whereHidden) whereHidden.value = btn.dataset.value; hideAll(); whereEditor.blur(); } }); }
  const whoDone = byId('who-done'); if(whoDone){ whoDone.addEventListener('click', ()=>hideAll()); }

  fetchWhatCategories()
    .then((items) => {
      whatSource = items || [];
      whatSourceReady = true;
      renderWhat((whatInput && whatInput.value) || '');
    })
    .catch(() => {
      whatSource = [];
      whatSourceReady = true;
      renderWhat((whatInput && whatInput.value) || '');
    });

  if (whereEditor) {
    fetch('/cache/locations.json', { cache: 'no-store' })
      .then((res) => (res && res.ok) ? res.json() : null)
      .then((payload) => {
        const source = Array.isArray(payload?.flat) && payload.flat.length ? payload.flat : (Array.isArray(payload?.suggestions) ? payload.suggestions : []);
        const seen = new Set();
        whereSource = source.map((item) => {
          const title = String(item?.title || item?.label || item?.slug || '').trim();
          const country = String(item?.country || '').trim();
          const county = String(item?.county || item?.district || item?.region || '').trim();
          const slug = String(item?.slug || title || '').toLowerCase().replace(/&/g, ' and ').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
          return {
            title,
            label: title,
            value: title,
            subtitle: item?.online ? 'Virtual' : [county, country].filter(Boolean).join(', '),
            slug,
            country,
            county,
            region: String(item?.region || '').trim(),
            online: !!item?.online,
            total: Number(item?.counts?.total || item?.counts?.products || item?.counts?.offerings || 0),
            search: [title, country, county, item?.label, item?.place_name, slug].filter(Boolean).join(' '),
          };
        }).filter((item) => {
          const key = normalizeText(item.value);
          if (!key || seen.has(key)) return false;
          seen.add(key);
          return true;
        }).sort((a, b) => {
          if (a.online !== b.online) return a.online ? -1 : 1;
          const at = Number(a.total || 0);
          const bt = Number(b.total || 0);
          if (at !== bt) return bt - at;
          return a.title.localeCompare(b.title);
        });
        whereSourceReady = true;
        renderWhere(whereEditor.textContent || '');
      })
      .catch(() => {
        whereSource = [];
        whereSourceReady = true;
        renderWhere(whereEditor.textContent || '');
      });
  }

  // Shared Who panel controls (Adults counter + group type)
  (function initWhoControls(){
    const pane = byId('who-pane');
    const adultsEl = byId('adults-val');
    const groupList = byId('group-type-list');
    const summaryEl = byId('who-summary');
    if (!pane || !adultsEl || pane.dataset.wowWhoBound === '1') return;

    let groupTouched = false;

    function clampAdults(n){
      const num = Number(n);
      if (!Number.isFinite(num)) return 0;
      return Math.max(0, Math.min(20, Math.round(num)));
    }

    function getAdults(){
      return clampAdults((adultsEl.textContent || adultsEl.value || '0').trim());
    }

    function setGroupSelection(name){
      if (!groupList) return;
      const target = name == null ? '' : String(name || '');
      Array.from(groupList.querySelectorAll('[data-group]')).forEach((btn) => {
        const isMatch = String(btn.getAttribute('data-group')) === target;
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
      const sel = groupList.querySelector('[data-group][aria-selected="true"]');
      return (sel?.getAttribute?.('data-group') || '').trim();
    }

    function updateSummary(){
      if (!summaryEl) return;
      const adults = getAdults();
      const group = getSelectedGroup();
      const parts = [];
      if (adults > 0) parts.push(`${adults} ${adults === 1 ? 'adult' : 'adults'}`);
      if (group) parts.push(group);
      summaryEl.textContent = parts.length ? parts.join(' · ') : 'Add guests';
    }

    function applyAdults(n){
      const next = clampAdults(n);
      adultsEl.textContent = String(next);
      if (!groupTouched) setGroupSelection(groupForAdults(next));
      updateSummary();
    }

    pane.addEventListener('click', (event) => {
      const dec = event.target.closest('[data-dec="adults"]');
      const inc = event.target.closest('[data-inc="adults"]');
      if (!dec && !inc) return;
      event.preventDefault();
      const current = getAdults();
      applyAdults(current + (inc ? 1 : -1));
    });

    if (groupList) {
      groupList.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-group]');
        if (!btn) return;
        const group = (btn.getAttribute('data-group') || '').trim();
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

    // Initial sync
    updateSummary();
    pane.dataset.wowWhoBound = '1';
  })();
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
    closeHeaderDropdownExcept('account');
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

  try {
    window.__WOWCloseAccountDropdown = closePanel;
  } catch (_err) {}
}

function mountSearchRangeCalendars() {
  try {
    document.querySelectorAll('[id$="-calendarMount"]').forEach((el) => {
      if (!el || el.dataset.wowMounted === '1') return;
      const prefix = String(el.id || '').replace(/-calendarMount$/, '');
      if (!prefix) return;
      el.dataset.wowMounted = '1';
      try {
        createApp(SearchRangeCalendar, { prefix }).use(ui).mount(el);
      } catch (err) {
        el.dataset.wowMounted = '0';
        console.warn('[WOW] calendar mount failed', err);
      }
    });
  } catch (err) {
    console.warn('[WOW] calendar bootstrap skipped', err);
  }
}

function mountSearchBarV4() {
  try {
    document.querySelectorAll('[data-wow-searchbar-v4]').forEach((el) => {
      if (!el || el.dataset.wowMounted === '1') return;
      el.dataset.wowMounted = '1';

      let initialQuery = {};
      try {
        initialQuery = JSON.parse(el.dataset.initialQuery || '{}') || {};
      } catch (_err) {
        initialQuery = {};
      }

      const readBool = (value, fallback = false) => {
        if (value === undefined || value === null || value === '') return fallback;
        return ['1', 'true', 'yes', 'on'].includes(String(value).toLowerCase());
      };

      const props = {
        idPrefix: el.dataset.idPrefix || 'search-v4',
        searchUrl: el.dataset.searchUrl || '/search',
        resultCount: Number(el.dataset.resultCount || 0),
        mobileTopOffset: el.dataset.mobileTopOffset || 12,
        initialQuery,
        staticLayout: readBool(el.dataset.staticLayout, false),
        showChrome: readBool(el.dataset.showChrome, true),
        mobileChrome: readBool(el.dataset.mobileChrome, false),
        navigateOnSubmit: readBool(el.dataset.navigateOnSubmit, false),
        forceMobileLayout: readBool(el.dataset.forceMobileLayout, false),
        defaultActiveSegment: el.dataset.defaultActiveSegment || '',
        hideTopRow: readBool(el.dataset.hideTopRow, false),
        hideMobileClose: readBool(el.dataset.hideMobileClose, false),
      };

      try {
        createApp(SearchBarV4, props).use(ui).mount(el);
      } catch (err) {
        el.dataset.wowMounted = '0';
        console.warn('[WOW] search bar v4 mount failed', err);
      }
    });
  } catch (err) {
    console.warn('[WOW] search bar v4 bootstrap skipped', err);
  }
}

function mountHomeSearchBarV4() {
  const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
  const normalizeText = (value) => String(value || '')
    .toLowerCase()
    .replace(/[\u2018\u2019\u201c\u201d]/g, "'")
    .replace(/[^a-z0-9]+/g, ' ')
    .trim();
  const scoreItem = (query, item) => {
    const q = normalizeText(query);
    if (!q) return 999;
    const title = normalizeText(item?.title || item?.label || item?.value || '');
    const hay = normalizeText([item?.title, item?.label, item?.value, item?.slug, item?.search, item?.subtitle, item?.type].filter(Boolean).join(' '));
    if (title === q) return 0;
    if (title.startsWith(q)) return 1;
    if (title.includes(q)) return 2;
    if (hay.includes(q)) return 3;
    const tokens = q.split(/\s+/).filter(Boolean);
    if (tokens.length && tokens.every((token) => hay.includes(token))) return 4;
    return 999;
  };
  const setVisible = (el, visible) => {
    if (!el) return;
    el.classList.toggle('hidden', !visible);
    el.classList.toggle('flex', visible && el.id?.includes('modal'));
  };
  const buildSearchUrl = (baseUrl, params) => {
    const url = new URL(baseUrl || '/search', window.location.origin);
    Object.entries(params).forEach(([key, value]) => {
      if (value == null) return;
      const str = String(value).trim();
      if (!str) return;
      url.searchParams.set(key, str);
    });
    return `${url.pathname}${url.search}${url.hash}`;
  };
  const renderWhatItems = (items, query) => {
    const q = String(query || '').trim();
    const filtered = q.length < 2
      ? items.slice(0, 8)
      : items
        .map((item) => ({ item, score: scoreItem(q, item) }))
        .filter((row) => row.score < 999)
        .sort((a, b) => a.score - b.score || String(a.item.title || '').localeCompare(String(b.item.title || '')))
        .slice(0, 10)
        .map((row) => row.item);

    return filtered.map((item) => `
      <li>
        <button type="button" data-select-what="${escapeHtml(item.value || item.title || '')}" class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-left transition-colors hover:bg-[#F9FAFB]">
          <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#F4F6FB] text-[#344054]">
            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
              <path d="M13 4l2.2 5.6L21 12l-5.8 2.4L13 20l-2.2-5.6L5 12l5.8-2.4L13 4Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
            </svg>
          </span>
          <span class="min-w-0 flex-1">
            <span class="block truncate text-sm font-semibold text-[#101828]">${escapeHtml(item.title || '')}</span>
            <span class="block truncate text-xs text-[#667085]">${escapeHtml(item.subtitle || item.type || item.cat || 'Offerings')}</span>
          </span>
        </button>
      </li>
    `).join('');
  };
  const renderWhereItems = (items, query) => {
    const q = String(query || '').trim();
    const filtered = q.length < 2
      ? items.slice(0, 8)
      : items
        .map((item) => ({ item, score: scoreItem(q, item) }))
        .filter((row) => row.score < 999)
        .sort((a, b) => a.score - b.score || String(a.item.title || '').localeCompare(String(b.item.title || '')))
        .slice(0, 10)
        .map((row) => row.item);

    return filtered.map((item) => `
      <li>
        <button type="button" data-select-where="${escapeHtml(item.value || item.title || '')}" data-select-mode="${escapeHtml(item.online ? 'online' : 'in-person')}" class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-left transition-colors hover:bg-[#F9FAFB]">
          <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#F4F6FB] text-[#344054]">
            ${item.online ? `
              <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/>
                <path d="M4 12h16M12 4c2.5 2.4 4 5.2 4 8s-1.5 5.6-4 8M12 4c-2.5 2.4-4 5.2-4 8s1.5 5.6 4 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
              </svg>
            ` : `
              <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                <path d="M12 21s6-5.2 6-11a6 6 0 1 0-12 0c0 5.8 6 11 6 11Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                <circle cx="12" cy="10" r="2.3" stroke="currentColor" stroke-width="1.8"/>
              </svg>
            `}
          </span>
          <span class="min-w-0 flex-1">
            <span class="block truncate text-sm font-semibold text-[#101828]">${escapeHtml(item.title || '')}</span>
            <span class="block truncate text-xs text-[#667085]">${escapeHtml(item.subtitle || (item.online ? 'Virtual' : 'Popular place'))}</span>
          </span>
        </button>
      </li>
    `).join('');
  };
  const geocodeCurrentLocation = async (mapboxKey) => {
    if (!navigator.geolocation) return '';
    const position = await new Promise((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(resolve, reject, {
        enableHighAccuracy: false,
        timeout: 10000,
        maximumAge: 600000,
      });
    });
    if (!position) return '';
    const lat = position.coords.latitude;
    const lng = position.coords.longitude;
    if (!mapboxKey) return `${lat.toFixed(2)}, ${lng.toFixed(2)}`;
    try {
      const endpoint = new URL(`https://api.mapbox.com/geocoding/v5/mapbox.places/${lng},${lat}.json`);
      endpoint.searchParams.set('types', 'place,locality,region');
      endpoint.searchParams.set('limit', '1');
      endpoint.searchParams.set('access_token', mapboxKey);
      const res = await fetch(endpoint.toString());
      if (!res.ok) throw new Error(`reverse geocode ${res.status}`);
      const data = await res.json();
      const feature = Array.isArray(data?.features) ? data.features[0] : null;
      return String(feature?.text || feature?.place_name || '').trim();
    } catch (_error) {
      return '';
    }
  };

  try {
    document.querySelectorAll('[data-wow-home-searchbar-v4]').forEach((el) => {
      if (!el || el.dataset.wowMounted === '1') return;
      el.dataset.wowMounted = '1';

      const searchUrl = el.dataset.searchUrl || '/search';
      const mapboxKey = el.dataset.mapboxKey || '';
      const desktopForm = el.querySelector('#wow-home-search-desktop');
      const mobileForm = el.querySelector('#wow-home-search-mobile');
      const desktopWhatInput = el.querySelector('#desktop-what');
      const desktopWhereInput = el.querySelector('#desktop-where');
      const desktopModeInput = el.querySelector('#desktop-mode');
      const mobileWhatInput = el.querySelector('#mobile-what-input');
      const mobileWhereInput = el.querySelector('#mobile-where-input');
      const mobileModeInput = el.querySelector('#mobile-mode');
      const desktopWhatDropdown = el.querySelector('#desktop-what-dropdown');
      const desktopWhereDropdown = el.querySelector('#desktop-where-dropdown');
      const desktopWhatList = el.querySelector('#desktop-what-list');
      const desktopWhereList = el.querySelector('#desktop-location-list');
      const mobileWhatModal = el.querySelector('#mobile-what-modal');
      const mobileWhereModal = el.querySelector('#mobile-where-modal');
      const mobileWhatList = el.querySelector('#mobile-what-list');
      const mobileWhereList = el.querySelector('#mobile-where-list');
      const desktopWhatField = el.querySelector('#desktop-what-field');
      const desktopWhereField = el.querySelector('#desktop-where-field');
      const desktopClearWhat = el.querySelector('#desktop-clear-what');
      const desktopClearWhere = el.querySelector('#desktop-clear-where');
      const desktopUseLocation = el.querySelector('#desktop-use-location');
      const desktopOnline = el.querySelector('#desktop-online');
      const mobileOpenWhat = el.querySelector('#mobile-open-what');
      const mobileOpenWhere = el.querySelector('#mobile-open-where');
      const mobileUseLocation = el.querySelector('#mobile-use-location');
      const mobileOnline = el.querySelector('#mobile-online');
      const mobileWhatLabel = el.querySelector('#mobile-what-label');
      const mobileWhereLabel = el.querySelector('#mobile-where-label');

      if (!desktopForm || !mobileForm || !desktopWhatInput || !desktopWhereInput || !desktopWhatList || !desktopWhereList || !mobileWhatList || !mobileWhereList) {
        el.dataset.wowMounted = '0';
        return;
      }

      const state = {
        what: String(el.dataset.initialQuery || '').trim(),
        where: String(el.dataset.initialWhere || '').trim(),
        mode: String(el.dataset.initialMode || '').trim(),
        whatItems: [],
        whereItems: [],
      };

      const closeDesktopDropdowns = () => {
        setVisible(desktopWhatDropdown, false);
        setVisible(desktopWhereDropdown, false);
      };
      const closeMobileModals = () => {
        setVisible(mobileWhatModal, false);
        setVisible(mobileWhereModal, false);
      };
      const updateDesktopClearButtons = () => {
        if (desktopClearWhat) desktopClearWhat.classList.toggle('hidden', !state.what);
        if (desktopClearWhere) desktopClearWhere.classList.toggle('hidden', !state.where && state.mode !== 'online');
      };
      const syncMobileLabels = () => {
        if (mobileWhatLabel) mobileWhatLabel.textContent = state.what || 'Search offerings';
        if (mobileWhereLabel) mobileWhereLabel.textContent = state.where || (state.mode === 'online' ? 'Online' : 'Online or location');
      };
      const syncInputs = () => {
        desktopWhatInput.value = state.what;
        desktopWhereInput.value = state.where;
        if (desktopModeInput) desktopModeInput.value = state.mode;
        mobileWhatInput.value = state.what;
        mobileWhereInput.value = state.where;
        if (mobileModeInput) mobileModeInput.value = state.mode;
        updateDesktopClearButtons();
        syncMobileLabels();
      };
      const setWhat = (value) => {
        state.what = String(value || '').trim();
        syncInputs();
      };
      const setWhere = (value, mode = '') => {
        state.where = String(value || '').trim();
        state.mode = String(mode || '').trim();
        syncInputs();
      };
      const openDesktopWhat = () => {
        setVisible(desktopWhatDropdown, true);
        setVisible(desktopWhereDropdown, false);
      };
      const openDesktopWhere = () => {
        setVisible(desktopWhereDropdown, true);
        setVisible(desktopWhatDropdown, false);
      };
      const openMobileWhat = () => {
        setVisible(mobileWhatModal, true);
        setVisible(mobileWhereModal, false);
      };
      const openMobileWhere = () => {
        setVisible(mobileWhereModal, true);
        setVisible(mobileWhatModal, false);
      };
      const submitSearch = () => {
        const url = buildSearchUrl(searchUrl, {
          what: state.what,
          where: state.where,
          mode: state.mode,
        });
        window.location.assign(url);
      };
      const renderDesktopWhat = (query) => {
        desktopWhatList.innerHTML = renderWhatItems(state.whatItems, query) || `
          <li>
            <div class="px-4 py-3 text-sm text-[#667085]">No matches found</div>
          </li>
        `;
      };
      const renderDesktopWhere = (query) => {
        const q = String(query || '').trim();
        const onlineChip = `
          <li>
            <button type="button" data-select-where="Online" data-select-mode="online" class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-left transition-colors hover:bg-[#F9FAFB]">
              <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#F4F6FB] text-[#344054]">
                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                  <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/>
                  <path d="M4 12h16M12 4c2.5 2.4 4 5.2 4 8s-1.5 5.6-4 8M12 4c-2.5 2.4-4 5.2-4 8s1.5 5.6 4 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
              </span>
              <span class="min-w-0 flex-1">
                <span class="block text-sm font-semibold text-[#101828]">Online</span>
                <span class="block text-xs text-[#667085]">Virtual sessions and classes</span>
              </span>
            </button>
          </li>
        `;
        const locationItems = renderWhereItems(state.whereItems, q);
        const headingWrap = el.querySelector('#desktop-location-list-heading-wrap');
        if (headingWrap) headingWrap.classList.toggle('hidden', !locationItems);
        desktopWhereList.innerHTML = [
          desktopUseLocation ? '' : onlineChip,
          desktopOnline ? '' : '',
          locationItems || '<li><div class="px-4 py-3 text-sm text-[#667085]">No locations found</div></li>',
        ].join('');
      };
      const renderMobileWhat = (query) => {
        mobileWhatList.innerHTML = renderWhatItems(state.whatItems, query) || '<li><div class="px-4 py-3 text-sm text-[#667085]">No matches found</div></li>';
      };
      const renderMobileWhere = (query) => {
        const q = String(query || '').trim();
        mobileWhereList.innerHTML = renderWhereItems(state.whereItems, q) || '<li><div class="px-4 py-3 text-sm text-[#667085]">No locations found</div></li>';
      };

      desktopForm.addEventListener('submit', (event) => {
        event.preventDefault();
        submitSearch();
      });
      mobileForm.addEventListener('submit', (event) => {
        event.preventDefault();
        submitSearch();
      });

      desktopWhatField?.addEventListener('click', () => {
        desktopWhatInput.focus();
        openDesktopWhat();
      });
      desktopWhereField?.addEventListener('click', () => {
        desktopWhereInput.focus();
        openDesktopWhere();
      });
      desktopWhatInput.addEventListener('focus', () => {
        openDesktopWhat();
        renderDesktopWhat(desktopWhatInput.value);
      });
      desktopWhatInput.addEventListener('input', () => {
        setWhat(desktopWhatInput.value);
        openDesktopWhat();
        renderDesktopWhat(desktopWhatInput.value);
      });
      desktopWhereInput.addEventListener('focus', () => {
        openDesktopWhere();
        renderDesktopWhere(desktopWhereInput.value);
      });
      desktopWhereInput.addEventListener('input', () => {
        setWhere(desktopWhereInput.value, state.mode === 'online' && desktopWhereInput.value !== 'Online' ? '' : state.mode);
        openDesktopWhere();
        renderDesktopWhere(desktopWhereInput.value);
      });
      desktopClearWhat?.addEventListener('click', () => {
        setWhat('');
        renderDesktopWhat('');
        desktopWhatInput.focus();
        openDesktopWhat();
      });
      desktopClearWhere?.addEventListener('click', () => {
        setWhere('', '');
        renderDesktopWhere('');
        desktopWhereInput.focus();
        openDesktopWhere();
      });
      desktopUseLocation?.addEventListener('click', async () => {
        desktopUseLocation.disabled = true;
        try {
          const label = await geocodeCurrentLocation(mapboxKey);
          setWhere(label || 'Current location', 'in-person');
          desktopWhereInput.value = state.where;
          renderDesktopWhere(state.where);
          closeDesktopDropdowns();
        } finally {
          desktopUseLocation.disabled = false;
        }
      });
      desktopOnline?.addEventListener('click', () => {
        setWhere('Online', 'online');
        desktopWhereInput.value = state.where;
        renderDesktopWhere(state.where);
        closeDesktopDropdowns();
      });

      mobileOpenWhat?.addEventListener('click', () => {
        mobileWhatInput.value = state.what;
        renderMobileWhat(mobileWhatInput.value);
        openMobileWhat();
      });
      mobileOpenWhere?.addEventListener('click', () => {
        mobileWhereInput.value = state.where;
        renderMobileWhere(mobileWhereInput.value);
        openMobileWhere();
      });
      mobileWhatInput.addEventListener('input', () => {
        setWhat(mobileWhatInput.value);
        renderMobileWhat(mobileWhatInput.value);
      });
      mobileWhereInput.addEventListener('input', () => {
        setWhere(mobileWhereInput.value, state.mode === 'online' && mobileWhereInput.value !== 'Online' ? '' : state.mode);
        renderMobileWhere(mobileWhereInput.value);
      });
      mobileUseLocation?.addEventListener('click', async () => {
        mobileUseLocation.disabled = true;
        try {
          const label = await geocodeCurrentLocation(mapboxKey);
          setWhere(label || 'Current location', 'in-person');
          renderMobileWhere(state.where);
          closeMobileModals();
        } finally {
          mobileUseLocation.disabled = false;
        }
      });
      mobileOnline?.addEventListener('click', () => {
        setWhere('Online', 'online');
        renderMobileWhere(state.where);
        closeMobileModals();
      });

      el.addEventListener('click', (event) => {
        const whatButton = event.target.closest('[data-select-what]');
        if (whatButton) {
          const value = whatButton.getAttribute('data-select-what') || '';
          setWhat(value);
          renderDesktopWhat(value);
          renderMobileWhat(value);
          closeDesktopDropdowns();
          closeMobileModals();
          desktopWhatInput.blur();
          mobileWhatInput.blur();
          return;
        }
        const whereButton = event.target.closest('[data-select-where]');
        if (whereButton) {
          const value = whereButton.getAttribute('data-select-where') || '';
          const mode = whereButton.getAttribute('data-select-mode') || '';
          setWhere(value, mode);
          renderDesktopWhere(value);
          renderMobileWhere(value);
          closeDesktopDropdowns();
          closeMobileModals();
          desktopWhereInput.blur();
          mobileWhereInput.blur();
          return;
        }
        if (event.target.closest('[data-close-mobile-modal]')) {
          closeMobileModals();
        }
      });

      document.addEventListener('click', (event) => {
        if (!el.contains(event.target)) {
          closeDesktopDropdowns();
          closeMobileModals();
        }
      });
      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
          closeDesktopDropdowns();
          closeMobileModals();
        }
      });

      Promise.all([
        fetchWhatCategories().catch(() => []),
        fetchLocations(12, '').catch(() => []),
      ]).then(([whatItems, whereItems]) => {
        state.whatItems = Array.isArray(whatItems) ? whatItems : [];
        state.whereItems = Array.isArray(whereItems) ? whereItems : [];
        syncInputs();
        renderDesktopWhat(state.what);
        renderDesktopWhere(state.where);
        renderMobileWhat(state.what);
        renderMobileWhere(state.where);
      }).catch(() => {
        state.whatItems = [];
        state.whereItems = [];
        syncInputs();
        renderDesktopWhat(state.what);
        renderDesktopWhere(state.where);
        renderMobileWhat(state.what);
        renderMobileWhere(state.where);
      });
    });
  } catch (err) {
    console.warn('[WOW] home search bar v4 bootstrap skipped', err);
  }
}

function closeHeaderDropdownExcept(source) {
  try {
    if (source !== 'account' && typeof window.__WOWCloseAccountDropdown === 'function') {
      window.__WOWCloseAccountDropdown();
    }
  } catch (_err) {}
  try {
    if (source !== 'cart' && typeof window.__WOWCloseCartDropdown === 'function') {
      window.__WOWCloseCartDropdown();
    }
  } catch (_err) {}
}

onDocumentReady(() => {
  runIdle(() => { try { initMegaMenu(); } catch (e) {} });
  runIdle(() => { try { initMobileMenu(); } catch (e) {} });
  runIdle(() => { try { ['home-template','home-sticky'].forEach(prefix => setupUltraSearchBar(prefix)); } catch (e) {} });
  runIdle(() => { try { mountSearchRangeCalendars(); } catch (e) {} });
  try { mountSearchBarV4(); } catch (e) {}
  try { mountHomeSearchBarV4(); } catch (e) {}
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
          var amt = (it.price!=null) ? money((Number(it.price)||0) * qty) : '';
          var variantRaw = (it.variant_label || it.subtitle || '').trim();
          var metaParts = [];
          if(variantRaw){
            var safeVariant = variantRaw.replace(/[&<>"']/g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[c]));
            metaParts.push(safeVariant);
          }
          metaParts.push('Qty '+qty);
          var removeBtn = '<button class="cartdd-remove remove-btn js-remove" type="button" aria-label="Remove item" data-remove="'+String(it.id||'')+'">'
            + '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">'
            +   '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6" />'
            + '</svg>'
            + '</button>';
          return '<div class="cartdd-item" data-id="'+String(it.id||'')+'">'
            + img
            + '<div class="cartdd-info"><a class="cartdd-title" href="'+(it.url||'#')+'">'+title+'</a><div class="cartdd-meta">'+metaParts.join(' • ')+'</div></div>'
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
        return items.map(function(x){
          return {
            title:x.title||x.name,
            qty:Number(x.qty||x.quantity||1),
            price:x.price_min||x.price,
            image:x.image||x.img,
            url:x.url||x.href,
            id:x.id,
            variant_label:x.variant_label||x.options_label||''
          };
        });
      }catch(_){ return []; }
    }
    function writeLocalCart(items){
      try{
        var bag={ items: items||[] };
        (items||[]).forEach(function(it){ if(it && typeof it.id!=='undefined'){ bag[String(it.id)] = it; } });
        localStorage.setItem('wow_cart', JSON.stringify(bag));
      }catch(_){ }
      try {
        var cookieItems = [];
        (items||[]).forEach(function(it){
          if(!it || typeof it.id === 'undefined') return;
          cookieItems.push({
            id: String(it.id),
            product_id: it.product_id || null,
            variant_id: it.variant_id || null,
            variant_label: it.variant_label || '',
            source_version: it.source_version || it.meta?.source_version || null,
            title: it.title || '',
            price: Number(it.price || it.unit || 0),
            qty: Number(it.qty || 1) || 1,
            image: it.image || it.img || '',
            url: it.url || '#'
          });
        });
        if (cookieItems.length) {
          var encoded = encodeURIComponent(JSON.stringify(cookieItems));
          document.cookie = 'wow_cart=; Path=/; Max-Age=0; SameSite=Lax';
          document.cookie = 'wow_cart=' + encoded + '; Domain=.weofferwellness.co.uk; Path=/; Max-Age=' + (60*60*24*30) + '; SameSite=Lax';
        } else {
          document.cookie = 'wow_cart=; Path=/; Max-Age=0; SameSite=Lax';
          document.cookie = 'wow_cart=; Domain=.weofferwellness.co.uk; Path=/; Max-Age=0; SameSite=Lax';
        }
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
          var serverItems = Array.isArray(data?.items) ? data.items : [];
          var localItems = readLocalCart();
          var byId = new Map();
          var storeProductIds = new Set(localItems.filter(function(item){ return String(item?.source_version || item?.meta?.source_version || '').toLowerCase() === 'store'; }).map(function(item){ return String(item.product_id || item.id || ''); }));
          serverItems.forEach(function(item){
            if(!item || item.id == null) return;
            if(storeProductIds.has(String(item.product_id || item.id))) return;
            byId.set(String(item.id), item);
          });
          localItems.forEach(function(item){
            if(!item || item.id == null) return;
            var key = String(item.id);
            // Store products are browser-cart lines until checkout; never let
            // an empty/legacy server response erase them.
            if(String(item.source_version || item.meta?.source_version || '').toLowerCase() === 'store' || !byId.has(key)) byId.set(key, item);
          });
          var items = Array.from(byId.values());
          writeLocalCart(items);
          renderItems(items);
        })
        .catch(() => { renderItems(readLocalCart()); });
    }
      function show(){
        if(!isDesktop()) return;
        closeHeaderDropdownExcept('cart');
        loadMini();
        try{ var hint = panel.querySelector('#freeShipHint'); if(hint) hint.textContent = 'Instant delivery'; }catch(_){}
        panel.hidden = false;
      }
    function hide(){ panel.hidden = true; }
    // Defer showing/rotation to the upsell-aware handler below
    wrap.addEventListener('mouseenter', () => { if (hideTimer) { clearTimeout(hideTimer); hideTimer=null; } });
    wrap.addEventListener('mouseleave', () => { hideTimer = setTimeout(hide, 120); });
    // Prevent default on desktop clicks to keep dropdown open; on mobile it navigates
    link.addEventListener('click', (e) => { if (isDesktop()) { e.preventDefault(); show(); } });
    // Expose helpers for external triggers (e.g., add-to-cart)
    try { window.__cartDropdownShow = show; } catch(_){ }
    try { window.__WOWCloseCartDropdown = hide; } catch(_){ }
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
      function slugifySegment(value){
        return String(value || '')
          .toLowerCase()
          .replace(/[^a-z0-9]+/g, '-')
          .replace(/^-+|-+$/g, '');
      }
      function buildUpsellUrl(item){
        if (item && item.url) return item.url;
        var formatSource = item && (
          item.format
          || (item.type && (item.type.slug || item.type.name))
          || item.type
          || 'therapies'
        );
        var modalitySource = item && (
          item.modality
          || (item.category && (item.category.slug || item.category.name))
          || item.category_name
          || item.category_label
          || (item.type && (item.type.modality || item.type.slug || item.type.name))
          || ''
        );
        var format = slugifySegment(formatSource);
        var modality = slugifySegment(modalitySource);
        var slug = slugifySegment(item && (item.slug || item.handle || item.title || item.name || item.id || ''));
        var sourceVersion = String((item && item.source_version) || (item && item.sourceType) || (item && item.source_type) || '').toLowerCase();
        var isStructuredOffering = sourceVersion === 'v3' || sourceVersion === 'offering';
        if (format && modality && slug) return '/' + format + '/' + modality + '/' + slug;
        if (format && slug) return '/' + format + '/' + slug;
        return '/' + (slug || 'offerings');
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
            var url = buildUpsellUrl(it);
            var title = esc(it.title||'');
            return '<div class="upsell-item">'
              + (img?('<img src="'+img+'" alt="">'):'<div style="width:46px;height:46px;border-radius:8px;background:#f3f5f7;border:1px solid #eceff3"></div>')
              + '<div><p class="upsell-title">'+title+'</p><div class="upsell-price">'+money(p)+'</div></div>'
              + '<button class="btn-wow btn-wow--outline btn-sm js-add-to-cart" data-id="'+it.id+'" data-product-id="'+it.id+'" data-title="'+title+'" data-price="'+p.toFixed(2)+'" data-image="'+img+'" data-url="'+url+'">Add</button>'
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
