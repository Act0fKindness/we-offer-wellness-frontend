<script setup>
import { computed, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import SiteLayout from '@/Layouts/SiteLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import UltraSearchBar from '@/Components/UltraSearchBar.vue'
import ProductCard from '@/Components/ProductCard.vue'
import RecommendationsRail from '@/Components/RecommendationsRail.vue'
import DistanceFilter from '@/Components/DistanceFilter.vue'
import Pagination from '@/Components/Pagination.vue'
import ClassSchedule from '@/Components/ClassSchedule.vue'
import WowButton from '@/Components/ui/WowButton.vue'

const props = defineProps({
  type: { type: String, required: true },
  categories: { type: Array, default: () => [] },
  products: { type: [Array, Object], default: () => [] },
  mapsKey: { type: String, default: '' },
  geoStatus: { type: String, default: '' },
  userCity: { type: String, default: '' },
})

const heads = {
  therapies: { title: 'Therapies', kicker: '1‑to‑1 support', desc: 'Book trusted practitioners for massage, reiki, reflexology and more.' },
  events:    { title: 'Events', kicker: 'What’s on', desc: 'Find upcoming sound baths, breathwork, and community sessions.' },
  workshops: { title: 'Workshops', kicker: 'Learn & practice', desc: 'Hands‑on learning with small group workshops.' },
  classes:   { title: 'Classes', kicker: 'Weekly timetable', desc: 'Recurring yoga, movement and mindfulness classes.' },
  retreats:  { title: 'Retreats', kicker: 'Getaways', desc: 'Day and weekend retreats to recharge and reset.' },
  gifts:     { title: 'Gifts', kicker: 'High‑intent', desc: 'Digital gift cards and curated experience gifts.' },
  'near-me': { title: 'Near me', kicker: 'Local picks', desc: 'We’ll detect your location and show nearby options.' },
}

const heroCtaMap = {
  therapies: [
    { label: 'Browse therapies', href: '#hub-products', variant: 'cta', arrow: true },
    { label: 'Personalise my plan', href: '/plan', variant: 'ghost' },
  ],
  events: [
    { label: 'See what’s on', href: '#hub-products', variant: 'cta', arrow: true },
    { label: 'Plan with WOW', href: '/events-and-workshops', variant: 'ghost' },
  ],
  workshops: [
    { label: 'Explore workshops', href: '#hub-products', variant: 'cta', arrow: true },
    { label: 'Corporate options', href: '/corporate/wellbeing-workshops', variant: 'outline' },
  ],
  classes: [
    { label: 'View class schedule', href: '#hub-products', variant: 'cta', arrow: true },
    { label: 'Anytime resets', href: '/search?format=online&type=classes', variant: 'ghost' },
  ],
  retreats: [
    { label: 'Browse retreats', href: '#hub-products', variant: 'cta', arrow: true },
    { label: 'Request bespoke', href: '/contact?topic=retreats', variant: 'outline' },
  ],
  gifts: [
    { label: 'Shop curated gifts', href: '#hub-products', variant: 'cta', arrow: true },
    { label: 'Send gift vouchers', href: '/gift-cards', variant: 'ghost' },
  ],
  'near-me': [
    { label: 'See nearby options', href: '#hub-products', variant: 'cta', arrow: true },
    { label: 'Search everything', href: '/search', variant: 'ghost' },
  ],
  default: [
    { label: 'Browse listings', href: '#hub-products', variant: 'cta', arrow: true },
  ],
}

const heroPanelMap = {
  therapies: {
    kicker: 'Safety-first',
    title: 'Practitioners we trust',
    copy: 'We review modalities for trauma-awareness, insurance and client care before featuring them here.',
    items: [
      'Massage, reiki, reflexology & more',
      'Online + in-person availability',
      'Transparent pricing & reviews',
    ],
    cta: { label: 'Safety & contraindications', href: '/safety-and-contraindications', variant: 'ghost' },
  },
  events: {
    kicker: 'Guided gatherings',
    title: 'Breath, sound & somatics',
    copy: 'From WOW-hosted series to partner pop-ups, each event is designed to keep nervous systems regulated.',
    items: [
      'Small, inclusive groups',
      'Corporate & private options',
      'Vetted facilitators',
    ],
    cta: { label: 'Browse featured events', href: '/events-and-workshops', variant: 'outline', arrow: true },
  },
  workshops: {
    kicker: 'Hands-on learning',
    title: 'Workshops that go deeper',
    copy: 'Breathwork, somatics, creativity and workplace wellbeing delivered by trusted facilitators.',
    items: [
      'Breath & nervous system labs',
      'Sound baths & restorative circles',
      'Custom sessions for teams',
    ],
  },
  classes: {
    kicker: 'Weekly rhythm',
    title: 'Classes for every schedule',
    copy: 'Mix live timetables with on-demand replays across yoga, somatics and gentle strength.',
    items: [
      'Morning, lunch & evening slots',
      'Online + studio availability',
      'Filter by focus & pace',
    ],
    cta: { label: 'See today’s schedule', href: '#hub-products', variant: 'ghost' },
  },
  retreats: {
    kicker: 'Plan ahead',
    title: 'Small group getaways',
    copy: 'Day and weekend retreats curated with nervous-system friendly itineraries.',
    items: [
      'Nature-led venues',
      'Evidence-informed facilitators',
      'Bespoke itineraries available',
    ],
    cta: { label: 'Enquire about retreats', href: '/contact?topic=retreats', variant: 'outline' },
  },
  gifts: {
    kicker: 'High-intent gifting',
    title: 'Experiences they’ll love',
    copy: 'Send curated sessions or instant cards redeemable on every WOW category.',
    items: [
      'Handpicked therapies & classes',
      'Schedule or send instantly',
      'No expiry, balances tracked',
    ],
    cta: { label: 'Send a gift card', href: '/gift-cards', variant: 'cta' },
  },
  'near-me': {
    kicker: 'Local mode',
    title: 'Share location once',
    copy: 'Opt in to organise therapies, classes and events by distance and travel time.',
    items: [
      'Secure, opt-in detection',
      'Filter by travel time',
      'Map + list view',
    ],
    cta: { label: 'How location works', href: '/help', variant: 'ghost' },
  },
}

const meta = computed(() => heads[props.type] || { title: 'Discover Wellness', kicker: 'Explore', desc: 'Browse categories and popular choices.' })
const categoriesFiltered = computed(() => (Array.isArray(props.categories) ? props.categories : []).filter(c => Number(c.count||0) > 0))
const productsTitle = computed(() => props.type === 'therapies' ? 'All therapies' : 'Popular right now')
const canonical = computed(() => {
  const path = '/' + (props.type || '').toString()
  try { return window.location.origin + path } catch { return path }
})
const desc = computed(() => meta.value.desc)
const items = computed(() => Array.isArray(props.products) ? props.products : (props.products?.data || []))
const filteredIds = ref([])
const showItems = computed(() => {
  if (!filteredIds.value.length) return items.value
  const set = new Set(filteredIds.value)
  return items.value.filter(p => set.has(p.id))
})
const itemListLd = computed(() => {
  const list = items.value.slice(0, 24)
  return {
    '@context': 'https://schema.org',
    '@type': 'ItemList',
    'itemListElement': list.map((p, i) => ({ '@type': 'ListItem', position: i+1, url: p.url, name: p.title }))
  }
})
const breadcrumbLd = computed(() => ({
  '@context': 'https://schema.org',
  '@type': 'BreadcrumbList',
  'itemListElement': [
    { '@type': 'ListItem', position: 1, name: 'Home', item: (typeof window!=='undefined' ? window.location.origin : '/') },
    { '@type': 'ListItem', position: 2, name: meta.value.title, item: canonical.value }
  ]
}))
const faqLd = computed(() => ({
  '@context': 'https://schema.org', '@type': 'FAQPage',
  'mainEntity': [
    { '@type': 'Question', 'name': 'Can I book online or in‑person?', 'acceptedAnswer': { '@type': 'Answer', 'text': 'Yes. Many therapies offer both online and in‑studio sessions. Filter by mode on search and listing pages.' } },
    { '@type': 'Question', 'name': 'Are practitioners verified?', 'acceptedAnswer': { '@type': 'Answer', 'text': 'We review and verify practitioners where applicable and publish transparent pricing and reviews.' } }
  ]
}))
const heroCtas = computed(() => heroCtaMap[props.type] || heroCtaMap.default)
const heroPanel = computed(() => heroPanelMap[props.type] || null)
const needsGeo = computed(() => props.type === 'near-me' && props.geoStatus !== 'ready')

function requestLocationAccess() {
  try {
    document.cookie = 'wow_geo_reask=1; Max-Age=300; Path=/; SameSite=Lax'
  } catch {}
  try { window.location.reload() } catch {}
}
</script>

<template>
  <Head :title="meta.title">
    <meta name="description" :content="desc" />
    <link rel="canonical" :href="canonical" />
    <meta property="og:title" :content="meta.title" />
    <meta property="og:description" :content="desc" />
    <meta property="og:url" :content="canonical" />
    <script type="application/ld+json">{{ JSON.stringify(breadcrumbLd) }}</script>
    <script type="application/ld+json">{{ JSON.stringify(itemListLd) }}</script>
    <script type="application/ld+json">{{ JSON.stringify(faqLd) }}</script>
  </Head>
  <SiteLayout>
    <!-- Hero -->
    <section class="section">
      <div class="container-page">
        <Breadcrumbs :items="[{ label: meta.title }]" />
        <div class="wow-hero-card hub-hero mt-6">
          <div class="wow-hero-grid">
            <div>
              <div class="kicker">{{ meta.kicker }}</div>
              <h1 class="mt-2">{{ meta.title }}</h1>
              <p class="text-ink-600 text-lg mt-2 max-w-2xl">{{ meta.desc }}</p>
              <div v-if="heroCtas?.length" class="mt-4 flex flex-wrap gap-3">
                <WowButton v-for="cta in heroCtas" :key="cta.label" as="a" :href="cta.href" :variant="cta.variant || 'cta'" :arrow="cta.arrow || false">
                  {{ cta.label }}
                </WowButton>
              </div>
            </div>
            <div v-if="heroPanel" class="wow-hero-panel">
              <div v-if="heroPanel.kicker" class="kicker mb-2">{{ heroPanel.kicker }}</div>
              <h3 class="m-0">{{ heroPanel.title }}</h3>
              <p v-if="heroPanel.copy" class="text-ink-600 mt-2">{{ heroPanel.copy }}</p>
              <ul v-if="heroPanel.items?.length" class="mt-3">
                <li v-for="item in heroPanel.items" :key="item">{{ item }}</li>
              </ul>
              <div v-if="heroPanel.cta" class="mt-4">
                <WowButton as="a" :href="heroPanel.cta.href" :variant="heroPanel.cta.variant || 'outline'" :arrow="heroPanel.cta.arrow || false">
                  {{ heroPanel.cta.label }}
                </WowButton>
              </div>
            </div>
          </div>
        </div>
        <!-- Search removed on hubs (kept on Home and Search only) -->
      </div>
    </section>

    <!-- Classes timetable near top of Classes hub -->
    <ClassSchedule v-if="props.type==='classes'" :products="items" title="Today’s Class Schedule" />

    <!-- Categories -->
    <section class="section" v-if="categoriesFiltered?.length && props.type!=='near-me'">
      <div class="container-page">
        <div class="mb-4 flex items-center justify-between">
          <h2 class="h4 m-0 section-title">Browse by category</h2>
          <a href="/search" class="btn-wow btn-wow--outline btn-sm btn-arrow">
            <span class="btn-label">See all</span>
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
        <div class="cat-grid">
          <a v-for="c in categoriesFiltered" :key="c.slug" class="cat-card" :href="c.url">
            <div class="cat-top">
              <span class="cat-name">{{ c.name }}</span>
              <span class="cat-arrow" aria-hidden="true"></span>
            </div>
            <div class="cat-bottom">
              <span class="cat-count" v-if="Number(c.count||0) > 0">{{ c.count }} listings</span>
            </div>
          </a>
        </div>
      </div>
  </section>

    <!-- Products -->
    <section class="section" id="hub-products">
      <div class="container-page">
        <div class="mb-3 flex items-center justify-between">
          <h2 class="h4 m-0 section-title">{{ productsTitle }}</h2>
          <a v-if="props.type !== 'therapies'" :href="`/search?sort=popular`" class="btn-wow btn-wow--outline btn-sm btn-arrow">
            <span class="btn-label">Shop all</span>
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
        <div v-if="needsGeo" class="card p-6 text-ink-700">
          <p class="m-0">We couldn’t detect your town yet. Use the “Near me” button in the header or share your location to see nearby sessions.</p>
          <div class="mt-3">
            <button class="btn-wow btn-wow--cta btn-sm" type="button" @click="requestLocationAccess">Share location</button>
          </div>
        </div>
        <template v-else>
          <DistanceFilter v-if="props.mapsKey && props.type==='near-me'" :maps-key="props.mapsKey" :products="items" @filtered="ids => filteredIds = ids" />
          <div v-if="items?.length" class="grid sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 gap-4">
            <ProductCard v-for="p in showItems" :key="p.id" :product="p" :fluid="true" />
          </div>
          <div v-else class="card p-6 text-ink-700">We’re curating offerings here. Try another category or check back soon.</div>
          <Pagination v-if="!Array.isArray(props.products) && props.products?.links" :links="props.products.links" />
        </template>
      </div>
    </section>

    <!-- Fallback sections when categories are missing/zero (ecommerce helpful rails) -->
    <RecommendationsRail v-if="!categoriesFiltered.length && (props.type==='therapies')" title="Top‑rated therapies" :query="{ type: 'therapies', sort: 'popular' }" :limit="12" />
    <RecommendationsRail v-if="!categoriesFiltered.length && (props.type==='therapies')"
      title="New this month"
      subtitle="Fresh arrivals vetted by our team"
      theme="brand"
      :query="{ type: 'therapies', sort: 'newest' }"
      :limit="12" />
    <RecommendationsRail v-if="!categoriesFiltered.length && (props.type==='therapies')"
      title="Under £50"
      subtitle="Feel better without breaking the bank"
      theme="muted"
      :query="{ type: 'therapies', sort:'popular' }"
      :usePriceBands="true"
      :limit="12" />
  </SiteLayout>
  </template>

<style scoped>
.h4 { font-size: 1.25rem; font-weight: 600; }
/* Category grid (beautifully styled) */
.cat-grid{ display:grid; grid-template-columns: repeat(1,minmax(0,1fr)); gap:12px }
@media (min-width: 576px){ .cat-grid{ grid-template-columns: repeat(2,minmax(0,1fr)) } }
@media (min-width: 992px){ .cat-grid{ grid-template-columns: repeat(3,minmax(0,1fr)) } }
@media (min-width: 1200px){ .cat-grid{ grid-template-columns: repeat(4,minmax(0,1fr)) } }
.cat-card{ position:relative; display:flex; flex-direction:column; gap:8px; padding:14px 14px; border-radius:16px; background:linear-gradient(180deg,#ffffff, #f9fbfd); border:1px solid var(--ink-200); box-shadow: 0 6px 16px rgba(2,8,23,.06); text-decoration:none }
.cat-card::after{ content:""; position:absolute; inset:0; border-radius:16px; background: radial-gradient(120px 60px at 100% 0%, rgba(84,148,131,.08), transparent 60%), radial-gradient(120px 60px at 0% 100%, rgba(84,148,131,.06), transparent 60%); pointer-events:none }
.cat-top{ display:flex; align-items:center; gap:10px }
.cat-name{ color:#0b1323; font-weight:700; letter-spacing:.2px }
.cat-arrow{ margin-left:auto; width:18px; height:18px; background: #3b7768; opacity:.8; -webkit-mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/20/solid/chevron-right.svg') no-repeat center/contain; mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/20/solid/chevron-right.svg') no-repeat center/contain; transition: transform .18s ease }
.cat-bottom{ display:flex; align-items:center; gap:8px; color: var(--ink-600) }
.cat-count{ display:inline-flex; align-items:center; height:24px; padding:0 8px; border-radius:999px; background:#eef2f7; font-size:.85rem; color:#334155; border:1px solid #e2e8f0 }
.cat-card:hover{ transform: translateY(-2px); box-shadow: 0 10px 22px rgba(2,8,23,.10) }
.cat-card:hover .cat-arrow{ transform: translateX(2px) }
</style>
