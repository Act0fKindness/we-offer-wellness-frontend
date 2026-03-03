@include('partials.styles.ratings')

@php
    $productModel = $product ?? null;
    $ratingValue = null;
    $reviewCount = 0;

    if ($productModel instanceof \Illuminate\Database\Eloquent\Model && method_exists($productModel, 'vendorReviewStats')) {
        $stats = $productModel->vendorReviewStats();
        $ratingValue = $stats['avg'] ?? null;
        $reviewCount = (int) ($stats['count'] ?? 0);
    }

    if ($productModel instanceof \Illuminate\Database\Eloquent\Model) {
        if ($ratingValue === null) {
            $ratingValue = data_get($productModel, 'reviews_avg_rating');
        }
        if ($reviewCount === 0) {
            $reviewCount = (int) (data_get($productModel, 'reviews_count') ?? 0);
        }
    }

    if ($ratingValue === null && isset($product)) {
        $ratingValue = data_get($product, 'rating');
    }
    if ($reviewCount === 0 && isset($product)) {
        $reviewCount = (int) (data_get($product, 'review_count') ?? data_get($product, 'reviews_count') ?? 0);
    }

    if ($ratingValue === null) {
        $ratingValue = is_null($rating ?? null) ? null : round((float) $rating, 1);
    }
    if ($reviewCount === 0) {
        $reviewCount = max(0, (int) ($reviews ?? 0));
    }

    $ratingValue = max(0, min(5, $ratingValue ?? 0));
    $fullStars = (int) floor($ratingValue);
    $hasHalf = ($ratingValue - $fullStars) >= 0.5 && $fullStars < 5;
    $label = 'Rating ' . number_format($ratingValue, 1) . ' out of 5 from ' . number_format($reviewCount) . ' ' . \Illuminate\Support\Str::plural('review', $reviewCount);
@endphp

<div class="wow-rating wow-review-row" aria-label="{{ $label }}">
    <span class="stars wow-review-stars" aria-hidden="true">
        @for ($i = 1; $i <= 5; $i++)
            @php
                $class = 'star wow-review-star';
                if ($i <= $fullStars) {
                    $class .= ' wow-review-star--full';
                } elseif ($hasHalf && $i === $fullStars + 1) {
                    $class .= ' wow-review-star--half';
                }
            @endphp
            <span class="{{ $class }}"></span>
        @endfor
    </span>
    <span class="wow-review-meta">
        <span class="wow-review-score">{{ number_format($ratingValue, 1) }}</span>
        <span class="wow-review-count">{{ number_format($reviewCount) }} {{ \Illuminate\Support\Str::plural('review', $reviewCount) }}</span>
    </span>
</div>
