<script setup>
import { ref, computed, onMounted } from 'vue'
import WowButton from '@/Components/ui/WowButton.vue'
import { fetchProducts } from '@/services/products'

const props = defineProps({
  products: { type: Array, default: null },
  title: { type: String, default: 'Book Your Class' },
  limit: { type: Number, default: 8 },
  eyebrow: { type: String, default: 'Book now' },
  subtitle: { type: String, default: '' },
})

const loading = ref(false)
const items = ref([])
// No mode tabs in UI (simpler header); keep unfiltered

function toDate(iso){ try { return iso ? new Date(iso) : null } catch { return null } }
function isSameDay(a, b){
  return a && b && a.getFullYear()===b.getFullYear() && a.getMonth()===b.getMonth() && a.getDate()===b.getDate()
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

function deriveStart(product){
  // Prefer explicit date (datetime), then start_date
  const d = toDate(product?.date) || toDate(product?.start_date)
  return d && isFinite(d.getTime()) ? d : null
}

function isLive(start){
  if (!start) return false
  const now = new Date()
  // Assume typical class length ~75 minutes if unspecified
  const end = new Date(start.getTime() + 75*60*1000)
  return now >= start && now <= end
}

function fmttime(d){ try { return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) } catch { return '' } }

const todayList = computed(() => {
  const now = new Date()
  const list = (items.value || []).map(p => {
    const start = deriveStart(p)
    const loc = p.location || (Array.isArray(p.locations) ? p.locations.find(v => String(v).trim()) : null)
    return {
      id: p.id,
      title: p.title,
      url: p.url || '#',
      start,
      startLabel: start ? fmttime(start) : '',
      location: cleanLocation(loc),
      mode: p.mode || null,
      live: isLive(start),
    }
  }).filter(it => it.start && isSameDay(it.start, now))
  list.sort((a,b) => a.start - b.start)
  return list
})


const upcomingSoon = computed(() => {
  const now = new Date()
  const horizon = new Date(now.getTime() + 48*3600*1000)
  const list = (items.value || []).map(p => {
    const start = deriveStart(p)
    const loc = p.location || (Array.isArray(p.locations) ? p.locations.find(v => String(v).trim()) : null)
    return {
      id: p.id,
      title: p.title,
      url: p.url || '#',
      start,
      day: start ? start.toLocaleDateString(undefined, { weekday: 'short' }) : '',
      when: start ? `${start.toLocaleDateString(undefined, { weekday: 'short' })} • ${fmttime(start)}` : '',
      location: cleanLocation(loc),
      mode: p.mode || null,
    }
  }).filter(it => it.start && it.start > now && it.start <= horizon)
  list.sort((a,b) => a.start - b.start)
  return list.slice(0, Math.max(4, props.limit/2))
})

const hasTimedClasses = computed(() => {
  return (items.value || []).some(p => !!deriveStart(p))
})

const headerTitle = computed(() => props.title || 'Book Your Class')
const liveCount = computed(() => todayList.value.filter((it) => it.live).length)

async function ensureData(){
  if (Array.isArray(props.products)) { items.value = props.products; return }
  loading.value = true
  try { items.value = await fetchProducts({ type: 'class', sort: 'popular', limit: 80 }) }
  catch { items.value = [] }
  finally { loading.value = false }
}

onMounted(ensureData)
</script>

