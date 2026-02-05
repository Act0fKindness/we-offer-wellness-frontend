<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: { type: [String, Number], default: null },
  options: { type: Array, required: true }, // [{ label, value }]
  groupLabel: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])

const index = computed(() => {
  const i = props.options.findIndex(o => o.value === props.modelValue)
  return i >= 0 ? i : 0
})

function select(val){ emit('update:modelValue', val) }

const indicatorStyle = computed(() => {
  const n = Math.max(1, props.options.length)
  const w = 100 / n
  return {
    width: w + '%',
    transform: `translateX(${index.value * w}%)`,
  }
})
</script>

<template>
  <div class="smartseg">
    <div v-if="groupLabel" class="smartseg-label">{{ groupLabel }}</div>
    <div class="smartseg-track" role="tablist" aria-label="smart options">
      <div class="smartseg-indicator" :style="indicatorStyle" aria-hidden="true"></div>
      <button v-for="(opt,i) in options" :key="i" type="button"
              class="smartseg-btn" role="tab"
              :aria-selected="modelValue===opt.value ? 'true' : 'false'"
              @click="select(opt.value)">
        <span>{{ opt.label }}</span>
      </button>
    </div>
  </div>
</template>

<style scoped>
.smartseg{ display:flex; align-items:center; gap:.5rem }
.smartseg-label{ font-weight:600; color: var(--ink-800, #1f2937); }
.smartseg-track{ position:relative; display:grid; grid-auto-flow:column; align-items:center; gap:0; background:#fff; border:1px solid var(--ink-200, #e5e7eb); border-radius:999px; padding:2px; min-height:36px; box-shadow: 0 1px 2px rgba(0,0,0,.03) }
.smartseg-btn{ position:relative; z-index:1; appearance:none; border:0; background:transparent; padding:.4rem .9rem; border-radius:999px; color: var(--ink-700, #374151); font-weight:600; cursor:pointer; white-space:nowrap }
.smartseg-btn[aria-selected="true"]{ color: var(--ink-900, #111827) }
.smartseg-indicator{ position:absolute; top:2px; bottom:2px; left:2px; background: var(--ink-100, #f3f4f6); border-radius:999px; transition: transform .22s cubic-bezier(.2,.75,.2,1), width .22s cubic-bezier(.2,.75,.2,1) }
@media (prefers-color-scheme: dark){
  .smartseg-track{ background: #0b1020; border-color: #1f2937 }
  .smartseg-indicator{ background: #0f172a }
  .smartseg-btn{ color: #d1d5db }
}
</style>

