@extends('layouts.app')

@section('content')

@php
  $p = $product ?? [];
  $title = $p['title'] ?? 'Therapy';
  $type = $p['type'] ?? 'therapy';
  $rating = $p['rating'] ?? null;
  $reviewCount = $p['review_count'] ?? 0;
  $priceMin = $p['price_min'] ?? ($p['price'] ?? null);
  if (is_numeric($priceMin) && $priceMin >= 1000) { $priceMin = $priceMin / 100; }
  $images = $p['images'] ?? ($p['image'] ? [ $p['image'] ] : []);
  $summary = trim((string)($p['summary'] ?? ''));
  $body = trim((string)($p['body_html'] ?? ''));
  $what = trim((string)($p['what_to_expect'] ?? ''));
  $included = trim((string)($p['included'] ?? ''));
  $safety = trim((string)($p['safety_notes'] ?? ''));
  $contra = trim((string)($p['contraindications'] ?? ''));
  $variants = $p['variants'] ?? [];
  $options = $p['options'] ?? [];
  $reviewPreviewWords = 55;
  // Extract Location(s) list from options
  $locationValues = [];
  foreach (($options ?? []) as $opt) {
    $name = isset($opt['name']) ? strtolower($opt['name']) : '';
    if (str_contains($name, 'location')) {
      foreach (($opt['values'] ?? []) as $v) {
        $val = is_array($v) ? ($v['value'] ?? '') : (string) $v;
        $val = trim($val);
        if ($val !== '') { $locationValues[] = $val; }
      }
    }
  }
  // Make unique while preserving order
  $seen = [];
  $locationsList = [];
  foreach ($locationValues as $lv) {
    $k = strtolower($lv);
    if (!isset($seen[$k])) { $seen[$k] = true; $locationsList[] = $lv; }
  }
  $participantRange = null;
  foreach (($options ?? []) as $opt) {
    $name = strtolower((string)($opt['name'] ?? $opt['meta_name'] ?? ''));
    if ($name === '' || !str_contains($name, 'person')) {
      continue;
    }
    $vals = $opt['values'] ?? [];
    if (!is_array($vals) || empty($vals)) {
      break;
    }
    $numbers = [];
    $allNumeric = true;
    foreach ($vals as $val) {
      $raw = is_array($val) ? ($val['value'] ?? '') : (string) $val;
      $raw = trim($raw);
      if ($raw === '' || !preg_match('/^\d+$/', $raw)) {
        $allNumeric = false;
        break;
      }
      $numbers[] = (int) $raw;
    }
    if ($allNumeric && !empty($numbers)) {
      sort($numbers);
      $min = $numbers[0];
      $max = $numbers[count($numbers) - 1];
      $participantRange = [
        'label' => $min === $max ? 'For ' . $min : ('For ' . $min . '–' . $max),
        'suffix' => $max === 1 ? 'participant' : 'participants',
      ];
    }
    break;
  }
@endphp

<section class="section product-page">
  <style>
    /* Force Manrope for all headings/titles on product page */
    .product-page h1,
    .product-page h2,
    .product-page h3,
    .product-page .wow-section-title,
    .product-page .wow-acc-title,
    .product-page .wow-title {
      font-family: 'Manrope', var(--bs-font-sans-serif) !important;
    }
    .product-page h1 { font-weight: 600 !important; }

    /* FOMO text styling within content-bottom */
    .content-bottom .fomo {
      margin: 0 0 8px;
      font-size: var(--fomo);
      font-weight: 600;
      color: rgba(11, 18, 32, .84);
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
  </style>
  <div class="container-page">
    <div class="row g-4">
      <div class="col-12 col-lg-8">
        <div class="mb-3">
          <div class="kicker mb-1">{{ ucfirst($type ?? 'Therapy') }}</div>
          <h1 class="h2 m-0">{{ $title ?? 'Offering' }}</h1>
        </div>
        @if(!empty($images))
          @include('offering.partials.gallery', ['images' => $images, 'title' => $title])
        @else
          <div class="card p-2">
            <div class="ratio ratio-16x9 bg-ink-100 rounded"></div>
          </div>
        @endif

        @include('offering.partials.details_accordion', [
          'summary' => $summary,
          'body' => $body,
          'what' => $what,
          'included' => $included,
          'safety' => $safety,
          'contra' => $contra,
          'locationsList' => $locationsList,
          'participantRange' => $participantRange,
        ])
      </div>

      <div class="col-12 col-lg-4">
        @include('offering.partials.advanced_buybox')
        @include('offering.partials.variant_helper')

        @php
          $clientReviews = $p['client_reviews'] ?? [];
        @endphp
        @if(!empty($clientReviews))
          <div class="card p-4 mt-4">
            <h3 class="h6 m-0">Client reviews</h3>
            <div class="mt-3 d-grid gap-3">
              @foreach($clientReviews as $review)
                @php
                  $body = trim((string)($review['body'] ?? ''));
                  $preview = $body;
                  $needsToggle = false;
                  if ($body !== '') {
                    $preview = \Illuminate\Support\Str::words($body, $reviewPreviewWords, '…');
                    $needsToggle = ($preview !== $body);
                  }
                @endphp
                <div class="p-3 border rounded bg-ink-50 js-review-card">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-semibold text-ink-900">{{ $review['author'] ?? 'Verified client' }}</div>
                    <div class="text-warning small" aria-label="{{ $review['rating'] ?? 0 }} out of 5 stars">
                      @for($i = 1; $i <= 5; $i++)
                        <i class="bi {{ $i <= ($review['rating'] ?? 0) ? 'bi-star-fill' : 'bi-star' }}"></i>
                      @endfor
                    </div>
                  </div>
                  <p class="mb-2 text-ink-800 js-review-body" style="white-space:pre-line;" data-expanded="false">{{ $preview }}</p>
                  @if($needsToggle)
                    <button type="button" class="btn btn-link px-0 text-decoration-underline fw-semibold small js-review-toggle" aria-expanded="false">Read more</button>
                    <template class="js-review-preview">{{ $preview }}</template>
                    <template class="js-review-full">{{ $body }}</template>
                  @endif
                  <div class="small text-muted">{{ $review['date'] ?? '' }}</div>
                </div>
              @endforeach
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</section>

@endsection

@push('scripts')
<script>
(function(){
  function hydrateReview(card){
    var body = card.querySelector('.js-review-body');
    var toggle = card.querySelector('.js-review-toggle');
    if(!body || !toggle) return;
    var fullTpl = card.querySelector('.js-review-full');
    var previewTpl = card.querySelector('.js-review-preview');
    var fullText = fullTpl ? fullTpl.textContent.trim() : '';
    var previewText = previewTpl ? previewTpl.textContent.trim() : body.textContent.trim();
    if(!fullText || fullText === previewText){
      toggle.remove();
      return;
    }
    var expanded = false;
    function apply(){
      body.textContent = expanded ? fullText : previewText;
      toggle.textContent = expanded ? 'Read less' : 'Read more';
      toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
      body.dataset.expanded = expanded ? 'true' : 'false';
    }
    toggle.addEventListener('click', function(){
      expanded = !expanded;
      apply();
    });
    apply();
  }
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.js-review-card').forEach(hydrateReview);
  });
})();
</script>
@endpush

 
