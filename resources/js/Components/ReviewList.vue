<script setup>
import { computed } from 'vue'

const props = defineProps({
  reviews: { type: Array, default: () => [] },
  average: { type: [Number, String], default: null },
  count: { type: Number, default: 0 },
})

const safeReviews = computed(() => Array.isArray(props.reviews) ? props.reviews : [])

function starArray(r) {
  const n = Math.max(0, Math.min(5, Number(r)||0))
  return Array.from({ length: 5 }, (_, i) => i < Math.round(n))
}

function initials(name) {
  const s = String(name || '')
  if (!s.trim()) return '•'
  const parts = s.trim().split(/\s+/)
  const a = parts[0]?.[0] || ''
  const b = parts[1]?.[0] || ''
  return (a + b).toUpperCase()
}

function fmtDate(iso){
  if (!iso) return ''
  try { return new Date(iso).toLocaleDateString(undefined, { year:'numeric', month:'short', day:'numeric' }) } catch { return '' }
}
</script>

<template>
  <div class="reviews">
    <div class="summary" v-if="count || average">
      <div class="stars">
        <template v-for="(on,i) in starArray(average)" :key="i">
          <svg v-if="on" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="s star on" aria-hidden="true" fill="currentColor"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="s star off" aria-hidden="true" fill="none"><path d="M12 4.84 14.1 9.9l.2.46.5.04 5.41.46-4.11 3.57-.38.33.12.49 1.27 5.44-4.7-2.9-.44-.27-.44.27-4.7 2.9 1.27-5.44.12-.49-.38-.33L3.79 10.86l5.41-.46.5-.04.2-.46L12 4.84Z" stroke="currentColor" stroke-width="1.5"/></svg>
        </template>
      </div>
      <div class="avg">{{ average || '—' }}</div>
      <div class="meta" v-if="count">({{ count }} reviews)</div>
    </div>

    <div v-if="safeReviews.length === 0" class="empty">No reviews yet.</div>
    <ul v-else class="list">
      <li v-for="r in safeReviews" :key="r.id" class="item">
        <div class="avatar" aria-hidden="true">{{ initials(r?.user?.name) }}</div>
        <div class="body">
          <div class="row1">
            <div class="name">{{ r?.user?.name || 'Verified customer' }}</div>
            <div class="spacer" />
            <div class="date">{{ fmtDate(r?.created_at) }}</div>
          </div>
          <div class="row2">
            <div class="stars">
              <template v-for="(on,i) in starArray(r?.rating)" :key="i">
                <svg v-if="on" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="xs star on" aria-hidden="true" fill="currentColor"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="xs star off" aria-hidden="true" fill="none"><path d="M12 4.84 14.1 9.9l.2.46.5.04 5.41.46-4.11 3.57-.38.33.12.49 1.27 5.44-4.7-2.9-.44-.27-.44.27-4.7 2.9 1.27-5.44.12-.49-.38-.33L3.79 10.86l5.41-.46.5-.04.2-.46L12 4.84Z" stroke="currentColor" stroke-width="1.5"/></svg>
              </template>
            </div>
          </div>
          <div class="text">{{ r?.review }}</div>
        </div>
      </li>
    </ul>
  </div>
  
</template>

<style scoped>
.reviews{ display:grid; gap: 1rem }
.summary{ display:flex; align-items:center; gap:.5rem; padding:.5rem .75rem; background: var(--muted); border:1px solid var(--ink-200); border-radius: 12px; width: fit-content }
.summary .avg{ font-weight:600; color: var(--ink-900) }
.summary .meta{ color: var(--ink-600); font-size:.9rem }
.stars{ display:inline-flex; align-items:center; gap:2px; color:#f59e0b }
.star.s{ width:22px; height:22px }
.star.xs{ width:16px; height:16px }
.star.off{ color: var(--ink-300) }
.list{ list-style:none; margin:0; padding:0; display:grid; gap:.75rem }
.item{ display:flex; gap:.75rem; padding: .75rem; border:1px solid var(--ink-200); border-radius: 12px; background:#fff }
.avatar{ flex: 0 0 auto; width:36px; height:36px; border-radius:9999px; background: linear-gradient(180deg, color-mix(in srgb, var(--aux-ice) 22%, white), white); color: var(--ink-700); display:flex; align-items:center; justify-content:center; font-weight:700; border:1px solid var(--ink-200) }
.body{ min-width:0; flex:1 }
.row1{ display:flex; gap:.5rem; align-items:center }
.name{ font-weight:600; color: var(--ink-800) }
.date{ color: var(--ink-500); font-size:.85rem }
.spacer{ flex:1 }
.row2{ display:flex; align-items:center; gap:.5rem; margin:.1rem 0 .25rem 0 }
.text{ color: var(--ink-800) }
</style>

