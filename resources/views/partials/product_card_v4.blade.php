@php
    $slug = \Illuminate\Support\Str::slug($product->title ?: (string) $product->id);
    $url = app(\App\Services\SeoStructureService::class)->canonicalProductUrl($product);

    $toLower = function ($s) {
        return function_exists('mb_strtolower') ? mb_strtolower((string) $s, 'UTF-8') : strtolower((string) $s);
    };
    $ucWords = function ($s) {
        return function_exists('mb_convert_case') ? mb_convert_case((string) $s, MB_CASE_TITLE, 'UTF-8') : ucwords((string) $s);
    };
    $normalizeTypeLabel = function ($value) use ($toLower, $ucWords) {
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

        return $map[$normalized] ?? $ucWords($normalized);
    };

    $title = trim((string) ($product->title ?? 'Untitled'));
    $titleFormatted = $ucWords($toLower($title));

    $typeCandidate = data_get($product, 'type.name')
        ?? data_get($product, 'type_label')
        ?? data_get($product, 'type_name');
    if ($typeCandidate === null) {
        $rawType = data_get($product, 'type');
        if (is_string($rawType) || is_numeric($rawType)) {
            $typeCandidate = (string) $rawType;
        }
    }
    $typeRaw = trim((string) ($typeCandidate ?? ($product->product_type ?? 'Experience')));
    $typeLabel = $normalizeTypeLabel($typeRaw);

    $categoryRaw = $product->category?->name
        ?? ($product->category_name ?? null)
        ?? ($product->category_label ?? null)
        ?? ((is_string($product->category ?? null)) ? $product->category : null);
    if (is_array($categoryRaw)) {
        $categoryRaw = $categoryRaw['name'] ?? reset($categoryRaw) ?? null;
    }
    $categoryLabel = $categoryRaw ? $normalizeTypeLabel($categoryRaw) : null;

    $eventSource = data_get($product, 'when.event', data_get($product, 'event', []));
    if (is_object($eventSource)) {
        $eventSource = (array) $eventSource;
    }
    if (!is_array($eventSource)) {
        $eventSource = [];
    }
    $hasEventSchedule = strtolower((string) data_get($product, 'when.type', '')) === 'event'
        || filled(data_get($product, 'date'))
        || filled(data_get($product, 'start_date'))
        || filled(data_get($product, 'end_date'))
        || filled(data_get($product, 'start_time'))
        || filled(data_get($product, 'end_time'))
        || !empty($eventSource['dates'] ?? [])
        || !empty($eventSource['upcoming_dates'] ?? [])
        || !empty($eventSource['availability_dates'] ?? [])
        || !empty(data_get($product, 'event.dates', []))
        || !empty(data_get($product, 'when.event.dates', []));

    if ($hasEventSchedule) {
        $typeLabel = 'Event';
    }

    $categoryBadgeLabel = $categoryLabel ?? $typeLabel;

    $image = $product->getFirstImageUrl();
    $hasDisplayableImage = method_exists($product, 'hasDisplayableImage')
        ? $product->hasDisplayableImage()
        : !str_contains((string) $image, 'no-product-image.jpg');
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
        ? ($starterPlan ? $provider : $ucWords($toLower(str_replace('_', ' ', $provider))))
        : null;

    $locations = $product->getLocations();
    $hasOnline = in_array('Online', $locations, true);
    $physical = array_values(array_filter($locations, fn ($l) => $l !== 'Online'));
    $physicalShort = [];
    $seenShort = [];
    foreach ($physical as $locRaw) {
        $short = trim((string) $locRaw);
        if ($short === '') {
            continue;
        }
        $key = mb_strtolower($short);
        if (!isset($seenShort[$key])) {
            $seenShort[$key] = true;
            $physicalShort[] = $short;
        }
    }
    $matchedLocation = trim((string) ($product->matched_location_label ?? ''));
    $primary = $matchedLocation !== '' ? $matchedLocation : ($physicalShort[0] ?? null);
    $remainingCount = max(0, count($physicalShort) - ($primary ? 1 : 0));
    $exclusiveOnline = $hasOnline && count($physicalShort) === 0;

    $nextLabel = $product->next_label ?? $product->next ?? null;
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
    $giftCardHaystack = $toLower(implode(' ', array_filter([
        $title,
        $benefitText,
        $slug,
        $categoryRaw,
        $typeRaw,
        $product->product_type ?? null,
        $fomoText,
    ])));
    $isGiftCard = (bool) preg_match('/gift\s*card|giftcard|voucher|e-?gift/i', $giftCardHaystack);

    $availabilityDays = [];
    try {
        $vendorUser = optional(optional($product->vendor)->user);
        $defaultAvailability = collect(data_get($vendorUser, 'defaultAvailability', data_get($vendorUser, 'default_availability', [])));
        foreach ($defaultAvailability as $row) {
            $dayValue = data_get($row, 'day_of_week');
            $available = data_get($row, 'is_available', true);
            if (!filter_var($available, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) && (string) $available !== '1') {
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

    $signalText = $fomoText !== ''
        ? $fomoText
        : ($exclusiveOnline
            ? 'Exclusively online'
            : ($remainingCount > 0
                ? '+' . $remainingCount . ' more locations'
                : ($nextLabel ? 'Next: ' . $nextLabel : null)));

    $calendarLabel = $availabilityDays ? 'Availability calendar' : 'Request day/time';
    $calendarNote = $availabilityDays ? 'Live calendar' : 'Practitioner confirms';
    $availabilityClass = $availabilityDays ? 'has-availability' : 'needs-availability';
    $durationLabel = $product->duration ?? null;
    $isEvent = $typeLabel === 'Event'
        || str_contains($toLower($typeRaw), 'event')
        || str_contains($toLower($categoryRaw ?? ''), 'event')
        || ($hasEventSchedule && str_contains($toLower($typeRaw), 'experience'));
@endphp

@once
    <style>
      .wow-therapy-card-scope{
        --ink:#101828;
        --muted:#667085;
        --line:#dde3ea;
        --soft:#edf0f2;
        --green:#4f9381;
        --green-dark:#417c6d;
        --green-soft:#e8f5f1;
        --gold-soft:#ffe5b3;
        --gold-text:#6f4b10;
        --blue-soft:#e8f0ff;
        --blue-text:#254a85;
        --rose-soft:#fff1f3;
        --rose-text:#b42318;
        --warm-soft:#fff7ed;
        --warm-text:#b54708;
        --radius:13px !important;
        --shadow:0 12px 34px rgba(16,24,40,.045);
      }
      .wow-therapy-card-scope .wow-card{
        position:relative;
        display:block;
        border-radius:var(--radius);
      }
      .wow-therapy-card-scope .wow-card.is-image-loading{
        pointer-events:none;
      }
      @media (min-width: 621px){
        .wow-therapy-card-scope .wow-card.md{
          width:280px;
          max-width:280px;
          flex:0 0 280px;
          justify-self:start;
          max-height:690px;
          overflow:hidden;
        }
        .wow-therapy-card-scope .therapy-card{
          width:280px;
        }
        .wow-therapy-card-scope .therapy-card{
          max-height:690px;
        }
      }
      .wow-therapy-card-scope .therapy-card{
        display:flex;
        flex-direction:column;
        min-height: 492px;
        overflow:visible;
        background:#fff;
        border:1px solid rgba(16,24,40,.18);
        border-radius:var(--radius);
        box-shadow:var(--shadow);
        transition:transform 180ms ease, border-color 180ms ease, box-shadow 180ms ease;
      }
      .wow-card:hover .therapy-card{
        transform:translateY(-3px);
        border-color:rgba(79,147,129,.42);
        box-shadow:0 20px 48px rgba(16,24,40,.085);
      }
      .therapy-card__media{
        position:relative;
        aspect-ratio:1.64/1;
        overflow:hidden;
        border-radius:var(--radius) var(--radius) 0 0;
        background:
          radial-gradient(circle at 24% 28%, rgba(79,147,129,.12), transparent 30%),
          radial-gradient(circle at 74% 70%, rgba(37,74,133,.09), transparent 32%),
          linear-gradient(135deg, #eef2f4 0%, #f8fbfd 100%);
      }
      .therapy-card__media img{
        position:relative;
        z-index:0;
        width:100%;
        height:100%;
        display:block;
        object-fit:cover;
        opacity:1;
        transition:opacity 180ms ease, transform 240ms ease;
      }
      .wow-card:hover .therapy-card__media img{ transform:scale(1.035); }
      .wow-therapy-card-scope .wow-card.is-image-loading .therapy-card__media{
        background:#111111;
      }
      .wow-therapy-card-scope .wow-card.is-image-loading .therapy-card__media::before{
        content:"";
        position:absolute;
        inset:0;
        background:
          linear-gradient(to bottom, transparent 50%, rgba(0,0,0,.22) 100%),
          linear-gradient(90deg, rgba(255,255,255,.14), transparent 22%, rgba(255,255,255,.08) 72%, transparent);
        mix-blend-mode:screen;
        opacity:.68;
        pointer-events:none;
        z-index:2;
      }
      .wow-therapy-card-scope .wow-card.is-image-loading .therapy-card__media img{
        opacity:0;
      }
      .wow-therapy-card-scope .wow-card.is-image-loading .therapy-card__signal,
      .wow-therapy-card-scope .wow-card.is-image-loading .wow-badge,
      .wow-therapy-card-scope .wow-card.is-image-loading .therapy-card__title,
      .wow-therapy-card-scope .wow-card.is-image-loading .therapy-card__provider,
      .wow-therapy-card-scope .wow-card.is-image-loading .rating-row,
      .wow-therapy-card-scope .wow-card.is-image-loading .therapy-card__description,
      .wow-therapy-card-scope .wow-card.is-image-loading .therapy-card__chip,
      .wow-therapy-card-scope .wow-card.is-image-loading .therapy-card__availability,
      .wow-therapy-card-scope .wow-card.is-image-loading .therapy-card__price small,
      .wow-therapy-card-scope .wow-card.is-image-loading .therapy-card__price strong,
      .wow-therapy-card-scope .wow-card.is-image-loading .btn-wow{
        position:relative;
        overflow:hidden;
        color:transparent !important;
        text-shadow:none !important;
        background:linear-gradient(90deg, #e7edf3, #f1f5f9);
      }
      .wow-therapy-card-scope .wow-card.is-image-loading .therapy-card__signal,
      .wow-therapy-card-scope .wow-card.is-image-loading .wow-badge{
        background:linear-gradient(90deg, #e7edf3, #f1f5f9);
      }
      .wow-therapy-card-scope .wow-card.is-image-loading .rating-row > *,
      .wow-therapy-card-scope .wow-card.is-image-loading .therapy-card__availability > *,
      .wow-therapy-card-scope .wow-card.is-image-loading .therapy-card__chip > *,
      .wow-therapy-card-scope .wow-card.is-image-loading .btn-wow > *{
        opacity:0 !important;
      }
      .wow-therapy-card-scope .wow-card.is-image-loading .therapy-card__title::before,
      .wow-therapy-card-scope .wow-card.is-image-loading .therapy-card__provider::before,
      .wow-therapy-card-scope .wow-card.is-image-loading .rating-row::before,
      .wow-therapy-card-scope .wow-card.is-image-loading .therapy-card__description::before,
      .wow-therapy-card-scope .wow-card.is-image-loading .therapy-card__chip::before,
      .wow-therapy-card-scope .wow-card.is-image-loading .therapy-card__availability::before,
      .wow-therapy-card-scope .wow-card.is-image-loading .therapy-card__price small::before,
      .wow-therapy-card-scope .wow-card.is-image-loading .therapy-card__price strong::before,
      .wow-therapy-card-scope .wow-card.is-image-loading .btn-wow::before,
      .wow-therapy-card-scope .wow-card.is-image-loading .therapy-card__signal::before,
      .wow-therapy-card-scope .wow-card.is-image-loading .wow-badge::before{
        content:"";
        position:absolute;
        inset:0;
        transform:translateX(-115%);
        background:linear-gradient(
          100deg,
          transparent 0%,
          rgba(255,255,255,.36) 28%,
          rgba(255,255,255,.82) 50%,
          rgba(255,255,255,.36) 72%,
          transparent 100%
        );
        animation:pv4-loader-shimmer 1.55s ease-in-out infinite;
      }
      .gift-card-card__media{
        position:relative;
        aspect-ratio:1.64/1;
        overflow:hidden;
        border-radius:var(--radius) var(--radius) 0 0;
        background:
          radial-gradient(circle at 24% 28%, rgba(79,147,129,.12), transparent 30%),
          radial-gradient(circle at 74% 70%, rgba(37,74,133,.09), transparent 32%),
          linear-gradient(135deg, #eef2f4 0%, #f8fbfd 100%);
      }
      .gift-card-card__media img{
        position:relative;
        z-index:0;
        width:100%;
        height:100%;
        display:block;
        object-fit:cover;
        opacity:1;
        transition:opacity 180ms ease, transform 240ms ease;
      }
      .wow-card:hover .gift-card-card__media img{ transform:scale(1.035); }
      .therapy-card__media::after{
        content:"";
        position:absolute;
        inset:0 0 auto 0;
        height:72px;
        pointer-events:none;
        z-index:2;
        background:linear-gradient(180deg, rgba(16,24,40,.34), rgba(16,24,40,0));
      }
      .gift-card-card__media::after{
        content:"";
        position:absolute;
        inset:0 0 auto 0;
        height:72px;
        pointer-events:none;
        z-index:2;
        background:linear-gradient(180deg, rgba(16,24,40,.22), rgba(16,24,40,0));
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
        box-shadow:
          0 12px 26px rgba(17,24,39,.16),
          inset 0 1px 0 rgba(255,255,255,.14);
        backdrop-filter:blur(14px) saturate(145%);
        -webkit-backdrop-filter:blur(14px) saturate(145%);
        transition:
          width 340ms cubic-bezier(.2,.8,.2,1),
          height 220ms ease,
          border-color 220ms ease,
          background 220ms ease,
          box-shadow 220ms ease,
          transform 220ms ease;
      }
      .premium-badge-holder:hover .premium-badge-drawer,
      .premium-badge-holder:focus-within .premium-badge-drawer{
        width:250px;
        height:56px;
        border-color:rgba(255,255,255,.42);
        background:rgba(16,151,150,.86);
        box-shadow:
          0 20px 48px rgba(17,24,39,.24),
          inset 0 1px 0 rgba(255,255,255,.18);
        transform:translateY(-2px);
      }
      .premium-badge-copy{
        width:174px;
        min-width:174px;
        padding-left:16px;
        opacity:0;
        transform:translateX(18px);
        transition:
          opacity 220ms ease 90ms,
          transform 280ms cubic-bezier(.2,.8,.2,1) 70ms;
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
        transition:
          transform 260ms cubic-bezier(.2,.8,.2,1),
          box-shadow 220ms ease,
          background 220ms ease;
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
        0%{
          left:-80%;
          opacity:0;
        }
        30%{
          opacity:1;
        }
        100%{
          left:112%;
          opacity:0;
        }
      }
      .therapy-card__signal{
        position:absolute;
        left:10px;
        top:10px;
        z-index:3;
        display:inline-flex;
        align-items:center;
        gap:7px;
        min-height:28px;
        border-radius:999px;
        padding:0 10px;
        border:1px solid transparent;
        font-size:11.5px;
        font-weight:700;
        white-space:nowrap;
        backdrop-filter:blur(10px);
        box-shadow:0 10px 22px rgba(16,24,40,.10);
        background:rgba(255,247,237,.94);
        color:var(--warm-text);
      }
      .therapy-card__badges{
        position:absolute;
        left:10px;
        bottom:10px;
        z-index:3;
        display:flex;
        flex-wrap:wrap;
        gap:6px;
        max-width:calc(100% - 20px);
      }
      .wow-badge{
        min-height:26px;
        display:inline-flex;
        align-items:center;
        border-radius:999px;
        padding:0 9px;
        font-size:11px;
        font-weight:700;
        backdrop-filter:blur(8px);
      }
      .wow-badge--gold{ background:rgba(255,229,179,.96); color:var(--gold-text); border:1px solid rgba(240,200,121,.9); }
      .wow-badge--blue{ background:rgba(232,240,255,.96); color:var(--blue-text); border:1px solid rgba(199,216,251,.9); }
      .therapy-card__body{
        flex:1;
        display:flex;
        flex-direction:column;
        gap:10px;
        padding:13px 14px 12px;
      }
      .therapy-card__title{
        display:-webkit-box;
        min-height:45px;
        margin:0;
        overflow:hidden;
        color:var(--ink);
        font-size:20px;
        font-weight:500;
        line-height:1.08;
        letter-spacing:-.045em;
        -webkit-box-orient:vertical;
        -webkit-line-clamp:2;
      }
      .wow-card:hover .therapy-card__title{ color:var(--green); }
      .therapy-card__provider{
        margin:0;
        color:var(--muted);
        font-size:12.75px;
      }
      .therapy-card__description{
        position:relative;
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
      .therapy-card__description::after{
        content:"";
        position:absolute;
        left:0;
        right:0;
        bottom:0;
        height:1.15em;
        background:linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(255,255,255,.84) 68%, #fff 100%);
        pointer-events:none;
      }
      .rating-row{
        display:flex;
        align-items:center;
        gap:7px;
        color:#101828;
        font-size:12.25px;
      }
      .stars{ letter-spacing:.8px; white-space:nowrap; }
      .star{
        width:18px;
        height:18px;
        display:inline-block;
        position:relative;
        background:currentColor;
        -webkit-mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23000' d='M11.083 5.104c.35-.8 1.485-.8 1.834 0l1.752 4.022a1 1 0 0 0 .84.597l4.463.342c.9.069 1.255 1.2.556 1.771l-3.33 2.723a1 1 0 0 0-.337 1.016l1.03 4.119c.214.858-.71 1.552-1.474 1.106l-3.913-2.281a1 1 0 0 0-1.008 0L7.583 20.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1 1 0 0 0 6.8 14.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1 1 0 0 0 .84-.597l1.753-4.022Z'/%3E%3C/svg%3E") center/contain no-repeat;
                mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23000' d='M11.083 5.104c.35-.8 1.485-.8 1.834 0l1.752 4.022a1 1 0 0 0 .84.597l4.463.342c.9.069 1.255 1.2.556 1.771l-3.33 2.723a1 1 0 0 0-.337 1.016l1.03 4.119c.214.858-.71 1.552-1.474 1.106l-3.913-2.281a1 1 0 0 0-1.008 0L7.583 20.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1 1 0 0 0 6.8 14.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1 1 0 0 0 .84-.597l1.753-4.022Z'/%3E%3C/svg%3E") center/contain no-repeat;
      }
      .star.star--empty{ color:#d0d5dd; }
      .therapy-card__meta{
        display:flex;
        flex-wrap:wrap;
        gap:6px;
        min-height: 26px;
      }
      .therapy-card__chip{
        min-height:27px;
        display:inline-flex;
        align-items:center;
        gap:6px;
        border:1px solid #e3e8ee;
        border-radius:999px;
        background:#fff;
        color:#596275;
        padding:0 9px;
        font-size:12.25px;
        white-space:nowrap;
      }
      .therapy-card__chip--online{ color:#2f6f60; border-color:rgba(79,147,129,.24); background:var(--green-soft); }
      .therapy-card__availability{
        margin-top:4px;
        min-height:62px;
        border:1px solid #e3e8ee;
        border-radius:11px;
        background:#fff;
        padding:8px;
      }
      .therapy-card__availability.has-availability{
        border-color:rgba(79,147,129,.24);
        background:linear-gradient(180deg, rgba(232,245,241,.64), rgba(255,255,255,.94)), #fff;
      }
      .therapy-card__availability.needs-availability{
        border-style:dashed;
        background:linear-gradient(180deg, rgba(255,247,237,.50), rgba(255,255,255,.96)), #fff;
      }
      .therapy-card__availability-top{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:9px;
        margin-bottom:7px;
      }
      .therapy-card__availability-label{
        display:flex;
        align-items:center;
        gap:6px;
        color:#344054;
        font-size:11.7px;
        font-weight:800;
        line-height:1.15;
      }
      .therapy-card__availability.has-availability .therapy-card__availability-label{ color:#2f6f60; }
      .therapy-card__availability.needs-availability .therapy-card__availability-label{ color:var(--warm-text); }
      .therapy-card__availability-note{
        color:#667085;
        font-size:10.9px;
        white-space:nowrap;
      }
      .wow-day-strip{
        display:grid;
        grid-template-columns:repeat(7, minmax(0, 1fr));
        gap:3px;
      }
      .wow-day{
        height:21px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border:1px solid #edf0f2;
        border-radius:6px;
        background:#f8fafc;
        color:#98a2b3;
        font-size:10px;
        font-weight:800;
      }
      .wow-day.is-active{
        border-color:rgba(79,147,129,.28);
        background:var(--green-soft);
        color:#2f6f60;
      }
      .wow-day.is-request{
        border-style:dashed;
        background:#fff;
      }
      .wow-request-action{
        height:23px;
        display:flex;
        align-items:center;
        justify-content:center;
        border:1px dashed rgba(181,71,8,.24);
        border-radius:7px;
        background:#fff;
        color:var(--warm-text);
        font-size:10.8px;
        font-weight:800;
      }
      .therapy-card__footer{
        display:grid;
        grid-template-columns:1fr;
        gap:10px;
        align-items:center;
        padding:12px 14px;
        border-top:1px solid var(--soft);
        background:#fff;
        border-radius:0 0 var(--radius) var(--radius);
      }
      .therapy-card__price,
      .therapy-card__actions{
        width:100%;
      }
      .therapy-card__price small{
        display:block;
        color:#667085;
        font-size:12px;
        line-height:1.1;
      }
      .therapy-card__price strong{
        display:block;
        margin-top:3px;
        color:#101828;
        font-size:23px;
        font-weight:600;
        line-height:1;
        letter-spacing:-.05em;
      }
      .therapy-card__actions{
        display:flex;
        gap:7px;
        align-items:center;
        flex-wrap:wrap;
      }
      .therapy-card__actions .btn-wow{
        flex:1;
        min-width:0;
      }
      .wow-therapy-card-scope .btn-wow{
        height:38px;
        border-radius:4px;
      }
      .wow-therapy-card-scope .btn-wow--outline{
        border:1px solid rgba(16,24,40,.22);
        background:#fff !important;
        color:rgba(11,18,32,.82);
        box-shadow:0 10px 22px rgba(16,24,40,.08);
      }
      .wow-therapy-card-scope .btn-wow--cta{
        background:#549483 !important;
        color:#fff;
      }
      .wow-therapy-card-scope .btn-wow--cta:hover{
        background:#417c6d !important;
      }
      .wow-therapy-card-scope .btn-wow--outline:hover{
        border-color:rgba(84,148,131,.42);
        color:#549483;
      }
      @keyframes pv4-loader-shimmer{
        100%{ transform:translateX(115%); }
      }
      @media (max-width: 620px){
        .wow-therapy-card-scope .therapy-card{ min-height:auto; }
        .therapy-card__actions{ width:100%; }
      }
    </style>
@endonce

@if($isGiftCard)
    @include('partials.product_card_v4_giftcard')
@elseif($isEvent)
    @include('partials.product_card_v4_event')
@else
  <div class="wow-therapy-card-scope">
    <a href="{{ $url }}" class="wow-card md {{ $hasDisplayableImage ? 'is-image-loading' : 'is-image-missing' }}" @if($hasDisplayableImage) aria-busy="true" @endif>
      <article class="therapy-card" aria-label="Offering card {{ $product->id }}">
        <div class="therapy-card__media">
          @if($hasDisplayableImage)
            <img
              src="{{ $image }}"
              alt="{{ $title }}"
              loading="lazy"
              onload="var card=this.closest('.wow-card'); if(card){card.classList.remove('is-image-loading'); card.classList.add('is-image-loaded'); card.setAttribute('aria-busy','false');}"
              onerror="var card=this.closest('.wow-card'); if(card){card.classList.remove('is-image-loading'); card.classList.add('is-image-missing'); card.setAttribute('aria-busy','false');} this.remove();"
            >
          @endif

          @if($signalText)
            <span class="therapy-card__signal">{{ $signalText }}</span>
          @endif

          @if($businessAcceleratorPlan)
            <div class="premium-badge-holder">
              <div class="premium-badge-drawer">
                <div class="premium-badge-sheen"></div>

                <div class="premium-badge-copy">
                  <p class="premium-badge-title">Premium Partner</p>
                  <span class="premium-badge-small">Business Accelerator</span>
                </div>

                <button
                  class="premium-badge-button"
                  type="button"
                  aria-label="Business Accelerator Premium Partner"
                  title="Premium Partner"
                  onclick="event.preventDefault(); event.stopPropagation();"
                >
                  <img
                    src="https://studio.weofferwellness.co.uk/storage/uploads/images/78aa908f-334b-45c0-9220-1c4d84053c5e.png"
                    alt="Premium Partner rosette"
                  >
                </button>
              </div>
            </div>
          @endif

          <span class="therapy-card__badges">
            <span class="wow-badge wow-badge--gold">{{ $categoryBadgeLabel }}</span>
            <span class="wow-badge wow-badge--blue">{{ $typeLabel }}</span>
          </span>
        </div>

        <div class="therapy-card__body">
          <h3 class="therapy-card__title">{{ $titleFormatted }}</h3>
          @if($providerFormatted)
            <p class="therapy-card__provider">with {{ $providerFormatted }}</p>
          @endif

          <div class="rating-row" aria-label="{{ $reviewCount > 0 ? 'Rated ' . number_format((float) $rating, 1) . ' out of 5' : 'Be the first to review' }}">
            <span class="stars" aria-hidden="true">
              @for($i = 1; $i <= 5; $i++)
                <span class="star {{ $i > $filledStars ? 'star--empty' : '' }}" style="color: {{ $i <= $filledStars ? '#f5c84b' : '#d0d5dd' }};"></span>
              @endfor
            </span>
            <span>{{ $reviewSummary }}</span>
          </div>

          <p class="therapy-card__description">{{ $benefitTextClean ?? '' }}</p>

          <div class="therapy-card__meta">
            @if($durationLabel)
              <span class="therapy-card__chip">{{ $durationLabel }}</span>
            @endif

            @if($exclusiveOnline)
              <span class="therapy-card__chip therapy-card__chip--online">Exclusively online</span>
            @elseif($hasOnline)
              <span class="therapy-card__chip therapy-card__chip--online">Online</span>
            @endif

            @if($primary && !$exclusiveOnline)
              <span class="therapy-card__chip">
                <span class="wow-chip-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><path d="M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><path d="M17.8 13.938h-.011a7 7 0 1 0-11.464.144h-.016l.14.171c.1.127.2.251.3.371L12 21l5.13-6.248c.194-.209.374-.429.54-.659l.13-.155Z"/></svg>
                </span>
                {{ $primary }}
              </span>
            @endif
          </div>

          <div class="therapy-card__availability {{ $availabilityClass }}">
            <div class="therapy-card__availability-top">
              <div class="therapy-card__availability-label">
                <span class="wow-chip-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><path d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1.5A2.5 2.5 0 0 1 22 6.5v12A2.5 2.5 0 0 1 19.5 21h-15A2.5 2.5 0 0 1 2 18.5v-12A2.5 2.5 0 0 1 4.5 4H6V3a1 1 0 0 1 1-1Zm12.5 8h-15v8.5a.5.5 0 0 0 .5.5h14a.5.5 0 0 0 .5-.5V10ZM5 6a.5.5 0 0 0-.5.5V8h15V6.5A.5.5 0 0 0 19 6H5Z"/></svg>
                </span>
                <span>{{ $calendarLabel }}</span>
              </div>
              <span class="therapy-card__availability-note">{{ $calendarNote }}</span>
            </div>

            @if($availabilityDays)
              <div class="wow-day-strip" aria-label="Availability calendar">
                @php $week = [1 => 'M', 2 => 'T', 3 => 'W', 4 => 'T', 5 => 'F', 6 => 'S', 0 => 'S']; @endphp
                @foreach([1,2,3,4,5,6,0] as $dayIndex)
                  <span class="wow-day {{ in_array($dayIndex, $availabilityDays, true) ? 'is-active' : 'is-request' }}" title="{{ $week[$dayIndex] }}">{{ $week[$dayIndex] }}</span>
                @endforeach
              </div>
            @endif
          </div>
        </div>

        <footer class="therapy-card__footer">
          <div class="therapy-card__price">
            <small>{{ $compareMin && $compareMin > $priceMin ? 'From' : 'From' }}</small>
            <strong>£{{ number_format((float) $priceMin, 2) }}</strong>
          </div>

          <div class="therapy-card__actions">
            <button type="button" class="btn-wow btn-wow--outline btn-sm js-add-to-cart js-open-cart"
              data-id="{{ $product->id }}"
              data-product-id="{{ $product->id }}"
              data-source-version="{{ $product->source_version ?? 'v1-v2' }}"
              data-title="{{ e($titleFormatted) }}"
              data-price="{{ number_format((float) $priceMin, 2, '.', '') }}"
              data-image="{{ $image }}"
              data-url="{{ $url }}"
            >
              <span class="btn-label">Add to cart</span>
            </button>
            <button type="button" class="btn-wow btn-wow--cta btn-sm js-buy-now"
              data-id="{{ $product->id }}"
              data-product-id="{{ $product->id }}"
              data-source-version="{{ $product->source_version ?? 'v1-v2' }}"
              data-title="{{ e($titleFormatted) }}"
              data-price="{{ number_format((float) $priceMin, 2, '.', '') }}"
              data-image="{{ $image }}"
              data-url="{{ $url }}"
              data-qty="1"
            >
              <span class="btn-label">Book</span>
            </button>
          </div>
        </footer>
      </article>
    </a>
  </div>
@endif
