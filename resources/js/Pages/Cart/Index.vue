<script setup>
import { Head } from '@inertiajs/vue3'
import { computed, ref, onMounted, nextTick } from 'vue'
import { useCart } from '@/stores/cart'
import SiteLayout from '@/Layouts/SiteLayout.vue'

const props = defineProps({ sessionPromo: { type: String, default: '' }, sessionGift: { type: String, default: '' } })
const cart = useCart()
const items = computed(() => cart.items.value || cart.items)
const subtotal = computed(() => cart.subtotal.value ?? cart.subtotal)
const safetyLink = '/safety-and-contraindications'

// Demo promo + gift logic (client-side)
const PROMOS = { WOW10:{ type:'percent', value:10, label:'10% off' }, WELCOME5:{ type:'fixed', value:5, label:'£5 off' } }
const GIFTCARDS = { GIFT25:25, GIFT50:50, GIFT100:100 }
const SHIP_COST_1DAY = 4.95

const promoCode = ref('')
const promoMsg = ref('')
const giftCode = ref('')
const giftMsg = ref('')
const appliedPromo = ref(null)
const appliedGift = ref(null)
const showPromo = ref(false)
const showGift = ref(false)

const needsShipping = computed(() => (items.value||[]).some(it => (it?.meta?.type==='physical') || (it?.type==='physical')))
const discountValue = computed(() => {
  const code = String(appliedPromo.value||'').toUpperCase()
  const p = PROMOS[code]
  const sub = Number(subtotal.value||0)
  if (!p) return 0
  if (p.type==='fixed') return Math.min(sub, p.value)
  if (p.type==='percent') return +(sub * (p.value/100)).toFixed(2)
  return 0
})
const shippingValue = computed(() => needsShipping.value ? SHIP_COST_1DAY : 0)
const giftValue = computed(() => {
  const code = String(appliedGift.value||'').toUpperCase()
  const val = GIFTCARDS[code] || 0
  const afterDisc = Math.max(0, Number(subtotal.value||0) - discountValue.value + shippingValue.value)
  return Math.min(afterDisc, val)
})
const grandTotal = computed(() => Math.max(0, Number(subtotal.value||0) - discountValue.value + shippingValue.value - giftValue.value))
const nowTs = ref(Date.now())
let tick = null
function mmss(ms){ const total=Math.max(0,Math.ceil(ms/1000)); const m=String(Math.floor(total/60)).padStart(2,'0'); const s=String(total%60).padStart(2,'0'); return `${m}:${s}` }
function holdRemaining(it){ try{ const t = String(it?.meta?.holdExpiresAt||''); if(!t) return 0; const until = new Date(t).getTime(); return Math.max(0, until - nowTs.value) } catch { return 0 } }
async function releaseHold(it){
  try{
    const id = it?.meta?.reservationId
    if (!id) return
    const token = document.querySelector('meta[name="csrf-token"]')?.content || ''
    await fetch('/api/reservations/release', { method:'POST', headers:{ 'Content-Type':'application/json', ...(token?{'X-CSRF-TOKEN':token}:{}) }, body: JSON.stringify({ id }) })
  }catch{}
}
function clearExpiredHolds(){
  try{
    const arr = items.value || []
    arr.forEach(async (it) => {
      const rem = holdRemaining(it)
      if (rem<=0 && (it?.meta?.holdExpiresAt||it?.meta?.reservationId)){
        await releaseHold(it)
        try { if(it.meta){ delete it.meta.holdExpiresAt; delete it.meta.reservationId; if (it.meta.booking){ delete it.meta.booking.time; delete it.meta.booking.date } } } catch {}
      }
    })
  }catch{}
}

function fmt(n) { try { return new Intl.NumberFormat('en-GB', { style: 'currency', currency: 'GBP' }).format(Number(n||0)) } catch { return `£${Number(n||0).toFixed(2)}` } }
function lineTotal(it){ return Number(it.price||0) * Math.max(1, Number(it.qty)||1) }
function dec(it){ cart.updateQty(it.id, Math.max(1, Number(it.qty||0)-1)) }
function inc(it){ cart.updateQty(it.id, Math.max(1, Number(it.qty||0)+1)) }
function bookingText(b){
  try{
    const hasDate = (b?.date||'').trim(); const hasTime = (b?.time||'').trim()
    if (hasDate){
      const d = new Date(b.date)
      const ds = isNaN(d.getTime()) ? b.date : d.toLocaleDateString(undefined,{ weekday:'short', day:'numeric', month:'short' })
      return hasTime ? `${ds} • ${b.time}` : `${ds}`
    }
    if (hasTime) return String(b.time)
    if ((b?.when||'')==='now') return 'Book now'
    return ''
  }catch{ return '' }
}

