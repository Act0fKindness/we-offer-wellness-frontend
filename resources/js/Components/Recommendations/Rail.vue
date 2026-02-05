<script setup>
import { ref, onMounted, computed } from 'vue'
import ProductCard from '@/Components/ProductCard.vue'
import { fetchProducts } from '@/services/products'
import { profileSignals } from '@/services/recs'

const props = defineProps({
  product: { type: Object, required: true },
  type: { type: String, default: '' },
  sections: { type: Array, default: () => ['you_may_also_like'] },
})

const loading = ref(false)
const data = ref({})

const productId = computed(() => Number(props.product?.id || 0))
const categoryId = computed(() => Number(props.product?.category?.id || 0))
const vendorId = computed(() => Number(props.product?.vendor_id || props.product?.vendor?.id || 0))
const userLoc = ref(null)
const trackRefs = ref({})
function setTrackRef(key){ return (el) => { if (el) { const m = { ...trackRefs.value }; m[key] = el; trackRefs.value = m } } }
function scrollByKey(key, dir){
  const el = trackRefs.value?.[key]
  if (!el) return
  const amount = Math.min(900, el.clientWidth * 0.9)
  try { el.scrollBy({ left: dir * amount, behavior: 'smooth' }) } catch { el.scrollLeft += dir * amount }
}

async function fetchJSON(url){ try{ const r=await fetch(url, { cache: 'no-store' }); return await r.json() }catch{ return null } }

function deriveType(p){
  const raw = String(p?.type || p?.product_type || '').trim().toLowerCase()
  if (raw) {
    if (raw === 'experience' || raw === 'experiences') return 'therapy'
    if (raw.includes('workshop')) return 'workshop'
    if (raw.includes('class')) return 'class'
    if (raw.includes('event')) return 'event'
    if (raw.includes('retreat')) return 'retreat'
    if (raw.includes('therapy')) return 'therapy'
  }
  const url = String(p?.url || '').toLowerCase()
  if (url.includes('/events/')) return 'event'
  if (url.includes('/workshops/')) return 'workshop'
  if (url.includes('/classes/')) return 'class'
  if (url.includes('/retreats/')) return 'retreat'
  if (url.includes('/therapies/')) return 'therapy'
  const cat = String(p?.category || p?.category_name || p?.category?.name || '').toLowerCase()
  if (cat.includes('event')) return 'event'
  if (cat.includes('workshop')) return 'workshop'
  if (cat.includes('class')) return 'class'
  if (cat.includes('retreat')) return 'retreat'
  if (cat.includes('therapy') || cat.includes('experience')) return 'therapy'
  return 'therapy'
}

function cleanLocation(raw) {
  const s = String(raw || '').trim()
  if (!s) return null
  const lower = s.toLowerCase()
  if (lower === 'online') return 'Online'
  let x = s.replace(/,?\s*(united kingdom|uk)$/i, '')
  x = x.replace(/\b([A-Z]{1,2}\d{1,2}[A-Z]?)\s*(\d[A-Z]{2})\b/i, '').trim()
  const parts = x.split(',').map(p => p.trim()).filter(Boolean)
  if (parts.length === 0) return null
  const city = parts[parts.length - 1]
  let county = null
  if (parts.length >= 2) {
    const cand = parts[parts.length - 2]
    if (!/\b(road|rd|street|st|farm|lane|ln|ave|avenue|close|cl|drive|dr)\b/i.test(cand)) { county = cand }
  }
  return county && county.toLowerCase() !== city.toLowerCase() ? `${city}, ${county}` : city
}

