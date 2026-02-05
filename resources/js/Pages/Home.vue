<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import SiteLayout from '@/Layouts/SiteLayout.vue'
import SquareButton from '@/Components/ui/SquareButton.vue'
import WowButton from '@/Components/ui/WowButton.vue'
// removed SquareOutlineButton import: heading CTAs should use standard outline buttons
import ProductCarousel from '@/Components/ProductCarousel.vue'
import ProductCard from '@/Components/ProductCard.vue'
import { fetchProducts, byTag, underPrice, sortByRating, sortByNewest } from '@/services/products'
import { fetchArticles } from '@/services/articles'
import UltraSearchBar from '@/Components/UltraSearchBar.vue'
import ClassSchedule from '@/Components/ClassSchedule.vue'
import PainpointTiles from '@/Components/PainpointTiles.vue'
import FeatureBand from '@/Components/FeatureBand.vue'
import RecommendationGrid from '@/Components/RecommendationGrid.vue'
import { fetchPainpoints } from '@/services/painpoints'
import WellnessQuiz from '@/Components/WellnessQuiz.vue'
import ResetStarterCTA from '@/Components/ResetStarterCTA.vue'
import { fetchFeaturedReviews } from '@/services/reviews'
// SmartDuoToggle replaced with segmented tabs on homepage
// import CategorySection from '@/Components/CategorySection.vue'
// import { fetchCatalog } from '@/services/catalog'

const props = defineProps({
  mapboxKey: { type: String, required: true },
})

// Region-aware homepage SEO
const appName = import.meta.env.VITE_APP_NAME || 'We Offer Wellness®'
function cookieGet(name){
  const m = typeof document !== 'undefined' && document.cookie.match('(^|;)\\s*'+name+'\\s*=\\s*([^;]+)');
  return m ? decodeURIComponent(m.pop()) : ''
}
function detectMarket(){
  try {
    const ctry = (cookieGet('wow_country') || '').toLowerCase()
    if (ctry.includes('united states') || ctry === 'us' || ctry === 'usa' || ctry.includes('america')) return 'us'
    if (ctry.includes('united kingdom') || ctry === 'uk' || ctry === 'gb' || ctry.includes('great britain')) return 'uk'
  } catch {}
  try {
    const host = (typeof location !== 'undefined' ? location.hostname : '').toLowerCase()
    if (host.endsWith('.co.uk') || host.endsWith('.uk')) return 'uk'
    if (host.endsWith('.com')) return 'us'
  } catch {}
  try {
    const lang = (typeof navigator !== 'undefined' ? navigator.language : '').toLowerCase()
    if (lang.includes('en-us') || /-us$/.test(lang)) return 'us'
    if (lang.includes('en-gb') || /-gb$/.test(lang)) return 'uk'
  } catch {}
  return 'uk'
}
const market = computed(() => detectMarket())
const homeTitle = computed(() =>
  market.value === 'us'
    ? `Wellness Therapies That Work | ${appName}`
    : `Holistic Therapy That Works | ${appName}`
)
const metaDescription = 'Holistic therapy, done right: new classes daily, frequent workshops & events, plus restorative retreats—led by trusted practitioners at We Offer Wellness®.'

const heroSecondaryCopy = ref('Therapies, classes, and workshops curated by practitioners you can trust so you can feel better, faster.')
const heroPromo = ref(null)
const showWhatsOnSection = false
const reviewsHref = '/reviews'
const mindfulTimesUrl = 'https://times.weofferwellness.co.uk'
const podcastUrl = `${mindfulTimesUrl}/seeking-wellness`
const podcastImage = 'https://cdn.shopify.com/s/files/1/0820/3947/2469/files/seeking-wellness.png?v=1746224190'
const practitionerChatsUrl = `${mindfulTimesUrl}#practitioner-chats`
const sevenDayGuideHref = `${mindfulTimesUrl}/7-day-reset-starter-kit`
const showMindfulTimesRibbon = true
const shopCategories = [
  {
    title: 'Breathwork',
    href: '/search?type=therapy&what=breathwork',
    img: 'https://images.unsplash.com/photo-1520880867055-1e30d1cb001c?q=80&w=1200&auto=format&fit=crop',
  },
  {
    title: 'Sound Healing',
    href: '/search?type=therapy&what=sound%20bath',
    img: 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?q=80&w=1200&auto=format&fit=crop',
  },
  {
    title: 'Massage Therapy',
    href: '/therapies/massage-therapy',
    img: 'https://images.unsplash.com/photo-1612152918775-49ed1e1c1d1f?q=80&w=1200&auto=format&fit=crop',
  },
  {
    title: 'Yoga & Movement',
    href: '/classes/yoga',
    img: 'https://images.unsplash.com/photo-1518611012118-696072aa579a?q=80&w=1200&auto=format&fit=crop',
  },
  {
    title: 'Reiki & Energy',
    href: '/therapies/reiki',
    img: 'https://images.unsplash.com/photo-1524492412937-b28074a5d7da?q=80&w=1200&auto=format&fit=crop',
  },
  {
    title: 'Coaching & Mindset',
    href: '/search?type=therapy&what=coaching',
    img: 'https://images.unsplash.com/photo-1497352305433-9b09be07364b?q=80&w=1200&auto=format&fit=crop',
  },
]

const heroStats = [
  { label: 'sessions booked', value: '12k+' },
  { label: 'secure checkout', value: '100%' },
]


function handleHeroPromoClick() {
  if (!heroPromo.value?.href) return
  try {
    window.location.href = heroPromo.value.href
  } catch {}
}

const values = [
  {
    title: 'Therapies first',
    copy: 'Evidence-informed modalities and trauma-aware practitioners are prioritised before everything else we do.',
  },
  {
    title: 'Human guidance',
    copy: 'Every offering is reviewed by our practitioner team so you know who is holding space for you.',
  },
  {
    title: 'Whole-self care',
    copy: 'We look at sleep, stress, digestion, hormones and energy together—never in isolation.',
  },
]

const reviewStats = ref({ avg_rating: null, verified_count: 0, review_count: 0 })
function fmtAvg(r){
  const n = Number(r)
  if (!Number.isFinite(n) || n <= 0) return '—'
  return n.toFixed(1) + '/5'
}
function fmtCount(n){
  const num = Number(n||0)
  if (num <= 0) return '0'
  return new Intl.NumberFormat(undefined).format(num) + '+'
}

// Feelings-first tiles: title = desired state; chip = pain point label
const problems = [
  { id: 'stress', title: 'Calm & grounded', chip: 'Stress & overwhelm', blurb: 'Rapid nervous‑system resets to ease tension and clear the mind.', emoji: '🧘', color: 'from-brand-500 to-accent-600' },
  { id: 'sleep',  title: 'Sleep better',    chip: 'Sleep & recovery', blurb: 'Wind‑down rituals and breathwork for deeper, more restful sleep.', emoji: '🌙', color: 'from-accent-500 to-brand-600' },
  { id: 'energy', title: 'More energy & focus', chip: 'Low energy & focus', blurb: 'Recharge with breath, cold exposure education and mindful movement.', emoji: '⚡', color: 'from-brand-500 to-accent-500' },
  { id: 'pain',   title: 'Move with ease',   chip: 'Pain & mobility',  blurb: 'Gentle mobility and release work to ease pain and restore flow.', emoji: '🦴', color: 'from-accent-600 to-brand-600' },
]

