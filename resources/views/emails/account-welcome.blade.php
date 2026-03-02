@extends('emails.layout')

@section('title','Welcome to We Offer Wellness')
@section('content')
  <span class="eyebrow">Account created</span>
  <h1>Your account is ready</h1>
  <p>Hi {{ $user->first_name ?? $user->name ?? 'there' }}, thanks for creating a We Offer Wellness® account. You now have faster checkout, saved favourites, and a single place for every receipt.</p>
  <a href="{{ $dashboardUrl }}" class="btn" target="_blank" rel="noopener">Open my account</a>
  <div class="info-card">
    <strong style="display:block; margin-bottom:6px;">What you can do next</strong>
    <ul style="margin:0; padding-left:20px; color:#4b5565;">
      <li>Track bookings and download receipts</li>
      <li>Save online rituals and nearby practitioners</li>
      <li>Update your profile and contact preferences</li>
    </ul>
  </div>
  <p style="margin-bottom:0;">Need help? <a href="{{ $helpUrl }}" target="_blank" rel="noopener">Visit the Help Centre</a> or reply to this email.</p>
@endsection
