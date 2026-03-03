@extends('layouts.app')

@section('title', $seo['title'] ?? 'Corporate Wellness — Coming Soon')
@section('meta_description', $seo['description'] ?? '')
@section('meta_robots', $seo['robots'] ?? 'noindex,follow')

@section('content')
<section class="section">
  <div class="container-page">
    <h1 class="display-5 mb-3">Corporate Wellness</h1>
    <p class="lead text-muted">We’re building an updated workplace wellbeing offering. Register your interest and we’ll be in touch.</p>
    <p class="mt-3"><a href="mailto:hello@weofferwellness.co.uk?subject=Corporate%20Wellness">Email the team</a></p>
  </div>
</section>
@endsection

