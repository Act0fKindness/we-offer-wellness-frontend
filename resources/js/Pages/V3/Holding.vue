<template>
  <SiteLayout>
    <div class="v3-temp">
      <section class="v3-temp__intro">
        <div class="v3-temp__container">
          <p class="v3-temp__eyebrow">Currently polishing v3</p>
          <h1>Holistic booking that feels as caring as the sessions.</h1>
          <p class="v3-temp__lede">We're building a calmer, smarter marketplace for therapies, classes and retreats — co-created with nervous-system specialists, trauma-aware practitioners and a community who wants booking to feel safe.</p>
          <div class="v3-temp__stat-grid">
            <article v-for="stat in stats" :key="stat.label" class="v3-temp__stat-card">
              <div class="value">{{ stat.value }}</div>
              <div class="label">{{ stat.label }}</div>
              <p>{{ stat.detail }}</p>
            </article>
          </div>
        </div>
      </section>

      <section class="v3-temp__pillars">
        <div class="v3-temp__container">
          <div class="v3-temp__section-heading">
            <p class="eyebrow">What we're improving</p>
            <h2>Co-created with our practitioner council & hundreds of beta testers.</h2>
          </div>
          <div class="v3-temp__pillar-grid">
            <article v-for="card in promises" :key="card.title" class="pillar-card">
              <div class="icon" aria-hidden="true">{{ card.icon }}</div>
              <h3>{{ card.title }}</h3>
              <p>{{ card.copy }}</p>
            </article>
          </div>
        </div>
      </section>

      <section class="v3-temp__features">
        <div class="v3-temp__container">
          <div class="v3-temp__section-heading">
            <p class="eyebrow">Feature highlights</p>
            <h2>Everything clients kept asking for (and a few surprises).</h2>
          </div>
          <div class="feature-grid">
            <article v-for="feature in features" :key="feature.title" class="feature-card">
              <h3>{{ feature.title }}</h3>
              <p>{{ feature.detail }}</p>
            </article>
          </div>
        </div>
      </section>

      <section class="v3-temp__experience">
        <div class="v3-temp__container">
          <div class="experience-grid">
            <article v-for="highlight in highlights" :key="highlight.title" class="experience-card">
              <span class="tag">{{ highlight.tag }}</span>
              <h3>{{ highlight.title }}</h3>
              <p>{{ highlight.detail }}</p>
              <ul v-if="highlight.bullets?.length">
                <li v-for="point in highlight.bullets" :key="point">{{ point }}</li>
              </ul>
            </article>
            <div class="experience-device" aria-hidden="true">
              <div class="device-screen">
                <div class="screen-header">
                  <span v-for="n in 3" :key="`bubble-${n}`"></span>
                </div>
                <div class="screen-content">
                  <div class="screen-card" v-for="n in 3" :key="`card-${n}`">
                    <div class="line line--strong"></div>
                    <div class="line"></div>
                    <div class="line line--faint"></div>
                  </div>
                  <div class="screen-footer">
                    <div class="pill">beta</div>
                    <span>Flow preview</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="v3-temp__timeline">
        <div class="v3-temp__container">
          <div class="v3-temp__section-heading">
            <p class="eyebrow">Where we're at</p>
            <h2>Transparent roadmap to launch.</h2>
          </div>
          <div class="timeline-grid">
            <article v-for="step in timeline" :key="step.title" class="timeline-card">
              <div class="step">{{ step.phase }}</div>
              <h3>{{ step.title }}</h3>
              <p>{{ step.detail }}</p>
            </article>
          </div>
        </div>
      </section>

      <section class="v3-temp__cta" id="v3-temp-notify">
        <div class="v3-temp__container">
          <div class="cta-card">
            <div>
              <p class="eyebrow">Join the insiders</p>
              <h2>Want the soft-launch invite?</h2>
              <p>Scroll up to the v3 hero and pop your details in. We send short progress notes, giveaways and early calendar invites only.</p>
            </div>
            <button type="button" class="cta-btn" @click="openNotifyModal">Add me to the list</button>
          </div>
        </div>
      </section>

      <section class="v3-temp__faq">
        <div class="v3-temp__container">
          <div class="v3-temp__section-heading">
            <p class="eyebrow">Quick answers</p>
            <h2>Most common questions while we wrap up development.</h2>
          </div>
          <div class="faq-grid">
            <article v-for="item in faqs" :key="item.question" class="faq-card">
              <h3>{{ item.question }}</h3>
              <p>{{ item.answer }}</p>
            </article>
          </div>
        </div>
      </section>
    </div>
  </SiteLayout>