const sessions = [
  { id: 1, when: 'Today • 6:30pm', title: 'Guided Breathwork (online)', mode: 'Online', spots: 4 },
  { id: 2, when: 'Tomorrow • 7:00am', title: 'Cold Plunge & Breath • Shoreditch', mode: 'In‑studio', spots: 2 },
  { id: 3, when: 'Thu • 12:30pm', title: 'Desk Reset: 20‑min Mobility', mode: 'Online', spots: 9 },
  { id: 4, when: 'Fri • 5:30pm', title: 'Rest & Restore Nidra', mode: 'In‑studio', spots: 3 },
  { id: 5, when: 'Sat • 10:00am', title: 'Community Cold + Sauna', mode: 'In‑studio', spots: 7 },
]

const posts = ref([])
const featuredArticle = computed(() => (posts.value && posts.value.length ? posts.value[0] : null))
const secondaryArticles = computed(() => (posts.value || []).slice(1, 3))
const painpoints = ref([])
const selectedPain = ref(null)
const quizOpen = ref(false)
const abVariant = ref('A')
const testimonials = ref([])
const podcastEmbedSrc = 'https://open.spotify.com/embed/show/5L8zM83I4zTtQVl0UvsUt7'
const homeLoadError = ref(false)

function recordHomeError(label, error) {
  console.error(`[home] ${label}`, error)
  homeLoadError.value = true
}

async function loadTestimonials() {
  try {
    const data = await fetchFeaturedReviews({ limit: 3, minRating: 4 })
    testimonials.value = data
  } catch (error) {
    console.warn('[home] featured reviews failed', error)
    testimonials.value = []
  }
}

// (Reverted) hero booking teaser — static demo content

// Dynamic "What’s on this week"
const classesWeek = ref([])
const workshopsWeek = ref([])
const retreatsWeek = ref([])
// Full lists (for upcoming/soon fallback)
const classesAll = ref([])
const workshopsAll = ref([])
const retreatsAll = ref([])
const filterMode = ref('all') // 'all' | 'online' | 'in-person'
function startOfWeek(d = new Date()) {
  const date = new Date(d)
  const day = (date.getDay() + 6) % 7 // Monday=0
  date.setHours(0,0,0,0)
  date.setDate(date.getDate() - day)
  return date
}
function endOfWeek(d = new Date()) {
  const s = startOfWeek(d)
  const e = new Date(s)
  e.setDate(e.getDate() + 7)
  e.setMilliseconds(-1)
  return e
}
const weekStart = startOfWeek()
const weekEnd = endOfWeek()
const soonEnd = (() => { const d = new Date(weekEnd); d.setDate(d.getDate()+30); return d })()

function toDate(iso){ try { return iso ? new Date(iso) : null } catch { return null } }
function deriveStart(item){ return toDate(item?.date) || toDate(item?.start_date) || null }
function isLiveNow(start){ if (!start) return false; const now = new Date(); const end = new Date(start.getTime() + 75*60*1000); return now >= start && now <= end }
function overlapsWeek(item){
  const s = toDate(item.start_date || item.date)
  const e = toDate(item.end_date || item.date)
  if (!s && !e) return false
  const a = s || e, b = e || s
  const from = a || new Date(0)
  const to = b || a || new Date(0)
  return from <= weekEnd && to >= weekStart
}
function whenLabel(item){
  const dt = toDate(item.date || item.start_date || null)
  if (!dt) return ''
  const today = new Date(); today.setHours(0,0,0,0)
  const d = new Date(dt); d.setSeconds(0)
  const diffDays = Math.floor((d - today) / (24*3600*1000))
  const time = d.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' })
  if (diffDays === 0) return `Today • ${time}`
  if (diffDays === 1) return `Tomorrow • ${time}`
  const day = d.toLocaleDateString(undefined, { weekday: 'short' })
  return `${day} • ${time}`
}
const weekItems = computed(() => {
  const merged = [...classesWeek.value, ...workshopsWeek.value, ...retreatsWeek.value]
  const filtered = merged.filter(it => {
    if (filterMode.value === 'online') return (it.mode || '').toLowerCase() === 'online'
    if (filterMode.value === 'in-person') return (it.mode || '').toLowerCase() !== 'online'
    return true
  })
  // sort by soonest date within the week
  filtered.sort((a,b) => new Date(a.date || a.start_date || 0) - new Date(b.date || b.start_date || 0))
  return filtered.slice(0, 5)
})
const soonItems = computed(() => {
  // Items not in this week, but coming in the next ~30 days
  const merged = [
    ...classesAll.value,
    ...workshopsAll.value,
    ...retreatsAll.value,
  ]
  const list = merged.filter(it => {
    const d = toDate(it.date || it.start_date || null)
    if (!d) return false
    return d > weekEnd && d <= soonEnd
  })
  list.sort((a,b) => new Date(a.date || a.start_date || 0) - new Date(b.date || b.start_date || 0))
  return list.slice(0, 5).map(it => ({ ...it, when: whenLabel(it) }))
})
const emptyStates = computed(() => ({
  classes: classesWeek.value.length === 0,
  workshops: workshopsWeek.value.length === 0,
  retreats: retreatsWeek.value.length === 0,
}))

const all = ref([])
const popular = ref([])
const newest = ref([])
const giftsUnder50 = ref([])
// const categories = ref([])

// Comfort of home — smart dual filters
const comfortAllOnline = ref([])
const comfortRef = ref(null)
const comfortPriceMax = ref(50)
const comfortPeople = ref('solo') // 'solo' | 'couple' | 'group'

