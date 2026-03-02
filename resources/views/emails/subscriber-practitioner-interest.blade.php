@extends('emails.layout')

@section('title','We received your practitioner details')
@section('content')
  <span class="eyebrow">Become a WOW Practitioner</span>
  <h1>Thanks for your interest</h1>
  <p>Hi {{ $subscriber->first_name ?? $subscriber->name ?? 'there' }}, thanks for sharing your details to become a WOW Practitioner. Our partnerships team reviews every submission and will be in touch shortly with the next steps.</p>
  <div class="info-card">
    <strong style="display:block; margin-bottom:6px;">What happens next</strong>
    <ul style="margin:0; padding-left:20px; color:#4b5565;">
      <li>We’ll review your availability preferences, including online and in-person options if you selected them.</li>
      <li>If we need anything else, we’ll reply right away so you’re never left waiting.</li>
      <li>Expect a personal follow-up with onboarding details and launch timings.</li>
    </ul>
  </div>
  <p>While you wait, feel free to browse our <a href="{{ $providersUrl }}" target="_blank" rel="noopener">provider stories</a> or check the <a href="{{ $helpUrl }}" target="_blank" rel="noopener">help centre</a> for answers to common questions.</p>
  <p style="margin:0;">Talk soon,<br>The We Offer Wellness partnerships team</p>
@endsection
