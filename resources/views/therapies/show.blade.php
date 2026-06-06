{{-- resources/views/therapies/show.blade.php --}}
@extends('layouts.app')

@push('head')
  <title>{{ $seo['title'] ?? (($therapy['title'] ?? 'Therapy').' | We Offer Wellness™') }}</title>
  @if(!empty($seo['description']))<meta name="description" content="{{ $seo['description'] }}">@endif
  @if(!empty($seo['robots']))<meta name="robots" content="{{ $seo['robots'] }}">@endif
@endpush

@section('content')
@php
  $slug  = $therapy['slug'] ?? request()->route('slug');
  $items = $results['items'] ?? collect();
  if (!($items instanceof \Illuminate\Support\Collection)) {
    $items = collect($items ?? []);
  }
  $sortValue = (string) ($filters['sort'] ?? '');
  $formatValue = (string) ($filters['format'] ?? '');
  $locationValue = trim((string) ($filters['location'] ?? ''));
  $sortLabel = match ($sortValue) {
    'price_asc' => 'Price: Low → High',
    'price_desc' => 'Price: High → Low',
    'rating_desc' => 'Top rated',
    default => 'Recommended',
  };
  $formatLabel = match ($formatValue) {
    'online' => 'Online',
    'in_person' => 'Near me',
    default => 'All',
  };
  $therapyFilterSegments = [
    [
      'key' => 'sort',
      'label' => 'Sort',
      'value' => $sortLabel,
      'placeholder' => 'Recommended',
      'panelTitle' => 'Sort therapies',
      'panelSubtitle' => 'Choose how results are ordered.',
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
    [
      'key' => 'format',
      'label' => 'Format',
      'value' => $formatLabel,
      'placeholder' => 'All',
      'panelTitle' => 'Choose format',
      'panelSubtitle' => 'Online or in-person sessions.',
      'panelWidth' => 430,
      'options' => [
        [
          'label' => 'All',
          'value' => '',
          'subtitle' => 'Any format',
          'selected' => $formatValue === '',
        ],
        [
          'label' => 'Online',
          'value' => 'online',
          'subtitle' => 'Join from anywhere',
          'selected' => $formatValue === 'online',
        ],
        [
          'label' => 'Near me',
          'value' => 'in_person',
          'subtitle' => 'Physical sessions near you',
          'selected' => $formatValue === 'in_person',
        ],
      ],
    ],
    [
      'key' => 'location',
      'label' => 'Location',
      'value' => $locationValue !== '' ? $locationValue : 'Anywhere',
      'placeholder' => 'Anywhere',
      'panelTitle' => 'Filter by location',
      'panelSubtitle' => 'Search by city, county, or area.',
      'panelWidth' => 480,
      'kind' => 'input',
      'param' => 'location',
      'inputLabel' => 'Location',
      'inputValue' => $locationValue,
      'inputPlaceholder' => 'e.g. London, Kent',
      'buttonLabel' => 'Update location',
    ],
  ];
  $therapyFilterChips = array_values(array_filter([
    $sortValue !== '' ? ['param' => 'sort', 'label' => 'Sort', 'value' => $sortLabel] : null,
    $formatValue !== '' ? ['param' => 'format', 'label' => 'Format', 'value' => $formatLabel] : null,
    $locationValue !== '' ? ['param' => 'location', 'label' => 'Location', 'value' => $locationValue] : null,
  ]));
@endphp

@include('partials.breadcrumbs', [
  'crumbs' => [
    ['label' => 'Home', 'url' => url('/')],
    ['label' => 'Modalities', 'url' => url('/therapies')],
    ['label' => $therapy['title'] ?? 'Therapy'],
  ],
  'schemaUrl' => url('/therapies/' . $slug),
])

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

    @include('partials.wow-filter-bar', [
      'action' => url('/therapies/' . $slug),
      'clearUrl' => url('/therapies/' . $slug),
      'ariaLabel' => 'Filter therapies',
      'mobileLabel' => 'Filters',
      'resultCount' => $items->count(),
      'resultLabel' => 'results',
      'filters' => $filters,
      'segments' => $therapyFilterSegments,
      'chips' => $therapyFilterChips,
    ])

    {{-- Results --}}
    @if($items->count())
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($items as $product)
          @include('partials.product_card_v4', [
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
