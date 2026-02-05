<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import LocationAutocomplete from '@/Components/LocationAutocomplete.vue'

const props = defineProps({ open: { type: Boolean, default: false } })
const emit = defineEmits(['close'])

const step = ref(0)
const totalBase = 6 // base steps without conditional 'where'
const a = ref({
  feel: '', // stressed | low-energy | sleep | anxious | pain
  mode: 'in-person', // online | in-person
  time: '30', // 15 | 30 | 60
  budget: '60', // 30 | 60 | 999
  goal: '', // relax | focus | sleep | recovery | social
  when: 'this week', // this week | this weekend | next month
  whereName: '',
  coords: null,
})

const steps = computed(() => {
  // Insert location step right after mode if in-person
  const base = ['feel','mode','time','budget','goal','when']
  return a.value.mode === 'in-person' ? ['feel','mode','where','time','budget','goal','when'] : base
})
const progress = computed(() => Math.round(((step.value+1)/steps.value.length)*100))
const currentStep = computed(() => steps.value[step.value] || 'feel')
const trackIndex = computed(() => steps.value.indexOf(currentStep.value))
const themeHue = computed(() => (trackIndex.value * 36) % 360) // rotate hue per step
const canNext = computed(() => {
  switch (currentStep.value) {
    case 'feel': return !!a.value.feel
    case 'mode': return !!a.value.mode
    case 'where': return !!(a.value.whereName && a.value.whereName.trim()) || !!a.value.coords
    case 'time': return !!a.value.time
    case 'budget': return !!a.value.budget
    case 'goal': return !!a.value.goal
    case 'when': return !!a.value.when
    default: return true
  }
})

function close() { emit('close') }
function next(){ const last = steps.value.length - 1; if (step.value < last && canNext.value) step.value++ }
function prev(){ if (step.value > 0) step.value-- }

function buildSearch(){
  const p = new URLSearchParams()
  // Map feel/goal → what
  const feelMap = {
    'stressed': 'stress relief',
    'low-energy': 'energy',
    'sleep': 'sleep',
    'anxious': 'anxiety',
    'pain': 'pain relief',
  }
  const goalMap = {
    'relax': 'relaxation',
    'focus': 'focus',
    'sleep': 'sleep',
    'recovery': 'recovery',
    'social': 'community',
  }
  const what = goalMap[a.value.goal] || feelMap[a.value.feel] || ''
  if (what) p.set('what', what)
  // Mode
  if (a.value.mode === 'online') p.set('mode', 'online')
  else p.set('mode','in-person')
  // Where
  const where = (a.value.whereName || '').trim()
  if (where && a.value.mode === 'in-person') p.set('where', where)
  // Type heuristic based on time/goal
  let type = ''
  if (a.value.time === '60' || a.value.goal === 'social') type = 'workshop'
  else if (a.value.goal === 'recovery') type = 'retreat'
  else type = 'class'
  p.set('type', type)
  // Budget
  if (a.value.budget === '30') p.set('price_max', '30')
  else if (a.value.budget === '60') p.set('price_max', '60')
  // When
  const w = a.value.when
  if (w === 'this week') p.set('when', 'this week')
  else if (w === 'this weekend') p.set('when', 'this weekend')
  else if (w === 'next month') p.set('when', 'next month')
  return '/search?' + p.toString()
}

function buildPlan(){
  const p = new URLSearchParams()
  const s = buildSearch().replace(/^\/search\?/, '')
  // Carry over all params
  s.split('&').forEach(kv => { if (!kv) return; const [k,v] = kv.split('='); if (k) p.set(k, v || '') })
  return '/plan?' + p.toString()
}

function start(){ step.value = 0 }
watch(() => props.open, (v) => { if (v) start() })

function onKey(e){ if (e.key === 'Escape') close() }
onMounted(() => document.addEventListener('keydown', onKey))
onBeforeUnmount(() => document.removeEventListener('keydown', onKey))

async function useMyLocation(){
  try {
    if (!navigator.geolocation) return
    navigator.geolocation.getCurrentPosition(async (pos) => {
      const lat = pos.coords.latitude, lng = pos.coords.longitude
      try {
        const key = window.WOW_MAPS_KEY || ''
        if (key) {
          const url = new URL(`https://api.mapbox.com/geocoding/v5/mapbox.places/${lng},${lat}.json`)
          url.searchParams.set('access_token', key)
          url.searchParams.set('limit', '1')
          const res = await fetch(url)
          const data = await res.json()
          const name = data?.features?.[0]?.place_name || ''
          a.value.whereName = name
          a.value.coords = { lat, lng }
        }
      } catch {}
    })
  } catch {}
}
</script>

