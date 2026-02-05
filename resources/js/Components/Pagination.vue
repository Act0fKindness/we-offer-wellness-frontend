<script setup>
import { Link } from '@inertiajs/vue3'
const props = defineProps({
  links: { type: Array, default: () => [] },
})
</script>

<template>
  <nav v-if="(links||[]).length" class="pager" aria-label="Pagination">
    <ul>
      <li v-for="(l,i) in links" :key="i">
        <span v-if="!l.url" class="page disabled" v-html="l.label"></span>
        <Link v-else :href="l.url" class="page" :class="{ active: l.active }" preserve-scroll v-html="l.label" />
      </li>
    </ul>
  </nav>
</template>

<style scoped>
.pager{ display:flex; justify-content:center; margin: 16px 0 0 }
.pager ul{ list-style:none; display:flex; gap:6px; padding:0; margin:0 }
.page{ display:inline-flex; align-items:center; justify-content:center; min-width:36px; height:36px; padding:0 10px; border-radius:10px; border:1px solid var(--ink-200); background:#fff; color: var(--ink-800); text-decoration:none; font-weight:600 }
.page:hover{ background:#f8fafc }
.page.active{ background: linear-gradient(180deg, #549483, #3b7768); border-color:#3b7768; color:#fff }
.page.disabled{ opacity:.4; cursor:not-allowed }
</style>

