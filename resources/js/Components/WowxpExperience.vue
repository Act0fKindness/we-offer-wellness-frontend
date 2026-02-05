<script setup>
import { onMounted, onBeforeUnmount, ref, computed, nextTick } from 'vue'
import ReviewList from '@/Components/ReviewList.vue'
import WowButton from '@/Components/ui/WowButton.vue'

const props = defineProps({
  product: { type: Object, required: true },
  type: { type: String, default: '' },
})

const typeLabel = computed(() => {
  const map = {
    therapy: 'Therapy',
    therapies: 'Therapy',
    class: 'Class',
    classes: 'Class',
    event: 'Event',
    events: 'Event',
    workshop: 'Workshop',
    workshops: 'Workshop',
    retreat: 'Retreat',
    retreats: 'Retreat',
  }
  const raw = String(props.type || props.product?.type || '').toLowerCase()
  return map[raw] || 'Session'
})

const heroCtaLabel = computed(() => {
  const raw = typeLabel.value
  if (raw === 'Therapy') return 'Book therapy'
  if (raw === 'Class') return 'Book your class'
  if (raw === 'Event' || raw === 'Workshop' || raw === 'Retreat') return 'Book your place'
  return 'Book now'
})

function formatDurationLabel(value) {
  if (!value) return null
  if (typeof value === 'number') {
    if (value >= 60 && value % 60 === 0) return `${value / 60} hr`
    return `${value} min`
  }
  const str = String(value)
  if (/hour|hr/i.test(str)) return str
  const num = parseInt(str.replace(/[^0-9]/g, ''), 10)
  if (!isNaN(num)) {
    return num >= 60 && num % 60 === 0 ? `${num / 60} hr` : `${num} min`
  }
  return str
}

const displayDuration = computed(() => formatDurationLabel(props.product?.duration))

const displayFormat = computed(() => {
  const mode = String(props.product?.mode || '').toLowerCase()
  if (mode.includes('online') && mode.includes('person')) return 'Online or in-person'
  if (mode.includes('online')) return 'Online'
  if (mode.includes('person')) return 'In-person'
  const locations = Array.isArray(props.product?.locations) ? props.product.locations.map(l => String(l || '').toLowerCase()) : []
  if (locations.length) {
    const hasOnline = locations.includes('online')
    const hasPhysical = locations.some(l => l !== 'online')
    if (hasOnline && hasPhysical) return 'Online or in-person'
    if (hasOnline) return 'Online'
    if (hasPhysical) return 'In-person'
  }
  return null
})

const displayLocation = computed(() => {
  if (props.product?.location) return props.product.location
  const locs = Array.isArray(props.product?.locations) ? props.product.locations : []
  if (locs.length) return locs[0]
  if ((displayFormat.value || '').includes('Online')) return 'Online'
  return null
})

function formatDateRange(product) {
  try {
    if (product.start_date && product.end_date) {
      const s = new Date(product.start_date)
      const e = new Date(product.end_date)
      const sameYear = s.getFullYear() === e.getFullYear()
      const sameMonth = sameYear && s.getMonth() === e.getMonth()
      const startStr = s.toLocaleDateString(undefined, { month: 'short', day: 'numeric' })
      const endStr = e.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: sameMonth ? undefined : 'numeric' })
      return `${startStr} – ${endStr}`
    }
    if (product.date) {
      const d = new Date(product.date)
      if (!isNaN(d.getTime())) {
        return d.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' })
      }
    }
  } catch {}
  return null
}

const nextAvailability = computed(() => formatDateRange(props.product) || null)

function plainText(input) {
  if (!input) return ''
  try {
    const wrapper = document.createElement('div')
    wrapper.innerHTML = sanitizeHtml(input)
    return wrapper.textContent?.replace(/\s+/g, ' ').trim() || ''
  } catch {
    return String(input).replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim()
  }
}

const heroSummary = computed(() => props.product?.summary || plainText(props.product?.description || props.product?.body_html || ''))

const heroTags = computed(() => {
  const benefits = Array.isArray(props.product?.benefits) ? props.product.benefits : []
  const tags = benefits.length ? benefits : (Array.isArray(props.product?.tags) ? props.product.tags : [])
  return tags.filter(Boolean).slice(0, 4)
})

const infoHighlights = computed(() => {
  const rows = [
    { label: 'Duration', value: displayDuration.value },
    { label: 'Format', value: displayFormat.value },
    { label: 'Location', value: displayLocation.value },
    { label: 'Next availability', value: nextAvailability.value },
  ]
  return rows.filter(row => !!row.value)
})

const benefitsList = computed(() => {
  const list = Array.isArray(props.product?.benefits) && props.product.benefits.length
    ? props.product.benefits
    : heroTags.value
  return list.filter(Boolean)
})

const whoForList = computed(() => (Array.isArray(props.product?.who_for) ? props.product.who_for : []).filter(Boolean))
const whoNotList = computed(() => {
  const list = Array.isArray(props.product?.who_not_for) ? props.product.who_not_for : []
  if (list.length) return list.filter(Boolean)
  const contra = String(props.product?.contraindications || '').trim()
  return contra ? [contra] : []
})

const expectHtml = computed(() => sanitizeHtml(props.product?.what_to_expect || ''))
const aftercareHtml = computed(() => sanitizeHtml(props.product?.aftercare || ''))

const faqList = computed(() => {
  const raw = Array.isArray(props.product?.faq) ? props.product.faq : []
  return raw
    .map((item) => {
      if (typeof item === 'string') return { question: item, answer: '' }
      if (item && typeof item === 'object') {
        const question = item.question || item.q || ''
        const answerRaw = item.answer || item.a || ''
        return { question, answer: sanitizeHtml(answerRaw) }
      }
      return null
    })
    .filter((item) => item && (item.question || item.answer))
})

const practitioner = computed(() => props.product?.practitioner || null)
const practitionerProfileUrl = computed(() => practitioner.value?.profile_url || '/providers')

const safetyNotes = computed(() => String(props.product?.safety_notes || '').trim())
const contraindications = computed(() => String(props.product?.contraindications || '').trim())
const standardSafetyLine = 'Always consult your GP or healthcare professional before starting any new therapy, especially if you are pregnant, have a diagnosed condition or are taking prescribed medication.'
const safetyLink = '/safety-and-contraindications'

const rootEl = ref(null)
const tabsBar = ref(null)
const aboutExpanded = ref(false)
const activeTab = ref('#overview')

