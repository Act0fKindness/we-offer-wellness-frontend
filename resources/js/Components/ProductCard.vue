<script setup>
import { computed, ref, onMounted, watch } from 'vue'
import { TherapyCard, EventCard, WorkshopCard, RetreatCard } from '@/Components/wow-cards'
import { fetchProductReviewSummary } from '@/services/reviews'

const props = defineProps({
  product: { type: Object, required: true },
  fluid: { type: Boolean, default: false },
  size: { type: String, default: 'md' }, // 'md' | 'xl'
})

function deriveType(p){
  const raw = String(p?.type || p?.product_type || '').trim().toLowerCase()
  if (raw) {
    if (raw === 'experience' || raw === 'experiences') return 'therapy'
    if (raw.includes('workshop')) return 'workshop'
    if (raw.includes('class')) return 'class'
    if (raw.includes('event')) return 'event'
    if (raw.includes('retreat')) return 'retreat'
    if (raw.includes('therapy')) return 'therapy'
  }
  const url = String(p?.url || '').toLowerCase()
  if (url.includes('/events/')) return 'event'
  if (url.includes('/workshops/')) return 'workshop'
  if (url.includes('/classes/')) return 'class'
  if (url.includes('/retreats/')) return 'retreat'
  if (url.includes('/therapies/')) return 'therapy'
  // Fallback: use tags/categories if present
  const cat = String(p?.category || p?.category_name || '').toLowerCase()
  if (cat.includes('event')) return 'event'
  if (cat.includes('workshop')) return 'workshop'
  if (cat.includes('class')) return 'class'
  if (cat.includes('retreat')) return 'retreat'
  if (cat.includes('therapy') || cat.includes('experience')) return 'therapy'
  return 'therapy'
}

const type = computed(() => deriveType(props.product))

function fmt(price) { return new Intl.NumberFormat(undefined, { style: 'currency', currency: props.product.currency || 'GBP' }).format(Number(price)) }
function fmtDate(iso) {
  if (!iso) return null
  try { const d = new Date(iso); return d.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' }) } catch { return null }
}
function fmtRange(start, end) {
  const s = start ? new Date(start) : null
  const e = end ? new Date(end) : null
  if (!s && !e) return null
  if (s && e) {
    const sameMonth = s.getMonth() === e.getMonth() && s.getFullYear() === e.getFullYear()
    const sStr = s.toLocaleDateString(undefined, { month: sameMonth ? 'short' : 'short', day: 'numeric' })
    const eStr = e.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: s.getFullYear() === e.getFullYear() ? undefined : 'numeric' })
    return `${sStr} – ${eStr}`
  }
  return (s || e)?.toLocaleDateString()
}

function cleanLocation(raw) {
  const s = String(raw || '').trim()
  if (!s) return null
  const lower = s.toLowerCase()
  if (lower === 'online') return 'Online'
  let x = s.replace(/,?\s*(united kingdom|uk)$/i, '')
  x = x.replace(/\b([A-Z]{1,2}\d{1,2}[A-Z]?)\s*(\d[A-Z]{2})\b/i, '').trim()
  const parts = x.split(',').map(p => p.trim()).filter(Boolean)
  if (parts.length === 0) return null
  const city = parts[parts.length - 1]
  let county = null
  if (parts.length >= 2) {
    const cand = parts[parts.length - 2]
    if (!/\b(road|rd|street|st|farm|lane|ln|ave|avenue|close|cl|drive|dr)\b/i.test(cand)) { county = cand }
  }
  return county && county.toLowerCase() !== city.toLowerCase() ? `${city}, ${county}` : city
}

const locationText = computed(() => {
  const p = props.product || {}
  const loc = p.location || (Array.isArray(p.locations) ? p.locations.find(v => String(v).trim()) : null)
  if (!loc) return null
  return cleanLocation(loc)
})

