<script setup>
import { Head, Link } from '@inertiajs/vue3'
import SiteLayout from '@/Layouts/SiteLayout.vue'
import WowButton from '@/Components/ui/WowButton.vue'

const title = 'Corporate Wellness'
const desc = 'Science‑backed workshops and experiences for teams — stress, sleep, energy and focus. On‑site or online.'
</script>

<template>
  <Head :title="title">
    <meta name="description" :content="desc" />
    <link rel="canonical" :href="(typeof window!=='undefined'? window.location.origin + '/corporate' : '/corporate')" />
  </Head>
  <SiteLayout>
    <!-- Hero -->
    <section class="section">
      <div class="container-page">
        <div class="wow-hero-card corporate-hero">
          <div class="wow-hero-grid">
            <div>
              <div class="kicker">For teams</div>
              <h1 class="mt-2 m-0">{{ title }}</h1>
              <p class="text-ink-700 text-lg mt-3">{{ desc }}</p>
              <div class="mt-4 flex flex-wrap gap-3">
                <WowButton as="a" href="#talk" variant="cta" :arrow="true">Talk to us</WowButton>
                <WowButton as="a" href="/corporate/wellbeing-workshops" variant="outline" :arrow="true">View workshops</WowButton>
              </div>
            </div>
            <div class="wow-hero-panel corporate-panel">
              <p class="text-ink-600 m-0">Programmes teams book most:</p>
              <div class="grid sm:grid-cols-2 gap-3 mt-3">
                <div class="corporate-chip">
                  <div class="h5 m-0">Stress &amp; burnout</div>
                  <div class="text-ink-600 mt-1">Breath and nervous-system resets.</div>
                </div>
                <div class="corporate-chip">
                  <div class="h5 m-0">Energy &amp; focus</div>
                  <div class="text-ink-600 mt-1">Cadence breathing & cold education.</div>
                </div>
                <div class="corporate-chip">
                  <div class="h5 m-0">Sleep &amp; recovery</div>
                  <div class="text-ink-600 mt-1">Wind-down routines and restorative work.</div>
                </div>
                <div class="corporate-chip">
                  <div class="h5 m-0">Hybrid delivery</div>
                  <div class="text-ink-600 mt-1">On-site or live online anywhere.</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Offerings -->
    <section class="section">
      <div class="container-page">
        <div class="kicker">What we offer</div>
        <h2 class="mt-2">Bring wellness to work</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
          <Link class="card p-5 hover:shadow-glow" href="/corporate/wellbeing-workshops">
            <div class="h5 m-0">Wellbeing Workshops</div>
            <div class="text-ink-600 mt-1">Stress, sleep, energy and focus — tailored.</div>
          </Link>
          <Link class="card p-5 hover:shadow-glow" href="/corporate/meditation">
            <div class="h5 m-0">Meditation for Teams</div>
            <div class="text-ink-600 mt-1">Guided sessions online or on‑site.</div>
          </Link>
          <Link class="card p-5 hover:shadow-glow" href="/corporate/breathwork">
            <div class="h5 m-0">Breathwork for Teams</div>
            <div class="text-ink-600 mt-1">Science‑backed breathing for resilience.</div>
          </Link>
          <Link class="card p-5 hover:shadow-glow" href="/corporate/sound-bath">
            <div class="h5 m-0">Workplace Sound Baths</div>
            <div class="text-ink-600 mt-1">Immersive relaxation with gongs & bowls.</div>
          </Link>
          <Link class="card p-5 hover:shadow-glow" href="/corporate/employee-rewards">
            <div class="h5 m-0">Employee Rewards</div>
            <div class="text-ink-600 mt-1">Meaningful perks that actually help.</div>
          </Link>
        </div>
      </div>
    </section>

    <!-- Why it works -->
    <section class="section">
      <div class="container-page">
        <div class="card p-6 md:p-8">
          <div class="grid md:grid-cols-3 gap-4">
            <div>
              <div class="kicker">Expert‑led</div>
              <div class="h5 m-0">Trusted practitioners</div>
              <p class="text-ink-600 mt-1">We vet every practitioner for safety, qualifications and client care.</p>
            </div>
            <div>
              <div class="kicker">Flexible</div>
              <div class="h5 m-0">On‑site or online</div>
              <p class="text-ink-600 mt-1">From team offsites to recurring lunch‑and‑learns, anywhere.</p>
            </div>
            <div>
              <div class="kicker">Measurable</div>
              <div class="h5 m-0">Feedback & usage</div>
              <p class="text-ink-600 mt-1">Track attendance and sentiment to prove impact.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Talk to us CTA -->
    <section id="talk" class="section">
      <div class="container-page">
        <div class="card p-6 md:p-8">
          <div class="grid md:grid-cols-2 gap-6 items-center">
            <div>
              <div class="kicker">Let’s plan it</div>
              <h3 class="m-0">Talk to us</h3>
              <p class="text-ink-600 mt-2">Tell us about your team, goals and dates — we’ll suggest a plan in under 24 hours.</p>
              <div class="mt-3 d-flex gap-2 flex-wrap">
                <WowButton as="a" href="/contact?topic=corporate" variant="cta" :arrow="true">Contact</WowButton>
                <WowButton as="a" href="mailto:hello@weofferwellness.co.uk" variant="outline">hello@weofferwellness.co.uk</WowButton>
              </div>
            </div>
            <div>
              <div class="grid sm:grid-cols-2 gap-3">
                <div class="card p-4"><div class="h5 m-0">1. Share goals</div><div class="text-ink-600 mt-1">Stress, sleep, energy, focus.</div></div>
                <div class="card p-4"><div class="h5 m-0">2. Get proposal</div><div class="text-ink-600 mt-1">Format, timing and costs.</div></div>
                <div class="card p-4"><div class="h5 m-0">3. Deliver</div><div class="text-ink-600 mt-1">On‑site or live online.</div></div>
                <div class="card p-4"><div class="h5 m-0">4. Measure</div><div class="text-ink-600 mt-1">Attendance and feedback.</div></div>
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
</style>
