<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import SiteLayout from '@/Layouts/SiteLayout.vue'
import WowButton from '@/Components/ui/WowButton.vue'

const props = defineProps({
  meta: { type: Object, default: () => ({}) },
})

const pageTitle = computed(() => props.meta?.title || 'Safety & Contraindications')
const pageDesc = computed(() => props.meta?.description || 'Important safety information before you book a session.')
const canonical = computed(() => {
  if (props.meta?.canonical) return props.meta.canonical
  if (typeof window !== 'undefined') return `${window.location.origin}/safety-and-contraindications`
  return ''
})

const heroHighlights = [
  { label: 'Last reviewed', value: 'July 2024' },
  { label: 'Scope', value: 'Therapies, classes & retreats' },
  { label: 'Emergency', value: 'Call 999 (UK)' },
]

const principles = [
  {
    title: 'Get medical clearance first',
    description: 'If you are pregnant, have a diagnosed condition or take prescription medication, confirm suitability with your GP or specialist before booking.',
    icon: 'bi-clipboard-pulse',
  },
  {
    title: 'Share accurate health history',
    description: 'Complete the intake form honestly and update your practitioner if anything changes — including diagnoses, medications or recent procedures.',
    icon: 'bi-chat-square-heart',
  },
  {
    title: 'Pause if anything feels wrong',
    description: 'Stop the session immediately if you feel light-headed, in pain, emotional distress or experience a new symptom, then seek medical advice.',
    icon: 'bi-exclamation-octagon',
  },
]

const riskGroups = [
  {
    title: 'Cardiovascular & circulation',
    summary: 'High or low blood pressure, arrhythmias, clotting disorders or history of stroke require sign-off before breathwork, intense heat or deep bodywork.',
    bullets: [
      'Uncontrolled hypertension or hypotension',
      'Recent cardiac event, surgery or stent',
      'History of blood clots, stroke or DVT',
    ],
  },
  {
    title: 'Pregnancy & postpartum',
    summary: 'First trimester, high-risk pregnancy or postpartum recovery may limit movements, temperature exposure and lying positions. Ask for modifications.',
    bullets: [
      'Placenta previa, pre-eclampsia or gestational diabetes',
      'Pelvic floor dysfunction or diastasis recti',
      'Postpartum recovery under 12 weeks or post C-section',
    ],
  },
  {
    title: 'Recent surgery, injuries & chronic pain',
    summary: 'Scar tissue, implants, hernias or unresolved injuries can be aggravated by massage, adjustments or inversions. Provide operative notes if relevant.',
    bullets: [
      'Spinal fusions, disc herniation or spondylitis',
      'Joint replacements or metal hardware',
      'Tendon/ligament tears or active inflammatory pain',
    ],
  },
  {
    title: 'Neurological & mental health',
    summary: 'Some techniques impact the nervous system and psyche. Work only with facilitators experienced in your condition and keep medication consistent.',
    bullets: [
      'Epilepsy or seizure disorders',
      'Bipolar disorder, psychosis or complex PTSD',
      'ADHD medication adjustments or withdrawal',
    ],
  },
]

const modalityCautions = [
  {
    title: 'Breathwork & cold exposure',
    summary: 'Powerful breath patterns, ice baths or contrast showers affect blood pressure, oxygenation and the vagus nerve.',
    badges: ['Asthma/COPD', 'Cardiac history', 'Pregnancy'],
    points: [
      'Avoid breath holds or intense hyperventilation with cardiovascular disease or glaucoma.',
      'People with asthma or COPD need inhalers nearby and milder pacing.',
      'Pregnant clients should stick to gentle nasal breathing and avoid extreme temperature shifts.',
    ],
  },
  {
    title: 'Sound therapy & frequency work',
    summary: 'Vibrations and binaural beats can unsettle those with neurological or hearing conditions.',
    badges: ['Migraines', 'Epilepsy', 'Tinnitus'],
    points: [
      'Remove hearing aids or implanted devices only if cleared by your audiologist.',
      'History of seizures requires avoiding flashing lights and very low frequencies.',
      'Keep volume below 85 dB for those with tinnitus or sound sensitivity.',
    ],
  },
  {
    title: 'Manual therapy & bodywork',
    summary: 'Massage, fascial release and adjustments need adaptations for bone density, clotting and skin integrity.',
    badges: ['Osteoporosis', 'Blood thinners', 'Skin conditions'],
    points: [
      'No deep tissue over varicose veins, open wounds or fragile skin.',
      'Inform therapists about anticoagulants to prevent excessive bruising.',
      'Lymphatic work can mobilise fluids — avoid with uncontrolled infections.',
    ],
  },
  {
    title: 'Herbal medicine & supplements',
    summary: 'Natural does not mean safe. Many botanicals interact with common prescriptions.',
    badges: ['SSRIs', 'IBS/IBD', 'Autoimmune'],
    points: [
      'St John’s Wort alters how the liver processes antidepressants and contraceptives.',
      'Adaptogens can raise blood pressure or interfere with thyroid medication.',
      'Always disclose allergies and current supplements to avoid duplication.',
    ],
  },
]

