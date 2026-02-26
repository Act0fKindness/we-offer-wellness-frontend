<script setup>
import { ref, onMounted } from 'vue'

const open = ref(false)
const mode = ref('mixed') // 'online' or 'mixed'
const status = ref('idle') // 'idle' | 'locating' | 'saving'
const error = ref('')

function cookieGet(name){
  const m = document.cookie.match('(^|;)\\s*'+name+'\\s*=\\s*([^;]+)');
  return m ? decodeURIComponent(m.pop()) : ''
}
function cookieSet(name, value, days){
  const maxAge = days ? days*24*60*60 : 60*60*24*365*5
  document.cookie = `${name}=${encodeURIComponent(value)}; Max-Age=${maxAge}; Path=/; SameSite=Lax`
}
function csrfToken(){
  try { return document.querySelector('meta[name="csrf-token"]').content || window.__csrfToken || '' }
  catch { return window.__csrfToken || '' }
}

async function save(data){
  status.value = 'saving'
  try {
    const res = await fetch('/api/geo', { method:'POST', headers: { 'Content-Type':'application/json', 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken() }, body: JSON.stringify(data) })
    if (!res.ok) throw new Error('geo '+res.status)
  } catch(e){ /* ignore */ }
  cookieSet('wow_geo_done','1', 365*5)
  open.value = false
  window.location.reload()
}

async function useMyLocation(){
  error.value = ''
  status.value = 'locating'
  if (!('geolocation' in navigator)) { status.value='idle'; error.value = 'Geolocation not available'; return }
  navigator.geolocation.getCurrentPosition(async (pos) => {
    const lat = pos.coords.latitude, lng = pos.coords.longitude
    let city='', region='', country=''
    try {
      const key = window.WOW_MAPS_KEY || ''
      if (key) {
        const url = new URL(`https://api.mapbox.com/geocoding/v5/mapbox.places/${lng},${lat}.json`)
        url.searchParams.set('access_token', key)
        url.searchParams.set('limit', '1')
        const res = await fetch(url)
        const json = await res.json()
        const feat = json?.features?.[0]
        if (feat) {
          const comps = feat?.context || []
          city = (comps.find(c=>c.id?.startsWith('place'))?.text) || (comps.find(c=>c.id?.startsWith('locality'))?.text) || ''
          region = (comps.find(c=>c.id?.startsWith('region'))?.text) || ''
          country = (comps.find(c=>c.id?.startsWith('country'))?.text) || ''
        }
      }
    } catch {}
    await save({ lat, lng, city, region, country, mode: mode.value })
  }, () => { status.value='idle'; error.value = 'We couldn\'t get your location.' }, { enableHighAccuracy:false, timeout:6000, maximumAge:60000 })
}

function chooseOnline(){ mode.value='online' }
function chooseMixed(){ mode.value='mixed' }
function saveOnlineOnly(){ save({ mode: 'online' }) }

onMounted(() => {
  const done = cookieGet('wow_geo_done') === '1'
  const reask = cookieGet('wow_geo_reask') === '1'
  if (!done || reask) { open.value = true }
})
</script>

<template>
  <div v-if="open" class="lgate-overlay" @click.self="open=false">
    <div class="lgate">
      <div class="lgate-header">
        <img src="https://cdn.shopify.com/s/files/1/0820/3947/2469/files/logo.png?v=1738109013" alt="We Offer Wellness" />
      </div>
      <div class="lgate-body">
        <h3>Tailor your experience</h3>
        <p class="lead">Looking for <strong>Online only</strong> or <strong>Online & In‑person</strong> near you?</p>

        <div class="choice-row">
          <button :class="['chip', mode==='online' ? 'chip-brand' : '']" @click="chooseOnline">Online only</button>
          <button :class="['chip', mode==='mixed' ? 'chip-brand' : '']" @click="chooseMixed">Online & In‑person</button>
        </div>

        <div v-if="mode==='mixed'" class="action-row">
          <button class="btn btn-primary" :disabled="status!=='idle'" @click="useMyLocation">
            <span v-if="status==='locating'">Locating…</span>
            <span v-else>Use my location</span>
          </button>
          <div class="muted">We’ll remember this and show nearby options first.</div>
        </div>
        <div v-else class="action-row">
          <button class="btn btn-primary" :disabled="status!=='idle'" @click="saveOnlineOnly">Save preference</button>
          <div class="muted">You can change this anytime from the menu.</div>
        </div>

        <div v-if="error" class="error">{{ error }}</div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.lgate-overlay{ position:fixed; inset:0; background:rgba(17,24,39,.55); -webkit-backdrop-filter: blur(6px); backdrop-filter: blur(6px); z-index: 1000; display:flex; align-items:center; justify-content:center; padding: 2rem }
.lgate{ width:min(720px, 96vw); background:#fff; border-radius:18px; border:1px solid var(--ink-200); box-shadow: 0 20px 50px rgba(0,0,0,.15); overflow:hidden }
.lgate-header{ padding: 10px 14px; border-bottom:1px solid var(--ink-200); display:flex; align-items:center }
.lgate-header img{ height:28px; width:auto }
.lgate-body{ padding: 20px }
.lgate-body h3{ margin:0; font-size: 1.5rem; font-weight: 700 }
.lgate-body .lead{ margin:.35rem 0 0; color: var(--ink-700) }
.choice-row{ display:flex; gap:.5rem; margin-top: 1rem }
.action-row{ display:flex; align-items:center; gap:.75rem; margin-top: 1rem }
.muted{ color: var(--ink-500); font-size: .9rem }
.error{ color: var(--danger); margin-top:.75rem }
</style>
