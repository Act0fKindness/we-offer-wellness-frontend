@extends('layouts.app')

@section('content')

@include('home.sections.hero')

@include('home.sections.mindful_times_ribbon')

@include('home.sections.search_bar')


<section data-v-cad6ed7c="" data-v-f43bb09d="" class="section" aria-labelledby="schedule-title">
    <div data-v-cad6ed7c="" class="container-page">
        <div data-v-cad6ed7c="" class="mb-6 flex items-end justify-between">
            <div data-v-cad6ed7c="">
                <div data-v-cad6ed7c="" class="kicker">Classes</div>
                <h2 data-v-cad6ed7c="" id="schedule-title">Book Your Class</h2>
                <p data-v-cad6ed7c="" class="schedule-sub">3 simple steps to book a gentle gong bath, Yoga
                    Nidra and more…</p></div>
            <a href="/classes" class="btn-wow btn-wow--outline btn-sm btn-arrow" data-v-cad6ed7c=""
               data-loader-init="1"><span class="btn-label" data-v-cad6ed7c="">View all classes</span><span
                class="btn-icon-wrap" aria-hidden="true" data-v-cad6ed7c=""><svg class="btn-icon-hover"
                                                                                 xmlns="http://www.w3.org/2000/svg"
                                                                                 viewBox="0 0 24 24"
                                                                                 data-v-cad6ed7c=""><path
                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 12H5m14 0-4 4m4-4-4-4" data-v-cad6ed7c=""></path></svg><svg class="btn-icon-default"
                                                                                   xmlns="http://www.w3.org/2000/svg"
                                                                                   viewBox="0 0 24 24"
                                                                                   data-v-cad6ed7c=""><path
                fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12l-4 4m4-4-4-4" data-v-cad6ed7c=""></path></svg></span><span class="btn-spinner"
                                                                                     aria-hidden="true"
                                                                                     data-v-cad6ed7c=""><span
                class="spin" data-v-cad6ed7c=""></span></span></a></div>
        <div data-v-cad6ed7c="" class="card p-0 overflow-hidden">
            <div data-v-cad6ed7c="">
                <div data-v-cad6ed7c="" class="p-6 text-ink-700">
                    <div class="flex items-center justify-between gap-4 flex-wrap" data-v-cad6ed7c="">
                        <div data-v-cad6ed7c=""><h3 class="m-0 h5" data-v-cad6ed7c="">Classes are being
                            scheduled</h3>
                            <div class="text-ink-600" data-v-cad6ed7c="">New sessions are added
                                weekly—browse what’s live now.
                            </div>
                        </div>
                        <a href="/classes" class="btn-wow btn-wow--outline btn-sm btn-arrow"
                           data-v-cad6ed7c="" data-loader-init="1"><span class="btn-label"
                                                                         data-v-cad6ed7c="">Browse classes</span><span
                            class="btn-icon-wrap" aria-hidden="true" data-v-cad6ed7c=""><svg
                            class="btn-icon-hover" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                            data-v-cad6ed7c=""><path stroke="currentColor" stroke-linecap="round"
                                                     stroke-linejoin="round" stroke-width="2"
                                                     d="M19 12H5m14 0-4 4m4-4-4-4"
                                                     data-v-cad6ed7c=""></path></svg><svg
                            class="btn-icon-default" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                            data-v-cad6ed7c=""><path fill="none" stroke="#fff" stroke-linecap="round"
                                                     stroke-linejoin="round" stroke-width="2"
                                                     d="M15 12l-4 4m4-4-4-4"
                                                     data-v-cad6ed7c=""></path></svg></span><span
                            class="btn-spinner" aria-hidden="true" data-v-cad6ed7c=""><span class="spin"
                                                                                            data-v-cad6ed7c=""></span></span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section><!---->