// Images
const images = computed(() => {
  const p = props.product || {}
  const arr = Array.isArray(p.images) && p.images.length ? p.images : (p.image ? [p.image] : [])
  return arr.filter(Boolean)
})
const imageGroups = computed(() => {
  const arr = images.value
  const out = []
  for (let i = 0; i < arr.length; i += 3) out.push(arr.slice(i, i+3))
  return out
})

// Desktop carousel state (allow sliding when more than 3 images)
const galleryStripEl = ref(null)
const currentSet = ref(0)
const isDesktop = ref(false)
const canScroll = ref(false)
const useCarousel = computed(() => isDesktop.value && images.value.length > 3)
function detectDesktop(){
  try { isDesktop.value = window.matchMedia('(min-width: 992px)').matches } catch { isDesktop.value = false }
}
function updateScrollState(){
  try {
    const el = galleryStripEl.value
    const sets = el ? el.querySelectorAll('.wowxp-set') : null
    canScroll.value = !!(sets && sets.length > 1)
  } catch { canScroll.value = false }
}
function slide(dir){
  try{
    const el = galleryStripEl.value; if (!el) return
    const sets = el.querySelectorAll('.wowxp-set'); if (!sets || !sets.length) return
    const next = Math.max(0, Math.min(sets.length - 1, currentSet.value + (dir < 0 ? -1 : 1)))
    const target = sets[next]
    el.scrollTo({ left: target.offsetLeft, behavior: 'smooth' })
    currentSet.value = next
  } catch {}
}
function syncCurrentSet(){
  try{
    const el = galleryStripEl.value; if (!el) return
    const sets = el.querySelectorAll('.wowxp-set'); if (!sets || !sets.length) return
    const x = el.scrollLeft + el.clientWidth/2
    let best = 0; let bestDist = Infinity
    sets.forEach((s, i) => { const d = Math.abs((s.offsetLeft + s.clientWidth/2) - x); if (d < bestDist){ best=i; bestDist=d } })
    currentSet.value = best
  } catch {}
}

// Locations
const locations = computed(() => {
  const locs = Array.isArray(props.product?.locations) ? props.product.locations : []
  const toEmbed = (label) => {
    const q = encodeURIComponent(String(label || '').trim())
    return `https://www.google.com/maps?q=${q}&output=embed`
  }
  const mapped = locs.map(l => {
    const label = String(l)
    const isOnline = /online/i.test(label)
    return { type: isOnline ? 'online' : 'map', label, embed: toEmbed(label) }
  })
  // Only include an explicit Online row when the product actually supports Online
  const hasOnlineExplicit = mapped.some(m => m.type === 'online')
  const hasOnlineViaMode = String(props.product?.mode || '').toLowerCase() === 'online'
  const hasOnlineViaOpts = Array.isArray(props.product?.options)
    && props.product.options.some(o => /location/i.test(String(o?.name || o?.meta_name || ''))
      && Array.isArray(o?.values) && o.values.some(v => /online/i.test(String(v))))
  const supportsOnline = hasOnlineExplicit || hasOnlineViaMode || hasOnlineViaOpts
  if (!hasOnlineExplicit && supportsOnline) mapped.push({ type: 'online', label: 'Online (Live)', embed: '' })
  return mapped
})
const activeLocIdx = ref(0)
const showPane = ref('map') // 'map' | 'online'

  async function selectLoc(i){
  const item = locations.value[i]
  if (!item) return
  activeLocIdx.value = i
  showPane.value = item.type === 'online' ? 'online' : 'map'
  // Inform buy box to switch variant Format accordingly
  try {
    const format = (item.type === 'online') ? 'Online' : 'In-person'
    window.dispatchEvent(new CustomEvent('wow:select-format', { detail: { format } }))
    window.dispatchEvent(new CustomEvent('wow:select-location', { detail: { index: i, label: item.label } }))
    // Also broadcast the picked location label for cart/meta tracking
    window.dispatchEvent(new CustomEvent('wow:location-picked', { detail: { label: item.label } }))
  } catch {}
  // update iframe src lazily
  try {
    if (item.type === 'map') {
      const frame = rootEl.value?.querySelector('#wowxp-map')
      if (frame && frame.src !== item.embed) frame.src = item.embed
    }
  } catch {}
  // Wait for DOM to reflect the new active item before positioning the bar
  await nextTick()
  positionLocBar()
}

// ----- Sync location from selected variant id (URL or buybox/inspector events) -----
function normLabel(s){ return String(s||'').toLowerCase().replace(/[^a-z0-9]+/g,'') }
function findLocIndexByLabel(lbl){
  try{
    const want = normLabel(lbl)
    const list = locations.value || []
    // Exact/norm match first
    const exact = list.findIndex(x => normLabel(x.label) === want)
    if (exact >= 0) return exact
    // Fuzzy contains
    const fuzzy = list.findIndex(x => {
      const n = normLabel(x.label); return n && (n.includes(want) || want.includes(n))
    })
    if (fuzzy >= 0) return fuzzy
    return -1
  }catch{ return -1 }
}
function extractLocationFromVariant(vid){
  try{
    const p = props.product || {}
    const variants = Array.isArray(p.variants) ? p.variants : []
    const v = variants.find(x => String(x?.id) === String(vid))
    const toks = Array.isArray(v?.options) ? v.options.map(t=>String(t||'')) : []
    // Prefer explicit Online
    if (toks.some(t => /online/i.test(String(t)))) return 'Online'
    // Otherwise, match against known location labels
    const list = Array.isArray(p.locations) ? p.locations.map(s=>String(s||'')) : []
    for (const lbl of list){
      const n = normLabel(lbl)
      if (!n) continue
      const hit = toks.find(t => { const tn=normLabel(t); return tn && (tn===n || tn.includes(n) || n.includes(tn)) })
      if (hit) return lbl
    }
    return ''
  }catch{ return '' }
}
function setLocationFromVariant(vid){
  try{
    const lbl = extractLocationFromVariant(vid)
    if (!lbl) return
    // If Online, open the online pane, else map
    const idx = findLocIndexByLabel(lbl)
    if (idx >= 0) { selectLoc(idx) }
    else if (/online/i.test(lbl)) {
      // If we render an implicit online row, switch panes
      showPane.value = 'online'
    }
  }catch{}
}

onMounted(() => {
  try{
    const onVar = (ev)=>{ const vid = ev?.detail?.id; if (vid!=null) setLocationFromVariant(vid) }
    const onVarIdx = (ev)=>{ try { const idx = Number(ev?.detail?.index); const p=props.product||{}; const arr=Array.isArray(p.variants)?p.variants:[]; const v=arr[idx]; if (v && v.id!=null) setLocationFromVariant(v.id) } catch {} }
    window.addEventListener('wow:variant-select', onVar)
    window.addEventListener('wow:location-index', onVarIdx)
    try { window.__wowxp_onVar = onVar; window.__wowxp_onVarIdx = onVarIdx } catch {}
  } catch {}
})
onBeforeUnmount(() => {
  try { if (window.__wowxp_onVar) window.removeEventListener('wow:variant-select', window.__wowxp_onVar) } catch {}
  try { if (window.__wowxp_onVarIdx) window.removeEventListener('wow:location-index', window.__wowxp_onVarIdx) } catch {}
})

