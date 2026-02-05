<script setup>
import { ref, computed, onMounted, watch } from 'vue'

const props = defineProps({
  items: { type: Array, default: () => [] },
})

const DEFAULT_IMG = 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=1200&q=80'

// Per‑painpoint random image selection (new each load)
const chosen = ref(new Map())

function chooseFor(pp){
  const arr = Array.isArray(pp?.images) ? pp.images.filter(Boolean) : []
  const list = arr.length ? arr : (pp?.image_url ? [pp.image_url] : [DEFAULT_IMG])
  return list[Math.floor(Math.random() * list.length)]
}

function imgFor(pp){
  return chosen.value.get(pp?.key) || chooseFor(pp)
}

const tiles = computed(() => Array.isArray(props.items) ? props.items.slice(0, 5) : [])

function refreshChoices(){
  const map = new Map()
  for (const pp of tiles.value) {
    map.set(pp.key, chooseFor(pp))
  }
  chosen.value = map
}

onMounted(refreshChoices)
watch(tiles, refreshChoices)
</script>

<template>
  <section class="section">
    <div class="container-page">
      <div class="mb-3">
        <div class="kicker">Explore by need</div>
        <h2 class="m-0">How do you want to feel?</h2>
        <div class="text-ink-600">Browse by what you want to solve — we’ll guide you faster.</div>
      </div>
      <div class="wow-pp-grid">
        <a v-for="pp in tiles" :key="pp.key" :href="`/need/${pp.key}`" class="tile">
          <img class="tile-img" :src="imgFor(pp)" :alt="pp.name" @error="e => { if (e?.target) e.target.src = DEFAULT_IMG }" />
          <div class="tile-overlay"></div>
          <div class="tile-content">
            <div class="eyebrow">{{ pp.name }}</div>
            <div class="title">{{ pp.feeling || pp.name }}</div>
            <div class="pill">Explore</div>
          </div>
        </a>
      </div>
    </div>
  </section>
  
</template>

<style scoped>
.wow-pp-grid{ display:grid; grid-template-columns: repeat(5, minmax(0,1fr)); gap: 1rem }
.tile{ position:relative; display:block; border-radius:16px; overflow:hidden; height:220px; box-shadow:0 10px 24px rgba(0,0,0,.08); transition: transform .25s ease, box-shadow .25s ease; text-decoration:none }
.tile:hover{ transform: translateY(-3px); box-shadow: 0 16px 36px rgba(0,0,0,.12) }
.tile-img{ position:absolute; inset:0; width:100%; height:100%; object-fit:cover; transform: scale(1.03) }
.tile-overlay{ position:absolute; inset:0; pointer-events:none; background: linear-gradient(180deg, rgba(0,0,0,.28) 0%, rgba(0,0,0,.55) 100%) }
.tile-content{ position:absolute; inset:0; display:flex; flex-direction:column; justify-content:space-between; padding:18px; color:#fff }
.eyebrow{ font-size:.95rem; opacity:.95; font-weight:800 }
.title{ font-size:1.4rem; font-weight:800; letter-spacing:.2px; line-height:1.1 }
.pill{ align-self:flex-start; background:#fff; color:#111; border:0; border-radius:999px; padding:8px 16px; font-weight:700; box-shadow: 0 6px 14px rgba(0,0,0,.12) }
</style>