<section data-v-f43bb09d="" class="section">
    <div class="container-page">
        <div class="mb-6 flex items-end justify-between">
            <div>
                <div class="kicker">Thoughtful ways to nourish someone you love</div>
                <h2>Gifts that glow (under £50)</h2></div>
            <div class="flex items-center gap-2">
                <button class="hidden sm:inline-flex carousel-arrow" aria-label="Previous">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2">
                        <path d="M15 18l-6-6 6-6"></path>
                    </svg>
                </button>
                <button class="hidden sm:inline-flex carousel-arrow" aria-label="Next">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2">
                        <path d="M9 6l6 6-6 6"></path>
                    </svg>
                </button>
                <a href="/search?tag=Gift&amp;price_max=50"
                   class="btn-wow btn-wow--outline btn-sm btn-arrow" data-loader-init="1"><span
                    class="btn-label">Find a thoughtful gift</span><span class="btn-icon-wrap"
                                                                         aria-hidden="true"><svg
                    class="btn-icon-hover" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path
                    stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 12H5m14 0-4 4m4-4-4-4"></path></svg><svg class="btn-icon-default"
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    viewBox="0 0 24 24"><path fill="none"
                                                                                              stroke="#fff"
                                                                                              stroke-linecap="round"
                                                                                              stroke-linejoin="round"
                                                                                              stroke-width="2"
                                                                                              d="M15 12l-4 4m4-4-4-4"></path></svg></span><span
                    class="btn-spinner" aria-hidden="true"><span class="spin"></span></span></a></div>
        </div>
        <div
            class="flex gap-6 overflow-x-auto overflow-y-visible no-scrollbar snap-x snap-mandatory pt-2 pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 bg-transparent"></div>
    </div>
</section>
<section data-v-f43bb09d="" class="section" aria-labelledby="comfort-title">
    <div data-v-f43bb09d="" class="container-page">
        <div data-v-f43bb09d="" class="mb-6">
            <div data-v-f43bb09d="" class="kicker">No travel needed</div>
            <h2 data-v-f43bb09d="" id="comfort-title">From the comfort of your own home</h2>
            <p data-v-f43bb09d="" class="text-ink-600 mt-2 max-w-2xl"> When your mind won’t slow down,
                soften into rituals that meet you where you are — gentle sessions that bring calm, clarity
                and care right to your space. </p></div>
        <div data-v-f43bb09d="" class="flex items-center justify-between gap-4 mb-4 flex-wrap">
            <div data-v-f43bb09d="" class="flex items-center gap-4 flex-wrap">
                <div data-v-f43bb09d="" class="flex items-center gap-2"><span data-v-f43bb09d=""
                                                                              class="font-semibold text-ink-800">Under</span>
                    <div data-v-f43bb09d="" class="seg-group" role="tablist" aria-label="Under price">
                        <button data-v-f43bb09d="" class="seg active" role="tab" aria-selected="true">£50
                        </button>
                        <button data-v-f43bb09d="" class="seg" role="tab" aria-selected="false">£100
                        </button>
                        <button data-v-f43bb09d="" class="seg" role="tab" aria-selected="false">£500
                        </button>
                    </div>
                </div>
                <div data-v-f43bb09d="" class="flex items-center gap-2"><span data-v-f43bb09d=""
                                                                              class="font-semibold text-ink-800">For</span>
                    <div data-v-f43bb09d="" class="seg-group" role="tablist" aria-label="For">
                        <button data-v-f43bb09d="" class="seg active" role="tab" aria-selected="true">Solo
                        </button>
                        <button data-v-f43bb09d="" class="seg" role="tab" aria-selected="false">Couple
                        </button>
                    </div>
                </div>
            </div>
            <div data-v-f43bb09d="" class="hidden sm:flex items-center gap-2 ml-auto">
                <button data-v-f43bb09d="" class="carousel-arrow" aria-label="Previous">
                    <svg data-v-f43bb09d="" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2">
                        <path data-v-f43bb09d="" d="M15 18l-6-6 6-6"></path>
                    </svg>
                </button>
                <button data-v-f43bb09d="" class="carousel-arrow" aria-label="Next">
                    <svg data-v-f43bb09d="" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2">
                        <path data-v-f43bb09d="" d="M9 6l6 6-6 6"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div data-v-f43bb09d="">
            <div data-v-f43bb09d=""
                 class="flex gap-6 overflow-x-auto overflow-y-visible no-scrollbar snap-x snap-mandatory pt-2 pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 bg-transparent"></div>
            <div data-v-f43bb09d="" class="mt-4 text-right"><a data-v-f43bb09d=""
                                                               href="/search?price_max=50&amp;format=online"
                                                               class="btn-wow btn-wow--outline btn-sm btn-arrow"
                                                               data-loader-init="1"><span data-v-f43bb09d=""
                                                                                          class="btn-label">See all under £50 (solo)</span><span
                data-v-f43bb09d="" class="btn-icon-wrap" aria-hidden="true"><svg data-v-f43bb09d=""
                                                                                 class="btn-icon-hover"
                                                                                 xmlns="http://www.w3.org/2000/svg"
                                                                                 viewBox="0 0 24 24"><path
                data-v-f43bb09d="" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"></path></svg><svg data-v-f43bb09d=""
                                                                                 class="btn-icon-default"
                                                                                 xmlns="http://www.w3.org/2000/svg"
                                                                                 viewBox="0 0 24 24"><path
                data-v-f43bb09d="" fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round"
                stroke-width="2" d="M15 12l-4 4m4-4-4-4"></path></svg></span><span data-v-f43bb09d=""
                                                                                   class="btn-spinner"
                                                                                   aria-hidden="true"><span
                data-v-f43bb09d="" class="spin"></span></span></a></div>
        </div>
    </div>