function variantSummary(it){
  const parts = []
  if (it?.meta?.format) parts.push(it.meta.format)
  if (it?.meta?.duration) parts.push(it.meta.duration)
  if (it?.meta?.groupCount) parts.push(`${it.meta.groupCount} people`)
  if (Array.isArray(it?.meta?.selected) && it.meta.selected.length) parts.push(it.meta.selected.join(' • '))
  return parts.filter(Boolean).join(' • ')
}

// Clear + undo
let snapshot = null
function clearCart(){ snapshot = (items.value||[]).map(x=>({ ...x })) ; cart.clear(); showToast('Cart cleared.', true) }
function undoClear(){ if (snapshot && Array.isArray(snapshot)) { snapshot.forEach(it => cart.add(it)); snapshot=null } }

// Promo + gift actions
function applyPromo(){
  const code = String(promoCode.value||'').trim().toUpperCase()
  if (!code) { promoMsg.value = 'Enter a discount code.'; return }
  if (!PROMOS[code]) { promoMsg.value = 'Code not recognised.'; shake('#promoInput'); return }
  appliedPromo.value = code; promoMsg.value = `Discount “${code}” applied.`; showToast(`Discount “${code}” applied.`)
  setCookie('wow_promo_code', code, 60)
  saveSession('promo', code)
}
function removePromo(){ appliedPromo.value=null; promoMsg.value='' }
function applyGift(){
  const code = String(giftCode.value||'').trim().toUpperCase()
  if (!code) { giftMsg.value = 'Enter a gift card code.'; return }
  if (!(code in GIFTCARDS)) { giftMsg.value='Code not recognised.'; shake('#giftInput'); return }
  appliedGift.value = code; giftMsg.value = `Gift card “${code}” applied.`; showToast(`Gift card “${code}” applied.`)
  setCookie('wow_gift_code', code, 60)
  saveSession('gift', code)
}
function removeGift(){ appliedGift.value=null; giftMsg.value='' }

// Snackbar toast
const snackOpen = ref(false)
const snackText = ref('')
const canUndo = ref(false)
function showToast(text, undo=false){ snackText.value = text; canUndo.value = !!undo; snackOpen.value = true; clearTimeout(showToast._t); showToast._t = setTimeout(()=>snackOpen.value=false, 3200) }

// Swipe to remove + long‑press stepper
function attachGestures(){
  nextTick(() => {
    document.querySelectorAll('.row-item').forEach(row => {
      const id = row.getAttribute('data-id');
      let startX=0, dx=0, swiping=false; const TH=88
      row.addEventListener('pointerdown', e=>{ startX=e.clientX; dx=0; swiping=true; row.classList.add('swiping'); row.setPointerCapture?.(e.pointerId) })
      row.addEventListener('pointermove', e=>{ if(!swiping) return; dx=e.clientX-startX; if(dx<0){ row.style.transform=`translateX(${dx}px)` } })
      function end(){ if(!swiping) return; swiping=false; row.classList.remove('swiping'); if(Math.abs(dx)>TH){ row.style.transform='translateX(-120%)'; setTimeout(()=>{ try { cart.remove(id) } catch{} }, 80) } else { row.style.transform='translateX(0)' } }
      row.addEventListener('pointerup', end); row.addEventListener('pointercancel', end); row.addEventListener('pointerleave', end)
    })

    // Long-press for stepper
    const guards = new Map()
    function bindLP(sel, action){
      document.querySelectorAll(sel).forEach(btn => {
        const key = Math.random().toString(36).slice(2); guards.set(key,{timer:null, interval:null})
        const start=(e)=>{ e.preventDefault?.(); const g=guards.get(key); action(); g.timer=setTimeout(()=>{ g.interval=setInterval(action,120) }, 400) }
        const stop=()=>{ const g=guards.get(key); if(!g) return; clearTimeout(g.timer); clearInterval(g.interval); g.timer=null; g.interval=null }
        btn.addEventListener('mousedown', start); btn.addEventListener('touchstart', start, { passive:false })
        ;['mouseup','mouseleave','touchend','touchcancel','pointerup','pointercancel'].forEach(ev=> btn.addEventListener(ev, stop))
      })
    }
    document.querySelectorAll('.row-item').forEach(row => {
      const id = row.getAttribute('data-id');
      const it = (items.value||[]).find(x=>String(x.id)===String(id))
      if (!it) return
      bindLP(`[data-dec="${id}"]`, ()=> dec(it))
      bindLP(`[data-inc="${id}"]`, ()=> inc(it))
    })
  })
}

