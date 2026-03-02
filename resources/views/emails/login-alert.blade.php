@extends('emails.layout')

@section('title','New login alert')
@section('content')
  <span class="eyebrow">Security alert</span>
  <h1>New login to your account</h1>
  <p>We noticed a new sign-in to your We Offer Wellness® account.</p>
  <div class="info-card" style="font-size:14px;">
    <p style="margin:0;">Device: {{ $context['device'] ?? ($context['user_agent'] ?? 'Unknown') }}</p>
    <p style="margin:0;">Location: {{ $context['location'] ?? 'Not available' }}</p>
    <p style="margin:0;">IP: {{ $context['ip'] ?? 'Not available' }}</p>
    <p style="margin:0;">Time: {{ optional($context['time'] ?? now())->format('d M Y · H:i T') }}</p>
  </div>
  <p style="margin-bottom:0;">If this was you, no action needed. Otherwise <a href="{{ url('/forgot-password') }}" target="_blank" rel="noopener">reset your password</a> and reach out to <a href="mailto:hello@weofferwellness.co.uk">hello@weofferwellness.co.uk</a>.</p>
@endsection
