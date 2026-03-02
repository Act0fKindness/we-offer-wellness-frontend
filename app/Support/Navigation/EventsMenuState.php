<?php

namespace App\Support\Navigation;

use App\Support\WowEventsFeed;

class EventsMenuState
{
    public static function make(): array
    {
        $checks = [
            'events' => WowEventsFeed::hasInventory(['type' => 'event']),
            'workshops' => WowEventsFeed::hasInventory(['type' => 'workshop']),
            'online' => WowEventsFeed::hasInventory(['format' => 'online']),
            'near_me' => WowEventsFeed::hasInventory(['format' => 'in_person']),
            'this_week' => WowEventsFeed::hasInventory(['date' => 'this_week']),
            'this_month' => WowEventsFeed::hasInventory(['date' => 'this_month']),
        ];

        $visible = !($checks['events'] === false && $checks['workshops'] === false);

        return [
            'visible' => $visible,
            'links' => [
                'events' => $checks['events'] === true,
                'workshops' => $checks['workshops'] === true,
                'classes' => false,
                'online' => $checks['online'] === true,
                'near_me' => $checks['near_me'] === true,
                'this_week' => $checks['this_week'] === true,
                'this_month' => $checks['this_month'] === true,
            ],
        ];
    }
}
