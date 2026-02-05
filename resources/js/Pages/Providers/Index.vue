<script setup>
import { Head } from '@inertiajs/vue3'
import SiteLayout from '@/Layouts/SiteLayout.vue'
import WowButton from '@/Components/ui/WowButton.vue'

const props = defineProps({
  title: { type: String, default: 'Practitioners' },
  metaDescription: { type: String, default: 'Find trusted wellness practitioners across therapies and modalities.' },
})

const practitionerSignupUrl = import.meta.env.VITE_ATEASE_SIGNUP_URL || 'https://atease.weofferwellness.co.uk/register'
</script>

<template>
  <Head :title="props.title">
    <meta name="description" :content="props.metaDescription" />
    <link rel="canonical" :href="(typeof window!=='undefined'?window.location.origin + '/providers':'/providers')" />
  </Head>
  <SiteLayout>
    <section class="section">
      <div class="container-page">
        <div class="wow-hero-card provider-hero">
          <div class="wow-hero-grid">
            <div>
              <div class="kicker">Become a practitioner</div>
              <h1 class="mt-2 m-0">{{ props.title }}</h1>
              <p class="text-ink-700 text-lg mt-3">Join WOW to reach thoughtful clients, receive timely payouts and contribute to a trauma-aware marketplace.</p>
              <div class="mt-4 flex flex-wrap gap-3">
                <WowButton as="a" :href="practitionerSignupUrl" variant="cta" :arrow="true">Apply now</WowButton>
                <WowButton as="a" href="/contact?topic=partners" variant="ghost">Talk to partnerships</WowButton>
              </div>
            </div>
            <div class="wow-hero-panel provider-panel">
              <p class="text-ink-600 m-0">What we look for</p>
              <ul class="mt-3">
                <li>Active insurance and evidence of training.</li>
                <li>Trauma-aware, inclusive client care.</li>
                <li>Transparent pricing &amp; cancellation terms.</li>
              </ul>
              <p class="text-ink-600 mt-4 text-sm">Applications are reviewed weekly. Expect a response within five business days.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </SiteLayout>
</template>

<style scoped>
.provider-panel ul{ padding-left:1.25rem; color:var(--ink-700); }
.provider-panel li{ margin-bottom:.35rem; }
</style>