// Scroll offset variable for section scroll-margin
function setScrollOffsetVar(){
  try {
    const header = document.querySelector('header')
    const isDesktop = typeof window !== 'undefined' && window.matchMedia('(min-width: 992px)').matches
    const stickyTop = isDesktop ? 82 : ((header?.offsetHeight) || 64)
    // Sticky top offset
    rootEl.value?.style.setProperty('--wowxp-sticky-top', stickyTop + 'px')
    // Scroll margin offset a bit more for sections
    rootEl.value?.style.setProperty('--wowxp-scrolloff', (stickyTop + 12) + 'px')
  } catch {}
}

function smoothTo(hash){
  const id = typeof hash === 'string' ? hash : ''
  if (!id.startsWith('#')) return
  const el = rootEl.value?.querySelector(id) || document.querySelector(id)
  if (!el) return
  el.scrollIntoView({ behavior:'smooth', block:'start' })
}

// Scroll spy
let scrollRaf = 0
function onScroll(){
  if (scrollRaf) return
  scrollRaf = requestAnimationFrame(()=>{
    scrollRaf = 0
    const ids = ['#overview','#included','#need','#location','#wowxp-reviews']
    const sections = ids.map(id => ({ id, el: rootEl.value?.querySelector(id) })).filter(s => !!s.el)
    const topOffset = ((tabsBar.value?.offsetHeight) || 64) + 12
    let best = null; let bestScore = Infinity
    for (const s of sections){
      const r = s.el.getBoundingClientRect()
      if (r.bottom > topOffset){
        const d = Math.abs(r.top - topOffset)
        if (d < bestScore){ best = s; bestScore = d }
      }
    }
    if (!best && sections.length) best = sections[sections.length - 1]
    if (best) activeTab.value = best.id
  })
}

// Lightbox (simplified)
const lbOpen = ref(false)
const lbIndex = ref(0)
function openLightbox(i){ lbIndex.value = i; lbOpen.value = true; document.body.classList.add('no-scroll') }
function closeLightbox(){ lbOpen.value = false; document.body.classList.remove('no-scroll') }
function prev(){ lbIndex.value = Math.max(0, lbIndex.value - 1) }
function next(){ lbIndex.value = Math.min(images.value.length - 1, lbIndex.value + 1) }

// Location highlight bar
function positionLocBar(){
  try {
    const list = rootEl.value?.querySelector('.wowxp-loclist')
    const bar = rootEl.value?.querySelector('.wowxp-locbar')
    const active = list?.querySelector('.list-group-item.active')
    if (!list || !bar || !active) return
    const aRect = active.getBoundingClientRect()
    const cRect = list.getBoundingClientRect()
    const top = aRect.top - cRect.top + list.scrollTop
    bar.style.height = aRect.height + 'px'
    bar.style.transform = `translateY(${top}px)`
  } catch {}
}

onMounted(async () => {
  detectDesktop();
  updateScrollState();
  try { window.addEventListener('resize', detectDesktop); window.addEventListener('resize', updateScrollState) } catch {}
  try { galleryStripEl.value?.addEventListener('scroll', syncCurrentSet, { passive:true }) } catch {}
  await nextTick()
  setScrollOffsetVar()
  // Open based on URL format param if present
  try {
    const url = new URL(window.location.href)
    const f = url.searchParams.get('format')
    if (f) {
      const target = String(f).toLowerCase().includes('online') ? 'online' : 'map'
      let idx = locations.value.findIndex(l => l.type === target)
      if (idx < 0) idx = 0
      // Apply without dispatching external events
      activeLocIdx.value = idx
      showPane.value = target
      try {
        const item = locations.value[idx]
        if (item?.type === 'map') {
          const frame = rootEl.value?.querySelector('#wowxp-map')
          if (frame && frame.src !== item.embed) frame.src = item.embed
        }
      } catch {}
      await nextTick(); positionLocBar()
    } else {
      selectLoc(0)
    }
  } catch { selectLoc(0) }
  addEventListener('resize', setScrollOffsetVar)
  rootEl.value?.querySelector('.wowxp-loclist')?.addEventListener('scroll', positionLocBar, { passive:true })
  addEventListener('scroll', onScroll, { passive:true })
  // ESC to close lightbox
  const onKey = (e) => { if (e.key === 'Escape' && lbOpen.value) closeLightbox() }
  addEventListener('keydown', onKey)
  // store to remove
  rootEl.value && (rootEl.value._onKey = onKey)
  setTimeout(positionLocBar, 60)
  // Recompute scroll state once images settle
  setTimeout(updateScrollState, 200)
  // React to initial/ongoing format selections emitted by the buy box
  try {
    const handler = (ev) => {
      const f = String(ev?.detail?.format || '')
      if (!f) return
      const target = f.toLowerCase().includes('online') ? 'online' : 'map'
      let idx = locations.value.findIndex(l => l.type === target)
      if (idx < 0) idx = 0
      activeLocIdx.value = idx
      showPane.value = target
      try {
        const item = locations.value[idx]
        if (item?.type === 'map') {
          const frame = rootEl.value?.querySelector('#wowxp-map')
          if (frame && frame.src !== item.embed) frame.src = item.embed
        }
      } catch {}
      nextTick().then(positionLocBar)
    }
    window.addEventListener('wow:format-selected', handler)
    rootEl.value && (rootEl.value._fmtHandler = handler)
  } catch {}
})

onBeforeUnmount(() => {
  removeEventListener('resize', setScrollOffsetVar)
  removeEventListener('scroll', onScroll)
  try { window.removeEventListener('resize', detectDesktop); window.removeEventListener('resize', updateScrollState) } catch {}
  try { galleryStripEl.value?.removeEventListener('scroll', syncCurrentSet) } catch {}
  try{ if (rootEl.value?._onKey) removeEventListener('keydown', rootEl.value._onKey) }catch{}
  try{ if (rootEl.value?._fmtHandler) window.removeEventListener('wow:format-selected', rootEl.value._fmtHandler) }catch{}
})

