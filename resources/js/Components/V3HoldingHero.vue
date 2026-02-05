<template>
  <section class="v3-holding" id="v3-holding">
    <div class="v3-holding__inner">
      <div class="v3-holding__copy">
        <p class="eyebrow">We Offer Wellness®</p>
        <h1>v3 is almost here.</h1>
        <p class="lede">Join the launch list to hear first, unlock cash giveaways, early discounts and access to the widest holistic therapy directory.</p>
        <div class="cta-row">
          <button class="cta-btn" type="button" @click="openModal">
            <span>Notify me</span>
            <svg viewBox="0 0 28 28" aria-hidden="true"><path d="M4 14h20m-8-8 8 8-8 8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
          </button>
          <p class="cta-note">No spam. Quick updates + early rewards only.</p>
        </div>
        <p v-if="success" class="text-success mt-3">Thanks — you’re on the list!</p>
        <ul class="benefits">
          <li>Cash giveaways + early discounts</li>
          <li>Launch alerts & behind-the-scenes updates</li>
          <li>First access to the most complete holistic directory</li>
        </ul>
      </div>
      <div class="v3-holding__art" aria-hidden="true">
        <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <radialGradient id="blobGradient" cx="50%" cy="50%" r="75%">
              <stop offset="0%" stop-color="#f6a972" />
              <stop offset="50%" stop-color="#f6c8a2" />
              <stop offset="100%" stop-color="#ffe1ce" />
            </radialGradient>
          </defs>
          <image href="https://images.unsplash.com/photo-1488161628813-04466f872be2?auto=format&fit=crop&w=764&q=80" width="200" height="200" preserveAspectRatio="xMidYMid slice" clip-path="url(#blobClip)"></image>
          <clipPath id="blobClip">
            <path d="M43.1,-68.5C56.2,-58.6,67.5,-47.3,72.3,-33.9C77.2,-20.5,75.5,-4.9,74.2,11.3C72.9,27.6,71.9,44.5,63.8,57.2C55.7,69.8,40.6,78.2,25.5,79.2C10.4,80.1,-4.7,73.6,-20.9,69.6C-37.1,65.5,-54.5,63.9,-66,54.8C-77.5,45.8,-83.2,29.3,-85.7,12.3C-88.3,-4.8,-87.7,-22.3,-79.6,-34.8C-71.5,-47.3,-55.8,-54.9,-41.3,-64.2C-26.7,-73.6,-13.4,-84.7,0.8,-86C15,-87.2,29.9,-78.5,43.1,-68.5Z" transform="translate(100 100)"></path>
          </clipPath>
          <path class="blob" d="M43.1,-68.5C56.2,-58.6,67.5,-47.3,72.3,-33.9C77.2,-20.5,75.5,-4.9,74.2,11.3C72.9,27.6,71.9,44.5,63.8,57.2C55.7,69.8,40.6,78.2,25.5,79.2C10.4,80.1,-4.7,73.6,-20.9,69.6C-37.1,65.5,-54.5,63.9,-66,54.8C-77.5,45.8,-83.2,29.3,-85.7,12.3C-88.3,-4.8,-87.7,-22.3,-79.6,-34.8C-71.5,-47.3,-55.8,-54.9,-41.3,-64.2C-26.7,-73.6,-13.4,-84.7,0.8,-86C15,-87.2,29.9,-78.5,43.1,-68.5Z" transform="translate(100 100)" fill="url(#blobGradient)"></path>
          <path id="textPath" d="M43.1,-68.5C56.2,-58.6,67.5,-47.3,72.3,-33.9C77.2,-20.5,75.5,-4.9,74.2,11.3C72.9,27.6,71.9,44.5,63.8,57.2C55.7,69.8,40.6,78.2,25.5,79.2C10.4,80.1,-4.7,73.6,-20.9,69.6C-37.1,65.5,-54.5,63.9,-66,54.8C-77.5,45.8,-83.2,29.3,-85.7,12.3C-88.3,-4.8,-87.7,-22.3,-79.6,-34.8C-71.5,-47.3,-55.8,-54.9,-41.3,-64.2C-26.7,-73.6,-13.4,-84.7,0.8,-86C15,-87.2,29.9,-78.5,43.1,-68.5Z" transform="translate(100 100)" fill="none" stroke="none" pathLength="100"></path>
          <text class="text-content">
            <textPath href="#textPath" startOffset="0%">❤ MADE WITH LOVE ❤ MADE WITH LOVE ❤ MADE WITH LOVE ❤ MADE WITH LOVE
              <animate attributeName="startOffset" from="0%" to="100%" dur="15s" repeatCount="indefinite"></animate>
            </textPath>
            <textPath href="#textPath" startOffset="100%">❤ MADE WITH LOVE ❤ MADE WITH LOVE ❤ MADE WITH LOVE ❤ MADE WITH LOVE
              <animate attributeName="startOffset" from="-100%" to="0%" dur="15s" repeatCount="indefinite"></animate>
            </textPath>
          </text>
        </svg>
      </div>
    </div>
  </section>
  <div v-if="modalOpen" class="v3-modal" role="dialog" aria-modal="true" aria-labelledby="v3-modal-title">
    <div class="v3-modal__backdrop" @click="closeModal" aria-hidden="true"></div>
    <div class="v3-modal__panel" ref="modalPanelRef" tabindex="-1">
      <button class="v3-modal__close" type="button" @click="closeModal" aria-label="Close waitlist form">
        <span aria-hidden="true">&times;</span>
      </button>
      <p class="modal-eyebrow">Join the v3 insiders</p>
      <h2 id="v3-modal-title">Get the soft-launch invite</h2>
      <p class="modal-subtitle">Giveaways, early access drops and launch-day reminders.</p>
      <form class="row g-3" @submit.prevent="submit">
        <div class="col-md-6">
          <label class="form-label">Name (optional)</label>
          <input v-model="name" name="name" type="text" class="form-control" placeholder="Jordan" />
        </div>
        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input v-model="email" name="email" type="email" required class="form-control" placeholder="you@example.com" />
        </div>
        <div class="col-12">
          <button class="btn btn-dark w-100" :disabled="pending">
            <span v-if="pending" class="spinner-border spinner-border-sm me-2" />
            Notify me
          </button>
        </div>
      </form>
      <p v-if="success" class="text-success mt-3">Thanks — you’re on the list!</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick, watch } from 'vue'

