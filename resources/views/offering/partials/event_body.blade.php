@php
    $event = (array) ($product['event'] ?? []);
    $eventTitle = trim((string) ($product['title'] ?? 'Event'));
    $eventSummary = trim((string) ($product['summary'] ?? $product['description'] ?? ''));
    $eventBody = trim((string) ($product['body_html'] ?? ''));
    $eventWhat = trim((string) ($product['what_to_expect'] ?? ''));
    $eventIncluded = trim((string) ($product['included'] ?? ''));
    $eventFaqRaw = $product['faq'] ?? [];
    $eventFaq = is_array($eventFaqRaw) ? array_values(array_filter($eventFaqRaw)) : [];
    $eventVideoUrl = trim((string) ($product['video_url'] ?? ''));
    $eventImage = trim((string) ($product['image'] ?? ($product['images'][0] ?? '')));
    $eventImages = is_array($product['images'] ?? null) ? array_values(array_filter($product['images'])) : [];
    if ($eventImage !== '' && ! in_array($eventImage, $eventImages, true)) {
        array_unshift($eventImages, $eventImage);
    }
    $eventImages = array_values(array_unique($eventImages));
    $eventHeroImages = $eventImages;
    $eventVideoPoster = trim((string) ($eventHeroImages[0] ?? $eventImage));
    $eventMode = trim((string) ($product['mode'] ?? ''));
    $eventLocation = trim((string) ($product['location'] ?? ''));
    $eventCategory = trim((string) data_get($product, 'category.name', ''));
    $eventScheduleLater = (bool) data_get($event, 'schedule_later', false);
    $renderRichHtml = static function (string $value): string {
        return \App\Support\ContentFormatter::format($value);
    };
    $eventIncludedHtml = $renderRichHtml($eventIncluded);
    $eventWhatHtml = $renderRichHtml($eventWhat);
    $eventCapacity = (int) ($product['capacity'] ?? data_get($product, 'event.capacity', 0));
    $eventCapacity = max(0, min(1000, $eventCapacity > 0 ? $eventCapacity : 0));
    $venueLocationsRaw = $product['venue_locations'] ?? [];
    $venueLocations = is_array($venueLocationsRaw) ? array_values(array_filter($venueLocationsRaw, fn ($loc) => is_array($loc))) : [];
    $eventStartDate = trim((string) ($product['start_date'] ?? data_get($event, 'start_date', '')));
    $eventStartTime = trim((string) ($product['start_time'] ?? data_get($event, 'start_time', '')));
    $eventEndDate = trim((string) ($product['end_date'] ?? data_get($event, 'end_date', '')));
    $eventEndTime = trim((string) ($product['end_time'] ?? data_get($event, 'end_time', '')));
    $eventRangeText = '';
    $eventTimelineDays = [];
    if ($eventStartDate !== '') {
        try {
            $start = \Carbon\Carbon::parse($eventStartDate . ($eventStartTime !== '' ? ' ' . $eventStartTime : ''));
            $end = $eventEndDate !== '' ? \Carbon\Carbon::parse($eventEndDate . ($eventEndTime !== '' ? ' ' . $eventEndTime : '')) : $start->copy();
            $startLabel = $start->format('D j M' . ($eventStartTime !== '' ? ', g:i A' : ''));
            if ($end->toDateString() !== $start->toDateString()) {
                $endLabel = $end->format('D j M' . ($eventEndTime !== '' ? ', g:i A' : ''));
                $eventRangeText = $startLabel . ' – ' . $endLabel;
            } elseif ($eventEndTime !== '' && $eventEndTime !== $eventStartTime) {
                $eventRangeText = $startLabel . ' – ' . $end->format('g:i A');
            } else {
                $eventRangeText = $startLabel;
            }

            $cursor = $start->copy()->startOfDay();
            $limit = $end->copy()->startOfDay();
            $dayIndex = 1;
            while ($cursor->lte($limit) && $dayIndex <= 31) {
                $eventTimelineDays[] = [
                    'index' => $dayIndex,
                    'date' => $cursor->toDateString(),
                    'label' => $cursor->format('D j M'),
                    'time' => $start->format('g:i A') . ' – ' . $end->format('g:i A'),
                ];
                $cursor->addDay();
                $dayIndex++;
            }
        } catch (\Throwable $e) {
            $eventRangeText = trim(implode(' ', array_filter([$eventStartDate, $eventStartTime, $eventEndDate, $eventEndTime])));
        }
    }

    if (empty($eventTimelineDays)) {
        $eventTimelineDays[] = [
            'index' => 1,
            'date' => $eventStartDate !== '' ? $eventStartDate : now()->toDateString(),
            'label' => $eventRangeText !== '' ? $eventRangeText : 'Event day',
            'time' => $eventStartTime !== '' ? ($eventStartTime . ($eventEndTime !== '' ? ' – ' . $eventEndTime : '')) : '',
        ];
    }

    $eventTicketRows = [];
    foreach (array_values($product['variants'] ?? []) as $index => $variant) {
        if (!is_array($variant)) {
            continue;
        }

        $label = trim((string) ($variant['options'][0] ?? $variant['title'] ?? 'Ticket'));
        $price = is_numeric($variant['price'] ?? null) ? (float) $variant['price'] : 0.0;
        $eventTicketRows[] = [
            'index' => $index,
            'label' => $label !== '' ? $label : 'Ticket',
            'price' => $price,
            'price_formatted' => number_format($price, 2, '.', ''),
            'coverage' => $index === 0 ? ($eventRangeText !== '' ? $eventRangeText : 'Full event') : ($eventTimelineDays[$index - 1]['label'] ?? 'Single day'),
            'note' => $index === 0 || stripos($label, 'full event') !== false || stripos($label, 'event ticket') !== false ? 'Covers all event dates' : 'Single-day ticket',
        ];
    }
    if (empty($eventTicketRows)) {
        $fallbackPrice = is_numeric($product['price'] ?? null) ? (float) $product['price'] : 0.0;
        $eventTicketRows[] = [
            'index' => 0,
            'label' => 'Event ticket',
            'price' => $fallbackPrice,
            'price_formatted' => number_format($fallbackPrice, 2, '.', ''),
            'coverage' => $eventRangeText !== '' ? $eventRangeText : 'Full event',
            'note' => 'Covers all event dates',
        ];
    }

    $snapshotFacts = array_filter([
        ['label' => 'Dates', 'value' => $eventRangeText !== '' ? $eventRangeText : 'Fixed dates'],
        ['label' => 'Location', 'value' => $eventLocation !== '' ? $eventLocation : (trim((string) data_get($venueLocations[0] ?? [], 'label', '')) !== '' ? trim((string) data_get($venueLocations[0] ?? [], 'label', '')) : 'Venue details below')],
        ['label' => 'Capacity', 'value' => $eventCapacity > 0 ? ('Up to ' . $eventCapacity) : 'Capacity on request'],
        ['label' => 'Days', 'value' => count($eventTimelineDays) . (count($eventTimelineDays) === 1 ? ' day' : ' days')],
        ['label' => 'Tickets', 'value' => count($eventTicketRows) . (count($eventTicketRows) === 1 ? ' type' : ' types')],
        ['label' => 'Delivery', 'value' => 'E-ticket by email'],
        ['label' => 'Format', 'value' => $eventMode !== '' ? $eventMode : 'In-person'],
    ], fn ($item) => !empty($item['value']));

    $eventExtras = [];
    $contentHaystack = strtolower(implode(' ', array_filter([
        $eventSummary,
        $eventBody,
        $eventWhat,
        $eventIncluded,
        implode(' ', array_map(fn ($loc) => implode(' ', array_filter([
            $loc['label'] ?? '',
            $loc['address_line_1'] ?? '',
            $loc['address_line_2'] ?? '',
            $loc['city'] ?? '',
            $loc['county'] ?? '',
            $loc['postcode'] ?? '',
            $loc['notes'] ?? '',
        ])), $venueLocations)),
    ])));
    foreach ([
        'marketplace' => 'Marketplace',
        'market' => 'Marketplace',
        'exhibitor' => 'Exhibitors',
        'exhibitors' => 'Exhibitors',
        'food' => 'Food & drink',
        'drink' => 'Food & drink',
        'cafe' => 'Food & drink',
        'parking' => 'Parking',
        'car park' => 'Parking',
        'camping' => 'Camping',
        'stall' => 'Stalls',
        'stalls' => 'Stalls',
    ] as $needle => $label) {
        if (str_contains($contentHaystack, $needle) && ! in_array($label, $eventExtras, true)) {
            $eventExtras[] = $label;
        }
    }

    $guidelineBullets = array_values(array_filter([
        'Please arrive 15 to 20 minutes early so you can settle in before the first session starts.',
        $eventCapacity > 0 ? ('This event is capped at ' . $eventCapacity . ' places, so please bring your ticket confirmation with you.') : 'Please bring your ticket confirmation with you.',
        $venueLocations ? 'Check the venue and parking notes below before you travel.' : null,
        'Let us know in advance if you have accessibility needs, dietary requests, or age-related questions.',
        'Dress comfortably and check the weather if the event includes outdoor areas, stalls, or camping.',
    ]));

    $faqItems = [];
    foreach ($eventFaq as $item) {
        if (is_array($item)) {
            $question = trim((string) data_get($item, 'question', data_get($item, 'title', '')));
            $answer = trim((string) data_get($item, 'answer', data_get($item, 'value', data_get($item, 'body', ''))));
        } else {
            $question = '';
            $answer = trim((string) $item);
        }

        if ($question === '' && $answer === '') {
            continue;
        }

        if ($question === '') {
            $question = 'Event question';
        }

        $faqItems[] = [
            'question' => $question,
            'answer' => $answer,
        ];
    }

    $venuePins = array_values(array_filter(array_map(function ($loc) {
        $lat = $loc['lat'] ?? null;
        $lng = $loc['lng'] ?? null;
        if (! is_numeric($lat) || ! is_numeric($lng)) {
            return null;
        }

        return [
            'name' => trim((string) ($loc['label'] ?? 'Venue')),
            'lat' => (float) $lat,
            'lng' => (float) $lng,
            'description' => trim(implode(', ', array_filter([
                $loc['address_line_1'] ?? '',
                $loc['city'] ?? '',
                $loc['postcode'] ?? '',
            ]))),
        ];
    }, $venueLocations)));

    $eventScheduleSource = $event['schedule'] ?? [];
    if (is_object($eventScheduleSource)) {
        $eventScheduleSource = (array) $eventScheduleSource;
    }
    if (! is_array($eventScheduleSource)) {
        $eventScheduleSource = [];
    }

    $eventScheduleDays = [];
    $rawScheduleDays = $eventScheduleSource['days'] ?? [];
    if ($rawScheduleDays instanceof \Illuminate\Support\Collection) {
        $rawScheduleDays = $rawScheduleDays->all();
    }
    if (! is_array($rawScheduleDays)) {
        $rawScheduleDays = [];
    }

    foreach ($rawScheduleDays as $dayIndex => $day) {
        if (is_object($day)) {
            $day = (array) $day;
        }
        if (! is_array($day)) {
            continue;
        }

        $dayDate = trim((string) data_get($day, 'date', ''));
        $dayLabel = trim((string) data_get($day, 'label', ''));
        $rawSessions = data_get($day, 'sessions', []);
        if ($rawSessions instanceof \Illuminate\Support\Collection) {
            $rawSessions = $rawSessions->all();
        }
        if (! is_array($rawSessions)) {
            $rawSessions = [];
        }

        $sessions = [];
        foreach ($rawSessions as $sessionIndex => $session) {
            if (is_object($session)) {
                $session = (array) $session;
            }
            if (! is_array($session)) {
                continue;
            }

            $label = trim((string) data_get($session, 'label', data_get($session, 'title', '')));
            $spaceArea = trim((string) data_get($session, 'space_area', data_get($session, 'spaceArea', '')));
            $startTime = trim((string) data_get($session, 'start_time', data_get($session, 'startTime', '')));
            $endTime = trim((string) data_get($session, 'end_time', data_get($session, 'endTime', '')));
            $notes = trim((string) data_get($session, 'notes', data_get($session, 'description', '')));

            if ($label === '' && $spaceArea === '' && $startTime === '' && $endTime === '' && $notes === '') {
                continue;
            }

            $sessions[] = [
                'id' => (string) (data_get($session, 'id', '') ?: sprintf('event_schedule_%s_%d_%d', $dayDate !== '' ? $dayDate : 'day', $dayIndex + 1, $sessionIndex + 1)),
                'label' => $label !== '' ? $label : 'Session',
                'space_area' => $spaceArea,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'notes' => $notes,
            ];
        }

        $eventScheduleDays[] = [
            'id' => (string) (data_get($day, 'id', '') ?: sprintf('event_day_%s_%d', $dayDate !== '' ? $dayDate : 'day', $dayIndex + 1)),
            'date' => $dayDate,
            'label' => $dayLabel !== '' ? $dayLabel : ($dayDate !== '' ? \Carbon\Carbon::parse($dayDate)->format('D j M') : 'Day ' . ($dayIndex + 1)),
            'sessions' => $sessions,
        ];
    }

    $eventScheduleDays = array_map(static function (array $day): array {
        $sessions = array_values(array_filter($day['sessions'] ?? [], fn ($session) => is_array($session)));
        $spaceOrder = [];

        foreach ($sessions as $session) {
            $spaceArea = trim((string) ($session['space_area'] ?? ''));
            if ($spaceArea === '') {
                continue;
            }

            if (! in_array($spaceArea, $spaceOrder, true)) {
                $spaceOrder[] = $spaceArea;
            }
        }

        $hasMultipleSpaces = count($spaceOrder) > 1;
        $groupedSessions = [];
        if ($hasMultipleSpaces) {
            foreach ($sessions as $session) {
                $spaceArea = trim((string) ($session['space_area'] ?? ''));
                if ($spaceArea === '') {
                    $spaceArea = 'General';
                }

                if (! isset($groupedSessions[$spaceArea])) {
                    $groupedSessions[$spaceArea] = [];
                }

                $groupedSessions[$spaceArea][] = $session;
            }
        }

        return $day + [
            'space_order' => $spaceOrder,
            'has_multiple_spaces' => $hasMultipleSpaces,
            'grouped_sessions' => array_map(
                static fn (string $spaceArea, array $spaceSessions): array => [
                    'label' => $spaceArea,
                    'sessions' => $spaceSessions,
                ],
                array_keys($groupedSessions),
                array_values($groupedSessions)
            ),
        ];
    }, $eventScheduleDays);

    if (empty($eventScheduleDays) && ! empty($eventTimelineDays)) {
        foreach ($eventTimelineDays as $day) {
            $eventScheduleDays[] = [
                'id' => 'timeline-' . ($day['index'] ?? 1),
                'date' => $day['date'] ?? '',
                'label' => $day['label'] ?? ('Day ' . ($day['index'] ?? 1)),
                'sessions' => $eventScheduleLater
                    ? []
                    : [[
                        'id' => 'timeline-session-' . ($day['index'] ?? 1),
                        'label' => 'Festival day',
                        'start_time' => '',
                        'end_time' => '',
                        'notes' => trim((string) ($day['time'] ?? '')),
                    ]],
            ];
        }
    }

    $eventScheduleSessionCount = 0;
    foreach ($eventScheduleDays as $day) {
        $eventScheduleSessionCount += count($day['sessions'] ?? []);
    }

    $eventScheduleSummary = $eventScheduleLater && $eventScheduleSessionCount === 0
        ? 'Schedule coming soon'
        : ($eventScheduleSessionCount > 0
        ? $eventScheduleSessionCount . ' session' . ($eventScheduleSessionCount === 1 ? '' : 's') . ' across ' . count($eventScheduleDays) . ' day' . (count($eventScheduleDays) === 1 ? '' : 's')
        : ($eventScheduleDays ? count($eventScheduleDays) . ' day' . (count($eventScheduleDays) === 1 ? '' : 's') : 'Schedule coming soon'));

    $eventWatermark = trim((string) preg_replace('/\s*[–-].*$/', '', $eventTitle));
    if ($eventWatermark === '') {
        $eventWatermark = $eventTitle;
    }
    $eventWatermark = strtoupper($eventWatermark);

    $eventVenueLabel = '';
    if (! empty($venueLocations)) {
        $firstVenue = (array) ($venueLocations[0] ?? []);
        $eventVenueLabel = trim(implode(', ', array_filter([
            (string) ($firstVenue['label'] ?? ''),
            (string) ($firstVenue['city'] ?? ''),
            (string) ($firstVenue['county'] ?? ''),
        ])));
    }

    $eventLocationLabel = $eventLocation !== ''
        ? $eventLocation
        : ($eventVenueLabel !== '' ? $eventVenueLabel : ($eventMode !== '' ? $eventMode : 'In-person festival'));
    $eventTimeLabel = '';
    if ($eventStartTime !== '' || $eventEndTime !== '') {
        $eventTimeLabel = trim(($eventStartTime !== '' ? $eventStartTime : 'All day') . ($eventEndTime !== '' ? ' - ' . $eventEndTime : ''));
    } elseif ($eventScheduleSessionCount > 0) {
        $eventTimeLabel = 'See schedule below';
    }

    $eventMetaCards = array_values(array_filter([
        ['label' => 'Dates', 'value' => $eventRangeText !== '' ? $eventRangeText : 'Fixed dates'],
        ['label' => 'Time', 'value' => $eventTimeLabel !== '' ? $eventTimeLabel : 'All day'],
        ['label' => 'Format', 'value' => $eventLocationLabel],
        ['label' => 'Tickets', 'value' => count($eventTicketRows) . (count($eventTicketRows) === 1 ? ' type' : ' types')],
    ], fn ($item) => ! empty($item['value'])));