function isOnline(p){
  const loc = String(p?.location || '').toLowerCase()
  const fmt = String(p?.format || '').toLowerCase()
  if (loc === 'online' || fmt.includes('online')) return true
  if (Array.isArray(p?.locations)) {
    if (p.locations.some(x => String(x || '').toLowerCase() === 'online')) return true
  }
  const tags = Array.isArray(p?.tags) ? p.tags.map(t => String(t).toLowerCase()) : []
  if (tags.includes('online')) return true
  return false
}
function priceMin(p){
  const candidates = [p?.price_min, p?.price, p?.variants_min_price].map(v => Number(v))
  let n = candidates.find(x => Number.isFinite(x) && x > 0)
  if (n == null && Array.isArray(p?.variants)){
    const vals = p.variants.map(v => Number(v?.price)).filter(x => Number.isFinite(x) && x > 0)
    if (vals.length) n = Math.min(...vals)
  }
  return Number.isFinite(n) ? n : Infinity
}
function personNumbersFromOptions(p){
  const nums = new Set()
  const arrs = [p?.options, p?.product_options]
  for (const arr of arrs){
    if (!Array.isArray(arr)) continue
    const match = arr.find(o => /person|people|guests?/i.test(String(o?.name || o?.meta_name || '')))
    const vals = match ? (match.values || match.option_values || match.values_list) : null
    const texts = Array.isArray(vals) ? vals.map(v => String(v?.value ?? v)) : []
    for (const t of texts){
      const n = parseInt(t.replace(/[^0-9]/g,''),10)
      if (Number.isFinite(n) && n > 0) nums.add(n)
    }
  }
  // Fallback to capacity range if explicit persons option is absent
  if (nums.size === 0 && (p?.capacity_max || p?.capacity_min)){
    const a = Number(p.capacity_min || 1), b = Number(p.capacity_max || p.capacity || a)
    if (Number.isFinite(a)) nums.add(a)
    if (Number.isFinite(b)) nums.add(b)
  }
  return nums
}
function fitsPeople(p, kind){
  const nums = personNumbersFromOptions(p)
  if (nums.size === 0) return true // inclusive fallback when not specified
  const has1 = nums.has(1)
  const has2 = nums.has(2)
  const hasGroup = [...nums].some(n => n > 2)
  if (kind === 'solo') return has1
  if (kind === 'couple') return has2
  if (kind === 'group') return hasGroup
  return true
}
const comfortFiltered = computed(() => {
  const max = Number(comfortPriceMax.value) || 50
  // Premium tier does not affect filtering logic; map to base types
  const who = String(comfortPeople.value || 'solo')
  return comfortAllOnline.value
    .filter(p => priceMin(p) < max)
    .filter(p => fitsPeople(p, who))
    .slice(0, 12)
})

function comfortScrollBy(dir){
  const el = comfortRef.value
  if (!el) return
  const amount = Math.min(900, el.clientWidth * 0.9)
  el.scrollBy({ left: dir * amount, behavior: 'smooth' })
}

async function mixByType(sort = 'popular', take = 12) {
  // Pull a balanced mix across types; interleave to avoid one type (events) dominating
  const types = ['event', 'workshop', 'class', 'retreat', 'experience'] // therapy often stored as 'experience'
  const lists = await Promise.all(types.map(t => fetchProducts({ type: t, sort, limit: 10 })))
  const buckets = lists.map(arr => (Array.isArray(arr) ? arr : []))
  const seen = new Set()
  const out = []
  let idx = 0
  while (out.length < take) {
    let added = false
    for (let b = 0; b < buckets.length && out.length < take; b++) {
      const item = buckets[b][idx]
      if (item && !seen.has(item.id)) { out.push(item); seen.add(item.id); added = true }
    }
    if (!added) break
    idx++
  }
  // Fallback: top items regardless of type to fill remaining slots
  if (out.length < take) {
    const top = await fetchProducts({ sort, limit: take * 2 })
    for (const it of top) { if (!seen.has(it.id)) { out.push(it); seen.add(it.id) } if (out.length >= take) break }
  }
  return out
}

onMounted(async () => {
  // Server-lean fetches for performance
  try {
    popular.value = await mixByType('popular', 12)
  } catch (error) {
    recordHomeError('popular', error)
    popular.value = []
  }
  try {
    newest.value = await mixByType('newest', 12)
  } catch (error) {
    recordHomeError('newest', error)
    newest.value = []
  }
  try {
    const under = await fetchProducts({ tag: 'Gift', price_max: 50, limit: 50 })
    giftsUnder50.value = under.filter(p => Number(p.price_min || p.price) < 50)
  } catch (error) {
    recordHomeError('gifts-under-50', error)
    giftsUnder50.value = []
  }
  try {
    posts.value = await fetchArticles(3)
  } catch (error) {
    recordHomeError('articles', error)
    posts.value = []
  }
  try {
    await loadTestimonials()
  } catch (error) {
    recordHomeError('testimonials', error)
  }
  // Painpoints & selection/rotation
  try {
    const api = await fetchPainpoints()
    painpoints.value = Array.isArray(api) ? api : []
    const ref = (document.referrer || '').toLowerCase()
    const fromNeed = painpoints.value.find(pp => ref.includes(`/need/${pp.key}`))
    const lastKey = localStorage.getItem('wow_pp_last')
    selectedPain.value = fromNeed || painpoints.value.find(pp => pp.key === lastKey) || painpoints.value[0]
    if (selectedPain.value) localStorage.setItem('wow_pp_last', selectedPain.value.key)
  } catch {}
  try {
    let v = localStorage.getItem('wow_ab_variant')
    if (v !== 'A' && v !== 'B') { v = Math.random() < 0.5 ? 'A' : 'B'; localStorage.setItem('wow_ab_variant', v) }
    abVariant.value = v
  } catch {}
  // categories.value = await fetchCatalog({ product_limit: 12 })

  // Comfort: preload a wide set then filter to online
  try {
    const top = await fetchProducts({ sort: 'popular', limit: 160 })
    comfortAllOnline.value = (Array.isArray(top) ? top : []).filter(isOnline)
    // If too few online from popular, backfill with newest
    if (comfortAllOnline.value.length < 24) {
      const more = await fetchProducts({ sort: 'newest', limit: 160 })
      const add = (Array.isArray(more) ? more : []).filter(isOnline)
      const ids = new Set(comfortAllOnline.value.map(p => p.id))
      comfortAllOnline.value.push(...add.filter(p => !ids.has(p.id)))
    }
  } catch {}

  // Fetch by type and filter to this week
  try {
    const [cls, wks, rts] = await Promise.all([
      fetchProducts({ type: 'class', sort: 'popular', limit: 60 }),
      fetchProducts({ type: 'workshop', sort: 'popular', limit: 60 }),
      fetchProducts({ type: 'retreat', sort: 'popular', limit: 60 }),
    ])
    classesAll.value = Array.isArray(cls) ? cls : []
    workshopsAll.value = Array.isArray(wks) ? wks : []
    retreatsAll.value = Array.isArray(rts) ? rts : []
    classesWeek.value = classesAll.value.filter(overlapsWeek).map(it => ({ ...it, when: whenLabel(it) }))
    workshopsWeek.value = workshopsAll.value.filter(overlapsWeek).map(it => ({ ...it, when: whenLabel(it) }))
    retreatsWeek.value = retreatsAll.value.filter(overlapsWeek).map(it => ({ ...it, when: whenLabel(it) }))
  } catch {}

  // Reviews (site-wide)
  try {
    const res = await fetch('/api/review-stats', { cache: 'no-store' })
    if (res.ok) {
      const data = await res.json()
      reviewStats.value = {
        avg_rating: data?.avg_rating ?? null,
        verified_count: data?.verified_count ?? 0,
        review_count: data?.review_count ?? 0,
      }
    }
  } catch {}
})

function sizePanelMarquee() {
  try {
    const vps = document.querySelectorAll('.whero-grid-viewport')
    vps.forEach((vp) => {
      const row = vp.querySelector('.whero-grid')
      if (!row) return
      const dist = Math.max(0, row.scrollWidth - vp.clientWidth)
      row.style.setProperty('--scrollDist', dist + 'px')
      if (dist <= 4) {
        row.style.setProperty('--drift-time', '0s')
        if (row instanceof HTMLElement) row.style.animation = 'none'
      } else {
        const duration = Math.min(36, Math.max(16, 12 + dist / 80))
        row.style.setProperty('--drift-time', duration.toFixed(1) + 's')
        if (row instanceof HTMLElement) row.style.animation = ''
      }
    })
  } catch {}
}

