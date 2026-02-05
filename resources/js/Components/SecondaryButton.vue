<script setup>
import WowButton from '@/Components/ui/WowButton.vue'

defineProps({
  type: { type: String, default: 'button' },
  disabled: { type: Boolean, default: false },
})
</script>

<template>
  <!-- Secondary = neutral/grey variant from style deck -->
  <WowButton :type="type" :disabled="disabled" variant="secondary" size="md">
    <slot />
  </WowButton>
</template>
