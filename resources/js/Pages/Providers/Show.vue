<script setup>
import { Head } from '@inertiajs/vue3'
import SiteLayout from '@/Layouts/SiteLayout.vue'

const props = defineProps({ slug: { type: String, required: true } })
const title = `Practitioner: ${props.slug.replace(/-/g,' ')}`
</script>

<template>
  <Head :title="title">
    <meta name="robots" content="noindex,follow" />
  </Head>
  <SiteLayout>
    <section class="section">
      <div class="container-page">
        <h1 class="mb-3">{{ title }}</h1>
        <p class="text-ink-700">Profile page placeholder. This route is reserved; full profiles will be available soon.</p>
      </div>
    </section>
  </SiteLayout>
</template>

