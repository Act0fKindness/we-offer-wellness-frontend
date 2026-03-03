{{-- resources/views/therapies/show.blade.php --}}
@extends('layouts.app')

@push('head')
  <title>{{ $seo['title'] ?? (($therapy['title'] ?? 'Therapy').' | We Offer Wellness™') }}</title>
  @if(!empty($seo['description']))<meta name="description" content="{{ $seo['description'] }}">@endif
  @if(!empty($seo['robots']))<meta name="robots" content="{{ $seo['robots'] }}">@endif
  @if(!empty($seo['canonical']))<link rel="canonical" href="{{ $seo['canonical'] }}">@endif
@endpush

@section('content')
@php
  $slug  = $therapy['slug'] ?? request()->route('slug');
  $items = $results['items'] ?? collect();
  if (!($items instanceof \Illuminate\Support\Collection)) {
    $items = collect($items ?? []);
  }
@endphp

<section class="section">
  <div class="container-page">
    <div class="flex items-end justify-between gap-4 mb-4">
      <div>
        <div class="kicker">Therapies</div>
        <h1>{{ $therapy['title'] ?? 'Therapy' }}</h1>
        @if(!empty($therapy['seo_description']))
          <p class="text-ink-600 mt-2" style="max-width:70ch;">{{ $therapy['seo_description'] }}</p>
        @endif
      </div>
      <div class="hidden md:block">
        <a href="{{ route('therapies.index') }}" class="btn btn-light">All therapies</a>
      </div>
    </div>

    {{-- Filters --}}
    <form method="get" action="{{ url('/therapies/'.$slug) }}" class="card p-3 mb-4" style="border-radius:18px;">
      <div class="grid md:grid-cols-3 gap-3 items-end">
        <div class="col-span-3 md:col-span-1">
          <label class="form-label">Format</label>
          <select class="form-control" name="format">
            <option value="" @selected(($filters['format'] ?? '') === '')>All</option>
            <option value="online" @selected(($filters['format'] ?? '') === 'online')>Online</option>
            <option value="in_person" @selected(($filters['format'] ?? '') === 'in_person')>Near me</option>
          </select>
        </div>
        <div class="col-span-3 md:col-span-1">
          <label class="form-label">Location</label>
          <input class="form-control" name="location" value="{{ $filters['location'] ?? '' }}" placeholder="e.g. London, Kent">
        </div>
        <div class="col-span-3 md:col-span-1">
          <label class="form-label">Sort</label>
          <div class="flex gap-2">
            <select class="form-control" name="sort">
              <option value="" @selected(($filters['sort'] ?? '') === '')>Recommended</option>
              <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>Price: Low → High</option>
              <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>Price: High → Low</option>
              <option value="rating_desc" @selected(($filters['sort'] ?? '') === 'rating_desc')>Top rated</option>
            </select>
            <div class="flex gap-2">
              <button class="btn btn-primary" type="submit">Apply</button>
              <a class="btn btn-light" href="{{ url('/therapies/'.$slug) }}">Reset</a>
            </div>
          </div>
        </div>
      </div>
    </form>

    {{-- Results --}}
    @if($items->count())
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($items as $product)
          @include('partials.product_card', [
            'product' => $product,
            'preferredLocation' => $filters['location'] ?? null,
          ])
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
        <div class="text-muted">No results yet — try changing format/location or resetting filters.</div>
      </div>
    @endif
  </div>
</section>
@endsection

@push('scripts')
<script>
(function(){
  const entry = {
    slug: @json($slug ?? null),
    title: @json($therapy['title'] ?? 'Therapy'),
    url: @json(url('/therapies/'.$slug)),
    id: @json($therapy['id'] ?? null)
  };
  if (!entry.slug) return;
  const KEY = 'wow_therapy_history';
  try {
    const raw = localStorage.getItem(KEY);
    let list = [];
    if (raw) {
      const parsed = JSON.parse(raw);
      if (Array.isArray(parsed)) list = parsed;
    }
    list = list.filter(item => item && item.slug !== entry.slug);
    list.unshift(entry);
    list = list.slice(0, 6);
    localStorage.setItem(KEY, JSON.stringify(list));
    document.dispatchEvent(new CustomEvent('wow:therapy-history', { detail: list }));
  } catch (_err) {}
})();
</script>
@endpush
