@php
    $eventSource = data_get($product, 'when.event', data_get($product, 'event', []));
    if (is_object($eventSource)) {
        $eventSource = (array) $eventSource;
    }
    if (!is_array($eventSource)) {
        $eventSource = [];
    }

    $eventParseDateTime = function ($dateValue, $timeValue = null) {
        $rawDate = trim((string) ($dateValue ?? ''));
        if ($rawDate === '') {
            return null;
        }

        try {
            $raw = $timeValue
                ? $rawDate . 'T' . $timeValue . ':00'
                : (preg_match('/[T\s]/', $rawDate) ? $rawDate : $rawDate . 'T00:00:00');

            return \Carbon\Carbon::parse($raw);
        } catch (\Throwable $e) {
            return null;
        }
    };

    $eventBuildUrl = function ($baseUrl, $isoDate) {
        $baseUrl = trim((string) ($baseUrl ?? ''));
        $isoDate = trim((string) ($isoDate ?? ''));

        if ($baseUrl === '') {
            return $isoDate !== '' ? '?date=' . $isoDate : '#';
        }

        if ($isoDate === '') {
            return $baseUrl;
        }

        return str_contains($baseUrl, '?') ? $baseUrl . '&date=' . $isoDate : $baseUrl . '?date=' . $isoDate;
    };

    $eventFormatRange = function ($startDate, $startTime, $endDate, $endTime) use ($eventParseDateTime) {
        $start = $eventParseDateTime($startDate, $startTime);
        $end = $eventParseDateTime($endDate ?: $startDate, $endTime ?: $startTime);
        if (!$start || !$end) {
            return null;
        }

        $sameDay = $start->isSameDay($end);
        $sameYear = $start->year === $end->year;
        $startDateLabel = $start->format($sameYear ? 'M j' : 'M j, Y');
        $endDateLabel = $end->format($sameYear ? 'M j' : 'M j, Y');

        if ($startTime || $endTime) {
            $startTimeLabel = $start->format('g:i A');
            $endTimeLabel = $end->format('g:i A');

            return $sameDay
                ? $startDateLabel . ', ' . $startTimeLabel . ' – ' . $endTimeLabel
                : $startDateLabel . ', ' . $startTimeLabel . ' – ' . $endDateLabel . ', ' . $endTimeLabel;
        }

        return $sameDay ? $startDateLabel : $startDateLabel . ' – ' . $endDateLabel;
    };

    $eventTilesFromValue = function ($value, $baseUrl) use ($eventParseDateTime, $eventBuildUrl) {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_string($value)) {
            $parsed = $eventParseDateTime($value, null);
            if (!$parsed) {
                return [];
            }

            $iso = $parsed->format('Y-m-d');
            return [[
                'month' => $parsed->format('M'),
                'day' => $parsed->format('d'),
                'url' => $eventBuildUrl($baseUrl, $iso),
            ]];
        }

        if (is_object($value)) {
            $value = (array) $value;
        }

        if (!is_array($value)) {
            return [];
        }

        $month = $value['month'] ?? $value['mon'] ?? $value['short_month'] ?? null;
        $dayValue = $value['day'] ?? $value['date_day'] ?? $value['day_of_month'] ?? null;
        $startValue = $value['date'] ?? $value['start_date'] ?? $value['start'] ?? $value['value'] ?? null;
        $startTime = $value['start_time'] ?? $value['time'] ?? null;
        $endValue = $value['end_date'] ?? $value['finish_date'] ?? null;
        $endTime = $value['end_time'] ?? null;
        $startParsed = $eventParseDateTime($startValue, $startTime);
        $tiles = [];

        if ($month && $dayValue !== null && $dayValue !== '') {
            $tileUrl = $value['url'] ?? $value['href'] ?? $eventBuildUrl($baseUrl, $startParsed ? $startParsed->format('Y-m-d') : null);
            $tiles[] = [
                'month' => (string) $month,
                'day' => str_pad((string) $dayValue, 2, '0', STR_PAD_LEFT),
                'url' => $tileUrl,
            ];
        } elseif ($startParsed) {
            $tiles[] = [
                'month' => $startParsed->format('M'),
                'day' => $startParsed->format('d'),
                'url' => $value['url'] ?? $value['href'] ?? $eventBuildUrl($baseUrl, $startParsed->format('Y-m-d')),
            ];
        }

        $endParsed = $eventParseDateTime($endValue ?: $startValue, $endTime ?: $startTime);
        if ($startParsed && $endParsed && ! $startParsed->isSameDay($endParsed)) {
            $tiles[] = [
                'month' => $endParsed->format('M'),
                'day' => $endParsed->format('d'),
                'url' => $eventBuildUrl($baseUrl, $endParsed->format('Y-m-d')),
            ];
        }

        return $tiles;
    };

    $eventStartDate = $eventSource['start_date'] ?? $eventSource['date'] ?? $product->start_date ?? $product->date ?? null;
    $eventEndDate = $eventSource['end_date'] ?? $eventSource['finish_date'] ?? $product->end_date ?? $eventStartDate ?? null;
    $eventStartTime = $eventSource['start_time'] ?? $product->start_time ?? null;
    $eventEndTime = $eventSource['end_time'] ?? $product->end_time ?? null;
    $eventRangeLabel = $eventFormatRange($eventStartDate, $eventStartTime, $eventEndDate, $eventEndTime);

    $eventRawSources = [];
    foreach (['dates', 'upcoming_dates', 'availability_dates'] as $key) {
        $value = $eventSource[$key] ?? [];
        if ($value instanceof \Illuminate\Support\Collection) {
            $value = $value->all();
        }
        if (is_array($value)) {
            foreach ($value as $item) {
                $eventRawSources[] = $item;
            }
        }
    }

    $eventTiles = [];
    foreach ($eventRawSources as $item) {
        foreach ($eventTilesFromValue($item, $url) as $tile) {
            $eventTiles[] = $tile;
        }
    }

    $eventStartParsed = $eventParseDateTime($eventStartDate, $eventStartTime);
    $eventEndParsed = $eventParseDateTime($eventEndDate, $eventEndTime);

    if (!$eventTiles) {
        if ($eventStartParsed) {
            $eventTiles[] = [
                'month' => $eventStartParsed->format('M'),
                'day' => $eventStartParsed->format('d'),
                'url' => $eventBuildUrl($url, $eventStartParsed->format('Y-m-d')),
            ];
        }
        if ($eventEndParsed && (!$eventStartParsed || !$eventStartParsed->isSameDay($eventEndParsed))) {
            $eventTiles[] = [
                'month' => $eventEndParsed->format('M'),
                'day' => $eventEndParsed->format('d'),
                'url' => $eventBuildUrl($url, $eventEndParsed->format('Y-m-d')),
            ];
        }
    } elseif ($eventStartParsed && $eventEndParsed && !$eventStartParsed->isSameDay($eventEndParsed)) {
        $eventEndIso = $eventEndParsed->format('Y-m-d');
        $eventHasEndTile = false;

        foreach ($eventTiles as $eventTile) {
            $tileUrl = (string) ($eventTile['url'] ?? '');
            if ($tileUrl !== '' && str_contains($tileUrl, $eventEndIso)) {
                $eventHasEndTile = true;
                break;
            }

            if (($eventTile['month'] ?? null) === $eventEndParsed->format('M') && ($eventTile['day'] ?? null) === $eventEndParsed->format('d')) {
                $eventHasEndTile = true;
                break;
            }
        }

        if (! $eventHasEndTile) {
            $eventTiles[] = [
                'month' => $eventEndParsed->format('M'),
                'day' => $eventEndParsed->format('d'),
                'url' => $eventBuildUrl($url, $eventEndIso),
            ];
        }
    }

    $eventTiles = array_values(array_filter($eventTiles, function ($eventTile) {
        if (!is_array($eventTile)) {
            return false;
        }

        if (!empty($eventTile['more'])) {
            return true;
        }

        $month = trim((string) ($eventTile['month'] ?? ''));
        $day = trim((string) ($eventTile['day'] ?? ''));

        return empty($eventTile['placeholder'])
            && $month !== ''
            && $month !== 'Soon'
            && $day !== ''
            && $day !== '—';
    }));

    $eventTileCount = count($eventTiles);
    if ($eventTileCount > 4) {
        $eventTiles = array_slice($eventTiles, 0, 3);
        $eventTiles[] = [
            'month' => 'More',
            'day' => '+' . max(0, $eventTileCount - 3),
            'url' => $url . '#dates',
            'more' => true,
        ];
    }

    $eventBadgeTile = ['month' => 'Soon', 'day' => '—'];
    $eventBadgeStart = $eventParseDateTime($eventStartDate, $eventStartTime);
    if ($eventBadgeStart) {
        $eventBadgeTile = [
            'month' => $eventBadgeStart->format('M'),
            'day' => $eventBadgeStart->format('d'),
        ];
    } elseif (isset($eventTiles[0]) && empty($eventTiles[0]['placeholder'])) {
        $eventBadgeTile = [
            'month' => $eventTiles[0]['month'] ?? 'Soon',
            'day' => $eventTiles[0]['day'] ?? '—',
        ];
    }

    $eventAvailabilityTitle = $eventTileCount > 1
        ? 'Fixed dates'
        : ($eventBadgeStart ? 'Fixed date' : 'Event dates');
    $eventAvailabilityNote = $eventTileCount > 1
        ? ($eventRangeLabel ?: 'Choose a date')
        : ($eventRangeLabel ?: 'View dates');

    $eventLocationLabel = $primary
        ?? ($exclusiveOnline ? 'Online' : ($hasOnline ? 'Online' : 'In-person'));
    $eventDescription = $benefitTextClean ?: 'Upcoming event details coming soon.';
    $eventPriceLabel = is_numeric($priceMin)
        ? '£' . number_format((float) $priceMin, 2)
        : '£0.00';
    $isPastEvent = (bool) data_get($product, 'is_past_event', \App\Support\EventListing::isPast($product) && \App\Support\EventListing::isEventLike($product));
    $filledStars = $reviewCount > 0 ? max(0, min(5, (int) round((float) $rating))) : 0;
    $reviewSummary = $reviewCount > 0
        ? number_format((float) $rating, 1) . ' · ' . $reviewCount . ' review' . ($reviewCount === 1 ? '' : 's')
        : 'Be the first to review';

    $vendorUser = data_get($product, 'vendor.user');
    $starterPlan = $vendorUser instanceof \App\Models\User
        ? $vendorUser->isStarterPlan()
        : strtolower(trim((string) data_get($product, 'plan_key', ''))) === 'starter';

    $provider = $starterPlan
        ? 'Wellness practitioner'
        : trim((string) (
            $product->vendor_name
            ?? data_get($product, 'vendor.vendor_name')
            ?? ''
        ));
    $providerFormatted = $provider !== ''
        ? ($starterPlan ? $provider : $ucWords($toLower(str_replace('_', ' ', $provider))))
        : null;
