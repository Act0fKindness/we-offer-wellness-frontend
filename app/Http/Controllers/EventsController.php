<?php

namespace App\Http\Controllers;

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
                'title' => 'Events & Workshops | We Offer Wellness™',
                'description' => 'Discover upcoming wellness events and workshops, online and near you.',
                'robots' => $hasFacets ? 'noindex,follow' : 'index,follow',
                'canonical' => url('/events'),
            ],
            'filters' => $filters,
            'results' => $results,
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $event = WowEventsFeed::find($slug);
        abort_if($event === null, 404);

        $title = (string) ($event['seo_title'] ?? $event['title'] ?? 'Event');

        return view('events.show', [
            'seo' => [
                'title' => $title . ' | We Offer Wellness™',
                'description' => (string) ($event['seo_description'] ?? $event['summary'] ?? 'Explore this event on We Offer Wellness™.'),
                'robots' => 'index,follow',
                'canonical' => url('/events/' . $slug),
            ],
            'event' => $event,
        ]);
    }
}
