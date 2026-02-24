{{-- resources/views/locations/index.blade.php --}}
@extends('layouts.app')

@push('head')
  <title>{{ $seo['title'] ?? 'Locations | We Offer Wellness™' }}</title>
  @if(!empty($seo['description']))<meta name="description" content="{{ $seo['description'] }}">@endif
  @if(!empty($seo['robots']))<meta name="robots" content="{{ $seo['robots'] }}">@endif
@endpush

@section('content')
<section class="section">
  <div class="container-page">
    <div class="mb-4">
      <div class="kicker">Browse</div>
      <h1>Locations</h1>
      <p class="text-ink-600 mt-2" style="max-width:70ch;">
        Find experiences by location — or use Near Me for the fastest route to what’s close.
      </p>
      <div class="mt-3">
        <a class="btn btn-primary" href="{{ route('nearMe') }}">Near Me</a>
      </div>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
      @foreach(($locations ?? []) as $loc)
        <a href="{{ route('locations.show', ['slug' => $loc['slug']]) }}" class="wow-card md is-fluid" style="text-decoration:none;">
          <div class="wow-body">
            <div class="wow-type text-muted">Location</div>
            <div class="wow-title">{{ $loc['title'] }}</div>
            @if(!empty($loc['seo_description']))
              <div class="text-ink-600 mt-2">{{ $loc['seo_description'] }}</div>
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
