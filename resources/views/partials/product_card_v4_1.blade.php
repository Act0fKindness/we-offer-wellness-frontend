@php
    $slug = \Illuminate\Support\Str::slug($product->title ?: (string) $product->id);
    $url = app(\App\Services\SeoStructureService::class)->canonicalProductUrl($product);
    $sourceVersion = strtolower(trim((string) data_get($product, 'source_version', '')));

    $toLower = function ($value) {
        return function_exists('mb_strtolower') ? mb_strtolower((string) $value, 'UTF-8') : strtolower((string) $value);
    };
    $titleCase = function ($value) {
        return function_exists('mb_convert_case') ? mb_convert_case((string) $value, MB_CASE_TITLE, 'UTF-8') : ucwords((string) $value);
    };
    $normalizeTypeLabel = function ($value) use ($toLower, $titleCase) {
        $raw = trim((string) $value);
        if ($raw === '') {
            return 'Experience';
        }

        $normalized = $toLower(str_replace(['_', '-'], ' ', $raw));
        $map = [
            'therapies' => 'Therapy',
            'therapy' => 'Therapy',
            'workshops' => 'Workshop',
            'workshop' => 'Workshop',
            'events' => 'Event',
            'event' => 'Event',
            'classes' => 'Class',
            'class' => 'Class',
            'retreats' => 'Retreat',
            'retreat' => 'Retreat',
            'experiences' => 'Experience',
            'experience' => 'Experience',
        ];

        return $map[$normalized] ?? $titleCase($normalized);
    };

    $title = trim((string) ($product->title ?? 'Untitled'));
    $titleFormatted = $titleCase($toLower($title));
    $image = $product->getFirstImageUrl();
    $hasDisplayableImage = method_exists($product, 'hasDisplayableImage')
        ? $product->hasDisplayableImage()
        : ! str_contains((string) $image, 'no-product-image.jpg');

    $typeCandidate = data_get($product, 'type.name')
        ?? data_get($product, 'type_label')
        ?? data_get($product, 'type_name');
    if ($typeCandidate === null) {
        $rawType = data_get($product, 'type');
        if (is_string($rawType) || is_numeric($rawType)) {
            $typeCandidate = (string) $rawType;
        }
    }

    $typeRaw = trim((string) ($typeCandidate ?? ($product->product_type ?? '')));
    $typeNormalized = $toLower(str_replace(['_', '-'], ' ', $typeRaw));
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
    $categoryLabel = $categoryRaw ? $titleCase($toLower((string) $categoryRaw)) : null;
    $categoryBadgeLabel = $categoryLabel ?? $typeLabel;

    $eventSource = data_get($product, 'when.event', data_get($product, 'event', []));
    if (is_object($eventSource)) {
        $eventSource = (array) $eventSource;
    }
    if (! is_array($eventSource)) {
        $eventSource = [];
    }

    $hasEventSchedule = strtolower((string) data_get($product, 'when.type', '')) === 'event'
        || filled(data_get($product, 'date'))
        || filled(data_get($product, 'start_date'))
        || filled(data_get($product, 'end_date'))
        || filled(data_get($product, 'start_time'))
        || filled(data_get($product, 'end_time'))
        || ! empty($eventSource['dates'] ?? [])
        || ! empty($eventSource['upcoming_dates'] ?? [])
        || ! empty($eventSource['availability_dates'] ?? [])
        || ! empty(data_get($product, 'event.dates', []))
        || ! empty(data_get($product, 'when.event.dates', []));

    $isEventLike = $hasEventSchedule
        || str_contains($typeNormalized, 'event')
        || str_contains($toLower((string) $categoryRaw), 'event');

    $giftHaystack = $toLower(implode(' ', array_filter([
        $title,
        $slug,
        $categoryRaw,
        $typeRaw,
        $product->product_type ?? null,
        data_get($product, 'summary'),
        data_get($product, 'benefit'),
    ])));

    $isGiftCard = (bool) preg_match('/gift\s*card|giftcard|voucher|e-?gift/i', $giftHaystack);

    $isTherapyLike = ! $isGiftCard
        && ! $isEventLike
        && (
            $typeRaw === ''
            || str_contains($typeNormalized, 'therap')
            || str_contains($typeNormalized, 'experience')
            || str_contains($toLower((string) $url), '/therapies/')
            || str_contains($toLower((string) $url), '/therapy/')
            || str_contains($toLower((string) $categoryRaw), 'therap')
            || str_contains($toLower((string) $categoryRaw), 'experience')
        );

    $priceMin = $product->variants_min_price ?? ($product->price ?? null);
    if (is_numeric($priceMin) && $priceMin > 1000 && $priceMin % 100 === 0) {
        $priceMin = $priceMin / 100;
    }
    $compareMin = $product->variants_min_compare ?? ($product->compare_at_price ?? null);
    if (is_numeric($compareMin) && $compareMin > 1000 && $compareMin % 100 === 0) {
        $compareMin = $compareMin / 100;
    }

    $vendorUser = data_get($product, 'vendor.user');
    $starterPlan = $vendorUser instanceof \App\Models\User
        ? $vendorUser->isStarterPlan()
        : strtolower(trim((string) data_get($product, 'plan_key', ''))) === 'starter';
    $businessAcceleratorPlan = $vendorUser instanceof \App\Models\User
        ? $vendorUser->isBusinessAcceleratorPlan()
        : \Illuminate\Support\Str::slug((string) data_get($product, 'plan_key', '')) === 'business-accelerator';

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
    $filledStars = $reviewCount > 0 ? max(0, min(5, (int) round((float) $rating))) : 0;
    $reviewSummary = $reviewCount > 0
        ? number_format((float) $rating, 1) . ' · ' . $reviewCount . ' review' . ($reviewCount === 1 ? '' : 's')
        : 'Be the first to review';

    $provider = $starterPlan
        ? 'Wellness practitioner'
        : trim((string) (
            $product->vendor_name
            ?? data_get($product, 'vendor.vendor_name')
            ?? ''
        ));
    $providerFormatted = $provider !== ''
        ? ($starterPlan ? $provider : $titleCase($toLower(str_replace('_', ' ', $provider))))
        : null;

    $locations = $product->getLocations();
    $hasOnline = collect($locations)->contains(fn ($location) => str_contains(strtolower(trim((string) $location)), 'online'));
    $physical = array_values(array_filter($locations, fn ($location) => ! str_contains(strtolower(trim((string) $location)), 'online')));
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
    $fomoText = trim((string) ($product->fomo_text ?? ''));
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
    $signalText = $fomoText !== ''
        ? $fomoText
        : ($exclusiveOnline
            ? 'Exclusively online'
            : ($remainingCount > 0
                ? '+' . $remainingCount . ' more locations'
                : ($product->next_label ?? $product->next ?? null ? 'Next: ' . ($product->next_label ?? $product->next) : null)));

    $durationLabel = $product->duration ?? null;

    $availabilityDays = [];
    try {
        $defaultAvailability = collect(data_get($vendorUser, 'defaultAvailability', data_get($vendorUser, 'default_availability', [])));
        foreach ($defaultAvailability as $row) {
            $dayValue = data_get($row, 'day_of_week');
            $available = data_get($row, 'is_available', true);
            if (! filter_var($available, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) && (string) $available !== '1') {
                continue;
            }
            $day = is_numeric($dayValue) ? (int) $dayValue : null;
            if ($day === null) {
                $dayName = strtolower(trim((string) $dayValue));
                $map = [
                    'mon' => 1, 'monday' => 1,
                    'tue' => 2, 'tues' => 2, 'tuesday' => 2,
                    'wed' => 3, 'wednesday' => 3,
                    'thu' => 4, 'thur' => 4, 'thurs' => 4, 'thursday' => 4,
                    'fri' => 5, 'friday' => 5,
                    'sat' => 6, 'saturday' => 6,
                    'sun' => 7, 'sunday' => 7,
                ];
                $day = $map[$dayName] ?? null;
            }
            if ($day !== null) {
                $availabilityDays[] = ($day === 7) ? 0 : $day;
            }
        }
        $availabilityDays = array_values(array_unique($availabilityDays));
        $dayOrder = [1, 2, 3, 4, 5, 6, 0];
        usort($availabilityDays, function ($left, $right) use ($dayOrder) {
            return array_search($left, $dayOrder, true) <=> array_search($right, $dayOrder, true);
        });
    } catch (\Throwable $e) {
        $availabilityDays = [];
    }

    $calendarLabel = $availabilityDays ? 'Availability calendar' : 'Request day/time';
    $calendarNote = $availabilityDays ? 'Live calendar' : 'Practitioner confirms';
    $availabilityClass = $availabilityDays ? 'has-availability' : 'needs-availability';
    $availabilityBadgeLabel = $hasOnline && count($physicalShort) > 0
        ? 'In-person + Online'
        : ($hasOnline ? 'Online' : ($primary ? 'In-person' : 'By request'));