</section>
<section data-v-f43bb09d="" class="section">
    <div data-v-f43bb09d="" class="container-page">
        <div data-v-f43bb09d="" class="card p-6 md:p-8">
            <div data-v-f43bb09d="" class="grid md:grid-cols-3 gap-6 items-center">
                <div data-v-f43bb09d="" class="space-y-2">
                    <div data-v-f43bb09d="" class="kicker">Feel safe to try</div>
                    <h3 data-v-f43bb09d="">You’re in safe hands</h3>
                    <p data-v-f43bb09d="" class="text-ink-600">Real outcomes, real people. Verified reviews
                        and clear pricing — so you can relax into booking and focus on how you want to
                        feel.</p></div>
                <div data-v-f43bb09d="" class="stat-row">
                    <div data-v-f43bb09d="" class="stat-pill">
                        <svg data-v-f43bb09d="" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                             class="icon star" aria-hidden="true" fill="currentColor">
                            <path data-v-f43bb09d=""
                                  d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path>
                        </svg>
                        <div data-v-f43bb09d="">
                            <div data-v-f43bb09d="" class="title">—</div>
                            <div data-v-f43bb09d="" class="sub">0 verified reviews</div>
                        </div>
                    </div>
                    <div data-v-f43bb09d="" class="stat-pill"><span data-v-f43bb09d="" class="dot"
                                                                    aria-hidden="true"></span>
                        <div data-v-f43bb09d="">
                            <div data-v-f43bb09d="" class="title">97% would book again</div>
                            <div data-v-f43bb09d="" class="sub">People felt better after their session</div>
                        </div>
                    </div>
                </div>
                <div data-v-f43bb09d="" class="text-right"><a data-v-f43bb09d="" href="/reviews"
                                                              class="btn-wow btn-wow--outline btn-sm"
                                                              data-loader-init="1">See reviews<span
                    class="btn-spinner" aria-hidden="true"><span class="spin"></span></span></a></div>
            </div>
        </div>
    </div>
