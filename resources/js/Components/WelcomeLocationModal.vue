<script setup>
import { ref, onMounted } from 'vue'
import Modal from '@/Components/Modal.vue'
import LocationAutocomplete from '@/Components/LocationAutocomplete.vue'

const props = defineProps({
  accessToken: { type: String, required: true },
})

const show = ref(false)
const locationText = ref('')
const coords = ref(null)

function storageSet(key, val, days = 30) {
  const item = { v: val, t: Date.now(), exp: days > 0 ? Date.now() + days*24*60*60*1000 : null }
  try { localStorage.setItem(key, JSON.stringify(item)) } catch {}
}
function storageGet(key) {
  try {
    const raw = localStorage.getItem(key)
    if (!raw) return null
    const obj = JSON.parse(raw)
    if (obj.exp && Date.now() > obj.exp) { localStorage.removeItem(key); return null }
    return obj.v
  } catch { return null }
}

function saveAndClose() {
  if (locationText.value) {
    storageSet('wow_location', { name: locationText.value, coords: coords.value }, 30)
  }
  storageSet('wow_modal_dismissed', true, 30)
  try { document.cookie = `wow_modal_dismissed=1; max-age=${30*24*60*60}; path=/` } catch {}
  show.value = false
}

function onSelect(place) {
  locationText.value = place.name
  coords.value = { lat: place.lat, lng: place.lng }
}

function useMyLocation() {
  if (!navigator.geolocation) return
  navigator.geolocation.getCurrentPosition(async (pos) => {
    const { latitude, longitude } = pos.coords
    // Reverse geocode
    try {
      const url = new URL(`https://api.mapbox.com/geocoding/v5/mapbox.places/${longitude},${latitude}.json`)
      url.searchParams.set('access_token', props.accessToken)
      url.searchParams.set('limit', '1')
      const res = await fetch(url)
      const data = await res.json()
      const name = data?.features?.[0]?.place_name || 'Current location'
      locationText.value = name
      coords.value = { lat: latitude, lng: longitude }
    } catch {}
  })
}

onMounted(() => {
  const dismissed = storageGet('wow_modal_dismissed') || (typeof document !== 'undefined' && document.cookie.includes('wow_modal_dismissed='))
  const loc = storageGet('wow_location')
  if (!dismissed && !loc) {
    show.value = true
  }
})
</script>

<template>
  <Modal :show="show" max-width="lg" @close="saveAndClose">
    <div class="p-6">
      <div class="flex items-start gap-3">
        <div class="h-10 w-10 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-6-4.35-6-9a6 6 0 1 1 12 0c0 4.65-6 9-6 9z"/><circle cx="12" cy="12" r="2"/></svg>
        </div>
        <div>
          <h3 class="text-xl font-semibold text-ink-900">Personalise your experience</h3>
          <p class="text-sm text-ink-600 mt-1">Tell us where you are and we’ll show sessions nearby. We’ll remember this for next time.</p>
        </div>
      </div>
      <div class="mt-4">
        <div class="rounded-2xl border border-ink-200 p-3 flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-ink-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-6-4.35-6-9a6 6 0 1 1 12 0c0 4.65-6 9-6 9z"/><circle cx="12" cy="12" r="2"/></svg>
          <LocationAutocomplete v-model="locationText" :access-token="props.accessToken" placeholder="Enter city or postcode" @select="onSelect" />
        </div>
        <div class="flex items-center gap-3 mt-3">
          <button type="button" class="btn-wow btn-wow--ghost is-square btn-sm" @click="useMyLocation">Use my location</button>
          <button type="button" class="btn-wow is-square btn-sm" @click="saveAndClose">Save</button>
        </div>
        <div class="text-xs text-ink-400 mt-2">We store your choice for 30 days. No spam, no pop‑ups every visit.</div>
      </div>
    </div>
  </Modal>
  
</template>