// Online/in-person detection and labels
function hasOnline(p){
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
function formatLabelShort(p, locText){
  const online = hasOnline(p)
  const hasPhysical = !!(locText && String(locText).toLowerCase() !== 'online')
  if (online && hasPhysical) return 'Online & In-person'
  if (online) return 'Online'
  return 'In-person'
}

// Emotional families mapping (feeling-first cues + CTAs)
function familyFor(p){
  const title = String(p?.title || '').toLowerCase()
  const tags = Array.isArray(p?.tags) ? p.tags.map(t => String(t).toLowerCase()) : []
  const text = [title, ...(Array.isArray(p?.categories)?p.categories:[]), ...(Array.isArray(p?.keywords)?p.keywords:[]), ...tags].join(' ')
  const has = (re) => re.test(text)
  if (has(/reiki|massage|sound\s*bath|nidra|calm|relax|sleep|breathwork|crystal/)) return 'calm'
  if (has(/coach|coaching|empower|menopause|course|training|goal|confidence|power/)) return 'empower'
  if (has(/trauma|hypno|hypnotherapy|eft|tapping|recover|grief|healing/)) return 'healing'
  if (has(/circle|women'?s\s*circle|drum\s*circle|community|group|together/)) return 'belonging'
  if (has(/facial|acupuncture|skin|beauty|glow|body\s*treatment/)) return 'beauty'
  if (has(/meditation|astrology|journal|journaling|tarot|clarity|focus|mindfulness/)) return 'clarity'
  // Fallback by type
  if (deriveType(p) === 'class') return 'clarity'
  if (deriveType(p) === 'workshop') return 'empower'
  return 'calm'
}

function familyCue(f){
  return ({
    calm: 'A gentle reset for your nervous system',
    empower: 'Tools and momentum for what’s next',
    healing: 'Gentle space to feel held and heal',
    belonging: 'Shared energy, shared growth — together',
    beauty: 'Small rituals that nourish your glow',
    clarity: 'Pause, see clearly, realign within',
  })[f] || ''
}
function familyLabel(f){
  return ({
    calm: 'Calm & Release',
    empower: 'Growth & Empowerment',
    healing: 'Healing & Recovery',
    belonging: 'Connection & Belonging',
    beauty: 'Beauty & Renewal',
    clarity: 'Focus & Clarity',
  })[f] || ''
}
function familyCTA(f){
  return ({
    calm: 'Begin your reset',
    empower: 'Step into your power',
    healing: 'Feel held and heal',
    belonging: 'Join the circle',
    beauty: 'Nourish your glow',
    clarity: 'Find your focus',
  })[f] || 'Learn more'
}
function familyChip(f){
  return ({
    calm: 'Calm reset',
    empower: 'Step forward',
    healing: 'Healing space',
    belonging: 'Find your circle',
    beauty: 'Glow & renew',
    clarity: 'Find clarity',
  })[f] || ''
}
const family = computed(() => familyFor(props.product))

function typeSingularLabel(){
  switch (type.value) {
    case 'therapy': return 'Therapy'
    case 'event': return 'Event'
    case 'workshop': return 'Workshop'
    case 'class': return 'Class'
    case 'retreat': return 'Retreat'
    default: return (type.value || '').charAt(0).toUpperCase() + (type.value || '').slice(1)
  }
}

function categoryLabelForProduct(){
  const p = props.product || {}
  const cat = p.category || p.category_name || p.category_label
  if (typeof cat === 'string' && cat.trim()) return cat.trim()
  if (cat && typeof cat === 'object') {
    const candidates = [cat.name, cat.label, cat.title, cat.slug]
    const first = candidates.find((val) => typeof val === 'string' && val.trim())
    if (first) return first.trim()
  }
  if (Array.isArray(p.categories) && p.categories.length) {
    const first = String(p.categories[0] || '').trim()
    if (first) return first
  }
  return typeSingularLabel()
}

// Derive persons/capacity from product data/options
function derivePersons(p){
  // legacy: keep for display if needed
  if (p?.capacity_max || p?.capacity) {
    const max = Number(p.capacity_max || p.capacity)
    const min = Number(p.capacity_min || 1)
    if (Number.isFinite(max)) return `${min && min !== max ? `${min}–` : ''}${max} persons`
  }
  const optArrays = [p?.options, p?.product_options]
  for (const arr of optArrays) {
    if (Array.isArray(arr)){
      const match = arr.find(o => /person|people|guests?/i.test(String(o?.name || '')))
      if (match){
        const vals = match.values || match.option_values || match?.values_list
        if (Array.isArray(vals) && vals.length){
          const nums = vals.map(v => Number(String(v).replace(/[^0-9]/g,''))).filter(n=>Number.isFinite(n)).sort((a,b)=>a-b)
          if (nums.length){
            const min = nums[0], max = nums[nums.length-1]
            return `${min && min!==max ? `${min}–` : ''}${max} persons`
          }
        }
        const text = match.value || match.label || match.text
        if (text) return `${text}`
      }
    }
  }
  const s = String(p?.option_values || p?.persons || p?.people || '').trim()
  if (s) return s
  return null
}

function toTitleCase(s){
  try { return String(s).replace(/\w\S*/g, t => t.charAt(0).toUpperCase() + t.slice(1).toLowerCase()) } catch { return s }
}
// Identify supported person counts and emit Solo/Couple/Group tags
function personNumbersFromOptions(p){
  const nums = new Set()
  const arrs = [p?.options, p?.product_options]
  for (const arr of arrs){
    if (!Array.isArray(arr)) continue
    const match = arr.find(o => /person|people|guests?/i.test(String(o?.name || o?.meta_name || '')))
    const vals = match ? (match.values || match.option_values || match.values_list) : null
    const texts = Array.isArray(vals) ? vals.map(v => String(v?.value ?? v)) : []
    for (const t of texts){ const n = parseInt(t.replace(/[^0-9]/g,''),10); if (Number.isFinite(n) && n>0) nums.add(n) }
  }
  if (nums.size===0 && (p?.capacity_min || p?.capacity_max)){
    const a = Number(p.capacity_min || 1), b = Number(p.capacity_max || p.capacity || a)
    if (Number.isFinite(a)) nums.add(a)
    if (Number.isFinite(b)) nums.add(b)
  }
  return nums
}
function peopleTagsFor(p){
  const nums = personNumbersFromOptions(p)
  const tags = []
  if (nums.has(1)) tags.push('Solo')
  if (nums.has(2)) tags.push('Couple')
  if ([...nums].some(n=>n>2)) tags.push('Group')
  return tags
}
function categorySingular(raw){
  const s = String(raw || '').trim()
  if (!s) return ''
  const low = s.toLowerCase()
  const map = {
    therapies: 'Therapy', therapy: 'Therapy',
    events: 'Event', event: 'Event',
    workshops: 'Workshop', workshop: 'Workshop',
    classes: 'Class', class: 'Class',
    retreats: 'Retreat', retreat: 'Retreat',
    experiences: 'Experience', experience: 'Experience',
  }
  if (map[low]) return map[low]
  // naive singular: remove trailing 'es' or 's'
  let out = s
  if (/\w+ses$/i.test(s)) out = s.replace(/es$/i, '')
  else if (/\w+s$/i.test(s)) out = s.replace(/s$/i, '')
  return toTitleCase(out)
}
function productCategoryForCTA(){
  const cat = categoryLabelForProduct()
  const t = typeSingularLabel()
  // For non-therapy types, prefer the type name (Class, Workshop, Event, Retreat)
  if (['event','workshop','class','retreat'].includes(String(type.value))) return t
  // For therapy, prefer specific category if provided (e.g., Massage, Reiki)
  const sing = categorySingular(cat)
  // If the category is a generic word equal to the type, fallback to type
  const generic = ['Therapy','Event','Workshop','Class','Retreat','Experience']
  return generic.includes(sing) ? t : sing
}
const ctaLabel = computed(() => `Book ${productCategoryForCTA()}`)

const ratingVal = ref(typeof props.product.rating === 'number' ? props.product.rating : null)
const reviewCount = ref(Number(props.product.review_count || 0))

async function refreshReviews() {
  const id = props.product?.id
  if (!id) return
  const { rating, review_count } = await fetchProductReviewSummary(id)
  if (typeof rating === 'number') ratingVal.value = rating
  if (typeof review_count === 'number') reviewCount.value = review_count
}

onMounted(refreshReviews)
watch(() => props.product?.id, () => { ratingVal.value = props.product?.rating ?? null; reviewCount.value = Number(props.product?.review_count || 0); refreshReviews() })

const common = computed(() => ({
  image: props.product.image,
  alt: props.product.title,
  title: props.product.title,
  rating: ratingVal.value ?? (typeof props.product.rating === 'number' ? props.product.rating : null) ?? undefined,
  hasReviews: (Number(reviewCount.value) || 0) > 0,
  reviewsText: (Number(reviewCount.value) || 0) > 0 ? `${reviewCount.value} reviews` : '',
  ctaHref: props.product.url || '#',
  ctaLabel: ctaLabel.value,
  product: props.product,
}))

const therapyProps = computed(() => {
  const online = hasOnline(props.product)
  const duration = props.product.duration || props.product.duration_text || null
  const persons = derivePersons(props.product)
  const peopleTags = peopleTagsFor(props.product)
  const meta = []
  // primary chip: short emotion-evoking message
  meta.push({ icon: 'bi-tag', text: (familyChip(family.value) || categoryLabelForProduct()), dark: true })
  const fmtShort = formatLabelShort(props.product, locationText.value)
  meta.push({ icon: online ? 'bi-camera-video' : 'bi-geo-alt', text: fmtShort })
  if (locationText.value) meta.push({ icon: 'bi-geo-alt', text: locationText.value })
  if (duration) meta.push({ icon: 'bi-alarm', text: String(duration) })
  if (peopleTags.length) meta.push({ icon: 'bi-people', text: peopleTags.join(' • ') })
  // Ensure we have at least 3 items; add Giftable if sparse
  if (meta.length <= 2) meta.push({ icon: 'bi-bag-heart', text: 'Giftable' })
  const from = Number(props.product.price_max || 0) > Number(props.product.price_min || props.product.price || 0)
  return {
    ...common.value,
    badges: [],
    typeLabel: `Therapy${locationText.value ? ' • ' + locationText.value : ''}`,
    meta,
    policyText: 'Free cancellation 24h',
    price: fmt(props.product.price_min || props.product.price),
    priceNote: from ? '/ from' : '',
  }
})

const eventProps = computed(() => {
  const meta = []
  // primary chip: short emotion-evoking message
  meta.push({ icon: 'bi-tag', text: (familyChip(family.value) || categoryLabelForProduct()), dark: true })
  const fmtShortE = formatLabelShort(props.product, locationText.value)
  meta.push({ icon: fmtShortE.includes('Online') ? 'bi-camera-video' : 'bi-geo-alt', text: fmtShortE })
  if (locationText.value) meta.push({ icon: 'bi-geo-alt', text: locationText.value })
  if (props.product.date) meta.push({ icon: 'bi-clock-history', text: fmtDate(props.product.date) })
  const peopleTagsE = peopleTagsFor(props.product)
  if (peopleTagsE.length) meta.push({ icon: 'bi-people', text: peopleTagsE.join(' • ') })
  else if (props.product.availability_text) meta.push({ icon: 'bi-people', text: props.product.availability_text })
  const detailsMeta = meta
  const policies = [{ icon: 'bi-check2-circle', text: 'Free cancellation 24h', good: true }]
  return {
    ...common.value,
    badges: [],
    typeLabel: `Event${locationText.value ? ' • ' + locationText.value : ''}`,
    detailsMeta,
    policies,
    price: fmt(props.product.price_min || props.product.price),
    priceNote: '/ per session',
    availText: props.product.availability_text || 'Limited',
  }
})

const workshopProps = computed(() => {
  const meta = []
  // primary chip: short emotion-evoking message
  meta.push({ icon: 'bi-tag', text: (familyChip(family.value) || categoryLabelForProduct()), dark: true })
  const fmtShortW = formatLabelShort(props.product, locationText.value)
  meta.push({ icon: fmtShortW.includes('Online') ? 'bi-camera-video' : 'bi-geo-alt', text: fmtShortW })
  if (locationText.value) meta.push({ icon: 'bi-geo-alt', text: locationText.value })
  if (props.product.duration) meta.push({ icon: 'bi-alarm', text: `${props.product.duration}` })
  const peopleTagsW = peopleTagsFor(props.product)
  if (peopleTagsW.length) meta.push({ icon: 'bi-people', text: peopleTagsW.join(' • ') })
  if (props.product.date) meta.push({ icon: 'bi-calendar3', text: fmtDate(props.product.date) })
  if (meta.length <= 2) meta.push({ icon: 'bi-bag-heart', text: 'Giftable' })
  const isClass = type.value === 'class'
  return {
    ...common.value,
    badges: [],
    typeLabel: `${isClass ? 'Class' : 'Workshop'}${locationText.value ? ' • ' + locationText.value : ''}`,
    meta,
    policyText: 'Free reschedule',
    // Use consistent booking CTA for classes/workshops
    ctaLabel: ctaLabel.value,
    price: fmt(props.product.price_min || props.product.price),
    priceNote: isClass ? '' : '/ one-off',
  }
})

const retreatProps = computed(() => {
  const meta = []
  // primary chip: short emotion-evoking message
  meta.push({ icon: 'bi-tag', text: (familyChip(family.value) || categoryLabelForProduct()), dark: true })
  const fmtShortR = formatLabelShort(props.product, locationText.value)
  meta.push({ icon: fmtShortR.includes('Online') ? 'bi-camera-video' : 'bi-geo-alt', text: fmtShortR })
  if (props.product.start_date || props.product.end_date) meta.push({ icon: 'bi-calendar4-week', text: fmtRange(props.product.start_date, props.product.end_date) })
  const peopleTagsR = peopleTagsFor(props.product)
  if (peopleTagsR.length) meta.push({ icon: 'bi-people', text: peopleTagsR.join(' • ') })
  else if (props.product.capacity_max) meta.push({ icon: 'bi-people', text: `Max ${props.product.capacity_max} guests` })
  meta.push({ icon: 'bi-bag-heart', text: 'Giftable' })
  const typeLabel = `Retreat${locationText.value ? ' • ' + locationText.value : ''}`
  return {
    ...common.value,
    badges: [],
    typeLabel,
    meta,
    policyText: 'Free cancellation 24h',
    price: fmt(props.product.price_min || props.product.price),
    priceNote: '/ person',
  }
})
</script>

<template>
  <TherapyCard v-if="type==='therapy'" v-bind="therapyProps" :size="props.size" :class="fluid ? 'is-fluid' : ''" />
  <EventCard v-else-if="type==='event'" v-bind="eventProps" :size="props.size" :class="fluid ? 'is-fluid' : ''" />
  <WorkshopCard v-else-if="type==='workshop' || type==='class'" v-bind="workshopProps" :size="props.size" :class="fluid ? 'is-fluid' : ''" />
  <RetreatCard v-else-if="type==='retreat'" v-bind="retreatProps" :size="props.size" :class="fluid ? 'is-fluid' : ''" />
  <TherapyCard v-else v-bind="therapyProps" :size="props.size" :class="fluid ? 'is-fluid' : ''" />
</template>

<style scoped>
/* No wrapper styles; each card provides its own layout via wow-cards.css */
</style>
