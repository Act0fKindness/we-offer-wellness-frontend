<script setup>
import { onMounted, ref, watch, computed } from 'vue'
import ProductCard from '@/Components/ProductCard.vue'
import { fetchProducts } from '@/services/products'

const props = defineProps({
  painpoint: { type: Object, default: null },
})

const items = ref([])
const loading = ref(false)
const heading = computed(() => props.painpoint ? `Recommended for ${props.painpoint.feeling || props.painpoint.name}` : 'Recommended for You')
const sub = computed(() => 'Curated picks for where you are right now.')

async function load() {
  loading.value = true
  try {
    const what = props.painpoint?.name || ''
    const list = await fetchProducts({ what, sort:'popular', limit: 12 })
    items.value = list
  } catch { items.value = [] }
  loading.value = false
}

onMounted(load)
watch(() => props.painpoint?.key, load)
</script>

<template>
  <section class="section">
    <div class="container-page">
      <div class="mb-3">
        <div class="kicker">Discover</div>
        <h2 class="m-0">{{ heading }}</h2>
        <div class="text-ink-600">{{ sub }}</div>
      </div>
      <div v-if="loading" class="card p-6 text-ink-600">Loading…</div>
      <div v-else class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <ProductCard v-for="p in items" :key="p.id" :product="p" :fluid="true" />
      </div>
    </div>
  </section>
</template>

