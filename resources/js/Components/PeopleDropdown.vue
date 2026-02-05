<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({ modelValue: { type: String, default: '1' } })
const emit = defineEmits(['update:modelValue'])

const open = ref(false)
const local = ref(props.modelValue)
watch(() => props.modelValue, v => { local.value = v })

function setVal(v) { emit('update:modelValue', v); open.value = false }

function onDoc(e) {
  if (!open.value) return
  const root = document.getElementById('people-dd-root')
  if (root && !root.contains(e.target)) open.value = false
}
onMounted(() => document.addEventListener('click', onDoc))
onBeforeUnmount(() => document.removeEventListener('click', onDoc))
</script>

<template>
  <div id="people-dd-root" class="relative">
    <button type="button" class="inline-flex items-center gap-2 h-11 px-3 rounded-xl bg-white border border-ink-200 text-sm text-ink-800 hover:bg-white" @click="open=!open">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-ink-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      <span>{{ local==='1' ? '1 person' : (local==='2' ? 'Couple' : 'Group') }}</span>
    </button>
    <div v-if="open" class="absolute z-20 mt-2 w-56 rounded-xl bg-white border border-ink-200 shadow-card p-2">
      <button type="button" class="quick-tag w-full justify-between" @click="setVal('1')">1 person</button>
      <button type="button" class="quick-tag w-full justify-between mt-2" @click="setVal('2')">Couple</button>
      <button type="button" class="quick-tag w-full justify-between mt-2" @click="setVal('group')">Group</button>
    </div>
  </div>
</template>
