@php
    $activeTaxonomy = $activeTaxonomy ?? null;
    $categoryLabel = $categoryLabel ?? null;
    $typeLabel = $typeLabel ?? null;
    $seg = $seg ?? null;
    $tags = $tags ?? '';
    $rating = $rating ?? null;
    $reviewCount = $reviewCount ?? 0;

    $chipOne = null;
    if ($activeTaxonomy === 'category') {
        $chipOne = $typeLabel ?? $categoryLabel;
    } elseif ($activeTaxonomy === 'type') {
        $chipOne = $categoryLabel ?? $typeLabel;
    } else {
        $chipOne = $categoryLabel ?? $typeLabel;
    }

    $tagBlob = strtolower(trim(($tags ?? '').' '.($product->tags_list ?? '')));
    $isGift = (bool) (
        data_get($product, 'is_gift_card')
        ?? data_get($product, 'is_gift')
        ?? data_get($product, 'gift_card')
    );
    if (! $isGift && ($seg === 'gifts' || ($tagBlob !== '' && str_contains($tagBlob, 'gift')))) {
        $isGift = true;
    }

    $vendor = data_get($product, 'vendor');
    if (!is_array($vendor) && !is_object($vendor)) {
        $providerRelation = data_get($product, 'provider');
        if (is_array($providerRelation) || is_object($providerRelation)) {
            $vendor = $providerRelation;
        } else {
            $vendor = null;
        }
    }
    if ($vendor === null) {
        $practitionerRelation = data_get($product, 'practitioner');
        if (is_array($practitionerRelation) || is_object($practitionerRelation)) {
            $vendor = $practitionerRelation;
        }
    }
    $vendorVerified = (bool) (
        data_get($product, 'vendor_verified')
        ?? data_get($product, 'is_verified_provider')
        ?? data_get($vendor, 'is_verified')
        ?? false
    );

    $views7d = data_get($product, 'views_7d')
        ?? data_get($product, 'metrics.views_7d')
        ?? data_get($product, 'stats.views_7d')
        ?? null;

    $vendorCreatedAt = data_get($product, 'vendor_created_at') ?? data_get($vendor, 'created_at');
    $vendorHasProfile = (bool) ((data_get($vendor, 'photo_url') ?? data_get($vendor, 'headshot_url') ?? data_get($vendor, 'avatar_url'))
        && (data_get($vendor, 'bio') ?? data_get($vendor, 'about')));

    $trustChip = null;
    if ($isGift) {
        $trustChip = 'Instant e-gift';
    } elseif ($vendorVerified) {
        $trustChip = 'Verified practitioner';
    } elseif (($reviewCount ?? 0) >= 20 && ($rating ?? 0) >= 4.8) {
        $trustChip = 'Top Rated';
    } elseif ($views7d !== null && $views7d >= 500) {
        $trustChip = 'Trending';
    } elseif ($vendorCreatedAt) {
        try {
            $yearsWithWow = \Illuminate\Support\Carbon::parse($vendorCreatedAt)->diffInYears(now());
            if ($yearsWithWow >= 2) {
                $trustChip = '2+ years with WOW';
            }
        } catch (\Throwable $e) {
            // Ignore parse issues
        }
    }

    if (! $trustChip && $vendorHasProfile) {
        $trustChip = 'Meet the practitioner';
    }

    if (! $trustChip) {
        $trustChip = 'Hosted on WOW';
    }
@endphp

<div class="badges">
    @if($chipOne)
        <span class="badge badge--warm">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="currentColor" d="M12 3 2 9l10 6 8-4.8V17h2V9L12 3Z"/>
            </svg>
            {{ $chipOne }}
        </span>
    @endif

    @if($trustChip)
        <span class="badge badge--trust">{{ $trustChip }}</span>
    @endif
</div>
