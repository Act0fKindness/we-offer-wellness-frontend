<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import SiteLayout from '@/Layouts/SiteLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import { useCart } from '@/stores/cart'
const props = defineProps({ product: { type: Object, required: true } })
const cart = useCart()
const product = computed(() => props.product || {})
const money = (value) => new Intl.NumberFormat('en-GB', { style: 'currency', currency: product.value.currency || 'GBP' }).format(Number(value || 0))
const selectedVariant = computed(() => product.value.variants?.[0] || null)
function addToCart() { cart.add({ id: `store-${product.value.id}`, product_id: product.value.id, title: product.value.title, price: Number(selectedVariant.value?.price ?? product.value.price ?? 0), image: product.value.image, url: product.value.url, meta: { type: 'physical', product_kind: 'physical_product', store_product_id: product.value.id } }) }
</script>
<template>
  <Head :title="product.title"><meta name="description" :content="product.summary || product.title" /><link rel="canonical" :href="product.url" /></Head>
  <SiteLayout><main class="section store-product-detail"><div class="container-page">
    <Breadcrumbs :items="[{ label: 'Store', href: '/products' }, { label: product.category?.name || 'Products', href: product.category?.slug ? `/product/${product.category.slug}` : '/products' }, { label: product.title }]" />
    <div class="store-product-detail__hero">
      <div class="store-product-detail__gallery"><div class="store-product-detail__image-wrap"><img v-if="product.image" :src="product.image" :alt="product.title"><div v-else class="store-product-detail__image-fallback">Physical product</div><span class="store-product-detail__badge">Physical product</span></div></div>
      <aside class="store-product-detail__buybox"><p class="store-product-detail__eyebrow">{{ product.category?.name || 'Store product' }}</p><p v-if="product.brand" class="store-product-detail__brand">{{ product.brand }}</p><h1>{{ product.title }}</h1><p class="store-product-detail__summary">{{ product.summary }}</p><div class="store-product-detail__price"><small>Price</small><strong>{{ money(selectedVariant?.price ?? product.price) }}</strong></div><p v-if="product.requires_shipping" class="store-product-detail__shipping">Ships to you · Secure checkout</p><button class="store-product-detail__button" type="button" @click="addToCart">Add to cart</button></aside>
    </div>
    <section v-if="product.description" class="store-product-detail__content"><h2>About this product</h2><div v-html="product.description"></div></section>
  </div></main></SiteLayout>
</template>
<style scoped>.store-product-detail{padding-top:18px}.store-product-detail__hero{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(320px,.9fr);gap:42px;align-items:start;margin-top:20px}.store-product-detail__image-wrap{position:relative;min-height:520px;overflow:hidden;border:1px solid #dce7e2;border-radius:3px;background:#eef6f2}.store-product-detail__image-wrap img,.store-product-detail__image-fallback{display:grid;width:100%;height:100%;min-height:520px;place-items:center;object-fit:cover;color:#527568;font-weight:800}.store-product-detail__badge{position:absolute;top:16px;left:16px;padding:7px 10px;border-radius:3px;background:#101b25;color:#fff;font-size:11px;font-weight:800}.store-product-detail__buybox{position:sticky;top:110px;padding:28px;border:1px solid #dce7e2;border-radius:3px;background:#fff;box-shadow:0 14px 38px rgba(16,27,37,.08)}.store-product-detail__eyebrow{margin:0 0 10px;color:#4f9381;font-size:11px;font-weight:800;letter-spacing:.18em;text-transform:uppercase}.store-product-detail__brand{margin:0 0 8px;color:var(--ink-600);font-weight:700}.store-product-detail h1{margin:0;color:#101828;font-size:clamp(34px,5vw,58px);line-height:1.05;letter-spacing:-.045em}.store-product-detail__summary{margin:22px 0;color:var(--ink-600);line-height:1.7}.store-product-detail__price{display:flex;flex-direction:column;margin:26px 0 8px}.store-product-detail__price small{color:var(--ink-600);font-size:11px;text-transform:uppercase;letter-spacing:.12em}.store-product-detail__price strong{font-size:28px}.store-product-detail__shipping{color:var(--ink-600);font-size:13px}.store-product-detail__button{width:100%;margin-top:16px;border:0;border-radius:3px;background:#101b25;color:#fff;padding:15px 20px;font-weight:800;cursor:pointer}.store-product-detail__content{max-width:760px;margin:52px 0 0;padding-top:28px;border-top:1px solid #dce7e2;color:var(--ink-600);line-height:1.7}.store-product-detail__content h2{margin:0 0 16px;color:#101828;font-size:28px}.store-product-detail__content :deep(p){margin:0 0 14px}@media(max-width:760px){.store-product-detail__hero{grid-template-columns:1fr;gap:24px}.store-product-detail__image-wrap,.store-product-detail__image-wrap img,.store-product-detail__image-fallback{min-height:360px}.store-product-detail__buybox{position:static;padding:22px}}
</style>
