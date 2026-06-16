@props([
    'idPrefix' => 'search-v4',
    'searchUrl' => url('/search'),
    'resultCount' => 0,
    'mobileTopOffset' => 12,
])

@php
    $what = trim((string) request('what', ''));
    $where = trim((string) request('where', ''));
    $when = trim((string) request('when', ''));
    $whenStart = trim((string) request('when_start', ''));
    $whenEnd = trim((string) request('when_end', ''));

    $adults = max(0, (int) request('adults', 0));
    $groupType = trim((string) request('group_type', ''));
    if ($adults <= 0 && $groupType !== '') {
        $groupTypeLower = strtolower($groupType);
        $adults = match ($groupTypeLower) {
            'couple' => 2,
            'group' => 3,
            'solo' => 1,
            default => 0,
        };
    }

    $sort = trim((string) request('sort', 'popular')) ?: 'popular';
    $priceMax = trim((string) request('price_max', ''));
    $rating = trim((string) request('rating', ''));
    $type = trim((string) request('type', ''));
    $onlineOnly = in_array(strtolower((string) request('mode', '')), ['online', '1', 'true', 'yes', 'on'], true);
    $anytime = in_array(strtolower((string) request('anytime', '')), ['1', 'true', 'yes', 'on'], true);
    $view = trim((string) request('view', 'map')) ?: 'map';
    $mapMode = trim((string) request('map_mode', '2d')) ?: '2d';

    $sortLabels = [
        'popular' => 'Recommended',
        'newest' => 'Newest',
        'price_asc' => 'Price: low to high',
        'price_desc' => 'Price: high to low',
        'rating_desc' => 'Highest rated',
    ];

    $ratingLabels = [
        'reviewed' => 'Reviewed only',
        '4.5' => '4.5+ stars',
        '4' => '4.0+ stars',
    ];

    $typeLabels = [
        'therapies' => 'Therapies',
        'therapy' => 'Therapies',
        'classes' => 'Classes',
        'class' => 'Classes',
        'events' => 'Events',
        'event' => 'Events',
        'retreats' => 'Retreats',
        'retreat' => 'Retreats',
        'gifts' => 'Gifts',
        'gift' => 'Gifts',
    ];

    $whenLabel = $when !== '' ? $when : 'Select dates';
    if ($when !== '') {
        $quickWhenLabels = [
            'today' => 'Today',
            'tomorrow' => 'Tomorrow',
            'weekend' => 'This weekend',
            'next-week' => 'Next week',
        ];

        $whenKey = strtolower($when);
        if (isset($quickWhenLabels[$whenKey])) {
            $whenLabel = $quickWhenLabels[$whenKey];
        }
    }

    if ($whenLabel === 'Select dates' && ($whenStart !== '' || $whenEnd !== '')) {
        try {
            $startLabel = $whenStart !== '' ? \Illuminate\Support\Carbon::parse($whenStart)->format('d M Y') : '';
            $endLabel = $whenEnd !== '' ? \Illuminate\Support\Carbon::parse($whenEnd)->format('d M Y') : '';
            $whenLabel = $startLabel && $endLabel
                ? $startLabel . ' - ' . $endLabel
                : ($startLabel ?: ($endLabel ?: 'Select dates'));
        } catch (\Throwable $e) {
            $whenLabel = 'Select dates';
        }
    }

    $guestsLabel = $adults > 0
        ? $adults . ' ' . ($adults === 1 ? 'guest' : 'guests')
        : 'Any guests';

    if ($adults > 0 && $groupType !== '') {
        $guestsLabel .= ' · ' . ucfirst(strtolower($groupType));
    }

    $chips = [];
    if ($what !== '') {
        $chips[] = ['label' => 'What', 'value' => $what];
    }
    if ($where !== '') {
        $chips[] = ['label' => 'Where', 'value' => $where];
    }
    if ($whenLabel !== 'Select dates') {
        $chips[] = ['label' => 'When', 'value' => $whenLabel];
    }
    if ($adults > 0 || $groupType !== '') {
        $chips[] = ['label' => 'Who', 'value' => $guestsLabel];
    }
    if ($sort !== 'popular') {
        $chips[] = ['label' => 'Sort', 'value' => $sortLabels[$sort] ?? $sort];
    }
    if ($priceMax !== '') {
        $chips[] = ['label' => 'Price', 'value' => 'Up to £' . $priceMax];
    }
    if ($rating !== '') {
        $chips[] = ['label' => 'Rating', 'value' => $ratingLabels[$rating] ?? $rating];
    }
    if ($type !== '') {
        $chips[] = ['label' => 'Type', 'value' => $typeLabels[$type] ?? ucfirst($type)];
    }
    if ($onlineOnly) {
        $chips[] = ['label' => 'Mode', 'value' => 'Online only'];
    }
    if ($anytime) {
        $chips[] = ['label' => 'Timing', 'value' => 'Anytime'];
    }

    $filterCount = count($chips);
    $resultCount = (int) $resultCount;