const sessionSteps = [
  {
    stage: 'Before your session',
    title: 'Pre-session checklist',
    points: [
      'Review contraindications on the listing and confirm logistics (in-person vs online, required equipment).',
      'Share allergies, current medications, implants, pacemakers or medical devices.',
      'Eat and hydrate appropriately — fasting or overhydrating can both cause dizziness.',
    ],
  },
  {
    stage: 'During the session',
    title: 'Stay aware and communicate',
    points: [
      'Tell the practitioner immediately if you feel heat, numbness, palpitations or distress.',
      'Take breaks for water, rest or fresh air whenever needed — you set the pace.',
      'Avoid sudden standing after breathwork or bodywork to prevent drops in blood pressure.',
    ],
  },
  {
    stage: 'Aftercare',
    title: 'Monitor recovery',
    points: [
      'Notice your mood, sleep, bleeding, bruising or swelling for 24–48 hours.',
      'Follow the practitioner’s aftercare notes: hydration, journaling, stretching or referrals.',
      'Contact medical services if you experience worsening symptoms or anything listed below.',
    ],
  },
]

const redFlags = [
  'Chest pain, tightness or sudden shortness of breath',
  'Fainting, seizures or difficulty speaking clearly',
  'Loss of sensation, facial droop or inability to move a limb',
  'Heavy bleeding, severe abdominal pain or unusual discharge',
  'Any allergic reaction: swelling of lips, tongue or throat',
]

const medicationNotes = [
  {
    title: 'Anticoagulants & antiplatelets',
    description: 'Deep tissue massage, cupping or acupuncture can increase bruising and bleeding risk.',
    examples: 'Warfarin, Apixaban, Clopidogrel, aspirin therapy',
  },
  {
    title: 'SSRIs, SNRIs & MAOIs',
    description: 'Some herbs and breathwork intensives impact serotonin and blood pressure. Titrate only with your prescriber.',
    examples: 'Sertraline, Duloxetine, Venlafaxine, Moclobemide',
  },
  {
    title: 'Stimulants & beta blockers',
    description: 'Practices that spike or drop heart rate (ice baths, HIIT, intense breathwork) need cautious pacing.',
    examples: 'Methylphenidate, Adderall, Propranolol, Bisoprolol',
  },
  {
    title: 'Diabetes medication',
    description: 'Long sessions, saunas or fasting breathwork can shift blood sugar quickly. Keep snacks and monitoring devices accessible.',
    examples: 'Insulin, Metformin, SGLT2 inhibitors, GLP-1 agonists',
  },
]

const providerDuties = [
  'Publish contraindications, equipment needs and access considerations on your listing.',
  'Collect and review intake forms before the session; escalate anything outside your scope.',
  'Hold active professional indemnity and public liability insurance for your modality.',
  'Stop or modify the session if a client experiences distress, red-flag symptoms or requests a pause.',
  'Document and report any incident to We Offer Wellness support within 24 hours.',
]
</script>