</section>
<section data-v-f43bb09d="" class="section" aria-labelledby="values-title">
    <div data-v-f43bb09d="" class="container-page">
        <div data-v-f43bb09d="" class="grid md:grid-cols-2 gap-8">
            <div data-v-f43bb09d="">
                <div data-v-f43bb09d="" class="kicker">Our approach</div>
                <h2 data-v-f43bb09d="" id="values-title">Holistic therapies, grounded in care</h2>
                <p data-v-f43bb09d="" class="text-ink-600 mt-3">We champion modalities that meet you where
                    you are, taught and held by practitioners who prioritise nervous-system safety.</p>
            </div>
            <dl data-v-f43bb09d="" class="space-y-5">
                <div data-v-f43bb09d="">
                    <dt data-v-f43bb09d="" class="font-semibold text-ink-900">Therapies first</dt>
                    <dd data-v-f43bb09d="" class="text-ink-600 mt-1">Evidence-informed modalities and
                        trauma-aware practitioners are prioritised before everything else we do.
                    </dd>
                </div>
                <div data-v-f43bb09d="">
                    <dt data-v-f43bb09d="" class="font-semibold text-ink-900">Human guidance</dt>
                    <dd data-v-f43bb09d="" class="text-ink-600 mt-1">Every offering is reviewed by our
                        practitioner team so you know who is holding space for you.
                    </dd>
                </div>
                <div data-v-f43bb09d="">
                    <dt data-v-f43bb09d="" class="font-semibold text-ink-900">Whole-self care</dt>
                    <dd data-v-f43bb09d="" class="text-ink-600 mt-1">We look at sleep, stress, digestion,
                        hormones and energy together—never in isolation.
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</section><!----><!---->
<section data-v-f43bb09d="" id="reset-guide" class="section" style="display:none">
    <div class="container-page">
        <div class="card p-6 md:p-10 flex flex-col md:flex-row items-center gap-8">
            <div class="flex-1 w-full">
                <div class="kicker mb-3">Free guide</div>
                <h3>Free Guide – 7-Day Reset Starter Kit</h3>
                <p class="text-ink-600 mt-2 max-w-xl">Simple daily rituals to reduce stress, sleep deeper,
                    and feel more energised. Join our list and get the guide instantly.</p>
                <form class="mt-5 flex flex-col sm:flex-row gap-3 max-w-lg"><input type="email"
                                                                                   placeholder="Email address"
                                                                                   class="w-full input-search-radius bg-white border border-ink-200 px-4 py-3 text-sm placeholder:text-ink-400 focus:outline-none focus:ring-2 focus:ring-brand-400"
                                                                                   aria-label="Email address"><a
                    class="btn btn-secondary btn-search-radius"
                    href="https://times.weofferwellness.co.uk/7-day-reset-starter-kit">Email me the
                    guide</a></form>
                <div class="mt-3 text-xs text-ink-400">We’ll also send our 60‑sec quiz to personalise your
                    plan.
                </div>
            </div>
            <div class="flex-1 w-full">
                <div class="relative">
                    <div class="card p-6">
                        <div class="kicker mb-3">Preview</div>
                        <ul class="space-y-3 text-sm text-ink-700">
                            <li>• Morning nervous‑system reset</li>
                            <li>• 3× micro‑mobility breaks</li>
                            <li>• Bedtime wind‑down ritual</li>
                        </ul>
                        <div class="mt-4"><a href="#quiz" class="link-wow--button" data-draw-line=""
                                             data-draw-init="1"><span>Take the 60‑sec quiz</span><span
                            data-draw-line-box=""></span></a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section><!---->
