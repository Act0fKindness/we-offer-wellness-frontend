{{-- resources/views/home/partials/hero-slider-3.blade.php --}}

<section data-v-f43bb09d="" class="whero">
    <div data-v-f43bb09d="" class="whero-radial" aria-hidden="true"></div>
    <div data-v-f43bb09d="" class="container whero-pad">
        <div data-v-f43bb09d="" class="row align-items-center g-5">
            <div data-v-f43bb09d="" class="col-12 col-lg-7"><span data-v-f43bb09d="" class="whero-eyebrow">Events &amp; workshops</span>
                <h1 data-v-f43bb09d="" class="whero-title">Make your weekend feel like recovery, not chores</h1>
                <p data-v-f43bb09d="" class="whero-sub mt-3">Workshops, classes and events designed to reset your nervous system — without the fluff. <span
                        data-v-f43bb09d="" class="whero-subline">Find sessions held by trusted practitioners, with dates and availability that actually suit real life.</span>
                </p>
                <form data-v-f43bb09d="" class="whero-cta mt-4" action="#" method="post"
                      onsubmit="return false;"><input data-v-f43bb09d="" class="whero-cta-input"
                                                      type="email" placeholder="Email address"
                                                      aria-label="Email address">
                    <button data-v-f43bb09d="" type="submit" class="btn-wow btn-md btn-arrow"
                            data-loader-init="1"><span class="btn-label">Join for updates</span><span
                        class="btn-icon-wrap" aria-hidden="true"><svg class="btn-icon-hover"
                                                                      xmlns="http://www.w3.org/2000/svg"
                                                                      viewBox="0 0 24 24"><path
                        stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"></path></svg><svg
                        class="btn-icon-default" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path
                        fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="2" d="M15 12l-4 4m4-4-4-4"></path></svg></span><span
                        class="btn-spinner" aria-hidden="true"><span class="spin"></span></span></button>
                </form>
            </div>
            <div data-v-f43bb09d="" class="col-12 col-lg-5">
                {{-- Keep the same right panel so existing styling/animation stays perfect --}}
                @include('home.partials.hero-slider-1')
            </div>
        </div>
    </div>
</section>
