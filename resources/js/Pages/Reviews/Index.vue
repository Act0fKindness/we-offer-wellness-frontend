<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import SiteLayout from '@/Layouts/SiteLayout.vue'

const props = defineProps({
  reviews: { type: Object, default: () => ({ data: [], links: [] }) },
  meta: { type: Object, default: () => ({}) },
})

const pageTitle = computed(() => props.meta?.title || 'Client Reviews | We Offer Wellness')
const pageDescription = computed(() => props.meta?.description || 'Real stories from people who booked therapies, classes, workshops and retreats.')
const entries = computed(() => Array.isArray(props.reviews?.data) ? props.reviews.data : (props.reviews || []))
const links = computed(() => Array.isArray(props.reviews?.links) ? props.reviews.links : [])

function starLabel(rating) {
  if (!rating || rating <= 0) return null
  const filled = Math.min(5, Math.max(1, rating))
  return '★'.repeat(filled)
}

function displayDate(iso) {
  if (!iso) return ''
  try {
    const dt = new Date(iso)
    return new Intl.DateTimeFormat(undefined, { year: 'numeric', month: 'long' }).format(dt)
  } catch {
    return ''
  }
}
</script>

<template>
  <SiteLayout>
    <Head :title="pageTitle">
      <meta name="description" :content="pageDescription" />
      <link rel="canonical" href="https://www.weofferwellness.co.uk/reviews" />
    </Head>

    <section class="section">
      <div class="container-page">
        <div class="card p-6 md:p-10 reviews-hero">
          <div>
            <div class="kicker">Client voices</div>
            <h1>Trusted reviews from the WOW community</h1>
            <p class="lead">Every quote below comes from a verified booking through We Offer Wellness — spanning therapies, classes, workshops and retreats.</p>
          </div>
          <a href="/therapies" class="btn-wow btn-wow--outline btn-sm btn-arrow">
            <span class="btn-label">Browse therapies</span>
          </a>
        </div>
      </div>
    </section>

    <section class="section pt-0">
      <div class="container-page">
        <div v-if="entries.length" class="reviews-grid">
          <article v-for="review in entries" :key="review.id" class="card review-card">
            <div class="review-top">
              <div v-if="starLabel(review.rating)" class="stars" :aria-label="`Rated ${review.rating} out of 5`">{{ starLabel(review.rating) }}</div>
              <div class="meta">
                <div class="name">{{ review.customer || 'Verified client' }}</div>
                <div class="details">
                  <span v-if="review.product">{{ review.product }}</span>
                  <span v-if="review.vendor"> · {{ review.vendor }}</span>
                  <span v-if="review.location"> · {{ review.location }}</span>
                  <span v-if="displayDate(review.created_at)"> · {{ displayDate(review.created_at) }}</span>
                </div>
              </div>
            </div>
            <p class="quote">“{{ review.quote }}”</p>
          </article>
        </div>
        <div v-else class="card p-6 text-ink-600">Reviews will appear here once they’re published.</div>
        <nav v-if="links.length" class="pagination-nav">
          <a v-for="l in links" :key="l.url || l.label" :href="l.url || '#'" :class="['page-link', { active: l.active, disabled: !l.url } ]" v-html="l.label"/>
        </nav>
      </div>
    </section>
  </SiteLayout>
</template>

<style scoped>

.reviews-hero{ display:flex; align-items:flex-start; justify-content:space-between; gap:2rem; background:linear-gradient(120deg,#f8fafc,#f1f5f9); border:1px solid var(--ink-200); border-radius:24px; }
.reviews-hero h1{ font-size:clamp(2.2rem,4vw,3rem); margin-bottom:.75rem; color:#0b1323; }
.reviews-hero .lead{ font-size:1.05rem; color:#475569; max-width:720px; }

.reviews-grid{ display:grid; grid-template-columns: repeat(auto-fit, minmax(280px,1fr)); gap:1.2rem; }
.review-card{ border-radius:20px; padding:1.5rem; background:#fff; display:flex; flex-direction:column; gap:.9rem; }
.review-top{ display:flex; flex-direction:column; gap:.4rem; }
.stars{ color:#f59e0b; font-size:1rem; letter-spacing:.15em; }
.quote{ font-size:1.05rem; color:#0b1323; line-height:1.6; flex:1; }
.meta .name{ font-weight:600; color:#0f172a; }
.meta .details{ font-size:.9rem; color:#64748b; }

.pagination-nav{ display:flex; gap:.35rem; flex-wrap:wrap; justify-content:center; margin-top:1rem }
.page-link{ display:inline-block; padding:.4rem .7rem; border:1px solid var(--ink-200); border-radius:8px; color:#0b1323; text-decoration:none; background:#fff }
.page-link.active{ background:#0b1323; color:#fff; border-color:#0b1323 }
.page-link.disabled{ opacity:.5; pointer-events:none }

@media (max-width: 767.98px){
  .reviews-hero{ flex-direction:column; align-items:flex-start; }
}
</style>
