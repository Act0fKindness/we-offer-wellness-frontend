<script setup>
const props = defineProps({
  items: { type: Array, default: () => [] }, // [{ label, href? }]
})
</script>

<template>
  <nav class="breadcrumbs" aria-label="Breadcrumb">
    <ol>
      <li><a href="/">Home</a></li>
      <li v-for="(it, i) in items" :key="i" :aria-current="i === items.length - 1 && !it.href ? 'page' : null">
        <template v-if="it.href"><a :href="it.href">{{ it.label }}</a></template>
        <template v-else><span>{{ it.label }}</span></template>
      </li>
    </ol>
  </nav>
</template>

<style scoped>
.breadcrumbs{ font-size:.9rem; color: var(--ink-600); margin-bottom: .5rem }
.breadcrumbs ol{ list-style:none; padding:0; margin:0; display:flex; flex-wrap:wrap; gap:.25rem .6rem; align-items:center }
.breadcrumbs a{ color: var(--ink-700); text-decoration:none }
.breadcrumbs a:hover{ color: var(--ink-900); text-decoration: underline; text-underline-offset: 3px }
.breadcrumbs li{ display:flex; align-items:center }
.breadcrumbs li+li:before{ content:'/'; color: var(--ink-400); margin-right:.6rem }
</style>

