@extends('layouts.app')

@section('title', ($document->title ?? 'Legal') . ' | We Offer Wellness™')

@section('content')
<section class="section py-5">
  <div class="container legal-document">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-9 col-xl-8">
        <p class="kicker mb-2">{{ $platform->name }}</p>
        <h1 class="mb-3">{{ $document->title }}</h1>
        @if($document->effective_date)
          <p class="text-muted mb-4">Last updated: {{ $document->effective_date->format('d/m/Y') }}</p>
        @endif
        <div class="legal-document__body rte">
          {!! $document->content !!}
        </div>
      </div>
    </div>
  </div>
</section>

@push('styles')
<style>
  .legal-document__body {
    font-size: 1.05rem;
    line-height: 1.75;
    color: #1f2937;
  }

  .legal-document__body > :first-child {
    margin-top: 0;
  }

  .legal-document__body p {
    margin: 0 0 1rem;
  }

  .legal-document__body h2,
  .legal-document__body h3,
  .legal-document__body h4,
  .legal-document__body h5,
  .legal-document__body h6 {
    margin: 1.75rem 0 0.75rem;
    line-height: 1.25;
    color: #0f172a;
  }

  .legal-document__body h2 {
    font-size: 1.6rem;
  }

  .legal-document__body h3 {
    font-size: 1.35rem;
  }

  .legal-document__body h4 {
    font-size: 1.15rem;
  }

  .legal-document__body ul,
  .legal-document__body ol {
    margin: 0 0 1rem 1.5rem;
    padding-left: 1.25rem;
  }

  .legal-document__body li {
    margin-bottom: 0.55rem;
  }

  .legal-document__body li > p {
    margin-bottom: 0.35rem;
  }

  .legal-document__body table {
    width: 100%;
    margin: 1rem 0 1.5rem;
    border-collapse: collapse;
  }

  .legal-document__body th,
  .legal-document__body td {
    border: 1px solid rgba(15, 23, 42, 0.12);
    padding: 0.75rem 0.875rem;
    vertical-align: top;
  }

  .legal-document__body th {
    background: #f8fafc;
    font-weight: 600;
  }

  .legal-document__body blockquote {
    margin: 1rem 0;
    padding: 0.85rem 1rem;
    border-left: 4px solid #0f62fe;
    background: #f8fbff;
    color: #334155;
  }

  .legal-document__body a {
    text-decoration: underline;
    text-underline-offset: 0.15em;
  }
</style>
@endpush
@endsection
