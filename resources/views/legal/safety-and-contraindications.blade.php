{{-- resources/views/legal/safety-and-contraindications.blade.php --}}
@extends('layouts.app')

@php
    $pageTitle = $title ?? 'Safety & Contraindications';
    $pageDescription = $metaDescription ?? 'Safety guidance and contraindications for wellness experiences on We Offer Wellness™.';
    $pageCanonical = $canonical ?? url('/safety-and-contraindications');

    $faqs = [
        [
            'q' => 'How do you vet your wellness providers?',
            'a' => 'Practitioners on We Offer Wellness™ are independent professionals. When they join, they confirm that they hold appropriate qualifications, experience and (where required) professional insurance for the services they offer.
We review profiles for clarity and alignment with our wellness guidelines and may remove providers who do not meet our standards or where we have concerns about safety or professionalism.',
        ],
        [
            'q' => 'Are your experiences safe?',
            'a' => 'Yes. Our providers follow industry best practices for health, safety and hygiene. If you have specific concerns, feel free to ask before booking.',
        ],
        [
            'q' => 'I’m pregnant – can I still book?',
            'a' => 'Many practitioners offer pregnancy-friendly options, but not all treatments are suitable during pregnancy or after birth. Please check the session description carefully and let your practitioner know that you’re pregnant. If you’re unsure, we recommend checking with your midwife or GP first.',
        ],
        [
            'q' => 'How do I raise a concern or complaint?',
            'a' => 'You can email us at hello@weofferwellness.co.uk with the details of what happened and the name of the practitioner. We review all safety-related reports and may follow up with you and the practitioner, and where appropriate we may suspend or remove accounts from the platform.',
        ],
    ];
@endphp

@section('title', $pageTitle)

{{-- If your layout supports a "head" section, this will populate meta safely --}}
@section('head')
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $pageCanonical }}">
@endsection

@section('content')
<main class="section" aria-labelledby="page-title">
    <div class="container-page py-10 md:py-14">
        {{-- Header --}}
        <div class="max-w-3xl">
            <div class="kicker">Important information</div>
            <h1 id="page-title" class="mt-2 text-3xl md:text-4xl font-semibold tracking-tight">
                {{ $pageTitle }}
            </h1>

            <div class="mt-5 space-y-4 text-ink-700 leading-relaxed">
                <p>
                    At We Offer Wellness™, your safety and enjoyment of the offering you purchase is paramount.
                </p>
                <p>
                    You should discuss any medical conditions with your chosen practitioner if you have any concerns as to suitability and be guided by their experience and expertise.
                </p>
            </div>
        </div>

        {{-- FAQ --}}
        <section class="mt-10 md:mt-12" aria-label="Safety FAQ">
            <div class="max-w-3xl">
                <h2 class="text-xl md:text-2xl font-semibold tracking-tight">Frequently asked questions</h2>

                <div class="mt-5 space-y-3 mb-4">
                    @foreach($faqs as $i => $item)
                        <details class="group rounded-2xl border border-ink-200/60 bg-white shadow-sm overflow-hidden">
                            <summary class="cursor-pointer list-none px-5 py-4 md:px-6 md:py-5 flex items-start gap-3">
                                <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-ink-200/70 bg-white">
                                    {{-- plus/minus (pure CSS toggle) --}}
                                    <span class="relative block h-3 w-3">
                                        <span class="absolute inset-x-0 top-1/2 -translate-y-1/2 h-[2px] w-full bg-ink-700/70"></span>
                                        <span class="absolute inset-y-0 left-1/2 -translate-x-1/2 w-[2px] h-full bg-ink-700/70 transition-opacity duration-200 group-open:opacity-0"></span>
                                    </span>
                                </span>

                                <span class="flex-1">
                                    <span class="block font-semibold text-ink-900">{{ $item['q'] }}</span>
                                    <span class="block mt-1 text-sm text-ink-600">
                                        Tap to {{ $i === 0 ? 'see how we review providers' : 'read more' }}
                                    </span>
                                </span>
                            </summary>

                            <div class="px-5 pb-5 md:px-6 md:pb-6 text-ink-700 leading-relaxed">
                                {!! nl2br(e($item['a'])) !!}

                                @if(str_contains($item['a'], 'hello@weofferwellness.co.uk'))
                                    <div class="mt-4">
                                        <a
                                            class="inline-flex items-center gap-2 rounded-full border border-ink-200/70 bg-white px-4 py-2 text-sm font-medium text-ink-900 hover:bg-ink-50 transition"
                                            href="mailto:hello@weofferwellness.co.uk"
                                        >
                                            Email hello@weofferwellness.co.uk
                                            <span aria-hidden="true">→</span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </details>
                    @endforeach
                </div>

                {{-- Gentle disclaimer (kept minimal, not scary) --}}
                <p class="mt-6 text-sm text-ink-600">
                    This information is general guidance and doesn’t replace medical advice. If you’re unsure, speak to a qualified healthcare professional.
                </p>
            </div>
        </section>
    </div>
</main>
@endsection