onMounted(() => {
  attachGestures()
  tick = setInterval(()=>{ nowTs.value = Date.now(); clearExpiredHolds() }, 1000)
})
try{ onUnmounted(()=>{ if(tick) clearInterval(tick) }) }catch{}

// Persist helpers (cookies + session API)
function setCookie(name, value, days){ try{ const d = new Date(); d.setTime(d.getTime()+days*24*60*60*1000); document.cookie = `${name}=${encodeURIComponent(value)}; expires=${d.toUTCString()}; path=/` }catch{} }
function readCookie(name){ try{ const m = document.cookie.match(new RegExp('(?:^|; )'+name.replace(/([.$?*|{}()\[\]\\\/\+^])/g,'\\$1')+'=([^;]*)')); return m? decodeURIComponent(m[1]) : '' }catch{ return '' } }
async function saveSession(kind, code){ try{ const token = document.querySelector('meta[name="csrf-token"]')?.content || ''; await fetch(kind==='promo'?'/api/cart/promo':'/api/cart/gift', { method:'POST', headers:{ 'Content-Type':'application/json', ...(token?{'X-CSRF-TOKEN':token}:{}) }, body: JSON.stringify({ code }) }) }catch{} }

onMounted(() => {
  // Preload from session props, fallback to cookies
  const sp = String(props.sessionPromo||'').trim().toUpperCase() || readCookie('wow_promo_code').toUpperCase()
  if (sp){ appliedPromo.value = sp; promoCode.value = sp; showPromo.value = true; promoMsg.value = `Discount “${sp}” applied.` }
  const sg = String(props.sessionGift||'').trim().toUpperCase() || readCookie('wow_gift_code').toUpperCase()
  if (sg){ appliedGift.value = sg; giftCode.value = sg; showGift.value = true; giftMsg.value = `Gift card “${sg}” applied.` }
})
</script>

