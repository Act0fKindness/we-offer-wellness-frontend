// Minimal interactivity for header mega menu, mobile menu, and ultra search bar panes

function initMegaMenu() {
  const header = document.querySelector('header');
  const panel = document.getElementById('mega-panel');
  if (!header || !panel) return;
  const navLinks = header.querySelectorAll('[data-mega-menu]');
  navLinks.forEach((a) => {
    a.addEventListener('mouseenter', () => {
      const which = a.getAttribute('data-mega-menu');
      panel.setAttribute('data-active', which || '');
      panel.style.display = 'block';
    });
    a.addEventListener('focus', () => {
      const which = a.getAttribute('data-mega-menu');
      panel.setAttribute('data-active', which || '');
      panel.style.display = 'block';
    });
  });
  header.addEventListener('mouseleave', () => { panel.style.display = 'none'; });
  document.addEventListener('click', (e) => {
    if (!header.contains(e.target)) panel.style.display = 'none';
  });
}

function initMobileMenu() {
  const toggle = document.querySelector('header button[aria-label="Toggle menu"]');
  const drawer = document.getElementById('mobile-menu');
  if (!toggle || !drawer) return;
  toggle.addEventListener('click', () => {
    drawer.style.display = drawer.style.display === 'none' || !drawer.style.display ? 'block' : 'none';
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
});

