<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  modelValue: { type: String, default: '' },
})
const emit = defineEmits(['update:modelValue'])

const open = ref(false)
const local = ref(props.modelValue)
watch(() => props.modelValue, v => { local.value = v })

function setVal(v) { emit('update:modelValue', v); open.value = false }

function onDoc(e) {
  if (!open.value) return
  const root = document.getElementById('date-dd-root')
  if (root && !root.contains(e.target)) open.value = false
}
onMounted(() => document.addEventListener('click', onDoc))
onBeforeUnmount(() => document.removeEventListener('click', onDoc))
</script>

<template>
  <div id="date-dd-root" class="relative">
    <button type="button" class="inline-flex items-center gap-2 h-11 px-3 rounded-xl bg-white border border-ink-200 text-sm text-ink-800 hover:bg-white"
      @click="open=!open">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-ink-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
      <span>{{ local ? new Date(local).toLocaleDateString() : 'Choose date' }}</span>
    </button>
    <div v-if="open" class="absolute z-20 mt-2 w-64 rounded-xl bg-white border border-ink-200 shadow-card p-3">
      <div class="text-xs text-ink-500 mb-2">Quick picks</div>
      <div class="flex flex-wrap gap-2 mb-3">
        <button type="button" class="quick-tag" @click="setVal(new Date().toISOString().slice(0,10))">Today</button>
        <button type="button" class="quick-tag" @click="setVal(new Date(Date.now()+86400000).toISOString().slice(0,10))">Tomorrow</button>
        <button type="button" class="quick-tag" @click="setVal('')">Anytime</button>
      </div>
      <div class="text-xs text-ink-500 mb-1">Pick a date</div>
      <input type="date" class="w-full rounded-lg bg-white border border-ink-200 px-3 py-2 text-sm text-ink-800 focus:outline-none focus:ring-2 focus:ring-brand-400"
             v-model="local" @change="emit('update:modelValue', local)" />
      <div class="mt-3 text-right">
        <button type="button" class="btn btn-ghost" @click="setVal('')">Clear</button>
        <button type="button" class="btn btn-primary" @click="setVal(local)">Apply</button>
      </div>
    </div>
  </div>
</template>
