<script setup>
import WowButton from '@/Components/ui/WowButton.vue'

defineProps({
  type: { type: String, default: 'button' },
  disabled: { type: Boolean, default: false },
})
</script>

<template>
  <!-- Primary = General black button from style deck -->
  <WowButton :type="type" :disabled="disabled" size="md">
    <slot />
  </WowButton>
</template>
