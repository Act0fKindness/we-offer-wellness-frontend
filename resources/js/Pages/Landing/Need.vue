<script setup>
import { Head, Link } from '@inertiajs/vue3'
import SiteLayout from '@/Layouts/SiteLayout.vue'
import ProductCard from '@/Components/ProductCard.vue'

const props = defineProps({
  need: { type: Object, required: true },
  products: { type: Array, default: () => [] },
})
</script>

<template>
  <Head :title="props.need?.name || 'How you feel'" />
  <SiteLayout>
    <section class="section">
      <div class="container-page">
        <div class="mb-6">
          <div class="kicker">How you feel</div>
          <h1 class="m-0">{{ props.need?.name || 'Personalised picks' }}</h1>
          <p class="text-ink-600 mt-2 max-w-2xl">Curated sessions matched to how you feel. Mix online and in‑person, with transparent pricing and verified reviews.</p>
        </div>
        <div v-if="(products||[]).length" class="grid sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 gap-4">
          <ProductCard v-for="p in products" :key="p.id" :product="p" :fluid="true" />
        </div>
        <div v-else class="card p-8 text-center text-ink-700">
          We’re curating practitioners for this need. In the meantime, explore what’s on this week or try the quiz to personalise your plan.
          <div class="mt-4 d-flex gap-2 justify-content-center">
            <a href="/therapies" class="btn-wow btn-wow--outline btn-sm btn-arrow">
              <span class="btn-label">Browse therapies</span>
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
            <a href="#quiz" class="btn-wow btn-wow--outline btn-sm btn-arrow">
              <span class="btn-label">Take the 60‑sec quiz</span>
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
      </div>
    </section>
  </SiteLayout>
</template>

<style scoped>
h1{ font-size: 2rem; font-weight: 700 }
@media (min-width: 640px){ h1{ font-size: 2.5rem } }
</style>