// Sanitize About HTML: strip classes/styles and inline events
function sanitizeHtml(raw){
  try {
    // Handle cases where HTML is entity-encoded (e.g. &lt;p&gt;...&lt;/p&gt;)
    let content = String(raw || '')
    const looksEncoded = /&lt;|&gt;|&amp;[a-z]+;/i.test(content) && !/[<][a-zA-Z!/]/.test(content)
    if (looksEncoded) {
      try {
        const decoder = document.createElement('textarea')
        decoder.innerHTML = content
        content = decoder.value || decoder.textContent || content
      } catch {}
    }

    const wrapper = document.createElement('div')
    wrapper.innerHTML = content
    // Remove script and style tags entirely
    wrapper.querySelectorAll('script, style').forEach(n => n.remove())
    const walker = document.createTreeWalker(wrapper, NodeFilter.SHOW_ELEMENT)
    while (walker.nextNode()){
      const el = walker.currentNode
      try { el.removeAttribute('class') } catch {}
      try { el.removeAttribute('style') } catch {}
      try {
        const attrs = Array.from(el.attributes || [])
        attrs.forEach(a => { if (/^on/i.test(a.name)) el.removeAttribute(a.name) })
      } catch {}
    }
    return wrapper.innerHTML
  } catch {
    return String(raw||'')
      .replace(/<\/(script|style)>/gi,'')
      .replace(/<[ ]*(script|style)[^>]*>.*?<\/(script|style)>/gsi,'')
      .replace(/\sclass="[^"]*"/gi,'')
      .replace(/\sstyle="[^"]*"/gi,'')
  }
}
const sanitizedAbout = computed(() => {
  const html = props.product?.body_html || props.product?.description || ''
  return sanitizeHtml(html)
})
</script>

