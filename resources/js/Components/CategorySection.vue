<script setup>
import ProductCard from '@/Components/ProductCard.vue'

const props = defineProps({
  category: { type: Object, required: true },
  href: { type: String, default: '#' },
})
</script>

<template>
  <section class="section">
    <div class="container-page">
      <div class="mb-6 flex items-end justify-between">
        <div>
          <div class="kicker">Category</div>
          <h2>{{ category.name }}</h2>
        </div>
        <a :href="href" class="btn-wow btn-wow--outline btn-sm btn-arrow">
          <span class="btn-label">View all</span>
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
      <div class="flex gap-6 overflow-x-auto no-scrollbar pb-2 -mx-4 px-4 sm:mx-0 sm:px-0">
        <ProductCard v-for="p in category.products" :key="p.id" :product="p" />
      </div>
    </div>
  </section>
</template>
