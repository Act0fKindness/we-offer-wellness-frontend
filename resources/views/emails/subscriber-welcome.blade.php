@extends('emails.layout')

@section('title','Welcome to We Offer Wellness')
@section('content')
  <span class="eyebrow">Subscription confirmed</span>
  <h1>Welcome to your new ritual inbox</h1>
  <p>Hey {{ $subscriber->first_name ?? $subscriber->name ?? 'friend' }}, you’re officially on the We Offer Wellness® list. Expect gentle launch alerts, curated collections for the goals you pick, and early-access deals.</p>
  <div class="info-card">
    <strong style="display:block; margin-bottom:6px;">What’s coming your way</strong>
    <ul style="margin:0; padding-left:20px; color:#4b5565;">
      <li>Launch invites and early booking windows</li>
      <li>Guided online rituals you can try tonight</li>
      <li>Nearby practitioners handpicked for your goals</li>
    </ul>
  </div>
  <p style="margin:18px 0 10px; font-weight:600;">Quick links</p>
  <p style="margin:0 0 4px;"><a href="{{ $browseUrl }}" target="_blank" rel="noopener">Discover online sessions</a></p>
  <p style="margin:0 0 4px;"><a href="{{ $nearbyUrl }}" target="_blank" rel="noopener">Find something near you</a></p>
  <p style="margin:0 0 24px;"><a href="{{ $preferencesUrl }}" target="_blank" rel="noopener">Tell us your preferences</a> so we only send what you love.</p>
@endsection
