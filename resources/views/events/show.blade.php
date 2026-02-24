{{-- resources/views/events/show.blade.php --}}
@extends('layouts.app')

@push('head')
  <title>{{ $seo['title'] ?? (($event['title'] ?? 'Event').' | We Offer Wellness™') }}</title>
  @if(!empty($seo['description']))<meta name="description" content="{{ $seo['description'] }}">@endif
  @if(!empty($seo['robots']))<meta name="robots" content="{{ $seo['robots'] }}">@endif
  @if(!empty($seo['canonical']))<link rel="canonical" href="{{ $seo['canonical'] }}">@endif
@endpush

@section('content')
@php
  $title = $event['title'] ?? 'Event';
  $img = $event['image'] ?? ($event['featured_image'] ?? null);
  $summary = $event['summary'] ?? null;
  $body = $event['description'] ?? ($event['body_html'] ?? ($event['content'] ?? null));
@endphp

<section class="section">
  <div class="container-page">
    <div class="mb-4">
      <div class="kicker">Events & Workshops</div>
      <h1>{{ $title }}</h1>
      @if($summary)
        <p class="text-ink-600 mt-2" style="max-width:70ch;">{{ $summary }}</p>
      @endif
    </div>

    @if($img)
      <div class="card mb-4 overflow-hidden" style="border-radius:18px;">
        <img src="{{ $img }}" alt="{{ $title }}" style="width:100%; height:auto; display:block;">
      </div>
    @endif

    <div class="grid lg:grid-cols-3 gap-4">
      <div class="lg:col-span-2">
        <div class="card p-4" style="border-radius:18px;">
          @if($body)
            {!! $body !!}
          @else
            <p class="text-muted mb-0">Details coming soon.</p>
          @endif
        </div>
      </div>

      <aside>
        <div class="card p-3" style="border-radius:18px;">
          <div class="text-muted mb-2">Quick details</div>

          @foreach([
            'Date' => ($event['date'] ?? $event['start_date'] ?? null),
            'Time' => ($event['time'] ?? $event['start_time'] ?? null),
            'Location' => ($event['location'] ?? $event['venue'] ?? null),
            'Format' => ($event['format'] ?? null),
          ] as $label => $val)
            @if($val)
              <div class="mb-2">
                <div style="font-weight:650;">{{ $label }}</div>
                <div class="text-ink-600">{{ is_array($val) ? json_encode($val) : $val }}</div>
              </div>
            @endif
          @endforeach

          <div class="mt-3">
            <a class="btn btn-light w-100" href="{{ route('events.index') }}">Back to events</a>
          </div>
        </div>
      </aside>
    </div>
  </div>
</section>
@endsection
