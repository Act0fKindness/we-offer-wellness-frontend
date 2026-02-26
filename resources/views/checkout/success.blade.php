@extends('layouts.app')

@section('content')
<section class="section">
  <div class="container-page">
    <div class="hero-card">
      <div class="hero-icon" aria-hidden="true">✓</div>
      <h1>Thank you! Your booking is confirmed.</h1>
      <p>
        We’ve emailed your receipt and next steps.
        If you checked out as a guest, create or log into your account using the same email to track your sessions.
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
