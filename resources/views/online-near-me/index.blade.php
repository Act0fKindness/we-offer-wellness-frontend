{{-- resources/views/online-near-me/index.blade.php --}}
@extends('layouts.app')

@push('head')
  <title>{{ $seo['title'] ?? 'Online & Near Me | We Offer Wellness™' }}</title>
  @if(!empty($seo['description']))<meta name="description" content="{{ $seo['description'] }}">@endif
  @if(!empty($seo['robots']))<meta name="robots" content="{{ $seo['robots'] }}">@endif
  @if(!empty($seo['canonical']))<link rel="canonical" href="{{ $seo['canonical'] }}">@endif
@endpush

@section('content')
<section class="section">
  <div class="container-page">
    <div class="mb-4">
      <div class="kicker">Browse</div>
      <h1>Online &amp; Near Me</h1>
      <p class="text-ink-600 mt-2" style="max-width:70ch;">
        Pick your path: join from anywhere, or find something close by.
      </p>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
      {{-- Online --}}
      <a href="{{ url('/online') }}" class="wow-card md is-fluid" style="text-decoration:none;">
        <div class="wow-body">
          <div class="wow-type text-muted">Option</div>
          <div class="wow-title">Online</div>
          <div class="text-ink-600 mt-2">
            Therapies you can join from anywhere — calm, convenient, no travel required.
          </div>
        </div>
        <div class="wow-bottom">
          <div class="actions"><span class="wow-btn-like">Browse online</span></div>
        </div>
      </a>

      {{-- Near Me --}}
      <a href="{{ url('/near-me') }}" class="wow-card md is-fluid" style="text-decoration:none;">
        <div class="wow-body">
          <div class="wow-type text-muted">Option</div>
          <div class="wow-title">Near Me</div>
          <div class="text-ink-600 mt-2">
            Enter your postcode and see what’s available near you — fast.
          </div>
        </div>
        <div class="wow-bottom">
          <div class="actions"><span class="wow-btn-like">Find near me</span></div>
        </div>
      </a>
    </div>
  </div>
</section>
@endsection