<template>
  <div ref="rootEl" class="wowxp-body">
    <div class="container wowxp-wrap py-3 py-lg-4">

      <section class="wowxp-hero" aria-labelledby="wowxp-title">
        <div class="wowxp-hero-meta">
          <span class="wowxp-pill">{{ typeLabel }}</span>
          <h1 id="wowxp-title" class="wowxp-hero-title">{{ props.product?.title }}</h1>
          <p v-if="heroSummary" class="wowxp-hero-summary">{{ heroSummary }}</p>
          <div class="wowxp-hero-tags" v-if="heroTags.length">
            <span v-for="(tag, idx) in heroTags" :key="idx">{{ tag }}</span>
          </div>
          <div class="wowxp-info-grid" v-if="infoHighlights.length">
            <div class="wowxp-info" v-for="item in infoHighlights" :key="item.label">
              <small class="wowxp-muted">{{ item.label }}</small>
              <strong>{{ item.value }}</strong>
            </div>
          </div>
          <div class="wowxp-rating-row" v-if="props.product?.rating">
            <span class="wowxp-stars" aria-hidden="true">★</span>
            <strong>{{ Number(props.product.rating).toFixed ? Number(props.product.rating).toFixed(1) : props.product.rating }} / 5</strong>
            <span class="wowxp-muted">({{ props.product.review_count || 0 }} reviews)</span>
          </div>
          <div class="wowxp-hero-ctas">
            <WowButton as="button" variant="cta" :arrow="true" @click="smoothTo('#booking-card')">{{ heroCtaLabel }}</WowButton>
            <button type="button" class="wowxp-hero-link" @click="smoothTo('#wowxp-reviews')">Read reviews</button>
          </div>
        </div>
      </section>

      <!-- Gallery -->
      <section class="wowxp-gallery" :class="{ 'is-carousel': useCarousel }" id="wowxp-gallery" aria-label="Gallery">
        <div v-if="useCarousel" class="wowxp-sets" ref="galleryStripEl">
          <div v-for="(grp,gi) in imageGroups" :key="gi" class="wowxp-set">
            <div :class="['wowxp-set-grid', (gi % 2 === 1) ? 'is-21' : 'is-12']">
              <figure v-if="grp[0]" :class="['wowxp-fig','wowxp-fig-lg','ga-big','is-loading']">
                <img :src="grp[0]" :alt="props.product?.title || ''" loading="lazy" decoding="async"
                     @load="($event.target.closest('.wowxp-fig')?.classList.remove('is-loading'))"
                     @click="openLightbox(gi*3)" style="cursor:zoom-in" />
                <span class="wowxp-zoomhint" aria-hidden="true"></span>
              </figure>
              <figure v-if="grp[1]" :class="['wowxp-fig','wowxp-fig-sm','ga-s1','is-loading']">
                <img :src="grp[1]" :alt="props.product?.title || ''" loading="lazy" decoding="async"
                     @load="($event.target.closest('.wowxp-fig')?.classList.remove('is-loading'))"
                     @click="openLightbox(gi*3 + 1)" style="cursor:zoom-in" />
                <span class="wowxp-zoomhint" aria-hidden="true"></span>
              </figure>
              <figure v-if="grp[2]" :class="['wowxp-fig','wowxp-fig-sm','ga-s2','is-loading']">
                <img :src="grp[2]" :alt="props.product?.title || ''" loading="lazy" decoding="async"
                     @load="($event.target.closest('.wowxp-fig')?.classList.remove('is-loading'))"
                     @click="openLightbox(gi*3 + 2)" style="cursor:zoom-in" />
                <span class="wowxp-zoomhint" aria-hidden="true"></span>
              </figure>
            </div>
          </div>
        </div>
        <div v-else class="wowxp-strip" ref="galleryStripEl">
          <figure v-for="(img,i) in images" :key="i" :class="['wowxp-fig', (i===0) ? 'wowxp-fig-lg' : '', 'is-loading']">
            <img :src="img" :alt="props.product?.title || ''" loading="lazy" decoding="async"
                 @load="($event.target.closest('.wowxp-fig')?.classList.remove('is-loading'))"
                 @click="openLightbox(i)" style="cursor:zoom-in" />
            <span class="wowxp-zoomhint" aria-hidden="true"></span>
          </figure>
        </div>
        <button v-if="useCarousel && canScroll" class="wowxp-gal-btn wowxp-gal-prev" type="button" aria-label="Previous images" @click="slide(-1)"></button>
        <button v-if="useCarousel && canScroll" class="wowxp-gal-btn wowxp-gal-next" type="button" aria-label="Next images" @click="slide(1)"></button>
      </section>

      <!-- Sticky tabs -->
      <div ref="tabsBar" class="wowxp-tabs mb-2">
        <ul class="nav nav-pills w-100" id="wowxp-tab">
          <li class="nav-item"><a :class="['nav-link', activeTab==='#overview' && 'active']" href="#overview" @click.prevent="smoothTo('#overview')">Overview</a></li>
          <li class="nav-item"><a :class="['nav-link', activeTab==='#included' && 'active']" href="#included" @click.prevent="smoothTo('#included')">What’s included</a></li>
          <li class="nav-item"><a :class="['nav-link', activeTab==='#need' && 'active']" href="#need" @click.prevent="smoothTo('#need')">Need to know</a></li>
          <li class="nav-item"><a :class="['nav-link', activeTab==='#location' && 'active']" href="#location" @click.prevent="smoothTo('#location')">Locations</a></li>
          <li class="nav-item"><a :class="['nav-link', activeTab==='#wowxp-reviews' && 'active']" href="#wowxp-reviews" @click.prevent="smoothTo('#wowxp-reviews')">Reviews</a></li>
        </ul>
      </div>

      <!-- Overview -->
      <section id="overview" class="wowxp-section">
        <div class="wowxp-head">
          <span class="wowxp-pill">Overview</span>
          <h3>How this helps</h3>
        </div>
        <div class="wowxp-card-grid" v-if="benefitsList.length || whoForList.length || whoNotList.length">
          <article class="wowxp-card" v-if="benefitsList.length">
            <h4>What you’ll gain</h4>
            <ul>
              <li v-for="(benefit, idx) in benefitsList" :key="`benefit-${idx}`">{{ benefit }}</li>
            </ul>
          </article>
          <article class="wowxp-card" v-if="whoForList.length">
            <h4>Who this is for</h4>
            <ul>
              <li v-for="(item, idx) in whoForList" :key="`for-${idx}`">{{ item }}</li>
            </ul>
          </article>
          <article class="wowxp-card" v-if="whoNotList.length">
            <h4>Who should skip</h4>
            <ul>
              <li v-for="(item, idx) in whoNotList" :key="`not-${idx}`">{{ item }}</li>
            </ul>
          </article>
        </div>
        <div v-if="expectHtml" class="wowxp-rich" v-html="expectHtml"></div>
        <div v-else-if="sanitizedAbout" class="wowxp-rich" :class="{ 'wowxp-clamp': !aboutExpanded }" id="wowxp-about" v-html="sanitizedAbout"></div>
        <button v-if="sanitizedAbout" class="wowxp-readlink" @click="aboutExpanded=!aboutExpanded; setTimeout(()=>onScroll(true), 0)" :aria-expanded="aboutExpanded ? 'true' : 'false'">
          <span v-text="aboutExpanded ? 'Show less ' : 'Read more '"></span>
          <span class="wowxp-arrow"></span>
        </button>
      </section>

      <!-- Included -->
      <section id="included" class="wowxp-section">
        <div class="wowxp-head">
          <span class="wowxp-pill">Included</span>
          <h3>What’s included</h3>
        </div>
        <ul class="wowxp-list" v-if="props.product?.included">
          <li v-for="(line,idx) in String(props.product.included).split('\n').filter(Boolean)" :key="idx">
            <span class="wowxp-li-ic" style="--wowxp-icon:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/solid/check-circle.svg')"></span>
            <span v-html="line"></span>
          </li>
        </ul>
        <p v-else class="wowxp-muted">See booking options for what’s included.</p>
      </section>

      <!-- Need to know -->
      <section id="need" class="wowxp-section">
        <div class="wowxp-head">
          <span class="wowxp-pill">Info</span>
          <h3>Need to know</h3>
        </div>
        <ul class="wowxp-list">
          <li><span class="wowxp-li-ic" style="--wowxp-icon:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/outline/shield-check.svg')"></span> Voucher valid for at least 90 days from purchase. Extensions available on request.</li>
          <li><span class="wowxp-li-ic" style="--wowxp-icon:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/outline/shield-check.svg')"></span> Suitable for most adults. Check any health or access requirements with the practitioner.</li>
          <li><span class="wowxp-li-ic" style="--wowxp-icon:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/outline/shield-check.svg')"></span> See product page for session-specific details.</li>
        </ul>
      </section>

      <section id="safety" class="wowxp-section">
        <div class="wowxp-head">
          <span class="wowxp-pill">Safety</span>
          <h3>Safety &amp; contraindications</h3>
        </div>
        <div class="wowxp-safety">
          <p v-if="safetyNotes">{{ safetyNotes }}</p>
          <p v-if="contraindications">{{ contraindications }}</p>
          <p class="wowxp-muted">{{ standardSafetyLine }}</p>
          <a :href="safetyLink" class="wowxp-hero-link">Read full Safety &amp; Contraindications guidance</a>
        </div>
      </section>

      <section v-if="practitioner" class="wowxp-section">
        <div class="wowxp-head">
          <span class="wowxp-pill">Practitioner</span>
          <h3>About your practitioner</h3>
        </div>
        <div class="wowxp-practitioner">
          <div class="wowxp-practitioner-photo" v-if="practitioner.photo">
            <img :src="practitioner.photo" :alt="practitioner.name" />
          </div>
          <div>
            <h4 class="wowxp-practitioner-name">
              {{ practitioner.name }}
              <small v-if="practitioner.pronouns">({{ practitioner.pronouns }})</small>
            </h4>
            <p v-if="practitioner.credentials" class="wowxp-muted">{{ practitioner.credentials }}</p>
            <p v-if="practitioner.bio" class="mt-2">{{ practitioner.bio }}</p>
            <div class="wowxp-hero-tags" v-if="practitioner.specialties?.length">
              <span v-for="(spec, idx) in practitioner.specialties" :key="`spec-${idx}`">{{ spec }}</span>
            </div>
            <a :href="practitionerProfileUrl" class="wowxp-hero-link mt-3">View practitioner profile</a>
          </div>
        </div>
      </section>

      <section v-if="aftercareHtml" class="wowxp-section">
        <div class="wowxp-head">
          <span class="wowxp-pill">Aftercare</span>
          <h3>After your session</h3>
        </div>
        <div class="wowxp-rich" v-html="aftercareHtml"></div>
      </section>

      <section v-if="faqList.length" id="wowxp-faq" class="wowxp-section">
        <div class="wowxp-head">
          <span class="wowxp-pill">FAQs</span>
          <h3>Common questions</h3>
        </div>
        <div class="accordion">
          <details v-for="(faq, idx) in faqList" :key="`faq-${idx}`" class="acc-item">
            <summary>{{ faq.question }}</summary>
            <p class="mt-2 wowxp-muted" v-if="faq.answer" v-html="faq.answer"></p>
          </details>
        </div>
      </section>

      <!-- Locations -->
      <section id="location" class="wowxp-section">
        <div class="wowxp-head">
          <span class="wowxp-pill">Locations</span>
          <h3>Choose a location</h3>
        </div>

        <div class="wowxp-locwrap">
          <div class="row g-3">
            <div class="col-md-5">
              <div class="list-group wowxp-loclist" role="tablist" aria-label="Locations">
                <div class="wowxp-locbar" aria-hidden="true"></div>

                <button v-for="(loc,i) in locations" :key="i" type="button" :class="['list-group-item','wowxp-loc', i===activeLocIdx && 'active']"
                        @click="selectLoc(i)">
                  <span class="wowxp-ico" :class="loc.type==='online' ? 'wowxp-online' : 'wowxp-pin'"></span>
                  <span class="wowxp-loclabel">{{ loc.label }}</span>
                  <span class="wowxp-chevron"></span>
                </button>
              </div>
            </div>

            <div class="col-md-7">
              <div class="wowxp-switcher">
                <div id="wowxp-mapwrap" class="wowxp-map wowxp-switchpane" :class="{ 'is-hidden': showPane==='online' }">
                  <iframe id="wowxp-map" title="Location Map" :src="locations[activeLocIdx]?.embed || ''" width="100%" height="360" style="border:0" loading="lazy"></iframe>
                </div>
                <div id="wowxp-online" class="wowxp-switchpane" :class="{ 'is-hidden': showPane!=='online' }">
                  <div class="p-3 border rounded-3" style="border-color:var(--wowxp-border); background:var(--wowxp-card)">
                    <div class="d-flex align-items-center gap-2 mb-1">
                      <span class="wowxp-ico wowxp-online"></span>
                      <strong>Available Online</strong>
                    </div>
                    <p class="mb-2 wowxp-muted">Join from anywhere. After purchase you’ll receive a booking link with your live session details.</p>
                  </div>
                </div>
              </div>
              <p class="small wowxp-muted mt-2">Locations shown shouldn’t be used for route planning.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Reviews -->
      <section id="wowxp-reviews" class="wowxp-section">
        <div class="wowxp-head">
          <span class="wowxp-pill">Reviews</span>
          <h3>What people say</h3>
        </div>
        <ReviewList :reviews="props.product.reviews || []" :average="props.product.rating" :count="props.product.review_count || 0" />
      </section>
    </div>

    <!-- LIGHTBOX -->
    <div class="wowxp-lightbox" v-if="lbOpen" role="dialog" aria-modal="true" aria-label="Image viewer" @click.self="closeLightbox">
      <div class="wowxp-lb-top">
        <button class="wowxp-lb-btn" @click="closeLightbox" aria-label="Close"><span class="wowxp-lb-icon wowxp-ico-close"></span></button>
        <div id="lb-counter">{{ lbIndex+1 }} / {{ images.length }}</div>
      </div>
      <button class="wowxp-lb-arrow wowxp-lb-prev d-none d-lg-flex" @click="prev" aria-label="Previous">
        <span class="wowxp-lb-icon wowxp-ico-left"></span>
      </button>
      <div class="wowxp-lb-track">
        <div class="wowxp-lb-slides" :style="{ transform: `translateX(${-lbIndex*100}vw)` }">
          <div v-for="(img,i) in images" :key="i" class="wowxp-lb-slide">
            <img class="wowxp-lb-img" :src="img" :alt="props.product?.title || ''">
          </div>
        </div>
      </div>
      <button class="wowxp-lb-arrow wowxp-lb-next d-none d-lg-flex" @click="next" aria-label="Next">
        <span class="wowxp-lb-icon wowxp-ico-right"></span>
      </button>
    </div>
  </div>
