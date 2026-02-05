<script setup>
import WowButton from '@/Components/ui/WowButton.vue'

const props = defineProps({
  as: { type: String, default: 'button' },
  href: { type: String, default: '' },
  type: { type: String, default: 'button' },
  variant: { type: String, default: 'primary' },
  size: { type: String, default: 'md' },
  arrow: { type: Boolean, default: false },
  block: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  semantics: { type: String, default: '' },
})
</script>

<template>
  <WowButton v-bind="props" :square="true">
    <slot />
  </WowButton>
</template>