@endphp

<article class="wow-card md wow-event-card-v4 {{ $hasDisplayableImage ? 'is-loading' : 'is-missing' }}" aria-label="Event card {{ $product->id }}" @if($hasDisplayableImage) aria-busy="true" @endif>
    <a href="{{ $url }}" class="wow-event-card-v4__link" aria-label="View {{ $titleFormatted }}"></a>

    <div class="wow-event-card-v4__image">
        @if($hasDisplayableImage)
            <img
                src="{{ $image }}"
                alt="{{ $titleFormatted }}"
                loading="lazy"
                onload="var card=this.closest('.wow-card'); if(card){card.classList.remove('is-loading'); card.classList.add('is-image-loaded'); card.setAttribute('aria-busy','false');}"
                onerror="var card=this.closest('.wow-card'); if(card){card.classList.remove('is-loading'); card.classList.add('is-image-missing'); card.setAttribute('aria-busy','false');} this.remove();"
            >
        @endif
    </div>

    <div class="wow-event-card-v4__shade"></div>

    <div class="wow-event-card-v4__top">
        <span class="wow-event-card-v4__date">
            <span class="wow-event-card-v4__date-month">{{ $eventBadgeTile['month'] }}</span>
            <span class="wow-event-card-v4__date-day">{{ $eventBadgeTile['day'] }}</span>
        </span>

        @if($businessAcceleratorPlan)
            <div class="premium-badge-holder">
                <div class="premium-badge-drawer">
                    <div class="premium-badge-sheen"></div>

                    <div class="premium-badge-copy">
                        <p class="premium-badge-title">Premium Partner</p>
                        <span class="premium-badge-small">Business Accelerator</span>
                    </div>

                    <button
                        class="premium-badge-button"
                        type="button"
                        aria-label="Business Accelerator Premium Partner"
                        title="Premium Partner"
                        onclick="event.preventDefault(); event.stopPropagation();"
                    >
                        <img
                            src="https://studio.weofferwellness.co.uk/storage/uploads/images/78aa908f-334b-45c0-9220-1c4d84053c5e.png"
                            alt="Premium Partner rosette"
                        >
                    </button>
                </div>
            </div>
        @endif
    </div>

    <div class="wow-event-card-v4__panel">
        <div class="wow-event-card-v4__tags">
            <span class="wow-event-card-v4__tag">{{ $categoryBadgeLabel }}</span>
            <span class="wow-event-card-v4__tag wow-event-card-v4__tag--blue">{{ $typeLabel }}</span>
        </div>

        <h3 class="wow-event-card-v4__title">{{ $titleFormatted }}</h3>

        <div class="wow-event-card-v4__hidden">
            @if($providerFormatted)
                <p class="wow-event-card-v4__provider">with {{ $providerFormatted }}</p>
            @endif

            @if($reviewCount > 0)
                <div class="rating-row" aria-label="{{ 'Rated ' . number_format((float) $rating, 1) . ' out of 5' }}">
                    <span class="stars" aria-hidden="true">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="star {{ $i > $filledStars ? 'star--empty' : '' }}" style="color: {{ $i <= $filledStars ? '#f5c84b' : '#d0d5dd' }};"></span>
                        @endfor
                    </span>
                    <span>{{ $reviewSummary }}</span>
                </div>
            @endif

            <div class="wow-event-card-v4__meta">
                <span class="wow-event-card-v4__meta-line">
                    <span class="wow-event-card-v4__meta-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M12 21s7-4.4 7-11a7 7 0 1 0-14 0c0 6.6 7 11 7 11Z" stroke="currentColor" stroke-width="2"></path>
                            <path d="M12 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" stroke="currentColor" stroke-width="2"></path>
                        </svg>
                    </span>
                    {{ $eventLocationLabel }}
                </span>

                <span class="wow-event-card-v4__meta-line">
                    <span class="wow-event-card-v4__meta-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M7 3v3M17 3v3M4.5 9.25h15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                            <path d="M6.75 5h10.5C18.77 5 20 6.23 20 7.75v10.5C20 19.77 18.77 21 17.25 21H6.75C5.23 21 4 19.77 4 18.25V7.75C4 6.23 5.23 5 6.75 5Z" stroke="currentColor" stroke-width="2"></path>
                        </svg>
                    </span>
                    {{ $eventRangeLabel ?? $eventAvailabilityTitle }}
                </span>
            </div>

            <p class="wow-event-card-v4__description">{{ $eventDescription }}</p>

            <div class="wow-event-card-v4__availability">
                <div class="wow-event-card-v4__availability-head">
                    <p class="wow-event-card-v4__availability-title">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M7 3v3M17 3v3M4.5 9.25h15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                            <path d="M6.75 5h10.5C18.77 5 20 6.23 20 7.75v10.5C20 19.77 18.77 21 17.25 21H6.75C5.23 21 4 19.77 4 18.25V7.75C4 6.23 5.23 5 6.75 5Z" stroke="currentColor" stroke-width="2"></path>
                        </svg>
                        {{ $eventAvailabilityTitle }}
                    </p>

                    <p class="wow-event-card-v4__availability-note">{{ $eventAvailabilityNote }}</p>
                </div>

                <div class="wow-event-card-v4__availability-grid">
                    @foreach($eventTiles as $eventTile)
                        @if(!empty($eventTile['more']))
                            <a href="{{ $eventTile['url'] }}" class="wow-event-card-v4__date-more" aria-label="View {{ $eventTile['day'] }} more dates">
                                <span class="wow-event-card-v4__date-more-top">More</span>
                                <span class="wow-event-card-v4__date-more-number">{{ $eventTile['day'] }}</span>
                            </a>
                        @else
                            <a href="{{ $eventTile['url'] }}" class="wow-event-card-v4__date-mini" aria-label="{{ $eventTile['month'] }} {{ $eventTile['day'] }}">
                                <span class="wow-event-card-v4__date-mini-month">{{ $eventTile['month'] }}</span>
                                <span class="wow-event-card-v4__date-mini-day">{{ $eventTile['day'] }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <footer class="wow-event-card-v4__footer">
            <div>
                <span class="wow-event-card-v4__price-label">From</span>
                <span class="wow-event-card-v4__price">{{ $eventPriceLabel }}</span>
            </div>

            <div class="wow-event-card-v4__actions">
                <a href="{{ $url }}" class="wow-event-card-v4__book-btn">{{ $isPastEvent ? 'View details' : 'Book' }}</a>
            </div>
        </footer>
    </div>
</article>
