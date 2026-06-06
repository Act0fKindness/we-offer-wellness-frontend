<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Support\WowEventsFeed;
use Illuminate\Http\Request;

class EventsController extends Controller
{
    public function index(Request $request)
    {
        $type = strtolower((string) $request->query('type', ''));
        $type = in_array($type, ['event', 'workshop'], true) ? $type : '';

        $filters = [
            'type'     => $type,
            'format'   => (string) $request->query('format', ''),
            'location' => (string) $request->query('location', ''),
            'date'     => (string) $request->query('date', ''),
            'sort'     => (string) $request->query('sort', ''),
            'page'     => max(1, (int) $request->query('page', 1)),
            'per_page' => min(48, max(8, (int) $request->query('per_page', 24))),
        ];

        $results = WowEventsFeed::list($filters);
        $items = collect($results['items'] ?? [])
            ->filter(fn ($item) => is_array($item))
            ->map(fn (array $item) => $this->decorateEventItem($item))
            ->values();
        [$upcomingEvents, $pastEvents] = $this->splitEvents($items);

        $hasFacets = (bool) (
            $filters['type'] ||
            $filters['format'] ||
            $filters['location'] ||
            $filters['date'] ||
            $filters['sort'] ||
            $request->has('page') ||
            $request->has('per_page')
        );

        return view('events.index', [
            'seo' => [
                'title' => 'Events | We Offer Wellness™',
                'description' => 'Discover upcoming and past wellbeing events, online and near you.',
                'robots' => $hasFacets ? 'noindex,follow' : 'index,follow',
                'canonical' => url('/events'),
            ],
            'filters' => $filters,
            'results' => $results,
            'upcomingEvents' => $upcomingEvents,
            'pastEvents' => $pastEvents,
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $event = WowEventsFeed::find($slug);
        abort_if($event === null, 404);

        $title = (string) ($event['seo_title'] ?? $event['title'] ?? 'Event');
        $canonical = trim((string) ($event['url'] ?? ''));
        if ($canonical === '') {
            $canonical = url('/events/' . $slug);
        }

        return view('events.show', [
            'seo' => [
                'title' => $title . ' | We Offer Wellness™',
                'description' => (string) ($event['seo_description'] ?? $event['summary'] ?? 'Explore this event on We Offer Wellness™.'),
                'robots' => 'index,follow',
                'canonical' => $canonical,
            ],
            'event' => $event,
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $items
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, array<string, mixed>>}
     */
    private function splitEvents($items): array
    {
        $upcoming = [];
        $past = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            if (! empty($item['display_is_past'])) {
                $past[] = $item;
                continue;
            }

            $upcoming[] = $item;
        }

        $upcoming = collect($upcoming)
            ->sortBy(fn (array $item) => (int) ($item['display_sort_ts'] ?? PHP_INT_MAX))
            ->values()
            ->all();

        $past = collect($past)
            ->sortByDesc(fn (array $item) => (int) ($item['display_sort_ts'] ?? 0))
            ->values()
            ->all();

        return [$upcoming, $past];
    }

    private function decorateEventItem(array $item): array
    {
        $startAt = $this->eventStartAt($item);
        $endAt = $this->eventEndAt($item, $startAt);
        $isPast = $startAt ? $startAt->lt(Carbon::now()) : false;

        $displayDate = $startAt ? $startAt->format('D, j M Y') : 'Date to be confirmed';
        $displayTime = '';
        if ($startAt) {
            $startTime = $startAt->format('g:i A');
            if ($endAt && $endAt->gt($startAt)) {
                $displayTime = $endAt->toDateString() !== $startAt->toDateString()
                    ? $startTime . ' - ' . $endAt->format('D, j M g:i A')
                    : ($endAt->format('g:i A') !== $startTime ? $startTime . ' - ' . $endAt->format('g:i A') : $startTime);
            } else {
                $displayTime = $startTime;
            }
        }

        $displayLocation = trim((string) data_get($item, 'location', data_get($item, 'venue.name', data_get($item, 'event.location', data_get($item, 'event.venue', '')))));
        if ($displayLocation === '') {
            $displayLocation = trim((string) data_get($item, 'venue.address', ''));
        }

        $displaySummary = trim((string) data_get($item, 'summary', data_get($item, 'description_short', data_get($item, 'description', ''))));
        $displayImage = trim((string) data_get($item, 'image', data_get($item, 'image_url', data_get($item, 'featured_image', ''))));
        $displayUrl = trim((string) data_get($item, 'url', data_get($item, 'link', '')));
        if ($displayUrl === '') {
            $slug = trim((string) data_get($item, 'slug', data_get($item, 'handle', '')));
            $displayUrl = $slug !== '' ? url('/events/' . ltrim($slug, '/')) : '';
        } elseif (! str_starts_with($displayUrl, 'http')) {
            $displayUrl = url($displayUrl);
        }

        return array_merge($item, [
            'display_when' => trim($displayDate . ($displayTime !== '' ? ' • ' . $displayTime : '')),
            'display_date' => $displayDate,
            'display_time' => $displayTime,
            'display_location' => $displayLocation,
            'display_summary' => $displaySummary,
            'display_image' => $displayImage,
            'display_url' => $displayUrl,
            'display_badge' => $isPast ? 'Past event' : 'Upcoming event',
            'display_is_past' => $isPast,
            'display_sort_ts' => $startAt?->getTimestamp() ?? 0,
        ]);
    }

    private function eventStartAt(array $item): ?Carbon
    {
        $timezone = $this->eventTimezone($item);
        $date = trim((string) data_get($item, 'when.event.start_date', data_get($item, 'event.start_date', data_get($item, 'start_date', data_get($item, 'date', '')))));
        $time = trim((string) data_get($item, 'when.event.start_time', data_get($item, 'event.start_time', data_get($item, 'start_time', ''))));

        if ($date === '') {
            return null;
        }

        $candidate = trim($date . ($time !== '' ? ' ' . $time : ''));

        try {
            return Carbon::parse($candidate, $timezone);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function eventEndAt(array $item, ?Carbon $startAt = null): ?Carbon
    {
        $timezone = $this->eventTimezone($item);
        $date = trim((string) data_get($item, 'when.event.end_date', data_get($item, 'event.end_date', data_get($item, 'end_date', ''))));
        $time = trim((string) data_get($item, 'when.event.end_time', data_get($item, 'event.end_time', data_get($item, 'end_time', ''))));

        if ($date === '' && ! $startAt) {
            return null;
        }

        $candidateDate = $date !== '' ? $date : ($startAt ? $startAt->toDateString() : '');
        $candidate = trim($candidateDate . ($time !== '' ? ' ' . $time : ''));

        if ($candidate === '') {
            return $startAt ? $startAt->copy() : null;
        }

        try {
            return Carbon::parse($candidate, $timezone);
        } catch (\Throwable $e) {
            return $startAt ? $startAt->copy() : null;
        }
    }

    private function eventTimezone(array $item): string
    {
        $timezone = trim((string) data_get($item, 'when.event.timezone', data_get($item, 'event.timezone', data_get($item, 'timezone', config('app.timezone', 'UTC')))));

        return $timezone !== '' ? $timezone : config('app.timezone', 'UTC');
    }
}
