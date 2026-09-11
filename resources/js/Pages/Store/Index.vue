<script setup>
import { Head } from '@inertiajs/vue3'
import SiteLayout from '@/Layouts/SiteLayout.vue'
import StoreProductCard from '@/Components/StoreProductCard.vue'
const props = defineProps({ products: { type: Array, default: () => [] } })
</script>
<template>
  <Head title="Store products" />
  <SiteLayout>
    <section class="section"><div class="container-page">
      <p class="kicker">We Offer Wellness Store</p><h1>Products for your wellbeing</h1>
      <p class="store-intro">Physical products selected to support your rituals between sessions.</p>
      <div class="store-grid"><StoreProductCard v-for="product in props.products" :key="product.id" :product="product" :fluid="true" /><p v-if="!props.products.length">There are no store products available right now.</p></div>
    </div></section>
  </SiteLayout>
</template>
<style scoped>.store-intro{max-width:620px;color:var(--ink-600);margin:0 0 30px}.store-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:24px}</style>