async function load(){
  loading.value = true
  const out = {}
  // 1) You may also like: same category and type, exclude current
  if (props.sections.includes('you_may_also_like')){
    try{
      const sig = profileSignals()
      const type = deriveType(props.product)
      const catPref = (sig.topCategories && sig.topCategories.includes(categoryId.value)) ? categoryId.value : (sig.topCategories?.[0] || categoryId.value)
      let arr = await fetchProducts({ limit: 48, sort: 'popular', category_id: catPref || undefined, type })
      if (!Array.isArray(arr) || !arr.length){ arr = await fetchProducts({ limit: 48, sort: 'popular', category_id: catPref || undefined }) }
      // Client-side refine by format + rough price band
      const prefersOnline = !!sig.prefersOnline
      const priceMin = Number(sig.priceMin||0), priceMax = Number(sig.priceMax||0)
      out.you_may_also_like = (arr||[])
        .filter(p => Number(p.id)!==productId.value)
        .filter(p => {
          if (prefersOnline){ const m=String(p?.mode||'').toLowerCase(); const locs=p?.locations||[]; return m==='online' || (Array.isArray(locs) && locs.some(v=>/online/i.test(String(v)))) }
          return true
        })
        .filter(p => {
          if (priceMin && priceMax){ const n=Number(p.price_min || p.price || 0); return !n || (n>=priceMin*0.6 && n<=priceMax*1.4) }
          return true
        })
        .slice(0, 12)
    }catch{ out.you_may_also_like = [] }
  }
  // 2) People also booked (fallback: popular in same category)
  if (props.sections.includes('people_also_booked')){
    try{
      const sig = profileSignals()
      let arr = await fetchJSON(`/api/recs/also-booked?product_id=${productId.value}`)
      if (!Array.isArray(arr) || !arr.length){
        const catPref = (sig.topCategories?.[0] || categoryId.value)
        arr = await fetchProducts({ limit: 48, sort:'popular', category_id: catPref || undefined })
      }
      const prefersOnline = !!sig.prefersOnline
      out.people_also_booked = (arr||[])
        .filter(p => Number(p.id)!==productId.value)
        .filter(p => { if (prefersOnline){ const m=String(p?.mode||'').toLowerCase(); const locs=p?.locations||[]; return m==='online' || (Array.isArray(locs) && locs.some(v=>/online/i.test(String(v)))) } return true })
        .slice(0,12)
    }catch{ out.people_also_booked = [] }
  }
  // 3) More from this practitioner
  if (props.sections.includes('more_from_provider') && vendorId.value){
    try{
      let arr = await fetchProducts({ vendor_id: vendorId.value, limit: 24, sort: 'popular' })
      out.more_from_provider = (arr||[]).filter(p => Number(p.id)!==productId.value).slice(0,12)
    }catch{ out.more_from_provider = [] }
  }
  // 4) Near you (geo) — best-effort browser geolocation fallback
  if (props.sections.includes('near_you')){
    try{
      if (!userLoc.value){
        await new Promise((resolve) => {
          try{
            navigator.geolocation.getCurrentPosition(pos => { userLoc.value = { lat: pos.coords.latitude, lng: pos.coords.longitude }; resolve() }, () => resolve(), { timeout: 1200 })
          } catch { resolve() }
        })
      }
      let arr = []
      if (userLoc.value){
        const qs = new URLSearchParams({ lat:String(userLoc.value.lat), lng:String(userLoc.value.lng), limit:'12' })
        const j = await fetchJSON('/api/products/near?'+qs.toString())
        arr = Array.isArray(j) ? j : []
      }
      // Fallback: same category filtered by similar location string
      if (!arr.length){
        const like = await fetchProducts({ limit: 48, sort: 'popular', category_id: categoryId.value || undefined })
        const locStr = cleanLocation(props.product?.location || (props.product?.locations||[])[0] || '')
        if (locStr){
          const lc = String(locStr).toLowerCase()
          arr = (like||[]).filter(p => String(p.location || (p.locations||[])[0] || '').toLowerCase().includes(lc))
        } else { arr = like || [] }
      }
      out.near_you = (arr||[]).filter(p => Number(p.id)!==productId.value).slice(0,12)
    }catch{ out.near_you = [] }
  }
  // 5) Trending in category (fallback to popular)
  if (props.sections.includes('trending_category')){
    try{
      let arr = await fetchJSON('/api/analytics/trending?'+new URLSearchParams({ limit:'24', category_id:String(categoryId.value||'') }))
      if (!Array.isArray(arr) || !arr.length) arr = await fetchProducts({ limit: 36, sort: 'popular', category_id: categoryId.value || undefined })
      out.trending_category = (arr||[]).filter(p => Number(p.id)!==productId.value).slice(0,12)
    }catch{ out.trending_category = [] }
  }
  // 6) Seasonal picks — tag by month
  if (props.sections.includes('seasonal_picks')){
    try{
      const m = (new Date()).getMonth()
      const tag = (m>=8 && m<=10) ? 'autumn' : (m>=2 && m<=4 ? 'spring' : (m>=5 && m<=7 ? 'summer' : 'winter'))
      const j = await fetchJSON('/api/products?'+new URLSearchParams({ limit:'12', tag }))
      out.seasonal_picks = Array.isArray(j) ? j.slice(0,12) : []
    }catch{ out.seasonal_picks = [] }
  }
  // 7) As Seen in Mindful Times — related content
  if (props.sections.includes('as_seen')){
    try{
      const j = await fetchJSON(`/api/articles/related?product_id=${productId.value}&limit=8`)
      out.as_seen = Array.isArray(j) ? j : []
    }catch{ out.as_seen = [] }
  }
  // 8) Recommended for Your Mood — placeholder if no personalization
  if (props.sections.includes('mood_based')){
    try{
      const j = await fetchJSON('/api/recs/mood?limit=12')
      out.mood_based = Array.isArray(j) ? j : []
    }catch{ out.mood_based = [] }
  }
  // Final fallbacks to ensure we show relevant rails
  try{
    const popularAll = await fetchProducts({ limit: 48, sort: 'popular' })
    const popularCat = categoryId.value ? await fetchProducts({ limit: 48, sort: 'popular', category_id: categoryId.value }) : []
    const notSelf = (arr)=> (arr||[]).filter(p => Number(p.id)!==productId.value)
    const take = (arr,n=12)=> notSelf(arr).slice(0,n)
    const ensure = (key, arr)=>{ if (!Array.isArray(out[key]) || out[key].length===0) out[key] = take(arr) }
    if (props.sections.includes('you_may_also_like')){
      ensure('you_may_also_like', popularCat.length ? popularCat : popularAll)
    }
    if (props.sections.includes('people_also_booked')){
      const pool = (popularCat.length ? popularCat : popularAll)
      const used = new Set((out.you_may_also_like||[]).map(p=>p.id))
      ensure('people_also_booked', pool.filter(p=>!used.has(p.id)))
    }
    if (props.sections.includes('more_from_provider')){
      if (!Array.isArray(out.more_from_provider) || !out.more_from_provider.length){
        ensure('more_from_provider', popularCat.length ? popularCat : popularAll)
      }
    }
    if (props.sections.includes('near_you')){
      if (!Array.isArray(out.near_you) || !out.near_you.length){
        ensure('near_you', popularCat.length ? popularCat : popularAll)
      }
    }
    if (props.sections.includes('trending_category')){
      if (!Array.isArray(out.trending_category) || !out.trending_category.length){
        ensure('trending_category', popularCat.length ? popularCat : popularAll)
      }
    }
  }catch{}

  data.value = out
  loading.value = false
}

