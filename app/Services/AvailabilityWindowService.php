<?php

namespace App\Services;

use App\Models\User;
use App\Models\VendorAvailabilityDefault;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class AvailabilityWindowService
{
    public static function extractAvailabilitySettings(?User $user): array
    {
        if (! $user) {
            return self::defaultSettings();
        }

        $settingsData = $user->settings?->settings_data ?? [];

        return [
            'timezone' => (string) Arr::get($settingsData, 'availability_timezone', $user->timezone ?? config('app.timezone', 'Europe/London')),
            'slotInterval' => (int) Arr::get($settingsData, 'slot_interval_minutes', 30),
            'bookingHorizon' => (int) Arr::get($settingsData, 'booking_horizon_days', 30),
            'minNotice' => (int) Arr::get($settingsData, 'min_notice_hours', 12),
            'bufferBefore' => (int) Arr::get($settingsData, 'buffer_before_minutes', 0),
            'bufferAfter' => (int) Arr::get($settingsData, 'buffer_after_minutes', 0),
            'maxPerDay' => (int) Arr::get($settingsData, 'max_bookings_per_day', 8),
        ];
    }

    public static function buildWeeklyWindows(?User $user): array
    {
        $dayMap = [
            'mon' => 'Monday',
            'tue' => 'Tuesday',
            'wed' => 'Wednesday',
            'thu' => 'Thursday',
            'fri' => 'Friday',
            'sat' => 'Saturday',
            'sun' => 'Sunday',
        ];

        if (! $user) {
            return array_reduce(array_keys($dayMap), function ($carry, $key) {
                $carry[$key] = ['enabled' => false, 'windows' => []];
                return $carry;
            }, []);
        }

        $settingsData = $user->settings?->settings_data ?? [];
        $storedRules = Arr::get($settingsData, 'availability_weekly_rules', []);
        $defaults = VendorAvailabilityDefault::where('user_id', $user->id)->get();
        $normalized = [];

        foreach ($dayMap as $key => $label) {
            $rule = is_array($storedRules[$key] ?? null) ? $storedRules[$key] : [];
            $isRuleEnabled = $rule ? (bool) Arr::get($rule, 'enabled', true) : true;

            if ($rule && ! $isRuleEnabled) {
                $normalized[$key] = ['enabled' => false, 'windows' => []];
                continue;
            }

            $windows = array_values(array_filter(array_map(function ($window) {
                $start = self::normalizeTimeString(Arr::get($window, 'start'));
                $end = self::normalizeTimeString(Arr::get($window, 'end'));

                if (! $start || ! $end || strtotime($end) <= strtotime($start)) {
                    return null;
                }

                return compact('start', 'end');
            }, Arr::get($rule, 'windows', []))));

            if (! $windows) {
                $default = $defaults->firstWhere('day_of_week', $label);
                if ($default && $default->is_available) {
                    $start = self::normalizeTimeString($default->start_time);
                    $end = self::normalizeTimeString($default->end_time);
                    if ($start && $end && strtotime($end) > strtotime($start)) {
                        $windows[] = compact('start', 'end');
                    }
                }
            }

            $normalized[$key] = [
                'enabled' => $isRuleEnabled && ! empty($windows),
                'windows' => $windows,
            ];
        }

        return $normalized;
    }

    public static function buildSpecificWindows(iterable $records): array
    {
        $specific = [];

        foreach ($records as $record) {
            $date = $record->date ?? null;
            if (! $date) {
                continue;
            }

            $start = self::normalizeTimeString($record->start_time ?? null);
            $end = self::normalizeTimeString($record->end_time ?? null);
            if (! $start || ! $end || strtotime($end) <= strtotime($start)) {
                continue;
            }

            if (! isset($specific[$date])) {
                $specific[$date] = [
                    'available' => [],
                    'unavailable' => [],
                ];
            }

            $bucket = ! empty($record->is_available) ? 'available' : 'unavailable';
            $specific[$date][$bucket][] = compact('start', 'end');
        }

        foreach ($specific as $date => $adjustments) {
            $specific[$date]['available'] = self::mergeWindows($adjustments['available'] ?? []);
            $specific[$date]['unavailable'] = self::mergeWindows($adjustments['unavailable'] ?? []);
        }

        return $specific;
    }

    public static function resolveDateWindows(Carbon $date, array $weeklyWindows, array $specificWindows = []): array
    {
        $dayKey = self::dayKeyFromCarbon($date);
        $baseConfig = $weeklyWindows[$dayKey] ?? ['enabled' => false, 'windows' => []];
        $baseWindows = [];

        if (is_array($baseConfig) && Arr::has($baseConfig, 'enabled')) {
            if (Arr::get($baseConfig, 'enabled')) {
                $baseWindows = Arr::get($baseConfig, 'windows', []);
            }
        } elseif (is_array($baseConfig)) {
            $baseWindows = $baseConfig;
        }

        $windows = self::mergeWindows($baseWindows);
        $dateKey = $date->format('Y-m-d');
        $adjustments = $specificWindows[$dateKey] ?? ['available' => [], 'unavailable' => []];

        if (! empty($adjustments['available'])) {
            $windows = self::mergeWindows(array_merge($windows, $adjustments['available']));
        }

        foreach (($adjustments['unavailable'] ?? []) as $window) {
            $windows = self::subtractWindow($windows, $window);
        }

        return self::mergeWindows($windows);
    }

    public static function generateSlots(
        array $weeklyWindows,
        array $settings,
        int $durationMinutes,
        int $days = 7,
        ?Carbon $startDate = null,
        array $specificWindows = [],
        iterable $blockedReservations = []
    ): array {
        $timezone = $settings['timezone'] ?? config('app.timezone', 'Europe/London');
        $slotDuration = max(15, $durationMinutes ?: $settings['slotInterval'] ?: 30);
        $slotStep = max($slotDuration, $settings['slotInterval'] ?: $slotDuration);
        $bufferBefore = $settings['bufferBefore'];
        $bufferAfter = $settings['bufferAfter'];
        $minNotice = $settings['minNotice'];
        $anchor = ($startDate ?? Carbon::now($timezone))->copy();
        $minStart = Carbon::now($timezone)->copy()->addHours($minNotice);
        $slotsByDay = [];
        $blockedRanges = self::buildReservationBlocks($blockedReservations, $timezone, $bufferBefore, $bufferAfter);

        for ($dayOffset = 0; $dayOffset < max(1, $days); $dayOffset++) {
            $date = $anchor->copy()->startOfDay()->addDays($dayOffset);
            $dayWindows = self::resolveDateWindows($date, $weeklyWindows, $specificWindows);
            if (empty($dayWindows)) {
                continue;
            }
            $friendlyLabel = $date->format('l, j F');
            $daySlots = [];

            foreach ($dayWindows as $window) {
                $windowStart = self::buildDateTime($date, $window['start'], $timezone);
                $windowEnd = self::buildDateTime($date, $window['end'], $timezone);
                if (! $windowStart || ! $windowEnd) {
                    continue;
                }

                $slotCursor = $windowStart->copy()->addMinutes($bufferBefore);
                $windowBoundary = $windowEnd->copy()->subMinutes($bufferAfter);
                if ($windowBoundary->lte($slotCursor)) {
                    continue;
                }

                while (true) {
                    $slotEnd = $slotCursor->copy()->addMinutes($slotDuration);
                    if ($slotEnd->gt($windowBoundary)) {
                        break;
                    }

                    if ($slotCursor->gte($minStart)) {
                        if (self::slotOverlapsReservation($slotCursor, $slotEnd, $blockedRanges)) {
                            $slotCursor->addMinutes($slotStep);
                            continue;
                        }

                        $daySlots[] = [
                            'start' => $slotCursor->format('H:i'),
                            'end' => $slotEnd->format('H:i'),
                            'iso' => $slotCursor->toIso8601String(),
                        ];
                    }

                    $slotCursor->addMinutes($slotStep);
                    if ($slotCursor->gte($windowBoundary)) {
                        break;
                    }
                }
            }

            if ($daySlots) {
                $slotsByDay[$date->format('Y-m-d')] = [
                    'label' => $friendlyLabel,
                    'slots' => $daySlots,
                ];
            }
        }

        return $slotsByDay;
    }

    private static function mergeWindows(array $windows): array
    {
        $normalized = array_values(array_filter(array_map(function ($window) {
            $start = self::normalizeTimeString(Arr::get($window, 'start'));
            $end = self::normalizeTimeString(Arr::get($window, 'end'));

            if (! $start || ! $end || strtotime($end) <= strtotime($start)) {
                return null;
            }

            return compact('start', 'end');
        }, $windows)));

        if (empty($normalized)) {
            return [];
        }

        usort($normalized, fn ($a, $b) => strcmp($a['start'], $b['start']));

        $merged = [$normalized[0]];

        foreach (array_slice($normalized, 1) as $window) {
            $lastIndex = count($merged) - 1;
            $last = $merged[$lastIndex];

            if (strtotime($window['start']) <= strtotime($last['end'])) {
                if (strtotime($window['end']) > strtotime($last['end'])) {
                    $merged[$lastIndex]['end'] = $window['end'];
                }
                continue;
            }

            $merged[] = $window;
        }

        return $merged;
    }

    private static function subtractWindow(array $windows, array $subtract): array
    {
        $subtractStart = self::normalizeTimeString(Arr::get($subtract, 'start'));
        $subtractEnd = self::normalizeTimeString(Arr::get($subtract, 'end'));

        if (! $subtractStart || ! $subtractEnd || strtotime($subtractEnd) <= strtotime($subtractStart)) {
            return $windows;
        }

        $result = [];

        foreach ($windows as $window) {
            $windowStart = self::normalizeTimeString(Arr::get($window, 'start'));
            $windowEnd = self::normalizeTimeString(Arr::get($window, 'end'));

            if (! $windowStart || ! $windowEnd || strtotime($windowEnd) <= strtotime($windowStart)) {
                continue;
            }

            if (strtotime($subtractStart) >= strtotime($windowEnd) || strtotime($subtractEnd) <= strtotime($windowStart)) {
                $result[] = ['start' => $windowStart, 'end' => $windowEnd];
                continue;
            }

            if (strtotime($windowStart) < strtotime($subtractStart)) {
                $result[] = ['start' => $windowStart, 'end' => $subtractStart];
            }

            if (strtotime($windowEnd) > strtotime($subtractEnd)) {
                $result[] = ['start' => $subtractEnd, 'end' => $windowEnd];
            }
        }

        return self::mergeWindows($result);
    }

    private static function buildReservationBlocks(iterable $reservations, string $timezone, int $bufferBefore, int $bufferAfter): array
    {
        $blocks = [];
        foreach ($reservations as $reservation) {
            $date = $reservation->date ?? null;
            $start = $reservation->start_time ?? null;
            $end = $reservation->end_time ?? null;
            if (! $date || ! $start || ! $end) {
                continue;
            }

            try {
                $slotStart = Carbon::parse("{$date} {$start}", $timezone)->subMinutes($bufferBefore);
                $slotEnd = Carbon::parse("{$date} {$end}", $timezone)->addMinutes($bufferAfter);
            } catch (\Exception $exception) {
                continue;
            }

            if ($slotEnd->lte($slotStart)) {
                continue;
            }

            $blocks[] = compact('slotStart', 'slotEnd');
        }

        return $blocks;
    }

    private static function slotOverlapsReservation(Carbon $slotStart, Carbon $slotEnd, array $blockedRanges): bool
    {
        foreach ($blockedRanges as $block) {
            if ($slotStart->lt($block['slotEnd']) && $slotEnd->gt($block['slotStart'])) {
                return true;
            }
        }

        return false;
    }

    private static function buildDateTime(Carbon $date, ?string $time, string $timezone): ?Carbon
    {
        if (! $time) {
            return null;
        }

        try {
            return Carbon::parse($date->format('Y-m-d') . ' ' . $time, $timezone);
        } catch (\Exception $exception) {
            return null;
        }
    }

    private static function dayKeyFromCarbon(Carbon $date): string
    {
        return match ($date->dayOfWeek) {
            Carbon::MONDAY => 'mon',
            Carbon::TUESDAY => 'tue',
            Carbon::WEDNESDAY => 'wed',
            Carbon::THURSDAY => 'thu',
            Carbon::FRIDAY => 'fri',
            Carbon::SATURDAY => 'sat',
            default => 'sun',
        };
    }

    private static function normalizeTimeString(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $value = trim((string) $value);

        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Exception $exception) {
            return null;
        }
    }

    private static function defaultSettings(): array
    {
        return [
            'timezone' => config('app.timezone', 'Europe/London'),
            'slotInterval' => 30,
            'bookingHorizon' => 30,
            'minNotice' => 12,
            'bufferBefore' => 0,
            'bufferAfter' => 0,
            'maxPerDay' => 8,
        ];
    }
}
