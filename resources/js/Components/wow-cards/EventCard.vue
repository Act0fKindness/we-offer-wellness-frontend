<script setup>
import { ref } from 'vue'
import SquareButton from '@/Components/ui/SquareButton.vue'
import WowButton from '@/Components/ui/WowButton.vue'
import { useCart } from '@/stores/cart'

const props = defineProps({
  size: { type: String, default: 'md' }, // 'md' | 'xl'
  image: { type: String, required: true },
  alt: { type: String, default: '' },
  badges: { type: Array, default: () => [
    { icon: 'bi-megaphone', text: 'Sponsored' },
    { icon: 'bi-calendar2-week', text: '25 Oct – 29 Nov' },
  ] },
  typeLabel: { type: String, default: 'Event • Multi-date course' },
  title: { type: String, required: true },
  rating: { type: [String, Number], default: '4.9' },
  hasReviews: { type: Boolean, default: true },
  reviewsText: { type: String, default: 'Excellent • 60 reviews' },
  detailsMeta: { type: Array, default: () => [
    { icon: 'bi-geo-alt', text: 'Langton Green Village Hall, TN3 0JJ' },
    { icon: 'bi-clock-history', text: '10:00–12:00' },
    { icon: 'bi-people', text: 'Few left' },
  ] },
  tags: { type: Array, default: () => [
    { icon: 'bi-music-note-beamed', text: 'Live gongs', dark: true },
    { icon: 'bi-activity', text: 'Tai Chi • Shibashi', dark: true },
    { icon: 'bi-emoji-sunglasses', text: 'Guest teacher', dark: true },
  ] },
  policies: { type: Array, default: () => [
    { icon: 'bi-check2-circle', text: 'Free cancellation 24h', good: true },
  ] },
  ticker: { type: String, default: 'LIVE • Limited tickets • Breathwork warm-up • Free tea at break • Doors 09:45 • ' },
  price: { type: String, default: '£65.00' },
  priceNote: { type: String, default: '/ per session' },
  availText: { type: String, default: '3 spots left' },
  ctaLabel: { type: String, default: 'Book Event' },
  ctaHref: { type: String, default: '#' },
  product: { type: Object, default: () => ({}) },
})

const liked = ref(false)
const sharing = ref(false)
const cart = useCart()

function addToCart(e){ /* disabled */ }
async function share(){
  const url = props.product?.url || props.ctaHref || window.location.href
  try { await navigator.clipboard.writeText(url); sharing.value = true; setTimeout(()=>sharing.value=false, 900) } catch { alert('Link: ' + url) }
}
</script>