const email = ref('')
const name = ref('')
const pending = ref(false)
const success = ref(false)
const sessionToken = ref(null)
const modalOpen = ref(false)
const modalPanelRef = ref(null)

let sessionStartMs = loadNumber('v3_session_started_at') ?? Date.now()
storeNumber('v3_session_started_at', sessionStartMs)
let heartbeatId = null
let trackLock = false
let geoCache = loadJson('v3_geo')

function openModal(){
  success.value = false
  modalOpen.value = true
}

function closeModal(){
  modalOpen.value = false
}

const handleGlobalModal = () => openModal()

function handleEscape(event){
  if (event.key === 'Escape' && modalOpen.value) {
    event.preventDefault()
    closeModal()
  }
}

watch(modalOpen, (isOpen) => {
  if (typeof document === 'undefined') return
  try { document.body.style.overflow = isOpen ? 'hidden' : '' } catch {}
  if (isOpen) {
    nextTick(() => {
      try {
        const input = modalPanelRef.value?.querySelector('input[name="email"]')
        input?.focus()
      } catch {}
    })
  }
})

onMounted(() => {
  initSession()
  heartbeatId = window.setInterval(() => sendTrack({ throttle: true }), 30000)
  document.addEventListener('visibilitychange', handleVisibility, { passive: true })
  window.addEventListener('pagehide', handlePageHide)
  window.addEventListener('wow:v3-open-modal', handleGlobalModal)
  document.addEventListener('keydown', handleEscape)
})

onBeforeUnmount(() => {
  if (heartbeatId) window.clearInterval(heartbeatId)
  document.removeEventListener('visibilitychange', handleVisibility)
  window.removeEventListener('pagehide', handlePageHide)
  window.removeEventListener('wow:v3-open-modal', handleGlobalModal)
  document.removeEventListener('keydown', handleEscape)
  try { document.body.style.overflow = '' } catch {}
})

