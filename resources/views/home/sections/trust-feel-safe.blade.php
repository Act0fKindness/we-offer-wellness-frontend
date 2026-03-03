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
                            <div data-v-f43bb09d="" class="title">{{ isset($avg_rating) && $avg_rating ? number_format($avg_rating, 1) . '/5' : '—' }}</div>
                            <div data-v-f43bb09d="" class="sub">{{ number_format((int)($review_count ?? 0)) }} verified reviews</div>
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
                {{-- Review CTA removed by request --}}
            </div>
        </div>
    </div>
</section>
