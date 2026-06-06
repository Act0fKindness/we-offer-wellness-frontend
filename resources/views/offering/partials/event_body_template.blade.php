@php
    $event = (array) ($product['event'] ?? []);
    $eventTitle = trim((string) ($product['title'] ?? 'Event'));
    $eventSummary = trim((string) ($product['summary'] ?? $product['description'] ?? ''));
    $eventWhat = trim((string) ($product['what_to_expect'] ?? ''));
    $eventIncluded = trim((string) ($product['included'] ?? ''));
    $eventLocation = trim((string) ($product['location'] ?? ''));
    $eventMode = trim((string) ($product['mode'] ?? ''));
    $eventImage = trim((string) ($product['image'] ?? ($product['images'][0] ?? '')));
    $eventVideo = trim((string) ($product['video_url'] ?? data_get($event, 'video_url', '')));
    $extractYoutubeId = static function (string $url): ?string {
        $url = trim($url);
        if ($url === '') {
            return null;
        }
        if (! str_starts_with($url, 'http')) {
            $url = 'https://' . $url;
        }

        $parts = parse_url($url);
        if (! is_array($parts)) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = (string) ($parts['path'] ?? '');
        $query = (string) ($parts['query'] ?? '');

        if (str_contains($host, 'youtu.be')) {
            $id = ltrim($path, '/');
            return preg_match('/^[A-Za-z0-9_-]{6,}$/', $id) ? $id : null;
        }

        if (str_contains($host, 'youtube.com')) {
            parse_str($query, $q);
            if (! empty($q['v']) && preg_match('/^[A-Za-z0-9_-]{6,}$/', (string) $q['v'])) {
                return (string) $q['v'];
            }
            if (str_starts_with($path, '/embed/')) {
                $id = substr($path, strlen('/embed/'));
                return preg_match('/^[A-Za-z0-9_-]{6,}$/', $id) ? $id : null;
            }
            if (str_starts_with($path, '/shorts/')) {
                $id = substr($path, strlen('/shorts/'));
                return preg_match('/^[A-Za-z0-9_-]{6,}$/', $id) ? $id : null;
            }
        }

        return null;
    };
    $eventVideoEmbedUrl = '';
    $eventVideoId = $extractYoutubeId($eventVideo);
    if ($eventVideoId !== null) {
        $eventVideoEmbedUrl = 'https://www.youtube.com/embed/' . $eventVideoId . '?rel=0&modestbranding=1';
    }
    $eventImages = is_array($product['images'] ?? null) ? array_values(array_filter($product['images'])) : [];
    if ($eventImage !== '' && ! in_array($eventImage, $eventImages, true)) {
        array_unshift($eventImages, $eventImage);
    }
    $eventImages = array_values(array_unique($eventImages));
    $heroImages = $eventImages;
    if (! empty($heroImages)) {
        while (count($heroImages) < 4) {
            $heroImages[] = $heroImages[count($heroImages) % count($heroImages)];
        }
    }
    $galleryItems = [];
    foreach ($eventImages as $imageIndex => $image) {
        $galleryItems[] = [
            'type' => 'image',
            'src' => $image,
            'label' => $imageIndex === 0 ? 'Festival setting' : ($imageIndex === 1 ? 'Meditation' : 'Live sound'),
            'alt' => $imageIndex === 0 ? 'Peaceful countryside festival setting' : ($imageIndex === 1 ? 'Meditation session' : 'Live music and festival atmosphere'),
        ];
    }
    if ($eventVideo !== '') {
        array_unshift($galleryItems, [
            'type' => $eventVideoEmbedUrl !== '' ? 'youtube' : 'video',
            'src' => $eventVideoEmbedUrl !== '' ? $eventVideoEmbedUrl : $eventVideo,
            'poster' => $eventImage !== '' ? $eventImage : ($eventImages[0] ?? ''),
            'label' => 'Festival film',
            'alt' => 'Festival video preview',
        ]);
    }
    if (empty($galleryItems)) {
        $galleryItems[] = [
            'type' => 'empty',
            'src' => '',
            'label' => 'More media coming soon',
            'alt' => 'Event media coming soon',
        ];
    }
    $galleryMain = $galleryItems[0];
    $gallerySide = [
        $galleryItems[1] ?? $galleryItems[0],
        $galleryItems[2] ?? $galleryItems[0],
    ];

    $venueLocationsRaw = $product['venue_locations'] ?? ($product['locations'] ?? []);
    $venueLocations = is_array($venueLocationsRaw) ? array_values(array_filter($venueLocationsRaw, fn ($loc) => is_array($loc))) : [];
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
    $primaryVenue = (array) ($venueLocations[0] ?? []);
    $venueAddressLines = array_values(array_filter([
        trim((string) ($primaryVenue['address_line_1'] ?? '')),
        trim((string) ($primaryVenue['address_line_2'] ?? '')),
        trim((string) ($primaryVenue['city'] ?? '')),
        trim((string) ($primaryVenue['county'] ?? '')),
        trim((string) ($primaryVenue['postcode'] ?? '')),
        trim((string) ($primaryVenue['country'] ?? '')),
    ]));
    if (empty($venueAddressLines)) {
        $venueAddressLines = ['Priory Road', 'Bilsington', 'TN25 7AU', 'United Kingdom'];
    }

    $eventVenueLabel = trim(implode(', ', array_filter([
        (string) ($primaryVenue['label'] ?? ''),
        (string) ($primaryVenue['city'] ?? ''),
        (string) ($primaryVenue['county'] ?? ''),
    ])));
    if ($eventVenueLabel === '') {
        $eventVenueLabel = $eventLocation !== '' ? $eventLocation : 'Bilsington Priory Estate';
    }
    $eventLocationLabel = $eventLocation !== '' ? $eventLocation : $eventVenueLabel;

    $eventStartDate = trim((string) ($product['start_date'] ?? data_get($event, 'start_date', '')));
    $eventStartTime = trim((string) ($product['start_time'] ?? data_get($event, 'start_time', '')));
    $eventEndDate = trim((string) ($product['end_date'] ?? data_get($event, 'end_date', '')));
    $eventEndTime = trim((string) ($product['end_time'] ?? data_get($event, 'end_time', '')));
    $eventDateBadge = 'Sat 11 Jul – Sun 12 Jul 2026';
    $eventMetaDate = '11–12 July 2026';
    $eventSnapshotDate = '11–12 Jul';
    $eventTimeLabel = '';
    if ($eventStartDate !== '') {
        try {
            $start = \Carbon\Carbon::parse($eventStartDate . ($eventStartTime !== '' ? ' ' . $eventStartTime : ''));
            $end = $eventEndDate !== '' ? \Carbon\Carbon::parse($eventEndDate . ($eventEndTime !== '' ? ' ' . $eventEndTime : '')) : $start->copy();
            if ($end->toDateString() !== $start->toDateString()) {
                $eventDateBadge = $start->format('D j M') . ' – ' . $end->format('D j M') . ' ' . $start->format('Y');
                $eventMetaDate = $start->format('j') . '–' . $end->format('j F Y');
                $eventSnapshotDate = $start->format('j') . '–' . $end->format('j M');
            } else {
                $eventDateBadge = $start->format('D j M Y');
                $eventMetaDate = $start->format('j F Y');
                $eventSnapshotDate = $start->format('j M');
            }

            $eventTimeLabel = trim(($eventStartTime !== '' ? $eventStartTime : '10:00 AM') . ($eventEndTime !== '' ? ' – ' . $eventEndTime : ' – 5:00 PM'));
        } catch (\Throwable $e) {
            $eventDateBadge = trim(implode(' ', array_filter([$eventStartDate, $eventStartTime, $eventEndDate, $eventEndTime])));
        }
    }
    if ($eventTimeLabel === '') {
        $eventTimeLabel = '10:00 AM – 5:00 PM';
    }

    $eventCapacity = (int) ($product['capacity'] ?? data_get($event, 'capacity', 1000));
    $eventCapacity = max(1, min(1000, $eventCapacity > 0 ? $eventCapacity : 1000));

    $renderRichHtml = static function (string $value): string {
        return \App\Support\ContentFormatter::format($value);
    };
    $eventIncludedHtml = $renderRichHtml($eventIncluded);
    $eventWhatHtml = $renderRichHtml($eventWhat);

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
            $startTime = trim((string) data_get($session, 'start_time', data_get($session, 'startTime', '')));
            $endTime = trim((string) data_get($session, 'end_time', data_get($session, 'endTime', '')));
            $notes = trim((string) data_get($session, 'notes', data_get($session, 'description', '')));

            if ($label === '' && $startTime === '' && $endTime === '' && $notes === '') {
                continue;
            }

            $sessions[] = [
                'id' => (string) (data_get($session, 'id', '') ?: sprintf('event_schedule_%s_%d_%d', $dayDate !== '' ? $dayDate : 'day', $dayIndex + 1, $sessionIndex + 1)),
                'label' => $label !== '' ? $label : 'Session',
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

    $eventScheduleSessionCount = 0;
    foreach ($eventScheduleDays as $day) {
        $eventScheduleSessionCount += count($day['sessions'] ?? []);
    }

    $eventScheduleIntro = $eventScheduleSessionCount > 0
        ? 'Browse the published sessions for each day. Tap a tab to switch between them.'
        : (! empty($eventScheduleDays)
            ? 'The dates are set, but the detailed timetable has not been published yet.'
            : 'Schedule coming soon.');

    $eventWatermark = strtoupper(trim(preg_replace('/\s*[–-].*$/', '', $eventTitle)) ?: 'OUR VIBE');
    $eventScheduleSummary = '35+ sessions';

    $eventMetaCards = [
        ['label' => 'Dates', 'value' => $eventMetaDate],
        ['label' => 'Time', 'value' => $eventTimeLabel],
        ['label' => 'Format', 'value' => $eventMode !== '' ? $eventMode : 'In-person festival'],
        ['label' => 'Tickets', 'value' => count($product['variants'] ?? []) > 0 ? count($product['variants']) . ' ticket types' : '3 ticket types'],
    ];

    $eventDirectionsCoords = [0.913226, 51.085016];
    if (! empty($venuePins) && isset($venuePins[0]['lng'], $venuePins[0]['lat']) && is_numeric($venuePins[0]['lng']) && is_numeric($venuePins[0]['lat'])) {
        $eventDirectionsCoords = [(float) $venuePins[0]['lng'], (float) $venuePins[0]['lat']];
    }
    $eventDirectionsUrl = 'https://www.mapbox.com/directions?destination=' . $eventDirectionsCoords[0] . ',' . $eventDirectionsCoords[1];
@endphp

@push('styles')
<link href="https://api.mapbox.com/mapbox-gl-js/v3.5.1/mapbox-gl.css" rel="stylesheet">
<style>
    .festival-page {
        --ink: #0d1b2a;
        --muted: #637486;
        --blue-950: #071d33;
        --blue-900: #0b2b4a;
        --blue-75: #f3f9fd;
        --button-blue: #478ee4;
        --button-blue-hover: #326fbd;
        --teal-soft: #e7f5f3;
        --amber: #f5a400;
        --white: #ffffff;
        --line: #d9e4ec;
        --line-dark: rgba(13, 27, 42, 0.16);
        --shadow: 0 16px 44px rgba(7, 29, 51, 0.12);
        --shadow-soft: 0 10px 28px rgba(7, 29, 51, 0.08);
        --radius: 1px;
        --max: 1180px;
        min-height: 100vh;
        color: var(--ink);
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        background: #f7fbfd;
    }

    .festival-page,
    .festival-page * {
        box-sizing: border-box;
        font-weight: 400;
        background: none;
    }

    .festival-page img {
        width: 100%;
        display: block;
    }

    .festival-page a {
        color: inherit;
        text-decoration: none;
    }

    .festival-page button,
    .festival-page select {
        font: inherit;
    }

    .festival-page .mobile-map-hero {
        display: none;
    }

    .festival-page .event-nav {
        max-width: var(--max);
        margin: 20px auto 0;
        padding: 0 20px;
    }

    .festival-page .event-nav-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 10px;
        border: 1px solid var(--line);
        background: var(--white);
        border-radius: var(--radius);
        box-shadow: var(--shadow-soft);
    }

    .festival-page .breadcrumbs {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
        font-size: 13px;
        color: var(--muted);
        white-space: nowrap;
        overflow: auto;
    }

    .festival-page .crumb {
        display: inline-flex;
        align-items: center;
        padding: 7px 10px;
        background: var(--blue-75);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        color: var(--blue-900);
    }

    .festival-page .crumb-current {
        background: var(--teal-soft);
        color: #1f6f68;
        border-color: #cce8e4;
    }

    .festival-page .nav-badges {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .festival-page .nav-badge {
        padding: 7px 10px;
        border-radius: var(--radius);
        background: var(--white);
        border: 1px solid var(--line);
        color: var(--blue-900);
        font-size: 13px;
    }

    .festival-page .nav-badge.green {
        background: var(--teal-soft);
        color: #1f6f68;
        border-color: #cce8e4;
    }

    .festival-page .hero-wrap {
        max-width: var(--max);
        margin: 18px auto 0;
        padding: 0 20px;
    }

    .festival-page .hero {
        position: relative;
        min-height: 630px;
        border-radius: var(--radius);
        overflow: hidden;
        background: var(--blue-950);
        box-shadow: var(--shadow);
    }

    .festival-page .hero-slide {
        position: absolute;
        inset: 0;
        background-size: cover;
        background-position: center;
        opacity: 0;
        transform: scale(1.02);
        transition: opacity 900ms ease, transform 5500ms ease;
    }

    .festival-page .hero-slide.is-active {
        opacity: 1;
        transform: scale(1);
    }

    .festival-page .hero-overlay {
        position: absolute;
        inset: 0;
        background: rgba(7, 29, 51, 0.56);
        z-index: 1;
        pointer-events: none;
    }

    .festival-page .hero-watermark {
        position: absolute;
        right: 20px;
        top: 34px;
        z-index: 2;
        font-size: clamp(78px, 13vw, 168px);
        line-height: 0.82;
        letter-spacing: -0.09em;
        color: rgba(255, 255, 255, 0.08);
        text-transform: uppercase;
        pointer-events: none;
    }

    .festival-page .hero-content {
        position: relative;
        z-index: 3;
        display: grid;
        grid-template-columns: minmax(0, 1fr) 380px;
        gap: 28px;
        padding: 42px;
        min-height: 630px;
        align-items: end;
    }

    .festival-page .hero-main {
        max-width: 730px;
    }

    .festival-page .kicker-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 18px;
    }

    .festival-page .pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        border-radius: 1px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.22);
        color: #eef9ff;
        font-size: 13px;
    }

    .festival-page .hero h1 {
        margin: 0;
        color: var(--white);
        font-size: clamp(44px, 6vw, 82px);
        line-height: 0.9;
        letter-spacing: -0.07em;
    }

    .festival-page .hero-subtitle {
        margin: 22px 0 0;
        max-width: 650px;
        color: rgba(255, 255, 255, 0.86);
        font-size: 18px;
        line-height: 1.65;
    }

    .festival-page .hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 28px;
    }

    .festival-page .btn {
        appearance: none;
        border: 1px solid transparent;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-height: 50px;
        padding: 0 18px;
        border-radius: 0;
        font-size: 15px;
        transition: transform 0.18s ease, background 0.18s ease, border-color 0.18s ease;
    }

    .festival-page .btn:hover {
        transform: translateY(-1px);
    }

    .festival-page .btn-primary,
    .festival-page .checkout-button {
        color: var(--white);
        background: var(--button-blue);
        border-color: var(--button-blue);
    }

    .festival-page .btn-primary:hover,
    .festival-page .checkout-button:hover {
        background: var(--button-blue-hover);
        border-color: var(--button-blue-hover);
    }

    .festival-page .btn-secondary {
        color: #ffffff;
        background: rgba(255, 255, 255, 0.12);
        border-color: rgba(255, 255, 255, 0.28);
    }

    .festival-page .hero-meta {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-top: 34px;
        max-width: 760px;
    }

    .festival-page .meta-card {
        min-height: 92px;
        padding: 15px;
        border-radius: var(--radius);
        background: rgba(255, 255, 255, 0.11);
        border: 1px solid rgba(255, 255, 255, 0.18);
        color: #ffffff;
    }

    .festival-page .meta-card small {
        display: block;
        margin-bottom: 6px;
        color: rgba(255, 255, 255, 0.66);
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.09em;
    }

    .festival-page .meta-card strong {
        display: block;
        font-size: 15px;
        line-height: 1.25;
    }

    .festival-page .ticket-panel {
        position: sticky;
        top: 22px;
        align-self: start;
        border-radius: var(--radius);
        padding: 18px;
        background: var(--white);
        border: 1px solid var(--line);
        box-shadow: 0 22px 60px rgba(2, 18, 32, 0.26);
    }

    .festival-page .ticket-top {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        align-items: flex-start;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--line);
    }

    .festival-page .price-label {
        color: var(--muted);
        font-size: 12px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .festival-page .price {
        margin-top: 3px;
        color: var(--blue-950);
        font-size: 34px;
        line-height: 1;
        letter-spacing: -0.05em;
    }

    .festival-page .status-dot {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #176e66;
        background: var(--teal-soft);
        border: 1px solid #cce8e4;
        padding: 7px 9px;
        border-radius: var(--radius);
        font-size: 12px;
        white-space: nowrap;
    }

    .festival-page .status-dot::before {
        content: "";
        width: 7px;
        height: 7px;
        background: #249b7f;
        border-radius: 2px;
    }

    .festival-page .booking-fields {
        display: block;
    }

    .festival-page .ticket-label {
        display: block;
        margin: 16px 0 8px;
        font-size: 13px;
        color: var(--blue-950);
    }

    .festival-page .calendar-box {
        border: 1px solid var(--line);
        background: var(--blue-75);
        border-radius: var(--radius);
        padding: 12px;
    }

    .festival-page .calendar-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }

    .festival-page .calendar-title {
        color: var(--blue-950);
        font-size: 15px;
    }

    .festival-page .calendar-note {
        color: var(--muted);
        font-size: 11px;
    }

    .festival-page .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 5px;
    }

    .festival-page .calendar-day-name {
        text-align: center;
        color: var(--muted);
        font-size: 10px;
        line-height: 1;
        text-transform: uppercase;
        padding: 4px 0 7px;
    }

    .festival-page .calendar-date {
        min-height: 36px;
        border-radius: var(--radius);
        border: 1px solid var(--line);
        background: var(--white);
        color: var(--blue-950);
        cursor: pointer;
        font-size: 13px;
    }

    .festival-page .calendar-date:hover {
        border-color: var(--button-blue);
        background: #eef6ff;
    }

    .festival-page .calendar-date.is-disabled {
        color: #aab8c4;
        background: #f8fbfd;
        cursor: not-allowed;
    }

    .festival-page .calendar-date.is-event-day {
        border-color: #a7d3e7;
        background: #ffffff;
    }

    .festival-page .calendar-date.is-selected,
    .festival-page .calendar-date.is-range {
        color: var(--white);
        background: var(--button-blue);
        border-color: var(--button-blue);
    }

    .festival-page .ticket-select {
        width: 100%;
        min-height: 48px;
        border-radius: var(--radius);
        border: 1px solid var(--line-dark);
        background: var(--white);
        color: var(--blue-950);
        padding: 0 12px;
        font-size: 14px;
        outline: none;
    }

    .festival-page .ticket-select:focus {
        border-color: var(--button-blue);
        box-shadow: 0 0 0 3px rgba(71, 142, 228, 0.16);
    }

    .festival-page .custom-ticket-dropdown {
        position: relative;
    }

    .festival-page .custom-ticket-trigger {
        width: 100%;
        min-height: 54px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 11px 12px;
        border-radius: var(--radius);
        border: 1px solid var(--line-dark);
        background: var(--white);
        color: var(--blue-950);
        cursor: pointer;
        text-align: left;
        font-size: 14px;
        line-height: 1.2;
    }

    .festival-page .custom-ticket-trigger:focus-visible {
        outline: none;
        border-color: var(--button-blue);
        box-shadow: 0 0 0 3px rgba(71, 142, 228, 0.16);
    }

    .festival-page .custom-ticket-copy {
        min-width: 0;
        display: grid;
        gap: 3px;
    }

    .festival-page .custom-ticket-title,
    .festival-page .custom-ticket-option-title {
        display: block;
        color: var(--blue-950);
        font-size: 14px;
    }

    .festival-page .custom-ticket-meta,
    .festival-page .custom-ticket-option-meta {
        display: block;
        color: var(--muted);
        font-size: 12px;
        line-height: 1.35;
    }

    .festival-page .custom-ticket-price,
    .festival-page .custom-ticket-option-price {
        color: var(--blue-950);
        font-size: 14px;
        white-space: nowrap;
    }

    .festival-page .custom-ticket-chevron {
        width: 18px;
        height: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--muted);
        transition: transform 0.18s ease;
        flex: 0 0 auto;
    }

    .festival-page .custom-ticket-chevron svg {
        width: 18px;
        height: 18px;
    }

    .festival-page .custom-ticket-dropdown.is-open .custom-ticket-chevron {
        transform: rotate(180deg);
    }

    .festival-page .custom-ticket-menu {
        position: absolute;
        left: 0;
        right: 0;
        top: calc(100% + 6px);
        z-index: 30;
        display: none;
        border: 1px solid var(--line-dark);
        background: var(--white);
        box-shadow: 0 18px 34px rgba(7, 29, 51, 0.14);
        max-height: 280px;
        overflow: auto;
    }

    .festival-page .custom-ticket-dropdown.is-open .custom-ticket-menu {
        display: grid;
    }

    .festival-page .custom-ticket-option {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 13px 12px;
        border: 0;
        border-bottom: 1px solid var(--line);
        background: var(--white);
        color: var(--blue-950);
        cursor: pointer;
        text-align: left;
    }

    .festival-page .custom-ticket-option:last-child {
        border-bottom: 0;
    }

    .festival-page .custom-ticket-option:hover,
    .festival-page .custom-ticket-option.is-selected {
        background: #eef6ff;
    }

    .festival-page .custom-ticket-option.is-selected {
        box-shadow: inset 3px 0 0 var(--button-blue);
    }

    .festival-page .selected-summary {
        margin-top: 10px;
        padding: 12px;
        border-radius: var(--radius);
        background: var(--blue-75);
        border: 1px solid var(--line);
    }

    .festival-page .selected-summary-row {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        color: var(--muted);
        font-size: 12px;
    }

    .festival-page .selected-summary-row + .selected-summary-row {
        margin-top: 7px;
    }

    .festival-page .selected-summary strong {
        color: var(--blue-950);
        text-align: right;
    }

    .festival-page .hold-banner {
        display: none;
        align-items: center;
        gap: 12px;
        margin-top: 12px;
        padding: 12px;
        border-radius: var(--radius);
        background: #fff7e4;
        border: 1px solid #ffdca3;
        color: var(--amber);
    }

    .festival-page .hold-banner.is-active {
        display: flex;
    }

    .festival-page .hourglass {
        width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        animation: hourglassFlip 2.2s infinite ease-in-out;
        transform-origin: 50% 50%;
    }

    .festival-page .hourglass svg {
        width: 22px;
        height: 22px;
    }

    @keyframes hourglassFlip {
        0% { transform: rotate(0deg); }
        38% { transform: rotate(180deg); }
        50% { transform: rotate(180deg); }
        88% { transform: rotate(360deg); }
        100% { transform: rotate(360deg); }
    }

    .festival-page .ticket-control {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 12px;
        border-radius: var(--radius);
        background: var(--blue-75);
        border: 1px solid var(--line);
        margin: 14px 0;
    }

    .festival-page .ticket-control strong {
        display: block;
        color: var(--blue-950);
        font-size: 14px;
    }

    .festival-page .ticket-control small {
        display: block;
        color: var(--muted);
        font-size: 12px;
        margin-top: 3px;
    }

    .festival-page .qty {
        display: inline-flex;
        align-items: center;
        border-radius: var(--radius);
        background: var(--white);
        border: 1px solid var(--line-dark);
    }

    .festival-page .qty button {
        width: 38px;
        height: 38px;
        border: 0;
        background: transparent;
        cursor: pointer;
        color: var(--blue-950);
        font-size: 18px;
    }

    .festival-page .qty span {
        min-width: 34px;
        text-align: center;
        color: var(--blue-950);
    }

    .festival-page .checkout-button {
        width: 100%;
        min-height: 54px;
        border-radius: var(--radius);
    }

    .festival-page .secondary-checkout {
        width: 100%;
        min-height: 50px;
        border-radius: var(--radius);
        margin-top: 0;
        color: var(--blue-950);
        background: #ffffff;
        border: 1px solid var(--line-dark);
    }

    .festival-page .mobile-panel-pick {
        display: none;
        margin-top: 14px;
    }

    .festival-page .secure-note {
        margin: 13px 4px 2px;
        color: var(--muted);
        font-size: 12px;
        line-height: 1.45;
        text-align: center;
    }

    .festival-page .mobile-ticket-bar {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 50;
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 10px;
        border-radius: var(--radius);
        background: var(--white);
        border-top: 1px solid var(--line-dark);
        box-shadow: 0 18px 44px rgba(7, 29, 51, 0.18);
    }

    .festival-page .mobile-ticket-bar.is-visible {
        display: flex;
    }

    .festival-page .mobile-ticket-bar strong {
        display: block;
        color: var(--blue-950);
        font-size: 18px;
        letter-spacing: -0.03em;
    }

    .festival-page .mobile-ticket-bar span {
        color: var(--muted);
        font-size: 12px;
    }

    .festival-page .mobile-ticket-bar .checkout-button {
        width: auto;
        min-width: 124px;
        min-height: 48px;
    }

    .festival-page .wow-booking-backdrop {
        position: fixed;
        inset: 0;
        z-index: 80;
        display: none;
        background: rgba(7, 29, 51, 0.56);
    }

    .festival-page .wow-booking-backdrop.is-open {
        display: block;
    }

    .festival-page .wow-booking-modal {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 90;
        display: none;
        max-height: 92vh;
        overflow: auto;
        background: var(--white);
        border-radius: var(--radius) var(--radius) 0 0;
        border-top: 1px solid var(--line);
        box-shadow: 0 -18px 44px rgba(7, 29, 51, 0.2);
    }

    .festival-page .wow-booking-modal.is-open {
        display: block;
    }

    .festival-page .booking-modal-head {
        position: sticky;
        top: 0;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 16px;
        background: var(--white);
        border-bottom: 1px solid var(--line);
    }

    .festival-page .booking-modal-head h3 {
        margin: 0;
        color: var(--blue-950);
        font-size: 20px;
        letter-spacing: -0.03em;
    }

    .festival-page .booking-modal-close {
        width: 40px;
        height: 40px;
        border-radius: var(--radius);
        border: 1px solid var(--line-dark);
        background: var(--white);
        color: var(--blue-950);
        font-size: 22px;
        cursor: pointer;
    }

    .festival-page .booking-modal-body {
        padding: 16px;
    }

    .festival-page .booking-modal-footer {
        position: sticky;
        bottom: 0;
        z-index: 2;
        padding: 12px 16px 16px;
        background: var(--white);
        border-top: 1px solid var(--line);
    }

    .festival-page .modal-fields-slot {
        display: contents;
    }

    .festival-page .content-wrap {
        max-width: var(--max);
        margin: 34px auto 100px;
        padding: 0 20px;
        display: grid;
        grid-template-columns: minmax(0, 1fr) 380px;
        gap: 28px;
        align-items: start;
    }

    .festival-page .content-main {
        min-width: 0;
    }

    .festival-page .section {
        padding: 34px;
        margin-bottom: 22px;
        border-radius: var(--radius);
        background: var(--white);
        border: 1px solid var(--line);
        box-shadow: var(--shadow-soft);
    }

    .festival-page .section.flat {
        box-shadow: none;
        background: transparent;
        border: 0;
        padding: 0;
    }

    .festival-page .section-heading {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 22px;
    }

    .festival-page .eyebrow {
        margin: 0 0 8px;
        color: var(--button-blue);
        font-size: 12px;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .festival-page .section h2 {
        margin: 0;
        color: var(--blue-950);
        font-size: clamp(26px, 3vw, 40px);
        line-height: 0.98;
        letter-spacing: -0.055em;
    }

    .festival-page .section-intro {
        max-width: 620px;
        color: var(--muted);
        font-size: 16px;
        line-height: 1.65;
        margin: 12px 0 0;
    }

    .festival-page .gallery {
        display: grid;
        grid-template-columns: 1.3fr 0.7fr;
        gap: 12px;
    }

    .festival-page .gallery-main,
    .festival-page .gallery-side,
    .festival-page .gallery-tile {
        position: relative;
        overflow: hidden;
        border-radius: var(--radius);
        background: #dceef7;
    }

    .festival-page .gallery-main {
        min-height: 430px;
    }

    .festival-page .gallery-side {
        display: grid;
        gap: 12px;
        background: transparent;
    }

    .festival-page .gallery-side .gallery-tile {
        min-height: 209px;
    }

    .festival-page .gallery img {
        height: 100%;
        object-fit: cover;
        transition: transform 0.35s ease;
    }

    .festival-page .gallery video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        background: #dceef7;
    }

    .festival-page .gallery iframe {
        width: 100%;
        height: 100%;
        display: block;
        border: 0;
        background: #dceef7;
    }

    .festival-page .gallery-empty {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 18px;
        background: linear-gradient(180deg, rgba(7, 29, 51, 0.08) 0%, rgba(7, 29, 51, 0.72) 100%);
        color: #ffffff;
    }

    .festival-page .gallery-empty strong {
        display: block;
        margin-bottom: 4px;
        font-size: 18px;
        letter-spacing: -0.03em;
    }

    .festival-page .gallery-empty span {
        display: block;
        color: rgba(255, 255, 255, 0.78);
        font-size: 13px;
        line-height: 1.45;
    }

    .festival-page .gallery-main:hover img,
    .festival-page .gallery-tile:hover img,
    .festival-page .gallery-main:hover video,
    .festival-page .gallery-tile:hover video {
        transform: scale(1.035);
    }

    .festival-page .photo-label {
        position: absolute;
        left: 12px;
        bottom: 12px;
        padding: 8px 10px;
        border-radius: var(--radius);
        background: rgba(7, 29, 51, 0.82);
        color: #ffffff;
        font-size: 12px;
    }

    .festival-page .highlight-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .festival-page .highlight-card {
        padding: 22px;
        min-height: 170px;
        border-radius: var(--radius);
        background: var(--blue-75);
        border: 1px solid var(--line);
    }

    .festival-page .highlight-card h3 {
        margin: 0 0 8px;
        color: var(--blue-950);
        font-size: 18px;
        letter-spacing: -0.03em;
    }

    .festival-page .highlight-card p {
        margin: 0;
        color: var(--muted);
        font-size: 14px;
        line-height: 1.55;
    }

    .festival-page .schedule-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 20px;
    }

    .festival-page .schedule-tab {
        border: 1px solid var(--line-dark);
        background: #ffffff;
        color: var(--blue-950);
        border-radius: var(--radius);
        padding: 10px 12px;
        cursor: pointer;
    }

    .festival-page .schedule-tab.active,
    .festival-page .schedule-tab[aria-selected="true"] {
        background: var(--button-blue);
        color: #ffffff;
        border-color: var(--button-blue);
    }

    .festival-page .timeline {
        display: grid;
        gap: 10px;
    }

    .festival-page .timeline[hidden],
    .festival-page [data-schedule][hidden] {
        display: none !important;
    }

    .festival-page .timeline-item {
        display: grid;
        grid-template-columns: 110px minmax(0, 1fr);
        gap: 18px;
        padding: 18px;
        border-radius: var(--radius);
        background: #ffffff;
        border: 1px solid var(--line);
    }

    .festival-page .timeline-time {
        color: var(--button-blue);
        font-size: 14px;
    }

    .festival-page .timeline-item h3 {
        margin: 0 0 5px;
        color: var(--blue-950);
        font-size: 17px;
        letter-spacing: -0.02em;
    }

    .festival-page .timeline-item p {
        margin: 0;
        color: var(--muted);
        font-size: 14px;
        line-height: 1.55;
    }

    .festival-page .split {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .festival-page .info-panel {
        padding: 24px;
        border-radius: var(--radius);
        background: var(--blue-75);
        border: 1px solid var(--line);
    }

    .festival-page .info-panel h3 {
        margin: 0 0 12px;
        color: var(--blue-950);
        font-size: 22px;
        letter-spacing: -0.04em;
    }

    .festival-page .info-panel p {
        margin: 0;
        color: var(--muted);
        line-height: 1.7;
        font-size: 15px;
    }

    .festival-page .venue-card {
        padding: 24px;
        border-radius: var(--radius);
        background: var(--blue-950);
        color: #ffffff;
        min-height: 270px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .festival-page .venue-card h3 {
        margin: 0 0 12px;
        font-size: 25px;
        line-height: 1;
        color: #fff;
        letter-spacing: -0.04em;
    }

    .festival-page .venue-card p {
        margin: 0;
        color: rgba(255, 255, 255, 0.72);
        line-height: 1.6;
    }

    .festival-page .side-stack {
        position: sticky;
        top: 138px;
        display: grid;
        gap: 16px;
        align-self: start;
    }

    .festival-page .side-card {
        padding: 22px;
        border-radius: var(--radius);
        background: #ffffff;
        border: 1px solid var(--line);
        box-shadow: var(--shadow-soft);
    }

    .festival-page .side-card h3 {
        margin: 0 0 14px;
        color: var(--blue-950);
        font-size: 19px;
        letter-spacing: -0.03em;
    }

    .festival-page .side-map-card {
        padding: 0;
        overflow: hidden;
    }

    .festival-page .side-map-box {
        height: 300px;
        border-bottom: 1px solid var(--line);
        background: #eaf3f8;
        position: relative;
    }

    .festival-page .mapbox-map {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
    }

    .festival-page .map-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 18px;
        text-align: center;
        color: var(--muted);
        font-size: 14px;
        background: #eaf3f8;
    }

    .festival-page .mapboxgl-map {
        font-family: inherit;
    }

    .festival-page .mapbox-map.mapboxgl-map .map-placeholder {
        display: none !important;
    }

    .festival-page .mapboxgl-ctrl-logo,
    .festival-page .mapboxgl-ctrl-attrib {
        transform: scale(0.82);
        transform-origin: bottom left;
    }

    .festival-page .wow-map-marker {
        width: 34px;
        height: 34px;
        background: var(--button-blue);
        border: 4px solid #ffffff;
        box-shadow: 0 10px 28px rgba(7, 29, 51, 0.28);
        transform: rotate(45deg);
        border-radius: 50% 50% 50% 0;
        position: relative;
    }

    .festival-page .wow-map-marker::after {
        content: "";
        position: absolute;
        width: 9px;
        height: 9px;
        left: 50%;
        top: 50%;
        background: #ffffff;
        transform: translate(-50%, -50%);
        border-radius: 50%;
    }

    .festival-page .side-map-body {
        padding: 18px;
    }

    .festival-page .side-map-body p {
        margin: 0 0 14px;
        color: var(--muted);
        font-size: 14px;
        line-height: 1.55;
    }

    .festival-page .mini-list {
        display: grid;
        gap: 12px;
    }

    .festival-page .mini-row {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--line);
        color: var(--muted);
        font-size: 14px;
    }

    .festival-page .mini-row:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .festival-page .mini-row strong {
        color: var(--blue-950);
        text-align: right;
    }

    @media (max-width: 980px) {
        .festival-page .hero-content,
        .festival-page .content-wrap {
            grid-template-columns: 1fr;
        }

        .festival-page .hero-content {
            padding: 28px;
        }

        .festival-page .hero-meta {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .festival-page .gallery,
        .festival-page .split {
            grid-template-columns: 1fr;
        }

        .festival-page .ticket-panel {
            position: relative;
            top: auto;
        }

        .festival-page .side-stack {
            position: relative;
            top: auto;
        }
    }

    @media (max-width: 720px) {
        body.mobile-ticket-visible {
            padding-bottom: 82px;
        }

        .festival-page .mobile-map-hero {
            display: block;
            position: fixed;
            top: var(--wow-header-offset, 0px);
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

        .festival-page .mobile-map-canvas {
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        .festival-page .mobile-map-shade {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.06);
            pointer-events: none;
        }

        .festival-page .mobile-map-shade::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 140px;
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0), rgba(247, 251, 253, 0.98));
            -webkit-backdrop-filter: blur(5px);
            backdrop-filter: blur(5px);
        }

        .festival-page {
            position: relative;
            z-index: 1;
            padding-top: calc(48vh - 98px);
        }

        .festival-page .event-nav {
            display: none;
        }

        .festival-page .hero-wrap,
        .festival-page .content-wrap {
            padding-left: 12px;
            padding-right: 12px;
        }

        .festival-page .hero-wrap {
            margin: 0 auto;
        }

        .festival-page .hero {
            min-height: auto;
            overflow: visible;
            background: transparent;
            box-shadow: none;
        }

        .festival-page .hero-slide,
        .festival-page .hero-overlay,
        .festival-page .hero-watermark {
            display: none;
        }

        .festival-page .hero-content {
            display: block;
            min-height: auto;
            padding: 0;
        }

        .festival-page .hero-main {
            position: relative;
            z-index: 2;
            max-width: none;
            padding: 20px;
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid var(--line);
            box-shadow: 0 -12px 36px rgba(7, 29, 51, 0.14);
            backdrop-filter: blur(18px);
        }

        .festival-page .pill {
            background: #eef6ff;
            border-color: #d6e8fb;
            color: var(--blue-950);
            font-size: 12px;
        }

        .festival-page .hero h1 {
            color: var(--blue-950);
            font-size: 42px;
            line-height: 0.95;
            letter-spacing: -0.065em;
        }

        .festival-page .hero-subtitle {
            margin-top: 16px;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.55;
        }

        .festival-page .hero-actions {
            margin-top: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .festival-page .btn-secondary {
            color: var(--blue-950);
            background: #ffffff;
            border-color: var(--line-dark);
        }

        .festival-page .hero-meta {
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 18px;
        }

        .festival-page .desktop-ticket-buttons {
            display: none;
        }

        .festival-page .mobile-panel-pick {
            display: flex;
        }

        .festival-page .ticket-panel .booking-fields {
            display: none;
        }

        .festival-page .ticket-panel .secure-note {
            display: none;
        }

        .festival-page .meta-card {
            min-height: 82px;
            background: var(--blue-75);
            border: 1px solid var(--line);
            color: var(--blue-950);
        }

        .festival-page .meta-card small {
            color: var(--muted);
        }

        .festival-page .content-wrap {
            margin-top: 22px;
            display: block;
        }

        .festival-page .section {
            padding: 22px;
            margin-bottom: 16px;
        }

        .festival-page .section.flat {
            padding: 0;
        }

        .festival-page .gallery {
            gap: 12px;
        }

        .festival-page .gallery-main {
            min-height: 330px;
        }

        .festival-page .gallery-side .gallery-tile {
            min-height: 230px;
        }

        .festival-page .highlight-grid {
            grid-template-columns: 1fr;
        }

        .festival-page .timeline-item {
            grid-template-columns: 1fr;
        }

        .festival-page .side-stack {
            display: block;
            margin-top: 16px;
        }

        .festival-page .side-map-card {
            display: none;
        }

        .festival-page .side-card {
            margin-bottom: 16px;
        }

        .festival-page .venue-card {
            min-height: 250px;
        }

        .festival-page .mobile-ticket-bar .checkout-button {
            width: auto;
            min-width: 124px;
            min-height: 48px;
            background: var(--button-blue);
            border-color: var(--button-blue);
        }

        .festival-page .wow-booking-modal {
            border-radius: 1px 1px 0 0;
        }
    }

    @media (min-width: 721px) {
        .festival-page .wow-booking-modal,
        .festival-page .wow-booking-backdrop {
            display: none !important;
        }

        .festival-page .mobile-ticket-bar {
            display: none !important;
        }
    }