<template>
  <Head title="Your Cart" />
  <SiteLayout>
    <section class="section">
      <div class="container-page">
        <div class="wrap">
          <!-- Header -->
          <header class="head">
            <h1>Your cart</h1>
            <button v-if="items.length" class="btn-reset" @click="clearCart">Clear</button>
          </header>

          <!-- Main grid -->
          <main class="grid">
            <!-- Cart list -->
            <section id="cart" class="cart" aria-live="polite">
              <!-- Empty -->
              <div v-if="items.length===0" class="empty">
                Your cart is empty.<br /><br />
                <a href="/search" class="btn btn-secondary">Continue shopping</a>
              </div>

              <!-- Items -->
              <article v-for="it in items" :key="it.id" class="item" :data-id="it.id" v-else>
                <div class="thumb">
                  <img v-if="it.image" :src="it.image" alt="" />
                </div>
                <div>
                  <div class="item-title-row">
                    <span v-if="it.meta?.displayType" class="pill type-pill">{{ it.meta.displayType }}</span>
                    <div class="item-title">{{ (it.title||'').toUpperCase() }}</div>
                  </div>
                  <div class="pills">
                    <span v-if="it.meta?.location" class="pill">{{ it.meta.location }}</span>
                    <span v-for="(sel,idx) in (it.meta?.selected||[])" :key="idx" class="pill">{{ sel }}</span>
                    <span v-if="it.meta?.groupCount" class="pill">Group: {{ it.meta.groupCount }}</span>
                    <span v-if="it.meta?.booking && (it.meta.booking.date || it.meta.booking.time)" class="pill">{{ bookingText(it.meta.booking) }}</span>
                    <span v-if="holdRemaining(it)>0" class="pill">Hold: {{ mmss(holdRemaining(it)) }}</span>
                  </div>
                  <div class="item-subline" v-if="it.meta?.practitioner">Practitioner: {{ it.meta.practitioner }}</div>
                  <div class="item-subline" v-if="variantSummary(it)">{{ variantSummary(it) }}</div>
                  <div class="item-subline" v-if="it.meta?.nextAvailability">Next: {{ it.meta.nextAvailability }}</div>
                  <a :href="it.url" class="change-link">Change date &amp; time</a>
                  <div class="controls">
                    <div class="stepper" role="group" :aria-label="`Change quantity for ${it.title}`">
                      <button class="step" :data-dec="it.id" aria-label="Decrease" @click="dec(it)">–</button>
                      <input class="qty" :value="it.qty" inputmode="numeric" pattern="[0-9]*" aria-label="Quantity" @input="e => cart.updateQty(it.id, e.target.value.replace(/[^\\d]/g,''))" />
                      <button class="step" :data-inc="it.id" aria-label="Increase" @click="inc(it)">+</button>
                    </div>
                    <button class="remove" @click="cart.remove(it.id)" aria-label="Remove"></button>
                  </div>
                  <div class="item-safety">
                    <span class="booking-card__icon" aria-hidden="true"></span>
                    Please review <a :href="safetyLink">Safety &amp; Contraindications</a> before your session.
                  </div>
                </div>
                <div class="price">{{ fmt(lineTotal(it)) }}</div>
                <span class="trash-ghost" aria-hidden="true"></span>
              </article>
            </section>

            <!-- Summary -->
            <aside class="summary" aria-label="Order summary">
              <div class="line"><strong>Subtotal</strong><strong>{{ fmt(subtotal) }}</strong></div>
              <div class="muted" style="margin-top:-6px">No shipping for therapies. Physical items ship 1‑day.</div>

              <!-- Discount code reveal -->
              <div v-if="!appliedPromo && !showPromo" class="muted small">
                <button type="button" class="toggle-link" @click="showPromo=true">Have a discount code?</button>
              </div>
              <div v-if="appliedPromo" class="tag">
                <span>{{ appliedPromo }}</span>
                <button type="button" class="remove-tag" @click="removePromo(); setCookie('wow_promo_code','',-1); saveSession('promo','');">×</button>
              </div>
              <div id="promoWrap" class="promo" v-show="showPromo || appliedPromo">
                <input id="promoInput" class="input" placeholder="Discount code" aria-label="Discount code" v-model="promoCode" />
                <button id="apply" class="btn btn-secondary" @click="applyPromo">Apply</button>
              </div>
              <div id="promoMsg" class="muted" aria-live="polite">{{ promoMsg }}</div>

              <!-- Gift card reveal -->
              <div v-if="!appliedGift && !showGift" class="muted small">
                <button type="button" class="toggle-link" @click="showGift=true">Have a gift card?</button>
              </div>
              <div v-if="appliedGift" class="tag">
                <span>{{ appliedGift }}</span>
                <button type="button" class="remove-tag" @click="removeGift(); setCookie('wow_gift_code','',-1); saveSession('gift','');">×</button>
              </div>
              <div id="gcWrap" class="promo" v-show="showGift || appliedGift">
                <input id="giftInput" class="input" placeholder="Gift card code" aria-label="Gift card code" v-model="giftCode" />
                <button id="applyGift" class="btn btn-secondary" @click="applyGift">Apply</button>
              </div>
              <div id="giftMsg" class="muted" aria-live="polite">{{ giftMsg }}</div>

              <div class="line" style="margin-top:8px"><span>Discount</span><span>{{ discountValue ? '– '+fmt(discountValue) : '– £0.00' }}</span></div>
              <div class="line" v-show="needsShipping">
                <span id="shippingLabel">Delivery (1‑day)</span><span id="shipping">{{ fmt(shippingValue) }}</span>
              </div>
              <div class="line" v-show="giftValue">
                <span>Gift card</span><span id="giftvalue">– {{ fmt(giftValue) }}</span>
              </div>
              <div class="line" style="border-top:1px solid var(--border);padding-top:14px">
                <div style="font-weight:900">Total</div>
                <div id="total" style="font-weight:900">{{ fmt(grandTotal) }}</div>
              </div>

              <div class="trust">
                <div class="trust-row"><span class="ic ic-shield"></span><div><strong>Secure checkout</strong><div class="muted">256‑bit SSL • PCI‑DSS</div></div></div>
                <div class="trust-row"><span class="ic ic-verify"></span><div><strong>Trusted providers</strong><div class="muted">Vetted & reviewed</div></div></div>
              </div>

              <div class="line" style="border-top:1px solid var(--border)"></div>
              <div style="display:grid;gap:10px">
                <a id="checkout" href="/checkout" class="btn btn-primary">Checkout</a>
                <a id="continue" href="/search" class="btn btn-secondary">Continue shopping</a>
              </div>
            </aside>
          </main>
        </div>

        <!-- Mobile sticky checkout bar -->
        <div class="sticky-bar d-lg-none" role="region" aria-label="Quick checkout" v-if="items.length">
          <div>
            <div class="muted" style="font-size:12px;margin-bottom:2px">Total</div>
            <div id="stickyTotal" class="sticky-total">{{ fmt(grandTotal) }}</div>
          </div>
          <a id="stickyCheckout" href="/checkout" class="btn btn-primary sticky-cta">Checkout</a>
        </div>

        <!-- Snackbar -->
        <div :class="['snack', snackOpen && 'show']" role="status" aria-live="polite">
          <span id="snackText">{{ snackText }}</span>
          <button v-if="canUndo" id="undo" class="undo" @click="undoClear">Undo</button>
        </div>
      </div>
    </section>
  </SiteLayout>