<section data-v-f43bb09d="" id="partners" class="section">
    <div data-v-f43bb09d="" class="container-page">
        <div data-v-f43bb09d="" class="grid md:grid-cols-2 gap-6 items-center">
            <div data-v-f43bb09d="" class="card p-6 md:p-8">
                <div data-v-f43bb09d="" class="kicker mb-2">Affiliate / referral</div>
                <h3 data-v-f43bb09d="">Partner spotlight</h3>
                <p data-v-f43bb09d="" class="text-ink-600 mt-2">Arriving from a partner? You’re in the right
                    place. Redeem your voucher and book a session today.</p>
                <div data-v-f43bb09d="" class="mt-5 flex gap-3"><a data-v-f43bb09d="" href="#"
                                                                   class="btn-wow btn-sm is-square"
                                                                   data-loader-init="1"><span
                    class="btn-label">Redeem voucher</span><!----><span class="btn-spinner"
                                                                        aria-hidden="true"><span
                    class="spin"></span></span></a><a data-v-f43bb09d="" href="#whats-on"
                                                      class="btn-wow btn-wow--ghost btn-sm is-square" style="display:none"
                                                      data-loader-init="1"><span
                    class="btn-label">Book now</span><!----><span class="btn-spinner"
                                                                  aria-hidden="true"><span
                    class="spin"></span></span></a></div>
            </div>
            <div data-v-f43bb09d="" class="card p-6 md:p-8">
                <div data-v-f43bb09d="" class="kicker mb-2">Corporate gateway</div>
                <h3 data-v-f43bb09d="">Wellness for teams</h3>
                <p data-v-f43bb09d="" class="text-ink-600 mt-2">HR and team leads — build a science‑backed
                    wellness program with measurable outcomes.</p>
                <div data-v-f43bb09d="" class="mt-5 flex gap-3"><a data-v-f43bb09d="" href="#corporate"
                                                                   class="btn-wow btn-wow--outline btn-sm is-square"
                                                                   data-loader-init="1"><span
                    class="btn-label">Explore corporate</span><!----><span class="btn-spinner"
                                                                           aria-hidden="true"><span
                    class="spin"></span></span></a><a data-v-f43bb09d="" href="#"
                                                      class="btn-wow btn-wow--ghost btn-sm is-square"
                                                      data-loader-init="1"><span class="btn-label">Talk to us</span>
                    <!----><span class="btn-spinner" aria-hidden="true"><span
                        class="spin"></span></span></a></div>
            </div>
        </div>
    </div>
</section>
<section data-v-f43bb09d="" id="practitioner-chats" class="section"
         aria-labelledby="practitioner-chats-title">
    <div data-v-f43bb09d="" class="container-page">
        <div data-v-f43bb09d="" class="card p-6 md:p-10 flex flex-col md:flex-row items-center gap-6">
            <div data-v-f43bb09d="" class="flex-1">
                <div data-v-f43bb09d="" class="kicker">New series</div>
                <h2 data-v-f43bb09d="" id="practitioner-chats-title">Practitioner Chats</h2>
                <p data-v-f43bb09d="" class="text-ink-600 mt-2">Monthly conversations with WOW practitioners
                    on how they hold space, approach safety, and design therapies that work.</p></div>
            <div data-v-f43bb09d="" class="flex gap-3"><a data-v-f43bb09d=""
                                                          href="https://times.weofferwellness.co.uk#practitioner-chats"
                                                          class="btn-wow btn-wow--cta btn-arrow"
                                                          data-loader-init="1"><span data-v-f43bb09d=""
                                                                                     class="btn-label">Explore chats</span><span
                class="btn-spinner" aria-hidden="true"><span class="spin"></span></span></a><a
                data-v-f43bb09d="" href="https://times.weofferwellness.co.uk" class="btn-wow btn-wow--ghost"
                data-loader-init="1">See notes<span class="btn-spinner" aria-hidden="true"><span
                class="spin"></span></span></a></div>
        </div>
    </div>
