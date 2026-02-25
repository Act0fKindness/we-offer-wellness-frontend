@extends('layouts.app')

@section('title', $seo['title'] ?? 'Practitioner')
@section('meta_description', $seo['description'] ?? '')
@section('meta_robots', $seo['robots'] ?? 'noindex,follow')

@section('content')
<section class="section">
  <div class="container-page">
    <h1 class="display-6 mb-2">Practitioner Profile</h1>
    <p class="text-muted">Profile for: <strong>{{ $slug }}</strong></p>
    <p class="mt-3">This practitioner page is being upgraded for the new site. In the meantime, browse therapies and events to book.</p>
    <p class="mt-2"><a class="btn-wow btn-wow--cta" href="/therapies">Browse therapies</a></p>
  </div>
</section>
@endsection

