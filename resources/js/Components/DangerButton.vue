<script setup>
import WowButton from '@/Components/ui/WowButton.vue'

defineProps({
  type: { type: String, default: 'button' },
  disabled: { type: Boolean, default: false },
})
</script>

<template>
  <WowButton :type="type" :disabled="disabled" semantics="danger" size="md">
    <slot />
  </WowButton>
</template>
