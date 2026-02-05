<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import SiteLayout from '@/Layouts/SiteLayout.vue'
import WowButton from '@/Components/ui/WowButton.vue'
import ProductCard from '@/Components/ProductCard.vue'
import { fetchProducts } from '@/services/products'

const loading = ref(true)
const products = ref([])
const error = ref(false)

async function loadGiftCards() {
  loading.value = true
  try {
    const items = await fetchProducts({ type: 'gift', sort: 'popular', limit: 24 }, { throwOnError: true })
    products.value = Array.isArray(items) ? items : []
    error.value = false
  } catch (e) {
    error.value = true
    products.value = []
  } finally {
    loading.value = false
  }
}

onMounted(loadGiftCards)
</script>

<template>
  <Head title="WOW Gift Cards">
    <meta name="description" content="Digital WOW gift vouchers for therapies, classes, events and workshops." />
    <link rel="canonical" :href="(typeof window !== 'undefined' ? window.location.origin : '') + '/gift-cards'" />
  </Head>
  <SiteLayout>
    <section class="section">
      <div class="container-page">
        <div class="wow-hero-card gift-hero">
          <div class="wow-hero-grid">
            <div>
              <div class="kicker">Gift wellness</div>
              <h1 class="mt-3">WOW gift vouchers</h1>
              <p class="text-ink-700 text-lg mt-3">Send instant e-gift cards that can be redeemed on any therapy, class, workshop or event across We Offer Wellness.</p>
              <div class="mt-4 flex flex-wrap gap-3">
                <WowButton as="a" href="#gift-grid" variant="cta">Shop gift vouchers</WowButton>
                <WowButton as="a" href="/help/gift-cards" variant="ghost">How gifting works</WowButton>
              </div>
            </div>
            <div class="wow-hero-panel gift-panel">
              <p class="text-ink-600">Why people choose WOW gift cards:</p>
              <ul>
                <li>Instant delivery with your message.</li>
                <li>Use on therapies, classes, events or retreats.</li>
                <li>No expiry, balances tracked automatically.</li>
              </ul>
              <div class="mt-4">
                <WowButton as="a" href="/contact?topic=gifting" variant="outline">Bulk or corporate gifting</WowButton>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="gift-grid" class="section">
      <div class="container-page">
        <div class="mb-6">
          <div class="kicker">Digital gift cards</div>
          <h2 class="mt-2">Pick an amount that feels right</h2>
        </div>
        <div v-if="loading" class="card p-6 text-ink-600">Loading gift cards…</div>
        <template v-else>
          <div v-if="products.length" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <ProductCard v-for="product in products" :key="product.id" :product="product" :fluid="true" />
          </div>
          <div v-else class="card p-6 text-ink-700">
            <p class="m-0">We’re refreshing our gift card catalogue. <a href="/contact?topic=gifting" class="link">Contact the team</a> and we’ll create one for you.</p>
          </div>
          <div v-if="error" class="mt-4 text-ink-700">Unable to load gift cards right now. Please try again in a moment.</div>
        </template>
      </div>
    </section>
  </SiteLayout>
</template>

<style scoped>
.gift-hero{ background:linear-gradient(135deg,#f6f8ff,#ffffff); }
.gift-panel ul{ margin:1rem 0 0; padding-left:1.25rem; color:var(--ink-700); }
.gift-panel li{ margin-bottom:.35rem; }
</style>
