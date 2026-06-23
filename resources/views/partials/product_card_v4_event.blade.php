@php
    $eventSource = data_get($product, 'when.event', data_get($product, 'event', []));
    if (is_object($eventSource)) {
        $eventSource = (array) $eventSource;
    }
    if (!is_array($eventSource)) {
        $eventSource = [];
    }

    $toLower = function ($value) {
        return function_exists('mb_strtolower') ? mb_strtolower((string) $value, 'UTF-8') : strtolower((string) $value);
    };
    $ucWords = function ($value) {
        return function_exists('mb_convert_case') ? mb_convert_case((string) $value, MB_CASE_TITLE, 'UTF-8') : ucwords((string) $value);
    };
    $normalizeTypeLabel = function ($value) use ($toLower, $ucWords) {
        $raw = trim((string) $value);
        if ($raw === '') {
            return 'Event';
        }

        $normalized = $toLower(str_replace(['_', '-'], ' ', $raw));
        $map = [
            'events' => 'Event',
            'event' => 'Event',
            'workshops' => 'Workshop',
            'workshop' => 'Workshop',
            'classes' => 'Class',
            'class' => 'Class',
            'retreats' => 'Retreat',
            'retreat' => 'Retreat',
            'experiences' => 'Experience',
            'experience' => 'Experience',
        ];

        return $map[$normalized] ?? $ucWords($normalized);
    };

    $title = trim((string) ($product->title ?? 'Untitled'));
    $titleFormatted = $ucWords($toLower($title));
    $url = app(\App\Services\SeoStructureService::class)->canonicalProductUrl($product);
    $image = method_exists($product, 'getFirstImageUrl') ? $product->getFirstImageUrl() : '';
    $hasDisplayableImage = method_exists($product, 'hasDisplayableImage')
        ? $product->hasDisplayableImage()
        : ($image !== '' && ! str_contains((string) $image, 'no-product-image.jpg'));

    $typeCandidate = data_get($product, 'type.name')
        ?? data_get($product, 'type_label')
        ?? data_get($product, 'type_name');
    if ($typeCandidate === null) {
        $rawType = data_get($product, 'type');
        if (is_string($rawType) || is_numeric($rawType)) {
            $typeCandidate = (string) $rawType;
        }
    }
    $typeRaw = trim((string) ($typeCandidate ?? ($product->product_type ?? 'Event')));
    $typeLabel = $normalizeTypeLabel($typeRaw);

    $categoryRaw = $product->category?->name
        ?? data_get($product, 'offering.category.name')
        ?? ($product->offering_category_name ?? null)
        ?? ($product->offering_category ?? null)
        ?? ($product->category_name ?? null)
        ?? ($product->category_label ?? null)
        ?? ((is_string($product->category ?? null)) ? $product->category : null);
    if (is_array($categoryRaw)) {
        $categoryRaw = $categoryRaw['name'] ?? reset($categoryRaw) ?? null;
    }
    $categoryLabel = $categoryRaw ? $normalizeTypeLabel($categoryRaw) : null;
    $categoryBadgeLabel = $categoryLabel ?? $typeLabel;

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

    $locations = method_exists($product, 'getLocations') ? $product->getLocations() : [];
    $hasOnline = in_array('Online', $locations, true);
    $physical = array_values(array_filter($locations, fn ($location) => $location !== 'Online'));
    $physicalShort = [];
    $seenShort = [];
    foreach ($physical as $locRaw) {
        $short = trim((string) $locRaw);
        if ($short === '') {
            continue;
        }
        $key = $toLower($short);
        if (! isset($seenShort[$key])) {
            $seenShort[$key] = true;
            $physicalShort[] = $short;
        }
    }
    $matchedLocation = trim((string) ($product->matched_location_label ?? ''));
    $primary = $matchedLocation !== '' ? $matchedLocation : ($physicalShort[0] ?? null);
    $remainingCount = max(0, count($physicalShort) - ($primary ? 1 : 0));
    $exclusiveOnline = $hasOnline && count($physicalShort) === 0;

    $benefitText = $product->benefit ?? ($product->summary ?? null);
    $stripEmoji = function ($value) {
        $text = (string) ($value ?? '');
        $clean = preg_replace('/[\x{1F1E6}-\x{1F1FF}\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE0F}\x{200D}]/u', '', $text);
        if ($clean === null) {
            $clean = $text;
        }
        $clean = preg_replace('/\s{2,}/u', ' ', $clean);
        if ($clean === null) {
            $clean = $text;
        }
        return trim($clean);
    };
    $benefitTextClean = $benefitText ? $stripEmoji($benefitText) : null;

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
    $priceMin = $product->variants_min_price ?? ($product->price ?? null);
    if (is_numeric($priceMin) && $priceMin > 1000 && $priceMin % 100 === 0) {
        $priceMin = $priceMin / 100;
    }
    $compareMin = $product->variants_min_compare ?? ($product->compare_at_price ?? null);
    if (is_numeric($compareMin) && $compareMin > 1000 && $compareMin % 100 === 0) {
        $compareMin = $compareMin / 100;
    }

    $vendorReviewSummary = data_get($product, 'vendor.review_summary');
    if (! is_array($vendorReviewSummary)) {
        $vendorReviewSummary = [];
    }

    $vendorReviewCount = (int) ($vendorReviewSummary['count'] ?? 0);
    $vendorReviewRating = isset($vendorReviewSummary['rating']) ? round((float) $vendorReviewSummary['rating'], 1) : null;
    $productReviewCount = (int) ($product->reviews_count ?? 0);
    $productReviewRating = isset($product->reviews_avg_rating) ? round((float) $product->reviews_avg_rating, 1) : null;

    $reviewCount = $vendorReviewCount > 0 ? $vendorReviewCount : $productReviewCount;
    $rating = $vendorReviewCount > 0 ? $vendorReviewRating : $productReviewRating;
    if ($reviewCount > 0 && (! is_numeric($rating) || (float) $rating <= 0)) {
        $rating = 5.0;
    }
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
    $businessAcceleratorPlan = $vendorUser instanceof \App\Models\User
        ? $vendorUser->isBusinessAcceleratorPlan()
        : \Illuminate\Support\Str::slug((string) data_get($product, 'plan_key', '')) === 'business-accelerator';

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
                style="position:relative; z-index:2; width:100%; height:100%; object-fit:cover;"
                onload="var card=this.closest('.wow-card'); if(card){card.classList.remove('is-loading'); card.classList.add('is-image-loaded'); card.setAttribute('aria-busy','false');}"
                onerror="var card=this.closest('.wow-card'); if(card){card.classList.remove('is-loading'); card.classList.add('is-image-missing'); card.setAttribute('aria-busy','false');} this.remove();"
            >
        @endif
        <div
            class="wow-event-card-v4__image-fallback"
            aria-hidden="true"
            style="position:absolute; inset:0; z-index:1; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:10px; background:linear-gradient(135deg, #24364f 0%, #111827 62%, #0f172a 100%); color:rgba(255,255,255,.88); text-align:center;"
        >
            <svg width="58" height="58" viewBox="0 0 88 88" fill="none" xmlns="http://www.w3.org/2000/svg" style="opacity:.55;">
                <rect x="16" y="16" width="56" height="56" rx="6" stroke="currentColor" stroke-width="3.7" />
                <path d="m16 58 16-18 32 32" stroke="currentColor" stroke-width="3.7" stroke-linecap="round" stroke-linejoin="round" />
                <circle cx="53" cy="35" r="7" fill="currentColor" opacity=".75" />
            </svg>
            <span style="font-size:12px; font-weight:800; letter-spacing:.1em; text-transform:uppercase;">Event</span>
        </div>
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