</section><!---->
<section data-v-f43bb09d="" id="mindful-times" class="section">
    <div data-v-f43bb09d="" class="container-page">
        <div data-v-f43bb09d="" class="mb-8 flex items-end justify-between">
            <div data-v-f43bb09d="">
                <div data-v-f43bb09d="" class="kicker">Mindful Times</div>
                <h2 data-v-f43bb09d="">Guides, practitioner interviews and tools to help you feel
                    better</h2></div>
            <a data-v-f43bb09d="" class="btn-wow btn-wow--outline btn-sm btn-arrow"
               href="https://times.weofferwellness.co.uk" data-loader-init="1"><span data-v-f43bb09d=""
                                                                                     class="btn-label">Visit Mindful Times</span><span
                data-v-f43bb09d="" class="btn-icon-wrap" aria-hidden="true"><svg data-v-f43bb09d=""
                                                                                 class="btn-icon-hover"
                                                                                 xmlns="http://www.w3.org/2000/svg"
                                                                                 viewBox="0 0 24 24"><path
                data-v-f43bb09d="" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"></path></svg><svg data-v-f43bb09d=""
                                                                                 class="btn-icon-default"
                                                                                 xmlns="http://www.w3.org/2000/svg"
                                                                                 viewBox="0 0 24 24"><path
                data-v-f43bb09d="" fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round"
                stroke-width="2" d="M15 12l-4 4m4-4-4-4"></path></svg></span><span data-v-f43bb09d=""
                                                                                   class="btn-spinner"
                                                                                   aria-hidden="true"><span
                data-v-f43bb09d="" class="spin"></span></span></a></div><!----></div>
</section>
<section data-v-f43bb09d="" class="section">
    <div data-v-f43bb09d="" class="container-page">
        <div data-v-f43bb09d="" class="mb-8">
            <div data-v-f43bb09d="" class="kicker">Discover</div>
            <h2 data-v-f43bb09d="">Shop by category</h2></div>
        <div data-v-f43bb09d="" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4"><a data-v-f43bb09d=""
                                                                                    class="relative overflow-hidden rounded-2xl border border-ink-200 bg-white shadow-card"
                                                                                    href="/search?type=therapy&amp;what=breathwork"><img
            data-v-f43bb09d=""
            src="https://images.unsplash.com/photo-1520880867055-1e30d1cb001c?q=80&amp;w=1200&amp;auto=format&amp;fit=crop"
            class="h-48 w-full object-cover" alt="Breathwork">
            <div data-v-f43bb09d=""
                 class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
            <div data-v-f43bb09d="" class="absolute bottom-3 left-3 text-white text-lg font-semibold">
                Breathwork
            </div>
        </a><a data-v-f43bb09d=""
               class="relative overflow-hidden rounded-2xl border border-ink-200 bg-white shadow-card"
               href="/search?type=therapy&amp;what=sound%20bath"><img data-v-f43bb09d=""
                                                                      src="https://images.unsplash.com/photo-1506126613408-eca07ce68773?q=80&amp;w=1200&amp;auto=format&amp;fit=crop"
                                                                      class="h-48 w-full object-cover"
                                                                      alt="Sound Healing">
            <div data-v-f43bb09d=""
                 class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
            <div data-v-f43bb09d="" class="absolute bottom-3 left-3 text-white text-lg font-semibold">Sound
                Healing
            </div>
        </a><a data-v-f43bb09d=""
               class="relative overflow-hidden rounded-2xl border border-ink-200 bg-white shadow-card"
               href="/therapies/massage-therapy"><img data-v-f43bb09d=""
                                                      src="https://images.unsplash.com/photo-1612152918775-49ed1e1c1d1f?q=80&amp;w=1200&amp;auto=format&amp;fit=crop"
                                                      class="h-48 w-full object-cover"
                                                      alt="Massage Therapy">
            <div data-v-f43bb09d=""
                 class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
            <div data-v-f43bb09d="" class="absolute bottom-3 left-3 text-white text-lg font-semibold">
                Massage Therapy
            </div>
        </a><a data-v-f43bb09d=""
               class="relative overflow-hidden rounded-2xl border border-ink-200 bg-white shadow-card"
               href="/classes/yoga"><img data-v-f43bb09d=""
                                         src="https://images.unsplash.com/photo-1518611012118-696072aa579a?q=80&amp;w=1200&amp;auto=format&amp;fit=crop"
                                         class="h-48 w-full object-cover" alt="Yoga &amp; Movement">
            <div data-v-f43bb09d=""
                 class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
            <div data-v-f43bb09d="" class="absolute bottom-3 left-3 text-white text-lg font-semibold">Yoga
                &amp; Movement
            </div>
        </a><a data-v-f43bb09d=""
               class="relative overflow-hidden rounded-2xl border border-ink-200 bg-white shadow-card"
               href="/therapies/reiki"><img data-v-f43bb09d=""
                                            src="https://images.unsplash.com/photo-1524492412937-b28074a5d7da?q=80&amp;w=1200&amp;auto=format&amp;fit=crop"
                                            class="h-48 w-full object-cover" alt="Reiki &amp; Energy">
            <div data-v-f43bb09d=""
                 class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
            <div data-v-f43bb09d="" class="absolute bottom-3 left-3 text-white text-lg font-semibold">Reiki
                &amp; Energy
            </div>
        </a><a data-v-f43bb09d=""
               class="relative overflow-hidden rounded-2xl border border-ink-200 bg-white shadow-card"
               href="/search?type=therapy&amp;what=coaching"><img data-v-f43bb09d=""
                                                                  src="https://images.unsplash.com/photo-1497352305433-9b09be07364b?q=80&amp;w=1200&amp;auto=format&amp;fit=crop"
                                                                  class="h-48 w-full object-cover"
                                                                  alt="Coaching &amp; Mindset">
            <div data-v-f43bb09d=""
                 class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
            <div data-v-f43bb09d="" class="absolute bottom-3 left-3 text-white text-lg font-semibold">
                Coaching &amp; Mindset
            </div>
        </a></div>
    </div>