</template>

<style>
/* ========= WOWXP (scoped to wrapper where applicable) ========= */
:root{
  --wowxp-primary:#1a73e8; --wowxp-green:#16a34a; --wowxp-green-2:#22c55e; --wowxp-ink:#0b1323;
  --wowxp-muted:#6b7280; --wowxp-bg:#f7f9fc; --wowxp-card:#ffffff; --wowxp-shadow:0 10px 28px rgba(11,19,35,.06);
  --wowxp-border:#e5e7eb; --wowxp-scrolloff:84px; --wowxp-radius:18px;
}
*{box-sizing:border-box}
.wowxp-body{background:transparent; color:var(--wowxp-ink)}
.wowxp-hero{background:#fff; border:1px solid var(--wowxp-border); border-radius:24px; padding:1.75rem; margin-bottom:1.5rem; box-shadow:var(--wowxp-shadow)}
.wowxp-hero-title{font-size:2rem; font-weight:700; margin:.5rem 0}
.wowxp-hero-summary{font-size:1.1rem; color:var(--wowxp-muted); margin-bottom:1rem; max-width:720px}
.wowxp-hero-tags{display:flex; flex-wrap:wrap; gap:.5rem; margin-bottom:1rem}
.wowxp-hero-tags span{background:var(--wowxp-ink); color:#fff; padding:.25rem .75rem; border-radius:999px; font-size:.85rem}
.wowxp-info-grid{display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:1rem; margin-bottom:1rem}
.wowxp-info strong{display:block; font-size:1rem; color:var(--wowxp-ink)}
.wowxp-rating-row{display:flex; align-items:center; gap:.5rem; font-weight:600; margin-bottom:1rem}
.wowxp-stars{color:#facc15; font-size:1.2rem}
.wowxp-hero-ctas{display:flex; flex-wrap:wrap; gap:.75rem}
.wowxp-hero-link{border:0; background:transparent; color:var(--wowxp-muted); font-weight:600; text-decoration:underline; padding:0}
.wowxp-card-grid{display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:1rem; margin-bottom:1.25rem}
.wowxp-card{background:#fff; border:1px solid var(--wowxp-border); border-radius:20px; padding:1.25rem; box-shadow:var(--wowxp-shadow)}
.wowxp-card h4{font-size:1.05rem; margin-bottom:.75rem}
.wowxp-card ul{padding-left:1.1rem; margin:0; line-height:1.5}
.wowxp-rich{line-height:1.7}
.wowxp-safety{background:#fff; border:1px solid var(--wowxp-border); border-radius:18px; padding:1.25rem; box-shadow:var(--wowxp-shadow); display:flex; flex-direction:column; gap:.5rem}
.wowxp-practitioner{display:flex; gap:1.25rem; align-items:flex-start; background:#fff; border:1px solid var(--wowxp-border); border-radius:20px; padding:1.5rem; box-shadow:var(--wowxp-shadow)}
.wowxp-practitioner-photo img{width:120px; height:120px; object-fit:cover; border-radius:18px}
.wowxp-practitioner-name{margin:0; font-size:1.2rem; font-weight:600}
.wowxp-wrap{max-width:1180px}
.wowxp-title{letter-spacing:.2px}
.no-scroll{overflow:hidden; touch-action:none}

/* Gallery */
.wowxp-gallery{position:relative; margin:0 -12px 10px}
.wowxp-strip{display:flex; gap:10px; overflow-x:auto; scroll-snap-type:x mandatory; -webkit-overflow-scrolling:touch; padding-bottom:6px}
.wowxp-strip::-webkit-scrollbar{display:none}
.wowxp-fig{position:relative; overflow:hidden; border-radius:14px; background:#e9eef8; flex:0 0 86vw; scroll-snap-align:center; margin-left:12px; margin-right:12px; aspect-ratio:4/3; box-shadow:var(--wowxp-shadow); transform:translateZ(0)}
.wowxp-fig img{width:100%; height:100%; object-fit:cover; transform:scale(1.02); transition:transform .55s cubic-bezier(.2,.8,.2,1), filter .35s ease, opacity .25s ease}
.wowxp-fig:hover img{transform:scale(1.04)}
.wowxp-fig.is-loading img{filter:blur(10px) saturate(110%); opacity:.85}
.wowxp-zoomhint{position:absolute; right:10px; bottom:10px; width:30px; height:30px; opacity:.9; background:#fff; border-radius:999px; display:flex; align-items:center; justify-content:center; box-shadow:0 8px 20px rgba(0,0,0,.08)}
.wowxp-zoomhint::before{content:""; width:18px; height:18px; background:#111827; -webkit-mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/20/solid/arrows-pointing-out.svg') no-repeat center/contain; mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/20/solid/arrows-pointing-out.svg') no-repeat center/contain}

@media (min-width:992px){
  .wowxp-gallery{margin:0 0 12px; padding:0}
  /* Non-carousel desktop: classic 1 big + 2 stacked */
  .wowxp-strip{display:grid; gap:14px; overflow:visible; grid-template-columns:1.15fr .85fr; grid-template-rows:240px 240px; scroll-snap-type:none}
  .wowxp-fig{flex:auto; margin:0; aspect-ratio:auto; border-radius:20px}
  .wowxp-fig-lg{grid-row:1 / 3}
  /* Carousel sets: each set uses the same grid composition */
  .wowxp-sets{display:flex; gap:14px; overflow-x:auto; scroll-snap-type:x mandatory; padding-bottom:6px}
  .wowxp-sets::-webkit-scrollbar{display:none}
  .wowxp-set{flex:0 0 clamp(720px, 70vw, 980px); scroll-snap-align:center}
  .wowxp-set-grid{display:grid; gap:14px; grid-template-columns:1.15fr .85fr; grid-template-rows:280px 280px; grid-template-areas: 'big small1' 'big small2'}
  .wowxp-set-grid.is-21{ grid-template-columns:.85fr 1.15fr; grid-template-areas: 'small1 big' 'small2 big' }
  .wowxp-set-grid .wowxp-fig{margin:0}
  .wowxp-set-grid .ga-big{ grid-area: big }
  .wowxp-set-grid .ga-s1{ grid-area: small1 }
  .wowxp-set-grid .ga-s2{ grid-area: small2 }
  .wowxp-fig-lg{grid-row:1 / 3}
  .wowxp-gal-btn{position:absolute; top:50%; transform:translateY(-50%); width:42px; height:42px; border-radius:999px; border:1px solid rgba(2,8,23,.12); background:rgba(255,255,255,.9); box-shadow:0 6px 14px rgba(2,8,23,.12); display:flex; align-items:center; justify-content:center; z-index:2}
  .wowxp-gal-prev{left:6px}
  .wowxp-gal-next{right:6px}
  .wowxp-gal-btn::before{content:""; display:block; width:20px; height:20px; background:#0b1323}
  .wowxp-gal-prev::before{ -webkit-mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/solid/chevron-left.svg') no-repeat center/contain; mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/solid/chevron-left.svg') no-repeat center/contain }
  .wowxp-gal-next::before{ -webkit-mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/solid/chevron-right.svg') no-repeat center/contain; mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/solid/chevron-right.svg') no-repeat center/contain }
}

/* Sticky buy box (kept for structure, not used directly here) */
.wowxp-buy{position:sticky; top:20px; background:var(--wowxp-card); border-radius:22px; padding:20px; box-shadow:var(--wowxp-shadow)}
.wowxp-price{font-size:32px; font-weight:800}

/* Sections, headings */
.wowxp-section{margin-top:22px; padding-top:18px; position:relative; scroll-margin-top:var(--wowxp-scrolloff)}
.wowxp-section::before{content:""; position:absolute; top:0; left:0; right:0; height:1px; background:linear-gradient(90deg, rgba(0,0,0,.06), rgba(0,0,0,.02))}
.wowxp-head{display:flex; align-items:center; gap:.6rem; margin-bottom:.6rem}
.wowxp-head .wowxp-pill{background:#eaf7ef; color:#064e2e; border-radius:999px; font-weight:700; padding:.25rem .65rem; font-size:.8rem; line-height:1}
.wowxp-head h3{margin:0; font-weight:800; font-size:1.125rem}

/* Quick info (optional) */
.wowxp-quick .wowxp-item{text-align:center; padding:12px 6px; transition:transform .25s ease}
.wowxp-quick .wowxp-item:hover{ transform: translateY(-3px) }
.wowxp-quick .wowxp-ic{width:30px; height:30px; display:block; margin:0 auto 8px; background:linear-gradient(135deg,var(--wowxp-green),var(--wowxp-green-2)); -webkit-mask-image:var(--wowxp-icon); mask-image:var(--wowxp-icon); -webkit-mask-repeat:no-repeat; mask-repeat:no-repeat; -webkit-mask-position:center; mask-position:center; -webkit-mask-size:contain; mask-size:contain; transition:transform .25s ease}
.wowxp-quick .wowxp-k{font-weight:800; font-size:.98rem}
.wowxp-quick .wowxp-s{color:var(--wowxp-muted); font-size:.82rem; margin-top:-2px}

/* Lists */
.wowxp-list{list-style:none; padding-left:0; margin:0}
.wowxp-list li{display:flex; gap:10px; align-items:flex-start; margin:.5rem 0}
.wowxp-list .wowxp-li-ic{width:20px; height:20px; background:var(--wowxp-green); -webkit-mask-image:var(--wowxp-icon); mask-image:var(--wowxp-icon); -webkit-mask-repeat:no-repeat; mask-repeat:no-repeat; -webkit-mask-position:center; mask-position:center; -webkit-mask-size:contain; mask-size:contain}

/* Reviews */
.wowxp-review{background:var(--wowxp-card); border-radius:18px; box-shadow:var(--wowxp-shadow); padding:18px; height:100%}
.wowxp-stars{display:flex; gap:2px}
.wowxp-star{width:18px; height:18px; background:#f59e0b; -webkit-mask-image:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/20/solid/star.svg'); mask-image:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/20/solid/star.svg'); -webkit-mask-repeat:no-repeat; mask-repeat:no-repeat; -webkit-mask-size:contain; mask-size:contain; -webkit-mask-position:center; mask-position:center}

/* Tabs */
.wowxp-tabs{position:sticky; top:var(--wowxp-sticky-top, 64px); z-index:1; background:transparent; padding:.5rem 0 .25rem}
.wowxp-tabs .nav{display:grid; grid-template-columns:repeat(auto-fit, minmax(120px, 1fr)); gap:.4rem; background:var(--wowxp-card); border:1px solid #ddd; border-radius:999px; padding:6px; box-shadow:var(--wowxp-shadow)}
.wowxp-tabs .nav-link{border:0; border-radius:999px; color:#384252; padding:.42rem .65rem; text-align:center; font-size:.84rem; line-height:1; white-space:nowrap; font-weight:600}
.wowxp-tabs .nav-link.active{background:#e7f0fe; color:#1147a6; font-weight:700}
/* Prevent global header underline animation from affecting wowxp pills */
.wowxp-tabs .nav-item > a::after{ content:none !important; display:none !important }

/* Locations */
.wowxp-locwrap{border-radius:18px; background:var(--wowxp-card); box-shadow:none; padding:14px}
.wowxp-loclist{position:relative}
.wowxp-locbar{position:absolute; left:-4px; right:-4px; top:0; height:48px; border-radius:14px; background:linear-gradient(180deg,#f0fdf4,#ecfdf5); box-shadow:0 10px 20px rgba(22,163,74,.06), 0 2px 6px rgba(22,163,74,.05); transform:translateY(0); transition:transform .32s cubic-bezier(.2,.8,.2,1), height .2s ease; z-index:0; pointer-events:none}
.wowxp-loclist .list-group-item{position:relative; z-index:1; border:0; border-radius:14px; padding:.7rem .85rem; display:flex; align-items:center; gap:.65rem; color:#0b1323; line-height:1.2; min-height:48px; background:transparent; transition:transform .2s ease, color .2s ease; overflow:hidden}
.wowxp-ico{width:22px;height:22px;flex:0 0 22px;position:relative}
.wowxp-pin{background:#ef4444; -webkit-mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/solid/map-pin.svg') no-repeat center/contain; mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/solid/map-pin.svg') no-repeat center/contain}
.wowxp-online{background:#10b981; -webkit-mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/solid/wifi.svg') no-repeat center/contain; mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/solid/wifi.svg') no-repeat center/contain}
.wowxp-chevron{width:16px;height:16px;margin-left:auto;background:#9ca3af; -webkit-mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/20/solid/chevron-right.svg') no-repeat center/contain; mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/20/solid/chevron-right.svg') no-repeat center/contain; transition:transform .2s ease, background-color .2s ease; flex:0 0 16px}
.wowxp-loclist .list-group-item.active .wowxp-chevron{ transform:translateX(4px); background:#16a34a }

.wowxp-switcher{position:relative; min-height:360px}
.wowxp-switchpane{transition:opacity .28s ease, transform .28s ease}
.wowxp-switchpane.is-hidden{opacity:0; transform:translateY(6px); visibility:hidden; pointer-events:none; position:absolute; inset:0}
.wowxp-map{border-radius:18px; overflow:hidden; box-shadow:var(--wowxp-shadow)}

/* Utilities */
.wowxp-muted{color:var(--wowxp-muted)}
.wowxp-clamp{display:-webkit-box; -webkit-line-clamp:5; -webkit-box-orient:vertical; overflow:hidden}
.wowxp-readlink{border:0; background:transparent; color:var(--wowxp-muted); font-weight:600; display:inline-flex; align-items:center; gap:6px}
.wowxp-readlink .wowxp-arrow{width:16px; height:16px; background:#9ca3af; -webkit-mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/20/solid/chevron-down.svg') no-repeat center/contain; mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/20/solid/chevron-down.svg') no-repeat center/contain; transition:transform .25s ease}
.wowxp-readlink[aria-expanded="true"] .wowxp-arrow{transform:rotate(180deg)}

/* Lightbox */
.wowxp-lightbox{position:fixed; inset:0; z-index:1090; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,.88); -webkit-backdrop-filter:saturate(140%) blur(8px); backdrop-filter:saturate(140%) blur(8px)}
.wowxp-lb-top{position:fixed; top:env(safe-area-inset-top, 10px); left:0; right:0; display:flex; align-items:center; justify-content:space-between; padding:8px 14px; color:#fff; pointer-events:auto; font-weight:600; text-shadow:0 2px 12px rgba(0,0,0,.5); z-index:2}
.wowxp-lb-btn{pointer-events:auto; border:0; background:rgba(255,255,255,.12); color:#fff; width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center}
.wowxp-lb-btn:active{transform:scale(.98)}
.wowxp-lb-icon{width:22px; height:22px; background:#fff}
.wowxp-ico-close{ -webkit-mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/solid/x-mark.svg') no-repeat center/contain; mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/solid/x-mark.svg') no-repeat center/contain }
.wowxp-ico-left{  -webkit-mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/solid/chevron-left.svg') no-repeat center/contain; mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/solid/chevron-left.svg') no-repeat center/contain }
.wowxp-ico-right{ -webkit-mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/solid/chevron-right.svg') no-repeat center/contain; mask:url('https://cdn.jsdelivr.net/npm/heroicons@2.1.5/24/solid/chevron-right.svg') no-repeat center/contain }
.wowxp-lb-track{position:relative; width:100%; height:100%; overflow:hidden; touch-action:pan-y}
.wowxp-lb-slides{display:flex; height:100%; will-change:transform; transition:transform .28s cubic-bezier(.2,.8,.2,1)}
.wowxp-lb-slide{min-width:100%; height:100%; display:flex; align-items:center; justify-content:center; padding:0}
.wowxp-lb-img{max-width:100vw; max-height:100vh; object-fit:contain; border-radius:0; box-shadow:0 30px 60px rgba(0,0,0,.25); transform:scale(1); transition:transform .2s ease}

@media (min-width:992px){
.wowxp-lb-arrow{position:fixed; top:50%; transform:translateY(-50%); width:52px; height:52px; border-radius:14px; background:rgba(255,255,255,.14); border:0; color:#fff; display:flex; align-items:center; justify-content:center; z-index:2}
  .wowxp-lb-prev{left:16px}
  .wowxp-lb-next{right:16px}
}
</style>
