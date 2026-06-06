@php
    $giftCardUrl = url('/giftcards');
    $giftCardTitle = $titleFormatted ?? 'Gift Card';
    $giftCardProvider = $providerFormatted ?? null;
    $giftCardSummary = $benefitTextClean ?: 'A flexible digital gift card you can send instantly.';
    $giftCardPriceLabel = is_numeric($priceMin ?? null)
        ? '£' . number_format((float) $priceMin, 2)
        : 'Gift cards';
@endphp

<div class="wow-therapy-card-scope">
<a href="{{ $giftCardUrl }}" class="wow-card md gift-card-card-wrap" aria-label="Gift card {{ $product->id ?? $giftCardTitle }}">
    <article class="therapy-card">
        <div class="therapy-card__media">
            @if($hasDisplayableImage)
                <img
                    src="{{ $image }}"
                    alt="{{ $giftCardTitle }}"
                    loading="lazy"
                >
            @else
                <div class="gift-card-card__fallback">Gift Card</div>
            @endif

            <span class="therapy-card__signal">Digital gift card</span>

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
                <span class="wow-badge wow-badge--gold">Gift card</span>
                <span class="wow-badge wow-badge--blue">Instant delivery</span>
            </span>
        </div>

        <div class="therapy-card__body">
            <h3 class="therapy-card__title">{{ $giftCardTitle }}</h3>

            @if($giftCardProvider)
                <p class="therapy-card__provider">with {{ $giftCardProvider }}</p>
            @endif

            <p class="therapy-card__description">{{ $giftCardSummary }}</p>

            <div class="therapy-card__meta">
                <span class="therapy-card__chip therapy-card__chip--online">Instant email delivery</span>
            </div>
        </div>

        <footer class="therapy-card__footer">
            <div class="therapy-card__price">
                <small>From</small>
                <strong>{{ $giftCardPriceLabel }}</strong>
            </div>

            <div class="therapy-card__actions">
                <button type="button" class="btn-wow btn-wow--cta btn-sm" onclick="window.location.href='{{ $giftCardUrl }}'; return false;">
                    <span class="btn-label">View gift cards</span>
                </button>
            </div>
        </footer>
    </article>
</a>
</div>