@endphp

<style>
  .wow-event-body {
    display: grid;
    gap: 22px;
  }
  .wow-event-body__hero,
  .wow-event-body__panel,
  .wow-event-body__section {
    border: 0;
    border-radius: 0;
    background: transparent;
    box-shadow: none;
  }
  .wow-event-body__hero {
    position: relative;
    overflow: hidden;
    min-height: clamp(320px, 45vw, 520px);
    padding: 0;
    border: 0;
    border-radius: 0;
    box-shadow: none;
    background: transparent;
  }
  .wow-event-body__hero-stage {
    position: absolute;
    inset: 0;
    overflow: hidden;
  }
  .wow-event-body__hero-slide {
    position: absolute;
    inset: 0;
    opacity: 0;
    transition: opacity 700ms ease;
    will-change: opacity;
  }
  .wow-event-body__hero-slide.is-active {
    opacity: 1;
  }
  .wow-event-body__hero-slide img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
    transform: scale(1.02);
  }
  .wow-event-body__hero-slide::after {
    content: "";
    position: absolute;
    inset: 0;
    background:
      linear-gradient(90deg, rgba(2, 6, 23, 0.78) 0%, rgba(2, 6, 23, 0.44) 55%, rgba(2, 6, 23, 0.10) 100%),
      linear-gradient(180deg, rgba(15, 23, 42, 0.20) 0%, rgba(15, 23, 42, 0.52) 100%);
  }
  .wow-event-body__hero-content {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    min-height: inherit;
    padding: clamp(24px, 4vw, 44px);
    gap: 12px;
    max-width: 920px;
    color: #fff;
  }
  .wow-event-body__hero-content .wow-event-body__eyebrow {
    margin-bottom: 0;
    color: #c6f3e5;
    text-shadow: 0 8px 20px rgba(15, 23, 42, 0.3);
  }
  .wow-event-body__hero-content .wow-event-body__title {
    color: #fff;
    text-shadow: 0 10px 32px rgba(15, 23, 42, 0.35);
  }
  .wow-event-body__hero-content .wow-event-body__summary {
    margin-top: 0;
    max-width: 760px;
    color: rgba(255, 255, 255, 0.92);
    text-shadow: 0 8px 22px rgba(15, 23, 42, 0.28);
  }
  .wow-event-body__eyebrow {
    color: #0f6b57;
    font-size: 12px;
    line-height: 1;
    font-weight: 900;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    margin-bottom: 10px;
  }
  .wow-event-body__title {
    margin: 0;
    color: #101624;
    font-family: Manrope, var(--bs-font-sans-serif);
    font-size: clamp(40px, 5.2vw, 72px);
    line-height: 0.98;
    font-weight: 600;
    letter-spacing: -0.05em;
  }
  .wow-event-body__summary {
    max-width: 860px;
    margin: 16px 0 0;
    color: #475467;
    font-size: 17px;
    line-height: 1.65;
  }
  .wow-event-body__badges,
  .wow-event-body__tags,
  .wow-event-body__extras {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }
  .wow-event-body__badges {
    margin-top: 18px;
  }
  .wow-event-body__badge,
  .wow-event-body__tag,
  .wow-event-body__extra {
    display: inline-flex;
    align-items: center;
    min-height: 34px;
    padding: 8px 12px;
    border-radius: 999px;
    border: 1px solid rgba(15, 107, 87, 0.14);
    background: #f2fffa;
    color: #0f6b57;
    font-size: 13px;
    line-height: 1;
    font-weight: 800;
  }
  .wow-event-body__badge--soft {
    background: #f7faf9;
    color: #334155;
    border-color: #e5e7eb;
  }
  .wow-event-body__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 18px;
  }
  .wow-event-body__action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    padding: 11px 16px;
    border-radius: 12px;
    text-decoration: none;
    font-size: 14px;
    line-height: 1;
    font-weight: 800;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .wow-event-body__action:hover {
    transform: translateY(-1px);
  }
  .wow-event-body__action--primary {
    background: #549483;
    color: #fff;
    box-shadow: 0 10px 24px rgba(84, 148, 131, 0.22);
  }
  .wow-event-body__action--secondary {
    background: #fff;
    color: #101624;
    border: 1px solid #d7dde5;
  }
  .wow-event-body__media-section {
    display: grid;
    gap: 14px;
  }
  .wow-event-body__media-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    align-items: start;
  }
  .wow-event-body__media-item,
  .wow-event-body__media,
  .wow-event-body__video {
    position: relative;
    overflow: hidden;
    border-radius: 0;
    background: #0f172a;
    height: auto;
    min-height: 0;
    max-height: none;
    box-shadow: none;
  }
  .wow-event-body__panel {
    padding: 0;
  }
  .wow-event-body__media-item.wow-event-body__panel {
    background: none;
  }
  .wow-event-body__media-item,
  .wow-event-body__media-item video,
  .wow-event-body__media-item img,
  .wow-event-body__video video,
  .wow-event-body__video img {
    width: 100%;
    height: auto;
    max-height: 364px;
    display: block;
    object-fit: contain;
  }
  .wow-event-body__media-item--video {
    cursor: pointer;
  }
  .wow-event-body__media-item--video video {
    object-fit: cover !important;
  }
  .wow-event-body__section {
    padding: 20px;
  }
  .wow-event-body__section-title {
    margin: 0 0 12px;
    color: #101624;
    font-family: Manrope, var(--bs-font-sans-serif);
    font-size: 22px;
    line-height: 1.1;
    font-weight: 700;
    letter-spacing: -0.04em;
  }
  .wow-event-body__section-lead {
    margin: 0 0 16px;
    color: #667085;
    font-size: 14px;
    line-height: 1.6;
  }
  .wow-event-body__snapshot-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
  }
  .wow-event-body__snapshot-card {
    padding: 16px;
    border-radius: 18px;
    border: 1px solid #e5e7eb;
    background: linear-gradient(180deg, #ffffff 0%, #fbfcfc 100%);
  }
  .wow-event-body__snapshot-label {
    margin: 0 0 8px;
    color: #98a2b3;
    font-size: 10px;
    line-height: 1;
    font-weight: 900;
    letter-spacing: 0.12em;
    text-transform: uppercase;
  }
  .wow-event-body__snapshot-value {
    margin: 0;
    color: #101624;
    font-size: 15px;
    line-height: 1.45;
    font-weight: 800;
  }
  .wow-event-body__ticket-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
  }
  .wow-event-body__ticket-card {
    display: grid;
    gap: 14px;
    align-content: start;
    padding: 18px;
    border-radius: 18px;
    border: 1px solid #dce3ea;
    background: #fff;
    text-decoration: none;
    color: inherit;
    transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
  }
  .wow-event-body__ticket-card:hover {
    transform: translateY(-2px);
    border-color: rgba(15, 107, 87, 0.24);
    box-shadow: 0 16px 36px rgba(16, 24, 40, 0.08);
  }
  .wow-event-body__ticket-price {
    color: #101624;
    font-size: 24px;
    line-height: 1;
    font-weight: 900;
    letter-spacing: -0.05em;
  }
  .wow-event-body__calendar-band {
    display: grid;
    gap: 10px;
  }
  .wow-event-body__calendar-range {
    display: inline-flex;
    align-items: center;
    min-height: 40px;
    width: fit-content;
    padding: 10px 14px;
    border-radius: 999px;
    background: #eefaf4;
    color: #0f6b57;
    font-size: 14px;
    line-height: 1;
    font-weight: 900;
  }
  .wow-event-body__days {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }
  .wow-event-body__day {
    min-width: 128px;
    padding: 14px 15px;
    border-radius: 18px;
    border: 1px solid #dce3ea;
    background: #fff;
    box-shadow: 0 8px 22px rgba(16, 24, 40, 0.05);
  }
  .wow-event-body__day-index {
    margin: 0 0 8px;
    color: #0f6b57;
    font-size: 10px;
    line-height: 1;
    font-weight: 900;
    letter-spacing: 0.14em;
    text-transform: uppercase;
  }
  .wow-event-body__day-label {
    margin: 0;
    color: #101624;
    font-size: 16px;
    line-height: 1.25;
    font-weight: 800;
  }
  .wow-event-body__day-time {
    margin: 6px 0 0;
    color: #667085;
    font-size: 13px;
    line-height: 1.45;
    font-weight: 600;
  }
  .wow-event-body__content-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
  }
  .wow-event-body__rich {
    color: #101624;
    font-size: 15px;
    line-height: 1.75;
  }
  .wow-event-body__rich p {
    margin: 0 0 10px;
  }
  .wow-event-body__rich p:last-child {
    margin-bottom: 0;
  }
  .wow-event-body__venue-grid {
    display: grid;
    grid-template-columns: minmax(0, 0.55fr) minmax(0, 1.05fr);
    gap: 16px;
  }
  .wow-event-body__venue-list {
    display: grid;
    gap: 12px;
  }
  .wow-event-body__venue-card {
    padding: 16px;
    border-radius: 18px;
    border: 1px solid #dce3ea;
    background: linear-gradient(180deg, #ffffff 0%, #fbfcfc 100%);
  }
  .wow-event-body__venue-name {
    margin: 0 0 8px;
    color: #101624;
    font-size: 16px;
    line-height: 1.25;
    font-weight: 800;
  }
  .wow-event-body__venue-lines {
    color: #475467;
    font-size: 14px;
    line-height: 1.6;
  }
  .wow-event-body__venue-notes {
    margin-top: 10px;
    color: #0f6b57;
    font-size: 13px;
    line-height: 1.55;
    font-weight: 700;
  }
  .wow-event-body__map {
    position: relative;
    min-height: 420px;
    border-radius: 24px;
    overflow: hidden;
    border: 1px solid #dce3ea;
    background: linear-gradient(180deg, #e9f3ef 0%, #edf2f7 100%);
  }
  .wow-event-body__map-placeholder {
    position: absolute;
    inset: 0;
    display: grid;
    place-items: center;
    padding: 24px;
    color: #667085;
    font-size: 14px;
    line-height: 1.5;
    font-weight: 700;
    text-align: center;
  }
  .wow-event-body__guidelines details {
    border: 1px solid #dce3ea;
    border-radius: 18px;
    background: #fff;
    padding: 14px 16px;
  }
  .wow-event-body__guidelines summary {
    cursor: pointer;
    list-style: none;
    font-size: 16px;
    line-height: 1.35;
    font-weight: 800;
    color: #101624;
  }
  .wow-event-body__guidelines summary::-webkit-details-marker {
    display: none;
  }
  .wow-event-body__guidelines-list {
    margin: 12px 0 0;
    padding-left: 18px;
    color: #101624;
    font-size: 14px;
    line-height: 1.75;
  }
  .wow-event-body__faq-list {
    display: grid;
    gap: 10px;
  }
  .wow-event-body__faq-item {
    border: 1px solid #dce3ea;
    border-radius: 18px;
    background: #fff;
    padding: 14px 16px;
  }
  .wow-event-body__faq-question {
    margin: 0;
    color: #101624;
    font-size: 15px;
    line-height: 1.35;
    font-weight: 800;
  }
  .wow-event-body__faq-answer {
    margin: 8px 0 0;
    color: #475467;
    font-size: 14px;
    line-height: 1.7;
  }
  .wow-event-body__related-grid {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }
  .wow-event-body__related-link {
    display: inline-flex;
    align-items: center;
    min-height: 40px;
    padding: 10px 14px;
    border-radius: 999px;
    border: 1px solid #d7dde5;
    background: #fff;
    color: #101624;
    text-decoration: none;
    font-size: 14px;
    line-height: 1;
    font-weight: 800;
  }

  /* Festival template overrides */
  .wow-event-body {
    position: relative;
    gap: 18px;
    font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    color: #0d1b2a;
  }
  .wow-event-body__hero,
  .wow-event-body__panel,
  .wow-event-body__section,
  .wow-event-body__snapshot-card,
  .wow-event-body__venue-card,
  .wow-event-body__faq-item,
  .wow-event-body__guidelines details,
  .wow-event-body__related-link,
  .wow-event-body__action,
  .wow-event-body__schedule-tab {
    border-radius: 1px;
  }
  .wow-event-body__hero,
  .wow-event-body__panel,
  .wow-event-body__section {
    border: 1px solid rgba(2, 6, 23, .10);
    background: #ffffff;
    box-shadow: 0 10px 28px rgba(7, 29, 51, 0.08);
  }
  .wow-event-body__hero {
    min-height: 630px;
    overflow: hidden;
    background: #071d33;
  }
  .wow-event-body__hero-stage {
    inset: 0;
  }
  .wow-event-body__hero-slide::after {
    background:
      linear-gradient(90deg, rgba(7, 29, 51, 0.80) 0%, rgba(7, 29, 51, 0.52) 55%, rgba(7, 29, 51, 0.08) 100%),
      linear-gradient(180deg, rgba(7, 29, 51, 0.12) 0%, rgba(7, 29, 51, 0.60) 100%);
  }
  .wow-event-body__hero-content {
    display: grid;
    align-content: end;
    gap: 14px;
    min-height: inherit;
    max-width: 760px;
    padding: clamp(24px, 4vw, 42px);
  }
  .wow-event-body__watermark {
    position: absolute;
    right: 20px;
    top: 34px;
    z-index: 2;
    font-size: clamp(78px, 13vw, 168px);
    line-height: 0.82;
    font-weight: 400;
    letter-spacing: -0.09em;
    color: rgba(255, 255, 255, 0.08);
    text-transform: uppercase;
    pointer-events: none;
  }
  .wow-event-body__kicker-row,
  .wow-event-body__badges,
  .wow-event-body__tags,
  .wow-event-body__extras,
  .wow-event-body__hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }
  .wow-event-body__kicker-row {
    margin-bottom: 2px;
  }
  .wow-event-body__pill,
  .wow-event-body__badge,
  .wow-event-body__tag,
  .wow-event-body__extra {
    display: inline-flex;
    align-items: center;
    min-height: 34px;
    padding: 8px 10px;
    border-radius: 1px;
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.22);
    color: #eef9ff;
    font-size: 13px;
    line-height: 1;
    font-weight: 400;
  }
  .wow-event-body__hero-content .wow-event-body__eyebrow {
    margin: 0;
    color: #c8f0e6;
    letter-spacing: 0.12em;
  }
  .wow-event-body__hero-content .wow-event-body__title,
  .wow-event-body__hero-content .wow-event-body__summary {
    color: #ffffff;
  }
  .wow-event-body__hero-content .wow-event-body__summary {
    max-width: 650px;
    margin-top: 0;
    color: rgba(255, 255, 255, 0.86);
  }
  .wow-event-body__hero-actions {
    margin-top: 12px;
  }
  .wow-event-body__action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 50px;
    padding: 0 18px;
    border: 1px solid transparent;
    background: #478ee4;
    color: #ffffff;
    text-decoration: none;
    font-size: 15px;
    font-weight: 400;
    transition: transform 0.18s ease, background 0.18s ease, border-color 0.18s ease;
  }
  .wow-event-body__action:hover {
    transform: translateY(-1px);
  }
  .wow-event-body__action--secondary {
    background: rgba(255, 255, 255, 0.12);
    border-color: rgba(255, 255, 255, 0.28);
  }
  .wow-event-body__hero-meta {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
    margin-top: 18px;
    max-width: 760px;
  }
  .wow-event-body__meta-card {
    min-height: 92px;
    padding: 15px;
    border-radius: 1px;
    background: rgba(255, 255, 255, 0.11);
    border: 1px solid rgba(255, 255, 255, 0.18);
    color: #ffffff;
  }
  .wow-event-body__meta-card small {
    display: block;
    margin-bottom: 6px;
    color: rgba(255, 255, 255, 0.66);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.09em;
    font-weight: 400;
  }
  .wow-event-body__meta-card strong {
    display: block;
    font-size: 15px;
    line-height: 1.25;
    font-weight: 400;
  }
  .wow-event-body__section {
    padding: 34px;
    margin-bottom: 0;
    background: #ffffff;
  }
  .wow-event-body__section-title {
    margin: 0 0 12px;
    color: #071d33;
    font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    font-size: clamp(26px, 3vw, 40px);
    line-height: 0.98;
    font-weight: 400;
    letter-spacing: -0.055em;
  }
  .wow-event-body__section-lead {
    max-width: 620px;
    margin: 0 0 16px;
    color: #637486;
    font-size: 16px;
    line-height: 1.65;
    font-weight: 400;
  }
  .wow-event-body__snapshot-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
  .wow-event-body__snapshot-card {
    min-height: 170px;
    padding: 22px;
    border-radius: 1px;
    background: #f3f9fd;
    border: 1px solid rgba(2, 6, 23, .10);
    box-shadow: none;
  }
  .wow-event-body__snapshot-label {
    color: #478ee4;
  }
  .wow-event-body__snapshot-value {
    font-size: 15px;
    line-height: 1.45;
    font-weight: 400;
  }
  .wow-event-body__schedule-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 20px;
  }
  .wow-event-body__schedule-tab {
    border: 1px solid rgba(13, 27, 42, .16);
    background: #ffffff;
    color: #071d33;
    padding: 10px 12px;
    cursor: pointer;
    font-weight: 400;
  }
  .wow-event-body__schedule-tab.is-active {
    background: #478ee4;
    color: #ffffff;
    border-color: #478ee4;
  }
  .wow-event-body__schedule-panel {
    display: grid;
    gap: 10px;
  }
  .wow-event-body__schedule-panel[hidden] {
    display: none !important;
  }
  .wow-event-body__timeline-item {
    display: grid;
    grid-template-columns: 110px minmax(0, 1fr);
    gap: 18px;
    padding: 18px;
    border-radius: 1px;
    background: #ffffff;
    border: 1px solid rgba(2, 6, 23, .10);
  }
  .wow-event-body__timeline-time {
    color: #478ee4;
    font-size: 14px;
    font-weight: 400;
  }
  .wow-event-body__timeline-item h3 {
    margin: 0 0 5px;
    color: #071d33;
    font-size: 17px;
    letter-spacing: -0.02em;
    font-weight: 400;
  }
  .wow-event-body__timeline-space {
    margin: 0 0 8px;
    color: #0f6b57;
    font-size: 12px;
    line-height: 1.35;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }
  .wow-event-body__timeline-item p {
    margin: 0;
    color: #637486;
    font-size: 14px;
    line-height: 1.55;
    font-weight: 400;
  }
  .wow-room-carousel {
    display: grid;
    grid-template-columns: 38px minmax(0, 1fr) 38px;
    gap: 8px;
    align-items: start;
  }
  .wow-room-carousel__viewport {
    min-width: 0;
    overflow-x: auto;
    overflow-y: hidden;
    scroll-behavior: smooth;
    scroll-snap-type: x mandatory;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
  }
  .wow-room-carousel__viewport::-webkit-scrollbar {
    display: none;
  }
  .wow-room-grid {
    display: flex;
    flex-wrap: nowrap;
    gap: 12px;
    align-items: stretch;
    min-width: 100%;
  }
  .wow-room {
    flex: 0 0 calc((100% - 12px) / 2);
    min-width: 0;
    overflow: hidden;
    border: 1px solid rgba(2, 6, 23, .10);
    border-radius: 1px;
    background: #f8fbff;
    scroll-snap-align: start;
  }
  .wow-room-grid.has-one-room .wow-room {
    flex-basis: 100%;
  }
  .wow-room-grid.has-two-rooms .wow-room {
    flex-basis: calc((100% - 12px) / 2);
  }
  .wow-room--tone-1 {
    background: #f5f9ff;
    border-color: #cfe2f7;
  }
  .wow-room--tone-2 {
    background: #f6fbf8;
    border-color: #cfe7d8;
  }
  .wow-room--tone-3 {
    background: #faf8ff;
    border-color: #ddd3f5;
  }
  .wow-room--tone-4 {
    background: #fffaf0;
    border-color: #ead9aa;
  }
  .wow-room__header {
    min-height: 88px;
    border-bottom: 1px solid rgba(2, 6, 23, .10);
    background: #f3f9fd;
    padding: 14px;
  }
  .wow-room--tone-1 .wow-room__header {
    background: #eef6ff;
    border-bottom-color: #cfe2f7;
  }
  .wow-room--tone-2 .wow-room__header {
    background: #effaf3;
    border-bottom-color: #cfe7d8;
  }
  .wow-room--tone-3 .wow-room__header {
    background: #f5f1ff;
    border-bottom-color: #ddd3f5;
  }
  .wow-room--tone-4 .wow-room__header {
    background: #fff5dc;
    border-bottom-color: #ead9aa;
  }
  .wow-room__name {
    margin: 0;
    color: #071d33;
    font-size: 18px;
    line-height: 1.15;
    letter-spacing: -0.025em;
    font-weight: 400;
  }
  .wow-room__note {
    margin: 6px 0 0;
    color: #637486;
    font-size: 13px;
    line-height: 1.3;
    font-weight: 400;
  }
  .wow-room__slots {
    display: grid;
    grid-auto-rows: 292px;
    gap: 12px;
    padding: 12px;
    background: transparent;
  }
  .wow-room-arrow {
    width: 38px;
    height: 54px;
    appearance: none;
    border: 1px solid rgba(13, 27, 42, .16);
    border-radius: 1px;
    background: #ffffff;
    color: #478ee4;
    font: inherit;
    font-size: 28px;
    line-height: 1;
    font-weight: 300;
    cursor: pointer;
    box-shadow: 0 10px 22px rgba(7, 27, 54, 0.08);
    transition: background 0.16s ease, color 0.16s ease, border-color 0.16s ease, opacity 0.16s ease;
  }
  .wow-room-arrow:hover:not(:disabled) {
    border-color: #478ee4;
    background: #478ee4;
    color: #ffffff;
  }
  .wow-room-arrow:disabled {
    opacity: 0.32;
    cursor: not-allowed;
    box-shadow: none;
  }
  .wow-room-carousel.is-not-scrollable .wow-room-arrow,
  .wow-room-carousel.is-not-scrollable .wow-room-progress {
    display: none;
  }
  .wow-room-carousel.is-not-scrollable {
    grid-template-columns: minmax(0, 1fr);
  }
  .wow-room-carousel.is-not-scrollable .wow-room-carousel__viewport {
    grid-column: 1;
  }
  .wow-room-progress {
    grid-column: 2;
    display: flex;
    justify-content: center;
    gap: 6px;
    margin-top: 12px;
  }
  .wow-room-progress__dot {
    width: 8px;
    height: 8px;
    border: 1px solid rgba(13, 27, 42, .16);
    border-radius: 999px;
    background: #ffffff;
  }
  .wow-room-progress__dot.is-active {
    border-color: #478ee4;
    background: #478ee4;
  }
  .wow-session {
    height: 100%;
    display: flex;
    flex-direction: column;
    border: 1px solid rgba(2, 6, 23, .10);
    border-radius: 1px;
    background: rgba(255, 255, 255, 0.82);
    padding: 16px;
    overflow: hidden;
  }
  .wow-room--tone-1 .wow-session {
    border-color: #cfe2f7;
    background: rgba(255, 255, 255, 0.74);
  }
  .wow-room--tone-2 .wow-session {
    border-color: #cfe7d8;
    background: rgba(255, 255, 255, 0.74);
  }
  .wow-room--tone-3 .wow-session {
    border-color: #ddd3f5;
    background: rgba(255, 255, 255, 0.74);
  }
  .wow-room--tone-4 .wow-session {
    border-color: #ead9aa;
    background: rgba(255, 255, 255, 0.74);
  }
  .wow-session__time {
    display: block;
    margin: 0 0 12px;
    color: #478ee4;
    font-size: 15px;
    line-height: 1.2;
    font-weight: 400;
    flex: 0 0 auto;
  }
  .wow-session__title {
    margin: 0;
    color: #071d33;
    font-size: 20px;
    line-height: 1.18;
    letter-spacing: -0.05em;
    font-weight: 400;
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  .wow-session__desc {
    margin: 10px 0 0;
    color: #637486;
    font-size: 16px;
    line-height: 1.45;
    font-weight: 400;
    display: -webkit-box;
    -webkit-line-clamp: 5;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  .wow-session__host {
    margin: auto 0 0;
    padding-top: 12px;
    color: #637486;
    font-size: 15px;
    line-height: 1.35;
    font-weight: 400;
    flex: 0 0 auto;
  }
  .wow-session__host strong {
    color: #071d33;
    font-weight: 500;
  }
  .wow-room__empty {
    height: 292px;
    display: flex;
    align-items: center;
    border: 1px solid rgba(2, 6, 23, .10);
    border-radius: 1px;
    background: rgba(255, 255, 255, 0.74);
    padding: 16px;
    color: #637486;
    font-size: 15px;
    line-height: 1.4;
  }
  .wow-event-body__venue-grid {
    grid-template-columns: minmax(0, 0.55fr) minmax(0, 1.05fr);
  }
  .wow-event-body__venue-card {
    border-radius: 1px;
    background: linear-gradient(180deg, #ffffff 0%, #fbfcfc 100%);
    border: 1px solid rgba(2, 6, 23, .10);
  }
  .wow-event-body__venue-name {
    color: #071d33;
    font-size: 16px;
    font-weight: 400;
  }
  .wow-event-body__venue-lines {
    color: #475467;
  }
  .wow-event-body__venue-notes {
    color: #0f6b57;
  }
  .wow-event-body__map {
    min-height: 420px;
    border-radius: 1px;
    border: 1px solid rgba(2, 6, 23, .10);
  }
  .wow-event-body__faq-item,
  .wow-event-body__guidelines details {
    border: 1px solid rgba(2, 6, 23, .10);
    border-radius: 1px;
    background: #ffffff;
  }
  .wow-event-body__faq-question,
  .wow-event-body__guidelines summary {
    color: #071d33;
    font-size: 15px;
    font-weight: 400;
  }
  .wow-event-body__faq-answer,
  .wow-event-body__rich,
  .wow-event-body__guidelines-list {
    color: #071d33;
    font-size: 15px;
    line-height: 1.75;
  }
  .wow-event-body__related-link {
    border-radius: 1px;
    border: 1px solid rgba(13, 27, 42, .16);
    background: #ffffff;
    color: #071d33;
    font-weight: 400;
  }
  .wow-event-body__calendar-range {
    border-radius: 1px;
  }

  .wow-event-body__mobile-map-hero {
    display: none;
  }
  .wow-event-body__nav {
    max-width: 1180px;
    margin: 20px auto 0;
    padding: 0 20px;
  }
  .wow-event-body__nav-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 10px;
    border: 1px solid rgba(2, 6, 23, .10);
    background: #ffffff;
    border-radius: 1px;
    box-shadow: 0 10px 28px rgba(7, 29, 51, 0.08);
  }
  .wow-event-body__breadcrumbs {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
    font-size: 13px;
    color: #637486;
    white-space: nowrap;
    overflow: auto;
  }
  .wow-event-body__crumb {
    display: inline-flex;
    align-items: center;
    padding: 7px 10px;
    background: #f3f9fd;
    border: 1px solid #d9e4ec;
    border-radius: 1px;
    color: #0b2b4a;
    font-weight: 400;
    text-decoration: none;
  }
  .wow-event-body__crumb-current {
    background: #e7f5f3;
    color: #1f6f68;
    border-color: #cce8e4;
  }
  .wow-event-body__nav-badges {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
  }
  .wow-event-body__nav-badge {
    padding: 7px 10px;
    border-radius: 1px;
    background: #ffffff;
    border: 1px solid #d9e4ec;
    color: #0b2b4a;
    font-size: 13px;
    font-weight: 400;
  }
  .wow-event-body__nav-badge--green {
    background: #e7f5f3;
    color: #1f6f68;
    border-color: #cce8e4;
  }
  .wow-event-body__hero-content {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 380px;
    gap: 28px;
    max-width: none;
    min-height: inherit;
    align-content: end;
    padding: clamp(24px, 4vw, 42px);
  }
  .wow-event-body__hero-main {
    display: grid;
    gap: 14px;
  }
  .wow-event-body__hero-side {
    align-self: end;
  }
  .wow-event-body__content-wrap {
    max-width: 1180px;
    margin: 34px auto 100px;
    padding: 0 20px;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 380px;
    gap: 28px;
    align-items: start;
  }
  .wow-event-body__content-main {
    min-width: 0;
  }
  .wow-event-body__gallery {
    display: grid;
    grid-template-columns: 1.3fr 0.7fr;
    gap: 12px;
  }
  .wow-event-body__gallery-main,
  .wow-event-body__gallery-side,
  .wow-event-body__gallery-tile {
    position: relative;
    overflow: hidden;
    border-radius: 1px;
    background: #dceef7;
  }
  .wow-event-body__gallery-main {
    min-height: 430px;
  }
  .wow-event-body__gallery-side {
    display: grid;
    gap: 12px;
    background: transparent;
  }
  .wow-event-body__gallery-side .wow-event-body__gallery-tile {
    min-height: 209px;
  }
  .wow-event-body__gallery img {
    height: 100%;
    object-fit: cover;
    transition: transform 0.35s ease;
  }
  .wow-event-body__gallery-main:hover img,
  .wow-event-body__gallery-tile:hover img {
    transform: scale(1.035);
  }
  .wow-event-body__side-stack {
    position: sticky;
    top: 22px;
    display: grid;
    gap: 16px;
    align-self: start;
  }
  .wow-event-body__side-card {
    padding: 22px;
    border-radius: 1px;
    background: #ffffff;
    border: 1px solid rgba(2, 6, 23, .10);
    box-shadow: 0 10px 28px rgba(7, 29, 51, 0.08);
  }
  .wow-event-body__side-map-card {
    padding: 0;
    overflow: hidden;
  }
  .wow-event-body__side-map-box {
    height: 300px;
    border-bottom: 1px solid rgba(2, 6, 23, .10);
    background: #eaf3f8;
    position: relative;
  }
  .wow-event-body__side-map-body {
    padding: 18px;
  }
  .wow-event-body__mini-list {
    display: grid;
    gap: 12px;
  }
  .wow-event-body__mini-row {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid rgba(2, 6, 23, .10);
    color: #637486;
    font-size: 14px;
    font-weight: 400;
  }
  .wow-event-body__mini-row:last-child {
    border-bottom: 0;
    padding-bottom: 0;
  }
  .wow-event-body__mini-row strong {
    color: #071d33;
    text-align: right;
  }
  .wow-event-body__section {
    margin-bottom: 22px;
  }
  .wow-event-body__section--flat {
    padding: 0;
    border: 0;
    background: transparent;
    box-shadow: none;
  }
  .wow-event-body__venue-card {
    padding: 24px;
    border-radius: 1px;
    background: #071d33;
    color: #ffffff;
    min-height: 270px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }
  .wow-event-body__venue-card p {
    margin: 0;
    color: rgba(255, 255, 255, 0.72);
    line-height: 1.6;
    font-weight: 400;
  }

  @media (max-width: 991px) {
    .wow-event-body__venue-grid,
    .wow-event-body__content-grid {
      grid-template-columns: 1fr;
    }
    .wow-event-body__snapshot-grid,
    .wow-event-body__ticket-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .wow-event-body__hero-meta {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .wow-event-body__hero-content,
    .wow-event-body__content-wrap {
      grid-template-columns: 1fr;
    }
    .wow-event-body__content-wrap {
      display: block;
    }
  }
  @media (max-width: 991px) {
    .wow-event-body__media-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .wow-event-body__side-stack {
      position: relative;
      top: auto;
      display: block;
      margin-top: 16px;
    }
  }
  @media (max-width: 575px) {
    .wow-event-body__hero,
    .wow-event-body__panel,
    .wow-event-body__section {
      border-radius: 1px;
    }
    .wow-event-body__hero,
    .wow-event-body__panel,
    .wow-event-body__section {
      padding: 20px;
    }
    .wow-event-body__title {
      font-size: 34px;
    }
    .wow-event-body__watermark {
      display: none;
    }
    .wow-event-body__hero-actions {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .wow-event-body__hero-actions .wow-event-body__action {
      width: 100%;
    }
    .wow-event-body__hero-meta,
    .wow-event-body__snapshot-grid,
    .wow-event-body__ticket-grid {
      grid-template-columns: 1fr;
    }
    .wow-event-body__snapshot-grid,
    .wow-event-body__ticket-grid {
      grid-template-columns: 1fr;
    }
    .wow-event-body__media-grid {
      grid-template-columns: 1fr;
    }
    .wow-event-body__nav {
      display: none;
    }
    .wow-event-body__mobile-map-hero {
      display: block;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 48vh;
      min-height: 340px;
      max-height: 430px;
      z-index: 0;
      background: #dceaf3;
      overflow: hidden;
      pointer-events: none;
    }
    .wow-event-body__mobile-map-canvas {
      position: absolute;
      inset: 0;
      pointer-events: none;
    }
    .wow-event-body__mobile-map-shade {
      position: absolute;
      inset: 0;
      background: rgba(247, 251, 253, 0.08);
      pointer-events: none;
    }
    .wow-event-body__mobile-map-shade::after {
      content: "";
      position: absolute;
      left: 0;
      right: 0;
      bottom: 0;
      height: 120px;
      background: linear-gradient(to bottom, rgba(247, 251, 253, 0), rgba(247, 251, 253, 0.94));
    }
    .wow-event-body {
      position: relative;
      z-index: 1;
      padding-top: calc(48vh - 98px);
    }
    .wow-event-body__hero-content {
      padding: 0;
    }
    .wow-event-body__gallery {
      grid-template-columns: 1fr;
    }
    .wow-event-body__gallery-main {
      min-height: 330px;
    }
    .wow-event-body__gallery-side .wow-event-body__gallery-tile {
      min-height: 230px;
    }
  }
</style>

<div class="wow-event-body__mobile-map-hero" aria-hidden="true">
  <div class="wow-event-body__mobile-map-canvas" id="mobileMap"></div>
  <div class="wow-event-body__mobile-map-shade"></div>
</div>

<nav class="wow-event-body__nav" aria-label="Event navigation">
  <div class="wow-event-body__nav-inner">
    <div class="wow-event-body__breadcrumbs">
      <a class="wow-event-body__crumb" href="{{ url('/') }}">Home</a>
      <a class="wow-event-body__crumb" href="{{ url('/events') }}">Events</a>
      <span class="wow-event-body__crumb wow-event-body__crumb-current">{{ $eventTitle }}</span>
    </div>

    <div class="wow-event-body__nav-badges">
      @if($eventRangeText !== '')
        <span class="wow-event-body__nav-badge">{{ $eventRangeText }}</span>
      @endif
      <span class="wow-event-body__nav-badge wow-event-body__nav-badge--green">{{ $eventLocationLabel }}</span>
      @if($eventScheduleSummary !== '')
        <span class="wow-event-body__nav-badge">{{ $eventScheduleSummary }}</span>
      @endif
    </div>
  </div>
</nav>

<section class="wow-event-body" aria-label="Event details">
  <header class="wow-event-body__hero" data-wow-event-hero>
    @if(!empty($eventHeroImages))
      <div class="wow-event-body__hero-stage" aria-hidden="true">
        @foreach($eventHeroImages as $heroIndex => $heroImage)
          <div class="wow-event-body__hero-slide{{ $loop->first ? ' is-active' : '' }}" data-wow-event-hero-slide>
            <img
              src="{{ $heroImage }}"
              alt=""
              loading="{{ $loop->first ? 'eager' : 'lazy' }}"
              decoding="async"
            >
          </div>
        @endforeach
      </div>
    @endif

    @if($eventWatermark !== '')
      <div class="wow-event-body__watermark" aria-hidden="true">{!! nl2br(e($eventWatermark)) !!}</div>
    @endif

    <div class="wow-event-body__hero-content">
      <div class="wow-event-body__hero-main">
        <div class="wow-event-body__kicker-row">
          @if($eventRangeText !== '')
            <span class="wow-event-body__pill">{{ $eventRangeText }}</span>
          @endif
          <span class="wow-event-body__pill">{{ $eventLocationLabel }}</span>
          @if($eventScheduleSummary !== '')
            <span class="wow-event-body__pill">{{ $eventScheduleSummary }}</span>
          @endif
        </div>

        <div class="wow-event-body__eyebrow">Event / Festival</div>
        <h1 class="wow-event-body__title">{{ $eventTitle }}</h1>
        @if($eventSummary !== '')
          <p class="wow-event-body__summary">{{ $eventSummary }}</p>
        @endif

        <div class="wow-event-body__hero-actions">
          @if($showBookingUi ?? true)
            <a class="wow-event-body__action wow-event-body__action--primary" href="#tickets">Book tickets</a>
          @endif
          <a class="wow-event-body__action wow-event-body__action--secondary" href="#wowEventTimeline">View schedule</a>
        </div>

        @if(! empty($eventMetaCards))
          <div class="wow-event-body__hero-meta" aria-label="Festival highlights">
            @foreach($eventMetaCards as $fact)
              <div class="wow-event-body__meta-card">
                <small>{{ $fact['label'] }}</small>
                <strong>{{ $fact['value'] }}</strong>
              </div>
            @endforeach
          </div>
        @endif
      </div>

      <div class="wow-event-body__hero-side">
        @if($showBookingUi ?? true)
          @include('offering.partials.event_buybox')
        @else
          <div class="card border-0 shadow-sm p-3">
            <div class="fw-semibold">This event has ended.</div>
            <div class="text-muted small mt-1">Ticket checkout is no longer available.</div>
          </div>
        @endif
      </div>
    </div>
  </header>

  <div class="wow-event-body__content-wrap">
    <div class="wow-event-body__content-main">
      <section class="wow-event-body__section wow-event-body__section--flat" aria-label="Festival media">
        <div class="wow-event-body__gallery">
          @php
            $galleryMain = $eventImages[0] ?? '';
            $gallerySide = array_slice($eventImages, 1, 2);
          @endphp
          <div class="wow-event-body__gallery-main">
            @if($galleryMain !== '')
              <img src="{{ $galleryMain }}" alt="Festival setting" loading="eager" decoding="async">
            @else
              <div class="wow-event-body__map-placeholder">Festival setting</div>
            @endif
            <span class="photo-label">Festival setting</span>
          </div>

          <div class="wow-event-body__gallery-side">
            @forelse($gallerySide as $galleryIndex => $galleryImage)
              <div class="wow-event-body__gallery-tile">
                <img src="{{ $galleryImage }}" alt="{{ $eventTitle }} gallery preview {{ $galleryIndex + 1 }}" loading="lazy" decoding="async">
                <span class="photo-label">{{ $galleryIndex === 0 ? 'Meditation' : 'Live sound' }}</span>
              </div>
            @empty
              <div class="wow-event-body__gallery-tile">
                <div class="wow-event-body__map-placeholder">More festival images soon.</div>
              </div>
              <div class="wow-event-body__gallery-tile">
                <div class="wow-event-body__map-placeholder">More festival images soon.</div>
              </div>
            @endforelse
          </div>
        </div>
      </section>

      <section class="wow-event-body__section">
        <div class="section-heading">
          <div>
            <p class="eyebrow">Festival overview</p>
            <h2>A weekend built for peace, connection and proper nervous-system recovery.</h2>
            <p class="section-intro">
              Step away from the noise and into two days of sound baths, gong baths, guided meditation, breathwork, mindful workshops, live music, holistic stalls and calm countryside atmosphere.
            </p>
          </div>
        </div>

        <div class="highlight-grid">
          <article class="highlight-card">
            <h3>Sound healing</h3>
            <p>Gong baths, sound baths and deep listening experiences designed to help you properly switch off.</p>
          </article>

          <article class="highlight-card">
            <h3>Mindful sessions</h3>
            <p>Guided meditation, breathwork and calming workshops across both days of the festival.</p>
          </article>

          <article class="highlight-card">
            <h3>Holistic marketplace</h3>
            <p>Explore wellness stalls, therapies, exhibitors, food and drink options between sessions.</p>
          </article>
        </div>
      </section>

      <section class="wow-event-body__section" id="wowEventTimeline">
        <div class="section-heading">
          <div>
            <p class="eyebrow">Event schedule</p>
            <h2>Two calm days, one big reset.</h2>
            <p class="section-intro">
              @if($eventScheduleSessionCount > 0)
                A simple preview of the festival flow. The full timetable can be expanded or amended day by day.
              @elseif($eventScheduleDays)
                The dates are fixed, but the detailed timetable will be added later.
              @else
                A simple preview of the event span so people can understand the dates before they book.
              @endif
            </p>
          </div>
        </div>

        @if(! empty($eventScheduleDays))
          <div class="wow-event-body__schedule-tabs" role="tablist" aria-label="Festival schedule days">
            @foreach($eventScheduleDays as $dayIndex => $day)
              <button
                type="button"
                class="wow-event-body__schedule-tab{{ $loop->first ? ' is-active' : '' }}"
                data-schedule-tab
                data-schedule-target="schedule-day-{{ $dayIndex }}"
                role="tab"
                aria-selected="{{ $loop->first ? 'true' : 'false' }}"
              >
                {{ $day['label'] ?? ('Day ' . ($dayIndex + 1)) }}
              </button>
            @endforeach
          </div>

          @foreach($eventScheduleDays as $dayIndex => $day)
            <div
              class="wow-event-body__schedule-panel"
              id="schedule-day-{{ $dayIndex }}"
              data-schedule-panel
              role="tabpanel"
              @if(! $loop->first) hidden @endif
            >
              @if(! empty($day['has_multiple_spaces']))
                <div class="wow-room-carousel" data-room-carousel>
                  <button type="button" class="wow-room-arrow wow-room-arrow--prev" data-room-prev aria-label="Show previous spaces">‹</button>

                  <div class="wow-room-carousel__viewport" data-room-viewport tabindex="0" aria-label="Event spaces">
                    <div class="wow-room-grid {{ count($day['grouped_sessions'] ?? []) === 1 ? 'has-one-room' : (count($day['grouped_sessions'] ?? []) === 2 ? 'has-two-rooms' : 'has-many-rooms') }}">
                      @foreach($day['grouped_sessions'] ?? [] as $spaceIndex => $spaceGroup)
                        <section class="wow-room wow-room--tone-{{ ($spaceIndex % 4) + 1 }}">
                          <header class="wow-room__header">
                            <h3 class="wow-room__name">{{ $spaceGroup['label'] ?? 'General' }}</h3>
                            <p class="wow-room__note">{{ count($spaceGroup['sessions'] ?? []) }} session{{ count($spaceGroup['sessions'] ?? []) === 1 ? '' : 's' }}</p>
                          </header>

                          <div class="wow-room__slots">
                            @forelse($spaceGroup['sessions'] ?? [] as $sessionIndex => $session)
                              @php
                                $sessionLabel = trim((string) ($session['label'] ?? ''));
                                $sessionStart = trim((string) ($session['start_time'] ?? ''));
                                $sessionEnd = trim((string) ($session['end_time'] ?? ''));
                                $sessionNotes = trim((string) ($session['notes'] ?? ''));
                              @endphp
                              <article class="wow-session">
                                <div class="wow-session__time">{{ $sessionStart !== '' || $sessionEnd !== '' ? trim(($sessionStart !== '' ? $sessionStart : 'All day') . ($sessionEnd !== '' ? ' - ' . $sessionEnd : '')) : ($day['label'] ?? ('Day ' . ($dayIndex + 1))) }}</div>
                                <h3 class="wow-session__title">{{ $sessionLabel !== '' ? $sessionLabel : 'Session ' . ($sessionIndex + 1) }}</h3>
                                @if($sessionNotes !== '')
                                  <p class="wow-session__desc">{{ $sessionNotes }}</p>
                                @endif
                              </article>
                            @empty
                              <div class="wow-room__empty">No sessions added for this space yet.</div>
                            @endforelse
                          </div>
                        </section>
                      @endforeach
                    </div>
                  </div>

                  <button type="button" class="wow-room-arrow wow-room-arrow--next" data-room-next aria-label="Show next spaces">›</button>
                  <div class="wow-room-progress" data-room-progress aria-hidden="true"></div>
                </div>
              @else
                @forelse($day['sessions'] ?? [] as $sessionIndex => $session)
                  @php
                    $sessionLabel = trim((string) ($session['label'] ?? ''));
                    $sessionSpaceArea = trim((string) ($session['space_area'] ?? ''));
                    $sessionStart = trim((string) ($session['start_time'] ?? ''));
                    $sessionEnd = trim((string) ($session['end_time'] ?? ''));
                    $sessionNotes = trim((string) ($session['notes'] ?? ''));
                  @endphp
                  <article class="wow-event-body__timeline-item">
                    <div class="wow-event-body__timeline-time">
                      {{ $sessionStart !== '' || $sessionEnd !== '' ? trim(($sessionStart !== '' ? $sessionStart : 'All day') . ($sessionEnd !== '' ? ' - ' . $sessionEnd : '')) : ($day['label'] ?? ('Day ' . ($dayIndex + 1))) }}
                    </div>
                    <div>
                      <h3>{{ $sessionLabel !== '' ? $sessionLabel : 'Session ' . ($sessionIndex + 1) }}</h3>
                      @if($sessionSpaceArea !== '')
                        <div class="wow-event-body__timeline-space">{{ $sessionSpaceArea }}</div>
                      @endif
                      <p>{{ $sessionNotes !== '' ? $sessionNotes : ($day['label'] ?? 'Festival session') }}</p>
                    </div>
                  </article>
                @empty
                  <article class="wow-event-body__timeline-item">
                    <div class="wow-event-body__timeline-time">{{ $day['label'] ?? ('Day ' . ($dayIndex + 1)) }}</div>
                    <div>
                      <h3>Schedule coming soon</h3>
                      <p>The detailed timetable for this day will be added later.</p>
                    </div>
                  </article>
                @endforelse
              @endif
            </div>
          @endforeach
        @endif
      </section>

      <section class="wow-event-body__section">
        <div class="split">
          <article class="info-panel">
            <h3>What’s included</h3>
            <div class="wow-event-body__rich">{!! $eventIncludedHtml !== '' ? $eventIncludedHtml : nl2br(e('Access to sound healing and meditation sessions, workshops, talks, live music and the wellness marketplace with exhibitors and therapists.')) !!}</div>
          </article>

          <article class="info-panel">
            <h3>What happens on the day?</h3>
            <div class="wow-event-body__rich">{!! $eventWhatHtml !== '' ? $eventWhatHtml : nl2br(e('Expect a tranquil weekend of guided sessions, immersive sound, mindful experiences, exhibitor stalls and peaceful time outdoors.')) !!}</div>
          </article>
        </div>
      </section>

      <section class="wow-event-body__section">
        <div class="section-heading">
          <div>
            <p class="eyebrow">Venue</p>
            <h2>{{ $eventVenueLabel !== '' ? $eventVenueLabel : 'Venue' }}</h2>
            <p class="section-intro">
              A historic Kent venue with countryside surroundings, festival space and a peaceful backdrop for the weekend.
            </p>
          </div>
        </div>

        @php
          $primaryVenue = (array) ($venueLocations[0] ?? []);
          $primaryVenueAddress = array_filter([
            trim((string) ($primaryVenue['address_line_1'] ?? '')),
            trim((string) ($primaryVenue['address_line_2'] ?? '')),
            trim((string) ($primaryVenue['city'] ?? '')),
            trim((string) ($primaryVenue['county'] ?? '')),
            trim((string) ($primaryVenue['postcode'] ?? '')),
            trim((string) ($primaryVenue['country'] ?? '')),
          ]);
        @endphp

        <article class="wow-event-body__venue-card">
          <div>
            <h3>{{ $primaryVenue['label'] ?? ($eventVenueLabel !== '' ? $eventVenueLabel : 'Venue') }}</h3>
            @if(! empty($primaryVenueAddress))
              <p>
                @foreach($primaryVenueAddress as $line)
                  {{ $line }}@if(! $loop->last)<br>@endif
                @endforeach
              </p>
            @else
              <p>{{ $eventLocationLabel }}</p>
            @endif
          </div>

          <a class="btn btn-primary" href="https://www.mapbox.com/directions?destination=0.9177,51.1024" target="_blank" rel="noopener">
            Open directions
          </a>
        </article>
      </section>
    </div>

    <aside class="wow-event-body__side-stack">
      <div class="wow-event-body__side-card wow-event-body__side-map-card">
        <div class="wow-event-body__side-map-box">
          <div class="wow-event-body__map" id="desktopMap" data-event-venue-pins='@json($venuePins)' data-event-venue-locations='@json($venueLocations)'>
            <div class="wow-event-body__map-placeholder">Loading venue map...</div>
          </div>
        </div>

        <div class="wow-event-body__side-map-body">
          <h3>{{ $eventVenueLabel !== '' ? $eventVenueLabel : 'Venue' }}</h3>
          <p>{{ $eventLocationLabel }}</p>
          <a class="btn btn-primary" href="https://www.mapbox.com/directions?destination=0.9177,51.1024" target="_blank" rel="noopener">
            Get directions
          </a>
        </div>
      </div>

      <div class="wow-event-body__side-card">
        <h3>Festival snapshot</h3>
        <div class="wow-event-body__mini-list">
          @foreach(array_slice($snapshotFacts, 0, 5) as $fact)
            <div class="wow-event-body__mini-row">
              <span>{{ $fact['label'] }}</span>
              <strong>{{ $fact['value'] }}</strong>
            </div>
          @endforeach
        </div>
      </div>
    </aside>
  </div>
</section>

@push('scripts')
<script>
(function () {
  const hero = document.querySelector('[data-wow-event-hero]');
  if (hero) {
    const slides = Array.from(hero.querySelectorAll('[data-wow-event-hero-slide]'));
    if (slides.length > 1) {
      let activeIndex = slides.findIndex((slide) => slide.classList.contains('is-active'));
      if (activeIndex < 0) activeIndex = 0;
      let timerId = null;

      const setActive = (nextIndex) => {
        activeIndex = (nextIndex + slides.length) % slides.length;
        slides.forEach((slide, index) => {
          slide.classList.toggle('is-active', index === activeIndex);
        });
      };

      const start = () => {
        if (timerId !== null || slides.length < 2) return;
        timerId = window.setInterval(() => {
          setActive(activeIndex + 1);
        }, 5200);
      };

      const stop = () => {
        if (timerId === null) return;
        window.clearInterval(timerId);
        timerId = null;
      };

      setActive(activeIndex);
      start();

      hero.addEventListener('mouseenter', stop);
      hero.addEventListener('mouseleave', start);
      document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
          stop();
        } else {
          start();
        }
      });
    }
  }

  const scheduleTabs = Array.from(document.querySelectorAll('[data-schedule-tab]'));
  const schedulePanels = Array.from(document.querySelectorAll('[data-schedule-panel]'));
  if (scheduleTabs.length && schedulePanels.length) {
    const showSchedule = (targetId) => {
      scheduleTabs.forEach((tab) => {
        const isActive = tab.getAttribute('data-schedule-target') === targetId;
        tab.classList.toggle('is-active', isActive);
        tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
      });

      schedulePanels.forEach((panel) => {
        panel.hidden = panel.id !== targetId;
      });

      window.requestAnimationFrame(refreshRoomCarousels);
    };

    showSchedule(scheduleTabs[0].getAttribute('data-schedule-target') || schedulePanels[0].id);
    scheduleTabs.forEach((tab) => {
      tab.addEventListener('click', () => {
        showSchedule(tab.getAttribute('data-schedule-target') || '');
      });
    });
  }

  function refreshRoomCarousels() {
    document.querySelectorAll('[data-room-carousel]').forEach((carousel) => {
      const viewport = carousel.querySelector('[data-room-viewport]');
      const prev = carousel.querySelector('[data-room-prev]');
      const next = carousel.querySelector('[data-room-next]');
      const rooms = Array.from(carousel.querySelectorAll('.wow-room'));
      const progress = carousel.querySelector('[data-room-progress]');

      if (!viewport || !prev || !next) {
        return;
      }

      const firstRoom = rooms[0];
      const track = carousel.querySelector('.wow-room-grid');
      const gap = track ? parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap || 0) : 0;
      const roomStep = firstRoom ? (firstRoom.getBoundingClientRect().width + gap) : viewport.clientWidth;
      const visibleCount = Math.max(1, Math.round(viewport.clientWidth / Math.max(roomStep, 1)));
      const maxScroll = Math.max(0, viewport.scrollWidth - viewport.clientWidth);
      const isScrollable = rooms.length > visibleCount && maxScroll > 2;

      carousel.classList.toggle('is-not-scrollable', !isScrollable);
      prev.disabled = !isScrollable || viewport.scrollLeft <= 2;
      next.disabled = !isScrollable || viewport.scrollLeft >= maxScroll - 2;

      if (!progress) {
        return;
      }

      if (!isScrollable) {
        progress.innerHTML = '';
        return;
      }

      const maxStartIndex = Math.max(0, rooms.length - visibleCount);
      const activeIndex = Math.min(maxStartIndex, Math.max(0, Math.round(viewport.scrollLeft / Math.max(roomStep, 1))));

      progress.innerHTML = Array.from({ length: maxStartIndex + 1 }, (_, index) => `
        <span class="wow-room-progress__dot ${index === activeIndex ? 'is-active' : ''}"></span>
      `).join('');
    });
  }

  document.querySelectorAll('[data-room-carousel]').forEach((carousel) => {
    if (carousel.dataset.carouselBound === '1') {
      return;
    }
    carousel.dataset.carouselBound = '1';

    const viewport = carousel.querySelector('[data-room-viewport]');
    const prev = carousel.querySelector('[data-room-prev]');
    const next = carousel.querySelector('[data-room-next]');

    if (!viewport || !prev || !next) {
      return;
    }

    const move = (direction) => {
      const firstRoom = carousel.querySelector('.wow-room');
      const track = carousel.querySelector('.wow-room-grid');
      const gap = track ? parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap || 0) : 0;
      const step = firstRoom ? (firstRoom.getBoundingClientRect().width + gap) : viewport.clientWidth;
      viewport.scrollBy({ left: step * direction, behavior: 'smooth' });
    };

    prev.addEventListener('click', () => move(-1));
    next.addEventListener('click', () => move(1));
    viewport.addEventListener('scroll', () => refreshRoomCarousels(), { passive: true });
  });

  window.addEventListener('resize', refreshRoomCarousels);
  refreshRoomCarousels();

  document.querySelectorAll('[data-wow-event-video-autoplay]').forEach((video) => {
    if (!(video instanceof HTMLVideoElement)) {
      return;
    }

    video.muted = true;
    video.autoplay = true;
    video.loop = true;
    video.playsInline = true;

    const attemptPlay = () => {
      try {
        const playResult = video.play();
        if (playResult && typeof playResult.catch === 'function') {
          playResult.catch(() => {});
        }
      } catch (_err) {}
    };

    attemptPlay();
    video.addEventListener('canplay', attemptPlay, { once: true });
  });

  const desktopMapEl = document.getElementById('desktopMap');
  const mobileMapEl = document.getElementById('mobileMap');
  if (!desktopMapEl && !mobileMapEl) return;

  let pins = [];
  let venueLocations = [];
  try {
    pins = JSON.parse(desktopMapEl?.getAttribute('data-event-venue-pins') || '[]');
  } catch (_e) {
    pins = [];
  }
  try {
    venueLocations = JSON.parse(desktopMapEl?.getAttribute('data-event-venue-locations') || '[]');
  } catch (_e) {
    venueLocations = [];
  }

  const token = window.WOW_MAPS_KEY || @json(config('services.mapbox.token'));
  if (!token) return;

  let desktopMapInstance = null;
  let mobileMapInstance = null;

  function createMarker() {
    const el = document.createElement('div');
    el.className = 'wow-map-marker';
    return el;
  }

  function trimList(parts) {
    return (Array.isArray(parts) ? parts : [])
      .map((part) => String(part || '').trim())
      .filter(Boolean)
      .join(', ');
  }

  function buildAddressQuery(loc) {
    return trimList([
      loc?.label,
      loc?.address_line_1,
      loc?.address_line_2,
      loc?.city,
      loc?.county,
      loc?.postcode,
      loc?.country,
    ]);
  }

  function buildAddressDescription(loc) {
    return trimList([
      loc?.address_line_1,
      loc?.address_line_2,
      loc?.city,
      loc?.county,
      loc?.postcode,
      loc?.country,
    ]);
  }

  function buildCountryParam(loc) {
    const value = String(loc?.country || '').trim().toLowerCase();
    if (!value) {
      return '';
    }
    if (['gb', 'uk', 'u.k.', 'united kingdom', 'great britain', 'england', 'scotland', 'wales', 'northern ireland'].includes(value)) {
      return '&country=gb';
    }
    return '';
  }

  async function geocodeVenue(loc) {
    const query = buildAddressQuery(loc || {});
    if (!query) {
      return null;
    }

    const endpoint = 'https://api.mapbox.com/geocoding/v5/mapbox.places/' + encodeURIComponent(query) + '.json?limit=1&autocomplete=false&types=address,place,poi,locality,neighborhood,postcode,region,district' + buildCountryParam(loc) + '&access_token=' + encodeURIComponent(token);

    try {
      const response = await fetch(endpoint, { credentials: 'omit' });
      if (!response.ok) return null;
      const payload = await response.json();
      const feature = Array.isArray(payload?.features) ? payload.features[0] : null;
      if (!feature || !Array.isArray(feature.center) || feature.center.length < 2) {
        return null;
      }

      return {
        name: String(loc?.label || 'Venue').trim() || 'Venue',
        lat: Number(feature.center[1]),
        lng: Number(feature.center[0]),
        description: buildAddressDescription(loc),
      };
    } catch (_err) {
      return null;
    }
  }

  async function resolvePins() {
    if (Array.isArray(pins) && pins.length) {
      return pins;
    }

    if (!Array.isArray(venueLocations) || !venueLocations.length) {
      return [];
    }

    const resolved = await Promise.all(venueLocations.map((loc) => geocodeVenue(loc)));
    return resolved.filter((pin) => pin && Number.isFinite(Number(pin.lat)) && Number.isFinite(Number(pin.lng)));
  }

  function initDesktopMap(resolvedPins) {
    if (!desktopMapEl || desktopMapInstance || !Array.isArray(resolvedPins) || !resolvedPins.length) {
      return;
    }

    desktopMapInstance = new window.mapboxgl.Map({
      container: 'desktopMap',
      style: 'mapbox://styles/mapbox/standard',
      center: [Number(resolvedPins[0].lng), Number(resolvedPins[0].lat)],
      zoom: 16,
      pitch: 62,
      bearing: -24,
      antialias: true,
    });

    desktopMapInstance.addControl(new window.mapboxgl.NavigationControl({
      visualizePitch: true,
    }), 'top-right');

    desktopMapInstance.on('style.load', () => {
      try {
        desktopMapInstance.setConfigProperty('basemap', 'lightPreset', 'day');
      } catch (_err) {}

      const bounds = new window.mapboxgl.LngLatBounds();
      resolvedPins.forEach((pin) => {
        if (!pin || !Number.isFinite(Number(pin.lng)) || !Number.isFinite(Number(pin.lat))) {
          return;
        }

        new window.mapboxgl.Marker({
          element: createMarker(),
          anchor: 'bottom',
        })
          .setLngLat([Number(pin.lng), Number(pin.lat)])
          .addTo(desktopMapInstance);
        bounds.extend([Number(pin.lng), Number(pin.lat)]);
      });

      if (!bounds.isEmpty()) {
        try {
          desktopMapInstance.fitBounds(bounds, { padding: 50, duration: 700 });
        } catch (_err) {}
      }

      desktopMapEl.querySelector('.wow-event-body__map-placeholder')?.remove();
    });
  }

  function initMobileMap(resolvedPins) {
    if (!mobileMapEl || mobileMapInstance || window.innerWidth > 720 || !Array.isArray(resolvedPins) || !resolvedPins.length) {
      return;
    }

    mobileMapInstance = new window.mapboxgl.Map({
      container: 'mobileMap',
      style: 'mapbox://styles/mapbox/streets-v12',
      center: [Number(resolvedPins[0].lng), Number(resolvedPins[0].lat)],
      zoom: 14.35,
      pitch: 0,
      bearing: 0,
      interactive: false,
      attributionControl: true,
    });

    mobileMapInstance.on('load', () => {
      resolvedPins.forEach((pin) => {
        if (!pin || !Number.isFinite(Number(pin.lng)) || !Number.isFinite(Number(pin.lat))) {
          return;
        }

        new window.mapboxgl.Marker({
          element: createMarker(),
          anchor: 'bottom',
        })
          .setLngLat([Number(pin.lng), Number(pin.lat)])
          .addTo(mobileMapInstance);
      });

      mobileMapEl.querySelector('.wow-event-body__map-placeholder')?.remove();
    });
  }

  function resizeMaps() {
    if (desktopMapInstance) {
      desktopMapInstance.resize();
    }

    if (mobileMapInstance) {
      mobileMapInstance.resize();
    }
  }

  function loadMapbox(cb) {
    if (window.mapboxgl && window.mapboxgl.Map) {
      try {
        Promise.resolve(cb()).catch(() => {});
      } catch (_err) {}
      return;
    }

    window.__wowMapboxQueue = window.__wowMapboxQueue || [];
    window.__wowMapboxQueue.push(cb);
    if (window.__wowMapboxLoading) return;
    window.__wowMapboxLoading = true;

    if (!document.querySelector('link[href*="mapbox-gl.css"]')) {
      const link = document.createElement('link');
      link.rel = 'stylesheet';
      link.href = 'https://api.mapbox.com/mapbox-gl-js/v3.6.0/mapbox-gl.css';
      document.head.appendChild(link);
    }

    const script = document.createElement('script');
    script.src = 'https://api.mapbox.com/mapbox-gl-js/v3.6.0/mapbox-gl.js';
    script.async = true;
    script.onload = function () {
      window.__wowMapboxLoading = false;
      const queue = (window.__wowMapboxQueue || []).splice(0);
      queue.forEach((fn) => {
        try { Promise.resolve(fn()).catch(() => {}); } catch (_err) {}
      });
    };
    script.onerror = function () {
      window.__wowMapboxLoading = false;
      window.__wowMapboxQueue = [];
    };
    document.body.appendChild(script);
  }

  loadMapbox(async () => {
    if (!(window.mapboxgl && window.mapboxgl.Map)) return;
    window.mapboxgl.accessToken = token;

    const resolvedPins = await resolvePins();
    if (!Array.isArray(resolvedPins) || !resolvedPins.length) {
      [desktopMapEl, mobileMapEl].forEach((el) => {
        const placeholder = el?.querySelector('.wow-event-body__map-placeholder');
        if (placeholder) {
          placeholder.textContent = 'Add a venue address or latitude/longitude to show the Mapbox map here.';
        }
      });
      return;
    }

    initDesktopMap(resolvedPins);
    initMobileMap(resolvedPins);

    window.addEventListener('resize', () => {
      if (!mobileMapInstance && window.innerWidth <= 720) {
        initMobileMap(resolvedPins);
      }
      resizeMaps();
    });
  });
})();
</script>
@endpush
