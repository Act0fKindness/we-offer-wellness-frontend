<script setup>
import { Head, Link } from '@inertiajs/vue3'
import SiteLayout from '@/Layouts/SiteLayout.vue'
import ProductCard from '@/Components/ProductCard.vue'

const props = defineProps({
  answers: { type: Object, default: () => ({}) },
  products: { type: Array, default: () => [] },
  fallback: { type: Array, default: () => [] },
})

function headline() {
  const a = props.answers || {}
  const bits = []
  if (a.what) bits.push(a.what)
  if (a.mode === 'online') bits.push('Online')
  if (a.mode === 'in-person' && a.where) bits.push('In ' + a.where)
  if (a.when) bits.push(a.when)
  return bits.length ? bits.join(' · ') : 'Your plan'
}
</script>

<template>
  <Head title="Your Plan" />
  <SiteLayout>
    <section class="section">
      <div class="container-page">
        <div class="mb-6">
          <div class="kicker">Personalised</div>
          <h1 class="m-0">{{ headline() }}</h1>
          <p class="text-ink-600 mt-2 max-w-2xl">We’ve pulled together the best matches for how you feel right now. Refine with the search bar or try another need.</p>
        </div>

        <div v-if="(products||[]).length" class="grid sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 gap-4">
          <ProductCard v-for="p in products" :key="p.id" :product="p" :fluid="true" />
        </div>
        <div v-else class="card p-8">
          <div class="mb-3">
            <h3 class="m-0">No exact matches</h3>
            <div class="text-ink-600">Here are strong alternatives you can book right now.</div>
          </div>
          <div v-if="(fallback||[]).length" class="grid sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 gap-4">
            <ProductCard v-for="p in fallback" :key="p.id" :product="p" :fluid="true" />
          </div>
          <div v-else class="text-ink-600">Check back soon or try a different filter above.</div>
          <div class="mt-4 d-flex gap-2">
            <a href="/need/sleep" class="btn-wow btn-wow--ghost is-square btn-sm">Sleep better</a>
            <a href="/need/stress" class="btn-wow btn-wow--ghost is-square btn-sm">Stress reset</a>
            <a href="/need/energy" class="btn-wow btn-wow--ghost is-square btn-sm">Energy boost</a>
            <a href="/need/pain" class="btn-wow btn-wow--ghost is-square btn-sm">Pain relief</a>
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
