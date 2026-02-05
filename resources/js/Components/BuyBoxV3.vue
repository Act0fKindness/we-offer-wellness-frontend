<script setup>
import BuyBoxAdvanced from '@/Components/BuyBoxAdvanced.vue'
const props = defineProps({ product: { type: Object, required: true } })
</script>

<template>
  <BuyBoxAdvanced :product="product" version="v3" @add="$emit('add',$event)" />
</template>