@endphp

<div
    id="{{ $idPrefix }}-root"
    data-wow-searchbar-v4
    data-id-prefix="{{ $idPrefix }}"
    data-search-url="{{ $searchUrl }}"
    data-result-count="{{ $resultCount }}"
    data-mobile-top-offset="{{ $mobileTopOffset }}"
    data-initial-query='@json(request()->query())'
>
    <div class="wow-search-filter-shell">
        <div class="wow-search-filter-spacer" aria-hidden="true" style="height: 176px;"></div>

        <section class="wow-search-filter" aria-label="Search filters">
            <div class="wow-search-top-row" aria-label="Search tools">
                <button class="wow-filter-icon-btn" type="button" aria-expanded="false" aria-label="Open filters">
                    <i class="bi bi-sliders" aria-hidden="true"></i>
                    <span class="wow-filter-badge">{{ $filterCount }}</span>
                </button>

                <div class="wow-desktop-map-controls" aria-label="Desktop view and map mode controls">
                    <div class="wow-segmented-control" data-control-group="view">
                        <span class="wow-control-label">View</span>
                        <span class="wow-control-pill">
                            <button type="button" class="{{ $view === 'map' ? 'is-active' : '' }}" aria-pressed="{{ $view === 'map' ? 'true' : 'false' }}">Map</button>
                            <button type="button" class="{{ $view === 'list' ? 'is-active' : '' }}" aria-pressed="{{ $view === 'list' ? 'true' : 'false' }}">List</button>
                        </span>
                    </div>

                    <div class="wow-segmented-control" data-control-group="mode">
                        <span class="wow-control-label">Mode</span>
                        <span class="wow-control-pill">
                            <button type="button" class="{{ $mapMode === '2d' ? 'is-active' : '' }}" aria-pressed="{{ $mapMode === '2d' ? 'true' : 'false' }}">2D</button>
                            <button type="button" class="{{ $mapMode === '3d' ? 'is-active' : '' }}" aria-pressed="{{ $mapMode === '3d' ? 'true' : 'false' }}">3D</button>
                        </span>
                    </div>
                </div>
            </div>

            <form class="wow-search-card" action="{{ $searchUrl }}" method="get" role="search">
                <div class="wow-search-main">
                    <div class="wow-segment wow-segment--what" data-search-segment="what">
                        <span class="wow-icon" aria-hidden="true">
                            <i class="bi bi-stars"></i>
                        </span>
                        <span class="wow-copy">
                            <span class="wow-label">What</span>
                            <input
                                id="{{ $idPrefix }}-what"
                                type="search"
                                name="what"
                                autocomplete="off"
                                placeholder="Massage, yoga, breathwork..."
                                aria-expanded="false"
                                aria-controls="{{ $idPrefix }}-what-pane"
                                value="{{ $what }}"
                            >
                        </span>
                    </div>

                    <div class="wow-segment wow-segment--where" data-search-segment="where">
                        <span class="wow-icon" aria-hidden="true">
                            <i class="bi bi-geo-alt"></i>
                        </span>
                        <span class="wow-copy">
                            <span class="wow-label">Where</span>
                            <input
                                id="{{ $idPrefix }}-where"
                                type="search"
                                name="where"
                                autocomplete="off"
                                placeholder="City, region, or Online"
                                aria-expanded="false"
                                aria-controls="{{ $idPrefix }}-where-pane"
                                value="{{ $where }}"
                            >
                        </span>
                    </div>

                    <div class="wow-segment wow-segment--when" role="button" tabindex="0" aria-expanded="false">
                        <span class="wow-icon" aria-hidden="true">
                            <i class="bi bi-calendar3"></i>
                        </span>
                        <span class="wow-copy">
                            <span class="wow-label">When</span>
                            <input
                                id="{{ $idPrefix }}-when"
                                type="text"
                                name="when"
                                readonly
                                aria-haspopup="dialog"
                                placeholder="Select dates"
                                value="{{ $whenLabel }}"
                            >
                        </span>
                    </div>

                    <div class="wow-segment wow-segment--who" role="button" tabindex="0" aria-expanded="false">
                        <span class="wow-icon" aria-hidden="true">
                            <i class="bi bi-person"></i>
                        </span>
                        <span class="wow-copy">
                            <span class="wow-label">Who</span>
                            <span class="wow-value">{{ $guestsLabel }}</span>
                        </span>
                    </div>

                    <button class="wow-submit" type="submit" aria-label="Search">
                        <span class="visually-hidden">Search</span>
                        <i class="bi bi-search" aria-hidden="true"></i>
                    </button>
                </div>

                @if ($sort !== 'popular')
                    <input type="hidden" name="sort" value="{{ $sort }}">
                @endif
                @if ($priceMax !== '')
                    <input type="hidden" name="price_max" value="{{ $priceMax }}">
                @endif
                @if ($rating !== '')
                    <input type="hidden" name="rating" value="{{ $rating }}">
                @endif
                @if ($type !== '')
                    <input type="hidden" name="type" value="{{ $type }}">
                @endif
                @if ($onlineOnly)
                    <input type="hidden" name="mode" value="online">
                @endif
                @if ($anytime)
                    <input type="hidden" name="anytime" value="1">
                @endif
                @if ($adults > 0)
                    <input type="hidden" name="adults" value="{{ $adults }}">
                @endif
                @if ($adults > 0 && $groupType !== '')
                    <input type="hidden" name="group_type" value="{{ strtolower($groupType) }}">
                @endif
                @if ($view !== 'map')
                    <input type="hidden" name="view" value="{{ $view }}">
                @endif
                @if ($mapMode !== '2d')
                    <input type="hidden" name="map_mode" value="{{ $mapMode }}">
                @endif
                @if ($whenStart !== '')
                    <input type="hidden" name="when_start" value="{{ $whenStart }}">
                @endif
                @if ($whenEnd !== '')
                    <input type="hidden" name="when_end" value="{{ $whenEnd }}">
                @endif
            </form>

            <div class="wow-search-bottom-row" aria-label="Search filters">
                <div class="wow-active-chips" data-chip-list>
                    @foreach ($chips as $chip)
                        <span class="wow-chip">
                            <strong>{{ $chip['label'] }}:</strong>
                            <span>{{ $chip['value'] }}</span>
                            <button type="button" aria-label="Remove {{ $chip['label'] }}">×</button>
                        </span>
                    @endforeach
                </div>

                <div class="wow-filter-actions">
                    <div class="wow-results-count wow-results-count--compact">
                        <strong data-result-count>{{ $resultCount }}</strong><span>results</span>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
