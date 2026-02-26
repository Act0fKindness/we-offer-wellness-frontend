@extends('layouts.app')

@section('content')
@php $order = $order ?? null; @endphp
<section class="section">
  <div class="container-page">
    <div class="hero-card hero-card--warn">
      <div class="hero-icon" aria-hidden="true">!</div>
      <h1>Your checkout wasn’t completed.</h1>
      @if($order)
        <p>We held Order #{{ $order->id }}, but the payment didn’t go through. Your cart still has the items below.</p>
      @else
        <p>No worries—your cart is still intact. You can try the payment again or adjust your selection.</p>
      @endif
      <div class="cta-row">
        <a class="btn-wow btn-wow--cta" href="/cart">Return to cart</a>
        <a class="btn-wow btn-wow--outline" href="/search">Keep browsing</a>
        <a class="link-wow" href="/help">Need help?</a>
      </div>
    </div>
  </div>
</section>
@endsection
