@extends('layouts.app')

@section('title', $seo['title'] ?? 'FAQ | We Offer Wellness®')
@section('meta_description', $seo['description'] ?? '')
@section('meta_robots', 'index,follow')

@section('content')
<section class="section">
  <div class="container-page">
    @include('partials.breadcrumbs', [
      'crumbs' => [
        ['label' => 'Home', 'url' => url('/')],
        ['label' => 'Help Centre', 'url' => url('/help')],
        ['label' => 'FAQ'],
      ],
      'schemaUrl' => url('/help/faq'),
    ])

    <div class="mb-4">
      <p class="text-uppercase fw-bold text-muted mb-2" style="letter-spacing:.18em;font-size:.76rem;">Help Centre</p>
      <h1 class="display-5 mb-3">Frequently asked questions</h1>
      <p class="lead text-muted mb-0">Answers to the most common booking, payment and account questions.</p>
    </div>

    <div class="row g-4">
      <div class="col-12 col-lg-4">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body">
            <h2 class="h5">Popular topics</h2>
            <ul class="list-unstyled mb-0">
              <li class="mb-2"><a href="#booking">Bookings</a></li>
              <li class="mb-2"><a href="#payments">Payments</a></li>
              <li class="mb-2"><a href="#account">Account</a></li>
              <li class="mb-2"><a href="#sessions">Online sessions</a></li>
              <li class="mb-2"><a href="#safety">Safety</a></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm mb-4" id="booking">
          <div class="card-body">
            <h2 class="h4">How do I manage a booking?</h2>
            <p class="mb-0">Visit your confirmation email to reschedule or cancel, or message the practitioner directly from your account.</p>
          </div>
        </div>
        <div class="card border-0 shadow-sm mb-4" id="payments">
          <div class="card-body">
            <h2 class="h4">What if I need to cancel?</h2>
            <p class="mb-0">Each listing includes a cancellation window. If you cannot find it, <a href="/contact?topic=support">contact support</a>.</p>
          </div>
        </div>
        <div class="card border-0 shadow-sm mb-4" id="account">
          <div class="card-body">
            <h2 class="h4">How do I reset my password?</h2>
            <p class="mb-0">Use the forgot-password link on sign in. If the email does not arrive, check spam/junk and search for We Offer Wellness®.</p>
          </div>
        </div>
        <div class="card border-0 shadow-sm mb-4" id="sessions">
          <div class="card-body">
            <h2 class="h4">Do I need any equipment?</h2>
            <p class="mb-0">Most therapies only require comfortable clothing and a quiet space. Classes will note props if needed.</p>
          </div>
        </div>
        <div class="card border-0 shadow-sm" id="safety">
          <div class="card-body">
            <h2 class="h4">How do I check whether a session is suitable?</h2>
            <p class="mb-0">Read the listing details and our <a href="/safety-and-contraindications">Safety &amp; Contraindications</a> guidance before booking if you are unsure.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