</template>

<style scoped>
.empty .ico-wrap{ display:flex; align-items:center; justify-content:center }
.empty .ico-wrap i{ font-size: 36px; color: var(--brand-600); }
.h-max{ height: max-content }
/* Wrap */
.wrap{ max-width:980px; margin-inline:auto; padding:clamp(16px,3vw,28px) }
/* Header */
.head{ display:flex; justify-content:space-between; align-items:center; margin:2px 0 14px }
.title{ font-weight:800; letter-spacing:.2px; font-size:clamp(28px,4.6vw,44px) }
.btn-reset{ border:1px solid var(--ink-200); background:#fff; color:#111827; font-weight:700; padding:10px 14px; border-radius:12px; transition:transform .22s cubic-bezier(.2,.8,.2,1), background .22s cubic-bezier(.2,.8,.2,1) }
.btn-reset:hover{ background:#f3f4f6; transform:translateY(-1px) }
.btn-reset:focus-visible{ outline:3px solid color-mix(in srgb, var(--brand-600) 30%, transparent); outline-offset:2px }
/* Layout */
.grid{ display:grid; gap:16px }
@media (min-width:992px){ .grid{ grid-template-columns:1.1fr .9fr } }
/* Cart list */
.cart{ display:grid; gap:12px }
.item{ position:relative; display:grid; grid-template-columns:86px 1fr auto; gap:14px; padding:14px; border:1px solid var(--ink-200); border-radius:16px; background:#fff; touch-action:pan-y; transition:transform .22s cubic-bezier(.2,.8,.2,1), box-shadow .22s cubic-bezier(.2,.8,.2,1) }
.item:hover{ transform:translateY(-2px) }
.thumb{ width:86px; height:86px; border-radius:12px; overflow:hidden; background:#e5e7eb }
.thumb img{ width:100%; height:100%; object-fit:cover; transform:scale(1.02); transition:transform 420ms cubic-bezier(.2,.8,.2,1) }
.item:hover .thumb img{ transform:scale(1.05) }
.item-title{ font-weight:800; margin:2px 0 6px; font-size:15px }
.item-title-row{ display:flex; align-items:center; gap:6px; flex-wrap:wrap }
.type-pill{ background:#e0e7ff; color:#312e81 }
.pills{ display:flex; flex-wrap:wrap; gap:6px; margin-bottom:8px }
.pill{ border:1px solid var(--ink-200); background:#fff; color:#334155; font-weight:700; font-size:12px; padding:6px 10px; border-radius:999px; line-height:1 }
.item-subline{ font-size:13px; color:#475569 }
.change-link{ display:inline-block; margin:4px 0 6px; font-size:13px; color:var(--brand-600); text-decoration:underline }
.controls{ display:flex; align-items:center; gap:10px }
.stepper{ display:inline-flex; align-items:center; border:1px solid var(--ink-200); border-radius:12px; padding:4px; background:#fff }
.step{ width:30px; height:30px; border:0; border-radius:10px; background:#fff; font-weight:900; cursor:pointer; transition:background .22s cubic-bezier(.2,.8,.2,1), transform .22s cubic-bezier(.2,.8,.2,1) }
.step:hover{ background:#f4f4f5 }
.step:active{ transform:scale(.98) }
.qty{ width:42px; border:0; text-align:center; font-weight:800; background:transparent; outline:0 }
.remove{ width:34px; height:34px; border:0; border-radius:10px; background:#fff; position:relative; cursor:pointer }
.remove::before{ content:""; position:absolute; inset:0; margin:auto; width:18px; height:18px; background:#64748b; -webkit-mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/solid/trash.svg') no-repeat center/contain; mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/solid/trash.svg') no-repeat center/contain; transition:background .22s cubic-bezier(.2,.8,.2,1) }
.remove:hover{ background:#fef2f2 }
.remove:hover::before{ background: var(--danger) }
.price{ font-weight:800; align-self:center; min-width:100px; text-align:right }
.trash-ghost{ position:absolute; right:12px; top:50%; transform:translateY(-50%); width:22px; height:22px; opacity:0; background:var(--danger); border-radius:8px; -webkit-mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/solid/trash.svg') no-repeat center/contain; mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/solid/trash.svg') no-repeat center/contain; transition:opacity .22s cubic-bezier(.2,.8,.2,1) }
.item.swiping .trash-ghost{ opacity:.9 }
.item-safety{ display:flex; align-items:center; gap:6px; font-size:12px; color:#475569; margin-top:6px }
.item-safety a{ color:inherit; text-decoration:underline }
.booking-card__icon{ width:14px; height:14px; background:#64748b; -webkit-mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/20/solid/shield-exclamation.svg') no-repeat center/contain; mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/20/solid/shield-exclamation.svg') no-repeat center/contain }
.empty{ padding:26px; text-align:center; color: var(--ink-600); border:1px dashed var(--ink-200); border-radius:16px }
/* Summary */
.summary{ padding:16px; border:1px solid var(--ink-200); border-radius:16px; background:#fff }
.line{ display:flex; justify-content:space-between; align-items:center; padding:10px 0 }
.line + .line{ border-top:1px dashed var(--ink-200) }
.muted{ color: var(--ink-600) }
.promo{ display:grid; grid-template-columns:1fr auto; gap:8px; margin:10px 0 12px }
.input{ border:1px solid var(--ink-200); background:#fff; border-radius:12px; padding:12px 14px; font-weight:700; transition:border-color .22s cubic-bezier(.2,.8,.2,1), box-shadow .22s cubic-bezier(.2,.8,.2,1) }
.input:focus{ border-color: color-mix(in srgb, var(--brand-600) 40%, white); box-shadow:0 0 0 6px color-mix(in srgb, var(--brand-600) 20%, transparent) }
.btn{ border:0; border-radius:12px; padding:14px 16px; font-weight:800; cursor:pointer; transition:transform .22s cubic-bezier(.2,.8,.2,1), opacity .22s ease }
.btn:active{ transform:scale(.99) }
.btn-primary{ background: var(--brand-600); color:#fff; text-align:center }
.btn-primary:hover{ filter:brightness(.97) }
.btn-secondary{ background:#f3f4f6; color: var(--ink-900); text-align:center }
.btn[disabled]{ opacity:.6; pointer-events:none }
.trust{ display:grid; gap:10px; margin-top:12px; color: var(--ink-600) }
.trust-row{ display:flex; gap:10px }
.ic{ width:18px; height:18px; background:#111827 }
.ic-shield{ -webkit-mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/outline/shield-check.svg') no-repeat center/contain; mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/outline/shield-check.svg') no-repeat center/contain }
.ic-verify{ -webkit-mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/outline/check-badge.svg') no-repeat center/contain; mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/outline/check-badge.svg') no-repeat center/contain }
/* Sticky */
.sticky-bar{ position:fixed; left:0; right:0; bottom:0; background:#fff; border-top:1px solid var(--ink-200); display:flex; gap:10px; align-items:center; justify-content:space-between; padding:10px 14px; z-index:50 }
.sticky-total{ font-weight:900 }
.sticky-cta{ min-width:44% }
@media (min-width:992px){ .sticky-bar{ display:none } }
/* Snackbar */
.snack{ position:fixed; left:50%; bottom:16px; transform:translate(-50%,100%); background:#111827; color:#fff; border-radius:12px; padding:10px 12px; display:flex; gap:12px; align-items:center; transition:transform .22s cubic-bezier(.2,.8,.2,1), opacity .22s ease; opacity:0; z-index:60 }
.snack.show{ transform:translate(-50%,0); opacity:1 }
.snack .undo{ border:1px solid #ffffff33; background:transparent; color:#fff; border-radius:10px; padding:8px 10px; font-weight:800 }
/* Responsive tweaks */
@media (max-width:575.98px){ .item{ grid-template-columns:72px 1fr auto } .thumb{ width:72px; height:72px } }
/* Toggle links + tag */
.toggle-link{ border:0; background:transparent; color: var(--brand-600); font-weight:700; padding:0 }
.toggle-link:hover{ text-decoration: underline }
.tag{ display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px; border:1px solid var(--ink-200); font-weight:700; margin:6px 0 }
.remove-tag{ border:0; background:transparent; font-weight:900; padding:0 4px; cursor:pointer }
</style>