<template>
  <Head :title="pageTitle">
    <meta name="description" :content="pageDesc" />
    <link v-if="canonical" rel="canonical" :href="canonical" />
  </Head>
  <SiteLayout>
    <section class="section">
      <div class="container-page">
        <div class="wow-hero-card safety-hero">
          <div class="wow-hero-grid">
            <div>
              <div class="kicker">Safety guidance</div>
              <h1 class="mt-3">Safety &amp; Contraindications</h1>
              <p class="text-ink-700 text-lg mt-3">Wellness should complement, never compromise, your care plan. Use this checklist before booking therapies, classes and retreats through We Offer Wellness.</p>
              <div class="hero-highlights">
                <div v-for="item in heroHighlights" :key="item.label" class="hero-highlight">
                  <p class="label">{{ item.label }}</p>
                  <p class="value">{{ item.value }}</p>
                </div>
              </div>
              <p class="mt-3 text-ink-500 small-text">This guidance supports, but does not replace, advice from your GP or healthcare team. When in doubt, pause and seek clinical input.</p>
            </div>
            <div class="wow-hero-panel safety-panel">
              <h3>Need help triaging a therapy?</h3>
              <p class="text-ink-600">Share your medical context and we’ll advise which modalities are appropriate or suggest alternatives.</p>
              <ul>
                <li><strong>Email:</strong> <a href="mailto:hello@weofferwellness.co.uk" class="link">hello@weofferwellness.co.uk</a></li>
                <li><strong>Support line:</strong> +44 (0)20 4525 2112</li>
                <li><strong>Response time:</strong> under 1 business day</li>
              </ul>
              <div class="mt-4 flex flex-wrap gap-3">
                <WowButton as="a" href="/contact?topic=safety" variant="cta" arrow>Contact support</WowButton>
                <WowButton as="a" href="/reviews" variant="ghost">Read client stories</WowButton>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container-page">
        <div class="section-heading">
          <div class="kicker">Before you book</div>
          <h2>Three actions to take every time</h2>
          <p class="text-ink-600">Your practitioner relies on accurate information to keep you safe. These steps protect you and ensure the session is tailored to your needs.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-4">
          <article v-for="item in principles" :key="item.title" class="safety-card">
            <div class="icon-wrap"><i class="bi" :class="item.icon" aria-hidden="true"></i></div>
            <h3>{{ item.title }}</h3>
            <p>{{ item.description }}</p>
          </article>
        </div>
      </div>
    </section>

    <section class="section section-muted">
      <div class="container-page">
        <div class="section-heading">
          <div class="kicker">Who should double-check with a clinician</div>
          <h2>Risk groups with stricter contraindications</h2>
          <p class="text-ink-600">If you recognise yourself below, get sign-off from your healthcare provider and confirm modifications with the practitioner before booking.</p>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
          <article v-for="group in riskGroups" :key="group.title" class="detail-card">
            <h3>{{ group.title }}</h3>
            <p>{{ group.summary }}</p>
            <ul>
              <li v-for="bullet in group.bullets" :key="bullet">{{ bullet }}</li>
            </ul>
          </article>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container-page">
        <div class="section-heading">
          <div class="kicker">Modalities at a glance</div>
          <h2>What to know about popular therapies</h2>
          <p class="text-ink-600">Every practitioner on WOW lists contraindications, but this quick guide helps you ask informed questions.</p>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
          <article v-for="modality in modalityCautions" :key="modality.title" class="detail-card modality-card">
            <div class="card-top">
              <div>
                <h3>{{ modality.title }}</h3>
                <p>{{ modality.summary }}</p>
              </div>
              <div class="badge-stack">
                <span v-for="badge in modality.badges" :key="badge" class="chip chip-brand">{{ badge }}</span>
              </div>
            </div>
            <ul>
              <li v-for="point in modality.points" :key="point">{{ point }}</li>
            </ul>
          </article>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container-page">
        <div class="section-heading">
          <div class="kicker">During &amp; after</div>
          <h2>Session timeline</h2>
          <p class="text-ink-600">Use this flow to stay present, advocate for yourself and flag anything unexpected.</p>
        </div>
        <div class="timeline">
          <article v-for="step in sessionSteps" :key="step.stage" class="timeline-card">
            <div class="timeline-badge">{{ step.stage }}</div>
            <h3>{{ step.title }}</h3>
            <ul>
              <li v-for="point in step.points" :key="point">{{ point }}</li>
            </ul>
          </article>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container-page">
        <div class="red-flag">
          <div>
            <div class="kicker">Stop immediately</div>
            <h2>Red-flag symptoms</h2>
            <p class="text-ink-600">End the session, call emergency services (999 in the UK) or NHS 111 for urgent medical advice.</p>
          </div>
          <ul>
            <li v-for="flag in redFlags" :key="flag">{{ flag }}</li>
          </ul>
        </div>
      </div>
    </section>

    <section class="section section-muted">
      <div class="container-page">
        <div class="section-heading">
          <div class="kicker">Medication &amp; supplement interactions</div>
          <h2>Share everything you take — including over-the-counter items</h2>
          <p class="text-ink-600">Practitioners are trained to stay within their scope, but only you and your prescriber can confirm if adjustments are needed.</p>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
          <article v-for="note in medicationNotes" :key="note.title" class="detail-card">
            <h3>{{ note.title }}</h3>
            <p>{{ note.description }}</p>
            <p class="text-ink-500 small-text"><strong>Examples:</strong> {{ note.examples }}</p>
          </article>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container-page">
        <div class="section-heading">
          <div class="kicker">Practitioner responsibilities</div>
          <h2>How we keep sessions safe</h2>
          <p class="text-ink-600">Every practitioner on WOW signs our standards agreement and agrees to the following practices.</p>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
          <article class="detail-card" v-for="duty in providerDuties" :key="duty">
            <div class="icon-wrap icon-small"><i class="bi bi-shield-check" aria-hidden="true"></i></div>
            <p>{{ duty }}</p>
          </article>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container-page">
        <div class="cta-panel">
          <div>
            <div class="kicker">Need bespoke guidance?</div>
            <h2>Talk to the WOW safety team</h2>
            <p class="text-ink-600">Send us your health considerations, preferred modalities or upcoming events and we’ll suggest vetted practitioners who can accommodate you.</p>
          </div>
          <div class="cta-actions">
            <WowButton as="a" href="/contact?topic=support" variant="cta" arrow>Message support</WowButton>
            <WowButton as="a" href="mailto:hello@weofferwellness.co.uk" variant="outline">Email hello@weofferwellness.co.uk</WowButton>
          </div>
        </div>
      </div>
    </section>
  </SiteLayout>
