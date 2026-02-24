{{-- resources/views/therapies/index.blade.php --}}
@extends('layouts.app')

@push('head')
  <title>{{ $seo['title'] ?? 'Therapies | We Offer Wellness™' }}</title>
  @if(!empty($seo['description']))<meta name="description" content="{{ $seo['description'] }}">@endif
  @if(!empty($seo['robots']))<meta name="robots" content="{{ $seo['robots'] }}">@endif
@endpush

@section('content')
<section class="section">
  <div class="container-page">
    <div class="mb-4">
      <div class="kicker">Browse</div>
      <h1>Therapies</h1>
      <p class="text-ink-600 mt-2" style="max-width:70ch;">
        Explore modalities and approaches — from sound healing and breathwork to Reiki and massage.
      </p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
      @foreach(($therapies ?? []) as $t)
        <a href="{{ route('therapies.show', ['slug' => $t['slug']]) }}" class="wow-card md is-fluid" style="text-decoration:none;">
          <div class="wow-body">
            <div class="wow-type text-muted">Therapy</div>
            <div class="wow-title">{{ $t['title'] }}</div>
            @if(!empty($t['seo_description']))
              <div class="text-ink-600 mt-2">{{ $t['seo_description'] }}</div>
            @endif
          </div>
          <div class="wow-bottom">
            <div class="actions"><span class="wow-btn-like">View</span></div>
          </div>
        </a>
      @endforeach
    </div>
  </div>
</section>
@endsection
