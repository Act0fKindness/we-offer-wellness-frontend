{{-- resources/views/locations/show.blade.php --}}
@extends('layouts.app')

@push('head')
  <title>{{ $seo['title'] ?? (($location['title'] ?? 'Location').' | We Offer Wellness™') }}</title>
  @if(!empty($seo['description']))<meta name="description" content="{{ $seo['description'] }}">@endif
  @if(!empty($seo['robots']))<meta name="robots" content="{{ $seo['robots'] }}">@endif
  @if(!empty($seo['canonical']))<link rel="canonical" href="{{ $seo['canonical'] }}">@endif
@endpush

@section('content')
@php
  $locationTitle = (string) ($location['title'] ?? 'Location');
@endphp

<section class="pt-4 pb-2 bg-transparent">
  <div class="container-page">
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
      <div>
        <div class="kicker mb-1 text-ink-600">Locations</div>
        <h1 class="text-ink-900" style="font-size:2rem;font-weight:700;">
          {{ $locationTitle }}
        </h1>
        <p class="text-ink-600 mt-2" style="max-width:72ch;">
          {!! nl2br(e((string) ($seo['description'] ?? ($location['seo_description'] ?? 'Find therapies, classes and events near you.')))) !!}
        </p>
      </div>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="{{ route('locations.index') }}" class="btn btn-light">All locations</a>
        <a href="{{ url('/search') }}" class="btn btn-primary">Search all</a>
      </div>
    </div>
  </div>
</section>

<div class="search-content-wrapper">
  @include('search.partials.desktop', [
      'resultsHeading' => $locationTitle.' results',
  ])
</div>
@endsection
