@extends('layouts.app')

@section('content')

@php
  $p = $product ?? [];
  $title = $p['title'] ?? 'Experience';
  $type = $p['type'] ?? 'experience';
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
@endphp

<section class="section">
  <div class="container-page">
    <div class="row g-4">
      <div class="col-12 col-lg-7">
        <div class="card p-2">
          @if(!empty($images))
            <div class="d-flex gap-2 flex-wrap">
              @foreach ($images as $img)
                <div class="flex-shrink-0" style="width: 160px; height: 120px; overflow: hidden; border-radius: 10px; border:1px solid var(--ink-200)">
                  <img src="{{ $img }}" alt="{{ $title }}" style="width:100%;height:100%;object-fit:cover">
                </div>
              @endforeach
            </div>
          @else
            <div class="ratio ratio-16x9 bg-ink-100 rounded"></div>
          @endif
        </div>

        @if($body !== '')
        <div class="card p-4 mt-4">
          <h3 class="h5">About this {{ strtolower($type) }}</h3>
          <div class="prose" style="max-width: 70ch;">{!! $body !!}</div>
        </div>
        @endif

        @if($what !== '')
        <div class="card p-4 mt-4">
          <h3 class="h6 m-0">What to expect</h3>
          <div class="mt-2 text-ink-700">{!! nl2br(e($what)) !!}</div>
        </div>
        @endif

        @if($included !== '')
        <div class="card p-4 mt-4">
          <h3 class="h6 m-0">What’s included</h3>
          <div class="mt-2 text-ink-700">{!! nl2br(e($included)) !!}</div>
        </div>
        @endif

        @if($safety !== '' || $contra !== '')
        <div class="card p-4 mt-4">
          <h3 class="h6 m-0">Safety & contraindications</h3>
          @if($safety!=='')<div class="mt-2 text-ink-700">{!! nl2br(e($safety)) !!}</div>@endif
          @if($contra!=='')<div class="mt-2 text-ink-700">{!! nl2br(e($contra)) !!}</div>@endif
        </div>
        @endif
      </div>

      <div class="col-12 col-lg-5">
        <div class="card p-4">
          <div class="kicker mb-1">{{ ucfirst($type) }}</div>
          <h1 class="h3 m-0">{{ $title }}</h1>
          <div class="d-flex align-items-center gap-2 mt-2">
            @if($rating)
              <div class="rating-text">★ {{ number_format((float)$rating,1) }}</div>
            @endif
            @if($reviewCount)
              <div class="text-muted small">({{ $reviewCount }})</div>
            @endif
          </div>
          @if($priceMin !== null)
            <div class="mt-3 h4">£{{ number_format((float)$priceMin, 2) }} <small class="text-muted">from</small></div>
          @endif

          @if(!empty($options))
            <div class="mt-3">
              @foreach($options as $opt)
                <div class="mb-2">
                  <div class="text-muted small">{{ $opt['name'] ?? 'Option' }}</div>
                  <div class="d-flex flex-wrap gap-2 mt-1">
                    @foreach(($opt['values'] ?? []) as $val)
                      <span class="chip">{{ $val['value'] ?? $val }}</span>
                    @endforeach
                  </div>
                </div>
              @endforeach
            </div>
          @endif

          @if(!empty($variants))
            <div class="mt-3">
              <div class="text-muted small mb-2">Available options</div>
              <div class="d-grid gap-2">
                @foreach($variants as $v)
                  @php $vp = $v['price'] ?? null; if(is_numeric($vp) && $vp >= 1000) $vp = $vp/100; @endphp
                  <div class="d-flex align-items-center justify-content-between p-2 rounded border">
                    <div class="text-ink-800">{{ implode(' · ', array_filter($v['options'] ?? [])) }}</div>
                    <div class="fw-semibold">@if($vp!==null) £{{ number_format((float)$vp,2) }} @endif</div>
                  </div>
                @endforeach
              </div>
            </div>
          @endif

          <div class="mt-4 d-flex gap-2">
            <a href="#" class="btn-wow btn-wow--cta btn-arrow"><span class="btn-label">Book now</span></a>
            <a href="/contact?topic=booking&id={{ $p['id'] ?? '' }}&title={{ urlencode($title) }}" class="btn-wow btn-wow--outline">Enquire</a>
          </div>
        </div>

        @if(!empty($p['reviews']))
          <div class="card p-4 mt-4">
            <h3 class="h6 m-0">Client reviews</h3>
            <div class="mt-3 d-grid gap-3">
              @foreach($p['reviews'] as $r)
                <div class="p-3 rounded border">
                  <div class="small text-muted">★ {{ (int)($r['rating'] ?? 0) }}/5</div>
                  <div class="mt-1">{{ $r['review'] ?? '' }}</div>
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

