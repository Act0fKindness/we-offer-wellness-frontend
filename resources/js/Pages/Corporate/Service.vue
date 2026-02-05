<script setup>
import { Head } from '@inertiajs/vue3'
import SiteLayout from '@/Layouts/SiteLayout.vue'

const props = defineProps({
  slug: { type: String, required: true },
  page: { type: Object, required: true },
})

const title = (props.page?.title || '').trim() || 'Corporate'
const desc = (props.page?.desc || '').trim()
const features = Array.isArray(props.page?.features) ? props.page.features : []
</script>

<template>
  <Head :title="title" />
  <SiteLayout>
    <section class="section">
      <div class="container-page">
        <div class="mb-6">
          <div class="kicker">Business</div>
          <h1>{{ title }}</h1>
          <p v-if="desc" class="text-ink-600 text-lg mt-2 max-w-2xl">{{ desc }}</p>
        </div>

        <!-- CTA strip -->
        <div class="card p-4 md:p-5 mb-5 d-flex flex-wrap gap-2 align-items-center">
          <a href="/contact?topic=corporate" class="btn btn-primary">Enquire</a>
          <a href="mailto:hello@weofferwellness.co.uk" class="btn btn-outline">Email hello@weofferwellness.co.uk</a>
          <div class="text-ink-600 ms-auto text-sm">On‑site and online options available</div>
        </div>

        <!-- Feature cards -->
        <div v-if="features.length" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="(f,i) in features" :key="i" class="card p-5">
            <div class="chip mb-2">{{ i+1 }}</div>
            <h2 class="h6 mb-1">{{ f.title }}</h2>
            <p class="text-ink-700 mb-0">{{ f.text }}</p>
          </div>
        </div>

        <!-- FAQ placeholder -->
        <div class="card p-5 mt-6">
          <h3 class="h6 mb-3">FAQ</h3>
          <details class="mb-2"><summary>How many people can join?</summary><div class="mt-1 text-ink-700">Typical sessions support 10–60 attendees, depending on format and space.</div></details>
          <details class="mb-2"><summary>Can you deliver online?</summary><div class="mt-1 text-ink-700">Yes — all formats are available live on Zoom or Google Meet.</div></details>
          <details class="mb-0"><summary>Do you provide equipment?</summary><div class="mt-1 text-ink-700">For sound baths and similar, we can bring mats and instruments if needed.</div></details>
        </div>
      </div>
    </section>
  </SiteLayout>
</template>

<style scoped>
.h6 { font-weight: 700; font-size: 1.05rem; }
.text-sm { font-size: .9rem; }
</style>

