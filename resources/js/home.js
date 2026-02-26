// Minimal interactivity for header mega menu, mobile menu, and ultra search bar panes

function initMegaMenu() {
  const header = document.querySelector('header');
  const panel = document.getElementById('mega-panel');
  const overlay = document.getElementById('mega-overlay');
  if (!header || !panel) return;
  const navLinks = header.querySelectorAll('[data-mega-menu]');
  navLinks.forEach((a) => {
    a.addEventListener('mouseenter', () => {
      const which = a.getAttribute('data-mega-menu');
      panel.setAttribute('data-active', which || '');
      panel.style.display = 'block';
      if (overlay) overlay.style.display = 'block';
    });
    a.addEventListener('focus', () => {
      const which = a.getAttribute('data-mega-menu');
      panel.setAttribute('data-active', which || '');
      panel.style.display = 'block';
      if (overlay) overlay.style.display = 'block';
    });
  });
  header.addEventListener('mouseleave', () => { panel.style.display = 'none'; if (overlay) overlay.style.display = 'none'; });
  document.addEventListener('click', (e) => {
    if (!header.contains(e.target)) { panel.style.display = 'none'; if (overlay) overlay.style.display = 'none'; }
  });
}

function initMobileMenu() {
  const toggle = document.querySelector('header button[aria-label="Toggle menu"]');
  const drawer = document.getElementById('mobile-menu');
  if (!toggle || !drawer) return;
  toggle.addEventListener('click', () => {
    const isOpen = drawer.style.display === 'block';
    const nextOpen = !isOpen;
    drawer.style.display = nextOpen ? 'block' : 'none';
    toggle.classList.toggle('opened', nextOpen);
    toggle.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
  });
}

function setupUltraSearchBar(prefix) {
  const root = document.querySelector(`#${prefix}-root`) || document;
  function byId(s){ return document.getElementById(prefix + '-' + s); }
  const panes = ['what-pane','where-pane','when-pane','who-pane'];
  function hideAll(){ panes.forEach((id) => { const el = byId(id); if (el) el.classList.add('d-none'); }); const what = byId('what'); if (what) what.setAttribute('aria-expanded','false'); }
  function openPane(which){ hideAll(); const pane = byId(which+'-pane'); if(pane){ pane.classList.remove('d-none'); } if(which==='what'){ const what = byId('what'); if(what) what.setAttribute('aria-expanded','true'); } }
  const whatInput = byId('what'); if(whatInput){ whatInput.addEventListener('focus', ()=>openPane('what')); whatInput.addEventListener('input', ()=>openPane('what')); const segWhat = byId('seg-what'); if(segWhat){ segWhat.addEventListener('click', ()=>openPane('what')); } }
  const whereEditor = byId('where-editor'); if(whereEditor){ whereEditor.addEventListener('focus', ()=>openPane('where')); whereEditor.addEventListener('click', ()=>openPane('where')); }
  const whenInput = byId('when'); if(whenInput){ whenInput.addEventListener('focus', ()=>openPane('when')); whenInput.addEventListener('click', ()=>openPane('when')); }
  const whoSeg = byId('seg-who'); if(whoSeg){ whoSeg.addEventListener('click', ()=>openPane('who')); }
  document.addEventListener('click', (e)=>{ const container = document.querySelector('.wow-ultra'); if(container && !container.contains(e.target)) hideAll(); });
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') hideAll(); });
  const whatList = byId('what-list'); if(whatList && byId('what')){ whatList.addEventListener('click', (e)=>{ const btn = e.target.closest('.item'); if(btn && btn.dataset.value){ byId('what').value = btn.dataset.value; hideAll(); byId('what').blur(); } }); }
  const whereHidden = byId('where'); if(byId('where-list') && whereEditor){ byId('where-list').addEventListener('click', (e)=>{ const btn = e.target.closest('.item'); if(btn && btn.dataset.value){ whereEditor.textContent = btn.dataset.value; if(whereHidden) whereHidden.value = btn.dataset.value; hideAll(); } }); }
  const whoDone = byId('who-done'); if(whoDone){ whoDone.addEventListener('click', ()=>hideAll()); }
}

document.addEventListener('DOMContentLoaded', () => {
  try { initMegaMenu(); } catch {}
  try { initMobileMenu(); } catch {}
  try { ['home-template','home-sticky'].forEach(prefix => setupUltraSearchBar(prefix)); } catch {}
  try {
    // Cart dropdown: hover on desktop shows mini cart; mobile click navigates
    const wrap = document.querySelector('.cart-wrap');
    const link = document.querySelector('.cart-wrap .cart-link');
    const panel = document.getElementById('cart-dropdown');
    if (wrap && link && panel) {
      function isDesktop(){ try { return window.matchMedia('(min-width: 992px)').matches } catch { return true } }
      let loaded = false; let hideTimer = null;
      function loadMini(){
        if (loaded) return; loaded = true;
        fetch('/api/cart/mini?t='+Date.now(), { headers:{ 'Accept':'text/html' }, credentials:'same-origin' })
          .then(r=>r.ok?r.text():Promise.reject()).then(html => { panel.querySelector('#cartdd-body').innerHTML = html; })
          .catch(() => { /* keep empty state */ });
      }
      function show(){ if(!isDesktop()) return; loadMini(); panel.hidden = false; }
      function hide(){ panel.hidden = true; }
      wrap.addEventListener('mouseenter', () => { if (hideTimer) { clearTimeout(hideTimer); hideTimer=null; } show(); });
      wrap.addEventListener('mouseleave', () => { hideTimer = setTimeout(hide, 120); });
      // Prevent default on desktop clicks to keep dropdown open; on mobile it navigates
      link.addEventListener('click', (e) => { if (isDesktop()) { e.preventDefault(); show(); } });
    }
  } catch {}
});
