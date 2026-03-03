@php
    $rating = is_null($rating ?? null) ? null : max(0, min(5, round((float) $rating, 1)));
    $reviews = max(0, (int) ($reviews ?? 0));
    $fullStars = $rating !== null ? (int) floor($rating) : 0;
    $hasHalf = $rating !== null ? (($rating - $fullStars) >= 0.5) : false;
    if ($hasHalf && $fullStars === 5) {
        $hasHalf = false;
    }
    if ($reviews > 0 && $rating !== null) {
        $label = 'Rating '.number_format($rating, 1).' out of 5 from '.number_format($reviews).' reviews';
    } elseif ($reviews > 0) {
        $label = number_format($reviews).' reviews published';
    } else {
        $label = 'No reviews yet';
    }
@endphp
<div class="rating-row" aria-label="{{ $label }}">
    <span class="stars" aria-hidden="true">
        @for ($i = 1; $i <= 5; $i++)
            @php
                $class = 'star star--empty';
                if ($i <= $fullStars) {
                    $class = 'star star--filled';
                } elseif ($hasHalf && $i === $fullStars + 1) {
                    $class = 'star star--half';
                }
            @endphp
            <span class="{{ $class }}"></span>
        @endfor
    </span>
    <span class="rating-count">
        {{ $reviews > 0 ? '('.number_format($reviews).')' : 'New' }}
    </span>
</div>
