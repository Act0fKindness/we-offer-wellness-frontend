<script setup>
import SquareButton from '@/Components/ui/SquareButton.vue'

const props = defineProps({
  as: { type: String, default: 'a' },
  href: { type: String, default: '#' },
  size: { type: String, default: 'sm' },
  arrow: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
})
</script>

<template>
  <SquareButton v-bind="props" variant="outline">
    <slot />
  </SquareButton>
</template>
