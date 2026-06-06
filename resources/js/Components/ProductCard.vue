<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useCart } from '@/stores/cart'
import { fetchProductReviewSummary } from '@/services/reviews'

const props = defineProps({
  product: { type: Object, required: true },
  fluid: { type: Boolean, default: false },
  size: { type: String, default: 'md' }, // 'md' | 'xl'
})

const cart = useCart()
const eventImageLoaded = ref(false)
const PREMIUM_BADGE_IMAGE = 'https://studio.weofferwellness.co.uk/storage/uploads/images/78aa908f-334b-45c0-9220-1c4d84053c5e.png'
const ratingVal = ref(typeof props.product?.rating === 'number' ? props.product.rating : null)
const reviewCount = ref(Number(props.product?.review_count || props.product?.reviews_count || 0))
const filledStars = computed(() => Math.max(0, Math.min(5, Math.round(Number(ratingVal.value) || 0))))
const NO_IMAGE_MARKERS = ['no-product-image.jpg', '/images/placeholder.png', 'placeholder.png']

function hasDisplayableImageUrl(value) {
  const url = String(value || '').trim()
  if (!url) return false
  const lower = url.toLowerCase()
  return !NO_IMAGE_MARKERS.some((marker) => lower.includes(marker))
}

function toTitleCase(value) {
  return String(value || '')
    .replace(/\s+/g, ' ')
    .trim()
    .replace(/\w\S*/g, (word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
}

function stripEmoji(value) {
  return String(value || '')
    .replace(/[\u{1F1E6}-\u{1F1FF}\u{1F300}-\u{1FAFF}\u{2600}-\u{27BF}\u{FE0F}\u{200D}]/gu, '')
    .replace(/\s{2,}/g, ' ')
    .trim()
}

function normalizePlanKey(value) {
  const normalized = String(value || '')
    .trim()
    .toLowerCase()
    .replace(/[_\s]+/g, '-')

  const map = {
    community: 'starter',
    starter: 'starter',
    standard: 'starter',
    'free-starter': 'starter',
    'starter-package': 'starter',
    core: 'business-accelerator',
    businessaccelerator: 'business-accelerator',
    'business-accelerator-package': 'business-accelerator',
    premium: 'premium-accelerator',
    premiumaccelerator: 'premium-accelerator',
    'premium-accelerator-package': 'premium-accelerator',
    'become-partner': 'become-partner',
    partner: 'become-partner',
  }

  return map[normalized] || normalized
}

function resolvePlanKey(p) {
  const candidates = [
    p?.plan_key,
    p?.plan_label,
    p?.vendor?.plan_key,
    p?.vendor?.plan_label,
    p?.vendor_details?.plan_key,
    p?.vendor_details?.plan_label,
    p?.vendor?.user?.tier?.tier,
    p?.vendor?.user?.account_type,
  ]

  for (const candidate of candidates) {
    const normalized = normalizePlanKey(candidate)
    if (normalized) {
      return normalized
    }
  }

  return ''
}

function normalizeMoneyValue(value) {
  const n = Number(value)
  if (!Number.isFinite(n) || n <= 0) return null
  let price = n
  if (price > 1000 && price % 100 === 0) {
    price /= 100
  }
  return price
}

function fmtCurrency(value) {
  const n = normalizeMoneyValue(value)
  if (n === null) return null
  return new Intl.NumberFormat(undefined, { style: 'currency', currency: props.product?.currency || 'GBP' }).format(n)
}

function deriveType(p) {
  const typeName = typeof p?.type === 'object'
    ? (p?.type?.name || p?.type?.slug || '')
    : p?.type
  const raw = String(
    p?.type?.name
    || p?.type_label
    || p?.type_name
    || typeName
    || p?.product_type
    || '',
  ).trim().toLowerCase()
  const eventType = String(p?.when?.type || p?.event?.type || '').toLowerCase()
  if (eventType === 'event') return 'event'
  if (hasEventSchedule(p)) return 'event'
  if (raw) {
    if (raw === 'experience' || raw === 'experiences') return 'therapy'
    if (raw.includes('workshop')) return 'workshop'
    if (raw.includes('class')) return 'class'
    if (raw.includes('event')) return 'event'
    if (raw.includes('retreat')) return 'retreat'
    if (raw.includes('therapy')) return 'therapy'
  }

  const url = String(p?.url || '').toLowerCase()
  if (url.includes('/offerings/')) return 'therapy'
  if (url.includes('/events/')) return 'event'
  if (url.includes('/workshops/')) return 'workshop'
  if (url.includes('/classes/')) return 'class'
  if (url.includes('/retreats/')) return 'retreat'
  if (url.includes('/therapies/')) return 'therapy'

  const cat = String(p?.category?.name || p?.category?.label || p?.category?.title || p?.category || p?.category_name || '').toLowerCase()
  if (cat.includes('event')) return 'event'
  if (cat.includes('workshop')) return 'workshop'
  if (cat.includes('class')) return 'class'
  if (cat.includes('retreat')) return 'retreat'
  if (cat.includes('therapy') || cat.includes('experience')) return 'therapy'
  return 'therapy'
}

function cleanLocation(raw) {
  const s = String(raw || '').trim()
  if (!s) return null
  if (s.toLowerCase() === 'online') return 'Online'
  const parts = s
    .replace(/,?\s*(united kingdom|uk)$/i, '')
    .split(',')
    .map((item) => item.trim())
    .filter(Boolean)
  if (!parts.length) return null
  return parts.length > 1 ? `${parts[parts.length - 1]}, ${parts[parts.length - 2]}` : parts[0]
}

function locationFromProduct(p) {
  const loc = p?.location || p?.location_name || p?.venue || null
  if (loc) return cleanLocation(loc)

  if (Array.isArray(p?.locations)) {
    const first = p.locations.find((value) => String(value || '').trim())
    if (first) {
      if (typeof first === 'string') return cleanLocation(first)
      return cleanLocation(first.name || first.label || first.title || first.city || first.location || '')
    }
  }

  return null
}

function hasOnline(p) {
  const loc = String(p?.location || '').toLowerCase()
  const fmt = String(p?.format || '').toLowerCase()
  if (loc === 'online' || fmt.includes('online')) return true
  if (Array.isArray(p?.locations) && p.locations.some((value) => String(value || '').toLowerCase() === 'online')) return true
  if (Array.isArray(p?.tags) && p.tags.some((tag) => String(tag || '').toLowerCase() === 'online')) return true
  return false
}

function formatAvailabilityLabel(p, locationText) {
  const online = hasOnline(p)
  const physical = !!(locationText && String(locationText).toLowerCase() !== 'online')
  if (online && physical) return 'Online & In-person'
  if (online) return 'Online'
  return 'In-person'
}

function typeLabelFor(type) {
  switch (type) {
    case 'therapy': return 'Therapy'
    case 'event': return 'Event'
    case 'workshop': return 'Workshop'
    case 'class': return 'Class'
    case 'retreat': return 'Retreat'
    default: return 'Therapy'
  }
}

function eventSourceFromProduct(p) {
  const base = p && typeof p === 'object' ? p : {}
  const nested = p?.when?.event || p?.event || {}
  return {
    ...base,
    ...(nested && typeof nested === 'object' ? nested : {}),
  }
}

function hasEventSchedule(p) {
  const event = eventSourceFromProduct(p)
  return Boolean(
    String(event?.type || p?.when?.type || '').toLowerCase() === 'event'
    || event?.date
    || event?.start_date
    || event?.end_date
    || event?.start_time
    || event?.end_time
    || p?.date
    || p?.start_date
    || p?.end_date
    || p?.start_time
    || p?.end_time
    || (Array.isArray(event?.dates) && event.dates.length)
    || (Array.isArray(event?.upcoming_dates) && event.upcoming_dates.length)
    || (Array.isArray(event?.availability_dates) && event.availability_dates.length)
  )
}

function parseEventDateTime(dateValue, timeValue) {
  if (!dateValue) return null
  const raw = timeValue
    ? `${dateValue}T${timeValue}:00`
    : (/[T\s]/.test(String(dateValue)) ? String(dateValue) : `${dateValue}T00:00:00`)
  const parsed = new Date(raw)
  return Number.isNaN(parsed.getTime()) ? null : parsed
}

function formatEventRange(startDate, startTime, endDate, endTime) {
  const start = parseEventDateTime(startDate, startTime)
  const end = parseEventDateTime(endDate || startDate, endTime || startTime)
  if (!start || !end) return null

  const sameDay = start.toDateString() === end.toDateString()
  const sameYear = start.getFullYear() === end.getFullYear()
  const startDateLabel = start.toLocaleDateString(undefined, { month: 'short', day: 'numeric', ...(sameYear ? {} : { year: 'numeric' }) })
  const endDateLabel = end.toLocaleDateString(undefined, { month: 'short', day: 'numeric', ...(sameYear ? {} : { year: 'numeric' }) })

  if (startTime || endTime) {
    const startTimeLabel = start.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' })
    const endTimeLabel = end.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' })
    if (sameDay) {
      return `${startDateLabel}, ${startTimeLabel} – ${endTimeLabel}`
    }
    return `${startDateLabel}, ${startTimeLabel} – ${endDateLabel}, ${endTimeLabel}`
  }

  if (sameDay) {
    return startDateLabel
  }

  return `${startDateLabel} – ${endDateLabel}`
}

function buildEventTileUrl(baseUrl, isoDate) {
  if (!baseUrl) return isoDate ? `?date=${isoDate}` : '#'
  if (!isoDate) return baseUrl
  if (String(baseUrl).includes('?')) return `${baseUrl}&date=${isoDate}`
  return `${baseUrl}?date=${isoDate}`
}

function eventTilesFromDate(value, baseUrl) {
  if (!value) return []

  if (typeof value === 'string') {
    const parsed = parseEventDateTime(value, null)
    if (!parsed) return []
    const iso = parsed.toISOString().slice(0, 10)
    return [{
      month: parsed.toLocaleDateString(undefined, { month: 'short' }),
      day: String(parsed.getDate()).padStart(2, '0'),
      url: buildEventTileUrl(baseUrl, iso),
    }]
  }

  if (typeof value === 'object') {
    const startValue = value.date || value.start_date || value.start || value.value || null
    const startTime = value.start_time || value.time || null
    const endValue = value.end_date || value.finish_date || null
    const endTime = value.end_time || null
    const month = value.month || value.mon || value.short_month
    const dayValue = value.day ?? value.date_day ?? value.day_of_month
    const explicitUrl = value.url || value.href || null
    const startParsed = parseEventDateTime(startValue, startTime)
    const tiles = []

    if (month && dayValue !== undefined && dayValue !== null) {
      tiles.push({
        month: String(month),
        day: String(dayValue).padStart(2, '0'),
        url: explicitUrl || buildEventTileUrl(baseUrl, startParsed ? startParsed.toISOString().slice(0, 10) : null),
      })
    } else if (startParsed) {
      const iso = startParsed.toISOString().slice(0, 10)
      tiles.push({
        month: startParsed.toLocaleDateString(undefined, { month: 'short' }),
        day: String(startParsed.getDate()).padStart(2, '0'),
        url: explicitUrl || buildEventTileUrl(baseUrl, iso),
      })
    }

    const endParsed = parseEventDateTime(endValue || startValue, endTime || startTime)
    if (startParsed && endParsed && startParsed.toDateString() !== endParsed.toDateString()) {
      const endIso = endParsed.toISOString().slice(0, 10)
      tiles.push({
        month: endParsed.toLocaleDateString(undefined, { month: 'short' }),
        day: String(endParsed.getDate()).padStart(2, '0'),
        url: buildEventTileUrl(baseUrl, endIso),
      })
    }

    return tiles
  }

  return []
}

function eventDateTilesForProduct(p, baseUrl) {
  const event = eventSourceFromProduct(p)
  const rawSources = []
  if (Array.isArray(event?.dates)) rawSources.push(...event.dates)
  if (Array.isArray(event?.upcoming_dates)) rawSources.push(...event.upcoming_dates)
  if (Array.isArray(event?.availability_dates)) rawSources.push(...event.availability_dates)

  const tiles = rawSources.reduce((acc, item) => acc.concat(eventTilesFromDate(item, baseUrl)), [])

  if (tiles.length) {
    const start = parseEventDateTime(event?.start_date || event?.date || null, event?.start_time)
    const end = parseEventDateTime(event?.end_date || event?.start_date || event?.date || null, event?.end_time)
    if (start && end && start.toDateString() !== end.toDateString()) {
      const endIso = end.toISOString().slice(0, 10)
      const hasEndTile = tiles.some((tile) => String(tile.url || '').includes(endIso))
      if (!hasEndTile) {
        tiles.push({
          month: end.toLocaleDateString(undefined, { month: 'short' }),
          day: String(end.getDate()).padStart(2, '0'),
          url: buildEventTileUrl(baseUrl, endIso),
        })
      }
    }

    return tiles.length > 4
      ? [...tiles.slice(0, 3), { month: 'More', day: `+${tiles.length - 3}`, url: `${baseUrl || '#'}#dates`, more: true }]
      : tiles.slice(0, 4)
  }

  const start = parseEventDateTime(event?.start_date || event?.date || null, event?.start_time)
  const end = parseEventDateTime(event?.end_date || event?.start_date || event?.date || null, event?.end_time)
  const fallbackTiles = []
  if (start) {
    fallbackTiles.push({
      month: start.toLocaleDateString(undefined, { month: 'short' }),
      day: String(start.getDate()).padStart(2, '0'),
      url: buildEventTileUrl(baseUrl, start.toISOString().slice(0, 10)),
    })
  }
  if (end && start && end.toDateString() !== start.toDateString()) {
    fallbackTiles.push({
      month: end.toLocaleDateString(undefined, { month: 'short' }),
      day: String(end.getDate()).padStart(2, '0'),
      url: buildEventTileUrl(baseUrl, end.toISOString().slice(0, 10)),
    })
  }
  return fallbackTiles.slice(0, 4)
}

function legacyLabel(value) {
  const raw = String(value ?? '').trim()
  if (!raw) return ''
  const normalized = raw.toLowerCase().replace(/[_-]+/g, ' ').replace(/\s+/g, ' ').trim()
  const map = {
    therapies: 'Therapy',
    therapy: 'Therapy',
    workshops: 'Workshop',
    workshop: 'Workshop',
    events: 'Event',
    event: 'Event',
    classes: 'Class',
    class: 'Class',
    retreats: 'Retreat',
    retreat: 'Retreat',
    experiences: 'Experience',
    experience: 'Experience',
  }
  return map[normalized] || toTitleCase(normalized)
}

function categoryLabelForProduct(p, typeLabel) {
  const cat = p?.category?.name || p?.category?.label || p?.category?.title || p?.category_name || p?.category_label || p?.category
  if (typeof cat === 'string' && cat.trim()) return legacyLabel(cat)
  if (cat && typeof cat === 'object') {
    const first = [cat.name, cat.label, cat.title, cat.slug].find((value) => typeof value === 'string' && value.trim())
    if (first) return legacyLabel(first)
  }
  if (Array.isArray(p?.categories) && p.categories.length) {
    const first = String(p.categories[0] || '').trim()
    if (first) return legacyLabel(first)
  }
  return typeLabel
}

function providerFor(p) {
  const provider =
    p?.vendor_name
    || p?.practitioner_name
    || p?.provider
    || p?.vendor?.name
    || p?.vendor?.vendor_name
    || null
  return provider ? toTitleCase(String(provider).replace(/[_-]/g, ' ')) : null
}

function isGiftCardProduct(p) {
  const haystack = [
    p?.title,
    p?.name,
    p?.summary,
    p?.benefit,
    p?.description,
    p?.excerpt,
    p?.slug,
    p?.category?.name,
    p?.category_name,
    p?.category_label,
    p?.type?.name,
    p?.product_type,
    p?.fomo_text,
  ]
    .filter(Boolean)
    .join(' ')
    .toLowerCase()

  return /gift\s*card|giftcard|voucher|e-?gift/i.test(haystack)
}

function imageFor(p) {
  return (
    p?.image
    || p?.image_url
    || p?.media?.[0]?.url
    || p?.media?.[0]?.original_url
    || p?.media?.[0]?.path
    || '/images/placeholder.png'
  )
}

function urlFor(p) {
  if (p?.url) return p.url
  const id = p?.id
  if (!id) return '#'
  const slug = String(p?.title || p?.name || id).toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')
  return `/offerings/${id}-${slug}`
}

function defaultAvailabilityDays(p) {
  const user = p?.vendor?.user || p?.user || null
  const source = user?.defaultAvailability || user?.default_availability || []
  const rows = Array.isArray(source) ? source : Object.values(source || {})
  const days = []

  rows.forEach((row) => {
    const available = row?.is_available ?? row?.available ?? true
    if (String(available).toLowerCase() === 'false' || available === 0 || available === '0') return

    let day = row?.day_of_week ?? row?.day ?? row?.weekday
    if (day == null) return
    if (typeof day === 'string' && !/^\d+$/.test(day)) {
      const lookup = {
        mon: 1, monday: 1,
        tue: 2, tues: 2, tuesday: 2,
        wed: 3, wednesday: 3,
        thu: 4, thur: 4, thurs: 4, thursday: 4,
        fri: 5, friday: 5,
        sat: 6, saturday: 6,
        sun: 0, sunday: 0,
      }
      day = lookup[String(day).toLowerCase().trim()] ?? null
    } else {
      day = Number(day)
      if (day === 7) day = 0
    }

    if (day != null && !Number.isNaN(day)) days.push(Number(day))
  })

  const order = [1, 2, 3, 4, 5, 6, 0]
  return [...new Set(days)].sort((a, b) => order.indexOf(a) - order.indexOf(b))
}

const isBusinessAccelerator = computed(() => resolvePlanKey(props.product) === 'business-accelerator')

async function refreshReviews() {
  const id = props.product?.id
  if (!id) return
  try {
    const { rating, review_count } = await fetchProductReviewSummary(id)
    if (typeof rating === 'number') ratingVal.value = rating
    if (typeof review_count === 'number') reviewCount.value = review_count
  } catch {
    // Keep the server-provided summary if the fetch fails.
  }
}

watch(
  () => props.product?.id,
  () => {
    ratingVal.value = typeof props.product?.rating === 'number' ? props.product.rating : null
    reviewCount.value = Number(props.product?.review_count || props.product?.reviews_count || 0)
    refreshReviews()
  }
)

onMounted(refreshReviews)

const type = computed(() => deriveType(props.product))
const typeLabel = computed(() => typeLabelFor(type.value))
const title = computed(() => String(props.product?.title || props.product?.name || 'Untitled'))
const titleFormatted = computed(() => toTitleCase(title.value))
const image = computed(() => imageFor(props.product))
const hasDisplayableImage = computed(() => {
  const candidates = [
    props.product?.image,
    props.product?.image_url,
    props.product?.featured_image,
    props.product?.media?.[0]?.url,
    props.product?.media?.[0]?.original_url,
    props.product?.media?.[0]?.path,
  ]
  return candidates.some(hasDisplayableImageUrl)
})
const url = computed(() => urlFor(props.product))
const giftCardUrl = '/giftcards'
const provider = computed(() => providerFor(props.product))
const categoryLabel = computed(() => categoryLabelForProduct(props.product, typeLabel.value))
const isGiftCard = computed(() => isGiftCardProduct(props.product))
const locationText = computed(() => locationFromProduct(props.product))
const availabilityDays = computed(() => defaultAvailabilityDays(props.product))
const online = computed(() => hasOnline(props.product))
const physicalLocations = computed(() => {
  if (Array.isArray(props.product?.locations)) {
    return props.product.locations
      .map((value) => {
        if (!value) return null
        if (typeof value === 'string') return cleanLocation(value)
        return cleanLocation(value.name || value.label || value.title || value.city || value.location || '')
      })
      .filter(Boolean)
      .filter((value, index, array) => array.indexOf(value) === index)
      .filter((value) => String(value).toLowerCase() !== 'online')
  }
  return locationText.value && String(locationText.value).toLowerCase() !== 'online' ? [locationText.value] : []
})
const exclusiveOnline = computed(() => online.value && physicalLocations.value.length === 0)
const remainingCount = computed(() => Math.max(0, physicalLocations.value.length - (physicalLocations.value[0] ? 1 : 0)))
const price = computed(() => fmtCurrency(
  props.product?.variants_min_price
  ?? props.product?.price_min
  ?? props.product?.price
  ?? props.product?.base_price
  ?? 0
) || '£0.00')
const giftCardPrice = computed(() => {
  const raw = normalizeMoneyValue(props.product?.variants_min_price || props.product?.price_min || props.product?.price || props.product?.base_price || 0)
  if (raw !== null) {
    return new Intl.NumberFormat(undefined, { style: 'currency', currency: props.product?.currency || 'GBP' }).format(raw)
  }
  return 'Gift cards'
})
const description = computed(() => {
  const raw = props.product?.benefit || props.product?.summary || props.product?.description || props.product?.excerpt || ''
  return stripEmoji(raw)
})
const giftCardDescription = computed(() => description.value || 'A flexible digital gift card you can send instantly.')
const nextLabel = computed(() => props.product?.next_label || props.product?.next || null)
const fomoText = computed(() => String(props.product?.fomo_text || '').trim())
const signalText = computed(() => {
  if (fomoText.value) return fomoText.value
  if (exclusiveOnline.value) return 'Exclusively online'
  if (remainingCount.value > 0) return `+${remainingCount.value} more locations`
  if (nextLabel.value) return `Next: ${nextLabel.value}`
  return null
})
const calendarLabel = computed(() => {
  if (availabilityDays.value.length) return 'Availability calendar'
  return 'Request day/time'
})
const calendarNote = computed(() => {
  if (availabilityDays.value.length) return 'Live calendar'
  return 'Practitioner confirms'
})
const availabilityClass = computed(() => (availabilityDays.value.length ? 'has-availability' : 'needs-availability'))
const availabilityLabel = computed(() => formatAvailabilityLabel(props.product, locationText.value))
const formatLabel = computed(() => {
  if (online.value && locationText.value && String(locationText.value).toLowerCase() !== 'online') return 'Online & In-person'
  if (online.value) return 'Online'
  return locationText.value || 'In-person'
})
const isFluid = computed(() => props.fluid ? 'is-fluid' : '')
const isEvent = computed(() => type.value === 'event')
const isPastEvent = computed(() => Boolean(props.product?.is_past_event || props.product?.display_is_past))
const eventData = computed(() => eventSourceFromProduct(props.product))
const eventRangeLabel = computed(() => formatEventRange(
  eventData.value?.start_date || eventData.value?.date || null,
  eventData.value?.start_time || null,
  eventData.value?.end_date || eventData.value?.start_date || eventData.value?.date || null,
  eventData.value?.end_time || null,
))
const eventDateTiles = computed(() => eventDateTilesForProduct(eventData.value, url.value))
const eventDisplayTiles = computed(() => {
  return eventDateTiles.value
    .filter((tile) => tile && !tile.placeholder && tile.month !== 'Soon' && tile.day !== '—')
    .slice(0, 4)
    .map((tile) => ({ ...tile }))
})
const eventBadgeTile = computed(() => {
  const start = parseEventDateTime(eventData.value?.start_date || eventData.value?.date || null, eventData.value?.start_time || null)
  if (start) {
    return {
      month: start.toLocaleDateString(undefined, { month: 'short' }),
      day: String(start.getDate()).padStart(2, '0'),
    }
  }
  const tile = eventDisplayTiles.value.find((item) => item && !item.more && !item.placeholder)
  return tile || { month: 'Soon', day: '—' }
})
const eventTileCount = computed(() => eventDisplayTiles.value.length)
const eventAvailabilityTitle = computed(() => {
  if (eventTileCount.value > 1) return 'Fixed dates'
  if (eventData.value?.start_date || eventData.value?.date) return 'Fixed date'
  return 'Event dates'
})
const eventAvailabilityNote = computed(() => {
  if (eventRangeLabel.value) return eventRangeLabel.value
  if (eventTileCount.value > 1) return 'Choose a date'
  return 'View dates'
})
const eventLocationLabel = computed(() => locationText.value || formatLabel.value || 'In-person')
const eventLoading = computed(() => isEvent.value && hasDisplayableImage.value && !eventImageLoaded.value)

watch(
  () => [
    props.product?.id,
    props.product?.image,
    props.product?.image_url,
    props.product?.featured_image,
    props.product?.media?.[0]?.url,
    props.product?.media?.[0]?.original_url,
    props.product?.media?.[0]?.path,
  ],
  () => {
    eventImageLoaded.value = !hasDisplayableImage.value
  },
  { immediate: true }
)

function openGiftCards() {
  if (typeof window !== 'undefined') {
    window.location.href = giftCardUrl
  }
}

function addToCart() {
  const normalizedPrice = normalizeMoneyValue(props.product?.variants_min_price || props.product?.price_min || props.product?.price || 0) || 0
  cart.add({
    id: props.product?.id || title.value,
    product_id: props.product?.id || null,
    title: titleFormatted.value,
    price: normalizedPrice,
    image: image.value,
    url: url.value,
    qty: 1,
  })
}

function formatDayLabel(day) {
  return ['M', 'T', 'W', 'T', 'F', 'S', 'S'][day]
}

function reviewLabel(count) {
  const n = Number(count) || 0
  return `${n} review${n === 1 ? '' : 's'}`
}
</script>

<template>
  <div class="wow-therapy-card-scope" :class="isFluid">
    <a v-if="isGiftCard" :href="giftCardUrl" :class="['wow-card', size === 'md' ? 'md' : '', 'gift-card-card-wrap']" :aria-label="`Gift card ${props.product?.id || titleFormatted}`">
      <article class="therapy-card">
        <div class="therapy-card__media">
          <img v-if="hasDisplayableImage" :src="image" :alt="titleFormatted" loading="lazy">
          <div v-else class="gift-card-card__fallback">Gift Card</div>

          <span class="therapy-card__signal">Digital gift card</span>

          <div class="therapy-card__badges">
            <span class="wow-badge wow-badge--gold">Gift card</span>
            <span class="wow-badge wow-badge--blue">Instant delivery</span>
          </div>
        </div>

        <div class="therapy-card__body">
          <h3 class="therapy-card__title">{{ titleFormatted }}</h3>

          <p v-if="provider" class="therapy-card__provider">with {{ provider }}</p>

          <p class="therapy-card__description">{{ giftCardDescription }}</p>

          <div class="therapy-card__meta">
            <span class="therapy-card__chip therapy-card__chip--online">Instant email delivery</span>
          </div>
        </div>

        <footer class="therapy-card__footer">
          <div class="therapy-card__price">
            <small>From</small>
            <strong>{{ giftCardPrice }}</strong>
          </div>

          <div class="therapy-card__actions">
            <button type="button" class="btn-wow btn-wow--cta btn-sm" @click.prevent.stop="openGiftCards">
              <span class="btn-label">View gift cards</span>
            </button>
          </div>
        </footer>
      </article>
    </a>

    <article v-else-if="isEvent" :class="['wow-card', size === 'md' ? 'md' : '', 'wow-event-card-v4', isFluid, { 'is-loading': eventLoading }]" :aria-label="`Event card ${props.product?.id || titleFormatted}`" :aria-busy="eventLoading ? 'true' : 'false'">
      <a :href="url" class="wow-event-card-v4__link" :aria-label="`View ${titleFormatted}`"></a>

      <div class="wow-event-card-v4__image">
        <img
          v-if="hasDisplayableImage"
          :src="image"
          :alt="titleFormatted"
          loading="lazy"
          @load="eventImageLoaded = true"
          @error="eventImageLoaded = true"
        >
      </div>

      <div class="wow-event-card-v4__shade"></div>

      <div class="wow-event-card-v4__top">
        <span class="wow-event-card-v4__date">
          <span class="wow-event-card-v4__date-month">{{ eventBadgeTile.month }}</span>
          <span class="wow-event-card-v4__date-day">{{ eventBadgeTile.day }}</span>
        </span>

        <div v-if="isBusinessAccelerator" class="premium-badge-holder">
          <div class="premium-badge-drawer">
            <div class="premium-badge-sheen"></div>

            <div class="premium-badge-copy">
              <p class="premium-badge-title">Premium Partner</p>
              <span class="premium-badge-small">Business Accelerator</span>
            </div>

            <button
              type="button"
              class="premium-badge-button"
              aria-label="Business Accelerator Premium Partner"
              title="Premium Partner"
              @click.prevent.stop
            >
              <img :src="PREMIUM_BADGE_IMAGE" alt="Premium Partner rosette">
            </button>
          </div>
        </div>
      </div>

      <div class="wow-event-card-v4__panel">
        <div class="wow-event-card-v4__tags">
          <span class="wow-event-card-v4__tag">{{ categoryLabel }}</span>
          <span class="wow-event-card-v4__tag wow-event-card-v4__tag--blue">{{ typeLabel }}</span>
        </div>

        <h3 class="wow-event-card-v4__title">{{ titleFormatted }}</h3>

        <div class="wow-event-card-v4__hidden">
          <p v-if="provider" class="wow-event-card-v4__provider">with {{ provider }}</p>

          <div v-if="reviewCount > 0" class="rating-row" :aria-label="`Rated ${ratingVal ? Number(ratingVal).toFixed(1) : '0.0'} out of 5`">
            <span class="stars" aria-hidden="true">
              <span
                v-for="i in 5"
                :key="i"
                class="star"
                :class="{ 'star--empty': i > filledStars }"
                :style="{ color: i <= filledStars ? '#f5c84b' : '#d0d5dd' }"
              ></span>
            </span>
            <span>{{ ratingVal ? Number(ratingVal).toFixed(1) : '0.0' }} · {{ reviewLabel(reviewCount) }}</span>
          </div>

          <div class="wow-event-card-v4__meta">
            <span class="wow-event-card-v4__meta-line">
              <span class="wow-event-card-v4__meta-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                  <path d="M12 21s7-4.4 7-11a7 7 0 1 0-14 0c0 6.6 7 11 7 11Z" stroke="currentColor" stroke-width="2"></path>
                  <path d="M12 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" stroke="currentColor" stroke-width="2"></path>
                </svg>
              </span>
              {{ eventLocationLabel }}
            </span>

            <span class="wow-event-card-v4__meta-line">
              <span class="wow-event-card-v4__meta-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                  <path d="M7 3v3M17 3v3M4.5 9.25h15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                  <path d="M6.75 5h10.5C18.77 5 20 6.23 20 7.75v10.5C20 19.77 18.77 21 17.25 21H6.75C5.23 21 4 19.77 4 18.25V7.75C4 6.23 5.23 5 6.75 5Z" stroke="currentColor" stroke-width="2"></path>
                </svg>
              </span>
              {{ eventRangeLabel || eventAvailabilityTitle }}
            </span>
          </div>

          <p class="wow-event-card-v4__description">{{ description || 'Upcoming event details coming soon.' }}</p>

          <div class="wow-event-card-v4__availability">
            <div class="wow-event-card-v4__availability-head">
              <p class="wow-event-card-v4__availability-title">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M7 3v3M17 3v3M4.5 9.25h15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                  <path d="M6.75 5h10.5C18.77 5 20 6.23 20 7.75v10.5C20 19.77 18.77 21 17.25 21H6.75C5.23 21 4 19.77 4 18.25V7.75C4 6.23 5.23 5 6.75 5Z" stroke="currentColor" stroke-width="2"></path>
                </svg>
                {{ eventAvailabilityTitle }}
              </p>

              <p class="wow-event-card-v4__availability-note">{{ eventAvailabilityNote }}</p>
            </div>

            <div class="wow-event-card-v4__availability-grid">
              <template v-for="(tile, index) in eventDisplayTiles" :key="`${tile.placeholder ? 'placeholder' : (tile.url || `${tile.month}-${tile.day}`)}-${index}`">
                <a
                  v-if="!tile.placeholder"
                  :href="tile.url || url"
                  class="wow-event-card-v4__date-mini"
                  :aria-label="`${tile.month} ${tile.day}`"
                >
                  <span class="wow-event-card-v4__date-mini-month">{{ tile.month }}</span>
                  <span class="wow-event-card-v4__date-mini-day">{{ tile.day }}</span>
                </a>
              </template>
            </div>
          </div>
        </div>

        <footer class="wow-event-card-v4__footer">
          <div>
            <span class="wow-event-card-v4__price-label">From</span>
            <span class="wow-event-card-v4__price">{{ price }}</span>
          </div>

          <div class="wow-event-card-v4__actions">
            <a :href="url" class="wow-event-card-v4__book-btn">
              {{ isPastEvent ? 'View details' : 'Book' }}
            </a>
          </div>
        </footer>
      </div>
    </article>

    <article v-else-if="hasDisplayableImage" :class="['wow-card', size === 'md' ? 'md' : '', 'wow-card-v4']" :aria-label="`Offering card ${props.product?.id || titleFormatted}`">
        <div class="therapy-card__media">
          <img :src="image" :alt="titleFormatted" loading="lazy">

          <span v-if="signalText" class="therapy-card__signal">{{ signalText }}</span>

          <div v-if="isBusinessAccelerator" class="premium-badge-holder">
            <div class="premium-badge-drawer">
              <div class="premium-badge-sheen"></div>

              <div class="premium-badge-copy">
                <p class="premium-badge-title">Premium Partner</p>
                <span class="premium-badge-small">Business Accelerator</span>
              </div>

              <button
                type="button"
                class="premium-badge-button"
                aria-label="Business Accelerator Premium Partner"
                title="Premium Partner"
                @click.prevent.stop
              >
                <img :src="PREMIUM_BADGE_IMAGE" alt="Premium Partner rosette">
              </button>
            </div>
          </div>

          <div class="therapy-card__badges">
            <span class="wow-badge wow-badge--gold">{{ categoryLabel }}</span>
            <span class="wow-badge wow-badge--blue">{{ typeLabel }}</span>
          </div>
        </div>

        <div class="therapy-card__body">
          <h3 class="therapy-card__title">{{ titleFormatted }}</h3>

          <p v-if="provider" class="therapy-card__provider">with {{ provider }}</p>

          <div class="rating-row" :aria-label="`Rated ${ratingVal ? Number(ratingVal).toFixed(1) : '0.0'} out of 5`">
            <span class="stars" aria-hidden="true">
              <span
                v-for="i in 5"
                :key="i"
                class="star"
                :class="{ 'star--empty': i > filledStars }"
                :style="{ color: i <= filledStars ? '#f5c84b' : '#d0d5dd' }"
              ></span>
            </span>
            <span>{{ ratingVal ? Number(ratingVal).toFixed(1) : '0.0' }} · {{ reviewLabel(reviewCount) }}</span>
          </div>

          <p class="therapy-card__description">{{ description }}</p>

          <div class="therapy-card__meta">
            <span v-if="online" class="therapy-card__chip therapy-card__chip--online">{{ availabilityLabel }}</span>
            <span v-else class="therapy-card__chip">{{ availabilityLabel }}</span>

            <span v-if="locationText && !exclusiveOnline" class="therapy-card__chip">
              <span class="wow-chip-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><path d="M17.8 13.938h-.011a7 7 0 1 0-11.464.144h-.016l.14.171c.1.127.2.251.3.371L12 21l5.13-6.248c.194-.209.374-.429.54-.659l.13-.155Z"/></svg>
              </span>
              {{ locationText }}
            </span>
          </div>

          <div class="therapy-card__availability" :class="availabilityClass">
            <div class="therapy-card__availability-top">
              <div class="therapy-card__availability-label">
                <span class="wow-chip-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><path d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1.5A2.5 2.5 0 0 1 22 6.5v12A2.5 2.5 0 0 1 19.5 21h-15A2.5 2.5 0 0 1 2 18.5v-12A2.5 2.5 0 0 1 4.5 4H6V3a1 1 0 0 1 1-1Zm12.5 8h-15v8.5a.5.5 0 0 0 .5.5h14a.5.5 0 0 0 .5-.5V10ZM5 6a.5.5 0 0 0-.5.5V8h15V6.5A.5.5 0 0 0 19 6H5Z"/></svg>
                </span>
                <span>{{ calendarLabel }}</span>
              </div>
              <span class="therapy-card__availability-note">{{ calendarNote }}</span>
            </div>

            <div v-if="availabilityDays.length" class="wow-day-strip" aria-label="Availability calendar">
              <span
                v-for="day in [1, 2, 3, 4, 5, 6, 0]"
                :key="day"
                class="wow-day"
                :class="{ 'is-active': availabilityDays.includes(day), 'is-request': !availabilityDays.includes(day) }"
                :title="formatDayLabel(day)"
              >
                {{ formatDayLabel(day) }}
              </span>
            </div>

          </div>
        </div>

        <footer class="therapy-card__footer">
          <div class="therapy-card__price">
            <small>From</small>
            <strong>{{ price }}</strong>
          </div>

          <div class="therapy-card__actions">
            <button type="button" class="btn-wow btn-wow--outline btn-sm js-add-to-cart js-open-cart" @click.prevent.stop="addToCart">
              <span class="btn-label">Add to cart</span>
            </button>
            <a :href="url" class="btn-wow btn-wow--cta btn-sm btn-arrow">
              <span class="btn-label">Book</span>
              <span class="btn-icon-wrap" aria-hidden="true">
                <svg class="btn-icon-hover" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"></path>
                </svg>
                <svg class="btn-icon-default" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                  <path fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12l-4 4m4-4-4-4"></path>
                </svg>
              </span>
            </a>
          </div>
        </footer>
    </article>
  </div>
</template>

<style scoped>
.wow-therapy-card-scope{
  --ink:#101828;
  --muted:#667085;
  --line:#dde3ea;
  --soft:#edf0f2;
  --green:#4f9381;
  --green-dark:#417c6d;
  --green-soft:#e8f5f1;
  --gold-soft:#ffe5b3;
  --gold-text:#6f4b10;
  --blue-soft:#e8f0ff;
  --blue-text:#254a85;
  --rose-soft:#fff1f3;
  --rose-text:#b42318;
  --warm-soft:#fff7ed;
  --warm-text:#b54708;
  --radius:13px !important;
  --shadow:0 12px 34px rgba(16,24,40,.045);
  position:relative;
  overflow:visible;
  background:transparent;
}

.wow-therapy-card-scope.is-fluid{
  width:100%;
}

.wow-therapy-card-scope .wow-card{
  display:block;
  color:inherit;
  text-decoration:none;
  position:relative;
  overflow:visible;
}

.wow-therapy-card-scope .wow-card.md{
  min-width:0;
}

@media (min-width: 621px){
  .wow-therapy-card-scope .wow-card.md{
    width:280px;
    max-width:280px;
    flex:0 0 280px;
    justify-self:start;
    max-height:690px;
    overflow:hidden;
  }
  .wow-therapy-card-scope .therapy-card{
    width:280px;
  }
  .wow-therapy-card-scope .therapy-card{
    max-height:690px;
  }
  .gift-card-card-wrap .therapy-card,
  .gift-card-card-wrap .gift-card-card{
    min-height:599px;
    height:599px;
  }
}

.wow-therapy-card-scope .therapy-card{
  display:flex;
  flex-direction:column;
  min-height:492px;
  overflow:visible;
  background:#fff;
  border:1px solid rgba(16,24,40,.18);
  border-radius:var(--radius);
  box-shadow:var(--shadow);
  transition:transform 180ms ease, border-color 180ms ease, box-shadow 180ms ease;
}

.wow-card:hover .therapy-card,
.wow-card:focus-within .therapy-card{
  transform:translateY(-3px);
  border-color:rgba(79,147,129,.42);
  box-shadow:0 20px 48px rgba(16,24,40,.085);
}

.therapy-card__media{
  position:relative;
  aspect-ratio:1.64/1;
  overflow:hidden;
  border-radius:var(--radius) var(--radius) 0 0;
  background:#eef2f4;
}

.therapy-card__media::after{
  content:"";
  position:absolute;
  inset:0 0 auto 0;
  height:72px;
  pointer-events:none;
  background:linear-gradient(180deg, rgba(16,24,40,.34), rgba(16,24,40,0));
}

.therapy-card__media img{
  width:100%;
  height:100%;
  display:block;
  object-fit:cover;
  transition:transform 240ms ease;
}

.gift-card-card__fallback{
  width:100%;
  height:100%;
  min-height:240px;
  display:flex;
  align-items:center;
  justify-content:center;
  border-radius:12px;
  background:linear-gradient(135deg,#eff8f5,#ffffff);
  color:#417c6d;
  font-size:16px;
  font-weight:800;
  letter-spacing:.08em;
  text-transform:uppercase;
  border:1px solid rgba(16,24,40,.10);
  box-shadow:0 14px 30px rgba(16,24,40,.06);
}

.gift-card-card-wrap .rating-row,
.gift-card-card-wrap .therapy-card__availability{
  display:none;
}

.gift-card-card-wrap .therapy-card__meta .therapy-card__chip:nth-child(2){
  display:none;
}

.wow-card:hover .therapy-card__media img{
  transform:scale(1.035);
}

.therapy-card__signal{
  position:absolute;
  left:10px;
  top:10px;
  z-index:3;
  display:inline-flex;
  align-items:center;
  min-height:28px;
  border-radius:999px;
  padding:0 10px;
  border:1px solid transparent;
  font-size:11.5px;
  font-weight:700;
  white-space:nowrap;
  backdrop-filter:blur(10px);
  box-shadow:0 10px 22px rgba(16,24,40,.10);
  background:rgba(255,247,237,.94);
  color:var(--warm-text);
}

.premium-badge-holder{
  position:absolute;
  top:16px;
  right:16px;
  z-index:20;
  width:52px;
  height:52px;
  overflow:visible;
}

.premium-badge-drawer{
  position:absolute;
  top:0;
  right:0;
  height:52px;
  width:52px;
  display:flex;
  align-items:center;
  justify-content:flex-end;
  gap:9px;
  overflow:hidden;
  border:1px solid rgba(255,255,255,.28);
  border-radius:999px;
  background:rgba(16,151,150,.72);
  box-shadow:
    0 12px 26px rgba(17,24,39,.16),
    inset 0 1px 0 rgba(255,255,255,.14);
  backdrop-filter:blur(14px) saturate(145%);
  -webkit-backdrop-filter:blur(14px) saturate(145%);
  transition:
    width 340ms cubic-bezier(.2,.8,.2,1),
    height 220ms ease,
    border-color 220ms ease,
    background 220ms ease,
    box-shadow 220ms ease,
    transform 220ms ease;
}

.premium-badge-holder:hover .premium-badge-drawer,
.premium-badge-holder:focus-within .premium-badge-drawer{
  width:250px;
  height:56px;
  border-color:rgba(255,255,255,.42);
  background:rgba(16,151,150,.86);
  box-shadow:
    0 20px 48px rgba(17,24,39,.24),
    inset 0 1px 0 rgba(255,255,255,.18);
  transform:translateY(-2px);
}

.premium-badge-copy{
  width:174px;
  min-width:174px;
  padding-left:16px;
  opacity:0;
  transform:translateX(18px);
  transition:
    opacity 220ms ease 90ms,
    transform 280ms cubic-bezier(.2,.8,.2,1) 70ms;
}

.premium-badge-holder:hover .premium-badge-copy,
.premium-badge-holder:focus-within .premium-badge-copy{
  opacity:1;
  transform:translateX(0);
}

.premium-badge-title{
  margin:0;
  color:#ffffff;
  font-size:14px;
  font-weight:400;
  line-height:1.1;
  letter-spacing:0;
  white-space:nowrap;
  text-shadow:0 1px 8px rgba(0,0,0,.16);
}

.premium-badge-small{
  display:block;
  margin-top:3px;
  color:rgba(255,255,255,.88);
  font-size:11px;
  font-weight:400;
  text-transform:uppercase;
  line-height:1.15;
  white-space:nowrap;
  text-shadow:0 1px 8px rgba(0,0,0,.12);
}

.premium-badge-button{
  position:relative;
  z-index:2;
  flex:0 0 46px;
  width:46px;
  height:46px;
  margin-right:3px;
  padding:0;
  border:0;
  border-radius:999px;
  background:rgba(255,255,255,.96);
  display:flex;
  align-items:center;
  justify-content:center;
  cursor:pointer;
  box-shadow:0 7px 18px rgba(17,24,39,.14);
  transition:
    transform 260ms cubic-bezier(.2,.8,.2,1),
    box-shadow 220ms ease,
    background 220ms ease;
}

.premium-badge-holder:hover .premium-badge-button,
.premium-badge-holder:focus-within .premium-badge-button{
  transform:scale(1.055) rotate(9deg);
  background:#ffffff;
  box-shadow:0 10px 24px rgba(17,24,39,.18);
}

.premium-badge-button:hover,
.premium-badge-button:focus-visible{
  outline:none;
}

.premium-badge-button img{
  width:36px;
  height:36px;
  display:block;
  object-fit:contain;
  filter:drop-shadow(0 3px 5px rgba(103,70,14,.24));
}

.premium-badge-sheen{
  position:absolute;
  top:-45%;
  left:-80%;
  width:70px;
  height:140px;
  background:linear-gradient(90deg, transparent, rgba(255,255,255,.36), transparent);
  transform:rotate(24deg);
  opacity:0;
  pointer-events:none;
}

.premium-badge-holder:hover .premium-badge-sheen,
.premium-badge-holder:focus-within .premium-badge-sheen{
  animation:premiumSheen 900ms ease forwards;
}

@keyframes premiumSheen{
  0%{
    left:-80%;
    opacity:0;
  }
  30%{
    opacity:1;
  }
  100%{
    left:112%;
    opacity:0;
  }
}

.therapy-card__badges{
  position:absolute;
  left:10px;
  bottom:10px;
  z-index:3;
  display:flex;
  flex-wrap:wrap;
  gap:6px;
  max-width:calc(100% - 20px);
}

.wow-badge{
  min-height:26px;
  display:inline-flex;
  align-items:center;
  border-radius:999px;
  padding:0 9px;
  font-size:11px;
  font-weight:700;
  backdrop-filter:blur(8px);
}

.wow-badge--gold{
  background:rgba(255,229,179,.96);
  color:var(--gold-text);
  border:1px solid rgba(240,200,121,.9);
}

.wow-badge--blue{
  background:rgba(232,240,255,.96);
  color:var(--blue-text);
  border:1px solid rgba(199,216,251,.9);
}

@media (max-width: 480px){
  .premium-badge-holder{
    top:14px;
    right:14px;
    width:50px;
    height:50px;
  }

  .premium-badge-drawer{
    width:50px;
    height:50px;
  }

  .premium-badge-holder:hover .premium-badge-drawer,
  .premium-badge-holder:focus-within .premium-badge-drawer{
    width:218px;
    height:54px;
  }

  .premium-badge-copy{
    width:144px;
    min-width:144px;
    padding-left:14px;
  }

  .premium-badge-title{
    font-size:13px;
  }

  .premium-badge-small{
    font-size:10px;
  }

  .premium-badge-button{
    flex-basis:44px;
    width:44px;
    height:44px;
  }

  .premium-badge-button img{
    width:34px;
    height:34px;
  }
}

.therapy-card__body{
  flex:1;
  display:flex;
  flex-direction:column;
  gap:10px;
  padding:13px 14px 12px;
  overflow:visible;
}

.therapy-card__title{
  display:-webkit-box;
  min-height:45px;
  margin:0;
  overflow:hidden;
  color:var(--ink);
  font-size:20px;
  font-weight:500;
  line-height:1.08;
  letter-spacing:-.045em;
  -webkit-box-orient:vertical;
  -webkit-line-clamp:2;
}

.wow-card:hover .therapy-card__title{
  color:var(--green);
}

.therapy-card__provider{
  margin:0;
  color:var(--muted);
  font-size:12.75px;
}

.rating-row{
  display:flex;
  align-items:center;
  gap:7px;
  color:#101828;
  font-size:12.25px;
}

.stars{
  display:inline-flex;
  gap:2px;
  white-space:nowrap;
}

.star{
  width:18px;
  height:18px;
  display:inline-block;
  position:relative;
  background:currentColor;
  -webkit-mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23000' d='M11.083 5.104c.35-.8 1.485-.8 1.834 0l1.752 4.022a1 1 0 0 0 .84.597l4.463.342c.9.069 1.255 1.2.556 1.771l-3.33 2.723a1 1 0 0 0-.337 1.016l1.03 4.119c.214.858-.71 1.552-1.474 1.106l-3.913-2.281a1 1 0 0 0-1.008 0L7.583 20.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1 1 0 0 0 6.8 14.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1 1 0 0 0 .84-.597l1.753-4.022Z'/%3E%3C/svg%3E") center/contain no-repeat;
  mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23000' d='M11.083 5.104c.35-.8 1.485-.8 1.834 0l1.752 4.022a1 1 0 0 0 .84.597l4.463.342c.9.069 1.255 1.2.556 1.771l-3.33 2.723a1 1 0 0 0-.337 1.016l1.03 4.119c.214.858-.71 1.552-1.474 1.106l-3.913-2.281a1 1 0 0 0-1.008 0L7.583 20.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1 1 0 0 0 6.8 14.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1 1 0 0 0 .84-.597l1.753-4.022Z'/%3E%3C/svg%3E") center/contain no-repeat;
}

.star.star--empty{
  color:#d0d5dd;
}

.therapy-card__description{
  position:relative;
  display:-webkit-box;
  min-height:calc(1.42em * 3);
  max-height:calc(1.42em * 3);
  margin:0;
  overflow:hidden;
  color:#344054;
  font-size:12.75px;
  line-height:1.42;
  -webkit-box-orient:vertical;
  -webkit-line-clamp:3;
}

.therapy-card__description::after{
  content:"";
  position:absolute;
  left:0;
  right:0;
  bottom:0;
  height:1.15em;
  background:linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(255,255,255,.84) 68%, #fff 100%);
  pointer-events:none;
}

.therapy-card__meta{
  display:flex;
  flex-wrap:wrap;
  gap:6px;
  min-height:26px;
}

.therapy-card__chip{
  min-height:27px;
  display:inline-flex;
  align-items:center;
  gap:6px;
  border:1px solid #e3e8ee;
  border-radius:999px;
  background:#fff;
  color:#596275;
  padding:0 9px;
  font-size:12.25px;
  white-space:nowrap;
}

.therapy-card__chip--online{
  color:#2f6f60;
  border-color:rgba(79,147,129,.24);
  background:var(--green-soft);
}

.wow-chip-icon{
  width:13px;
  height:13px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  color:#667085;
}

.wow-chip-icon svg{
  width:13px;
  height:13px;
  display:block;
  fill:currentColor;
}

.therapy-card__availability{
  margin-top:4px;
  min-height:62px;
  border:1px solid #e3e8ee;
  border-radius:11px;
  background:#fff;
  padding:8px;
}

.therapy-card__availability.has-availability{
  border-color:rgba(79,147,129,.24);
  background:linear-gradient(180deg, rgba(232,245,241,.64), rgba(255,255,255,.94)), #fff;
}

.therapy-card__availability.needs-availability{
  border-style:dashed;
  background:linear-gradient(180deg, rgba(255,247,237,.50), rgba(255,255,255,.96)), #fff;
}

.therapy-card__availability-top{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:9px;
  margin-bottom:7px;
}

.therapy-card__availability-label{
  display:flex;
  align-items:center;
  gap:6px;
  color:#344054;
  font-size:11.7px;
  font-weight:800;
  line-height:1.15;
}

.therapy-card__availability.has-availability .therapy-card__availability-label{
  color:#2f6f60;
}

.therapy-card__availability.needs-availability .therapy-card__availability-label{
  color:var(--warm-text);
}

.therapy-card__availability-note{
  color:#667085;
  font-size:10.9px;
  white-space:nowrap;
}

.wow-day-strip{
  display:grid;
  grid-template-columns:repeat(7, minmax(0, 1fr));
  gap:3px;
}

.wow-day{
  height:21px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  border:1px solid #edf0f2;
  border-radius:6px;
  background:#f8fafc;
  color:#98a2b3;
  font-size:10px;
  font-weight:800;
}

.wow-day.is-active{
  border-color:rgba(79,147,129,.28);
  background:var(--green-soft);
  color:#2f6f60;
}

.wow-day.is-request{
  border-style:dashed;
  background:#fff;
}

.wow-request-action{
  height:23px;
  display:flex;
  align-items:center;
  justify-content:center;
  border:1px dashed rgba(181,71,8,.24);
  border-radius:7px;
  background:#fff;
  color:var(--warm-text);
  font-size:10.8px;
  font-weight:800;
}

.therapy-card__footer{
  display:grid;
  grid-template-columns:1fr;
  gap:10px;
  align-items:center;
  padding:12px 14px;
  border-top:1px solid var(--soft);
  background:#fff;
  border-radius:0 0 var(--radius) var(--radius);
}

.therapy-card__price,
.therapy-card__actions{
  width:100%;
}

.therapy-card__price small{
  display:block;
  color:#667085;
  font-size:12px;
  line-height:1.1;
}

.therapy-card__price strong{
  display:block;
  margin-top:3px;
  color:#101828;
  font-size:23px;
  font-weight:600;
  line-height:1;
  letter-spacing:-.05em;
}

.therapy-card__actions{
  display:flex;
  gap:7px;
  align-items:center;
  flex-wrap:wrap;
}

.therapy-card__actions .btn-wow{
  flex:1;
  min-width:0;
}

.wow-therapy-card-scope .btn-wow{
  height:38px;
  border-radius:4px;
}

.wow-therapy-card-scope .btn-wow--outline{
  border:1px solid rgba(16,24,40,.22);
  background:#fff !important;
  color:rgba(11,18,32,.82);
  box-shadow:0 10px 22px rgba(16,24,40,.08);
}

.wow-therapy-card-scope .btn-wow--cta{
  background:#549483 !important;
  color:#fff;
}

.wow-therapy-card-scope .btn-wow--cta:hover{
  background:#417c6d !important;
}

.wow-therapy-card-scope .btn-wow--outline:hover{
  border-color:rgba(84,148,131,.42);
  color:#549483;
}

@media (max-width: 620px){
  .wow-therapy-card-scope .wow-card.md{
    --card-h:610px !important;
    width:309px;
    max-width:309px;
  }
  .wow-therapy-card-scope .therapy-card{
    min-height:auto;
  }
}
</style>
