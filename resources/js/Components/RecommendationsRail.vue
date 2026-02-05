<script setup>
import { ref, onMounted } from 'vue'
import ProductCard from '@/Components/ProductCard.vue'
import { fetchProducts } from '@/services/products'

const props = defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  theme: { type: String, default: 'default' }, // default|muted|brand
  usePriceBands: { type: Boolean, default: false }, // show £50 / £100 / Premium
  query: { type: Object, default: () => ({}) },
  limit: { type: Number, default: 12 },
})

const items = ref([])
const scroller = ref(null)
const band = ref('50') // '50' | '100' | 'premium'

async function load(){
  const base = { ...props.query }
  if (props.usePriceBands){
    if (band.value !== 'premium') base.price_max = Number(band.value)
    else delete base.price_max
  }
  const q = { ...base, limit: props.limit }
  items.value = await fetchProducts(q)
}

onMounted(load)
watch(band, () => { if (props.usePriceBands) load() })
</script>

<template>
  <section v-if="items.length" class="section">
    <div class="container-page">
      <div :class="['rail-card', 'rail-theme--'+props.theme]">
        <div class="rail-head d-flex align-items-center justify-content-between gap-3">
          <div>
            <div class="kicker">Featured</div>
            <h2 class="rail-title m-0">{{ props.title }}</h2>
            <p v-if="props.subtitle" class="text-ink-600 mt-1">{{ props.subtitle }}</p>
          </div>
          <div v-if="props.usePriceBands" class="bands">
            <div class="seg-group" role="tablist" aria-label="Under price">
              <button class="seg" :class="{ active: band==='50' }" @click="band='50'" role="tab" :aria-selected="band==='50'">Under £50</button>
              <button class="seg" :class="{ active: band==='100' }" @click="band='100'" role="tab" :aria-selected="band==='100'">£100</button>
              <button class="seg" :class="{ active: band==='premium' }" @click="band='premium'" role="tab" :aria-selected="band==='premium'">Premium</button>
            </div>
          </div>
          <div class="flex items-center gap-2 ms-auto">
            <button class="hidden sm:inline-flex carousel-arrow" @click="() => { const el=scroller?.value; if(!el) return; const a=Math.min(900, el.clientWidth*0.9); try{ el.scrollBy({left:-a, behavior:'smooth'}) }catch{ el.scrollLeft -= a } }" aria-label="Previous">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <button class="hidden sm:inline-flex carousel-arrow" @click="() => { const el=scroller?.value; if(!el) return; const a=Math.min(900, el.clientWidth*0.9); try{ el.scrollBy({left:a, behavior:'smooth'}) }catch{ el.scrollLeft += a } }" aria-label="Next">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
            </button>
          </div>
        </div>
        <div class="rail mt-2">
          <div class="track" ref="scroller">
            <div v-for="p in items" :key="p.id" class="cell">
              <ProductCard :product="p" :fluid="true" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.rail{ overflow:hidden }
.track{ display:flex; gap:12px; overflow-x:auto; padding-bottom:6px }
.track::-webkit-scrollbar{ display:none }
.cell{ width:260px; flex: 0 0 260px }
.rail-title { font-size: 1.25rem; font-weight: 500; line-height:1.2; letter-spacing:0 }
.rail-card{ background:#fff; border:1px solid var(--ink-200); border-radius:14px; padding:16px }
.rail-card.rail-theme--muted{ background: linear-gradient(180deg, #fff, #f8fafc) }
.rail-card.rail-theme--brand{ background: linear-gradient(180deg, #ffffff, #f1f7f5) }

/* Segmented tabs */
.seg-group{ display:inline-flex; background:#f8fafc; border:1px solid var(--ink-200); border-radius:999px; padding:2px }
.seg{ appearance:none; border:0; background:transparent; padding:6px 12px; border-radius:999px; color: var(--ink-700); font-weight:600; font-size:.9rem; transition: all .15s ease; }
.seg:hover{ background:#eef2f7 }
.seg.active{ background: linear-gradient(180deg, #549483, #3b7768); color:#fff; box-shadow: 0 1px 0 rgba(255,255,255,.4) inset }
</style>