function handleVisibility(){
  if (document.visibilityState === 'hidden') {
    sendTrack({ keepalive: true, force: true })
  }
}

function handlePageHide(){
  sendTrack({ keepalive: true, force: true })
}

async function initSession(){
  await sendTrack({ force: true })
  maybeRequestGeo()
}

function loadNumber(key){
  try {
    const raw = sessionStorage.getItem(key)
    return raw ? Number(raw) : null
  } catch { return null }
}

function storeNumber(key, value){
  try { sessionStorage.setItem(key, String(value)) } catch {}
}

function loadJson(key){
  try {
    const raw = sessionStorage.getItem(key)
    return raw ? JSON.parse(raw) : null
  } catch { return null }
}

function storeJson(key, value){
  try { sessionStorage.setItem(key, JSON.stringify(value)) } catch {}
}

function collectClientMeta(){
  const nav = typeof navigator !== 'undefined' ? navigator : {}
  const scr = typeof window !== 'undefined' ? window.screen || {} : {}
  const tz = (() => { try { return Intl.DateTimeFormat().resolvedOptions().timeZone } catch { return null } })()
  const languages = Array.isArray(nav.languages) ? nav.languages.join(',') : (nav.language || null)
  const locale = nav.language || null
  return {
    timezone: tz || null,
    locale,
    languages,
    platform: nav.platform || null,
    user_agent: nav.userAgent || null,
    device_memory: typeof nav.deviceMemory === 'number' ? String(nav.deviceMemory) : null,
    hardware_concurrency: typeof nav.hardwareConcurrency === 'number' ? String(nav.hardwareConcurrency) : null,
    screen_width: scr.width || null,
    screen_height: scr.height || null,
  }
}

function buildPayload(){
  const base = {
    session_token: sessionToken.value || null,
    landing_path: location.pathname + location.search,
    referrer: document.referrer || null,
    session_started_at: new Date(sessionStartMs).toISOString(),
    session_duration_seconds: Math.max(0, Math.round((Date.now() - sessionStartMs) / 1000)),
  }
  if (geoCache) {
    base.geo_lat = geoCache.lat
    base.geo_lng = geoCache.lng
    base.geo_accuracy = geoCache.accuracy
  }
  return { ...base, ...collectClientMeta() }
}

async function sendTrack(options = {}){
  if (trackLock && !options.force) return
  trackLock = true
  try{
    const token = document.querySelector('meta[name="csrf-token"]')?.content || ''
    const stored = (() => { try { return sessionStorage.getItem('v3_session_token') || null } catch { return null } })()
    if (stored && !sessionToken.value) {
      sessionToken.value = stored
    }
    const payload = buildPayload()
    if (sessionToken.value) {
      payload.session_token = sessionToken.value
    }
    const res = await fetch('/api/v3-subscribers/track', {
      method:'POST',
      headers:{ 'Content-Type':'application/json', ...(token?{ 'X-CSRF-TOKEN': token }: {}) },
      body: JSON.stringify(payload),
      keepalive: options.keepalive === true,
    })
    if (res.ok) {
      const data = await res.json().catch(() => null)
      if (data?.session_token) {
        sessionToken.value = data.session_token
        try { sessionStorage.setItem('v3_session_token', data.session_token) } catch {}
      }
    }
  } catch (err) {
    if (!options.throttle) console.error(err)
  } finally {
    trackLock = false
  }
}

function maybeRequestGeo(){
  if (geoCache || !('geolocation' in navigator)) return
  try {
    navigator.geolocation.getCurrentPosition(pos => {
      geoCache = {
        lat: Number(pos.coords.latitude.toFixed(7)),
        lng: Number(pos.coords.longitude.toFixed(7)),
        accuracy: pos.coords.accuracy ? Number(pos.coords.accuracy.toFixed(2)) : null,
        ts: Date.now(),
      }
      storeJson('v3_geo', geoCache)
      sendTrack({ force: true })
    }, () => {}, { enableHighAccuracy: false, timeout: 5000, maximumAge: 600000 })
  } catch {}
}

