@extends('emails.layout')

@section('title','Password updated')
@section('content')
  <span class="eyebrow">Security update</span>
  <h1>Your password was changed</h1>
  <p>{{ $user->first_name ?? $user->name ?? 'Hi' }}, this is a confirmation that your We Offer Wellness password was just updated.</p>
  <div class="info-card" style="font-size:14px;">
    <strong style="display:block; margin-bottom:6px;">Details</strong>
    <p style="margin:0;">Time: {{ optional($context['time'] ?? now())->format('d M Y · H:i T') }}</p>
    <p style="margin:0;">Device: {{ $context['device'] ?? ($context['user_agent'] ?? 'Unknown') }}</p>
    <p style="margin:0;">IP: {{ $context['ip'] ?? 'Unavailable' }}</p>
  </div>
  <p style="margin-bottom:0;">If this wasn’t you, <a href="{{ url('/forgot-password') }}" target="_blank" rel="noopener">reset your password</a> and let us know at <a href="mailto:hello@weofferwellness.co.uk">hello@weofferwellness.co.uk</a>.</p>
@endsection
