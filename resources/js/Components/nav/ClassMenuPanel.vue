<script setup>
import { ref, computed, onMounted } from 'vue'
import WowButton from '@/Components/ui/WowButton.vue'
import { fetchProducts } from '@/services/products'

const props = defineProps({
  limit: { type: Number, default: 6 },
})

const items = ref([])
const loading = ref(false)

function toDate(iso){ try { return iso ? new Date(iso) : null } catch { return null } }
function isSameDay(a, b){
  return a && b && a.getFullYear()===b.getFullYear() && a.getMonth()===b.getMonth() && a.getDate()===b.getDate()
}
function deriveStart(product){
  const d = toDate(product?.date) || toDate(product?.start_date)
  return d && isFinite(d.getTime()) ? d : null
}
function isLive(start){
  if (!start) return false
  const now = new Date()
  const end = new Date(start.getTime() + 75*60*1000)
  return now >= start && now <= end
}
function fmttime(d){ try { return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) } catch { return '' } }
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

const hasTimedClasses = computed(() => (items.value || []).some(p => !!deriveStart(p)))

async function ensureData(){
  loading.value = true
  try { items.value = await fetchProducts({ type: 'class', sort: 'popular', limit: 60 }) }
  catch { items.value = [] }
  finally { loading.value = false }
}

onMounted(ensureData)
</script>

<template>
  <div class="class-menu-panel">
    <div class="kicker mb-2">Right now</div>

    <!-- Prefer rendering fallback immediately; swap to table only if we detect live/today items -->
    <template v-if="todayList.length">
      <div class="table-wrap">
        <table class="wow-table mini">
          <thead>
            <tr>
              <th scope="col">Time</th>
              <th scope="col">Session</th>
              <th scope="col" class="w-1"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in todayList.slice(0, limit)" :key="c.id" :class="c.live ? 'is-live' : ''">
              <td class="time">{{ c.startLabel }}</td>
              <td class="title">
                <a :href="c.url" class="menu-link">{{ c.title }}</a>
                <span v-if="c.live" class="live-dot" aria-label="Live now"></span>
                <span v-if="c.mode==='Online'" class="badge mode ms-2">Online</span>
                <span v-else-if="c.location" class="muted ms-2">{{ c.location }}</span>
              </td>
              <td class="cta"><WowButton as="a" :href="c.url" size="sm" :arrow="true">Book</WowButton></td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
    <template v-else>
      <div class="fallback">
        <div class="info">No live classes right now</div>
        <div class="cards">
          <div class="card">
            <div class="h5">Book Anytime Classes</div>
            <p class="muted">Gentle gong baths, yoga nidra, breathwork and guided meditation to reset whenever you need.</p>
            <WowButton as="a" href="/search?format=online" size="sm" :arrow="true">Explore Anytime</WowButton>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
.class-menu-panel{ padding-left:0; }
.table-wrap{ max-height: 280px; overflow:auto }
.wow-table{ width:100%; border-collapse:separate; border-spacing:0 }
.wow-table thead th{ text-align:left; font-size:.8rem; font-weight:700; color:#fff; background:#3b7768; padding:8px 10px }
.wow-table tbody td{ padding:8px 10px; border-top:1px solid var(--ink-200); vertical-align:middle }
.wow-table .time{ white-space:nowrap; font-weight:600 }
.wow-table .title{ font-weight:600 }
.wow-table .cta{ white-space:nowrap; text-align:right }
.wow-table tr.is-live td{ background: linear-gradient(90deg, rgba(44,165,141,.06), transparent 40%); }
.live-dot{ display:inline-block; width:8px; height:8px; border-radius:50%; background:#ff3b3b; margin-left:6px; box-shadow:0 0 0 3px rgba(255,59,59,.25) }
.badge.mode{ display:inline-flex; align-items:center; height:20px; padding:0 8px; border-radius:999px; background:#eef2f7; color:#334155; font-weight:600; font-size:.75rem; border:1px solid #e2e8f0 }
.fallback .info{ font-weight:700; color:#0b1323; margin-bottom:6px }
.fallback .hint{ font-size:.9rem; margin-bottom:10px }
.cards{ display:grid; grid-template-columns:1fr; gap:10px }
@media (min-width: 992px){ .cards{ grid-template-columns: 1fr } }
.card{ background:#fff; border:1px solid var(--ink-200); border-radius:12px; padding:12px }
.card .h5{ font-weight:700; margin-bottom:4px; color:#245c4f }
.card p{ margin:0 0 8px }
</style>
