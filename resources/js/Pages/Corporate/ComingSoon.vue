<script setup>
import { Head } from '@inertiajs/vue3'
import SiteLayout from '@/Layouts/SiteLayout.vue'
import WowButton from '@/Components/ui/WowButton.vue'

const title = 'Corporate wellness – coming in 2026'
const desc = 'We are building a tailored wellness programme for teams with science-backed workshops, nervous-system resets and measurable outcomes.'
const highlights = [
  { label: 'Tailored workshops', copy: 'Stress, sleep, menopause support and more — built for your team size and cadence.' },
  { label: 'Trusted practitioners', copy: 'Every facilitator is vetted for trauma-awareness, qualifications and duty of care.' },
  { label: 'Hybrid delivery', copy: 'On-site in the UK or live online anywhere, with instant recordings for follow-up.' },
]
</script>

<template>
  <Head :title="title">
    <meta name="description" :content="desc" />
    <link rel="canonical" :href="(typeof window !== 'undefined' ? window.location.origin : '') + '/corporate-wellness'" />
  </Head>
  <SiteLayout>
    <section class="section">
      <div class="container-page">
        <div class="wow-hero-card corporate-hero">
          <div class="wow-hero-grid">
            <div>
              <div class="kicker">For People & Culture teams</div>
              <h1 class="mt-3 m-0">{{ title }}</h1>
              <p class="text-ink-700 text-lg mt-3">{{ desc }}</p>
              <div class="mt-5 flex flex-wrap gap-3">
                <WowButton as="a" href="/contact?topic=corporate" variant="cta" :arrow="true">Join the waitlist</WowButton>
                <WowButton as="a" href="mailto:hello@weofferwellness.co.uk" variant="outline">Email us</WowButton>
              </div>
            </div>
            <div class="wow-hero-panel corporate-panel">
              <p class="text-ink-600 m-0">What’s included</p>
              <div class="grid sm:grid-cols-2 gap-3 mt-3">
                <div v-for="item in highlights" :key="item.label" class="corporate-chip">
                  <div class="kicker">{{ item.label }}</div>
                  <p class="mt-2 text-ink-600">{{ item.copy }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <section class="section">
      <div class="container-page">
        <div class="card p-6 md:p-8">
          <div class="grid md:grid-cols-2 gap-6">
            <div>
              <div class="kicker">What to expect</div>
              <h2 class="mt-2">We’re building this with HR partners</h2>
              <ul class="corp-list">
                <li>Programme design workshops with People teams.</li>
                <li>Flexible formats: lunch-and-learn, full day, or rolling series.</li>
                <li>Usage dashboards and anonymised feedback.</li>
              </ul>
            </div>
            <div class="bg-ink-50 rounded-2xl p-5">
              <h3 class="m-0">Want early access?</h3>
              <p class="text-ink-600 mt-2">We’re onboarding pilot partners throughout 2025. Share your goals and we’ll keep you in the loop.</p>
              <div class="mt-4 flex flex-wrap gap-3">
                <WowButton as="a" href="/contact?topic=corporate" variant="cta">Talk to the team</WowButton>
                <WowButton as="a" href="mailto:hello@weofferwellness.co.uk" variant="ghost">hello@weofferwellness.co.uk</WowButton>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </SiteLayout>
</template>

<style scoped>
.corporate-hero{ background:linear-gradient(135deg,#eef5ff,#ffffff); }
.corporate-panel .corporate-chip{ border:1px solid rgba(15,23,42,.08); border-radius:16px; padding:1rem; background:#f8fafc; }
.corp-list{ margin:0; padding-left:1.25rem; color:var(--ink-700); line-height:1.6; }
.corp-list li{ margin-bottom:.35rem; }
</style>