onMounted(() => { load() })

function titleFor(key){
  switch(key){
    case 'you_may_also_like': return 'You may also like'
    case 'people_also_booked': return 'People also booked'
    case 'more_from_provider': return 'More from this practitioner'
    case 'near_you': return 'Wellness near you'
    case 'trending_category': return `Trending in ${props.product?.category?.name || 'this category'}`
    case 'seasonal_picks': return 'Seasonal picks'
    case 'as_seen': return 'As seen in Mindful Times'
    case 'mood_based': return 'Recommended for your mood'
    default: return ''
  }
}

const order = computed(() => props.sections.filter(k => Array.isArray(data.value?.[k]) ? data.value[k].length : 0))

// Presentation configs per section (row-only, themed styling)
function sectionConfig(key){
  switch (key){
    case 'you_may_also_like':
      return { theme: 'theme-default', kicker: 'Recommended', icon: 'bi-stars', title: titleFor(key), subtitle: '', boxed: true }
    case 'people_also_booked':
      return { theme: 'theme-booked', kicker: 'Often booked together', icon: 'bi-link-45deg', title: titleFor(key), subtitle: 'Pairs well with this', boxed: false }
    case 'more_from_provider':
      return { theme: 'theme-provider', kicker: 'From this practitioner', icon: 'bi-person-badge', title: titleFor(key), subtitle: '', boxed: false }
    case 'near_you':
      return { theme: 'theme-near', kicker: 'Nearby', icon: 'bi-geo-alt', title: titleFor(key), subtitle: 'Based on your location', boxed: false }
    case 'trending_category':
      return { theme: 'theme-trend', kicker: 'Trending', icon: 'bi-graph-up-arrow', title: titleFor(key), subtitle: '', boxed: true, grid: true }
    default:
      return { theme: 'theme-default', kicker: 'Discover', icon: 'bi-compass', title: titleFor(key), subtitle: '', boxed: false }
  }
}

