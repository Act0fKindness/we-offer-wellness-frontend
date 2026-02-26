@extends('layouts.app')

@section('content')
@php
  $order = $order ?? null;
  $items = $order?->items ?? [];
@endphp
<section class="section">
  <div class="container-page">
    <div class="hero-card">
      <div class="hero-icon" aria-hidden="true">✓</div>
      <h1>Thank you! Your booking is confirmed.</h1>
      <p>Order #{{ $order?->id ?? '—' }} · Paid £{{ number_format(($order?->amount_total ?? 0)/100, 2) }}</p>
      @if(!empty($items))
        <div class="order-card">
          @foreach($items as $item)
            <div class="order-line">
              <span>{{ $item->quantity }} × {{ $item->name }}</span>
              <span>£{{ number_format(($item->unit_amount ?? 0)/100, 2) }}</span>
            </div>
          @endforeach
        </div>
      @endif
      <p>
        We’ve emailed your receipt and next steps. If you checked out as a guest, create or log into your account using the same email to manage your sessions.
      </p>
      <div class="cta-row">
        <a class="btn-wow btn-wow--cta" href="/login">Log in</a>
        <a class="btn-wow btn-wow--outline" href="/register">Create account</a>
        <a class="link-wow" href="/search">Discover more therapies</a>
      </div>
    </div>
  </div>
</section>
@endsection
