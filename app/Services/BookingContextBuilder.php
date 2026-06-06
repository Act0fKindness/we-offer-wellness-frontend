<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\OfferingV3;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\VendorAvailability;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingContextBuilder
{
    public function buildForOffering(OfferingV3 $offering, ?int $priceOptionId = null, ?string $variantLabel = null): array
    {
        $offering->loadMissing([
            'category',
            'type',
            'media',
            'vendor.user.settings',
            'vendor.user.bio',
            'vendor.reviews',
        ]);

        return $this->build($offering, $priceOptionId, $variantLabel);
    }

    public function buildForProduct(Product $product, ?int $priceOptionId = null, ?string $variantLabel = null): array
    {
        $product->loadMissing([
            'vendor.user.settings',
            'vendor.user.bio',
            'vendor.reviews',
            'options.values',
            'variants',
        ]);

        return $this->buildForProductRecord($product, $priceOptionId, $variantLabel);
    }

    public function build(OfferingV3 $offering, ?int $priceOptionId = null, ?string $variantLabel = null): array
    {
        return $this->buildForOfferingRecord($offering, $priceOptionId, $variantLabel);
    }

    private function buildForOfferingRecord(OfferingV3 $offering, ?int $priceOptionId = null, ?string $variantLabel = null): array
    {
        $vendor = $offering->vendor;
        if (! $vendor || ! $vendor->user) {
            abort(404, 'Practitioner not found.');
        }

        $user = $vendor->user;
        $availabilitySettings = AvailabilityWindowService::extractAvailabilitySettings($user);
        $weeklyWindows = AvailabilityWindowService::buildWeeklyWindows($user);
        $schedule = DB::table('offering_schedule')->where('offering_id', $offering->id)->first();
        $duration = $this->offeringDurationMinutes($offering, $availabilitySettings, $variantLabel);
        $daysToShow = (int) ($availabilitySettings['bookingHorizon'] ?? 30);
        if ($daysToShow < 1) {
            $daysToShow = 30;
        }
        $daysToShow = min(365, $daysToShow);
        $now = Carbon::now($availabilitySettings['timezone']);
        $startAnchor = $now->copy();
        $rangeEnd = $startAnchor->copy()->addDays($daysToShow)->endOfDay();
        $holdCutoff = $now->copy()->subMinutes(10);

        $specificRecords = VendorAvailability::where('user_id', $user->id)
            ->whereBetween('date', [$startAnchor->toDateString(), $rangeEnd->toDateString()])
            ->orderBy('date')
            ->get();

        $specificWindows = AvailabilityWindowService::buildSpecificWindows($specificRecords);

        $reservationRecords = Reservation::where('user_id', $user->id)
            ->whereBetween('date', [$startAnchor->toDateString(), $rangeEnd->toDateString()])
            ->where(function ($query) use ($holdCutoff) {
                $query->where('is_confirmed', true)
                    ->orWhere('created_at', '>=', $holdCutoff);
            })
            ->orderBy('date')
            ->get();

        $bookingRecords = Booking::where('user_id', $user->id)
            ->whereBetween('date', [$startAnchor->toDateString(), $rangeEnd->toDateString()])
            ->orderBy('date')
            ->get();

        $blockedReservations = $reservationRecords->concat($bookingRecords)->all();
        $slotsByDay = AvailabilityWindowService::generateSlots(
            $weeklyWindows,
            $availabilitySettings,
            $duration,
            $daysToShow,
            $startAnchor,
            $specificWindows,
            $blockedReservations
        );

        $reservationHolds = [];
        foreach ($reservationRecords as $reservation) {
            if ($reservation->is_confirmed) {
                continue;
            }

            $createdAt = $reservation->created_at;
            if (! $createdAt || $createdAt->lt($holdCutoff)) {
                continue;
            }

            $holdUntil = $createdAt->copy()->addMinutes(10);
            if ($holdUntil->lte($createdAt)) {
                continue;
            }

            $dateKey = $reservation->date;
            if (! $dateKey) {
                continue;
            }

            $reservationHolds[$dateKey][$reservation->start_time] = $holdUntil->toIso8601String();
        }

        $settingsData = $user->settings?->settings_data ?? [];
        $practiceLabel = $settingsData['practice_label'] ?? $vendor->vendor_name ?? 'Wellness Practitioner';
        $experienceLabel = $settingsData['experience_years'] ?? null;
        $languageLabel = $user->language
            ? Str::of($user->language)->replace(['_', '-'], ' ')->title()->toString()
            : 'English';
        $bioText = $user->bio?->bio ?? $offering->summary ?? '';
        $profileImage = $user->profile_picture
            ? asset('storage/' . ltrim($user->profile_picture, '/'))
            : null;
        $priceOptions = collect(DB::table('offering_price_options')
            ->where('offering_id', $offering->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get());

        $priceOption = null;
        if ($priceOptionId) {
            $priceOption = $priceOptions->first(fn ($option) => (int) $option->id === (int) $priceOptionId);
        }

        if (! $priceOption) {
            $priceOption = $priceOptions->first();
        }

        $priceAmount = $priceOption->price_amount ?? $offering->price ?? 0;
        $audienceLabel = $priceOption && ! empty($priceOption->name)
            ? (string) $priceOption->name
            : ($priceOption && $priceOption->audience_type
                ? Str::of($priceOption->audience_type)->replace('_', ' ')->title()->toString()
                : '1 Person');
        $priceNote = $priceOption && ($priceOption->pricing_type === 'per_person')
            ? 'Per person'
            : 'Per session';
        $formatLabel = optional($offering->type)->name ?? optional($offering->category)->name ?? 'Wellbeing session';
        $practitionerName = trim($user->name ?? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')));
        if ($practitionerName === '') {
            $practitionerName = 'Wellness Practitioner';
        }
        $reviewAverage = (float) ($vendor->reviews->avg('rating') ?? 0);
        $reviewCount = $vendor->reviews->count();
        $reviewAverage = $reviewAverage ? round($reviewAverage, 1) : 0;

        $bookingPayload = [
            'practitioner' => [
                'name' => $practitionerName,
                'role' => $practiceLabel,
                'bio' => $bioText,
                'image' => $profileImage,
                'rating' => $reviewAverage,
                'ratingCount' => $reviewCount,
                'type' => $formatLabel,
                'category' => optional($offering->category)->name,
                'experience' => $experienceLabel ?? 'Experience not shared',
                'languages' => $languageLabel,
            ],
            'bookingConfig' => [
                'sessionTitle' => $offering->title ?? 'Wellness session',
                'format' => $formatLabel,
                'people' => $audienceLabel,
                'duration' => sprintf('%d Minutes', $duration),
                'pricePence' => (int) round((float) $priceAmount * 100),
                'priceNote' => $priceNote,
            ],
            'slug' => $offering->slug,
            'availabilitySettings' => $availabilitySettings,
            'weeklyWindows' => $weeklyWindows,
            'slotsByDay' => $slotsByDay,
            'duration' => $duration,
            'reservationHolds' => $reservationHolds,
        ];

        return compact(
            'offering',
            'vendor',
            'user',
            'availabilitySettings',
            'weeklyWindows',
            'slotsByDay',
            'duration',
            'startAnchor',
            'rangeEnd',
            'daysToShow',
            'bookingPayload'
        );
    }

    private function buildForProductRecord(Product $product, ?int $priceOptionId = null, ?string $variantLabel = null): array
    {
        $vendor = $product->vendor;
        if (! $vendor || ! $vendor->user) {
            abort(404, 'Practitioner not found.');
        }

        $user = $vendor->user;
        $availabilitySettings = AvailabilityWindowService::extractAvailabilitySettings($user);
        $weeklyWindows = AvailabilityWindowService::buildWeeklyWindows($user);
        $duration = $this->productDurationMinutes($product, $availabilitySettings, $priceOptionId, $variantLabel);
        $daysToShow = (int) ($availabilitySettings['bookingHorizon'] ?? 30);
        if ($daysToShow < 1) {
            $daysToShow = 30;
        }
        $daysToShow = min(365, $daysToShow);
        $now = Carbon::now($availabilitySettings['timezone']);
        $startAnchor = $now->copy();
        $rangeEnd = $startAnchor->copy()->addDays($daysToShow)->endOfDay();
        $holdCutoff = $now->copy()->subMinutes(10);

        $specificRecords = VendorAvailability::where('user_id', $user->id)
            ->whereBetween('date', [$startAnchor->toDateString(), $rangeEnd->toDateString()])
            ->orderBy('date')
            ->get();

        $specificWindows = AvailabilityWindowService::buildSpecificWindows($specificRecords);

        $reservationRecords = Reservation::where('user_id', $user->id)
            ->whereBetween('date', [$startAnchor->toDateString(), $rangeEnd->toDateString()])
            ->where(function ($query) use ($holdCutoff) {
                $query->where('is_confirmed', true)
                    ->orWhere('created_at', '>=', $holdCutoff);
            })
            ->orderBy('date')
            ->get();

        $bookingRecords = Booking::where('user_id', $user->id)
            ->whereBetween('date', [$startAnchor->toDateString(), $rangeEnd->toDateString()])
            ->orderBy('date')
            ->get();

        $blockedReservations = $reservationRecords->concat($bookingRecords)->all();
        $slotsByDay = AvailabilityWindowService::generateSlots(
            $weeklyWindows,
            $availabilitySettings,
            $duration,
            $daysToShow,
            $startAnchor,
            $specificWindows,
            $blockedReservations
        );

        $reservationHolds = [];
        foreach ($reservationRecords as $reservation) {
            if ($reservation->is_confirmed) {
                continue;
            }

            $createdAt = $reservation->created_at;
            if (! $createdAt || $createdAt->lt($holdCutoff)) {
                continue;
            }

            $holdUntil = $createdAt->copy()->addMinutes(10);
            if ($holdUntil->lte($createdAt)) {
                continue;
            }

            $dateKey = $reservation->date;
            if (! $dateKey) {
                continue;
            }

            $reservationHolds[$dateKey][$reservation->start_time] = $holdUntil->toIso8601String();
        }

        $settingsData = $user->settings?->settings_data ?? [];
        $practiceLabel = $settingsData['practice_label'] ?? $vendor->vendor_name ?? 'Wellness Practitioner';
        $experienceLabel = $settingsData['experience_years'] ?? null;
        $languageLabel = $user->language
            ? Str::of($user->language)->replace(['_', '-'], ' ')->title()->toString()
            : 'English';
        $bioText = $user->bio?->bio ?? $product->summary ?? '';
        $profileImage = $user->profile_picture
            ? asset('storage/' . ltrim($user->profile_picture, '/'))
            : null;

        $productMeta = is_array($product->meta_json ?? null) ? $product->meta_json : [];
        $priceAmount = $this->resolveProductPriceAmount($product, $priceOptionId);
        $productName = trim((string) ($product->title ?? 'Wellness session'));
        $reviewAverage = (float) ($vendor->reviews->avg('rating') ?? 0);
        $reviewCount = $vendor->reviews->count();
        $reviewAverage = $reviewAverage ? round($reviewAverage, 1) : 0;

        $bookingPayload = [
            'practitioner' => [
                'name' => trim((string) ($user->name ?? $vendor->vendor_name ?? 'Wellness Practitioner')),
                'role' => $practiceLabel,
                'bio' => $bioText,
                'image' => $profileImage,
                'rating' => $reviewAverage,
                'ratingCount' => $reviewCount,
                'type' => $product->product_type ?: 'experience',
                'category' => optional($product->category)->name,
                'experience' => $experienceLabel ?? 'Experience not shared',
                'languages' => $languageLabel,
            ],
            'bookingConfig' => [
                'sessionTitle' => $productName,
                'format' => $product->product_type ?: 'experience',
                'people' => '1 Person',
                'duration' => sprintf('%d Minutes', $duration),
                'pricePence' => (int) round((float) $priceAmount * 100),
                'priceNote' => 'Per session',
            ],
            'slug' => $product->handle ?: $product->id,
            'availabilitySettings' => $availabilitySettings,
            'slotsByDay' => $slotsByDay,
            'duration' => $duration,
            'reservationHolds' => $reservationHolds,
        ];

        return [
            'product' => $product,
            'vendor' => $vendor,
            'user' => $user,
            'availabilitySettings' => $availabilitySettings,
            'weeklyWindows' => $weeklyWindows,
            'slotsByDay' => $slotsByDay,
            'duration' => $duration,
            'startAnchor' => $startAnchor,
            'rangeEnd' => $rangeEnd,
            'daysToShow' => $daysToShow,
            'bookingPayload' => $bookingPayload,
        ];
    }

    private function offeringDurationMinutes(OfferingV3 $offering, array $settings, ?string $variantLabel = null): int
    {
        $schedule = DB::table('offering_schedule')->where('offering_id', $offering->id)->first();
        $duration = max(15, (int) ($schedule->duration_minutes ?? $settings['slotInterval'] ?? 60));
        if ($variantLabel) {
            $parsed = $this->parseExplicitDurationMinutes($variantLabel);
            if ($parsed > 0) {
                return max(15, $parsed);
            }
        }

        return $duration > 0 ? $duration : 60;
    }

    private function productDurationMinutes(Product $product, array $settings, ?int $priceOptionId = null, ?string $variantLabel = null): int
    {
        if ($variantLabel) {
            $parsed = $this->parseExplicitDurationMinutes($variantLabel);
            if ($parsed > 0) {
                return max(15, $parsed);
            }
        }

        $meta = is_array($product->meta_json ?? null) ? $product->meta_json : [];
        foreach (['duration_minutes', 'duration_mins', 'duration'] as $key) {
            $duration = $this->parseDurationMinutes($meta[$key] ?? null);
            if ($duration > 0) {
                return max(15, $duration);
            }
        }

        $selectedVariant = null;
        if ($priceOptionId !== null) {
            if ($product->relationLoaded('variants')) {
                $selectedVariant = $product->variants->first(fn ($variant) => (int) $variant->id === (int) $priceOptionId);
            }

            if (! $selectedVariant) {
                $selectedVariant = DB::table('product_variants')
                    ->where('product_id', $product->id)
                    ->where('id', $priceOptionId)
                    ->first();
            }
        }

        $selectedVariantDurations = [];
        if ($selectedVariant) {
            foreach (['title'] as $field) {
                $selectedVariantDurations[] = $this->parseExplicitDurationMinutes((string) ($selectedVariant->{$field} ?? ''));
            }
            if (is_array($selectedVariant->metadata ?? null)) {
                foreach (['duration', 'duration_minutes', 'duration_mins', 'session', 'sessions'] as $key) {
                    $value = $selectedVariant->metadata[$key] ?? '';
                    $selectedVariantDurations[] = $this->parseDurationMinutes($value);
                }
            }
        }

        $selectedVariantDurations = array_values(array_filter($selectedVariantDurations, fn ($minutes) => is_int($minutes) && $minutes >= 15));
        if ($selectedVariantDurations) {
            return max($selectedVariantDurations);
        }

        $allVariantDurations = [];
        if ($product->relationLoaded('variants')) {
            foreach ($product->variants as $variant) {
                $allVariantDurations[] = $this->parseExplicitDurationMinutes((string) ($variant->title ?? ''));
                if (is_array($variant->metadata ?? null)) {
                    $allVariantDurations[] = $this->parseDurationMinutes($variant->metadata['duration'] ?? null);
                    $allVariantDurations[] = $this->parseDurationMinutes($variant->metadata['duration_minutes'] ?? null);
                    $allVariantDurations[] = $this->parseDurationMinutes($variant->metadata['duration_mins'] ?? null);
                }
            }
        }

        $sessionTexts = [];
        foreach ($product->options as $option) {
            $name = strtolower(trim((string) ($option->name ?? $option->meta_name ?? '')));
            if ($name === '' || ! str_contains($name, 'session')) {
                continue;
            }

            foreach ($option->values as $value) {
                $raw = is_array($value) ? (string) ($value['value'] ?? '') : (string) $value->value;
                $sessionTexts[] = $raw;
            }
        }

        $sessionDurations = [];
        foreach ($sessionTexts as $text) {
            $duration = $this->parseExplicitDurationMinutes($text);
            if ($duration > 0) {
                $sessionDurations[] = max(15, $duration);
            }
        }

        $fallbackDurations = array_values(array_filter(
            array_merge($allVariantDurations, $sessionDurations),
            fn ($minutes) => is_int($minutes) && $minutes >= 15
        ));

        if ($fallbackDurations) {
            return max($fallbackDurations);
        }

        return 60;
    }

    private function resolveProductPriceAmount(Product $product, ?int $priceOptionId = null): float
    {
        if ($priceOptionId) {
            $variant = DB::table('product_variants')
                ->where('product_id', $product->id)
                ->where('id', $priceOptionId)
                ->first();

            if ($variant && is_numeric($variant->price ?? null)) {
                return (float) $variant->price;
            }
        }

        if (is_numeric($product->price ?? null)) {
            return (float) $product->price;
        }

        return 0.0;
    }

    private function parseDurationMinutes(mixed $value): int
    {
        if (is_numeric($value)) {
            $minutes = (int) $value;
            return $minutes >= 5 ? $minutes : 0;
        }

        $text = strtolower(trim((string) $value));
        if ($text === '') {
            return 0;
        }

        if (preg_match('/(\d+)\s*(hour|hr|hrs|hours)/i', $text, $match)) {
            return (int) $match[1] * 60;
        }

        if (preg_match('/(\d+)\s*(minute|min|mins|minutes)/i', $text, $match)) {
            return (int) $match[1];
        }

        if (preg_match('/\b(\d+)\b/', $text, $match)) {
            return (int) $match[1];
        }

        return 0;
    }

    private function parseExplicitDurationMinutes(mixed $value): int
    {
        $text = strtolower(trim((string) $value));
        if ($text === '') {
            return 0;
        }

        if (preg_match('/(\d+(?:\.\d+)?)\s*(hour|hr|hrs|hours)/i', $text, $match)) {
            return (int) round(((float) $match[1]) * 60);
        }

        if (preg_match('/(\d+(?:\.\d+)?)\s*(minute|min|mins|minutes)/i', $text, $match)) {
            return (int) round((float) $match[1]);
        }

        return 0;
    }
}