@endphp

@if($isGiftCard)
    @include('partials.product_card_v4_giftcard', ['product' => $product, 'preferredLocation' => $preferredLocation ?? null])
@elseif($isEventLike)
    @include('partials.product_card_v4_event_1', ['product' => $product, 'preferredLocation' => $preferredLocation ?? null])
@else
@once
    <style>
      .product-v4-1-card-scope{
        --pv41-ink:#101828;
        --pv41-muted:#667085;
        --pv41-line:#dde3ea;
        --pv41-soft:#edf0f2;
        --pv41-soft-2:#f8fafc;
        --pv41-green:#4f9381;
        --pv41-green-dark:#417c6d;
        --pv41-green-soft:#e8f5f1;
        --pv41-gold-soft:#ffe5b3;
        --pv41-gold-text:#6f4b10;
        --pv41-blue-soft:#e8f0ff;
        --pv41-blue-text:#254a85;
        --pv41-shadow:0 12px 34px rgba(16,24,40,.045);
        --pv41-radius:13px;
      }

      .product-v4-1-card-scope .product-v4-1-card{
        position:relative;
        display:flex;
        flex-direction:column;
        width:280px;
        min-width:280px;
        max-width:280px;
        min-height:492px;
        overflow:hidden;
        background:#fff;
        border:1px solid rgba(16,24,40,.18);
        border-radius:var(--pv41-radius);
        box-shadow:var(--pv41-shadow);
        transition:transform 180ms ease, border-color 180ms ease, box-shadow 180ms ease;
        cursor:pointer;
      }

      .product-v4-1-card-scope .product-v4-1-card:hover,
      .product-v4-1-card-scope .product-v4-1-card:focus-within{
        transform:translateY(-3px);
        border-color:rgba(79,147,129,.42);
        box-shadow:0 20px 48px rgba(16,24,40,.085);
      }

      .product-v4-1-card__surface-link{
        position:absolute;
        inset:0;
        z-index:4;
      }

      .product-v4-1-card__media,
      .product-v4-1-card__body,
      .product-v4-1-card__footer{
        position:relative;
        z-index:2;
        pointer-events:none;
      }

      .product-v4-1-card__media{
        position:relative;
        height:176px;
        overflow:hidden;
        background:
          radial-gradient(circle at 24% 28%, rgba(79,147,129,.14), transparent 30%),
          radial-gradient(circle at 74% 70%, rgba(37,74,133,.10), transparent 32%),
          linear-gradient(135deg, #eef2f4 0%, #f8fbfd 100%);
      }

      .product-v4-1-card__media img,
      .product-v4-1-card__fallback{
        width:100%;
        height:100%;
        display:block;
        object-fit:cover;
      }

      .product-v4-1-card__media img{
        transition:transform 240ms ease;
      }

      .product-v4-1-card:hover .product-v4-1-card__media img{
        transform:scale(1.035);
      }

      .product-v4-1-card__shade{
        position:absolute;
        inset:0 0 auto 0;
        height:72px;
        background:linear-gradient(180deg, rgba(16,24,40,.34), rgba(16,24,40,0));
        pointer-events:none;
      }

      .product-v4-1-card__signal{
        position:absolute;
        top:10px;
        left:10px;
        z-index:3;
        display:inline-flex;
        align-items:center;
        min-height:28px;
        padding:0 10px;
        border:1px solid transparent;
        border-radius:999px;
        background:rgba(255,247,237,.94);
        color:#b54708;
        font-size:11.5px;
        font-weight:700;
        white-space:nowrap;
        backdrop-filter:blur(10px);
        box-shadow:0 10px 22px rgba(16,24,40,.10);
      }

      .product-v4-1-card__badges{
        position:absolute;
        left:10px;
        bottom:10px;
        z-index:3;
        display:flex;
        flex-wrap:wrap;
        gap:6px;
      }

      .product-v4-1-card__badge{
        min-height:26px;
        display:inline-flex;
        align-items:center;
        border-radius:999px;
        padding:0 9px;
        font-size:11px;
        font-weight:700;
        backdrop-filter:blur(8px);
        white-space:nowrap;
      }

      .product-v4-1-card__badge--gold{
        background:rgba(255,229,179,.96);
        color:#6f4b10;
        border:1px solid rgba(240,200,121,.9);
      }

      .product-v4-1-card__badge--blue{
        background:rgba(232,240,255,.96);
        color:#254a85;
        border:1px solid rgba(199,216,251,.9);
      }

      .product-v4-1-card__actions,
      .product-v4-1-card__button,
      .premium-badge-holder,
      .premium-badge-button{
        pointer-events:auto;
      }

      .premium-badge-holder{
        position:absolute;
        top:16px;
        right:16px;
        z-index:20;
        width:52px;
        height:52px;
        overflow:visible;
      }

      .premium-badge-drawer{
        position:absolute;
        top:0;
        right:0;
        height:52px;
        width:52px;
        display:flex;
        align-items:center;
        justify-content:flex-end;
        gap:9px;
        overflow:hidden;
        border:1px solid rgba(255,255,255,.28);
        border-radius:999px;
        background:rgba(16,151,150,.72);
        box-shadow:0 12px 26px rgba(17,24,39,.16), inset 0 1px 0 rgba(255,255,255,.14);
        backdrop-filter:blur(14px) saturate(145%);
        -webkit-backdrop-filter:blur(14px) saturate(145%);
        transition:width 340ms cubic-bezier(.2,.8,.2,1), height 220ms ease, border-color 220ms ease, background 220ms ease, box-shadow 220ms ease, transform 220ms ease;
      }

      .premium-badge-holder:hover .premium-badge-drawer,
      .premium-badge-holder:focus-within .premium-badge-drawer{
        width:250px;
        height:56px;
        border-color:rgba(255,255,255,.42);
        background:rgba(16,151,150,.86);
        box-shadow:0 20px 48px rgba(17,24,39,.24), inset 0 1px 0 rgba(255,255,255,.18);
        transform:translateY(-2px);
      }

      .premium-badge-copy{
        width:174px;
        min-width:174px;
        padding-left:16px;
        opacity:0;
        transform:translateX(18px);
        transition:opacity 220ms ease 90ms, transform 280ms cubic-bezier(.2,.8,.2,1) 70ms;
      }

      .premium-badge-holder:hover .premium-badge-copy,
      .premium-badge-holder:focus-within .premium-badge-copy{
        opacity:1;
        transform:translateX(0);
      }

      .premium-badge-title{
        margin:0;
        color:#fff;
        font-size:14px;
        font-weight:400;
        line-height:1.1;
        letter-spacing:0;
        white-space:nowrap;
        text-shadow:0 1px 8px rgba(0,0,0,.16);
      }

      .premium-badge-small{
        display:block;
        margin-top:3px;
        color:rgba(255,255,255,.88);
        font-size:11px;
        font-weight:400;
        text-transform:uppercase;
        line-height:1.15;
        white-space:nowrap;
        text-shadow:0 1px 8px rgba(0,0,0,.12);
      }

      .premium-badge-button{
        position:relative;
        z-index:2;
        flex:0 0 46px;
        width:46px;
        height:46px;
        margin-right:3px;
        padding:0;
        border:0;
        border-radius:999px;
        background:rgba(255,255,255,.96);
        display:flex;
        align-items:center;
        justify-content:center;
        cursor:pointer;
        box-shadow:0 7px 18px rgba(17,24,39,.14);
        transition:transform 260ms cubic-bezier(.2,.8,.2,1), box-shadow 220ms ease, background 220ms ease;
      }

      .premium-badge-holder:hover .premium-badge-button,
      .premium-badge-holder:focus-within .premium-badge-button{
        transform:scale(1.055) rotate(9deg);
        background:#fff;
        box-shadow:0 10px 24px rgba(17,24,39,.18);
      }

      .premium-badge-button:hover,
      .premium-badge-button:focus-visible{
        outline:none;
      }

      .premium-badge-button img{
        width:36px;
        height:36px;
        display:block;
        object-fit:contain;
        filter:drop-shadow(0 3px 5px rgba(103,70,14,.24));
      }

      .premium-badge-sheen{
        position:absolute;
        top:-45%;
        left:-80%;
        width:70px;
        height:140px;
        background:linear-gradient(90deg, transparent, rgba(255,255,255,.36), transparent);
        transform:rotate(24deg);
        opacity:0;
        pointer-events:none;
      }

      .premium-badge-holder:hover .premium-badge-sheen,
      .premium-badge-holder:focus-within .premium-badge-sheen{
        animation:premiumSheen 900ms ease forwards;
      }

      @keyframes premiumSheen{
        0%{ left:-80%; opacity:0; }
        16%{ opacity:1; }
        100%{ left:150%; opacity:0; }
      }

      .product-v4-1-card__body{
        flex:1;
        display:flex;
        flex-direction:column;
        gap:8px;
        padding:13px 14px 12px;
      }

      .product-v4-1-card__type{
        color:var(--pv41-green);
        font-size:11px;
        font-weight:700;
        letter-spacing:.12em;
        text-transform:uppercase;
      }

      .product-v4-1-card__title{
        display:-webkit-box;
        min-height:45px;
        margin:0;
        overflow:hidden;
        color:var(--pv41-ink);
        font-size:20px;
        font-weight:500;
        line-height:1.08;
        letter-spacing:-.045em;
        -webkit-box-orient:vertical;
        -webkit-line-clamp:2;
      }

      .product-v4-1-card:hover .product-v4-1-card__title,
      .product-v4-1-card:focus-within .product-v4-1-card__title{
        color:var(--pv41-green);
      }

      .product-v4-1-card__provider{
        margin:0;
        color:var(--pv41-muted);
        font-size:12.75px;
      }

      .product-v4-1-card__rating{
        display:flex;
        align-items:center;
        gap:7px;
        color:var(--pv41-ink);
        font-size:12.25px;
      }

      .product-v4-1-card__stars{
        display:inline-flex;
        gap:2px;
      }

      .product-v4-1-card__star{
        width:14px;
        height:14px;
        display:inline-block;
        background:#f5c84b;
        -webkit-mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23000' d='M11.083 5.104c.35-.8 1.485-.8 1.834 0l1.752 4.022a1 1 0 0 0 .84.597l4.463.342c.9.069 1.255 1.2.556 1.771l-3.33 2.723a1 1 0 0 0-.337 1.016l1.03 4.119c.214.858-.71 1.552-1.474 1.106l-3.913-2.281a1 1 0 0 0-1.008 0L7.583 20.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1 1 0 0 0 6.8 14.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1 1 0 0 0 .84-.597l1.753-4.022Z'/%3E%3C/svg%3E") center/contain no-repeat;
                mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23000' d='M11.083 5.104c.35-.8 1.485-.8 1.834 0l1.752 4.022a1 1 0 0 0 .84.597l4.463.342c.9.069 1.255 1.2.556 1.771l-3.33 2.723a1 1 0 0 0-.337 1.016l1.03 4.119c.214.858-.71 1.552-1.474 1.106l-3.913-2.281a1 1 0 0 0-1.008 0L7.583 20.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1 1 0 0 0 6.8 14.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1 1 0 0 0 .84-.597l1.753-4.022Z'/%3E%3C/svg%3E") center/contain no-repeat;
      }

      .product-v4-1-card__star.is-empty{
        background:#d0d5dd;
      }

      .product-v4-1-card__rating-copy{
        color:var(--pv41-ink);
      }

      .product-v4-1-card__summary{
        display:-webkit-box;
        min-height:calc(1.42em * 3);
        max-height:calc(1.42em * 3);
        margin:0;
        overflow:hidden;
        color:#344054;
        font-size:12.75px;
        line-height:1.42;
        -webkit-box-orient:vertical;
        -webkit-line-clamp:3;
      }

      .product-v4-1-card__meta{
        display:flex;
        flex-wrap:wrap;
        gap:6px;
        min-height:26px;
      }

      .product-v4-1-card__chip{
        min-height:27px;
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:0 9px;
        border:1px solid rgba(79,147,129,.24);
        border-radius:999px;
        background:var(--pv41-green-soft);
        color:#2f6f60;
        font-size:12.25px;
        white-space:nowrap;
      }

      .product-v4-1-card__chip--blue{
        border-color:#c7d8fb;
        background:var(--pv41-blue-soft);
        color:var(--pv41-blue-text);
      }

      .product-v4-1-card__availability{
        margin-top:4px;
        min-height:62px;
        border:1px solid rgba(79,147,129,.24);
        border-radius:11px;
        background:linear-gradient(180deg, rgba(232,245,241,.64), rgba(255,255,255,.94));
        padding:8px;
      }

      .product-v4-1-card__availability.has-availability{
        border-color:rgba(79,147,129,.24);
        background:linear-gradient(180deg, rgba(232,245,241,.64), rgba(255,255,255,.94));
      }

      .product-v4-1-card__availability.needs-availability{
        border-color:rgba(237,137,54,.28);
        background:linear-gradient(180deg, rgba(255,244,230,.92), rgba(255,255,255,.98));
      }

      .product-v4-1-card__availability-top{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:9px;
        margin-bottom:7px;
      }

      .product-v4-1-card__availability-label{
        display:flex;
        align-items:center;
        gap:6px;
        color:#2f6f60;
        font-size:11.7px;
        font-weight:800;
      }

      .product-v4-1-card__availability.needs-availability .product-v4-1-card__availability-label{
        color:#b54708;
      }

      .product-v4-1-card__availability-note{
        color:var(--pv41-muted);
        font-size:10.9px;
        white-space:nowrap;
      }

      .product-v4-1-card__availability.needs-availability .product-v4-1-card__availability-note{
        color:#c2410c;
      }

      .product-v4-1-card__days{
        display:grid;
        grid-template-columns:repeat(7, minmax(0, 1fr));
        gap:3px;
      }

      .product-v4-1-card__day{
        height:21px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border-radius:6px;
        font-size:10px;
        font-weight:800;
      }

      .product-v4-1-card__day.is-active{
        border:1px solid rgba(79,147,129,.28);
        background:var(--pv41-green-soft);
        color:#2f6f60;
      }

      .product-v4-1-card__day.is-request{
        border:1px dashed #e3e8ee;
        background:#fff;
        color:#98a2b3;
      }

      .product-v4-1-card__footer{
        display:grid;
        grid-template-columns:1fr;
        gap:10px;
        padding:12px 14px;
        border-top:1px solid #edf0f2;
        background:#fff;
      }

      .product-v4-1-card__price small{
        display:block;
        color:var(--pv41-muted);
        font-size:12px;
        line-height:1.1;
      }

      .product-v4-1-card__price strong{
        display:block;
        margin-top:3px;
        color:var(--pv41-ink);
        font-size:23px;
        font-weight:600;
        line-height:1;
        letter-spacing:-.05em;
      }

      .product-v4-1-card__actions{
        display:flex;
        gap:7px;
        align-items:center;
      }

      .product-v4-1-card__button{
        position:relative;
        z-index:5;
        min-width:0;
        flex:1;
        height:38px;
        border-radius:4px;
        font-size:13px;
        font-weight:600;
      }

      .product-v4-1-card__button--outline{
        border:1px solid rgba(16,24,40,.22);
        background:#fff;
        color:rgba(11,18,32,.82);
        box-shadow:0 10px 22px rgba(16,24,40,.08);
      }

      .product-v4-1-card__button--cta{
        border:1px solid #549483;
        background:#549483;
        color:#fff;
      }

      .product-v4-1-card__button--cta:hover{
        background:#417c6d;
        border-color:#417c6d;
      }

      .product-v4-1-card__button--outline:hover{
        border-color:rgba(84,148,131,.42);
        color:#549483;
      }

      .product-v4-1-card__fallback{
        display:flex;
        align-items:center;
        justify-content:center;
        padding:16px;
        color:rgba(16,24,40,.52);
        font-size:12px;
        font-weight:700;
        letter-spacing:.16em;
        text-transform:uppercase;
      }

      @media (max-width: 620px){
        .product-v4-1-card-scope .product-v4-1-card{
          width:clamp(260px, 86vw, 280px);
          min-width:clamp(260px, 86vw, 280px);
          max-width:none;
          min-height:auto;
        }

        .product-v4-1-card__footer{
          gap:12px;
        }

        .product-v4-1-card__actions{
          width:100%;
        }
      }
    </style>
@endonce

<div class="product-v4-1-card-scope">
  <article class="product-v4-1-card" aria-label="Offering card {{ $product->id }}">
    <a href="{{ $url }}" class="product-v4-1-card__surface-link" aria-label="Open {{ $titleFormatted }}"></a>

    <div class="product-v4-1-card__media">
      @if($hasDisplayableImage)
        <img
          src="{{ $image }}"
          alt="{{ $titleFormatted }}"
          loading="lazy"
          onload="var card=this.closest('.product-v4-1-card'); if(card){card.classList.add('is-image-loaded'); card.setAttribute('aria-busy','false');}"
          onerror="var card=this.closest('.product-v4-1-card'); if(card){card.classList.add('is-image-missing'); card.setAttribute('aria-busy','false');} this.remove();"
        >
      @else
        <div class="product-v4-1-card__fallback">{{ $categoryBadgeLabel }}</div>
      @endif

      <div class="product-v4-1-card__shade"></div>

      @if($signalText)
        <span class="product-v4-1-card__signal">{{ $signalText }}</span>
      @endif

      @if($businessAcceleratorPlan)
        <div class="premium-badge-holder">
          <div class="premium-badge-drawer">
            <div class="premium-badge-sheen"></div>

            <div class="premium-badge-copy">
              <p class="premium-badge-title">Premium Partner</p>
              <span class="premium-badge-small">Business Accelerator</span>
            </div>

            <button class="premium-badge-button" type="button" aria-label="Business Accelerator Premium Partner" title="Premium Partner" onclick="event.preventDefault(); event.stopPropagation();">
              <img src="https://studio.weofferwellness.co.uk/storage/uploads/images/78aa908f-334b-45c0-9220-1c4d84053c5e.png" alt="Premium Partner rosette">
            </button>
          </div>
        </div>
      @endif

      <div class="product-v4-1-card__badges">
        <span class="product-v4-1-card__badge product-v4-1-card__badge--gold">{{ $typeLabel }}</span>
        <span class="product-v4-1-card__badge product-v4-1-card__badge--blue">{{ $availabilityBadgeLabel }}</span>
      </div>

    </div>

      <div class="product-v4-1-card__body">
      <div class="product-v4-1-card__type">{{ $categoryBadgeLabel }}</div>
      <h3 class="product-v4-1-card__title">{{ $titleFormatted }}</h3>

      @if($providerFormatted)
        <p class="product-v4-1-card__provider">with {{ $providerFormatted }}</p>
      @endif

      <div class="product-v4-1-card__rating" aria-label="{{ $reviewCount > 0 ? 'Rated ' . number_format((float) $rating, 1) . ' out of 5' : 'Be the first to review' }}">
        <span class="product-v4-1-card__stars" aria-hidden="true">
          @for($i = 1; $i <= 5; $i++)
            <span class="product-v4-1-card__star {{ $i > $filledStars ? 'is-empty' : '' }}"></span>
          @endfor
        </span>
        <span class="product-v4-1-card__rating-copy">{{ $reviewSummary }}</span>
      </div>

      <p class="product-v4-1-card__summary">{{ $benefitTextClean ?? '' }}</p>

      <div class="product-v4-1-card__meta">
        @if($durationLabel)
          <span class="product-v4-1-card__chip">{{ $durationLabel }}</span>
        @endif

        @if($hasOnline)
          <span class="product-v4-1-card__chip product-v4-1-card__chip--blue">{{ $exclusiveOnline ? 'Exclusively online' : 'Online' }}</span>
        @endif

        @if($primary && ! $exclusiveOnline)
          <span class="product-v4-1-card__chip">
            <span class="wow-chip-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24"><path d="M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><path d="M17.8 13.938h-.011a7 7 0 1 0-11.464.144h-.016l.14.171c.1.127.2.251.3.371L12 21l5.13-6.248c.194-.209.374-.429.54-.659l.13-.155Z"/></svg>
            </span>
            {{ $primary }}
          </span>
        @endif
      </div>

      <div class="product-v4-1-card__availability {{ $availabilityClass }}">
        <div class="product-v4-1-card__availability-top">
          <div class="product-v4-1-card__availability-label">
            <span class="wow-chip-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24"><path d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1.5A2.5 2.5 0 0 1 22 6.5v12A2.5 2.5 0 0 1 19.5 21h-15A2.5 2.5 0 0 1 2 18.5v-12A2.5 2.5 0 0 1 4.5 4H6V3a1 1 0 0 1 1-1Zm12.5 8h-15v8.5a.5.5 0 0 0 .5.5h14a.5.5 0 0 0 .5-.5V10ZM5 6a.5.5 0 0 0-.5.5V8h15V6.5A.5.5 0 0 0 19 6H5Z"/></svg>
            </span>
            <span>{{ $calendarLabel }}</span>
          </div>
          <span class="product-v4-1-card__availability-note">{{ $calendarNote }}</span>
        </div>

        @if($availabilityDays)
          <div class="product-v4-1-card__days" aria-label="Availability calendar">
            @php $week = [1 => 'M', 2 => 'T', 3 => 'W', 4 => 'T', 5 => 'F', 6 => 'S', 0 => 'S']; @endphp
            @foreach([1,2,3,4,5,6,0] as $dayIndex)
              <span class="product-v4-1-card__day {{ in_array($dayIndex, $availabilityDays, true) ? 'is-active' : 'is-request' }}" title="{{ $week[$dayIndex] }}">{{ $week[$dayIndex] }}</span>
            @endforeach
          </div>
        @endif

      </div>
    </div>

    <footer class="product-v4-1-card__footer">
      <div class="product-v4-1-card__price">
        <small>From</small>
        <strong>£{{ is_numeric($priceMin) ? number_format((float) $priceMin, 2) : '—' }}</strong>
      </div>

      <div class="product-v4-1-card__actions">
        <button
          type="button"
          class="product-v4-1-card__button product-v4-1-card__button--outline js-add-to-cart js-open-cart"
          data-id="{{ $product->id }}"
          data-product-id="{{ $product->id }}"
          data-source-version="{{ $product->source_version ?? 'v1-v2' }}"
          data-title="{{ $titleFormatted }}"
          data-product-title="{{ $titleFormatted }}"
          data-price="{{ is_numeric($priceMin) ? number_format((float) $priceMin, 2, '.', '') : '0.00' }}"
          data-image="{{ $image }}"
          data-product-url="{{ $url }}"
          data-url="{{ $url }}"
          data-qty="1"
        >
          Add to cart
        </button>
        <a href="{{ $url }}" class="product-v4-1-card__button product-v4-1-card__button--cta btn-wow btn-wow--cta btn-sm" data-loader-init="1">
          Book
        </a>
      </div>
    </footer>
  </article>
</div>
@endif
