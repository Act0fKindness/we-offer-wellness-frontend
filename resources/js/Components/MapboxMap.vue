<script setup>
import { onMounted, onBeforeUnmount, ref, watch } from 'vue'

const props = defineProps({
  accessToken: { type: String, required: true },
  markers: { type: Array, default: () => [] },
  center: { type: Array, default: () => [-0.1, 51.5] }, // [lng, lat]
  zoom: { type: Number, default: 10 },
})

const mapEl = ref(null)
let map
let markerObjs = []

async function ensureMapboxLoaded() {
  if (window.mapboxgl) return
  const cssHref = 'https://api.mapbox.com/mapbox-gl-js/v3.6.0/mapbox-gl.css'
  const jsSrc = 'https://api.mapbox.com/mapbox-gl-js/v3.6.0/mapbox-gl.js'
  if (!document.querySelector(`link[href="${cssHref}"]`)) {
    const link = document.createElement('link')
    link.rel = 'stylesheet'
    link.href = cssHref
    document.head.appendChild(link)
  }
  await new Promise((resolve, reject) => {
    const s = document.createElement('script')
    s.src = jsSrc
    s.onload = resolve
    s.onerror = reject
    document.head.appendChild(s)
  })
}

function renderMarkers() {
  // Clear existing markers
  markerObjs.forEach(m => m.remove())
  markerObjs = []
  if (!map) return
  const mapboxgl = window.mapboxgl
  const bounds = new mapboxgl.LngLatBounds()
  props.markers.forEach(m => {
    if (typeof m.lng !== 'number' || typeof m.lat !== 'number') return
    const el = document.createElement('div')
    el.className = 'rounded-full bg-brand-600 ring-2 ring-white w-3 h-3'
    const marker = new mapboxgl.Marker({ element: el }).setLngLat([m.lng, m.lat]).addTo(map)
    markerObjs.push(marker)
    bounds.extend([m.lng, m.lat])
  })
  if (props.markers.length > 0 && !bounds.isEmpty()) {
    map.fitBounds(bounds, { padding: 40, animate: true })
  }
}

onMounted(async () => {
  await ensureMapboxLoaded()
  const mapboxgl = window.mapboxgl
  mapboxgl.accessToken = props.accessToken
  map = new mapboxgl.Map({
    container: mapEl.value,
    style: 'mapbox://styles/mapbox/streets-v12',
    center: props.center,
    zoom: props.zoom,
  })
  map.addControl(new mapboxgl.NavigationControl({ visualizePitch: true }), 'top-right')
  map.on('load', renderMarkers)
})

onBeforeUnmount(() => {
  markerObjs.forEach(m => m.remove())
  map && map.remove()
})

watch(() => props.markers, renderMarkers, { deep: true })
</script>

<template>
  <div ref="mapEl" class="w-full h-full"></div>
</template>