<template>
  <section class="section" aria-labelledby="schedule-title">
    <div class="container-page">
      <div class="mb-6 flex items-end justify-between">
        <div>
          <div class="kicker">{{ props.eyebrow || 'Book now' }}</div>
          <h2 id="schedule-title">{{ headerTitle }}</h2>
          <p v-if="props.subtitle" class="schedule-sub">{{ props.subtitle }}</p>
        </div>
        <a href="/classes" class="btn-wow btn-wow--outline btn-sm btn-arrow">
          <span class="btn-label">View all classes</span>
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

      <div class="card p-0 overflow-hidden">
        <div v-if="loading" class="p-6 text-ink-600">Loading today’s schedule…</div>
        <template v-else>
          <div v-if="todayList.length" class="table-wrap">
            <div v-if="liveCount===0" class="info-banner">No live classes right now</div>
            <table class="wow-table">
              <thead>
                <tr>
                  <th scope="col">Time</th>
                  <th scope="col">Session</th>
                  <th scope="col">Location</th>
                  <th scope="col" class="w-1"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="c in todayList.slice(0, limit)" :key="c.id" :class="c.live ? 'is-live' : ''">
                  <td class="time">{{ c.startLabel }}</td>
                  <td class="title">
                    <a :href="c.url" class="link-wow">{{ c.title }}</a>
                    <div v-if="c.live" class="live-pill" aria-label="Live now">Live now</div>
                  </td>
                  <td class="location">
                    <span v-if="c.mode==='Online'" class="badge mode">Online</span>
                    <span v-if="c.location" class="muted">{{ c.location }}</span>
                  </td>
                  <td class="cta">
                    <WowButton as="a" :href="c.url" size="sm" :arrow="true">Book</WowButton>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else>
            <!-- If there are timed classes (just not today), show upcoming soon -->
            <div v-if="hasTimedClasses" class="p-6">
              <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                  <h3 class="m-0 h5">No live classes right now</h3>
                  <div class="text-ink-600">Here’s what’s coming up next.</div>
                </div>
                <a href="/classes" class="btn-wow btn-wow--outline btn-sm btn-arrow">
                  <span class="btn-label">Browse all classes</span>
                  <span class="btn-icon-wrap" aria-hidden="true">
                    <svg class="btn-icon-hover" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"/></svg>
                    <svg class="btn-icon-default" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12l-4 4m4-4-4-4"/></svg>
                  </span>
                  <span class="btn-spinner" aria-hidden="true"><span class="spin"></span></span>
                </a>
              </div>
              <div class="upnext-grid mt-4">
                <div v-for="u in upcomingSoon" :key="u.id" class="up-card">
                  <div class="up-when">{{ u.when }}</div>
                  <div class="up-title"><a :href="u.url" class="link-wow">{{ u.title }}</a></div>
                  <div class="up-meta">
                    <span v-if="u.mode==='Online'" class="badge mode">Online</span>
                    <span v-if="u.location" class="muted">{{ u.location }}</span>
                  </div>
                  <div class="up-cta"><WowButton as="a" :href="u.url" size="sm" :arrow="true">Book</WowButton></div>
                </div>
              </div>
            </div>

            <!-- Otherwise, no timed classes at all -->
            <div v-else class="p-6 text-ink-700">
              <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                  <h3 class="m-0 h5">Classes are being scheduled</h3>
                  <div class="text-ink-600">New sessions are added weekly—browse what’s live now.</div>
                </div>
                <a href="/classes" class="btn-wow btn-wow--outline btn-sm btn-arrow">
                  <span class="btn-label">Browse classes</span>
                  <span class="btn-icon-wrap" aria-hidden="true">
                    <svg class="btn-icon-hover" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"/></svg>
                    <svg class="btn-icon-default" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12l-4 4m4-4-4-4"/></svg>
                  </span>
                  <span class="btn-spinner" aria-hidden="true"><span class="spin"></span></span>
                </a>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </section>
</template>

