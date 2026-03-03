@extends('layouts.app')

@section('title', $seo['title'] ?? 'Practitioners')
@section('meta_description', $seo['description'] ?? '')

@section('content')
<section class="section">
  <div class="container-page">
    <h1 class="display-5 mb-4">Practitioners</h1>
    <p class="text-muted">Our verified practitioners and facilitators. Practitioner profiles and booking will return soon.</p>
  </div>
</section>
@endsection