</section>
<section data-v-f43bb09d="" class="section">
    <div data-v-f43bb09d="" class="container-page">
        <div data-v-f43bb09d="" class="card p-6 md:p-10 flex flex-col md:flex-row items-center gap-8">
            <div data-v-f43bb09d="" class="flex-1">
                <div data-v-f43bb09d="" class="kicker mb-3">Gifting made easy</div>
                <h3 data-v-f43bb09d="">Gift cards for any occasion</h3>
                <p data-v-f43bb09d="" class="text-ink-600 mt-2 max-w-xl">Choose the amount, add a message,
                    and we’ll email an instant e-gift card to you or your recipient to use on therapies,
                    classes, events and workshops.</p>
                <div data-v-f43bb09d="" class="mt-5 flex gap-3"><a data-v-f43bb09d="" href="/gift-cards"
                                                                   class="btn-wow is-square btn-md btn-wow--cta"
                                                                   data-loader-init="1">Send a gift
                    card<span class="btn-spinner" aria-hidden="true"><span class="spin"></span></span></a><a
                    data-v-f43bb09d="" href="/help/gift-cards"
                    class="btn-wow is-square btn-md btn-wow--outline" data-loader-init="1">How gifting works<span
                    class="btn-spinner" aria-hidden="true"><span class="spin"></span></span></a></div>
            </div>
            <div data-v-f43bb09d="" class="flex-1 w-full">
                <div data-v-f43bb09d="" class="rounded-2xl overflow-hidden border border-ink-200"><img
                    data-v-f43bb09d=""
                    src="https://images.unsplash.com/photo-1512428559087-560fa5ceab42?q=80&amp;w=1200&amp;auto=format&amp;fit=crop"
                    alt="Gift Card" class="w-full h-64 object-cover"></div>
            </div>
        </div>
    </div>
</section>

@endsection