</template>

<style scoped>
.section-heading { max-width: 52rem; margin-bottom: 1.75rem; }
.section-heading h2 { margin-top: 0.5rem; }
.section-muted { background: var(--panel); }
.section-muted .container-page { padding-top: 0.5rem; padding-bottom: 0.5rem; }
.safety-hero { background: linear-gradient(135deg, #f6f8ff, #fefaf5); }
.hero-highlights { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem; margin-top: 1.5rem; }
.hero-highlight { padding: 0.75rem 1rem; border-radius: 16px; border: 1px solid var(--ink-200); background: #fff; }
.hero-highlight .label { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--ink-500); margin: 0; }
.hero-highlight .value { margin: 0.2rem 0 0; font-weight: 600; color: var(--ink-900); }
.small-text { font-size: 0.9rem; }
.safety-panel ul { list-style: none; padding: 0; margin: 1rem 0 0; }
.safety-panel li { margin-bottom: 0.35rem; color: var(--ink-700); }
.safety-card { border: 1px solid var(--ink-150, #e5e7eb); border-radius: 20px; padding: 1.5rem; background: #fff; box-shadow: 0 15px 35px rgba(15, 23, 42, 0.06); }
.safety-card h3 { margin-top: 0.5rem; margin-bottom: 0.5rem; }
.icon-wrap { width: 48px; height: 48px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; background: var(--ink-50); color: var(--brand-600); font-size: 1.5rem; }
.icon-small { width: 40px; height: 40px; margin-bottom: 0.75rem; }
.detail-card { border: 1px solid var(--ink-200); border-radius: 24px; padding: 1.5rem; background: #fff; box-shadow: 0 18px 40px rgba(15, 23, 42, 0.05); }
.detail-card h3 { margin-top: 0; margin-bottom: 0.35rem; }
.detail-card ul { margin: 0.75rem 0 0; padding-left: 1.2rem; color: var(--ink-700); }
.detail-card li { margin-bottom: 0.35rem; }
.modality-card .card-top { display: flex; flex-direction: column; gap: 0.75rem; }
@media (min-width: 768px){ .modality-card .card-top { flex-direction: row; justify-content: space-between; } }
.badge-stack { display: flex; flex-wrap: wrap; gap: 0.35rem; }
.timeline { display: grid; gap: 1rem; }
@media (min-width: 768px){ .timeline { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
.timeline-card { border: 1px solid var(--ink-200); border-radius: 24px; padding: 1.5rem; background: #fff; position: relative; }
.timeline-badge { display: inline-flex; align-items: center; font-weight: 600; color: var(--brand-700); background: var(--brand-50); border-radius: 999px; padding: 0.25rem 0.85rem; font-size: 0.85rem; margin-bottom: 0.75rem; }
.red-flag { border-radius: 28px; padding: 2rem; background: #fff2ed; border: 1px solid color-mix(in srgb, var(--accent-400) 40%, white); display: grid; gap: 1.25rem; }
@media (min-width: 768px){ .red-flag { grid-template-columns: 0.9fr 1.1fr; align-items: center; } }
.red-flag ul { margin: 0; padding-left: 1.2rem; color: var(--accent-900); }
.cta-panel { border-radius: 30px; padding: 2rem; background: linear-gradient(135deg, #ecfdf5, #ffffff); border: 1px solid color-mix(in srgb, var(--brand-200) 60%, white); display: grid; gap: 1.25rem; }
@media (min-width: 768px){ .cta-panel { grid-template-columns: 1.2fr 0.8fr; align-items: center; } }
.cta-actions { display: flex; flex-direction: column; gap: 0.75rem; }
@media (min-width: 640px){ .cta-actions { flex-direction: row; flex-wrap: wrap; } }
</style>
