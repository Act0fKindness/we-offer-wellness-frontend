<script setup>
import { computed } from 'vue'
import { useCart } from '@/stores/cart'

const props = defineProps({ open: { type: Boolean, default: false } })
const emit = defineEmits(['close'])
const cart = useCart()

const items = computed(() => cart.items.value)
const subtotal = computed(() => cart.subtotal.value)
function fmt(n) { return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'GBP' }).format(n) }
</script>

<template>
  <div v-if="open" class="absolute top-full right-0 mt-2 w-112 bg-white border border-ink-200 shadow-lg z-50 dropdown-panel">
    <div class="p-3 border-bottom">
      <div class="fw-semibold">Your cart</div>
    </div>
    <div class="max-h-96 overflow-y-auto overflow-x-hidden no-scrollbar">
      <div v-if="items.length === 0" class="p-4 text-ink-600 text-center">Your cart is empty.</div>
      <div v-else>
        <div v-for="it in items" :key="it.id" class="border-bottom p-3 row-grid">
          <img v-if="it.image" :src="it.image" alt="" class="rounded" style="width:56px;height:56px;object-fit:cover" />
          <div class="min-w-0">
            <div class="fw-semibold text-truncate">{{ it.title }}</div>
            <div class="text-ink-600 text-sm text-truncate">Qty: {{ it.qty }} • {{ fmt(it.price) }}</div>
          </div>
          <button class="btn btn-ghost" @click.stop="cart.remove(it.id)" aria-label="Remove">×</button>
        </div>
      </div>
    </div>
    <div v-if="items.length > 0" class="p-3 d-flex align-items-center gap-2">
      <div class="fw-semibold ms-auto">Subtotal: {{ fmt(subtotal) }}</div>
    </div>
    <div v-if="items.length > 0" class="p-3 pt-0 d-flex gap-2">
      <a href="/cart" class="btn btn-outline w-50">View cart</a>
      <a href="/checkout" class="btn btn-primary w-50">Checkout</a>
    </div>
  </div>
</template>

<style scoped>
.border-bottom{ border-bottom:1px solid var(--ink-200); }
.text-sm{ font-size:.9rem }
.row-grid{ display:grid; grid-template-columns: 56px 1fr auto; align-items: start; gap: 12px; }
.dropdown-panel{ border-radius:10px; overflow:hidden }
</style>
