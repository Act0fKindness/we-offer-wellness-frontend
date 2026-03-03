{{-- resources/views/near-me/index.blade.php --}}
@extends('layouts.app')

@push('head')
  <title>{{ $seo['title'] ?? 'Near Me | We Offer Wellness™' }}</title>
  @if(!empty($seo['description']))<meta name="description" content="{{ $seo['description'] }}">@endif
  @if(!empty($seo['robots']))<meta name="robots" content="{{ $seo['robots'] }}">@endif
  @if(!empty($seo['canonical']))<link rel="canonical" href="{{ $seo['canonical'] }}">@endif
@endpush

@section('content')
<section class="section">
  <div class="container-page" style="max-width:760px;">
    <div class="mb-4">
      <div class="kicker">Find</div>
      <h1>Near Me</h1>
      <p class="text-ink-600 mt-2" style="max-width:70ch;">
        Enter your postcode and we’ll show what’s available near you.
      </p>
    </div>

    <div class="card p-4" style="border-radius:18px;">
      <form method="get" action="{{ url('/near-me') }}">
        <label class="form-label">Postcode</label>
        <div class="flex gap-2">
          <input class="form-control" name="postcode" placeholder="e.g. SW1A 1AA" value="{{ request()->query('postcode','') }}">
          <button class="btn btn-primary" type="submit">Search</button>
        </div>
        <p class="text-muted mt-2 mb-0" style="font-size:13px;">
          We use this only to find nearby results — not to start sending you “wellness near you” carrier pigeons.
        </p>
      </form>
    </div>
  </div>
</section>
@endsection
