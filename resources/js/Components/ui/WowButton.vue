<script setup>
import { computed } from 'vue'

const props = defineProps({
  as: { type: String, default: 'button' }, // 'button' | 'a'
  href: { type: String, default: '' },
  type: { type: String, default: 'button' },
  variant: { type: String, default: 'primary' }, // primary|cta|secondary|outline|ghost
  size: { type: String, default: 'md' }, // xs|sm|md|lg|xl
  arrow: { type: Boolean, default: false },
  block: { type: Boolean, default: false },
  squarish: { type: Boolean, default: false },
  square: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  semantics: { type: String, default: '' }, // success|danger|warning (optional extra)
})

const klass = computed(() => {
  const out = ['btn-wow']
  // variant mapping
  if (props.variant === 'cta') out.push('btn-wow--cta')
  else if (props.variant === 'secondary') out.push('btn-wow--secondary')
  else if (props.variant === 'outline') out.push('btn-wow--outline')
  else if (props.variant === 'ghost') out.push('btn-wow--ghost')
  // size
  out.push(`btn-${props.size}`)
  // flags
  if (props.block) out.push('is-block')
  if (props.squarish) out.push('is-squarish')
  if (props.square) out.push('is-square')
  // semantics
  if (props.semantics === 'success') out.push('is-success')
  if (props.semantics === 'danger') out.push('is-danger')
  if (props.semantics === 'warning') out.push('is-warning')
  if (props.loading) out.push('is-loading')
  return out.join(' ')
})

const isLink = computed(() => props.as === 'a' || !!props.href)
// Only render the disabled attribute when true on a real <button>
const disabledAttr = computed(() => (isLink.value || !props.disabled) ? undefined : true)
</script>

<template>
  <component :is="isLink ? 'a' : 'button'"
             :href="isLink ? (href || '#') : undefined"
             :type="!isLink ? type : undefined"
             :disabled="disabledAttr"
             :aria-busy="loading || undefined"
             :class="klass">
    <span class="btn-label"><slot /></span>
    <span v-if="arrow" class="btn-icon-wrap" aria-hidden="true">
      <svg class="btn-icon-hover" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19 12H5m14 0-4 4m4-4-4-4"/>
      </svg>
      <!-- white stroke in markup overridden to currentColor for outline via CSS -->
      <svg class="btn-icon-default" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 12l-4 4m4-4-4-4"/>
      </svg>
    </span>
    <span class="btn-spinner" aria-hidden="true"><span class="spin"></span></span>
  </component>
  
</template>
