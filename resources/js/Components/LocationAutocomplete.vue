<script setup>
import { ref, watch, onMounted } from 'vue'

const props = defineProps({
  idPrefix: { type: String, default: '' },
  modelValue: { type: String, default: '' },
  accessToken: { type: String, required: true },
  placeholder: { type: String, default: 'City or postcode' },
  country: { type: String, default: 'gb' },
  inputClass: { type: String, default: '' },
  panelClass: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue', 'select'])

const query = ref(props.modelValue)
const open = ref(false)
const focused = ref(false)
const loading = ref(false)
const results = ref([])
const catalog = ref(null)
let timer
const featuredOrder = {
  online: 0,
  london: 1,
  manchester: 2,
  'brighton-and-hove': 3,
  kent: 4,
}

watch(() => props.modelValue, v => { if (v !== query.value) query.value = v })

function setValue(val) {
  emit('update:modelValue', val)
}

function selectPlace(place) {
  const name = place.place_name || place.text
  const { path: _path, ...raw } = place || {}
  setValue(name)
  open.value = false
  emit('select', {
    name,
    lat: place.center?.[1] ?? place.geometry?.coordinates?.[1] ?? null,
    lng: place.center?.[0] ?? place.geometry?.coordinates?.[0] ?? null,
    context: place.context || [],
    raw,
  })
}

function normalize(text) {
  return String(text || '')
    .toLowerCase()
    .replace(/[\u2018\u2019\u201c\u201d]/g, "'")
    .replace(/&/g, ' and ')
    .replace(/[^a-z0-9]+/g, ' ')
    .replace(/\b(and|of|the)\b/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
}

function catalogItems() {
  const flat = Array.isArray(catalog.value?.suggestions) ? catalog.value.suggestions : []
  return flat
    .filter(Boolean)
    .map((item) => {
      const { path: _path, ...sanitized } = item || {}
      return sanitized
    })
    .sort((left, right) => {
    const leftSlug = normalize(left.slug || left.title)
    const rightSlug = normalize(right.slug || right.title)
    const leftScore = featuredOrder[leftSlug.replace(/\s+/g, '-')] ?? 99
    const rightScore = featuredOrder[rightSlug.replace(/\s+/g, '-')] ?? 99
    if (leftScore !== rightScore) return leftScore - rightScore
    return normalize(left.title).localeCompare(normalize(right.title))
    })
}

function localMatches(q) {
  const needle = normalize(q)
  if (!needle) return []
  return catalogItems().filter((item) => {
    const haystack = normalize([
      item.title,
      item.country,
      item.county,
      item.district,
      item.region,
      item.city,
      item.town,
      item.slug,
    ].filter(Boolean).join(' '))

    return haystack.includes(needle)
  })
}

function featuredMatches() {
  return catalogItems().filter((item) => featuredOrder[normalize(item.slug || item.title).replace(/\s+/g, '-')] !== undefined)
}

async function loadCatalog() {
  try {
    const res = await fetch('/cache/locations.json', { cache: 'force-cache' })
    if (!res.ok) return
    const data = await res.json()
    catalog.value = data
  } catch (e) {
    console.warn('[locations] catalog load error', e)
  }
}

async function search() {
  if (timer) clearTimeout(timer)
  timer = setTimeout(async () => {
    const q = (query.value || '').trim()
    if (!q && focused.value) {
      results.value = featuredMatches().slice(0, 5)
      open.value = results.value.length > 0
      loading.value = false
      return
    }
    if (q.length < 2) {
      results.value = []
      open.value = false
      return
    }

    const local = localMatches(q).slice(0, 6)
    if (local.length) {
      results.value = local
      open.value = focused.value && results.value.length > 0
      loading.value = false
      return
    }

    loading.value = true
    try {
      const url = new URL(`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(q)}.json`)
      url.searchParams.set('access_token', props.accessToken)
      url.searchParams.set('autocomplete', 'true')
      url.searchParams.set('limit', '6')
      url.searchParams.set('types', 'place,postcode,locality,neighborhood,address,region,district,country')
      if (props.country) url.searchParams.set('country', props.country)
      const res = await fetch(url.toString())
      if (!res.ok) throw new Error('geocode failed')
      const data = await res.json()
      results.value = (data.features || []).map((feature) => ({
        ...feature,
      }))
      open.value = focused.value && results.value.length > 0
    } catch (e) {
      console.warn('[mapbox] geocode error', e)
      results.value = []
      open.value = false
    } finally {
      loading.value = false
    }
  }, 250)
}

onMounted(() => {
  // Do not auto-open on load; only search when user focuses/types
  loadCatalog()
})
</script>

<template>
  <div class="relative">
    <input
      :id="props.idPrefix ? `${props.idPrefix}-input` : undefined"
      :placeholder="placeholder"
      :class="['w-full bg-transparent outline-none text-sm text-ink-900 placeholder:text-ink-500', inputClass]"
      type="search"
      :value="query"
      @input="(e)=>{ query = e.target.value; setValue(e.target.value); search() }"
      @focus="()=>{ focused.value = true; search() }"
      @blur="()=>{ focused.value = false; setTimeout(()=>open=false, 150) }"
    />
    <div v-if="open" :class="panelClass || 'absolute left-0 top-full mt-1 w-full bg-white border border-ink-200 p-2 z-50'">
      <div v-if="loading" class="px-3 py-2 text-sm text-ink-500">Searching…</div>
      <button
        v-for="(f, i) in results"
        :key="i"
        type="button"
        class="w-full text-left px-3 py-2 rounded-lg hover:bg-ink-100 flex items-start gap-2"
        @mousedown.prevent="selectPlace(f)"
      >
        <span class="mt-0.5 inline-flex h-4 w-4 items-center justify-center text-ink-500">
          <svg v-if="f.online" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="5" width="18" height="12" rx="2"></rect>
            <path d="M8 21h8"></path>
            <path d="M12 17v4"></path>
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-6-4.35-6-9a6 6 0 1 1 12 0c0 4.65-6 9-6 9z"/><circle cx="12" cy="12" r="2"/></svg>
        </span>
        <div>
          <div class="text-sm text-ink-900">{{ f.text }}</div>
          <div class="text-xs text-ink-500 truncate">{{ f.place_name }}</div>
        </div>
      </button>
      <div v-if="!loading && results.length===0" class="px-3 py-2 text-sm text-ink-500">No matches</div>
    </div>
  </div>
</template>
