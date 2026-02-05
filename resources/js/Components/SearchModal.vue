<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue'
import UltraSearchBar from '@/Components/UltraSearchBar.vue'

const props = defineProps({ open: { type: Boolean, default: false } })
const emit = defineEmits(['close'])

const root = ref(null)
function onKey(e){ if (e.key === 'Escape') emit('close') }
onMounted(()=> document.addEventListener('keydown', onKey))
onBeforeUnmount(()=> document.removeEventListener('keydown', onKey))
</script>

<template>
  <div v-if="open" ref="root" class="search-overlay" @click.self="$emit('close')">
    <div class="search-panel">
      <UltraSearchBar id-prefix="modal" :only-what="true" />
      <button class="close-btn" @click="$emit('close')" aria-label="Close search">
        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<style scoped>
.search-overlay{ position:fixed; inset:0; background:rgba(17,24,39,.55); -webkit-backdrop-filter: blur(6px); backdrop-filter: blur(6px); z-index: 1000; display:flex; align-items:flex-start; justify-content:center; padding: 10vh 1rem 2rem }
.search-panel{ position:relative; width:min(960px, 96vw); background:#fff; border-radius:18px; border:1px solid var(--ink-200); box-shadow: 0 20px 50px rgba(0,0,0,.15); padding: 1rem }
.close-btn{ position:absolute; top:-14px; right:-14px; width:38px; height:38px; border-radius:9999px; background:#fff; border:1px solid var(--ink-200); box-shadow:0 8px 18px rgba(0,0,0,.12); display:inline-flex; align-items:center; justify-content:center; color:#111827 }
.close-btn svg{ width:20px; height:20px; display:block }
.close-btn:hover{ background:#f9fafb }
@media (max-width: 768px){ .close-btn{ top:8px; right:8px } }
</style>