</template>

<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue'

const stats = [
  { label: 'Verified practitioners at launch', value: '120+', detail: 'Interviewed, insured and safety checked across bodywork, somatics and energy work.' },
  { label: 'Beta journeys completed', value: '2,500+', detail: 'From nervous-system resets to workplace wellness pilots, all feeding our flows.' },
  { label: 'Regions covered on day one', value: '45', detail: 'Nationwide + online — London, Kent, Bristol, Manchester and more.' },
]

const promises = [
  { icon: '01', title: 'Safety-first directory', copy: 'Profiles highlight contraindications, practitioner credentials and post-care steps up front.' },
  { icon: '02', title: 'Nervous-system aware booking', copy: 'Check-in questions match you with what you need — gentle, energising or trauma-focused support.' },
  { icon: '03', title: 'Smart matching tools', copy: 'Pair human curation with smart filtering: payment type, accessibility, cultural background and more.' },
]

const features = [
  { title: 'Guided booking flows', detail: 'Concise questions adapt the calendar, so you only see relevant sessions.' },
  { title: 'Flexible payment', detail: 'Mix single sessions, series passes and employer contributions in one cart.' },
  { title: 'Curated collections', detail: 'Weekly drops: sleep labs, hormone support, gentle movement, grief circles.' },
  { title: 'Community reviews', detail: 'Structured, anonymised feedback that celebrates practitioners without the noise.' },
  { title: 'Corporate-ready', detail: 'Team dashboards for HR & culture leads piloting mental-health stipends.' },
  { title: 'Grounded guidance', detail: 'Bite-sized checklists, safety notes and integration prompts once you book.' },
]

const highlights = [
  {
    tag: 'Client flow',
    title: 'Choose by feeling, not guesswork.',
    detail: 'Start with how you feel today and we surface the right therapy type, practitioner and delivery format.',
    bullets: ['Tone + support level sliders', 'Contraindication alerts before checkout', 'Suggested after-care kits'],
  },
  {
    tag: 'Practitioner tools',
    title: "Profiles you're proud to share.",
    detail: 'Rich profiles include video intros, modalities, playlists and accessibility notes without needing a developer.',
    bullets: ['Drag-and-drop schedule builder', 'Integrated intake forms', 'Transparent fees + instant payouts'],
  },
]

const timeline = [
  { phase: 'Now', title: 'Final beta sprints', detail: 'Iterating on onboarding and checkout speed with our 200-person tester pool.' },
  { phase: 'March', title: 'Soft launch & giveaways', detail: 'Invite-only release to the waitlist: gift cards, founding member perks, corporate pilots.' },
  { phase: 'Spring', title: 'Full public launch', detail: 'Open booking across therapies, classes, events and curated experiences.' },
]

const faqs = [
  { question: 'Will my current WOW account carry over?', answer: "Yes. Existing members keep their logins — we'll prompt a quick password refresh for extra security." },
  { question: 'How often will you email me?', answer: 'Only when something meaningful ships: big milestones, early access drops, giveaways and beta tester invites.' },
  { question: "I'm a practitioner. Can I still express interest?", answer: "Absolutely — reply to the confirmation email and include your modality. We're curating a trauma-aware roster and will follow up privately." },
  { question: 'Do you offer corporate programmes?', answer: 'Yes. V3 introduces lightweight dashboards for HR teams plus curated onsite / online activations. Mention "corporate" when you join the list.' },
]

function openNotifyModal(){
  if (typeof window === 'undefined') return
  try { window.dispatchEvent(new CustomEvent('wow:v3-open-modal')) } catch {}
}
</script>

