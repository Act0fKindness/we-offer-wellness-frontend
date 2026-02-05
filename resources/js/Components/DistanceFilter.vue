<script setup>
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
  mapsKey: { type: String, default: '' },
  products: { type: Array, default: () => [] },
  initialKm: { type: Number, default: 0 }, // 0 = off
})
const emit = defineEmits(['filtered'])

const km = ref(props.initialKm)
const loc = ref(readUserLoc())
const busy = ref(false)

function readUserLoc(){
  try{
    const get = (n) => (document.cookie.match('(^|;)\\s*'+n+'\\s*=\\s*([^;]+)')||[]).pop()
    const lat = parseFloat(decodeURIComponent(get('wow_lat')||''))
    const lng = parseFloat(decodeURIComponent(get('wow_lng')||''))
    if (Number.isFinite(lat) && Number.isFinite(lng)) return { lat, lng }
  }catch{}
  return null
}
function writeUserLoc(v){
  try{ const exp=new Date(Date.now()+30*864e5).toUTCString(); document.cookie = `wow_lat=${encodeURIComponent(v.lat)}; expires=${exp}; path=/`; document.cookie = `wow_lng=${encodeURIComponent(v.lng)}; expires=${exp}; path=/` }catch{}
}
function useMyLocation(){
  try{
    navigator.geolocation.getCurrentPosition(p => { loc.value = { lat: p.coords.latitude, lng: p.coords.longitude }; writeUserLoc(loc.value); runFilter() }, () => {}, { enableHighAccuracy:true, timeout:6000, maximumAge:60000 })
  }catch{}
}

// simple cache
const cache = new Map()
async function geocode(address){
  const key = (address||'').trim().toLowerCase(); if (!key) return null
  if (cache.has(key)) return cache.get(key)
  try{
    const url = new URL('https://maps.googleapis.com/maps/api/geocode/json')
    url.searchParams.set('address', address)
    url.searchParams.set('key', props.mapsKey)
    const res = await fetch(url.toString())
    const j = await res.json()
    const r = j?.results?.[0]
    const loc = r?.geometry?.location
    if (loc && Number.isFinite(loc.lat) && Number.isFinite(loc.lng)){
      const out = { lat:Number(loc.lat), lng:Number(loc.lng) }
      cache.set(key, out)
      return out
    }
  }catch{}
  return null
}

function normLocations(p){
  const arr = Array.isArray(p?.locations) ? p.locations : (p?.location ? [p.location] : [])
  return arr.map(s=>String(s).trim()).filter(s => s && !/^online$/i.test(s))
}

function hav(a,b){
  const R=6371; const dLat=(b.lat-a.lat)*Math.PI/180; const dLng=(b.lng-a.lng)*Math.PI/180;
  const sa=Math.sin(dLat/2)**2 + Math.cos(a.lat*Math.PI/180)*Math.cos(b.lat*Math.PI/180)*Math.sin(dLng/2)**2; return 2*R*Math.asin(Math.sqrt(sa))
}

async function runFilter(){
  if (!km.value || !loc.value){ emit('filtered', []); return }
  busy.value = true
  try{
    const within = []
    const jobs = props.products.map(async (p) => {
      const explicitLat = Number(p?.lat ?? p?.latitude); const explicitLng = Number(p?.lng ?? p?.longitude)
      let pt = (Number.isFinite(explicitLat) && Number.isFinite(explicitLng)) ? { lat: explicitLat, lng: explicitLng } : null
      if (!pt){
        const locs = normLocations(p)
        for (const s of locs){ pt = await geocode(s); if (pt) break }
      }
      if (pt){ const d = hav(loc.value, pt); if (d <= km.value) within.push(p.id) }
    })
    await Promise.all(jobs)
    emit('filtered', within)
  } finally { busy.value = false }
}

onMounted(() => { if (km.value && loc.value) runFilter() })

function setKm(v){ km.value = v; runFilter() }
</script>

<template>
  <div class="distance-filter">
    <div class="row1">
      <span class="label">Distance</span>
      <div class="seg-group">
        <button class="seg" :class="{ active: km===0 }" @click="setKm(0)">Any</button>
        <button class="seg" :class="{ active: km===5 }" @click="setKm(5)">5 km</button>
        <button class="seg" :class="{ active: km===10 }" @click="setKm(10)">10 km</button>
        <button class="seg" :class="{ active: km===25 }" @click="setKm(25)">25 km</button>
        <button class="seg" :class="{ active: km===50 }" @click="setKm(50)">50 km</button>
      </div>
      <button class="use-loc" @click="useMyLocation" :disabled="busy">Use my location</button>
    </div>
    <div v-if="busy" class="hint">Calculating distances…</div>
  </div>
</template>

<style scoped>
.distance-filter{ margin-top: 12px; padding: 10px 12px; border:1px solid var(--ink-200); border-radius:14px; background:#fff; box-shadow:0 6px 12px rgba(2,8,23,.06) }
.row1{ display:flex; align-items:center; gap:10px; flex-wrap:wrap }
.label{ color: var(--ink-700); font-size:.9rem }
.seg-group{ display:inline-flex; background:#f8fafc; border:1px solid var(--ink-200); border-radius:999px; padding:2px }
.seg{ appearance:none; border:0; background:transparent; padding:6px 12px; border-radius:999px; color: var(--ink-700); font-weight:600; font-size:.9rem; transition: all .15s ease }
.seg:hover{ background:#eef2f7 }
.seg.active{ background: linear-gradient(180deg, #549483, #3b7768); color:#fff }
.use-loc{ margin-left:auto; border:1px solid var(--ink-300); background:#fff; color:#0b1323; border-radius:999px; padding:6px 12px; font-weight:600 }
.hint{ margin-top:8px; color: var(--ink-600); font-size:.9rem }
</style>

