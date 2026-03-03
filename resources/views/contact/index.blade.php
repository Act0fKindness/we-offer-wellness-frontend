@extends('layouts.app')

@section('title', $seo['title'] ?? 'Contact')
@section('meta_description', $seo['description'] ?? '')

@section('content')
<section class="section">
  <div class="container-page">
    <h1 class="display-5 mb-3">Contact</h1>
    <p class="lead text-muted">We’re here to help. Choose a topic so we can route your message:</p>
    <ul class="list-unstyled mt-3">
      <li><a href="/contact?topic=support">Booking support</a></li>
      <li><a href="/partners">Partners</a></li>
      <li><a href="mailto:hello@weofferwellness.co.uk">Email: hello@weofferwellness.co.uk</a></li>
    </ul>
  </div>
</section>
@endsection

