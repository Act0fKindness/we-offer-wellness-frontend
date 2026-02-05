<script setup>
import { onMounted, onBeforeUnmount, ref, watch, nextTick } from 'vue'

const props = defineProps({
  apiKey: { type: String, required: true },
  products: { type: Array, default: () => [] },
  userLocation: { type: Object, default: null }, // { lat, lng }
})

const mapEl = ref(null)
let map
let googleMaps
let markers = []
let renderToken = 0
let mapReady = false
let userHasInteracted = false
let lastSignature = ''
let isAutoFitting = false
let loaderPromise = null
let activeInfoWindow = null
let userMarker = null
let directionsService = null

const cache = {
  get(key) {
    try {
      const raw = sessionStorage.getItem('geo_cache')
      if (!raw) return null
      const parsed = JSON.parse(raw)
      return parsed[key] || null
    } catch (error) {
      console.warn('[maps] cache read failed', error)
      return null
    }
  },
  set(key, value) {
    try {
      const raw = sessionStorage.getItem('geo_cache')
      const parsed = raw ? JSON.parse(raw) : {}
      parsed[key] = value
      sessionStorage.setItem('geo_cache', JSON.stringify(parsed))
    } catch (error) {
      console.warn('[maps] cache write failed', error)
    }
  },
}

