@extends('layouts.app')

@push('head')
  <title>{{ $seo['title'] ?? 'We Offer Wellness™' }}</title>
  @if(!empty($seo['description']))<meta name="description" content="{{ $seo['description'] }}">@endif
  @if(!empty($seo['robots']))<meta name="robots" content="{{ $seo['robots'] }}">@endif
  @if(!empty($seo['canonical']))<link rel="canonical" href="{{ $seo['canonical'] }}">@endif
@endpush

@section('content')
@php
  $items = $products ?? collect();
  $landing = $landing ?? [];
@endphp

<section class="section">
  <div class="container-page">
    <div class="wow-hero-card">
      <div class="grid md:grid-cols-2 gap-6 items-start">
        <div>
          <div class="kicker">{{ $landing['kicker'] ?? 'Explore' }}</div>
          <h1 class="mt-2">{{ $landing['title'] ?? 'Wellness' }}</h1>
          <p class="text-ink-600 mt-3" style="max-width:70ch;">{{ $landing['intro'] ?? ($seo['description'] ?? '') }}</p>

          @if(!empty($landing['points']))
            <ul class="mt-4 space-y-2 text-ink-700">
              @foreach($landing['points'] as $point)
                <li>• {{ $point }}</li>
              @endforeach
            </ul>
          @endif

          <div class="mt-5 flex flex-wrap gap-3">
            @if(!empty($landing['primary_cta']))
              <a href="{{ $landing['primary_cta']['href'] }}" class="btn-wow btn-wow--cta btn-arrow">
                <span class="btn-label">{{ $landing['primary_cta']['label'] }}</span>
                <span class="btn-icon-wrap" aria-hidden="true">
                  <svg class="btn-icon-hover" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"/></svg>
                  <svg class="btn-icon-default" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12l-4 4m4-4-4-4"/></svg>
                </span>
              </a>
            @endif
            @if(!empty($landing['secondary_cta']))
              <a href="{{ $landing['secondary_cta']['href'] }}" class="btn-wow btn-wow--outline btn-arrow">
                <span class="btn-label">{{ $landing['secondary_cta']['label'] }}</span>
                <span class="btn-icon-wrap" aria-hidden="true">
                  <svg class="btn-icon-hover" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"/></svg>
                  <svg class="btn-icon-default" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12l-4 4m4-4-4-4"/></svg>
                </span>
              </a>
            @endif
          </div>
        </div>

        <div class="wow-hero-panel">
          <div class="kicker mb-2">Search-friendly</div>
          <h3 class="m-0">{{ $landing['title'] ?? 'Wellness' }} now easier to find</h3>
          <p class="text-ink-600 mt-2">
            This page is the canonical SEO landing page for {{ strtolower((string)($landing['title'] ?? 'wellness')) }} on We Offer Wellness.
          </p>
          @if(!empty($categories) && count($categories))
            <div class="mt-4">
              <h4 class="text-base font-semibold mb-2">Popular categories</h4>
              <div class="flex flex-wrap gap-2">
                @foreach($categories as $category)
                  <a class="chip" href="{{ url('/search?type=' . urlencode($type) . '&category=' . urlencode($category['slug'])) }}">
                    {{ $category['name'] }}
                  </a>
                @endforeach
              </div>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container-page">
    <div class="mb-4">
      <div class="kicker">Featured results</div>
      <h2 class="section-title">{{ $landing['title'] ?? 'Listings' }}</h2>
    </div>

    <div id="landing-products" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      @forelse($items as $product)
        @include('partials.product_card_sm', ['product' => $product])
      @empty
        <div class="card p-4" style="border-radius:18px;">
          <div class="text-muted">No listings available yet. Try the search page for more results.</div>
        </div>
      @endforelse
    </div>
  </div>
</section>
@endsection