onMounted(() => {
  setTimeout(sizePanelMarquee, 0)
  window.addEventListener('resize', sizePanelMarquee)
})

// Sticky search visibility (desktop)
const stickyVisible = ref(false)
const navHeight = ref(64)
const heroEl = ref(null)
let heroEndY = 0
let lastY = 0

function measure() {
  const header = document.querySelector('header')
  navHeight.value = header?.offsetHeight || 64
  if (heroEl.value) {
    const r = heroEl.value.getBoundingClientRect()
    heroEndY = window.scrollY + r.top + r.height
  }
}

function onScroll() {
  const y = window.scrollY
  const goingDown = y > lastY + 2
  const goingUp = y < lastY - 2
  lastY = y

  if (y <= heroEndY) {
    stickyVisible.value = false
    return
  }
  const grace = 200 // show just after hero for a little bit
  if (y <= heroEndY + grace) {
    stickyVisible.value = true
    return
  }
  stickyVisible.value = goingUp
}

onMounted(() => {
  measure()
  window.addEventListener('scroll', onScroll, { passive: true })
  window.addEventListener('resize', measure)
  // initial state
  onScroll()
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', sizePanelMarquee)
  window.removeEventListener('scroll', onScroll)
  window.removeEventListener('resize', measure)
})
</script>

