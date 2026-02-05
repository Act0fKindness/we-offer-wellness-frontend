<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  href: { type: String, required: true },
  active: { type: Boolean, default: false },
})

const classes = computed(() => {
  const base = 'link-wow--nav'
  return props.active ? `${base}` : base
})
</script>

<template>
  <Link :href="href" :class="classes">
    <slot />
  </Link>
  
</template>
