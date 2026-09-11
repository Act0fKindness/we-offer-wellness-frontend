@php
    $eventProduct = $product ?? [];
    $mobileEventVariantRows = $eventVariantRows ?? [];

    if (empty($mobileEventVariantRows)) {
        $fallbackVariantRows = [];
        foreach (array_values($eventProduct['variants'] ?? []) as $index => $variant) {
            if (! is_array($variant)) {
                continue;
            }

            $label = trim((string) ($variant['options'][0] ?? $variant['title'] ?? 'Ticket'));
            $price = is_numeric($variant['price'] ?? null) ? (float) $variant['price'] : 0.0;

            $fallbackVariantRows[] = [
                'index' => $index,
                'id' => (string) ($variant['id'] ?? ('event_ticket_' . $index)),
                'label' => $label !== '' ? $label : 'Ticket',
                'price' => $price,
                'price_formatted' => number_format($price, 2, '.', ''),
            ];
        }

        if (empty($fallbackVariantRows)) {
            $fallbackPrice = is_numeric($eventProduct['price'] ?? null) ? (float) $eventProduct['price'] : 0.0;
            $fallbackVariantRows[] = [
                'index' => 0,
                'id' => 'event_ticket',
                'label' => 'Event ticket',
                'price' => $fallbackPrice,
                'price_formatted' => number_format($fallbackPrice, 2, '.', ''),
            ];
        }

        $mobileEventVariantRows = $fallbackVariantRows;
    }

    $mobileEventSelectedTicket = $eventSelectedTicket ?? ($mobileEventVariantRows[0] ?? [
        'id' => '',
        'label' => 'Event ticket',
        'price_formatted' => '0.00',
    ]);
    $mobileEventProductId = $eventProductId ?? ($eventProduct['id'] ?? null);
    $mobileEventButtons = $eventButtons ?? [
        'title' => trim((string) ($eventProduct['title'] ?? 'Event')),
        'image' => trim((string) ($eventProduct['image'] ?? ($eventProduct['images'][0] ?? ''))),
        'url' => url()->current(),
        'productId' => $mobileEventProductId,
    ];
    $mobileEventQty = $eventQty ?? 1;
@endphp

<div class="mobile-ticket-bar" id="mobileTicketBar">
    <div>
        <strong id="mobilePrice">£{{ $mobileEventSelectedTicket['price_formatted'] ?? '0.00' }}</strong>
        <span id="mobileTicket">{{ $mobileEventSelectedTicket['label'] ?? 'Select your festival dates' }}</span>
    </div>
    <button class="btn checkout-button" type="button" data-open-booking>
        Pick dates
    </button>
</div>

<div class="wow-booking-backdrop" id="bookingBackdrop"></div>

<section class="wow-booking-modal" id="bookingModal" aria-label="Mobile booking modal">
    <div class="booking-modal-head">
        <h3>Select dates</h3>
        <button class="booking-modal-close" type="button" id="closeBookingModal">×</button>
    </div>

    <div class="booking-modal-body">
        <div class="modal-fields-slot" id="modalFieldsSlot"></div>
    </div>

    <div class="booking-modal-footer">
        <button
            class="btn checkout-button js-buy-now"
            type="button"
            id="eventModalBookNowBtn"
            data-id="{{ $mobileEventSelectedTicket['id'] ?? '' }}"
            data-product-id="{{ $mobileEventProductId }}"
            data-title="{{ e($mobileEventButtons['title']) }}"
            data-price="{{ $mobileEventSelectedTicket['price_formatted'] ?? '0.00' }}"
            data-image="{{ $mobileEventButtons['image'] }}"
            data-url="{{ $mobileEventButtons['url'] }}"
            data-qty="{{ $mobileEventQty }}"
            data-variant-id="{{ $mobileEventSelectedTicket['id'] ?? '' }}"
            data-variant-label="{{ $mobileEventSelectedTicket['label'] ?? '' }}"
            data-source-version="v3"
        >
            Book now
        </button>
    </div>
</section>
