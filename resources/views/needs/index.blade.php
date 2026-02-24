{{-- resources/views/needs/index.blade.php --}}
@extends('layouts.app')

@push('head')
  <title>{{ $seo['title'] ?? 'By Need | We Offer Wellness™' }}</title>
  @if(!empty($seo['description']))<meta name="description" content="{{ $seo['description'] }}">@endif
  @if(!empty($seo['robots']))<meta name="robots" content="{{ $seo['robots'] }}">@endif
@endpush

@section('content')
<section class="section">
  <div class="container-page">
    <div class="mb-4">
      <div class="kicker">Browse</div>
      <h1>By Need</h1>
      <p class="text-ink-600 mt-2" style="max-width:70ch;">
        Start with what you need most — then we’ll match you with experiences and therapies that fit.
      </p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
      @foreach(($needs ?? []) as $need)
        <a href="{{ route('needs.show', ['slug' => $need['slug']]) }}" class="wow-card md is-fluid" style="text-decoration:none;">
          <div class="wow-body">
            <div class="wow-type text-muted">Need</div>
            <div class="wow-title">{{ $need['title'] }}</div>
            @if(!empty($need['seo_description']))
              <div class="text-ink-600 mt-2">{{ $need['seo_description'] }}</div>
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
