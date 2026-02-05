<script setup>
import BuyBoxAdvanced from '@/Components/BuyBoxAdvanced.vue'
const props = defineProps({ product: { type: Object, required: true } })
</script>

<template>
  <BuyBoxAdvanced :product="product" version="legacy" @add="$emit('add',$event)" />
</template>
