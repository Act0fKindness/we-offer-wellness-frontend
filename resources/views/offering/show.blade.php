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
          <h3 class="h5">About this experiences</h3>
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
        @include('offering.partials.advanced_buybox')
        @include('offering.partials.variant_helper')

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

 
