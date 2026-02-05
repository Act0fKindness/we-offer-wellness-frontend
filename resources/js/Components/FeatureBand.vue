<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import WowButton from '@/Components/ui/WowButton.vue'

const props = defineProps({
  painpoint: { type: Object, default: null },
})

const title = () => props.painpoint?.feeling || 'Calm & grounded'
const eyebrow = () => 'Loved by our experts'
const sub = () => 'Curated picks to meet you where you are.'
const cta = () => `Explore ${props.painpoint?.name || 'Stress & overwhelm'}`
const FALLBACK_IMG = 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=1400&q=80'
const images = computed(() => {
  const arr = Array.isArray(props.painpoint?.images) ? props.painpoint.images.filter(Boolean) : []
  const hero = props.painpoint?.image_url
  const out = [...arr]
  if (!out.length && hero) out.push(hero)
  return out.length ? out : [FALLBACK_IMG]
})
const href = () => `/need/${props.painpoint?.key || 'stress'}`

const idx = ref(0)
const imgSrc = ref(FALLBACK_IMG)

function chooseRandom(){
  const list = images.value
  if (!Array.isArray(list) || list.length === 0) { idx.value = 0; imgSrc.value = FALLBACK_IMG; return }
  idx.value = Math.floor(Math.random() * list.length)
  imgSrc.value = list[idx.value] || FALLBACK_IMG
}

function onImgError(e){
  if (e?.target) e.target.src = FALLBACK_IMG
  imgSrc.value = FALLBACK_IMG
}

onMounted(chooseRandom)
watch(() => props.painpoint?.key, chooseRandom)
watch(images, chooseRandom)
</script>

<template>
  <section class="section">
    <div class="container-page">
      <div class="card p-6 md:p-8">
        <div class="grid md:grid-cols-2 gap-6 items-center">
          <div>
            <div class="kicker">{{ eyebrow() }}</div>
            <h3 class="m-0">{{ title() }}</h3>
            <p class="text-ink-600 mt-2">{{ sub() }}</p>
            <div class="mt-4">
              <WowButton as="a" :href="href()" variant="outline" :arrow="true">{{ cta() }}</WowButton>
            </div>
          </div>
          <div class="img-wrap">
            <img :src="imgSrc" :alt="title()" @error="onImgError" class="w-full rounded-lg" style="object-fit:cover; max-height:360px" />
            <div class="img-overlay"></div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.img-wrap{ position:relative }
.img-overlay{ position:absolute; inset:0; border-radius: 0.5rem; background: rgba(0,0,0,.28); pointer-events:none }
</style>
