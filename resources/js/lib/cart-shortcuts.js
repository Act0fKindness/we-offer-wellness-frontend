// Global shortcuts for opening the cart dropdown/popover
// Usage: add class `js-open-cart` or `open-cart-dropdown` to any clickable element
// This will dispatch the event consumed by the Blade header mini-cart JS.
(function(){
  function openCart(){
    try { window.dispatchEvent(new CustomEvent('wow:open-cart', { detail: { source: 'shortcut' } })) } catch {}
  }
  function onClick(e){
    var t = e.target.closest('.js-open-cart, .open-cart-dropdown');
    if (!t) return;
    e.preventDefault();
    openCart();
  }
  document.addEventListener('click', onClick, true);
})();

