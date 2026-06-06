<?php

namespace App\Support;

use Carbon\Carbon;

class EventListing
{
    /**
     * Determine the timezone to use when interpreting a listing's date fields.
     */
    public static function timezone(mixed $item): string
    {
        $timezone = trim((string) data_get(
            $item,
            'when.event.timezone',
            data_get($item, 'event.timezone', data_get($item, 'timezone', data_get($item, 'meta_json.timezone', config('app.timezone', 'UTC'))))
        ));

        return $timezone !== '' ? $timezone : config('app.timezone', 'UTC');
    }

    /**
     * Parse the earliest start timestamp we can infer from the item.
     */
    public static function startAt(mixed $item): ?Carbon
    {
        $timezone = self::timezone($item);
        $date = self::dateValue($item, [
            'when.event.start_date',
            'event.start_date',
            'start_date',
            'date',
            'meta_json.start_date',
            'meta_json.date',
        ]);
        $time = self::dateValue($item, [
            'when.event.start_time',
            'event.start_time',
            'start_time',
            'meta_json.start_time',
        ]);

        if ($date === '') {
            return null;
        }

        $raw = $date;
        if ($time !== '') {
            $raw .= ' ' . $time;
        }

        try {
            return Carbon::parse($raw, $timezone);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Parse the latest end timestamp we can infer from the item.
     */
    public static function endAt(mixed $item, ?Carbon $startAt = null): ?Carbon
    {
        $timezone = self::timezone($item);
        $date = self::dateValue($item, [
            'when.event.end_date',
            'event.end_date',
            'end_date',
            'meta_json.end_date',
        ]);
        $time = self::dateValue($item, [
            'when.event.end_time',
            'event.end_time',
            'end_time',
            'meta_json.end_time',
        ]);

        if ($date === '' && ! $startAt) {
            return null;
        }

        $rawDate = $date !== '' ? $date : ($startAt ? $startAt->toDateString() : '');
        if ($rawDate === '') {
            return null;
        }

        $raw = $rawDate;
        if ($time !== '') {
            $raw .= ' ' . $time;
        }

        try {
            $parsed = Carbon::parse($raw, $timezone);
        } catch (\Throwable $e) {
            $parsed = $startAt ? $startAt->copy() : null;
        }

        if (! $parsed) {
            return $startAt ? $startAt->copy() : null;
        }

        if ($time === '' && ! self::hasTimeComponent($rawDate)) {
            return $parsed->copy()->endOfDay();
        }

        return $parsed;
    }

    /**
     * Return true when the listing has a date in the past and should be hidden from feeds.
     */
    public static function isPast(mixed $item, ?Carbon $now = null): bool
    {
        $startAt = self::startAt($item);
        $endAt = self::endAt($item, $startAt);

        if (! $startAt && ! $endAt) {
            return false;
        }

        $timezone = self::timezone($item);
        $now = $now ? $now->copy()->setTimezone($timezone) : Carbon::now($timezone);

        $effectiveEnd = $endAt ?: $startAt;
        if (! $effectiveEnd) {
            return false;
        }

        if ($endAt && ! self::hasExplicitTime($item, [
            'when.event.end_date',
            'event.end_date',
            'end_date',
            'meta_json.end_date',
        ], [
            'when.event.end_time',
            'event.end_time',
            'end_time',
            'meta_json.end_time',
        ])) {
            $effectiveEnd = $effectiveEnd->copy()->endOfDay();
        } elseif (! $endAt && $startAt && ! self::hasExplicitTime($item, [
            'when.event.start_date',
            'event.start_date',
            'start_date',
            'date',
            'meta_json.start_date',
            'meta_json.date',
        ], [
            'when.event.start_time',
            'event.start_time',
            'start_time',
            'meta_json.start_time',
        ])) {
            $effectiveEnd = $effectiveEnd->copy()->endOfDay();
        }

        return $effectiveEnd->lt($now);
    }

    /**
     * Return true if the item appears to have any scheduled date at all.
     */
    public static function hasScheduledDate(mixed $item): bool
    {
        return self::startAt($item) !== null || self::endAt($item) !== null;
    }

    /**
     * Return true when the listing looks like an event/workshop entry rather than a general service.
     */
    public static function isEventLike(mixed $item): bool
    {
        $textValue = static function (mixed $value): string {
            if (is_array($value)) {
                $value = reset($value);
            }

            if (is_object($value)) {
                $value = data_get($value, 'name', data_get($value, 'title', data_get($value, 'slug', '')));
            }

            return trim((string) $value);
        };

        $scheduleType = strtolower(trim((string) data_get($item, 'when.type', data_get($item, 'event.type', ''))));
        if (in_array($scheduleType, ['event', 'workshop'], true)) {
            return true;
        }

        $haystack = strtolower(trim(implode(' ', array_filter([
            $textValue(data_get($item, 'title')),
            $textValue(data_get($item, 'slug')),
            $textValue(data_get($item, 'handle')),
            $textValue(data_get($item, 'type')),
            $textValue(data_get($item, 'type.name')),
            $textValue(data_get($item, 'type_label')),
            $textValue(data_get($item, 'product_type')),
            $textValue(data_get($item, 'category')),
            $textValue(data_get($item, 'category.name')),
            $textValue(data_get($item, 'summary')),
            $textValue(data_get($item, 'description')),
            $textValue(data_get($item, 'event.title')),
        ]))));

        if ($haystack === '') {
            return self::hasScheduledDate($item);
        }

        foreach ([
            'event',
            'events',
            'workshop',
            'workshops',
            'seminar',
            'masterclass',
            'webinar',
            'conference',
            'meetup',
            'talk',
            'festival',
            'circle',
        ] as $keyword) {
            if (str_contains($haystack, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private static function dateValue(mixed $item, array $paths): string
    {
        foreach ($paths as $path) {
            $value = trim((string) data_get($item, $path, ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private static function hasTimeComponent(string $value): bool
    {
        $value = trim($value);
        if ($value === '') {
            return false;
        }

        return str_contains($value, 'T') || (bool) preg_match('/\b\d{1,2}:\d{2}\b/', $value);
    }

    private static function hasExplicitTime(mixed $item, array $datePaths, array $timePaths): bool
    {
        foreach ($datePaths as $path) {
            if (self::hasTimeComponent((string) data_get($item, $path, ''))) {
                return true;
            }
        }

        foreach ($timePaths as $path) {
            if (trim((string) data_get($item, $path, '')) !== '') {
                return true;
            }
        }

        return false;
    }
}