<template>
  <article :class="['wow-card', props.size === 'md' ? 'md' : '', 'event']" data-type="event" tabindex="0">
    <div class="wow-media">
      <span class="event-glaze"></span>
      <img :src="image" :alt="alt || title">

      <!-- Badges -->
      <div class="wow-badges">
        <span v-for="(b,i) in badges" :key="i" class="badge-chip"><i :class="['bi', b.icon]"></i> {{ b.text }}</span>
      </div>

      <!-- Like / Share -->
      <div class="wow-overlay-actions">
        <WowButton variant="outline" size="sm" :squarish="true" :aria-label="liked ? 'Remove from favourites' : 'Add to favourites'" @click="liked=!liked">
          <i :class="['bi', liked ? 'bi-heart-fill' : 'bi-heart']"></i>
        </WowButton>
        <WowButton variant="outline" size="sm" :squarish="true" aria-label="Share" @click="share">
          <i class="bi bi-share" :class="{ 'text-brand-400': sharing }"></i>
        </WowButton>
        <template v-if="!hasReviews">
          <span class="rating-tile rating-tile--wow rating-approved" title="Approved" aria-label="Approved">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1764 1764" aria-hidden="true">
              <g transform="translate(324.5,200)">
                <path fill="#ffffff" d="M556.68,1.16c56.42,35.17,110.95,73.94,161.8,116.78,60.19,50.71,118.5,108.96,158.45,177.22l-242.91,230.97c-22.66,19.27-43.36,41.78-66,60.96-2.23,1.89-9.41,8.52-11.54,8.46L237.07,295.16c39.98-68.26,98.26-126.5,158.45-177.22,50.75-42.76,105.2-81.14,161.15-116.78Z"/>
                <g>
                  <path fill="#ffffff" d="M529,1364h-8l-67.23-10.27C193.22,1301.1,47.58,1096.94,8.13,843.75L0,775.43v-19.99l360.47,345.75c7.82-13.07-4.98-32.27-12.06-43.89-26.58-43.62-67.87-89.47-101.9-128-73.96-83.76-152.99-162.93-231.03-242.84l-.31-3.19c13.08-47.87,26.91-101.54,52.67-144.33.69-1.15,2.13-4.89,3.63-4.31,72.21,73.62,147.03,144.85,228.07,208.78,20.54,16.2,41.42,32.43,63.44,46.52-66.4-117.24-164.66-213.16-259.83-307.01-.97-3.59,9.33-22.48,11.68-26.93,17.09-32.38,40.68-66.66,64.2-94.73,2.68-3.2,18-21.54,20.48-21.54l328.55,315.25,1.01,3.99-.06,705.04Z"/>
                  <path fill="#ffffff" d="M586,1364l-.06-705.04,1.01-3.99,328.55-315.25c2.47,0,17.8,18.35,20.48,21.54,23.51,28.08,47.1,62.35,64.2,94.73,2.35,4.45,12.66,23.34,11.68,26.93-68.85,67.19-137.24,135.48-196.25,211.67-23.38,30.18-45.7,61.52-63.58,95.34,22.01-14.09,42.9-30.32,63.44-46.52,81.05-63.93,155.86-135.16,228.07-208.78,1.51-.58,3.3,3.77,3.99,4.95,4.57,7.81,9.53,18.32,13.34,26.64,17.49,38.26,30.27,79.02,38.67,120.25-78.05,79.9-157.08,159.07-231.04,242.83-34.14,38.67-87.03,96.17-109.66,141.24-4.1,8.18-12.22,23.24-4.29,30.64l360.46-345.74v19.99c-1.22,2.15-1.76,6.02-1.99,8.5-26.33,281.9-169.3,518.64-463.83,571.76l-56.19,8.31h-7Z"/>
                </g>
              </g>
            </svg>
            <span class="rating-label">Approved</span>
          </span>
        </template>
      </div>

      <!-- Ticker -->
      <div class="event-ticker" aria-hidden="true">
        <span>{{ ticker }} {{ ticker }}</span>
      </div>

      <!-- Pinned head -->
      <div class="event-head">
        <div class="wow-type">{{ typeLabel }}</div>
        <h3 class="wow-title truncate-2">{{ title }}</h3>
      </div>

      <!-- Sliding details on hover -->
      <div class="event-details">
        <div class="wow-rating">
          <template v-if="hasReviews">
            <span class="rating-tile"><i class="bi bi-star-fill"></i> {{ rating }}</span>
            <span class="rating-text">{{ reviewsText }}</span>
          </template>
          <!-- no else: Approved badge shown in overlay-actions -->
        </div>

        <div class="wow-meta">
          <span v-for="(m,idx) in detailsMeta" :key="'m'+idx" :class="['chip', m.dark ? 'chip-dark' : '']"><i :class="['bi', m.icon]"></i> {{ m.text }}</span>
        </div>

        <div class="wow-meta">
          <span v-for="(t,idx) in tags" :key="'t'+idx" class="chip chip-dark"><i :class="['bi', t.icon]"></i> {{ t.text }}</span>
        </div>

        <div class="wow-meta">
          <span v-for="(p,idx) in policies" :key="'p'+idx" class="chip good"><i class="bi bi-check2-circle"></i>{{ p.text }}</span>
        </div>

        <div class="event-price">
          <div class="price">{{ price }} <small>{{ priceNote }}</small></div>
          <div class="avail low"><i class="bi bi-exclamation-circle"></i> {{ availText }}</div>
        </div>
        <div class="event-actions">
          <SquareButton as="a" :href="ctaHref" variant="cta" size="md">{{ ctaLabel }}</SquareButton>
          <SquareButton type="button" variant="outline" size="md" class="btn-icon is-square d-inline-flex align-items-center" aria-label="Add to cart">
            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h1.5L8 16m0 0h8m-8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm.75-3H7.5M11 7H6.312M17 4v6m-3-3h6"/>
            </svg>
          </SquareButton>
        </div>
      </div>
    </div>
  </article>
</template>

<style scoped>
</style>
