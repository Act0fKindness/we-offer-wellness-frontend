<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: { type: [String, Number], default: null },
  name: { type: String, required: true },
  left: { type: Object, required: true }, // { label, value }
  right: { type: Array, required: true }, // [{ label, value }, { label, value }]
  debug: { type: Boolean, default: false },
})
const emit = defineEmits(['update:modelValue'])

const rightSafe = computed(() => {
  const arr = Array.isArray(props.right) ? props.right : []
  if (arr.length >= 2) return arr.slice(0, 2)
  if (arr.length === 1) return [arr[0], arr[0]]
  return [{ label: 'A', value: 'a' }, { label: 'B', value: 'b' }]
})

const ids = computed(() => {
  const base = props.name.replace(/[^a-z0-9_-]/gi, '')
  return {
    left: `${base}-left`,
    r1: `${base}-r1`,
    r2: `${base}-r2`,
  }
})

function onSelect(val){ emit('update:modelValue', val) }
</script>

<template>
  <div class="control" :data-debug="debug ? 'true' : 'false'">
    <div class="control__track">
      <div class="indicator" aria-hidden="true"></div>
      <label :for="ids.left">{{ left.label }}</label>
      <input class="sr-only" type="radio" :name="name" :id="ids.left"
             :checked="modelValue === left.value"
             @change="onSelect(left.value)" />
      <div class="premium">
        <div class="indicator" aria-hidden="true"></div>
        <label :for="ids.r1"><span>{{ rightSafe[0].label }}</span><span class="sr-only">Premium {{ rightSafe[0].label }}</span></label>
        <input class="sr-only" type="radio" :name="name" :id="ids.r1"
               :checked="modelValue === rightSafe[0].value"
               @change="onSelect(rightSafe[0].value)" />
        <label :for="ids.r2"><span>{{ rightSafe[1].label }}</span><span class="sr-only">Premium {{ rightSafe[1].label }}</span></label>
        <input class="sr-only" type="radio" :name="name" :id="ids.r2"
               :checked="modelValue === rightSafe[1].value"
               @change="onSelect(rightSafe[1].value)" />
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Normalize essentials */
.sr-only{ position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border-width:0 }

/* Core variables and motion */
:root{
  --shadow-color: 0deg 0% 63%;
  --primary: #111827; /* ink-900 */
  --secondary: #fff;
  --inactive: #6b7280; /* ink-500 */
  --duration: .22;
  --drop-off: .4;
  --ease: cubic-bezier(.2,.75,.2,1);
  --border: #e5e7eb;
}

.control{ display:inline-block }
.control__track{ display:grid; place-items:center; grid-template-columns:repeat(4,1fr); width:260px; height:38px; background:var(--secondary); border-radius:100px; border:1px solid var(--border); padding:2px; box-shadow: -0.1px 1px 1px hsl(var(--shadow-color) / 0.39), -0.2px 3.2px 3.1px -0.4px hsl(var(--shadow-color) / 0.36), -0.3px 5.7px 5.5px -0.8px hsl(var(--shadow-color) / 0.34) }
.control__track > label{ color: var(--secondary) }
.indicator{ position:absolute; width:50%; left:0; top:0; bottom:0; background:var(--primary); border-radius:100px; transition: translate calc(var(--duration) * 1s) var(--ease) }

.premium{ width:100%; height:100%; display:grid; position:relative; grid-template-columns:1fr 1fr; border:1px solid transparent; container-type:size }
.premium .indicator{ background:var(--primary); left:50%; top:0; translate:-50% 0; transition-property: translate, background; transition-duration: calc(var(--duration) * 1s); transition-timing-function: var(--ease) }
.premium::before{ content:'Premium'; position:absolute; left:50%; top:50%; translate:-50% -80%; color:var(--primary); font-size:.75rem; z-index:2; transition: translate calc(var(--duration)*1s) var(--ease), scale calc(var(--duration)*1s) var(--ease) }

label{ display:grid; place-items:center; height:100%; width:100%; cursor:pointer; font-size:.75rem; color:var(--primary); z-index:2; transition: color calc(var(--duration)*1s) var(--ease), opacity calc(var(--duration)*1s) var(--ease) }
.premium label span{ display:grid; place-items:center; height:100%; width:100%; transition: scale calc(var(--duration)*1s) var(--ease) }
.premium label:nth-of-type(1) span{ scale:.75; transform-origin:150% 150%; border-radius:100px }
.premium label:nth-of-type(2) span{ scale:.75; transform-origin:-65% 150%; border-radius:100px }

/* Track indicator default state when none selected (fallback) */
.control__track:not(:has(> input:checked)) > .indicator{ translate:100% 0 }
.control__track:not(:has(> input:checked)) > label{ color: var(--primary); opacity: var(--drop-off) }

/* Premium selected styles */
.premium:has(:checked)::before{ translate:-50% -250%; scale:.85 }
.premium:has(:checked) label span{ scale:1 }
.premium:has(:checked) .indicator{ background:var(--secondary); clip-path: inset(0 0 0 0 round 100px) }
.premium:has(:checked) label{ color: var(--inactive); opacity:.75 }
.premium:has(:nth-of-type(1):checked) .indicator{ translate:-100% 0 }
.premium:has(:nth-of-type(2):checked) .indicator{ translate:0 0 }

/* When any radio checked on track, dim unselected */
.control__track:has(> input:checked) .premium .indicator{ background: var(--inactive) }
.control__track:has(> input:checked) .premium label{ color: var(--inactive) }

/* Dark mode tweak if needed */
@media (prefers-color-scheme: dark){
  :root{ --primary:#fff; --secondary:#0b1020; --border:#1f2937; --inactive:#9ca3af }
}
</style>
