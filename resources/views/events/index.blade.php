{{-- resources/views/events/index.blade.php --}}
@extends('layouts.app')

@push('head')
  <title>{{ $seo['title'] ?? 'Events & Workshops | We Offer Wellness™' }}</title>
  @if(!empty($seo['description']))<meta name="description" content="{{ $seo['description'] }}">@endif
  @if(!empty($seo['robots']))<meta name="robots" content="{{ $seo['robots'] }}">@endif
  @if(!empty($seo['canonical']))<link rel="canonical" href="{{ $seo['canonical'] }}">@endif
@endpush

@section('content')
@php
  $items = $results['items'] ?? [];
@endphp

<section class="section">
  <div class="container-page">
    <div class="mb-4">
      <div class="kicker">Browse</div>
      <h1>Events & Workshops</h1>
      <p class="text-ink-600 mt-2" style="max-width:70ch;">
        Upcoming sessions, gatherings and workshops — online and near you.
      </p>
    </div>

    {{-- Filters --}}
    <form method="get" action="{{ url('/events') }}" class="card p-3 mb-4" style="border-radius:18px;">
      <div class="grid md:grid-cols-5 gap-3 items-end">
        <div>
          <label class="form-label">Type</label>
          <select class="form-control" name="type">
            <option value="" @selected(($filters['type'] ?? '') === '')>All</option>
            <option value="event" @selected(($filters['type'] ?? '') === 'event')>Events</option>
            <option value="workshop" @selected(($filters['type'] ?? '') === 'workshop')>Workshops</option>
          </select>
        </div>

        <div>
          <label class="form-label">Format</label>
          <select class="form-control" name="format">
            <option value="" @selected(($filters['format'] ?? '') === '')>All</option>
            <option value="online" @selected(($filters['format'] ?? '') === 'online')>Online</option>
            <option value="in_person" @selected(($filters['format'] ?? '') === 'in_person')>Near me</option>
          </select>
        </div>

        <div>
          <label class="form-label">Location</label>
          <input class="form-control" name="location" value="{{ $filters['location'] ?? '' }}" placeholder="e.g. London, Kent">
        </div>

        <div>
          <label class="form-label">Sort</label>
          <select class="form-control" name="sort">
            <option value="" @selected(($filters['sort'] ?? '') === '')>Recommended</option>
            <option value="date_asc" @selected(($filters['sort'] ?? '') === 'date_asc')>Soonest</option>
            <option value="date_desc" @selected(($filters['sort'] ?? '') === 'date_desc')>Latest</option>
          </select>
        </div>

        <div class="flex gap-2 justify-end">
          <button class="btn btn-primary" type="submit">Apply</button>
          <a class="btn btn-light" href="{{ url('/events') }}">Reset</a>
        </div>
      </div>
    </form>

    {{-- Results --}}
    @if(count($items))
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($items as $it)
          @php
            $title = $it['title'] ?? 'Untitled';
            $img = $it['image'] ?? ($it['featured_image'] ?? null);
            $summary = $it['summary'] ?? ($it['description_short'] ?? null);

            $slug = $it['slug'] ?? ($it['handle'] ?? null);
            $url = $it['url'] ?? ($slug ? url('/events/'.$slug) : '#');
            if($url && !str_starts_with($url, 'http')) $url = url($url);
          @endphp

          <a href="{{ $url }}" class="wow-card md is-fluid" style="text-decoration:none;">
            <div class="wow-media">
              @if($img)<img src="{{ $img }}" alt="{{ $title }}">@endif
            </div>
            <div class="wow-body">
              <div class="wow-type text-muted">Event</div>
              <div class="wow-title">{{ $title }}</div>
              @if($summary)
                <div class="text-ink-600 mt-2">{{ \Illuminate\Support\Str::limit(strip_tags($summary), 120) }}</div>
              @endif
            </div>
            <div class="wow-bottom">
              <div class="actions"><span class="wow-btn-like">View details</span></div>
            </div>
          </a>
        @endforeach
      </div>

      {{-- Pagination --}}
      @php
        $meta = $results['meta'] ?? [];
        $current = (int)($meta['current_page'] ?? request()->query('page', 1));
        $last = (int)($meta['last_page'] ?? ($meta['total_pages'] ?? 1));
        $q = request()->query();
      @endphp

      @if($last > 1)
        <div class="flex items-center justify-center gap-3 mt-5">
          @if($current > 1)
            <a class="btn btn-light" href="{{ request()->url() . '?' . http_build_query(array_merge($q, ['page' => $current - 1])) }}">← Prev</a>
          @endif
          <span class="text-muted">Page {{ $current }} of {{ $last }}</span>
          @if($current < $last)
            <a class="btn btn-light" href="{{ request()->url() . '?' . http_build_query(array_merge($q, ['page' => $current + 1])) }}">Next →</a>
          @endif
        </div>
      @endif
    @else
      <div class="card p-4" style="border-radius:18px;">
        <div class="text-muted">No events found — try resetting filters.</div>
      </div>
    @endif
  </div>
</section>
@endsection
