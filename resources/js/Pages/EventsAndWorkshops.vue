<script setup>
import { ref, onMounted, onBeforeUnmount, computed, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
import SiteLayout from '@/Layouts/SiteLayout.vue'
import WowButton from '@/Components/ui/WowButton.vue'
import ProductCard from '@/Components/ProductCard.vue'
import { fetchProducts } from '@/services/products'

const loading = ref(true)
const error = ref(false)
const wowEvents = ref([])
const practitionerWorkshops = ref([])

const metrics = [
  { label: 'Avg. NPS', value: '+72' },
  { label: 'Cities served', value: '7' },
  { label: 'Lead time', value: '7 days' },
]

const logos = ['Soho House', 'Pinterest', 'Universal Music', 'The Outnet', 'Lululemon', 'Bloomberg']

const promises = [
  {
    title: 'Tailored to your people',
    text: 'We audit stressors, team culture and accessibility needs to craft a line-up that lands with execs and new joiners alike.',
  },
  {
    title: 'Turnkey production',
    text: 'Venue or studio sourcing, AV, props, scent, light and nourishment handled by WOW producers so you only show up.',
  },
  {
    title: 'Measured outcomes',
    text: 'Pulse surveys, recovery scores and qualitative feedback help you prove impact back to leadership.',
  },
]

const programmes = [
  {
    name: 'Nervous System Reset',
    price: 'From £1,250',
    duration: '90 mins • up to 30 guests',
    desc: 'Facilitated breathwork + sound bath pairing with guided journaling prompts.',
    bullets: ['Portable kit for office or studio', 'Breath regulation toolkit for every guest', 'Optional aftercare playlist & notes'],
  },
  {
    name: 'Peak Energy Lab',
    price: 'From £2,400',
    duration: 'Half day • up to 60 guests',
    desc: 'Contrast therapy education, ice bath rotation, fuelling talk and coaching on nervous-system mastery.',
    bullets: ['Senior breath + performance coaches', 'Brandable wrap + content capture', 'Ideal for launches or sales kick-offs'],
  },
  {
    name: 'Creative Offsite Residency',
    price: 'From £4,800',
    duration: 'Full day • multi-room experience',
    desc: 'Six rotating stations blending somatic release, cacao ritual, live acoustic sound and body percussion.',
    bullets: ['Venue & styling included', 'Hospitality + gifting add-ons', 'Producer + host to guide every group'],
  },
]

const formatHighlights = [
  {
    title: 'Breath + sound immersions',
    text: 'Signature WOW pairing for nervous-system resets and emotional processing.',
    bullets: ['Live gong, crystal bowls and guided voice', 'Can be mat-based or seated boardroom format', 'Option to capture audio for remote teams'],
  },
  {
    title: 'Movement & somatics',
    text: 'Functional mobility, fascia release and playful percussion for teams that sit all day.',
    bullets: ['Standing, seated or floor-based progressions', 'Adaptive flows for pregnancy or injury', 'Props can be shipped to hybrid teams'],
  },
  {
    title: 'Talks & masterclasses',
    text: 'Short, punchy education with demos from credentialed practitioners.',
    bullets: ['30–45 minute formats for all-hands', 'Stream-ready with slides + recordings', 'Expert facilitation + moderated Q&A'],
  },
]

const timelineSteps = [
  {
    stage: 'Week 0',
    title: 'Briefing call',
    detail: 'We learn your goals, audience size, timing and any red flags (accessibility, contraindications, budget).',
  },
  {
    stage: '+48 hrs',
    title: 'Concept + quote',
    detail: 'Receive a curated deck, menu of modalities, visuals and transparent pricing ready for stakeholder sign-off.',
  },
  {
    stage: 'Event week',
    title: 'Production + hosting',
    detail: 'WOW producers manage talent, logistics and on-site experience so you stay with your guests.',
  },
  {
    stage: '+3 days',
    title: 'Aftercare & reporting',
    detail: 'Impact summary, attendee toolkit and rebooking pathways delivered to your inbox.',
  },
]

const weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']

const calendarToday = (() => {
  const d = new Date()
  d.setHours(0, 0, 0, 0)
  return d
})()
const calendarTodayIso = calendarToday.toISOString().slice(0, 10)
const calendarSelectedDate = ref(new Date(calendarToday))
const calendarClock = ref(new Date())
let calendarClockTimer = null

function startOfDay(date) {
  const d = new Date(date)
  d.setHours(0, 0, 0, 0)
  return d
}
function toDate(value) {
  if (!value) return null
  const d = new Date(value)
  return Number.isFinite(d.getTime()) ? d : null
}
function formatTime(date) {
  try {
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
  } catch {
    return ''
  }
}
function dateKey(date) {
  if (!date) return ''
  return startOfDay(date).toISOString().slice(0, 10)
}
function cleanLocation(raw) {
  const s = String(raw || '').trim()
  if (!s) return null
  const lower = s.toLowerCase()
  if (lower === 'online' || lower === 'virtual') return 'Online'
  let x = s.replace(/,?\s*(united kingdom|uk)$/i, '')
  x = x.replace(/\b([A-Z]{1,2}\d{1,2}[A-Z]?)\s*(\d[A-Z]{2})\b/i, '').trim()
  const parts = x.split(',').map(p => p.trim()).filter(Boolean)
  if (parts.length === 0) return s
  const city = parts[parts.length - 1]
  let county = null
  if (parts.length >= 2) {
    const cand = parts[parts.length - 2]
    if (!/\b(road|rd|street|st|farm|lane|ln|ave|avenue|close|cl|drive|dr)\b/i.test(cand)) county = cand
  }
  return county && county.toLowerCase() !== city.toLowerCase() ? `${city}, ${county}` : city
}

const calendarFallbackSeeds = [
  { offset: 3, hour: 19, title: 'Sound Bath Salon', status: 'On sale', location: 'Soho Loft', url: '/events' },
  { offset: 10, hour: 18, title: 'Ice & Breath Lab', status: 'Few spots', location: 'London Fields Studio', url: '/events' },
  { offset: 17, hour: 17, title: 'Leadership Breath Lab', status: 'New drop', location: 'City of London', url: '/events' },
  { offset: 24, hour: 11, title: 'Creative Residency', status: 'Waitlist', location: 'Ashford Estate', url: '/events' },
]

function buildFallbackEvents() {
  return calendarFallbackSeeds.map((seed, idx) => {
    const start = new Date(calendarToday)
    start.setDate(calendarToday.getDate() + seed.offset)
    start.setHours(seed.hour, seed.minute || 0, 0, 0)
    return {
      id: `fallback-${idx}`,
      title: seed.title,
      url: seed.url,
      start,
      location: seed.location,
      status: seed.status,
      variant: 'wow',
      timeLabel: formatTime(start),
    }
  })
}

const calendarFallbackEvents = buildFallbackEvents()

function mapProductToCalendarEvent(item, idx = 0) {
  const start = toDate(item?.date || item?.start_date)
  if (!start) return null
  const location = cleanLocation(item?.location || (Array.isArray(item?.locations) ? item.locations.find(Boolean) : null))
  const status = item?.inventory_status || item?.inventory_label || (item?.availability === 'waitlist' ? 'Waitlist' : null)
  return {
    id: item?.id || item?.slug || `event-${idx}-${start.getTime()}`,
    title: item?.title || 'Upcoming session',
    url: item?.url || '#',
    start,
    location,
    status: status || 'Book now',
    variant: 'wow',
    timeLabel: formatTime(start),
  }
}

const calendarEventsRaw = computed(() => {
  const pool = [...(wowEvents.value || []), ...(practitionerWorkshops.value || [])]
  const parsed = pool.map((item, idx) => mapProductToCalendarEvent(item, idx)).filter(Boolean)
  return parsed.length ? parsed : calendarFallbackEvents
})

const calendarEventsChrono = computed(() => {
  return [...calendarEventsRaw.value].sort((a, b) => a.start - b.start)
})

const calendarEventsByDay = computed(() => {
  const map = new Map()
  calendarEventsRaw.value.forEach((evt) => {
    const iso = evt.start.toISOString().slice(0, 10)
    if (!map.has(iso)) map.set(iso, [])
    map.get(iso).push(evt)
  })
  for (const list of map.values()) {
    list.sort((a, b) => a.start - b.start)
  }
  return map
})

const calendarMonths = computed(() => Array.from({ length: 1 }, (_, i) => buildMonth(i, calendarEventsByDay.value)))

function buildMonth(addMonths = 0, eventsMap = new Map()) {
  const refDate = new Date(calendarToday.getFullYear(), calendarToday.getMonth() + addMonths, 1)
  const month = refDate.getMonth()
  const year = refDate.getFullYear()
  const label = refDate.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' })
  const firstDay = new Date(year, month, 1)
  const startOffset = (firstDay.getDay() + 6) % 7
  const gridStart = new Date(firstDay)
  gridStart.setDate(firstDay.getDate() - startOffset)
  const weeks = []
  for (let w = 0; w < 6; w += 1) {
    const days = []
    for (let d = 0; d < 7; d += 1) {
      const cellDate = new Date(gridStart)
      cellDate.setDate(gridStart.getDate() + w * 7 + d)
      const iso = cellDate.toISOString().slice(0, 10)
      const events = eventsMap.get(iso) || []
      days.push({
        iso,
        date: new Date(cellDate),
        day: cellDate.getDate(),
        currentMonth: cellDate.getMonth() === month,
        isToday: iso === calendarTodayIso,
        events,
        highlight: events.length ? { label: events[0].title, status: events[0].status, variant: events[0].variant || 'wow' } : null,
      })
    }
    weeks.push(days)
  }
  return { label, weeks }
}

const calendarSelectedIso = computed(() => dateKey(calendarSelectedDate.value))
const calendarSelectedEvents = computed(() => {
  const iso = calendarSelectedIso.value
  if (!iso) return []
  return calendarEventsByDay.value.get(iso) || []
})
const calendarSelectedLabel = computed(() => {
  const d = calendarSelectedDate.value
  return d.toLocaleDateString('en-GB', { weekday: 'long', month: 'short', day: 'numeric' })
})
const calendarSelectedIsToday = computed(() => calendarSelectedIso.value === calendarTodayIso)
const calendarTimelineProgress = computed(() => {
  if (!calendarSelectedIsToday.value) return null
  const start = startOfDay(calendarSelectedDate.value)
  const end = new Date(start)
  end.setDate(end.getDate() + 1)
  const now = calendarClock.value
  const total = end - start
  const elapsed = Math.min(Math.max(now - start, 0), total)
  return total ? elapsed / total : null
})
const calendarTimelineStyle = computed(() => {
  if (calendarTimelineProgress.value == null) return null
  const pct = Math.min(0.98, Math.max(0.02, calendarTimelineProgress.value)) * 100
  return { top: pct + '%' }
})

function setCalendarSelectedDate(date) {
  calendarSelectedDate.value = startOfDay(date)
}

watch(calendarEventsChrono, (list) => {
  if (!list.length) return
  const iso = calendarSelectedIso.value
  const hasEvents = iso && (calendarEventsByDay.value.get(iso) || []).length > 0
  if (!iso || !hasEvents) {
    calendarSelectedDate.value = startOfDay(list[0].start)
  }
}, { immediate: true })

async function loadEvents() {
  loading.value = true
  try {
    const [events, workshops] = await Promise.all([
      fetchProducts({ type: 'events', sort: 'popular', limit: 12 }, { throwOnError: true }),
      fetchProducts({ type: 'workshops', sort: 'popular', limit: 12 }, { throwOnError: true }),
    ])
    wowEvents.value = Array.isArray(events) ? events : []
    practitionerWorkshops.value = Array.isArray(workshops) ? workshops : []
    error.value = false
  } catch (e) {
    error.value = true
    wowEvents.value = []
    practitionerWorkshops.value = []
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadEvents()
  calendarClock.value = new Date()
  try {
    calendarClockTimer = window.setInterval(() => {
      calendarClock.value = new Date()
    }, 60 * 1000)
  } catch {}
})

onBeforeUnmount(() => {
  if (calendarClockTimer) {
    try { window.clearInterval(calendarClockTimer) } catch {}
  }
})
</script>

<template>
  <Head title="Events & Workshops">
    <meta name="description" content="Immersive wellness events planned like a creative studio: turnkey production, measurable impact." />
    <link rel="canonical" :href="(typeof window !== 'undefined' ? window.location.origin : '') + '/events-and-workshops'" />
  </Head>
  <SiteLayout>
    <section class="section hero-section">
      <div class="container-fluid hero-container">
        <div class="events-hero">
          <video class="hero-video" src="/videos/wow-events-hero.mp4" autoplay muted loop playsinline preload="auto" aria-hidden="true"></video>
          <div class="hero-overlay"></div>
          <div class="hero-content">
            <div class="hero-copy">
              <div class="kicker">WOW Studio</div>
              <h1>Events &amp; Workshops that actually move people</h1>
              <p class="hero-lede mt-3">From intimate founder salons to 200-person summits, we design multi-sensory moments that lower stress, fuel connection and drive performance.</p>
              <div class="hero-cta">
                <WowButton as="a" href="/contact?topic=events" variant="cta" arrow>Plan my event</WowButton>
                <WowButton as="a" href="#programmes" variant="outline">Explore programmes</WowButton>
              </div>
              <div class="hero-metrics">
                <div v-for="metric in metrics" :key="metric.label" class="metric">
                  <div class="value">{{ metric.value }}</div>
                  <div class="label">{{ metric.label }}</div>
                </div>
              </div>
              <p class="hero-note">Hybrid, office, studio and offsite production across the UK + Europe. Remote teams supported via broadcast kits.</p>
            </div>
            <div class="hero-panel">
              <p class="panel-eyebrow">Concierge production</p>
              <h3>Plug our producers into your next launch, offsite or studio series.</h3>
              <ul>
                <li>Venue &amp; AV sourcing</li>
                <li>Practitioner curation &amp; contracts</li>
                <li>Guest communication &amp; arrival</li>
                <li>On-site host + support crew</li>
              </ul>
              <div class="panel-box">
                <p class="panel-box-copy m-0"><strong>Need an instant quote?</strong><br>Email <a href="mailto:hello@weofferwellness.co.uk" class="link">hello@weofferwellness.co.uk</a> with your date + headcount for a response within one business day.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section calendar-section">
      <div class="container-page">
        <div class="calendar-card calendar-wide">
          <div class="calendar-head">
            <div>
              <p class="eyebrow">Browse by date</p>
              <h3>Next available WOW dates</h3>
            </div>
            <WowButton as="a" href="#wow" variant="ghost" size="sm">Jump to listings</WowButton>
          </div>
          <div class="calendar-layout">
            <div class="calendar-months">
              <div v-for="month in calendarMonths" :key="month.label" class="calendar-month">
                <div class="month-title">{{ month.label }}</div>
                <div class="calendar-weekdays">
                  <span v-for="day in weekdays" :key="day">{{ day }}</span>
                </div>
                <div class="calendar-weeks">
                  <div v-for="(week, index) in month.weeks" :key="index" class="calendar-week">
                    <button
                      v-for="day in week"
                      :key="day.iso"
                      type="button"
                      @click="setCalendarSelectedDate(day.date)"
                      :aria-pressed="calendarSelectedIso === day.iso"
                      :aria-label="day.events.length ? `${day.day} ${month.label} — ${day.events.length} session${day.events.length>1?'s':''}` : `${day.day} ${month.label}`"
                      :class="['calendar-day', { 'is-muted': !day.currentMonth, 'is-today': day.isToday, 'has-highlight': !!day.highlight, 'is-selected': calendarSelectedIso === day.iso }]">
                      <span class="day-number">{{ day.day }}</span>
                      <span v-if="day.highlight" class="day-pill" :data-variant="day.highlight.variant">
                        {{ day.highlight.label }}
                        <small>{{ day.highlight.status }}</small>
                      </span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <div class="calendar-agenda">
              <div class="calendar-agenda-head">
                <div>
                  <p class="eyebrow">Selected date</p>
                  <h4>{{ calendarSelectedLabel }}</h4>
                </div>
                <div v-if="calendarSelectedIsToday" class="calendar-pill">Today</div>
              </div>
              <div class="calendar-agenda-body">
                <div v-if="calendarTimelineStyle" class="calendar-time-indicator" :style="calendarTimelineStyle">
                  <span>Now</span>
                </div>
                <template v-if="calendarSelectedEvents.length">
                  <article v-for="evt in calendarSelectedEvents" :key="evt.id" class="calendar-agenda-item">
                    <div class="time">{{ evt.timeLabel }}</div>
                    <div class="details">
                      <div class="title">{{ evt.title }}</div>
                      <div class="meta">
                        <span v-if="evt.location">{{ evt.location }}</span>
                        <span class="status">{{ evt.status }}</span>
                      </div>
                    </div>
                    <div class="cta">
                      <WowButton as="a" :href="evt.url" size="sm" :arrow="true">Book</WowButton>
                    </div>
                  </article>
                </template>
                <div v-else class="calendar-agenda-empty">
                  <p>No practitioner sessions on this date.</p>
                  <a href="#wow" class="link">See all WOW events</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="wow" class="section">
      <div class="container-page">
        <div class="section-heading">
          <div class="kicker">WOW hosted</div>
          <h2>Immersive gatherings curated by our team</h2>
          <p class="text-ink-600">Tickets include all equipment, aftercare and friendly hosts so your guests feel held from arrival to re-integration.</p>
        </div>
        <div v-if="loading" class="loading-card">Loading WOW events…</div>
        <template v-else>
          <div v-if="wowEvents.length" class="product-grid">
            <ProductCard v-for="event in wowEvents" :key="event.id" :product="event" :fluid="true" />
          </div>
          <div v-else class="loading-card">
            We’re lining up the next series. <a href="/contact?topic=events" class="link">Contact us</a> to hear when it’s live.
          </div>
        </template>
      </div>
    </section>

    <section class="section logos">
      <div class="container-page">
        <p class="text-ink-500">Trusted by teams at</p>
        <div class="logo-row">
          <span v-for="logo in logos" :key="logo" class="logo-pill">{{ logo }}</span>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container-page">
        <div class="section-heading">
          <div class="kicker">Why partners book WOW</div>
          <h2>A studio-level approach to wellness experiences</h2>
          <p class="text-ink-600">We make it effortless to host memorable events that align with your brand, budget and wellbeing goals.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-4">
          <article v-for="promise in promises" :key="promise.title" class="promise-card">
            <h3>{{ promise.title }}</h3>
            <p>{{ promise.text }}</p>
          </article>
        </div>
      </div>
    </section>

    <section id="programmes" class="section section-muted">
      <div class="container-page">
        <div class="section-heading">
          <div class="kicker">Signature programmes</div>
          <h2>High-converting formats that sell out again and again</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-4">
          <article v-for="programme in programmes" :key="programme.name" class="programme-card">
            <div class="card-header">
              <div>
                <h3>{{ programme.name }}</h3>
                <p class="text-ink-600">{{ programme.duration }}</p>
              </div>
              <span class="price">{{ programme.price }}</span>
            </div>
            <p class="mt-2">{{ programme.desc }}</p>
            <ul>
              <li v-for="item in programme.bullets" :key="item">{{ item }}</li>
            </ul>
          </article>
        </div>
        <div class="mt-4 flex flex-wrap gap-3">
          <WowButton as="a" href="/contact?topic=events" variant="cta" arrow>Request a proposal</WowButton>
          <WowButton as="a" href="/corporate/wellbeing-workshops" variant="ghost">See corporate offering</WowButton>
        </div>
      </div>
    </section>

    <section id="practitioner" class="section">
      <div class="container-page">
        <div class="section-heading">
          <div class="kicker">Practitioner residencies</div>
          <h2>Bring trusted facilitators into your space</h2>
          <p class="text-ink-600">From in-office breath stations to pop-up studios, we place expert practitioners with full safeguarding and insurance handled for you.</p>
        </div>
        <div v-if="loading" class="loading-card">Loading workshops…</div>
        <template v-else>
          <div v-if="practitionerWorkshops.length" class="product-grid">
            <ProductCard v-for="workshop in practitionerWorkshops" :key="workshop.id" :product="workshop" :fluid="true" />
          </div>
          <div v-else class="loading-card">
            Workshops are being added now. <a href="/contact?topic=events" class="link">Tell us what you need</a> and we’ll match you.
          </div>
        </template>
        <div v-if="error" class="alert mt-4">We couldn’t load live listings. Refresh or email <a href="mailto:hello@weofferwellness.co.uk" class="link">hello@weofferwellness.co.uk</a> for a curated shortlist.</div>
      </div>
    </section>

    <section class="section">
      <div class="container-page">
        <div class="section-heading">
          <div class="kicker">Formats &amp; touchpoints</div>
          <h2>Ways we show up for your people</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-4">
          <article v-for="format in formatHighlights" :key="format.title" class="format-card">
            <h3>{{ format.title }}</h3>
            <p>{{ format.text }}</p>
            <ul>
              <li v-for="point in format.bullets" :key="point">{{ point }}</li>
            </ul>
          </article>
        </div>
      </div>
    </section>

    <section class="section section-muted">
      <div class="container-page">
        <div class="section-heading">
          <div class="kicker">How it works</div>
          <h2>From brief to applause in four steps</h2>
        </div>
        <div class="timeline">
          <article v-for="step in timelineSteps" :key="step.stage" class="timeline-step">
            <div class="badge">{{ step.stage }}</div>
            <h3>{{ step.title }}</h3>
            <p>{{ step.detail }}</p>
          </article>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container-page">
        <div class="cta-panel">
          <div>
            <div class="kicker">Ready to wow your guests?</div>
            <h2>Let’s design an event that sells out fast</h2>
            <p class="text-ink-600">Send us your must-haves and budget. We’ll reply with a concept, moodboard and quote within 48 hours.</p>
          </div>
          <div class="cta-actions">
            <WowButton as="a" href="/contact?topic=events" variant="cta" arrow>Request proposal</WowButton>
            <WowButton as="a" href="mailto:hello@weofferwellness.co.uk" variant="outline">Email hello@weofferwellness.co.uk</WowButton>
          </div>
        </div>
      </div>
    </section>
  </SiteLayout>
</template>

<style scoped>
.hero-section { padding-top: 0; }
.hero-container { padding-left: 1.5rem; padding-right: 1.5rem; }
@media (min-width: 992px){ .hero-container { padding-left: 0; padding-right: 0; } }
.events-hero { position: relative; border-radius: 0; overflow: hidden; min-height: 420px; }
.hero-video { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
.hero-overlay { position: absolute; inset: 0; background: linear-gradient(135deg, rgba(5,10,25,0.85), rgba(14,62,55,0.82)); }
.hero-content { position: relative; z-index: 1; padding: 3rem; display: grid; gap: 2rem; color: #fff; grid-template-columns: minmax(0, 1fr); max-width: 80rem; margin-left: auto; margin-right: auto; }
@media (max-width: 640px){ .hero-content { padding: 2rem; } }
@media (min-width: 992px){ .hero-content { grid-template-columns: minmax(0, 1.2fr) minmax(0, 0.8fr); align-items: start; } }
.hero-copy h1 { font-size: clamp(2.25rem, 4vw, 3.5rem); color: #fff; margin: 0; }
.hero-lede { color: rgba(255,255,255,0.95); font-size: clamp(1.05rem, 2vw, 1.2rem); line-height: 1.6; }
.hero-cta { display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1.75rem; }
.hero-cta :deep(.btn-wow--outline) { background: rgba(255,255,255,0.94); border-color: rgba(255,255,255,0.94); color: var(--ink-900); }
.hero-cta :deep(.btn-wow--outline .btn-label) { color: var(--ink-900); }
.hero-metrics { display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 2rem; }
.metric { min-width: 120px; border: 1px solid rgba(255,255,255,0.25); border-radius: 16px; padding: 0.75rem 1rem; }
.metric .value { font-size: 1.75rem; font-weight: 600; }
.metric .label { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(255,255,255,0.7); }
.hero-note { margin-top: 1rem; font-size: 0.95rem; color: rgba(255,255,255,0.9); }
.hero-panel { background: rgba(255,255,255,0.12); border-radius: 24px; padding: 2rem; box-shadow: inset 0 0 0 1px rgba(255,255,255,0.22); color: rgba(255,255,255,0.95); backdrop-filter: blur(14px); }
.hero-panel h3 { margin-top: 0.4rem; color: #fff; }
.hero-panel ul { margin: 1rem 0 1.5rem; padding-left: 1.2rem; }
.hero-panel li { margin-bottom: 0.35rem; }
.panel-eyebrow { text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.12em; color: rgba(255,255,255,0.7); }
.panel-box { border-radius: 18px; padding: 1rem 1.25rem; background: rgba(255,255,255,0.16); color: rgba(255,255,255,0.95); }
.panel-box-copy { color: #fff; }
.panel-box-copy strong { color: #fff; }
.panel-box-copy .link { color: #fff; text-decoration: underline; font-weight: 600; }

.calendar-section { padding-top: 1rem; }
.calendar-wide { width: 100%; }
.calendar-card { background: #fff; color: var(--ink-900); border-radius: 28px; padding: 1.75rem; box-shadow: 0 30px 80px rgba(15,23,42,0.18); }
.calendar-card .eyebrow { text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.12em; color: var(--ink-500); margin-bottom: 0.15rem; }
.calendar-card h3 { margin: 0; color: var(--ink-900); }
.calendar-head { display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; margin-bottom: 1.25rem; flex-wrap: wrap; }
.calendar-layout { display: flex; flex-direction: column; gap: 1.5rem; }
@media (min-width: 992px){ .calendar-layout { flex-direction: row; } }
.calendar-months { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1fr); flex: 1; }
.calendar-month { border: 1px solid var(--ink-100); border-radius: 20px; padding: 1rem; background: var(--surface); }
.month-title { font-weight: 600; margin-bottom: 0.5rem; }
.calendar-weekdays, .calendar-week { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 0.35rem; }
.calendar-weeks { display: grid; gap: 0.35rem; }
.calendar-weekdays span { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--ink-400); text-align: center; }
.calendar-day { border: 1px solid rgba(15,23,42,0.12); border-radius: 14px; padding: 0.35rem; text-align: left; position: relative; background: #fff; cursor: pointer; transition: border-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease; aspect-ratio: 1 / 1; display: flex; flex-direction: column; gap: 0.3rem; align-items: flex-start; justify-content: flex-start; margin-bottom: 0.3rem; }
.calendar-day:focus-visible { outline: 0; box-shadow: 0 0 0 2px rgba(59,119,104,0.35); border-color: var(--brand-500); }
.calendar-day.is-muted { opacity: 0.4; }
.calendar-day.is-today { border-color: var(--brand-500); box-shadow: 0 0 0 1px color-mix(in srgb, var(--brand-500) 25%, transparent); }
.calendar-day.is-selected { border-color: var(--accent-500); box-shadow: 0 6px 20px rgba(15,23,42,0.12); transform: translateY(-1px); }
.calendar-day.has-highlight { border-color: var(--accent-400); background: linear-gradient(135deg, rgba(240,99,61,0.08), #fff); }
.day-number { font-weight: 600; font-size: 0.95rem; color: var(--ink-800); margin-bottom: 0.1rem; }
.day-pill { display: block; margin-top: 0.1rem; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.08em; border-radius: 10px; padding: 0.1rem 0.3rem; color: #fff; }
.day-pill small { display: block; font-size: 0.55rem; text-transform: none; letter-spacing: normal; }
.day-pill[data-variant="wow"] { background: var(--brand-600); }
.day-pill[data-variant="limited"] { background: var(--accent-600); }
.calendar-agenda { flex: 0 0 360px; border: 1px solid var(--ink-100); border-radius: 24px; padding: 1.25rem; background: var(--surface); display: flex; flex-direction: column; gap: 0.75rem; }
@media (max-width: 991.98px){ .calendar-agenda { flex: 1; } }
.calendar-agenda-head { display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; }
.calendar-agenda-head h4 { margin: 0; font-size: 1.35rem; }
.calendar-agenda-body { position: relative; display: flex; flex-direction: column; gap: 0.75rem; max-height: 360px; overflow-y: auto; padding-right: 0.5rem; }
.calendar-agenda-item { display: flex; gap: 0.9rem; border: 1px solid rgba(15,23,42,0.08); border-radius: 16px; padding: 0.85rem; background: #fff; align-items: flex-start; }
.calendar-agenda-item .time { font-weight: 700; color: #0f172a; width: 72px; flex-shrink: 0; }
.calendar-agenda-item .details { flex: 1; min-width: 0; }
.calendar-agenda-item .details .title { font-weight: 600; margin-bottom: 0.25rem; }
.calendar-agenda-item .details .meta { display: flex; flex-wrap: wrap; gap: 0.5rem; font-size: 0.85rem; color: var(--ink-600); }
.calendar-agenda-item .details .status { font-weight: 600; color: var(--accent-600); }
.calendar-agenda-item .cta { flex-shrink: 0; }
.calendar-agenda-empty { border: 1px dashed var(--ink-200); border-radius: 16px; padding: 1rem; text-align: center; color: var(--ink-600); background: #fff; }
.calendar-time-indicator { position: absolute; left: 16px; right: 16px; height: 2px; background: linear-gradient(90deg, #f97316, #ec4899); box-shadow: 0 0 12px rgba(236,72,153,0.35); }
.calendar-time-indicator span { position: absolute; left: 0; transform: translateY(-10px); font-size: 0.65rem; letter-spacing: 0.08em; text-transform: uppercase; font-weight: 700; color: #f97316; background: #fff; padding: 0 6px; }
.calendar-pill { display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.3rem 0.85rem; border-radius: 999px; border: 1px solid #bbf7d0; background: #ecfdf5; color: #047857; font-weight: 600; font-size: 0.85rem; }
.day-pill[data-variant="soldout"] { background: var(--ink-700); }

.logos { padding-top: 1.5rem; padding-bottom: 1.5rem; }
.logo-row { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 0.5rem; }
.logo-pill { border: 1px solid var(--ink-200); border-radius: 999px; padding: 0.4rem 1.1rem; font-weight: 600; color: var(--ink-600); background: #fff; }

.section-heading { max-width: 50rem; margin-bottom: 1.75rem; }
.section-muted { background: var(--panel); }
.section-muted .container-page { padding-top: 2rem; padding-bottom: 2rem; }

.promise-card, .programme-card, .format-card { border-radius: 24px; background: #fff; border: 1px solid var(--ink-200); padding: 1.75rem; box-shadow: 0 20px 44px rgba(15,23,42,0.06); }
.promise-card h3 { margin-top: 0; margin-bottom: 0.5rem; }

.programme-card .card-header { display: flex; justify-content: space-between; align-items: center; gap: 1rem; }
.programme-card .price { font-weight: 700; color: var(--brand-700); }
.programme-card ul, .format-card ul { margin: 0.75rem 0 0; padding-left: 1.2rem; color: var(--ink-700); }
.programme-card li, .format-card li { margin-bottom: 0.35rem; }

.product-grid { display: grid; gap: 1.25rem; grid-template-columns: repeat(1, minmax(0, 1fr)); }
@media (min-width: 640px){ .product-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (min-width: 992px){ .product-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
.loading-card { border-radius: 20px; border: 1px dashed var(--ink-200); padding: 1.5rem; background: #fff; color: var(--ink-700); }

.alert { border-radius: 16px; padding: 1rem 1.25rem; background: #fff8ed; border: 1px solid color-mix(in srgb, var(--accent-300) 60%, white); color: var(--accent-900); }

.format-card h3 { margin-top: 0; }

.timeline { display: grid; gap: 1rem; }
@media (min-width: 768px){ .timeline { grid-template-columns: repeat(4, minmax(0,1fr)); } }
.timeline-step { border-radius: 22px; background: #fff; padding: 1.5rem; border: 1px solid var(--ink-200); box-shadow: 0 15px 32px rgba(15,23,42,0.05); }
.timeline-step .badge { display: inline-flex; align-items: center; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.1em; background: var(--ink-900); color: #fff; padding: 0.15rem 0.75rem; border-radius: 999px; margin-bottom: 0.75rem; }

.cta-panel { border-radius: 32px; padding: 2.5rem; background: linear-gradient(135deg, #f0fdf4, #ffffff); border: 1px solid color-mix(in srgb, var(--brand-200) 70%, white); display: grid; gap: 1.5rem; }
@media (min-width: 768px){ .cta-panel { grid-template-columns: 2fr 1fr; align-items: center; } }
.cta-actions { display: flex; flex-direction: column; gap: 0.75rem; }
@media (min-width: 640px){ .cta-actions { flex-direction: row; flex-wrap: wrap; } }
</style>