function loadGoogleMaps(apiKey) {
  if (typeof window === 'undefined') {
    return Promise.reject(new Error('Google Maps requires a browser environment'))
  }
  if (window.google?.maps) {
    return Promise.resolve(window.google.maps)
  }
  if (loaderPromise) {
    return loaderPromise
  }
  loaderPromise = new Promise((resolve, reject) => {
    const existing = document.querySelector('script[data-wow-google-maps]')
    if (existing) {
      existing.addEventListener('load', () => resolve(window.google.maps))
      existing.addEventListener('error', reject)
      return
    }
    const script = document.createElement('script')
    script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places`
    script.async = true
    script.defer = true
    script.dataset.wowGoogleMaps = '1'
    script.onload = () => resolve(window.google.maps)
    script.onerror = reject
    document.head.appendChild(script)
  })
  return loaderPromise
}

function toCoord(product) {
  const lat = Number(product?.lat ?? product?.latitude ?? product?.coords?.lat)
  const lng = Number(product?.lng ?? product?.longitude ?? product?.coords?.lng)
  if (Number.isFinite(lat) && Number.isFinite(lng)) {
    return { lat, lng, label: product.location || product.title || '' }
  }
  return null
}

const badLocation = (value = '') => {
  const lower = value.trim().toLowerCase()
  return !lower || ['online', 'virtual', 'remote', 'anywhere', 'multiple locations', 'various locations', 'tbc', 'n/a'].includes(lower)
}

function normaliseLocations(product) {
  const list = Array.isArray(product?.locations) && product.locations.length
    ? product.locations
    : (product?.location ? [product.location] : [])

  const cleaned = list
    .map((value) => (typeof value === 'string' ? value.trim() : ''))
    .filter((value) => value && !badLocation(value))

  return [...new Set(cleaned)]
}

function signatureForProducts(list = []) {
  return list
    .map((product) => `${product?.id || 'x'}:${normaliseLocations(product).join('|')}:${product?.lat || ''}:${product?.lng || ''}`)
    .join(';')
}

function escapeHtml(value = '') {
  return String(value).replace(/[&<>"']/g, (match) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
  })[match])
}

async function geocode(query) {
  if (!query) return null
  const trimmed = query.trim()
  if (!trimmed) return null
  const hit = cache.get(trimmed)
  if (hit) return hit
  try {
    const url = new URL('https://maps.googleapis.com/maps/api/geocode/json')
    url.searchParams.set('address', trimmed)
    url.searchParams.set('key', props.apiKey)
    const response = await fetch(url.toString())
    if (!response.ok) throw new Error(`geocode ${response.status}`)
    const data = await response.json()
    const result = data.results?.[0]
    const location = result?.geometry?.location
    if (location && Number.isFinite(location.lat) && Number.isFinite(location.lng)) {
      const point = {
        lat: Number(location.lat),
        lng: Number(location.lng),
        label: result.formatted_address || trimmed,
      }
      cache.set(trimmed, point)
      return point
    }
  } catch (error) {
    console.warn('[maps] geocode failed', error)
  }
  return null
}

async function computePoints(list) {
  const limit = Math.min(list.length, 120)
  const points = []
  const jobs = []
  const seen = new Set()

  for (let i = 0; i < limit; i += 1) {
    const product = list[i]
    if (!product) continue

    const explicit = toCoord(product)
    const locations = normaliseLocations(product)

    if (explicit) {
      const key = `${explicit.lat}:${explicit.lng}`
      if (!seen.has(key)) {
        seen.add(key)
        points.push({ lat: explicit.lat, lng: explicit.lng, label: explicit.label, product, location: locations[0] || explicit.label })
        if (locations.length) {
          seen.add(`${product.id}:${locations[0].toLowerCase()}`)
        }
      }
    }

    if (!locations.length && explicit) {
      continue
    }

    for (const location of locations) {
      const jobKey = `${product.id}:${location.toLowerCase()}`
      if (seen.has(jobKey)) continue
      seen.add(jobKey)
      jobs.push((async () => {
        const geo = await geocode(location)
        if (geo && Number.isFinite(geo.lat) && Number.isFinite(geo.lng)) {
          return { lat: geo.lat, lng: geo.lng, label: geo.label || location, product, location }
        }
        return null
      })())
    }
  }

  const resolved = await Promise.all(jobs)
  resolved.forEach((point) => { if (point) points.push(point) })
  return points
}

function clearMarkers() {
  if (!markers.length) return
  markers.forEach(({ marker, listener, infoWindow }) => {
    if (listener) {
      if (typeof listener.remove === 'function') {
        listener.remove()
      } else if (googleMaps?.event) {
        googleMaps.event.removeListener(listener)
      }
    }
    if (infoWindow) infoWindow.close()
    if (marker) marker.setMap(null)
  })
  markers = []
  activeInfoWindow = null
}

function fitToPoints(points) {
  if (!map || !googleMaps || !points.length) return
  isAutoFitting = true
  if (points.length === 1) {
    map.setZoom(12)
    map.panTo({ lat: points[0].lat, lng: points[0].lng })
    return
  }
  const bounds = new googleMaps.LatLngBounds()
  points.forEach((point) => bounds.extend({ lat: point.lat, lng: point.lng }))
  map.fitBounds(bounds, { padding: { top: 64, bottom: 64, left: 64, right: 64 } })
}

function markerIcon() {
  // Airbnb-style pin (teardrop)
  const path = 'M12 2C7.6 2 4 5.6 4 10c0 5.6 8 12 8 12s8-6.4 8-12c0-4.4-3.6-8-8-8z';
  return {
    path,
    fillColor: '#f0633d',
    fillOpacity: 1,
    strokeColor: '#ffffff',
    strokeWeight: 2,
    scale: 1,
    anchor: new googleMaps.Point(12, 24),
  }
}

function userIcon() {
  return {
    path: googleMaps.SymbolPath.CIRCLE,
    scale: 6,
    fillColor: '#2563eb',
    fillOpacity: 1,
    strokeColor: '#ffffff',
    strokeWeight: 2,
  }
}

function makeInfoContent(point) {
  const product = point.product || {}
  const title = escapeHtml(product.title || point.label || 'Experience')
  const location = point.location || product.location
  const locationLine = location ? `<div class="mt-1 text-xs text-ink-600">${escapeHtml(location)}</div>` : ''
  return `<div class="wow-map-popup"><strong class="text-sm">${title}</strong>${locationLine}</div>`
}

async function renderMarkers({ forceFit = false } = {}) {
  if (!mapReady || !googleMaps) return
  const token = ++renderToken
  const points = await computePoints(props.products || [])
  if (token !== renderToken) return
  clearMarkers()
  if (!points.length) return

  const icon = markerIcon()

  points.forEach((point) => {
    const marker = new googleMaps.Marker({
      map,
      position: { lat: point.lat, lng: point.lng },
      title: point.product?.title || point.label || 'Experience',
      icon: icon || undefined,
    })
    const infoWindow = new googleMaps.InfoWindow({
      content: makeInfoContent(point),
      maxWidth: 240,
    })
    const listener = marker.addListener('click', async () => {
      if (activeInfoWindow) activeInfoWindow.close()
      // If userLocation provided, compute travel time (driving)
      if (props.userLocation && directionsService) {
        try {
          const res = await directionsService.route({
            origin: { lat: Number(props.userLocation.lat), lng: Number(props.userLocation.lng) },
            destination: { lat: point.lat, lng: point.lng },
            travelMode: 'DRIVING',
          })
          const leg = res?.routes?.[0]?.legs?.[0]
          if (leg?.duration?.text) {
            const base = makeInfoContent(point)
            const eta = `<div class="mt-1 text-xs"><strong>${leg.duration.text}</strong> by car from your location</div>`
            infoWindow.setContent(base.replace('</div>', `${eta}</div>`))
          }
        } catch (e) { /* ignore */ }
      }
      infoWindow.open({ anchor: marker, map })
      activeInfoWindow = infoWindow
    })
    markers.push({ marker, infoWindow, listener })
  })

  if (forceFit || !userHasInteracted) {
    fitToPoints(points)
  }
}

onMounted(async () => {
  if (!props.apiKey) return
  await nextTick()
  try {
    googleMaps = await loadGoogleMaps(props.apiKey)
  } catch (error) {
    console.error('[maps] failed to load Google Maps SDK', error)
    return
  }

  map = new googleMaps.Map(mapEl.value, {
    center: { lat: 51.509865, lng: -0.118092 },
    zoom: 6,
    mapTypeControl: false,
    fullscreenControl: true,
    streetViewControl: false,
    clickableIcons: true,
    styles: [
      {
        featureType: 'poi',
        stylers: [{ visibility: 'off' }],
      },
      {
        featureType: 'transit',
        stylers: [{ visibility: 'off' }],
      },
    ],
  })

  directionsService = new googleMaps.DirectionsService()

  map.addListener('dragstart', () => { userHasInteracted = true })
  map.addListener('zoom_changed', () => { if (!isAutoFitting) userHasInteracted = true })
  map.addListener('idle', () => {
    if (isAutoFitting) {
      isAutoFitting = false
      return
    }
    userHasInteracted = true
  })

  mapReady = true
  lastSignature = signatureForProducts(props.products)
  renderMarkers({ forceFit: true })

  // Draw user marker if present
  if (props.userLocation && Number.isFinite(Number(props.userLocation.lat)) && Number.isFinite(Number(props.userLocation.lng))) {
    userMarker = new googleMaps.Marker({
      map,
      position: { lat: Number(props.userLocation.lat), lng: Number(props.userLocation.lng) },
      title: 'Your location',
      icon: userIcon(),
      zIndex: 9999,
    })
  }
})

watch(() => props.products, (next) => {
  if (!mapReady) return
  const signature = signatureForProducts(next)
  if (signature === lastSignature) return
  lastSignature = signature
  userHasInteracted = false
  renderMarkers({ forceFit: true })
}, { deep: false })

onBeforeUnmount(() => {
  clearMarkers()
  if (map) {
    googleMaps?.event?.clearInstanceListeners?.(map)
  }
})
</script>

<template>
  <div ref="mapEl" class="maps-panel"></div>
</template>

<style>
.maps-panel { width: 100%; height: calc(100vh - 144px); border: 1px solid var(--ink-200); border-radius: 18px; overflow: hidden; background: #fff; box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06); }
</style>