<template>
  <Head :title="homeTitle">
    <meta name="description" :content="metaDescription" />
    <link rel="canonical" :href="(typeof window!=='undefined'?window.location.origin + '/' : '/')" />
    <meta property="og:title" :content="homeTitle" />
    <meta property="og:description" :content="metaDescription" />
    <meta property="og:url" :content="(typeof window!=='undefined'?window.location.href:'')" />
  </Head>
  <SiteLayout>
    <!-- Sticky Search (desktop only) -->
    <div v-show="stickyVisible" class="hidden lg:block fixed left-0 right-0 z-30 transition-all" :style="{ top: navHeight + 'px' }">
      <div class="container-page py-2">
        <UltraSearchBar id-prefix="home-sticky" :compact="true" />
      </div>
    </div>

    <!-- New Hero (template adapted) -->
    <section class="whero" ref="heroEl">
      <div class="whero-radial" aria-hidden="true"></div>
      <div class="container whero-pad">
        <div class="row align-items-center g-5">
          <div class="col-12 col-lg-7">
            <span class="whero-eyebrow">Trusted holistic therapies</span>
            <h1 class="whero-title">Discover therapies and classes that work for you</h1>
            <p class="whero-sub mt-3">
              Every part of your well-being is connected — stress, sleep, energy, digestion, and calm all thread through one another.
              <span class="whero-subline">{{ heroSecondaryCopy }}</span>
            </p>
            <form class="whero-cta mt-4" action="#" method="post" onsubmit="return false;">
              <input class="whero-cta-input" type="email" placeholder="Email address" aria-label="Email address">
              <WowButton type="submit" size="md" :arrow="true" class="btn-arrow">Join our Community</WowButton>
            </form>
          </div>
          <div class="col-12 col-lg-5">
            <div class="whero-stack">
              <div class="whero-panel">
                <div class="whero-browser-frame">
                  <div class="whero-browser-chrome">
                    <div class="whero-browser-dots"><span class="r"></span><span class="y"></span><span class="g"></span></div>
                    <div class="whero-browser-url"><div class="whero-url-pill">weofferwellness.co.uk • Today</div></div>
                  </div>
                  <div class="whero-browser-page">
                    <div class="whero-bar"><span class="me-auto">TODAY • Studio Calendar</span><span class="badge text-bg-light">Search</span></div>
                    <div class="whero-grid-viewport">
                      <div class="whero-grid">
                        <div class="card"><div class="small text-muted">Upcoming class</div><div class="mt-1">Yoga Flow — 14:00</div><div class="text-muted">12 / 18 spots</div><div class="spark"></div></div>
                        <div class="card"><div class="small text-muted">1‑to‑1 therapy</div><div class="mt-1">Deep Tissue Massage</div><div class="text-success">3 slots open</div><div class="spark"></div></div>
                        <div class="card"><div class="small text-muted">Bookings (week)</div><div class="mt-1">+32.1%</div><div class="spark"></div></div>
                        <div class="card"><div class="small text-muted">Pilates</div><div class="mt-1">Reformer — 16:30</div><div class="text-muted">4 / 10 spots</div><div class="spark"></div></div>
                        <div class="card"><div class="small text-muted">Sound Bath</div><div class="mt-1">Candlelight — 19:00</div><div class="text-muted">8 / 20 spots</div><div class="spark"></div></div>
                        <div class="card"><div class="small text-muted">Acupuncture</div><div class="mt-1">Initial consult</div><div class="text-success">Today • 17:45</div><div class="spark"></div></div>
                        <div class="card"><div class="small text-muted">Meditation</div><div class="mt-1">Guided — 12:30</div><div class="text-muted">Live online</div><div class="spark"></div></div>
                        <div class="card"><div class="small text-muted">Hot Yoga</div><div class="mt-1">90 mins — 18:15</div><div class="text-muted">Waitlist open</div><div class="spark"></div></div>
                        <div class="card"><div class="small text-muted">Physio</div><div class="mt-1">Follow‑up</div><div class="text-success">2 slots today</div><div class="spark"></div></div>
                        <div class="card"><div class="small text-muted">Reiki</div><div class="mt-1">Energy balance</div><div class="text-muted">Tomorrow • 11:00</div><div class="spark"></div></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div v-if="heroPromo" class="whero-book">
                <div class="whero-appicon"></div>
                <div class="text-center">{{ heroPromo.title }}</div>
                <div v-if="heroPromo.meta" class="text-center text-muted" style="margin-top:2px">{{ heroPromo.meta }}</div>
                <button class="btn btn-primary mt-3" type="button" @click="handleHeroPromoClick">
                  {{ heroPromo.ctaLabel || 'Book now' }}
                </button>
                <div v-if="heroPromo.subcopy" class="text-center text-muted mt-2" style="font-size:.9rem">{{ heroPromo.subcopy }}</div>
                <div class="whero-line mt-2"></div>
                <div class="whero-kv"><div class="whero-field"></div><div class="whero-field"></div></div>
                <div class="whero-kv"><div class="whero-field"></div><div class="whero-field"></div></div>
                <div class="whero-line"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section v-if="showMindfulTimesRibbon" class="mindful-times-ribbon" aria-label="Mindful Times update">
      <div class="container">
        <div class="ribbon-inner">
          <span class="ribbon-label">Mindful Times</span>
          <span>New stories, guides and practitioner advice every week.</span>
          <div class="ribbon-links">
            <a :href="mindfulTimesUrl" class="link-wow">Read the latest</a>
            <span aria-hidden="true">•</span>
            <a :href="podcastUrl" class="link-wow">Listen to the Podcast</a>
          </div>
        </div>
      </div>
    </section>

    <!-- Inline Search under hero -->
    <section class="py-4">
      <div class="container">
        <UltraSearchBar id-prefix="home-template" />
      </div>
    </section>

    <section v-if="homeLoadError" class="py-2">
      <div class="container">
        <div class="card p-4 text-ink-700" style="background:#f8fafc;">
          Some sections are still loading. Refresh the page or explore another category while we fetch the latest offerings.
        </div>
      </div>
    </section>

    <!-- Today’s class schedule / booking rail -->
    <ClassSchedule
      :products="classesAll"
      title="Book Your Class"
      eyebrow="Classes"
      subtitle="3 simple steps to book a gentle gong bath, Yoga Nidra and more…"
    />

    <!-- Painpoint tiles (keep near top) -->
    <PainpointTiles v-if="painpoints.length" :items="painpoints" />


    
    <ProductCarousel
      title="Gifts that glow (under £50)"
      subtitle="Thoughtful ways to nourish someone you love"
      :products="giftsUnder50"
      cta-label="Find a thoughtful gift"
      cta-href="/search?tag=Gift&price_max=50"
    />

    <!-- Comfort of your own home (Tabbed) -->
    <section class="section" aria-labelledby="comfort-title">
      <div class="container-page">
        <div class="mb-6">
          <div class="kicker">No travel needed</div>
          <h2 id="comfort-title">From the comfort of your own home</h2>
          <p class="text-ink-600 mt-2 max-w-2xl">
            When your mind won’t slow down, soften into rituals that meet you where you are — gentle sessions that bring calm, clarity and care right to your space.
          </p>
        </div>

        <div class="flex items-center justify-between gap-4 mb-4 flex-wrap">
          <div class="flex items-center gap-4 flex-wrap">
            <div class="flex items-center gap-2">
              <span class="font-semibold text-ink-800">Under</span>
              <div class="seg-group" role="tablist" aria-label="Under price">
                <button class="seg" :class="{ active: comfortPriceMax===50 }" @click="comfortPriceMax=50" role="tab" :aria-selected="comfortPriceMax===50">£50</button>
                <button class="seg" :class="{ active: comfortPriceMax===100 }" @click="comfortPriceMax=100" role="tab" :aria-selected="comfortPriceMax===100">£100</button>
                <button class="seg" :class="{ active: comfortPriceMax===500 }" @click="comfortPriceMax=500" role="tab" :aria-selected="comfortPriceMax===500">£500</button>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <span class="font-semibold text-ink-800">For</span>
              <div class="seg-group" role="tablist" aria-label="For">
                <button class="seg" :class="{ active: comfortPeople==='solo' }" @click="comfortPeople='solo'" role="tab" :aria-selected="comfortPeople==='solo'">Solo</button>
                <button class="seg" :class="{ active: comfortPeople==='couple' }" @click="comfortPeople='couple'" role="tab" :aria-selected="comfortPeople==='couple'">Couple</button>
              </div>
            </div>
          </div>
          <div class="hidden sm:flex items-center gap-2 ml-auto">
            <button class="carousel-arrow" @click="comfortScrollBy(-1)" aria-label="Previous">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <button class="carousel-arrow" @click="comfortScrollBy(1)" aria-label="Next">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
            </button>
          </div>
        </div>

        <div>
          <div ref="comfortRef" class="flex gap-6 overflow-x-auto overflow-y-visible no-scrollbar snap-x snap-mandatory pt-2 pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 bg-transparent">
            <ProductCard v-for="p in comfortFiltered" :key="p.id" :product="p" class="snap-start" />
          </div>
          <div class="mt-4 text-right">
            <a :href="`/search?price_max=${comfortPriceMax}&format=online${comfortPeople==='couple'?'&q=couples':''}`" class="btn-wow btn-wow--outline btn-sm btn-arrow">
              <span class="btn-label">See all under £{{ comfortPriceMax }} ({{ comfortPeople }})</span>
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

    <!-- Featured categories grid (optional) can go here if desired -->

    <!-- Trust & credibility -->
    <section class="section">
      <div class="container-page">
        <div class="card p-6 md:p-8">
          <div class="grid md:grid-cols-3 gap-6 items-center">
            <div class="space-y-2">
              <div class="kicker">Feel safe to try</div>
              <h3>You’re in safe hands</h3>
              <p class="text-ink-600">Real outcomes, real people. Verified reviews and clear pricing — so you can relax into booking and focus on how you want to feel.</p>
            </div>
            <div class="stat-row">
              <div class="stat-pill">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="icon star" aria-hidden="true" fill="currentColor"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                <div>
                  <div class="title">{{ fmtAvg(reviewStats.avg_rating) }}</div>
                  <div class="sub">{{ fmtCount(reviewStats.verified_count || reviewStats.review_count) }} verified reviews</div>
                </div>
              </div>
              <div class="stat-pill">
                <span class="dot" aria-hidden="true"></span>
                <div>
                  <div class="title">97% would book again</div>
                  <div class="sub">People felt better after their session</div>
                </div>
              </div>
            </div>
            <div class="text-right">
              <a :href="reviewsHref" class="btn-wow btn-wow--outline btn-sm">See reviews</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section" aria-labelledby="values-title">
      <div class="container-page">
        <div class="grid md:grid-cols-2 gap-8">
          <div>
            <div class="kicker">Our approach</div>
            <h2 id="values-title">Holistic therapies, grounded in care</h2>
            <p class="text-ink-600 mt-3">We champion modalities that meet you where you are, taught and held by practitioners who prioritise nervous-system safety.</p>
          </div>
          <dl class="space-y-5">
            <div v-for="point in values" :key="point.title">
              <dt class="font-semibold text-ink-900">{{ point.title }}</dt>
              <dd class="text-ink-600 mt-1">{{ point.copy }}</dd>
            </div>
          </dl>
        </div>
      </div>
    </section>

    <section v-if="testimonials.length" class="section" aria-labelledby="testimonials-title">
      <div class="container-page">
        <div class="mb-6">
          <div class="kicker">Testimonials</div>
          <h2 id="testimonials-title">Clients who feel the difference</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-4">
          <article v-for="review in testimonials" :key="review.id || review.customer" class="card p-5 h-100 flex flex-col">
            <div v-if="review.rating" class="testimonial-stars" :aria-label="`Rated ${review.rating} out of 5`">
              {{ '★'.repeat(Math.min(5, Math.max(1, review.rating))) }}
            </div>
            <p class="text-lg text-ink-900 flex-1">“{{ review.quote }}”</p>
            <p class="mt-4 text-sm text-ink-500">
              {{ review.customer || 'Verified client' }}
              <span v-if="review.product"> · {{ review.product }}</span>
            </p>
          </article>
        </div>
      </div>
    </section>

    

    <!-- Quick booking: What’s on -->
    <section v-if="showWhatsOnSection" id="whats-on" class="section">
      <div class="container-page">
        <div class="mb-6 flex items-end justify-between">
          <div>
            <div class="kicker">Book now</div>
            <h2>What’s on this week</h2>
          </div>
          <div class="seg-group" role="tablist" aria-label="Mode">
            <button class="seg" :class="{ active: filterMode==='all' }" @click="filterMode='all'" role="tab" :aria-selected="filterMode==='all'">All</button>
            <button class="seg" :class="{ active: filterMode==='online' }" @click="filterMode='online'" role="tab" :aria-selected="filterMode==='online'">Online</button>
            <button class="seg" :class="{ active: filterMode==='in-person' }" @click="filterMode='in-person'" role="tab" :aria-selected="filterMode==='in-person'">In‑studio</button>
          </div>
        </div>
        <div v-if="weekItems.length" class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
          <a v-for="it in weekItems" :key="it.id" class="card p-4 flex flex-col hover:shadow-glow transition-shadow" :href="it.url">
            <div class="text-sm text-ink-500">{{ it.when }}</div>
            <div class="mt-1 text-ink-900 font-medium">{{ it.title }}</div>
            <div class="mt-3 flex items-center justify-between">
              <span class="chip">{{ it.mode || 'In‑studio' }}</span>
              <span v-if="it.price" class="chip chip-brand">{{ new Intl.NumberFormat(undefined, { style:'currency', currency: it.currency || 'GBP' }).format(Number(it.price_min || it.price)) }}</span>
            </div>
          </a>
        </div>
        <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div class="card p-4">
            <div class="kicker mb-2">Classes</div>
            <div class="text-ink-700">No classes this week.</div>
            <a href="/classes" class="btn-wow btn-wow--outline btn-sm btn-arrow mt-3">
              <span class="btn-label">See all classes</span>
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
          <div class="card p-4">
            <div class="kicker mb-2">Workshops</div>
            <div class="text-ink-700">No workshops this week.</div>
            <a href="/workshops" class="btn-wow btn-wow--outline btn-sm btn-arrow mt-3">
              <span class="btn-label">See workshops</span>
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
          <div class="card p-4">
            <div class="kicker mb-2">Retreats</div>
            <div class="text-ink-700">No retreats this week.</div>
            <a href="/retreats" class="btn-wow btn-wow--outline btn-sm btn-arrow mt-3">
              <span class="btn-label">Browse retreats</span>
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

    <!-- Email capture / quiz -->
    <ResetStarterCTA :guide-href="sevenDayGuideHref" @open-quiz="quizOpen = true" />
    <WellnessQuiz :open="quizOpen" @close="quizOpen=false" />

    <!-- Partner spotlight -->
    <section id="partners" class="section">
      <div class="container-page">
        <div class="grid md:grid-cols-2 gap-6 items-center">
          <div class="card p-6 md:p-8">
            <div class="kicker mb-2">Affiliate / referral</div>
            <h3>Partner spotlight</h3>
            <p class="text-ink-600 mt-2">Arriving from a partner? You’re in the right place. Redeem your voucher and book a session today.</p>
            <div class="mt-5 flex gap-3">
              <SquareButton as="a" href="#" size="sm">Redeem voucher</SquareButton>
              <SquareButton as="a" href="#whats-on" variant="ghost" size="sm">Book now</SquareButton>
            </div>
          </div>
          <div class="card p-6 md:p-8">
            <div class="kicker mb-2">Corporate gateway</div>
            <h3>Wellness for teams</h3>
            <p class="text-ink-600 mt-2">HR and team leads — build a science‑backed wellness program with measurable outcomes.</p>
            <div class="mt-5 flex gap-3">
              <SquareButton as="a" href="#corporate" variant="outline" size="sm">Explore corporate</SquareButton>
              <SquareButton as="a" href="#" variant="ghost" size="sm">Talk to us</SquareButton>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="practitioner-chats" class="section" aria-labelledby="practitioner-chats-title">
      <div class="container-page">
        <div class="card p-6 md:p-10 flex flex-col md:flex-row items-center gap-6">
          <div class="flex-1">
            <div class="kicker">New series</div>
            <h2 id="practitioner-chats-title">Practitioner Chats</h2>
            <p class="text-ink-600 mt-2">Monthly conversations with WOW practitioners on how they hold space, approach safety, and design therapies that work.</p>
          </div>
          <div class="flex gap-3">
            <a :href="practitionerChatsUrl" class="btn-wow btn-wow--cta btn-arrow">
              <span class="btn-label">Explore chats</span>
            </a>
            <a :href="mindfulTimesUrl" class="btn-wow btn-wow--ghost">See notes</a>
          </div>
        </div>
      </div>
    </section>

    <!-- Discover sections (moved much lower on the page) -->
    <template v-if="selectedPain">
      <FeatureBand v-if="abVariant==='A'" :painpoint="selectedPain" />
      <RecommendationGrid :painpoint="selectedPain" />
      <FeatureBand v-if="abVariant==='B'" :painpoint="selectedPain" />
    </template>

    <!-- Journal highlights -->
    <section id="mindful-times" class="section">
      <div class="container-page">
          <div class="mb-8 flex items-end justify-between">
            <div>
              <div class="kicker">Mindful Times</div>
              <h2>Guides, practitioner interviews and tools to help you feel better</h2>
            </div>
            <a class="btn-wow btn-wow--outline btn-sm btn-arrow" :href="mindfulTimesUrl">
              <span class="btn-label">Visit Mindful Times</span>
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
        <div
          class="mindful-layout"
          :class="{ 'mindful-layout--single': secondaryArticles.length === 0 }"
          v-if="featuredArticle || secondaryArticles.length > 0"
        >
          <article class="mindful-feature mindful-feature--embed">
            <div class="mindful-embed">
              <iframe
                :src="podcastEmbedSrc"
                width="100%"
                height="200"
                frameborder="0"
                allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                loading="lazy"
                title="Seeking Wellness podcast"
              ></iframe>
            </div>
            <div v-if="featuredArticle" class="mindful-feature-latest">
              <a :href="featuredArticle.href || '#'" class="mindful-feature-latest-link">
                <div class="mindful-feature-latest-media" v-if="featuredArticle.img">
                  <img :src="featuredArticle.img" alt="" />
                </div>
                <div class="mindful-feature-latest-body">
                  <div class="chip">{{ featuredArticle.tag || 'MindfulTimes' }}</div>
                  <h3>{{ featuredArticle.title }}</h3>
                  <p v-if="featuredArticle.excerpt">{{ featuredArticle.excerpt }}</p>
                  <span class="mindful-feature-latest-cta">Read article</span>
                </div>
              </a>
            </div>
          </article>
          <div class="mindful-stack" v-if="secondaryArticles.length > 0">
            <article v-for="p in secondaryArticles" :key="p.id" class="mindful-card">
              <a :href="p.href || '#'" class="mindful-card-link">
                <div class="mindful-card-media">
                  <img v-if="p.img" :src="p.img" alt="" />
                </div>
                <div class="mindful-card-body">
                  <div class="chip">{{ p.tag || 'MindfulTimes' }}</div>
                  <h4>{{ p.title }}</h4>
                </div>
              </a>
            </article>
          </div>
        </div>
      </div>
    </section>
    

    <!-- Shop by Category -->
    <section class="section">
      <div class="container-page">
        <div class="mb-8">
          <div class="kicker">Discover</div>
          <h2>Shop by category</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <a
            v-for="cat in shopCategories"
            :key="cat.title"
            class="relative overflow-hidden rounded-2xl border border-ink-200 bg-white shadow-card"
            :href="cat.href"
          >
            <img :src="cat.img" class="h-48 w-full object-cover" :alt="cat.title" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
            <div class="absolute bottom-3 left-3 text-white text-lg font-semibold">{{ cat.title }}</div>
          </a>
        </div>
      </div>
    </section>

    <!-- Gift Card CTA -->
    <section class="section">
      <div class="container-page">
        <div class="card p-6 md:p-10 flex flex-col md:flex-row items-center gap-8">
          <div class="flex-1">
            <div class="kicker mb-3">Gifting made easy</div>
            <h3>Gift cards for any occasion</h3>
            <p class="text-ink-600 mt-2 max-w-xl">Choose the amount, add a message, and we’ll email an instant e-gift card to you or your recipient to use on therapies, classes, events and workshops.</p>
            <div class="mt-5 flex gap-3">
              <a href="/gift-cards" class="btn-wow is-square btn-md btn-wow--cta">Send a gift card</a>
              <a href="/help/gift-cards" class="btn-wow is-square btn-md btn-wow--outline">How gifting works</a>
            </div>
          </div>
          <div class="flex-1 w-full">
            <div class="rounded-2xl overflow-hidden border border-ink-200">
              <img src="https://images.unsplash.com/photo-1512428559087-560fa5ceab42?q=80&w=1200&auto=format&fit=crop" alt="Gift Card" class="w-full h-64 object-cover"/>
            </div>
          </div>
        </div>
      </div>
    </section>
  </SiteLayout>
