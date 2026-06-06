{{-- resources/views/needs/show.blade.php --}}
@extends('layouts.app')

@push('head')
  <title>{{ $seo['title'] ?? (($need['title'] ?? 'Need').' | We Offer Wellness™') }}</title>
  @if(!empty($seo['description']))<meta name="description" content="{{ $seo['description'] }}">@endif
  @if(!empty($seo['robots']))<meta name="robots" content="{{ $seo['robots'] }}">@endif
@endpush

@section('content')
@php
  $slug  = $need['slug'] ?? request()->route('slug');
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
  $needFilterSegments = [
    [
      'key' => 'sort',
      'label' => 'Sort',
      'value' => $sortLabel,
      'placeholder' => 'Recommended',
      'panelTitle' => 'Sort results',
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
  $needFilterChips = array_values(array_filter([
    $sortValue !== '' ? ['param' => 'sort', 'label' => 'Sort', 'value' => $sortLabel] : null,
    $formatValue !== '' ? ['param' => 'format', 'label' => 'Format', 'value' => $formatLabel] : null,
    $locationValue !== '' ? ['param' => 'location', 'label' => 'Location', 'value' => $locationValue] : null,
  ]));
@endphp

@include('partials.breadcrumbs', [
  'crumbs' => [
    ['label' => 'Home', 'url' => url('/')],
    ['label' => 'Needs', 'url' => url('/needs')],
    ['label' => $need['title'] ?? 'Need'],
  ],
  'schemaUrl' => url('/needs/' . $slug),
])

<section class="section">
  <div class="container-page">
    <div class="flex items-end justify-between gap-4 mb-4">
      <div>
        <div class="kicker">By Need</div>
        <h1>{{ $need['title'] ?? 'Need' }}</h1>
        @if(!empty($need['seo_description']))
          <p class="text-ink-600 mt-2" style="max-width:70ch;">{{ $need['seo_description'] }}</p>
        @endif
      </div>
      <div class="hidden md:block">
        <a href="{{ route('needs.index') }}" class="btn btn-light">All needs</a>
      </div>
    </div>

    @include('partials.wow-filter-bar', [
      'action' => url('/needs/' . $slug),
      'clearUrl' => url('/needs/' . $slug),
      'ariaLabel' => 'Filter needs',
      'mobileLabel' => 'Filters',
      'resultCount' => $items->count(),
      'resultLabel' => 'results',
      'filters' => $filters,
      'segments' => $needFilterSegments,
      'chips' => $needFilterChips,
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
  const slug = @json($slug ?? null);
  const title = @json($need['title'] ?? 'Need');
  if (!slug) return;
  const entry = { slug: slug, title: title, url: @json(url('/needs/'.$slug)) };
  const key = 'wow_need_history';
  try {
    const raw = localStorage.getItem(key);
    let items = [];
    if (raw) {
      const parsed = JSON.parse(raw);
      if (Array.isArray(parsed)) items = parsed;
    }
    items = items.filter(item => item && item.slug !== entry.slug);
    items.unshift(entry);
    items = items.slice(0, 6);
    localStorage.setItem(key, JSON.stringify(items));
    document.dispatchEvent(new CustomEvent('wow:need-history', { detail: items }));
  } catch (_err) {}
})();
</script>
@endpush