<style scoped>
.v3-temp{
  background: linear-gradient(180deg, #fdfaf6 0%, #f3f5ff 40%, #ffffff 100%);
}
.v3-temp__container{
  width: min(1120px, 100%);
  margin: 0 auto;
  padding: 0 1.5rem;
}
.v3-temp__intro{
  text-align: center;
  padding: 4rem 0 2rem;
}
.v3-temp__eyebrow{
  text-transform: uppercase;
  letter-spacing: 0.2em;
  font-size: 0.8rem;
  color: #8b5b34;
  margin-bottom: 1rem;
  font-weight: 600;
}
.v3-temp__intro h1{
  font-size: clamp(2rem, 4vw, 3.3rem);
  color: #2b1c12;
  margin-bottom: 1rem;
}
.v3-temp__lede{
  margin: 0 auto;
  max-width: 720px;
  color: #4c3b2d;
  font-size: 1.1rem;
}
.v3-temp__stat-grid{
  display: grid;
  gap: 1.5rem;
  margin-top: 2.5rem;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
}
.v3-temp__stat-card{
  background: #fff;
  border-radius: 24px;
  padding: 1.75rem;
  text-align: left;
  border: 1px solid #f3e6d8;
  box-shadow: 0 25px 60px rgba(19, 10, 4, 0.08);
}
.v3-temp__stat-card .value{
  font-size: 2.5rem;
  font-weight: 700;
  color: #3d2b15;
}
.v3-temp__stat-card .label{
  font-weight: 600;
  color: #7c5a3a;
  margin-bottom: 0.4rem;
}
.v3-temp__stat-card p{
  margin: 0;
  color: #4f433a;
}
.v3-temp__pillars,
.v3-temp__features,
.v3-temp__experience,
.v3-temp__timeline,
.v3-temp__cta,
.v3-temp__faq{
  padding: 4rem 0;
}
.v3-temp__section-heading{
  max-width: 760px;
  margin: 0 auto 2.5rem;
  text-align: center;
}
.v3-temp__section-heading .eyebrow{
  text-transform: uppercase;
  letter-spacing: 0.18em;
  font-size: 0.78rem;
  color: #7a7f9b;
  margin-bottom: 0.75rem;
  font-weight: 700;
}
.v3-temp__section-heading h2{
  font-size: clamp(1.9rem, 3.4vw, 2.8rem);
  color: #1d1f2e;
  margin: 0;
}
.v3-temp__pillar-grid{
  display: grid;
  gap: 1.5rem;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
}
.pillar-card{
  background: #0f172a;
  color: #e2e8f0;
  border-radius: 28px;
  padding: 2rem;
  min-height: 260px;
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
  box-shadow: 0 30px 70px rgba(15, 23, 42, 0.3);
}
.pillar-card .icon{
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: rgba(255,255,255,0.1);
  border: 1px solid rgba(255,255,255,0.3);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1rem;
}
.pillar-card h3{
  font-size: 1.25rem;
  margin: 0;
}
.pillar-card p{
  margin: 0;
  color: #cbd5f5;
}
.feature-grid{
  display: grid;
  gap: 1.25rem;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
}
.feature-card{
  padding: 1.75rem;
  background: #ffffff;
  border-radius: 20px;
  border: 1px solid #e5e9ff;
  box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
}
.feature-card h3{
  margin: 0 0 0.4rem;
  font-size: 1.1rem;
  color: #1b1f3a;
}
.feature-card p{
  margin: 0;
  color: #4c4f63;
}
.experience-grid{
  display: grid;
  gap: 1.5rem;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  align-items: stretch;
}
.experience-card{
  background: #fff;
  border-radius: 24px;
  padding: 2rem;
  border: 1px solid #f1e9ff;
  box-shadow: 0 25px 60px rgba(23, 15, 42, 0.08);
}
.experience-card .tag{
  display: inline-flex;
  align-items: center;
  padding: 0.2rem 0.75rem;
  border-radius: 999px;
  font-size: 0.75rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  background: #efe8ff;
  color: #5a35b3;
  font-weight: 600;
  margin-bottom: 0.9rem;
}
.experience-card h3{
  font-size: 1.4rem;
  margin: 0 0 0.6rem;
  color: #1f1334;
}
.experience-card p{
  color: #4b3f58;
  margin-bottom: 0.75rem;
}
.experience-card ul{
  padding-left: 1.2rem;
  margin: 0;
  color: #2f1f42;
  font-weight: 600;
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}
.experience-device{
  background: radial-gradient(circle at top, #f8f5ff, #dfe5ff);
  border-radius: 32px;
  padding: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100%;
}
.device-screen{
  width: 100%;
  max-width: 320px;
  border-radius: 32px;
  background: #0f172a;
  color: #fff;
  padding: 1.5rem;
  box-shadow: 0 30px 80px rgba(15, 23, 42, 0.5);
}
.screen-header{
  display: flex;
  gap: 0.4rem;
  margin-bottom: 1.25rem;
}
.screen-header span{
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: rgba(255,255,255,0.3);
}
.screen-content{
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}
.screen-card{
  background: rgba(255,255,255,0.08);
  border-radius: 18px;
  padding: 1rem;
  min-height: 90px;
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}
.line{
  height: 6px;
  border-radius: 999px;
  background: rgba(255,255,255,0.3);
}
.line--strong{ width: 70%; }
.line--faint{ width: 35%; opacity: 0.4; }
.screen-footer{
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.5rem;
  font-size: 0.85rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: rgba(255,255,255,0.7);
}
.screen-footer .pill{
  padding: 0.15rem 0.6rem;
  border-radius: 999px;
  background: rgba(255,255,255,0.12);
  font-weight: 600;
  letter-spacing: 0.09em;
}
.timeline-grid{
  display: grid;
  gap: 1rem;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
}
.timeline-card{
  background: #fff;
  border-radius: 20px;
  padding: 1.75rem;
  border: 1px solid #e6ecff;
  box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
}
.timeline-card .step{
  font-size: 0.82rem;
  text-transform: uppercase;
  letter-spacing: 0.2em;
  margin-bottom: 0.75rem;
  color: #6574cd;
  font-weight: 700;
}
.timeline-card h3{
  margin: 0 0 0.5rem;
  color: #1e1f38;
}
.timeline-card p{
  margin: 0;
  color: #4a4f63;
}
.cta-card{
  background: #0d111f;
  border-radius: 28px;
  padding: clamp(2rem, 4vw, 3rem);
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
  align-items: center;
  justify-content: space-between;
  color: #dfe3f9;
  box-shadow: 0 30px 80px rgba(2,6,23,0.5);
}
.cta-card h2{
  margin: 0 0 0.4rem;
  color: #fff;
  font-size: clamp(1.8rem, 3vw, 2.5rem);
}
.cta-card p{
  margin: 0;
  max-width: 520px;
}
.cta-btn{
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.9rem 2.4rem;
  border-radius: 999px;
  border: none;
  background: linear-gradient(120deg, #fcb78a, #f47f97, #9a5bff);
  color: #0f0f1a;
  font-weight: 700;
  text-decoration: none;
  box-shadow: 0 15px 40px rgba(244, 127, 151, 0.4);
  cursor: pointer;
}
.faq-grid{
  display: grid;
  gap: 1rem;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
}
.faq-card{
  background: #fff;
  border-radius: 18px;
  padding: 1.5rem;
  border: 1px solid #f0f2fb;
  box-shadow: 0 15px 40px rgba(13, 15, 35, 0.08);
}
.faq-card h3{
  margin: 0 0 0.5rem;
  font-size: 1rem;
  color: #1e213a;
}
.faq-card p{
  margin: 0;
  color: #44495d;
}
@media (max-width: 640px){
  .v3-temp__stat-card{
    text-align: center;
  }
  .cta-card{
    flex-direction: column;
    text-align: center;
  }
  .experience-device{
    min-height: 280px;
  }
}
</style>