async function submit(){
  if (!email.value) return
  pending.value = true
  try{
    const token = document.querySelector('meta[name="csrf-token"]')?.content || ''
    const payload = {
      ...buildPayload(),
      email: email.value,
      name: name.value || null,
    }
    const res = await fetch('/api/v3-subscribers', {
      method:'POST',
      headers:{ 'Content-Type':'application/json', ...(token?{ 'X-CSRF-TOKEN': token }: {}) },
      body: JSON.stringify(payload)
    })
    if (!res.ok) throw new Error('Failed')
    const data = await res.json().catch(() => null)
    if (data?.session_token) {
      sessionToken.value = data.session_token
      try { sessionStorage.setItem('v3_session_token', data.session_token) } catch {}
    }
    success.value = true
    email.value = ''
    name.value = ''
  }catch(err){
    console.error(err)
    alert('Sorry, please try again shortly.')
  }finally{
    pending.value = false
    sendTrack({ force: true })
  }
}
</script>

<style scoped>
.v3-holding{
  min-height: auto;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 3rem 1.5rem 4rem;
}
.v3-holding__inner{
  width: min(1100px, 100%);
  background: rgba(255, 255, 255, 0.9);
  border-radius: 32px;
  padding: 3rem;
  box-shadow: 0 40px 120px rgba(0,0,0,0.15);
  display: grid;
  gap: 2.5rem;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
}
.v3-holding__copy{
  font-family: 'Instrument Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}
.eyebrow{
  text-transform: uppercase;
  letter-spacing: 0.24em;
  font-weight: 600;
  font-size: 0.85rem;
  color: #9b6547;
  margin-bottom: 0.75rem;
}
.lede{
  font-size: 1.1rem;
  color: #5b3f29;
}
.cta-row{
  margin-top: 1.75rem;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.6rem;
}
.cta-btn{
  border: none;
  border-radius: 999px;
  padding: 0.85rem 1.6rem;
  background: #0b1323;
  color: #fff;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  cursor: pointer;
  box-shadow: 0 20px 50px rgba(11,19,35,0.3);
}
.cta-btn svg{
  width: 24px;
  height: 24px;
}
.cta-note{
  margin: 0;
  color: #5b3f29;
  font-size: 0.95rem;
}
.benefits{
  margin-top: 2rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding: 0;
  list-style: none;
  color: #53371f;
  font-weight: 600;
}
.benefits li::before{
  content: '•';
  margin-right: 0.5rem;
  color: #e05c5c;
}
.v3-holding__art{
  display: flex;
  align-items: center;
  justify-content: center;
}
svg{
  width: min(420px, 90vw);
  height: auto;
}
.text-content{
  font: 700 10px/1.2 'Montserrat', 'Instrument Sans', sans-serif;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  fill: #111;
}
.v3-modal{
  position: fixed;
  inset: 0;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}
.v3-modal__backdrop{
  position: absolute;
  inset: 0;
  background: rgba(15,23,42,0.65);
}
.v3-modal__panel{
  position: relative;
  background: #fff;
  border-radius: 32px;
  padding: clamp(1.5rem, 3vw, 2.5rem);
  width: min(600px, 100%);
  box-shadow: 0 50px 120px rgba(15,23,42,0.3);
  z-index: 10;
  outline: none;
}
.v3-modal__close{
  position: absolute;
  top: 1.2rem;
  right: 1.2rem;
  border: none;
  background: transparent;
  font-size: 1.7rem;
  line-height: 1;
  color: #475569;
  cursor: pointer;
}
.modal-eyebrow{
  text-transform: uppercase;
  letter-spacing: 0.18em;
  font-size: 0.75rem;
  color: #a16230;
  font-weight: 700;
  margin-bottom: 0.5rem;
}
.v3-modal__panel h2{
  margin-bottom: 0.3rem;
  color: #0f172a;
  font-size: clamp(1.6rem, 4vw, 2.2rem);
}
.modal-subtitle{
  color: #475569;
  margin-bottom: 1.2rem;
}
@media (max-width: 768px){
  .v3-holding__inner{ padding: 2rem; }
}
@media (max-width: 1024px){
  .v3-holding__art{ display: none; }
}
</style>
