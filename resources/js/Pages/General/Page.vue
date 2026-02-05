<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import SiteLayout from '@/Layouts/SiteLayout.vue'

const props = defineProps({
  title: { type: String, required: true },
  bodyHtml: { type: String, default: '' },
  metaDescription: { type: String, default: '' },
  canonical: { type: String, default: '' },
  noindex: { type: Boolean, default: false },
  ogImage: { type: String, default: '' },
})

const desc = computed(() => props.metaDescription || 'We Offer Wellness — curated therapies and classes that help you feel better, safely and simply.')
const canon = computed(() => props.canonical || (typeof window !== 'undefined' ? window.location.href.split('#')[0] : ''))
const isFullMarkup = computed(() => /<section\b/i.test(props.bodyHtml || ''))
</script>

<template>
  <Head :title="props.title">
    <meta name="description" :content="desc" />
    <link v-if="canon" rel="canonical" :href="canon" />
    <meta v-if="noindex" name="robots" content="noindex,follow" />
    <meta property="og:title" :content="props.title" />
    <meta property="og:description" :content="desc" />
    <meta v-if="canon" property="og:url" :content="canon" />
    <meta v-if="ogImage" property="og:image" :content="ogImage" />
    <meta name="twitter:card" content="summary_large_image" />
  </Head>
  <SiteLayout>
    <template v-if="isFullMarkup">
      <div v-html="props.bodyHtml"></div>
    </template>
    <template v-else>
      <section class="section">
        <div class="container-page">
          <h1 class="mb-3">{{ props.title }}</h1>
          <div class="prose" v-html="props.bodyHtml"></div>
        </div>
      </section>
    </template>
  </SiteLayout>
</template>

<style scoped>
.prose :deep(p){ margin: 0 0 1rem 0; color: var(--ink-800) }
.prose :deep(h2){ font-size:1.25rem; margin: 1.25rem 0 .5rem 0; font-weight:700 }
.prose :deep(ul){ padding-left: 1rem; margin: .5rem 0 1rem }
.prose :deep(li){ margin: .25rem 0 }
</style>
