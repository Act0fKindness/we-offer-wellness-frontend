{{-- resources/views/online/index.blade.php --}}
@extends('layouts.app')

@push('head')
  <title>{{ $seo['title'] ?? 'Online | We Offer Wellness™' }}</title>
  @if(!empty($seo['description']))<meta name="description" content="{{ $seo['description'] }}">@endif
  @if(!empty($seo['robots']))<meta name="robots" content="{{ $seo['robots'] }}">@endif
  @if(!empty($seo['canonical']))<link rel="canonical" href="{{ $seo['canonical'] }}">@endif
@endpush

@section('content')
@include('partials.breadcrumbs', [
  'crumbs' => [
    ['label' => 'Home', 'url' => url('/')],
    ['label' => $modalityLabel ?? 'Online', 'url' => $pageUrl ?? url('/online')],
  ],
  'schemaUrl' => $pageUrl ?? url('/online'),
])

@php
  $items = collect($results['items'] ?? []);
  $sortValue = (string) ($filters['sort'] ?? '');
  $sortLabel = match ($sortValue) {
    'price_asc' => 'Price: Low → High',
    'price_desc' => 'Price: High → Low',
    'rating_desc' => 'Top rated',
    default => 'Recommended',
  };
  $onlineFilterSegments = [
    [
      'key' => 'sort',
      'label' => 'Sort',
      'value' => $sortLabel,
      'placeholder' => 'Recommended',
      'panelTitle' => 'Sort results',
      'panelSubtitle' => 'Choose how online experiences are ordered.',
      'panelWidth' => 430,
      'options' => [
        [
          'label' => 'Recommended',
          'value' => '',
          'subtitle' => 'Best match for this page',
          'count' => 'Default',
          'selected' => $sortValue === '',
        ],
        [
          'label' => 'Price: Low → High',
          'value' => 'price_asc',
          'subtitle' => 'Cheaper options first',
          'selected' => $sortValue === 'price_asc',
        ],
        [
          'label' => 'Price: High → Low',
          'value' => 'price_desc',
          'subtitle' => 'Higher-priced options first',
          'selected' => $sortValue === 'price_desc',
        ],
        [
          'label' => 'Top rated',
          'value' => 'rating_desc',
          'subtitle' => 'Highest reviewed offerings first',
          'selected' => $sortValue === 'rating_desc',
        ],
      ],
    ],
  ];
  $onlineFilterChips = $sortValue !== ''
    ? [['param' => 'sort', 'label' => 'Sort', 'value' => $sortLabel]]
    : [];
@endphp

<section class="section">
  <div class="container-page">
    <div class="mb-4">
      <div class="kicker">Browse</div>
      <h1>{{ $modalityLabel ?? 'Online' }}</h1>
      <p class="text-ink-600 mt-2" style="max-width:70ch;">
        @if(!empty($modality))
          Online {{ strtolower((string) $modalityLabel) }} experiences you can join from anywhere — calm, convenient, and actually enjoyable.
        @else
          Online wellness therapies you can join from anywhere — calm, convenient, and actually enjoyable.
        @endif
      </p>
    </div>

    @if(!empty($modality))
      @include('partials.guide_panel', [
        'guidePanelModality' => $modality,
      ])
    @endif

    @include('partials.wow-filter-bar', [
      'action' => $pageUrl ?? url('/online'),
      'clearUrl' => $pageUrl ?? url('/online'),
      'ariaLabel' => 'Filter online experiences',
      'mobileLabel' => 'Filters',
      'resultCount' => $items->count(),
      'resultLabel' => 'results',
      'filters' => $filters,
      'segments' => $onlineFilterSegments,
      'chips' => $onlineFilterChips,
    ])

    {{-- Results --}}
    @if($items->count())
      <div class="flex flex-wrap gap-6 items-start">
        @foreach($items as $item)
          @include('partials.product_card_v4', ['product' => $item, 'preferredLocation' => null])
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
