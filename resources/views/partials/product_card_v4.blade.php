@php
    $slug = \Illuminate\Support\Str::slug($product->title ?: (string) $product->id);
    $url = url('/offerings/' . $product->id . '-' . $slug);

    $toLower = function ($s) {
        return function_exists('mb_strtolower') ? mb_strtolower((string) $s, 'UTF-8') : strtolower((string) $s);
    };
    $ucWords = function ($s) {
        return function_exists('mb_convert_case') ? mb_convert_case((string) $s, MB_CASE_TITLE, 'UTF-8') : ucwords((string) $s);
    };

    $title = trim((string) ($product->title ?? 'Untitled'));
    $titleFormatted = $ucWords($toLower($title));

    $typeRaw = trim((string) ($product->product_type ?? 'Experience'));
    $typeLabel = $ucWords($toLower($typeRaw));

    $categoryRaw = $product->category?->name
        ?? ($product->category_name ?? null)
        ?? ($product->category_label ?? null)
        ?? ((is_string($product->category ?? null)) ? $product->category : null);
    if (is_array($categoryRaw)) {
        $categoryRaw = $categoryRaw['name'] ?? reset($categoryRaw) ?? null;
    }
    $categoryLabel = $categoryRaw ? $ucWords($toLower(str_replace(['_', '-'], ' ', $categoryRaw))) : null;
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
    $rating = isset($product->reviews_avg_rating) ? round((float) $product->reviews_avg_rating, 1) : null;
    $reviewCount = (int) ($product->reviews_count ?? 0);

    $provider = $product->vendor_name
        ?? (is_object($product->vendor ?? null) ? ($product->vendor->vendor_name ?? null) : null)
        ?? $product->practitioner_name
        ?? $product->provider
        ?? null;
    $providerFormatted = $provider ? $ucWords($toLower(str_replace('_', ' ', $provider))) : null;

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
    $giftCardHaystack = strtolower(implode(' ', array_filter([
        $title,
        $typeRaw,
        is_string($categoryRaw) ? $categoryRaw : '',
        (string) ($product->handle ?? ''),
        (string) ($benefitText ?? ''),
        (string) ($fomoText ?? ''),
    ])));
    $isGiftCard = \Illuminate\Support\Str::contains($giftCardHaystack, ['gift card', 'giftcard', 'voucher']);
@endphp

