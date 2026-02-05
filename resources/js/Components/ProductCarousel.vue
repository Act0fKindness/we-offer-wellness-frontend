<script setup>
import { ref, onMounted } from 'vue'
import ProductCard from '@/Components/ProductCard.vue'

const props = defineProps({
  title: String,
  subtitle: String,
  products: { type: Array, default: () => [] },
  ctaHref: { type: String, default: '#' },
  ctaLabel: { type: String, default: 'View all' },
})

const scroller = ref(null)
function scrollBy(dir) {
  const el = scroller.value
  if (!el) return
  const amount = Math.min(900, el.clientWidth * 0.9)
  el.scrollBy({ left: dir * amount, behavior: 'smooth' })
}
</script>

<template>
  <section class="section">
    <div class="container-page">
      <div class="mb-6 flex items-end justify-between">
        <div>
          <div class="kicker" v-if="subtitle">{{ subtitle }}</div>
          <h2>{{ title }}</h2>
        </div>
        <div class="flex items-center gap-2">
          <button class="hidden sm:inline-flex carousel-arrow" @click="scrollBy(-1)" aria-label="Previous">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
          </button>
          <button class="hidden sm:inline-flex carousel-arrow" @click="scrollBy(1)" aria-label="Next">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
          </button>
          <a :href="ctaHref" class="btn-wow btn-wow--outline btn-sm btn-arrow">
            <span class="btn-label">{{ ctaLabel }}</span>
            <span class="btn-icon-wrap" aria-hidden="true">
              <svg class="btn-icon-hover" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"/>
              </svg>
              <svg class="btn-icon-default" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12l-4 4m4-4-4-4"/>
              </svg>
            </span>
            <span class="btn-spinner" aria-hidden="true"><span class="spin"></span></span>
          </a>
        </div>
      </div>
      <div ref="scroller" class="flex gap-6 overflow-x-auto overflow-y-visible no-scrollbar snap-x snap-mandatory pt-2 pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 bg-transparent">
        <ProductCard v-for="p in products" :key="p.id" :product="p" class="snap-start" />
      </div>
    </div>
  </section>
</template>
