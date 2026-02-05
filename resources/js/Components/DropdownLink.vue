<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  href: { type: String, required: true },
})
</script>

<template>
  <Link :href="href" class="px-4 py-2 text-start text-sm link-wow">
    <slot />
  </Link>
</template>
