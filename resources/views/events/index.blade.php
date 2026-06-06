{{-- resources/views/events/index.blade.php --}}
@extends('layouts.app')

@push('head')
  <title>{{ $seo['title'] ?? 'Events & Workshops | We Offer Wellness™' }}</title>
  @if(!empty($seo['description']))<meta name="description" content="{{ $seo['description'] }}">@endif
  @if(!empty($seo['robots']))<meta name="robots" content="{{ $seo['robots'] }}">@endif
@endpush

@section('content')
@include('partials.breadcrumbs', [
  'crumbs' => [
    ['label' => 'Home', 'url' => url('/')],
    ['label' => 'Events'],
  ],
  'schemaUrl' => url('/events'),
])

@php
  $upcomingEvents = $upcomingEvents ?? [];
  $pastEvents = $pastEvents ?? [];
  $sortValue = (string) ($filters['sort'] ?? '');
  $typeValue = (string) ($filters['type'] ?? '');
  $formatValue = (string) ($filters['format'] ?? '');
  $locationValue = trim((string) ($filters['location'] ?? ''));
  $sortLabel = match ($sortValue) {
    'date_asc' => 'Soonest',
    'date_desc' => 'Latest',
    default => 'Recommended',
  };
  $typeLabel = match ($typeValue) {
    'event' => 'Events',
    'workshop' => 'Workshops',
    default => 'All',
  };
  $formatLabel = match ($formatValue) {
    'online' => 'Online',
    'in_person' => 'Near me',
    default => 'All',
  };
  $eventCount = count($upcomingEvents) + count($pastEvents);
  $eventSections = [
    [
      'title' => 'Upcoming events',
      'subtitle' => 'Fresh sessions and community gatherings coming up next.',
      'items' => $upcomingEvents,
      'empty' => 'No upcoming events found for these filters.',
    ],
    [
      'title' => 'Past events',
      'subtitle' => 'Archived sessions and previous gatherings.',
      'items' => $pastEvents,
      'empty' => 'No past events found for these filters.',
    ],
  ];
  $eventFilterSegments = [
    [
      'key' => 'sort',
      'label' => 'Sort',
      'value' => $sortLabel,
      'placeholder' => 'Recommended',
      'panelTitle' => 'Sort events',
      'panelSubtitle' => 'Choose how events are ordered.',
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
          'label' => 'Soonest',
          'value' => 'date_asc',
          'subtitle' => 'Events coming up first',
          'selected' => $sortValue === 'date_asc',
        ],
        [
          'label' => 'Latest',
          'value' => 'date_desc',
          'subtitle' => 'Newest event listings first',
          'selected' => $sortValue === 'date_desc',
        ],
      ],
    ],
    [
      'key' => 'type',
      'label' => 'Type',
      'value' => $typeLabel,
      'placeholder' => 'All',
      'panelTitle' => 'Filter by type',
      'panelSubtitle' => 'Show only events or workshops.',
      'panelWidth' => 430,
      'options' => [
        [
          'label' => 'All',
          'value' => '',
          'subtitle' => 'Show every event type',
          'selected' => $typeValue === '',
        ],
        [
          'label' => 'Events',
          'value' => 'event',
          'subtitle' => 'General event listings',
          'selected' => $typeValue === 'event',
        ],
        [
          'label' => 'Workshops',
          'value' => 'workshop',
          'subtitle' => 'Practical sessions and workshops',
          'selected' => $typeValue === 'workshop',
        ],
      ],
    ],
    [
      'key' => 'format',
      'label' => 'Format',
      'value' => $formatLabel,
      'placeholder' => 'All',
      'panelTitle' => 'Choose format',
      'panelSubtitle' => 'Online or in-person events.',
      'panelWidth' => 430,
      'options' => [
        [
          'label' => 'All',
          'value' => '',
          'subtitle' => 'Any event format',
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
          'subtitle' => 'Physical events near you',
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
  $eventFilterChips = array_values(array_filter([
    $sortValue !== '' ? ['param' => 'sort', 'label' => 'Sort', 'value' => $sortLabel] : null,
    $typeValue !== '' ? ['param' => 'type', 'label' => 'Type', 'value' => $typeLabel] : null,
    $formatValue !== '' ? ['param' => 'format', 'label' => 'Format', 'value' => $formatLabel] : null,
    $locationValue !== '' ? ['param' => 'location', 'label' => 'Location', 'value' => $locationValue] : null,
  ]));

  $eventCardProduct = static function (array $item) {
    return new class($item) {
      public array $item;

      public function __construct(array $item)
      {
        $this->item = $item;
      }

      public function __get(string $key): mixed
      {
        if ($key === 'id') {
          return $this->item['id'] ?? $this->item['source_id'] ?? null;
        }

        if ($key === 'category') {
          $category = $this->item['category'] ?? '';
          if (is_array($category)) {
            return (object) $category;
          }

          if (is_string($category) && $category !== '') {
            return (object) ['name' => $category];
          }

          return null;
        }

        if ($key === 'vendor') {
          $vendorName = trim((string) ($this->item['vendor_name'] ?? ''));
          $vendorRating = $this->item['vendor_rating'] ?? null;
          $vendorReviewCount = (int) ($this->item['vendor_review_count'] ?? $this->item['review_count'] ?? 0);

          return (object) [
            'vendor_name' => $vendorName,
            'review_summary' => [
              'count' => $vendorReviewCount,
              'rating' => is_numeric($vendorRating) ? round((float) $vendorRating, 1) : null,
            ],
            'user' => (object) [
              'plan_key' => (string) ($this->item['plan_key'] ?? ''),
            ],
          ];
        }

        return $this->item[$key] ?? null;
      }

      public function __isset(string $key): bool
      {
        return array_key_exists($key, $this->item);
      }

      public function getFirstImageUrl(): string
      {
        foreach (['display_image', 'image', 'featured_image'] as $key) {
          $value = trim((string) ($this->item[$key] ?? ''));
          if ($value !== '') {
            return $value;
          }
        }

        return '';
      }

      public function hasDisplayableImage(): bool
      {
        $image = $this->getFirstImageUrl();
        return $image !== '' && ! str_contains($image, 'no-product-image.jpg');
      }

      public function getLocations(): array
      {
        $locations = $this->item['locations'] ?? [];
        if ($locations instanceof \Illuminate\Support\Collection) {
          $locations = $locations->all();
        }

        if (! is_array($locations)) {
          return [];
        }

        return array_values(array_filter(array_map(static fn ($value) => trim((string) $value), $locations)));
      }
    };
  };
@endphp

<section class="section">
  <div class="container-page">
    <div class="mb-4">
      <div class="kicker">Browse</div>
      <h1>Events</h1>
      <p class="text-ink-600 mt-2" style="max-width:70ch;">
        Upcoming and past sessions, workshops, and community gatherings — online and near you.
      </p>
    </div>

    @include('partials.wow-filter-bar', [
      'action' => url('/events'),
      'clearUrl' => url('/events'),
      'ariaLabel' => 'Filter events',
      'mobileLabel' => 'Filters',
      'resultCount' => $eventCount,
      'resultLabel' => 'events',
      'filters' => $filters,
      'segments' => $eventFilterSegments,
      'chips' => $eventFilterChips,
    ])

    {{-- Results --}}
    @if(count($upcomingEvents) || count($pastEvents))
      @foreach($eventSections as $section)
        <section class="mb-5">
          <div class="d-flex align-items-end justify-content-between gap-3 mb-3">
            <div>
              <h2 class="h3 mb-1">{{ $section['title'] }}</h2>
              <p class="text-ink-600 mb-0">{{ $section['subtitle'] }}</p>
            </div>
            <span class="badge rounded-pill text-bg-light">{{ count($section['items']) }}</span>
          </div>

          @if(count($section['items']))
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
              @foreach($section['items'] as $it)
                @include('partials.product_card_v4', ['product' => $eventCardProduct($it), 'preferredLocation' => null])
              @endforeach
            </div>
          @else
            <div class="card p-4" style="border-radius:18px;">
              <div class="text-muted">{{ $section['empty'] }}</div>
            </div>
          @endif
        </section>
      @endforeach

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