<style scoped>
.h4 { font-size: 1.25rem; font-weight: 600; }
.h5 { font-size: 1.05rem; font-weight: 600; }
.table-wrap{ width:100%; overflow:auto }
.wow-table{ width:100%; border-collapse:separate; border-spacing:0 }
.wow-table thead th{ text-align:left; font-size:.85rem; font-weight:700; color:#fff; background:#3b7768; padding:10px 14px }
.wow-table tbody td{ padding:12px 14px; border-top:1px solid var(--ink-200); vertical-align:middle }
.wow-table tbody tr:hover{ background:#f9fbfd }
.wow-table .time{ font-weight:600; white-space:nowrap }
.wow-table .title{ font-weight:600 }
.wow-table .location .muted{ color: var(--ink-600) }
.wow-table .cta{ text-align:right; white-space:nowrap }
.wow-table tr.is-live td{ background: linear-gradient(90deg, rgba(44,165,141,.06), transparent 40%); }
.live-pill{ display:inline-flex; align-items:center; gap:6px; margin-top:6px; background:#ffefef; color:#cc2a2a; border:1px solid #f7c6c6; border-radius:999px; padding:2px 8px; font-size:.75rem; font-weight:700 }
.live-pill::before{ content:""; width:8px; height:8px; border-radius:50%; background:#ff3b3b; box-shadow:0 0 0 3px rgba(255,59,59,.25) }
.badge.mode{ display:inline-flex; align-items:center; height:22px; padding:0 8px; border-radius:999px; background:#eef2f7; color:#334155; font-weight:600; font-size:.8rem; border:1px solid #e2e8f0; margin-right:6px }
.upnext-grid{ display:grid; grid-template-columns: repeat(1,minmax(0,1fr)); gap:12px }
@media (min-width: 640px){ .upnext-grid{ grid-template-columns: repeat(2,minmax(0,1fr)) } }
@media (min-width: 992px){ .upnext-grid{ grid-template-columns: repeat(3,minmax(0,1fr)) } }
.up-card{ border:1px solid var(--ink-200); border-radius:14px; padding:14px; background:#fff }
.up-when{ font-weight:700; color:#0b1323 }
.up-title{ margin-top:2px; font-weight:600 }
.up-meta{ color: var(--ink-600); margin-top:4px }
.up-cta{ margin-top:10px }

.schedule-sub{ color: var(--ink-600); margin-top:.35rem; max-width:460px }

/* Info banner */
.info-banner{ padding:10px 14px; background:#fff8e6; color:#7a5300; border:1px solid #ffe0a3; border-left:4px solid #ffcc66; font-weight:600 }

/* Segmented tabs */
.seg-group{ display:inline-flex; background:#f8fafc; border:1px solid var(--ink-200); border-radius:999px; padding:2px }
.seg{ appearance:none; border:0; background:transparent; padding:6px 12px; border-radius:999px; color: var(--ink-700); font-weight:600; font-size:.9rem; transition: all .15s ease; }
.seg:hover{ background:#eef2f7 }
.seg.active{ background: linear-gradient(180deg, #549483, #3b7768); color:#fff; box-shadow: 0 1px 0 rgba(255,255,255,.4) inset }

/* Airplane toggle (static, always on) */
.air-toggle{ --sky:#78c7fe; --on:#1AB1FD; width:62px; height:28px; border-radius:999px; background:var(--sky); position:relative; overflow:hidden; box-shadow: 0 4px 10px rgba(2,8,23,.12) }
.air-toggle .runway{ position:absolute; left:6px; right:28px; top:50%; height:3px; background:#fff; opacity:.8; border-radius:4px; transform: translateY(-50%) }
.air-toggle .knob{ position:absolute; right:0; top:0; width:28px; height:28px; background:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow: 0 2px 6px rgba(2,8,23,.18) }
.air-toggle .plane{ width:12px; height:12px }
/* Tiny sky with animated clouds */
.air-toggle .sky{ position:absolute; inset:0; border-radius:inherit; pointer-events:none }
.air-toggle .cloud-line{ position:absolute; inset:0; animation: cloudLoop 6s linear infinite }
.air-toggle .cloud-container{ position:absolute; inset:0 }
.air-toggle .cloud-container:last-child{ left:100% }
.air-toggle .cloud{ position:absolute; width:12px; height:4px; background:#fff; border-radius:4px; opacity:.7 }
.air-toggle .cloud .c1,.air-toggle .cloud .c2{ position:absolute; border-radius:50%; background:#fff }
.air-toggle .cloud .c1{ width:6px; height:6px; left:-3px; top:-2px }
.air-toggle .cloud .c2{ width:5px; height:5px; right:-2px; top:-1px }
.air-toggle .cloud:nth-child(1){ left:6px; top:6px; opacity:.6 }
.air-toggle .cloud:nth-child(2){ left:24px; top:12px; transform:scale(.9); opacity:.7 }
.air-toggle .cloud:nth-child(3){ left:46px; top:4px; transform:scale(1.1); opacity:.5 }
@keyframes cloudLoop { 0%{ transform: translateX(0) } 100%{ transform: translateX(-100%) } }
</style>
