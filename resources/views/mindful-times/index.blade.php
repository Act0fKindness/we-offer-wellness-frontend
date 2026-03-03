@extends('layouts.app')

@section('title', $seo['title'] ?? 'Mindful Times')
@section('meta_description', $seo['description'] ?? '')
@section('meta_robots', $seo['robots'] ?? 'index,follow')

@section('content')
<section class="section">
  <div class="container-page">
    <h1 class="display-5 mb-3">Mindful Times</h1>
    <p class="lead text-muted">Guides, interviews and practical insights for everyday wellbeing. More coming soon.</p>
  </div>
  @include('home.sections.mindfultimes_guides_interviews')
  @include('home.sections.mindful_times_more')
  @include('home.sections.mindful_times_ribbon')
  @include('home.sections.partners')
@endsection