@if((is_numeric($priceMin) ? (float) $priceMin : 0.0) > 0.0)
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
        border-radius:var(--radius);
      }
      @media (min-width: 621px){
        .wow-therapy-card-scope .wow-card.md{
          max-height:690px;
          overflow:hidden;
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
        background:#eef2f4;
      }
      .therapy-card__media img{
        width:100%;
        height:100%;
        display:block;
        object-fit:cover;
        transition:transform 240ms ease;
      }
      .wow-card:hover .therapy-card__media img{ transform:scale(1.035); }
      .therapy-card__media::after{
        content:"";
        position:absolute;
        inset:0 0 auto 0;
        height:72px;
        pointer-events:none;
        background:linear-gradient(180deg, rgba(16,24,40,.34), rgba(16,24,40,0));
      }
      .wow-like-button{
        position:absolute;
        right:10px;
        top:10px;
        z-index:3;
        width:38px;
        height:38px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border:1px solid rgba(208,213,221,.92);
        border-radius:999px;
        background:rgba(255,255,255,.96);
        color:#344054;
        cursor:pointer;
        box-shadow:0 8px 18px rgba(16,24,40,.10);
      }
      .wow-like-button svg{ width:19px; height:19px; display:block; fill:transparent; stroke:currentColor; stroke-width:2; }
      .wow-like-button.is-liked{ border-color:rgba(79,147,129,.24); background:var(--green-soft); color:var(--green); }
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
      .star.star--empty{ background:transparent; }
      .star.star--empty::after{
        content:"";
        position:absolute;
        inset:0;
        background:#333;
        -webkit-mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='none' stroke='%23000' stroke-width='2' stroke-linejoin='round' stroke-linecap='round' d='M11.083 5.104c.35-.8 1.485-.8 1.834 0l1.752 4.022a1 1 0 0 0 .84.597l4.463.342c.9.069 1.255 1.2.556 1.771l-3.33 2.723a1 1 0 0 0-.337 1.016l1.03 4.119c.214.858-.71 1.552-1.474 1.106l-3.913-2.281a1 1 0 0 0-1.008 0L7.583 20.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1 1 0 0 0 6.8 14.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1 1 0 0 0 .84-.597l1.753-4.022Z'/%3E%3C/svg%3E") center/contain no-repeat;
                mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='none' stroke='%23000' stroke-width='2' stroke-linejoin='round' stroke-linecap='round' d='M11.083 5.104c.35-.8 1.485-.8 1.834 0l1.752 4.022a1 1 0 0 0 .84.597l4.463.342c.9.069 1.255 1.2.556 1.771l-3.33 2.723a1 1 0 0 0-.337 1.016l1.03 4.119c.214.858-.71 1.552-1.474 1.106l-3.913-2.281a1 1 0 0 0-1.008 0L7.583 20.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1 1 0 0 0 6.8 14.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1 1 0 0 0 .84-.597l1.753-4.022Z'/%3E%3C/svg%3E") center/contain no-repeat;
      }
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
        grid-template-columns:1fr auto;
        gap:10px;
        align-items:center;
        padding:12px 14px;
        border-top:1px solid var(--soft);
        background:#fff;
        border-radius:0 0 var(--radius) var(--radius);
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
      .gift-card-card{
        display:flex;
        flex-direction:column;
        min-height:492px;
        overflow:hidden;
        background:#fff;
        border:1px solid rgba(16,24,40,.18);
        border-radius:var(--radius);
        box-shadow:var(--shadow);
        transition:transform 180ms ease, border-color 180ms ease, box-shadow 180ms ease;
      }
      .wow-card:hover .gift-card-card{
        transform:translateY(-3px);
        border-color:rgba(79,147,129,.42);
        box-shadow:0 20px 48px rgba(16,24,40,.085);
      }
      .gift-card-card__media{
        position:relative;
        aspect-ratio:1.64/1;
        overflow:hidden;
        border-radius:var(--radius) var(--radius) 0 0;
        background:#eef2f4;
      }
      .gift-card-card__media img{
        width:100%;
        height:100%;
        display:block;
        object-fit:cover;
        transition:transform 240ms ease;
      }
      .wow-card:hover .gift-card-card__media img{ transform:scale(1.035); }
      .gift-card-card__media::after{
        content:"";
        position:absolute;
        inset:0 0 auto 0;
        height:72px;
        pointer-events:none;
        background:linear-gradient(180deg, rgba(16,24,40,.22), rgba(16,24,40,0));
      }
      .gift-card-card__badges{
        position:absolute;
        left:10px;
        bottom:10px;
        z-index:3;
        display:flex;
        flex-wrap:wrap;
        gap:6px;
        max-width:calc(100% - 20px);
      }
      .gift-card-card__body{
        flex:1;
        display:flex;
        flex-direction:column;
        gap:10px;
        padding:13px 14px 12px;
      }
      .gift-card-card__eyebrow{
        margin:0;
        color:#549483;
        font-size:12px;
        font-weight:800;
        letter-spacing:.08em;
        text-transform:uppercase;
      }
      .gift-card-card__title{
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
      .wow-card:hover .gift-card-card__title{ color:var(--green); }
      .gift-card-card__provider{
        margin:0;
        color:var(--muted);
        font-size:12.75px;
      }
      .gift-card-card__summary{
        margin:0;
        color:#344054;
        font-size:12.75px;
        line-height:1.45;
      }
      .gift-card-card__meta{
        display:flex;
        flex-wrap:wrap;
        gap:6px;
        min-height:26px;
      }
      .gift-card-card__chip{
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
      .gift-card-card__chip--green{
        color:#2f6f60;
        border-color:rgba(79,147,129,.24);
        background:var(--green-soft);
      }
      .gift-card-card__footer{
        display:grid;
        grid-template-columns:1fr auto;
        gap:10px;
        align-items:center;
        padding:12px 14px;
        border-top:1px solid var(--soft);
        background:#fff;
        border-radius:0 0 var(--radius) var(--radius);
      }
      .gift-card-card__price small{
        display:block;
        color:#667085;
        font-size:12px;
        line-height:1.1;
      }
      .gift-card-card__price strong{
        display:block;
        margin-top:3px;
        color:#101828;
        font-size:23px;
        font-weight:600;
        line-height:1;
        letter-spacing:-.05em;
      }
      .gift-card-card__actions{
        display:flex;
        gap:7px;
        align-items:center;
      }
      .wow-therapy-card-scope .gift-card-card .btn-wow{
        height:38px;
        border-radius:4px;
      }
      .wow-therapy-card-scope .gift-card-card .btn-wow--outline{
        border:1px solid rgba(16,24,40,.22);
        background:#fff !important;
        color:rgba(11,18,32,.82);
        box-shadow:0 10px 22px rgba(16,24,40,.08);
      }
      .wow-therapy-card-scope .gift-card-card .btn-wow--cta{
        background:#549483 !important;
        color:#fff;
      }
      .wow-therapy-card-scope .gift-card-card .btn-wow--cta:hover{
        background:#417c6d !important;
      }
      .wow-therapy-card-scope .gift-card-card .btn-wow--outline:hover{
        border-color:rgba(84,148,131,.42);
        color:#549483;
      }
      @media (max-width: 620px){
        .wow-therapy-card-scope .therapy-card{ min-height:auto; }
        .gift-card-card{ min-height:auto; }
        .therapy-card__footer{ grid-template-columns:1fr; }
        .gift-card-card__footer{ grid-template-columns:1fr; }
        .therapy-card__actions{ width:100%; }
        .gift-card-card__actions{ width:100%; }
        .wow-therapy-card-scope .btn-wow{ flex:1; min-width:0; }
      }
    </style>
  @endonce

  <div class="wow-therapy-card-scope">
    @if($isGiftCard)
    <div class="wow-card md">
      <article class="gift-card-card" aria-label="Gift card {{ $product->id }}">
        <div class="gift-card-card__media">
          @if($hasDisplayableImage)
            <img src="{{ $image }}" alt="{{ $title }}" loading="lazy">
          @else
            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#eff8f5,#ffffff);color:#417c6d;font-size:16px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;">
              Gift Card
            </div>
          @endif

          <span class="gift-card-card__badges">
            <span class="wow-badge wow-badge--gold">Gift card</span>
            <span class="wow-badge wow-badge--blue">Instant delivery</span>
          </span>
        </div>

        <div class="gift-card-card__body">
          <p class="gift-card-card__eyebrow">Digital gift card</p>
          <h3 class="gift-card-card__title">{{ $titleFormatted }}</h3>

          @if($providerFormatted)
            <p class="gift-card-card__provider">by {{ $providerFormatted }}</p>
          @endif

          @if($benefitText)
            <p class="gift-card-card__summary">{{ \Illuminate\Support\Str::limit($benefitText, 130) }}</p>
          @else
            <p class="gift-card-card__summary">A flexible digital gift card you can send instantly and redeem across We Offer Wellness.</p>
          @endif

          <div class="gift-card-card__meta">
            <span class="gift-card-card__chip gift-card-card__chip--green">Instant email delivery</span>
            <span class="gift-card-card__chip">Redeem on therapies, classes & events</span>
            <span class="gift-card-card__chip">Choose any amount</span>
          </div>
        </div>

        <footer class="gift-card-card__footer">
          <div class="gift-card-card__price">
            <small>From</small>
            <strong>£{{ number_format((float) $priceMin, 2) }}</strong>
          </div>

          <div class="gift-card-card__actions">
            <a href="{{ url('/giftcards') }}" class="btn-wow btn-wow--outline btn-sm">
              <span class="btn-label">Choose amount</span>
            </a>
            <a href="{{ $url }}" class="btn-wow btn-wow--cta btn-sm">
              <span class="btn-label">View details</span>
            </a>
          </div>
        </footer>
      </article>
    </div>
    @elseif($hasDisplayableImage)
    <a href="{{ $url }}" class="wow-card md">
      <article class="therapy-card" aria-label="Offering card {{ $product->id }}">
        <div class="therapy-card__media">
          <img src="{{ $image }}" alt="{{ $title }}" loading="lazy">

          @if($signalText)
            <span class="therapy-card__signal">{{ $signalText }}</span>
          @endif

          <span class="wow-like-button" role="button" tabindex="0" aria-label="Save offering" aria-pressed="false" title="Save">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z"></path>
            </svg>
          </span>

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

          <div class="rating-row" aria-label="Rated {{ $rating ? number_format((float) $rating, 1) : '0.0' }} out of 5">
            <span class="stars" aria-hidden="true">
              <span class="star" style="color:#f5c84b;"></span>
              <span class="star" style="color:#f5c84b;"></span>
              <span class="star" style="color:#f5c84b;"></span>
              <span class="star" style="color:#f5c84b;"></span>
              <span class="star star--empty"></span>
            </span>
            <span>{{ $rating ? number_format((float) $rating, 1) : '0.0' }} · {{ $reviewCount }} reviews</span>
          </div>

          @if($benefitText)
            <p class="therapy-card__provider" style="margin-top:-2px;color:#344054;">{{ \Illuminate\Support\Str::limit($benefitText, 110) }}</p>
          @endif

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
    @endif
  </div>
@endif
