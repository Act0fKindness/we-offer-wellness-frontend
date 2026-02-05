<script setup>
import { computed } from 'vue'
import { TherapyCard, WorkshopCard, EventCard, RetreatCard } from '@/Components/wow-cards'

const props = defineProps({
  product: { type: Object, required: true },
  size: { type: String, default: 'md' },
})

const kind = computed(() => {
  const p = props.product || {}
  const url = String(p.url || '')
  const t = String(p.type || '').toLowerCase()
  const path = url.toLowerCase()
  if (t.includes('therapy') || path.includes('/therap')) return 'therapy'
  if (t.includes('workshop') || path.includes('/workshop')) return 'workshop'
  if (t.includes('retreat') || path.includes('/retreat')) return 'retreat'
  if (t.includes('event') || path.includes('/event')) return 'event'
  // default to therapy card styling
  return 'therapy'
})
</script>

<template>
  <component
    :is="kind==='therapy' ? TherapyCard : kind==='workshop' ? WorkshopCard : kind==='retreat' ? RetreatCard : EventCard"
    :product="product"
    :size="size"
  />
</template>
