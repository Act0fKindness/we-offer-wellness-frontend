@extends('layouts.app')

@section('content')

@include('home.sections.hero')

@include('home.sections.mindful_times_ribbon')

@include('home.sections.search_bar')

@include('home.sections.schedule')

@include('home.sections.gifts')

@include('home.sections.no-travel-needed')

@include('home.sections.trust-feel-safe')

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



@include('home.sections.practitioner_chats_converstions')

@include('home.sections.mindfultimes_guides_interviews')

@include('home.sections.discover_category')

@include('home.sections.gift_cards_occasion')

@endsection
