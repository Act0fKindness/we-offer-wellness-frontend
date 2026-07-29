@php
    $giftCardUrl = url('/giftcards');
    $giftCardTitle = $titleFormatted ?? 'Gift Card';
    $giftCardProvider = $providerFormatted ?? null;
    $giftCardSummary = $benefitTextClean ?: 'A flexible digital gift card you can send instantly.';
    $giftCardPriceLabel = is_numeric($priceMin ?? null)
        ? '£' . number_format((float) $priceMin, 2)
        : 'Gift cards';
@endphp

@once
    <style>
        .gift-card-v4-scope{
            --gift-v4-ink:#101828;
            --gift-v4-muted:#667085;
            --gift-v4-line:#dde3ea;
            --gift-v4-green:#4f9381;
            --gift-v4-green-dark:#417c6d;
            --gift-v4-green-soft:#e8f5f1;
            --gift-v4-gold-soft:#ffe5b3;
            --gift-v4-blue-soft:#e8f0ff;
            width:100%;
        }

        .gift-card-v4-scope .product-v4-1-card{
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
            border-radius:13px;
            box-shadow:0 12px 34px rgba(16,24,40,.045);
            transition:transform 180ms ease,border-color 180ms ease,box-shadow 180ms ease;
        }

        .gift-card-v4-scope .product-v4-1-card:hover,
        .gift-card-v4-scope .product-v4-1-card:focus-within{
            transform:translateY(-3px);
            border-color:rgba(79,147,129,.42);
            box-shadow:0 20px 48px rgba(16,24,40,.085);
        }

        .gift-card-v4-scope .product-v4-1-card__surface-link{
            position:absolute;
            inset:0;
            z-index:4;
        }

        .gift-card-v4-scope .product-v4-1-card__media{
            position:relative;
            height:176px;
            overflow:hidden;
            background:
                radial-gradient(circle at 24% 28%,rgba(79,147,129,.18),transparent 30%),
                radial-gradient(circle at 74% 70%,rgba(37,74,133,.12),transparent 32%),
                linear-gradient(135deg,#eef2f4 0%,#f8fbfd 100%);
        }

        .gift-card-v4-scope .product-v4-1-card__media img,
        .gift-card-v4-scope .product-v4-1-card__fallback{
            width:100%;
            height:100%;
            display:block;
            object-fit:cover;
        }

        .gift-card-v4-scope .product-v4-1-card__shade{
            position:absolute;
            inset:0 0 auto;
            height:72px;
            background:linear-gradient(180deg,rgba(16,24,40,.34),rgba(16,24,40,0));
            pointer-events:none;
        }

        .gift-card-v4-scope .product-v4-1-card__signal{
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
        }

        .gift-card-v4-scope .product-v4-1-card__badges{
            position:absolute;
            left:10px;
            bottom:10px;
            z-index:3;
            display:flex;
            flex-wrap:wrap;
            gap:6px;
            margin:0;
            padding:0;
            list-style:none;
        }

        .gift-card-v4-scope .product-v4-1-card__badge{
            min-height:26px;
            display:inline-flex;
            align-items:center;
            padding:0 9px;
            border:1px solid rgba(240,200,121,.9);
            border-radius:999px;
            background:rgba(255,229,179,.96);
            color:#6f4b10;
            font-size:11px;
            font-weight:700;
            white-space:nowrap;
        }

        .gift-card-v4-scope .product-v4-1-card__badge--blue{
            border-color:#c7d8fb;
            background:rgba(232,240,255,.96);
            color:#254a85;
        }

        .gift-card-v4-scope .product-v4-1-card__body{
            position:relative;
            z-index:2;
            flex:1;
            display:flex;
            flex-direction:column;
            gap:8px;
            padding:13px 14px 12px;
            pointer-events:none;
        }

        .gift-card-v4-scope .product-v4-1-card__type{
            color:var(--gift-v4-green);
            font-size:11px;
            font-weight:700;
            letter-spacing:.12em;
            text-transform:uppercase;
        }

        .gift-card-v4-scope .product-v4-1-card__title{
            display:-webkit-box;
            min-height:45px;
            margin:0;
            overflow:hidden;
            color:var(--gift-v4-ink);
            font-size:20px;
            font-weight:500;
            line-height:1.08;
            letter-spacing:-.045em;
            -webkit-box-orient:vertical;
            -webkit-line-clamp:2;
        }

        .gift-card-v4-scope .product-v4-1-card__provider,
        .gift-card-v4-scope .product-v4-1-card__summary{
            margin:0;
            color:var(--gift-v4-muted);
            font-size:12.75px;
            line-height:1.42;
        }

        .gift-card-v4-scope .product-v4-1-card__summary{
            display:-webkit-box;
            min-height:calc(1.42em * 3);
            max-height:calc(1.42em * 3);
            overflow:hidden;
            color:#344054;
            -webkit-box-orient:vertical;
            -webkit-line-clamp:3;
        }

        .gift-card-v4-scope .product-v4-1-card__meta{
            display:flex;
            flex-wrap:wrap;
            gap:6px;
            min-height:26px;
            margin-top:auto;
        }

        .gift-card-v4-scope .product-v4-1-card__chip{
            min-height:27px;
            display:inline-flex;
            align-items:center;
            padding:0 9px;
            border:1px solid rgba(79,147,129,.24);
            border-radius:999px;
            background:var(--gift-v4-green-soft);
            color:#2f6f60;
            font-size:12.25px;
            white-space:nowrap;
        }

        .gift-card-v4-scope .product-v4-1-card__delivery{
            min-height:62px;
            margin-top:4px;
            padding:8px;
            border:1px solid rgba(79,147,129,.24);
            border-radius:11px;
            background:linear-gradient(180deg,rgba(232,245,241,.64),rgba(255,255,255,.94));
        }

        .gift-card-v4-scope .product-v4-1-card__delivery strong,
        .gift-card-v4-scope .product-v4-1-card__delivery span{
            display:block;
        }

        .gift-card-v4-scope .product-v4-1-card__delivery strong{
            color:#2f6f60;
            font-size:11.7px;
        }

        .gift-card-v4-scope .product-v4-1-card__delivery span{
            margin-top:5px;
            color:var(--gift-v4-muted);
            font-size:10.9px;
        }

        .gift-card-v4-scope .product-v4-1-card__footer{
            position:relative;
            z-index:2;
            display:grid;
            grid-template-columns:1fr;
            gap:10px;
            padding:12px 14px;
            border-top:1px solid #edf0f2;
            background:#fff;
        }

        .gift-card-v4-scope .product-v4-1-card__price small,
        .gift-card-v4-scope .product-v4-1-card__price strong{
            display:block;
        }

        .gift-card-v4-scope .product-v4-1-card__price small{
            color:var(--gift-v4-muted);
            font-size:12px;
            line-height:1.1;
        }

        .gift-card-v4-scope .product-v4-1-card__price strong{
            margin-top:3px;
            color:var(--gift-v4-ink);
            font-size:23px;
            font-weight:600;
            line-height:1;
            letter-spacing:-.05em;
        }

        .gift-card-v4-scope .product-v4-1-card__actions{
            display:flex;
            gap:7px;
            align-items:center;
        }

        .gift-card-v4-scope .product-v4-1-card__actions a{
            position:relative;
            z-index:5;
            width:100%;
            min-height:38px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            pointer-events:auto;
        }

        @media (max-width:620px){
            .gift-card-v4-scope .product-v4-1-card{
                width:clamp(260px,86vw,280px);
                min-width:clamp(260px,86vw,280px);
                max-width:none;
                min-height:auto;
            }
        }
    </style>
@endonce

<div class="gift-card-v4-scope">
    <article class="product-v4-1-card" aria-label="Gift card {{ $product->id ?? $giftCardTitle }}">
        <a href="{{ $giftCardUrl }}" class="product-v4-1-card__surface-link" aria-label="View {{ $giftCardTitle }}"></a>

        <div class="product-v4-1-card__media">
            @if($hasDisplayableImage)
                <img src="{{ $image }}" alt="{{ $giftCardTitle }}" loading="lazy">
            @else
                <div class="product-v4-1-card__fallback">Gift Card</div>
            @endif

            <div class="product-v4-1-card__shade"></div>
            <span class="product-v4-1-card__signal">Digital gift card</span>
            <div class="product-v4-1-card__badges">
                <span class="product-v4-1-card__badge">Gift card</span>
                <span class="product-v4-1-card__badge product-v4-1-card__badge--blue">Instant delivery</span>
            </div>
        </div>

        <div class="product-v4-1-card__body">
            <div class="product-v4-1-card__type">Gift card</div>
            <h3 class="product-v4-1-card__title">{{ $giftCardTitle }}</h3>

            @if($giftCardProvider)
                <p class="product-v4-1-card__provider">with {{ $giftCardProvider }}</p>
            @endif

            <p class="product-v4-1-card__summary">{{ $giftCardSummary }}</p>

            <div class="product-v4-1-card__meta">
                <span class="product-v4-1-card__chip">Instant email delivery</span>
            </div>

            <div class="product-v4-1-card__delivery">
                <strong>Ready to send</strong>
                <span>Choose your amount and email it instantly.</span>
            </div>
        </div>

        <footer class="product-v4-1-card__footer">
            <div class="product-v4-1-card__price">
                <small>From</small>
                <strong>{{ $giftCardPriceLabel }}</strong>
            </div>
            <div class="product-v4-1-card__actions">
                <a href="{{ $giftCardUrl }}" class="btn-wow btn-wow--cta btn-sm">
                    <span class="btn-label">View gift cards</span>
                </a>
            </div>
        </footer>
    </article>
</div>