<template>
  <div v-if="open" class="quiz-overlay" :style="{ '--quiz-hue': themeHue + 'deg' }">
    <div class="card quiz-card p-4 p-md-5">
      <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
        <div class="kicker">60‑sec Quiz</div>
        <button type="button" class="btn btn-ghost pill-sm" @click.stop="close" aria-label="Close">Close</button>
      </div>
      <div class="progress-line mb-3"><div class="progress-fill" :style="{ width: progress + '%' }"></div></div>

      <div class="stage">
        <div class="track" :style="{ transform: `translateX(-${trackIndex*100}%)` }">
          <!-- feel -->
          <div class="slide pane">
            <h3>How do you feel today?</h3>
            <div class="d-flex flex-wrap gap-2">
              <button type="button" :class="['chip', a.feel==='stressed' && 'chip-brand']" @click.stop="a.feel='stressed'">Stressed</button>
              <button type="button" :class="['chip', a.feel==='low-energy' && 'chip-brand']" @click.stop="a.feel='low-energy'">Low energy</button>
              <button type="button" :class="['chip', a.feel==='sleep' && 'chip-brand']" @click.stop="a.feel='sleep'">Can’t sleep</button>
              <button type="button" :class="['chip', a.feel==='anxious' && 'chip-brand']" @click.stop="a.feel='anxious'">Anxious</button>
              <button type="button" :class="['chip', a.feel==='pain' && 'chip-brand']" @click.stop="a.feel='pain'">Sore & tight</button>
            </div>
          </div>
          <!-- mode -->
          <div class="slide pane">
            <h3>Preferred mode</h3>
            <div class="d-flex flex-wrap gap-2">
              <button type="button" :class="['chip', a.mode==='online' && 'chip-brand']" @click.stop="a.mode='online'">Online</button>
              <button type="button" :class="['chip', a.mode==='in-person' && 'chip-brand']" @click.stop="a.mode='in-person'">In‑person</button>
            </div>
          </div>
          <!-- where (conditionally rendered to keep index continuity) -->
          <div v-if="steps.includes('where')" class="slide pane">
            <h3>Where are you looking?</h3>
            <div class="d-flex gap-2 mb-2">
              <button type="button" class="btn btn-ghost" @click.stop="useMyLocation">Use my location</button>
            </div>
            <div class="rounded-2xl border border-ink-200 p-2">
              <LocationAutocomplete v-model="a.whereName" :access-token="window.WOW_MAPS_KEY || ''" placeholder="Enter city or postcode" />
            </div>
          </div>
          <!-- time -->
          <div class="slide pane">
            <h3>Time you can spare</h3>
            <div class="d-flex flex-wrap gap-2">
              <button type="button" :class="['chip', a.time==='15' && 'chip-brand']" @click.stop="a.time='15'">15 min</button>
              <button type="button" :class="['chip', a.time==='30' && 'chip-brand']" @click.stop="a.time='30'">30 min</button>
              <button type="button" :class="['chip', a.time==='60' && 'chip-brand']" @click.stop="a.time='60'">60+ min</button>
            </div>
          </div>
          <!-- budget -->
          <div class="slide pane">
            <h3>Budget</h3>
            <div class="d-flex flex-wrap gap-2">
              <button type="button" :class="['chip', a.budget==='30' && 'chip-brand']" @click.stop="a.budget='30'">Under £30</button>
              <button type="button" :class="['chip', a.budget==='60' && 'chip-brand']" @click.stop="a.budget='60'">Under £60</button>
              <button type="button" :class="['chip', a.budget==='999' && 'chip-brand']" @click.stop="a.budget='999'">Flexible</button>
            </div>
          </div>
          <!-- goal -->
          <div class="slide pane">
            <h3>What’s your goal?</h3>
            <div class="d-flex flex-wrap gap-2">
              <button type="button" :class="['chip', a.goal==='relax' && 'chip-brand']" @click.stop="a.goal='relax'">Relax</button>
              <button type="button" :class="['chip', a.goal==='focus' && 'chip-brand']" @click.stop="a.goal='focus'">Focus</button>
              <button type="button" :class="['chip', a.goal==='sleep' && 'chip-brand']" @click.stop="a.goal='sleep'">Sleep</button>
              <button type="button" :class="['chip', a.goal==='recovery' && 'chip-brand']" @click.stop="a.goal='recovery'">Recovery</button>
              <button type="button" :class="['chip', a.goal==='social' && 'chip-brand']" @click.stop="a.goal='social'">Social</button>
            </div>
          </div>
          <!-- when -->
          <div class="slide pane">
            <h3>When works?</h3>
            <div class="d-flex flex-wrap gap-2">
              <button type="button" :class="['chip', a.when==='this week' && 'chip-brand']" @click.stop="a.when='this week'">This week</button>
              <button type="button" :class="['chip', a.when==='this weekend' && 'chip-brand']" @click.stop="a.when='this weekend'">This weekend</button>
              <button type="button" :class="['chip', a.when==='next month' && 'chip-brand']" @click.stop="a.when='next month'">Next month</button>
            </div>
          </div>
        </div>
      </div>

      <div class="d-flex align-items-center gap-2 border-top pt-3 mt-3">
        <button type="button" class="btn btn-ghost" :disabled="step===0" @click.stop="prev">Back</button>
        <div class="flex-1"></div>
        <button v-if="step < steps.length-1" type="button" class="btn btn-primary" :disabled="!canNext" @click.stop="next">Next</button>
        <a v-else class="btn btn-primary" :href="buildPlan()" @click="close">See your plan</a>
      </div>
    </div>
  </div>
</template>

<style scoped>
.quiz-overlay{ position:fixed; inset:0; z-index:1000; background:
  radial-gradient(1200px 400px at 10% 10%, color-mix(in srgb, var(--brand-50) 60%, white), transparent 60%),
  radial-gradient(900px 320px at 90% 20%, color-mix(in srgb, var(--aux-ice) 40%, white), transparent 60%),
  rgba(17,24,39,0.55);
  -webkit-backdrop-filter: blur(8px); backdrop-filter: blur(8px);
  display:flex; align-items:center; justify-content:center; padding: 1rem; transition: filter .3s ease, background .4s ease;
  filter: hue-rotate(var(--quiz-hue));
}
.quiz-card{ width:min(760px, 96vw); }
.progress-line{ height:6px; background: var(--ink-100); border-radius:999px; overflow:hidden }
.progress-fill{ height:6px; background: var(--brand-600); width:0 }
.pane{ padding: 8px 0 }
.pane h3{ margin: 0 0 10px 0; font-size: 1.25rem; font-weight:700 }
.flex-1{ flex:1 }
.stage{ overflow:hidden; }
.track{ display:flex; width:100%; transition: transform .32s cubic-bezier(.2,.6,.2,1) }
.slide{ flex: 0 0 100%; }
</style>