function wrapperClasses(key){
  const cfg = sectionConfig(key)
  return cfg.boxed ? ['rail-card', cfg.theme] : ['rail-plain']
}
</script>

<template>
  <div class="wow-recs">
    <div v-for="key in order" :key="key" class="wow-rec-rail">
      <div :class="wrapperClasses(key)">
        <div class="rail-head d-flex align-items-center justify-content-between gap-2">
          <div>
            <div class="kicker"><i :class="['bi', sectionConfig(key).icon]"></i> {{ sectionConfig(key).kicker }}</div>
            <h2 class="rail-title m-0">{{ sectionConfig(key).title }}</h2>
            <p v-if="sectionConfig(key).subtitle" class="text-ink-600 mt-1">{{ sectionConfig(key).subtitle }}</p>
          </div>
          <div v-if="!sectionConfig(key).grid" class="flex items-center gap-2">
            <button class="hidden sm:inline-flex carousel-arrow" @click="scrollByKey(key,-1)" aria-label="Previous">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <button class="hidden sm:inline-flex carousel-arrow" @click="scrollByKey(key,1)" aria-label="Next">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
            </button>
          </div>
        </div>
        <template v-if="sectionConfig(key).grid">
          <div class="grid8">
            <div v-for="it in (data?.[key]||[]).slice(0,8)" :key="it.id" class="grid8-cell">
              <ProductCard :product="it" :fluid="true" />
            </div>
          </div>
        </template>
        <template v-else>
          <div class="rail">
            <div class="track" :ref="setTrackRef(key)">
              <div v-for="it in (data?.[key]||[])" :key="it.id" class="cell">
                <ProductCard :product="it" :fluid="true" />
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<style scoped>
.wow-rec-rail + .wow-rec-rail{ margin-top: 1.25rem }
.wow-recs :deep(.wow-card){ flex:0 0 auto }
.rail{ overflow:hidden }
.track{ display:flex; gap:12px; overflow-x:auto; padding-bottom:6px }
.track::-webkit-scrollbar{ display:none }
.cell{ width:260px; flex: 0 0 260px }
.rail-title { font-size: 1.25rem; font-weight: 500; line-height:1.2; letter-spacing:0 }
.rail-card{ background:#fff; border:1px solid var(--ink-200); border-radius:14px; padding:16px }
.rail-plain{ padding: 0 }
.rail-head .kicker i{ margin-right:.4rem }
.rail-card.theme-booked{ background: linear-gradient(180deg, #ffffff, #f5f9ff) }
.rail-card.theme-provider{ background: linear-gradient(180deg, #ffffff, #f7f7ff) }
.rail-card.theme-near{ background: linear-gradient(180deg, #ffffff, #f5fbf6) }
.rail-card.theme-trend{ background: linear-gradient(180deg, #ffffff, #fff7ef) }
/* Distinct track/cell sizing per theme */
.rail-card.theme-booked .track{ gap:10px }
.rail-card.theme-provider .track{ gap:16px }
/* Grid layout for selected sections */
.grid8{ display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap:12px }
@media (min-width: 992px){ .grid8{ grid-template-columns: repeat(4, minmax(0,1fr)) } }
.grid8-cell{ min-width:0 }
.rail-head{ margin-bottom:8px }
</style>
