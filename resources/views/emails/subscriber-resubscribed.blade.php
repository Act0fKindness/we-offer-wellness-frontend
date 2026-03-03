@extends('emails.layout')

@section('title','Welcome back')
@section('content')
  <span class="eyebrow">Resubscribed</span>
  <h1>You’re back on the list</h1>
  <p>We missed you! You’ll start receiving launch alerts, curated rituals, and offers again. Update your preferences below so we only send what you care about.</p>
  <a href="{{ $preferencesUrl }}" class="btn" target="_blank" rel="noopener">Update preferences</a>
@endsection
