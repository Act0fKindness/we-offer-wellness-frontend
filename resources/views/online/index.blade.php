{{-- resources/views/online/index.blade.php --}}
@extends('layouts.app')

@push('head')
  <title>{{ $seo['title'] ?? 'Online | We Offer Wellness™' }}</title>
  @if(!empty($seo['description']))<meta name="description" content="{{ $seo['description'] }}">@endif
  @if(!empty($seo['robots']))<meta name="robots" content="{{ $seo['robots'] }}">@endif
  @if(!empty($seo['canonical']))<link rel="canonical" href="{{ $seo['canonical'] }}">@endif
@endpush

@section('content')
@php
  $items = $results['items'] ?? [];

  $normPrice = function($v){
    if($v === null || $v === '') return null;
    if(is_string($v)) $v = preg_replace('/[^0-9.\-]/', '', $v);
    return is_numeric($v) ? (float)$v : null;
  };
@endphp

<section class="section">
  <div class="container-page">
    <div class="mb-4">
      <div class="kicker">Browse</div>
      <h1>Online</h1>
      <p class="text-ink-600 mt-2" style="max-width:70ch;">
        Online wellness experiences you can join from anywhere — calm, convenient, and actually enjoyable.
      </p>
    </div>

    {{-- Sort --}}
    <form method="get" action="{{ url('/online') }}" class="card p-3 mb-4" style="border-radius:18px;">
      <div class="grid md:grid-cols-2 gap-3 items-end">
        <div>
          <label class="form-label">Sort</label>
          <select class="form-control" name="sort">
            <option value="" @selected(($filters['sort'] ?? '') === '')>Recommended</option>
            <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>Price: Low → High</option>
            <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>Price: High → Low</option>
            <option value="rating_desc" @selected(($filters['sort'] ?? '') === 'rating_desc')>Top rated</option>
          </select>
        </div>

        <div class="flex gap-2 justify-end">
          <button class="btn btn-primary" type="submit">Apply</button>
          <a class="btn btn-light" href="{{ url('/online') }}">Reset</a>
        </div>
      </div>
    </form>

    {{-- Results --}}
    @if(count($items))
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($items as $it)
          @php
            $title = $it['title'] ?? 'Untitled';
            $type  = $it['type'] ?? ($it['offering_type'] ?? 'Experience');
            $img   = $it['image'] ?? ($it['featured_image'] ?? null);

            $url = $it['url'] ?? ($it['handle'] ?? null);
            if($url && !str_starts_with($url, 'http')) $url = url($url);
            $url = $url ?: '#';

            $rating  = $it['rating'] ?? null;
            $reviews = $it['review_count'] ?? ($it['reviews'] ?? null);

            $priceMin = $normPrice($it['price_min'] ?? null);
            $price    = $priceMin ?? $normPrice($it['price'] ?? null);
          @endphp

          <a href="{{ $url }}" class="wow-card md is-fluid" style="text-decoration:none;">
            <div class="wow-media">
              @if($img)<img src="{{ $img }}" alt="{{ $title }}">@endif
            </div>

            <div class="wow-body">
              <div class="wow-type text-muted">{{ $type }}</div>
              <div class="wow-title">{{ $title }}</div>

              @if($rating)
                <div class="rating-text">
                  ★ {{ number_format((float)$rating, 1) }}
                  @if($reviews)<small class="text-muted">({{ (int)$reviews }})</small>@endif
                </div>
              @endif
            </div>

            <div class="wow-bottom">
              <div class="price">
                @if($price !== null)
                  £{{ number_format($price, 2) }} @if($priceMin !== null)<small>from</small>@endif
                @else
                  <span class="text-muted">View</span>
                @endif
              </div>
              <div class="actions"><span class="wow-btn-like">See details</span></div>
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
        <div class="text-muted">No results yet — try resetting filters.</div>
      </div>
    @endif
  </div>
</section>
@endsection
