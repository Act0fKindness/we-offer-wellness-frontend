// Lightweight cart interactions: add-to-cart, badge count, and dropdown open
(() => {
  function qs(sel, root){ return (root||document).querySelector(sel); }
  function qsa(sel, root){ return Array.from((root||document).querySelectorAll(sel)); }
  function isDesktop(){ try{ return window.matchMedia('(min-width: 992px)').matches }catch(_){ return true } }
  function csrf(){ try{ return document.querySelector('meta[name="csrf-token"]').content || '' } catch(_){ return '' } }
  function cookie(name){ try{ return document.cookie.split('; ').find(r=>r.startsWith(name+'='))?.split('=')[1]||'' }catch(_){ return '' } }
  function money(n){ try{ var x=Number(n); if(x>=1000) x=x/100; return '£'+x.toFixed(2) }catch(_){ return '£0.00' } }

  const LS_KEY = 'wow_cart';
  function loadLS(){ try{ return JSON.parse(localStorage.getItem(LS_KEY)||'{}')||{} }catch(_){ return {} } }
  function saveLS(obj){ try{ localStorage.setItem(LS_KEY, JSON.stringify(obj||{})); }catch(_){ } }
  function getItems(){ const o=loadLS(); if(Array.isArray(o.items)) return o.items; // new schema
    // legacy keyed map fallback
    const out=[]; Object.keys(o||{}).forEach(k=>{ if(k!=='items'){ out.push(o[k]); } }); return out; }
  function setItems(items){
    const bag = { items: items||[] };
    (items||[]).forEach(it => { if(it && typeof it.id!=='undefined'){ bag[String(it.id)] = it; } });
    saveLS(bag);
  }
  function countItems(){ return getItems().reduce((sum,it)=> sum + (Number(it.qty||1)||1), 0); }
  function upsertItem(newItem){
    const items = getItems();
    const id = String(newItem.id||'');
    const idx = items.findIndex(it => String(it.id||'')===id);
    if(idx>=0){ items[idx].qty = Number(items[idx].qty||1) + Number(newItem.qty||1); }
    else { items.push({ id, title:newItem.title, qty:Number(newItem.qty||1)||1, price:newItem.price, image:newItem.image, url:newItem.url }); }
    setItems(items);
  }

  function updateBadgeUI(n){
    try{
      const b = qs('.cart-badge');
      if(!b) return;
      const c = Number(n||0);
      b.textContent = String(c);
      b.style.display = c>0 ? 'inline-block' : 'none';
    }catch(_){ }
  }
  function fetchCountAndUpdateBadge(){
    return fetch('/api/cart/count', { credentials:'same-origin' })
      .then(r=>r.json())
      .then(j => { updateBadgeUI(Number(j.count||0)); })
      .catch(()=>{ updateBadgeUI(countItems()); });
  }

  // Expose dropdown open/render hooks if available
  const showDropdown = () => { try{ (window.__cartDropdownShow||function(){})(); }catch(_){ } };
  const renderDropdownFromLS = () => {
    try{
      const items = getItems();
      (window.__cartDropdownRender||function(){ })(items);
    }catch(_){ }
  };

  function serverAdd(payload){
    return fetch('/api/cart/add', {
      method:'POST',
      headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrf() || decodeURIComponent(cookie('XSRF-TOKEN')||'') },
      credentials:'same-origin',
      body: JSON.stringify({ id: payload.id, qty: payload.qty||1 })
    }).then(r => r.json()).catch(()=>{ throw new Error('server failed'); });
  }

  function addToCart(payload){
    // Always update local storage for resilience
    upsertItem(payload);
    // Try server add; on success refresh count; on failure, use LS count
    serverAdd(payload).then(()=>{
      fetchCountAndUpdateBadge();
    }).catch(()=>{
      updateBadgeUI(countItems());
    }).finally(()=>{
      if (isDesktop()) { renderDropdownFromLS(); showDropdown(); }
      else { /* mobile keep as is, icon navigates to /cart */ }
    });
  }

  function handleAddFromBtn(btn, ev){
    if (ev){ try{ ev.preventDefault(); ev.stopPropagation(); }catch(_){} }
    const id = btn.getAttribute('data-id');
    const title = btn.getAttribute('data-title')||'';
    const price = Number(btn.getAttribute('data-price')||'0');
    const image = btn.getAttribute('data-image')||'';
    const url = btn.getAttribute('data-url')||'';
    addToCart({ id, title, price, image, url, qty:1 });
  }
  // Delegate add-to-cart clicks (capture to beat anchor navigation)
  document.addEventListener('click', function(e){
    const btn = e.target.closest('.js-add-to-cart');
    if(!btn) return;
    handleAddFromBtn(btn, e);
  }, true);
  // Expose global helper for inline fallbacks
  try{ window.WOW_addToCart = function(el){ handleAddFromBtn(el); return false; }; }catch(_){ }

  // On load, set badge from server or LS
  fetchCountAndUpdateBadge();
})();
