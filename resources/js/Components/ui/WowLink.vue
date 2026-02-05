<script setup>
import { computed } from 'vue'
const props = defineProps({
  href: { type: String, required: true },
  variant: { type: String, default: 'default' }, // default|muted|nav|button
  drawLine: { type: Boolean, default: true },    // underline effect container
})

const klass = computed(() => {
  if (props.variant === 'muted') return 'link-wow--muted'
  if (props.variant === 'nav') return 'link-wow--nav'
  if (props.variant === 'button') return 'link-wow--button'
  return 'link-wow'
})
</script>

<template>
  <a :href="href" :class="klass" v-if="variant==='nav' || !drawLine" >
    <slot />
  </a>
  <a :href="href" :class="klass" v-else data-draw-line>
    <span><slot /></span>
    <span data-draw-line-box></span>
  </a>
</template>
