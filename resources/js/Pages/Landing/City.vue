<script setup>
import { Head } from '@inertiajs/vue3'
import SiteLayout from '@/Layouts/SiteLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import ProductCard from '@/Components/ProductCard.vue'

const props = defineProps({
  city: { type: String, required: true },
  products: { type: Array, default: () => [] },
})
</script>

<template>
  <Head :title="`Wellness in ${city}`" />
  <SiteLayout>
    <!-- Hero -->
    <section class="section">
      <div class="container-page">
        <Breadcrumbs :items="[{ label: city }]" />
        <div class="mb-6">
          <div class="kicker">City hub</div>
          <h1>Wellness in {{ city }}</h1>
          <p class="text-ink-600 text-lg mt-2 max-w-2xl">Explore therapies, events and classes available in {{ city }}. Use the search to refine by dates, group size and location.</p>
        </div>
        <!-- Search removed here per spec -->
      </div>
    </section>

    <section class="section">
      <div class="container-page">
        <div v-if="products?.length" class="grid sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 gap-4">
          <ProductCard v-for="p in products" :key="p.id" :product="p" :fluid="true" />
        </div>
        <div v-else class="card p-5 text-center text-ink-600">
          Nothing listed yet — we’re onboarding practitioners here soon.
        </div>
      </div>
    </section>
  </SiteLayout>
</template>

<style scoped>
</style>
