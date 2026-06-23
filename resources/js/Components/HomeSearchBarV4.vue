<script setup>
import SearchBarV4 from './SearchBarV4.vue'

const props = defineProps({
  idPrefix: { type: String, default: 'home-search-v4' },
  searchUrl: { type: String, default: '/search' },
  resultCount: { type: [Number, String], default: 0 },
  initialQuery: { type: Object, default: () => ({}) },
  mobileTopOffset: { type: [Number, String], default: 12 },
})
</script>

<template>
  <SearchBarV4 v-bind="props" :static-layout="true" />
</template>
