<script setup>
import { Head } from '@inertiajs/vue3'
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import SiteLayout from '@/Layouts/SiteLayout.vue'
import UltraSearchBar from '@/Components/UltraSearchBar.vue'
import ProductCard from '@/Components/ProductCard.vue'
import MapPanel from '@/Components/MapPanel.vue'
import { fetchProducts } from '@/services/products'

const props = defineProps({
  mapsKey: { type: String, required: true },
})

const products = ref([])
const loading = ref(false)
const loadError = ref(false)
const filters = ref(paramsFromUrl())
const view = ref('list-map') // 'list' | 'list-map'
const userLoc = ref(readUserLocation())

function paramsFromUrl() {
  const u = new URLSearchParams(window.location.search || '')
  const known = ['what','where','when','flexible','adults','group_type','sort','price_max','price_min','mode','type','tag']
  const out = {}
  known.forEach(k => { const v = u.get(k); if (v) out[k] = v })
  return out
}

function readUserLocation() {
  try {
    const get = (n) => (document.cookie.match('(^|;)\\s*'+n+'\\s*=\\s*([^;]+)')||[]).pop()
    const lat = parseFloat(decodeURIComponent(get('wow_lat')||''))
    const lng = parseFloat(decodeURIComponent(get('wow_lng')||''))
    if (Number.isFinite(lat) && Number.isFinite(lng)) return { lat, lng }
  } catch {}
  return null
}

async function load() {
  loading.value = true
  loadError.value = false
  filters.value = paramsFromUrl()
  try {
    products.value = await fetchProducts(filters.value, { throwOnError: true })
  } catch (error) {
    console.error('[search] load failed', error)
    loadError.value = true
    products.value = []
  } finally {
    loading.value = false
  }
}

const resultCount = computed(() => products.value.length)
const headline = computed(() => {
  const term = filters.value?.what
  return term ? `“${term}”` : 'all experiences'
})
const filterTags = computed(() => {
  const tags = []
  if (filters.value?.where) tags.push(filters.value.where)
  if (filters.value?.mode) tags.push(filters.value.mode === 'online' ? 'Online only' : 'In person')
  if (filters.value?.type) tags.push(filters.value.type)
  if (filters.value?.tag) tags.push(`#${filters.value.tag}`)
  if (filters.value?.price_max) tags.push(`Under £${filters.value.price_max}`)
  if (filters.value?.flexible) tags.push('Flexible dates')
  return tags
})

function handlePopstate() {
  load()
}

onMounted(() => {
  load()
  window.addEventListener('popstate', handlePopstate)
})

onBeforeUnmount(() => {
  window.removeEventListener('popstate', handlePopstate)
})
</script>

<template>
  <Head title="Search">
    <meta name="robots" content="noindex,follow" />
    <link rel="canonical" :href="(typeof window!=='undefined'? (window.location.origin + '/search') : '')" />
    <meta property="og:title" content="Search Therapies" />
    <meta property="og:description" content="Find therapies, classes and events that match how you feel." />
  </Head>
  <SiteLayout>
    <!-- Search bar under the nav bar -->
    <section class="pt-4 pb-2 bg-transparent">
      <div class="container-page">
        <UltraSearchBar id-prefix="search-top" />
      </div>
    </section>

    <section class="py-6 md:py-10">
      <div class="container-page space-y-6">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
          <div>
            <div class="kicker mb-1 text-ink-600 uppercase tracking-[0.2em]">Search results</div>
            <h1 class="text-ink-900" style="font-size:1.75rem;font-weight:700;">{{ resultCount }} therapies for {{ headline }}</h1>
          </div>
          <div class="d-flex flex-wrap align-items-center gap-2">
            <div v-if="filterTags.length" class="d-flex flex-wrap gap-2 me-3">
              <span v-for="tag in filterTags" :key="tag" class="chip">{{ tag }}</span>
            </div>
            <div class="btn-group" role="group" aria-label="View mode">
              <button type="button" class="btn-wow btn-wow--ghost is-square btn-sm" :class="{ 'btn-wow--secondary': view==='list' }" @click="view='list'">List</button>
              <button type="button" class="btn-wow btn-wow--ghost is-square btn-sm" :class="{ 'btn-wow--secondary': view==='list-map' }" @click="view='list-map'">List + Map</button>
            </div>
          </div>
        </div>

        <div v-if="userLoc" class="alert alert-info card p-3">
          Click a pin to calculate your travel time.
        </div>

        <div class="row g-3">
          <div :class="['col-12', view==='list-map' ? 'col-lg-6' : 'col-lg-12']">
            <div v-if="loading" class="card p-8 text-ink-600 text-center text-lg">Loading results…</div>
            <div v-else-if="loadError" class="card p-8 text-ink-600 text-center text-lg">
              We’re having trouble loading results right now. Please refresh or adjust your filters.
            </div>
            <div v-else-if="products.length === 0" class="card p-8 text-ink-600 text-center text-lg">
              No results matched your filters. Try widening your search.
            </div>
            <div v-else class="row g-3">
              <div v-for="(p, i) in products" :key="p.id ?? i" :class="['col-12','col-sm-6', view==='list-map' ? 'col-lg-6' : 'col-lg-3']">
                <ProductCard :product="p" :fluid="true" />
              </div>
            </div>
          </div>
          <div v-if="view==='list-map'" class="col-12 col-lg-6">
            <div style="position: sticky; top: 88px;">
              <MapPanel :api-key="props.mapsKey" :products="products" :user-location="userLoc" />
            </div>
            <!-- Optional: show a collapsed map toggle on small screens only -->
          </div>
        </div>
      </div>
    </section>
  </SiteLayout>
</template>