</template>

<style scoped>
/* Scoped hero CSS with unique class names (whero-*) to avoid conflicts */
.whero { position: relative; isolation: isolate; overflow: clip; }
.whero-pad { padding-top: clamp(72px, 8vw, 120px); padding-bottom: clamp(54px, 6vw, 96px); min-height: clamp(560px, 5vh, 820px); }
.whero-eyebrow { display:inline-flex; align-items:center; gap:.5rem; font-size:.7rem; letter-spacing:.12em; margin-bottom:5px; text-transform:uppercase; background:rgba(0,0,0,.6); color:#fff; border-radius:999px; padding:4px 12px; }
.whero-title { font-weight: 800; line-height: 1.02; letter-spacing: -.01em; font-size: clamp(3.6rem, 4vw + 1rem, 4.6rem); margin-top:.6rem; color:#0b1323; }
/* Support inverted colour logic so the moving background shows through */
@supports (mix-blend-mode:difference) {
  .whero-title { color:#fff; mix-blend-mode:difference; }
}

.whero-sub { color: #000; font-size: clamp(1rem, .4vw + .95rem, 1.125rem); max-width: 44rem; mix-blend-mode: normal; }
.whero-subline { display:block; margin-top:1rem; max-width:36rem; }

.mindful-times-ribbon { padding:12px 0; background:linear-gradient(90deg,#ecfdf5,#fefce8); border-bottom:1px solid #e2e8f0; border-top:1px solid #e2e8f0; }
.mindful-times-ribbon .container { max-width:960px; }
.ribbon-inner { display:flex; flex-wrap:wrap; gap:.75rem .5rem; align-items:center; justify-content:space-between; font-size:.95rem; }
.ribbon-label { font-weight:600; color:#065f46; padding:4px 12px; border-radius:999px; background:#d1fae5; text-transform:uppercase; letter-spacing:.05em; font-size:.75rem; }
.ribbon-links { display:flex; gap:.5rem; align-items:center; font-weight:600; }
.ribbon-links .link-wow { font-size:.95rem; }

.mindful-layout{ display:grid; grid-template-columns: minmax(0, 1.2fr) minmax(0, .8fr); gap:1.5rem; }
.mindful-layout--single{ grid-template-columns:1fr; }
.mindful-feature{ position:relative; border-radius:24px; overflow:hidden; }
.mindful-feature-link{ display:flex; flex-direction:column; height:100%; color:inherit; text-decoration:none; }
.mindful-feature-media{ position:relative; padding-top:56%; overflow:hidden; }
.mindful-feature-media img{ position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
.mindful-feature-copy{ padding:1.75rem; background:#843e04; color:#fff; flex:1; display:flex; flex-direction:column; gap:1rem; }
.mindful-feature-copy h3{ font-size:2rem; line-height:1.2; margin:0; color:#fff !important; }
.mindful-feature-copy p{ margin:0; color:#dbeafe; }
.mindful-feature .chip{ background:rgba(255,255,255,.15); color:#fff; }
.mindful-feature .read-more{ font-weight:600; }
.mindful-feature--embed{ display:flex; flex-direction:column; }
.mindful-embed{ border-radius:20px 20px 0 0; overflow:hidden; background:transparent; margin-bottom:0; }
.mindful-feature--embed .mindful-feature-copy{ border-radius:0 0 20px 20px; }
.mindful-embed iframe{ width:100%; display:block; border:0; }
.mindful-feature-latest{ border:1px solid var(--ink-200); border-top:0; border-radius:0 0 20px 20px; background:#fff; }
.mindful-feature-latest-link{ display:flex; gap:1.25rem; padding:1.5rem; color:inherit; text-decoration:none; align-items:stretch; }
.mindful-feature-latest-media{ width:150px; border-radius:16px; overflow:hidden; background:#f1f5f9; flex-shrink:0; aspect-ratio:4/3; }
.mindful-feature-latest-media img{ width:100%; height:100%; object-fit:cover; display:block; }
.mindful-feature-latest-body{ display:flex; flex-direction:column; gap:.5rem; }
.mindful-feature-latest-body h3{ margin:0; font-size:1.35rem; line-height:1.3; color:var(--ink-900); }
.mindful-feature-latest-body p{ margin:0; color:var(--ink-600); font-size:.95rem; }
.mindful-feature-latest-cta{ margin-top:auto; font-weight:600; color:var(--brand-700); text-decoration:underline; text-decoration-thickness:2px; text-underline-offset:4px; }

.mindful-stack{ display:grid; gap:1rem; }
.mindful-card{ border:1px solid var(--ink-200); border-radius:18px; background:#fff; overflow:hidden; }
.mindful-card-link{ display:flex; gap:1rem; padding:1rem; color:inherit; text-decoration:none; }
.mindful-card-media{ flex-shrink:0; width:120px; height:90px; border-radius:12px; overflow:hidden; background:#f8fafc; }
.mindful-card-media img{ width:100%; height:100%; object-fit:cover; }
.mindful-card-body{ flex:1; display:flex; flex-direction:column; gap:.35rem; }
.mindful-card-body h4{ margin:0; font-size:1rem; line-height:1.3; }
.mindful-card-body p{ margin:0; color:#475569; font-size:.9rem; }

.podcast-cta{ display:flex; flex-wrap:wrap; gap:1rem; }

.testimonial-stars{ color:#f59e0b; font-size:.9rem; letter-spacing:.1em; margin-bottom:.75rem; }

@media (max-width: 991.98px){
  .mindful-layout{ grid-template-columns:1fr; }
  .mindful-card-link{ flex-direction:column; }
  .mindful-card-media{ width:100%; height:160px; }
  .mindful-feature-latest-link{ flex-direction:column; }
  .mindful-feature-latest-media{ width:100%; height:200px; }
}

.whero-radial { position:absolute; inset:0; z-index:-1; display:grid; place-items:center; overflow:hidden; perspective:420px; background-image: radial-gradient(circle at 18% -12%, #dbeafe, #ffffff00 22em), conic-gradient(#7bf, #7fb, #b7f, #bf7, #f7b, #fb7, #7bf); opacity:.9; }
.whero-radial::after, .whero-radial::before{ content:""; position:absolute; inset:-100%; background-image: repeating-conic-gradient(#ffffff20 0 10deg, #0000 0 20deg, #ffffff20 0 40deg, #0000 0 55deg); animation: ray 30s linear infinite; mix-blend-mode: overlay; pointer-events:none; }
.whero-radial::before{ animation-duration: 60s; animation-direction: reverse; }
@keyframes ray { to { rotate: 1turn } }

.whero-stack{ position:relative; min-height:480px; perspective:1200px }
.whero-panel{ position:absolute; right:-30px; top:225px; width:min(660px, 100%); background:transparent; border:0; padding:0; transform: translateX(0) translateY(0); transition: transform .35s cubic-bezier(.2,.75,.2,1), z-index .01s; }
.whero-browser-frame{ border-radius:16px; overflow:hidden; background: rgba(255,255,255,.28); backdrop-filter: saturate(160%) blur(10px); -webkit-backdrop-filter:saturate(160%) blur(10px); border:1px solid rgba(255,255,255,.45); box-shadow:0 18px 48px rgba(2,8,23,.12) }
.whero-browser-chrome{ height:46px; display:flex; align-items:center; gap:10px; padding:0 10px; background: linear-gradient(180deg, rgba(255,255,255,.65), rgba(255,255,255,.38)); border-bottom:1px solid rgba(255,255,255,.35) }
.whero-browser-dots{ display:flex; gap:6px }
.whero-browser-dots span{ width:10px; height:10px; border-radius:50%; box-shadow: 0 0 0 1px rgba(0,0,0,.05) inset }
.whero-browser-dots .r{ background:#ff5f57 } .whero-browser-dots .y{ background:#febc2e } .whero-browser-dots .g{ background:#28c840 }
.whero-browser-url{ flex:1; display:flex; justify-content:center }
.whero-url-pill{ min-width:55%; max-width:76%; height:30px; display:flex; align-items:center; justify-content:center; gap:8px; padding:0 12px; border-radius:999px; background:rgba(255,255,255,.92); border:1px solid #e8eef5; box-shadow: inset 0 0 0 1px #eef3f7; font-size:13px; color:#475569; white-space:nowrap; overflow:hidden; text-overflow:ellipsis }
.whero-browser-page{ background:#fff; border-top:1px solid #eef2f7; padding:14px }
.whero-bar{ height:38px; background:#f8fafc; border:1px solid var(--panel-border, #e5e7eb); border-radius:10px; display:flex; align-items:center; padding:0 12px; gap:10px; color:#334155; font-weight:600 }
.whero-grid-viewport{ overflow:hidden; margin-top:14px }
.whero-panel .whero-grid{ display:flex; gap:14px; min-width:max-content; will-change: transform; animation: driftFull var(--drift-time, 18s) cubic-bezier(.25, .01, .25, 1) infinite; }
.whero-panel:hover .whero-grid{ animation-play-state: paused }
@keyframes driftFull {
  0%   { transform: translateX(0) }
  6%   { transform: translateX(0) }
  50%  { transform: translateX(calc(-1 * var(--scrollDist, 0px))) }
  56%  { transform: translateX(calc(-1 * var(--scrollDist, 0px))) }
  100% { transform: translateX(0) }
}
@media (prefers-reduced-motion: reduce) {
  .whero-panel .whero-grid { animation: none }
}
.whero-panel .card{ background:#fff; border:1px solid var(--panel-border, #e5e7eb); border-radius:14px; padding:12px; min-height:140px; width:220px; flex:0 0 220px }
.whero-spark{ height:88px; background: linear-gradient(180deg, #e2e8f0 0 1px, transparent 0) center/100% 22px repeat-y, linear-gradient(90deg, #94a3b81a, #94a3b845 50%, #94a3b81a); border-radius:8px; margin-top:10px }

.whero-book{ position:absolute; right:12%; top:-18px; width:320px; max-width:40vw; background:#fff; border:1px solid var(--panel-border, #e5e7eb); border-radius:28px; box-shadow:0 24px 60px rgba(2,8,23,.18); padding:18px 14px 16px; transform: translateX(0) translateY(0); transition: transform .35s cubic-bezier(.2,.75,.2,1), z-index .01s; }
.whero-panel .btn-primary, .whero-book .btn-primary { width: 100%; border-radius: 10px; }
.whero-stack:hover .whero-panel{ transform: translateX(-12px) translateY(3px) }
.whero-stack:hover .whero-book{ transform: translateX(16px) translateY(-3px) }
.whero-appicon{ width:42px; height:42px; border-radius:10px; background: linear-gradient(135deg, #7dd3fc, #f472b6, #fde047); margin:0 auto 10px }
.whero-line{ height:14px; background:#f1f5f9; border-radius:8px; margin-top:10px }
.whero-kv{ display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:10px }
.whero-field{ background:#f8fafc; border:1px solid var(--panel-border, #e5e7eb); border-radius:10px; height:38px }

.whero-cta{ height:50px; display:flex; align-items:center; gap:8px; padding:8px 8px 8px 20px; max-width:406px; background:#f7fafc; border:1px solid #e3eaf2; border-radius:999px; box-shadow: 0 1px 0 #fdfefe inset, 0 0 0 1px #eef3f7; }
.whero-cta-input{ appearance:none; border:0; background:transparent; outline:0; flex:1; min-width:0; font-size:15px; line-height:1; color:#344256; padding:0 }
.whero-cta-input::placeholder{ color:#344256; opacity:.9 }
/* Old hero subscribe button styles removed (replaced by WowButton) */

@media (max-width: 991.98px) { .whero-stack { min-height: 560px; margin-top: 24px } .whero-panel { position: relative; top: auto; right: auto; width: 100% } .whero-book { position: absolute; right: 6%; top: -22px } }
@media (max-width: 575.98px) { .whero-book { right: 2%; top: -16px; width: 88% } }

/* Segmented tabs (copied from listings) */
.seg-group{ display:inline-flex; background:#f8fafc; border:1px solid var(--ink-200); border-radius:999px; padding:2px }
.seg{ appearance:none; border:0; background:transparent; padding:6px 12px; border-radius:999px; color: var(--ink-700); font-weight:600; font-size:.9rem; transition: all .15s ease; }
.seg:hover{ background:#eef2f7 }
.seg.active{ background: linear-gradient(180deg, #549483, #3b7768); color:#fff; box-shadow: 0 1px 0 rgba(255,255,255,.4) inset }
</style>