</style>
@endpush

<div class="festival-page">
    <div class="mobile-map-hero" aria-hidden="true">
        <div class="mobile-map-canvas" id="mobileMap">
            <div class="map-placeholder">Loading venue map...</div>
        </div>
        <div class="mobile-map-shade"></div>
    </div>

    <header class="hero-wrap">
        <section class="hero">
            @foreach($heroImages as $heroImage)
                <div class="hero-slide{{ $loop->first ? ' is-active' : '' }}" style="background-image:url('{{ $heroImage }}');"></div>
            @endforeach

            <div class="hero-overlay"></div>
            <div class="hero-watermark">{!! nl2br(e($eventWatermark)) !!}</div>

            <div class="hero-content">
                <div class="hero-main">
                    <div class="kicker-row">
                        <span class="pill">{{ $eventDateBadge }}</span>
                        <span class="pill">{{ $eventLocationLabel }}</span>
                        <span class="pill">{{ $eventScheduleSummary }}</span>
                    </div>

                    <h1>{{ $eventTitle }}</h1>

                    <p class="hero-subtitle">
                        {{ $eventSummary !== '' ? $eventSummary : 'A calm, soul-resetting weekend of sound healing, meditation, breathwork, live music, wellness stalls and peaceful countryside energy at the historic Bilsington Priory Estate.' }}
                    </p>

                    <div class="hero-actions">
                        @if($showBookingUi ?? true)
                            <a class="btn btn-primary" href="#tickets">Book tickets</a>
                        @endif
                        <a class="btn btn-secondary" href="#schedule">View schedule</a>
                    </div>

                    <div class="hero-meta" aria-label="Festival highlights">
                        @foreach($eventMetaCards as $fact)
                            <div class="meta-card">
                                <small>{{ $fact['label'] }}</small>
                                <strong>{{ $fact['value'] }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if($showBookingUi ?? true)
                    @include('offering.partials.event_buybox')
                @else
                    <div class="card border-0 shadow-sm p-3">
                        <div class="fw-semibold">This event has ended.</div>
                        <div class="text-muted small mt-1">Ticket checkout is no longer available.</div>
                    </div>
                @endif
            </div>
        </section>
    </header>

    <div class="content-wrap">
        <div class="content-main">
            <section class="section flat" aria-label="Festival media">
                <div class="gallery">
                    <div class="gallery-main">
                        @if(($galleryMain['type'] ?? '') === 'youtube')
                            <iframe src="{{ $galleryMain['src'] }}" title="{{ $galleryMain['alt'] ?? 'Festival video preview' }}" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                        @elseif(($galleryMain['type'] ?? '') === 'video')
                            <video src="{{ $galleryMain['src'] }}" poster="{{ $galleryMain['poster'] ?? '' }}" autoplay muted loop playsinline preload="metadata" aria-label="{{ $galleryMain['alt'] ?? 'Festival video preview' }}"></video>
                        @elseif(($galleryMain['type'] ?? '') === 'image')
                            <img src="{{ $galleryMain['src'] }}" alt="{{ $galleryMain['alt'] ?? 'Peaceful countryside festival setting' }}" loading="eager" decoding="async">
                        @else
                            <div class="gallery-empty">
                                <strong>{{ $galleryMain['label'] ?? 'More media coming soon' }}</strong>
                                <span>We’ll add more event media once it’s published.</span>
                            </div>
                        @endif
                        <span class="photo-label">{{ $galleryMain['label'] ?? 'Festival setting' }}</span>
                    </div>

                    <div class="gallery-side">
                        <div class="gallery-tile">
                            @if(($gallerySide[0]['type'] ?? '') === 'youtube')
                                <iframe src="{{ $gallerySide[0]['src'] }}" title="{{ $gallerySide[0]['alt'] ?? 'Festival video preview' }}" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                            @elseif(($gallerySide[0]['type'] ?? '') === 'video')
                                <video src="{{ $gallerySide[0]['src'] }}" poster="{{ $gallerySide[0]['poster'] ?? '' }}" autoplay muted loop playsinline preload="metadata" aria-label="{{ $gallerySide[0]['alt'] ?? 'Festival video preview' }}"></video>
                            @elseif(($gallerySide[0]['type'] ?? '') === 'image')
                                <img src="{{ $gallerySide[0]['src'] }}" alt="{{ $gallerySide[0]['alt'] ?? 'Meditation session' }}" loading="lazy" decoding="async">
                            @else
                                <div class="gallery-empty">
                                    <strong>{{ $gallerySide[0]['label'] ?? 'More media coming soon' }}</strong>
                                    <span>We’ll add more event media once it’s published.</span>
                                </div>
                            @endif
                            <span class="photo-label">{{ $gallerySide[0]['label'] ?? 'Meditation' }}</span>
                        </div>

                        <div class="gallery-tile">
                            @if(($gallerySide[1]['type'] ?? '') === 'youtube')
                                <iframe src="{{ $gallerySide[1]['src'] }}" title="{{ $gallerySide[1]['alt'] ?? 'Festival video preview' }}" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                            @elseif(($gallerySide[1]['type'] ?? '') === 'video')
                                <video src="{{ $gallerySide[1]['src'] }}" poster="{{ $gallerySide[1]['poster'] ?? '' }}" autoplay muted loop playsinline preload="metadata" aria-label="{{ $gallerySide[1]['alt'] ?? 'Festival video preview' }}"></video>
                            @elseif(($gallerySide[1]['type'] ?? '') === 'image')
                                <img src="{{ $gallerySide[1]['src'] }}" alt="{{ $gallerySide[1]['alt'] ?? 'Live music and festival atmosphere' }}" loading="lazy" decoding="async">
                            @else
                                <div class="gallery-empty">
                                    <strong>{{ $gallerySide[1]['label'] ?? 'More media coming soon' }}</strong>
                                    <span>We’ll add more event media once it’s published.</span>
                                </div>
                            @endif
                            <span class="photo-label">{{ $gallerySide[1]['label'] ?? 'Live sound' }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section">
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

            <section class="section" id="schedule">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Event schedule</p>
                        <h2>Two calm days, one big reset.</h2>
                        <p class="section-intro">{{ $eventScheduleIntro }}</p>
                    </div>
                </div>

                @if(! empty($eventScheduleDays))
                    <div class="schedule-tabs" role="tablist" aria-label="Festival schedule days">
                        @foreach($eventScheduleDays as $day)
                            <button class="schedule-tab{{ $loop->first ? ' active' : '' }}" type="button" data-day="{{ $day['id'] }}" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}" aria-controls="schedule-{{ $day['id'] }}">
                                {{ $day['label'] }}
                            </button>
                        @endforeach
                    </div>

                    @foreach($eventScheduleDays as $day)
                        <div class="timeline" id="schedule-{{ $day['id'] }}" role="tabpanel" data-schedule="{{ $day['id'] }}" @if(! $loop->first) hidden @endif>
                            @forelse($day['sessions'] ?? [] as $sessionIndex => $session)
                                @php
                                    $sessionLabel = trim((string) ($session['label'] ?? ''));
                                    $sessionStart = trim((string) ($session['start_time'] ?? ''));
                                    $sessionEnd = trim((string) ($session['end_time'] ?? ''));
                                    $sessionNotes = trim((string) ($session['notes'] ?? ''));
                                @endphp
                                <article class="timeline-item">
                                    <div class="timeline-time">
                                        {{ $sessionStart !== '' || $sessionEnd !== '' ? trim(($sessionStart !== '' ? $sessionStart : 'All day') . ($sessionEnd !== '' ? ' - ' . $sessionEnd : '')) : ($day['label'] ?? 'Day') }}
                                    </div>
                                    <div>
                                        <h3>{{ $sessionLabel !== '' ? $sessionLabel : 'Session ' . ($sessionIndex + 1) }}</h3>
                                        <p>{{ $sessionNotes !== '' ? $sessionNotes : 'Session details will be added here soon.' }}</p>
                                    </div>
                                </article>
                            @empty
                                <article class="timeline-item">
                                    <div class="timeline-time">{{ $day['label'] }}</div>
                                    <div>
                                        <h3>Schedule coming soon</h3>
                                        <p>No sessions for this day yet.</p>
                                    </div>
                                </article>
                            @endforelse
                        </div>
                    @endforeach
                @else
                    <div class="timeline">
                        <article class="timeline-item">
                            <div class="timeline-time">Coming soon</div>
                            <div>
                                <h3>Schedule coming soon</h3>
                                <p>We’ll publish the full event timetable here once it’s confirmed.</p>
                            </div>
                        </article>
                    </div>
                @endif
            </section>

            <section class="section">
                <div class="split">
                    <article class="info-panel">
                        <h3>What’s included</h3>
                        <div>{!! $eventIncludedHtml !== '' ? $eventIncludedHtml : e('Your ticket includes access to sound healing and meditation sessions, workshops, talks, DJ sets, live music and the wellness marketplace with exhibitors and therapists.') !!}</div>
                    </article>

                    <article class="info-panel">
                        <h3>What happens on the day?</h3>
                        <div>{!! $eventWhatHtml !== '' ? $eventWhatHtml : e('Expect a tranquil weekend of guided sessions, immersive sound, mindful experiences, exhibitor stalls and peaceful time outdoors.') !!}</div>
                    </article>
                </div>
            </section>

            <section class="section">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Venue</p>
                        <h2>{{ $eventVenueLabel }}</h2>
                        <p class="section-intro">
                            A historic Kent venue with countryside surroundings, festival space and a peaceful backdrop for the weekend.
                        </p>
                    </div>
                </div>

                <article class="venue-card">
                    <div>
                        <h3>{{ $eventVenueLabel }}</h3>
                        <p>
                            @foreach($venueAddressLines as $line)
                                {{ $line }}@if(! $loop->last)<br>@endif
                            @endforeach
                        </p>
                    </div>

                    <a class="btn btn-primary" href="{{ $eventDirectionsUrl }}" target="_blank" rel="noopener">
                        Open directions
                    </a>
                </article>
            </section>
        </div>

        <aside class="side-stack">
            <div class="side-card side-map-card">
                <div class="side-map-box">
                    <div class="mapbox-map" id="desktopMap" data-event-venue-pins='@json($venuePins)' data-event-venue-locations='@json($venueLocations)'>
                        <div class="map-placeholder">Loading venue map...</div>
                    </div>
                </div>

                <div class="side-map-body">
                    <h3>{{ $eventVenueLabel }}</h3>
                    <p>{{ $eventLocationLabel }}</p>
                    <a class="btn btn-primary" href="{{ $eventDirectionsUrl }}" target="_blank" rel="noopener">
                        Get directions
                    </a>
                </div>
            </div>

            <div class="side-card">
                <h3>Festival snapshot</h3>
                <div class="mini-list">
                    <div class="mini-row">
                        <span>Dates</span>
                        <strong>{{ $eventSnapshotDate }}</strong>
                    </div>
                    <div class="mini-row">
                        <span>Location</span>
                        <strong>{{ $eventVenueLabel }}</strong>
                    </div>
                    <div class="mini-row">
                        <span>Capacity</span>
                        <strong>Up to {{ number_format($eventCapacity) }}</strong>
                    </div>
                    <div class="mini-row">
                        <span>Ticket delivery</span>
                        <strong>Email</strong>
                    </div>
                    <div class="mini-row">
                        <span>Format</span>
                        <strong>{{ $eventMode !== '' ? $eventMode : 'In-person' }}</strong>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>

@push('scripts')
<script data-cfasync="false">
(function () {
    const desktopMapEl = document.getElementById('desktopMap');
    const mobileMapEl = document.getElementById('mobileMap');
    const scheduleTabs = Array.from(document.querySelectorAll('.schedule-tab'));
    const schedules = Array.from(document.querySelectorAll('[data-schedule]'));
    const token = window.WOW_MAPS_KEY || @json(config('services.mapbox.token'));
    const fallbackCoords = @json($eventDirectionsCoords);

    if (!desktopMapEl && !mobileMapEl && !scheduleTabs.length) {
        return;
    }

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

    let desktopMapInstance = null;
    let mobileMapInstance = null;

    function createMarker() {
        const markerEl = document.createElement('div');
        markerEl.className = 'wow-map-marker';
        return markerEl;
    }

    function setMapPlaceholder(message) {
        [desktopMapEl, mobileMapEl].forEach((el) => {
            const placeholder = el?.querySelector('.map-placeholder');
            if (placeholder) {
                placeholder.textContent = message;
            }
        });
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
        if (!query || !token) {
            return null;
        }

        const endpoint = 'https://api.mapbox.com/geocoding/v5/mapbox.places/' + encodeURIComponent(query) + '.json?limit=1&autocomplete=false&types=address,place,poi,locality,neighborhood,postcode,region,district' + buildCountryParam(loc) + '&access_token=' + encodeURIComponent(token);

        try {
            const response = await fetch(endpoint, { credentials: 'omit' });
            if (!response.ok) {
                return null;
            }

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

        return [{
            name: 'Venue',
            lat: Number(fallbackCoords[1]),
            lng: Number(fallbackCoords[0]),
            description: '',
        }];
    }

    function initDesktopMap(resolvedPins) {
        if (!desktopMapEl || desktopMapInstance || !Array.isArray(resolvedPins) || !resolvedPins.length || !(window.mapboxgl && window.mapboxgl.Map)) {
            return;
        }

        desktopMapInstance = new window.mapboxgl.Map({
            container: 'desktopMap',
            style: 'mapbox://styles/mapbox/light-v11',
            center: [Number(resolvedPins[0].lng), Number(resolvedPins[0].lat)],
            zoom: 16,
            pitch: 62,
            bearing: -24,
            antialias: true
        });

        desktopMapInstance.addControl(new window.mapboxgl.NavigationControl({
            visualizePitch: true
        }), 'top-right');

        const bounds = new window.mapboxgl.LngLatBounds();

        resolvedPins.forEach((pin) => {
            if (!pin || !Number.isFinite(Number(pin.lng)) || !Number.isFinite(Number(pin.lat))) {
                return;
            }

            new window.mapboxgl.Marker({
                element: createMarker(),
                anchor: 'bottom'
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

        desktopMapEl.querySelector('.map-placeholder')?.remove();
    }

    function initMobileMap(resolvedPins) {
        if (!mobileMapEl || mobileMapInstance || window.innerWidth > 720 || !Array.isArray(resolvedPins) || !resolvedPins.length || !(window.mapboxgl && window.mapboxgl.Map)) {
            return;
        }

        mobileMapInstance = new window.mapboxgl.Map({
            container: 'mobileMap',
            style: 'mapbox://styles/mapbox/light-v11',
            center: [Number(resolvedPins[0].lng), Number(resolvedPins[0].lat)],
            zoom: 14.35,
            pitch: 0,
            bearing: 0,
            interactive: false,
            attributionControl: true
        });

        resolvedPins.forEach((pin) => {
            if (!pin || !Number.isFinite(Number(pin.lng)) || !Number.isFinite(Number(pin.lat))) {
                return;
            }

            new window.mapboxgl.Marker({
                element: createMarker(),
                anchor: 'bottom'
            })
                .setLngLat([Number(pin.lng), Number(pin.lat)])
                .addTo(mobileMapInstance);
        });

        mobileMapEl.querySelector('.map-placeholder')?.remove();
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

    const heroSlides = Array.from(document.querySelectorAll('.hero-slide'));
    if (heroSlides.length > 1) {
        let heroSlideIndex = 0;
        window.setInterval(() => {
            heroSlides[heroSlideIndex].classList.remove('is-active');
            heroSlideIndex = (heroSlideIndex + 1) % heroSlides.length;
            heroSlides[heroSlideIndex].classList.add('is-active');
        }, 4500);
    }

    function showSchedule(day) {
        scheduleTabs.forEach((tab) => {
            const isActive = tab.dataset.day === day;
            tab.classList.toggle('active', isActive);
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        schedules.forEach((schedule) => {
            schedule.hidden = schedule.dataset.schedule !== day;
        });
    }

    scheduleTabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            showSchedule(tab.dataset.day);
        });
    });

    if (scheduleTabs.length && schedules.length) {
        showSchedule(scheduleTabs[0].dataset.day || schedules[0].dataset.schedule || '');
    }

    if (token) {
        loadMapbox(async () => {
            if (!(window.mapboxgl && window.mapboxgl.Map)) {
                return;
            }

            window.mapboxgl.accessToken = token;

            const resolvedPins = await resolvePins();
            if (!Array.isArray(resolvedPins) || !resolvedPins.length) {
                setMapPlaceholder('Map coming soon.');
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
    } else {
        setMapPlaceholder('Map unavailable: missing Mapbox API key.');
    }
})();
</script>
@endpush
