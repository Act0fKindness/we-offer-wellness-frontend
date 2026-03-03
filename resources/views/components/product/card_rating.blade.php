@include('partials.styles.ratings')

@php
    $rating = is_null($rating ?? null) ? null : round((float) $rating, 1);
    $reviews = max(0, (int) ($reviews ?? 0));
    $vendorRating = is_null($vendorRating ?? null) ? null : round((float) $vendorRating, 1);
    $vendorReviews = max(0, (int) ($vendorReviews ?? 0));
    if (($reviews === 0 || $rating === null) && $vendorReviews > 0 && $vendorRating !== null) {
        $rating = $vendorRating;
        $reviews = $vendorReviews;
    }
    $rating = max(0, min(5, $rating ?? 0));
    $fullStars = (int) floor($rating);
    $hasHalf = ($rating - $fullStars) >= 0.5 && $fullStars < 5;
    $label = 'Rating '.number_format($rating, 1).' out of 5 from '.number_format($reviews).' '.\Illuminate\Support\Str::plural('review', $reviews);
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
        <span class="wow-review-score">{{ number_format($rating, 1) }}</span>
        <span class="wow-review-count">{{ number_format($reviews) }} {{ \Illuminate\Support\Str::plural('review', $reviews) }}</span>
    </span>
</div>
