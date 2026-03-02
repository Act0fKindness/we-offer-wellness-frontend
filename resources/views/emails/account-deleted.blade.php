@extends('emails.layout')

@section('title','Account deleted')
@section('content')
  <span class="eyebrow">Account closure</span>
  <h1>Your account has been closed</h1>
  <p>{{ $name ? 'Hi '.$name.',' : 'Hi there,' }} this confirms we deleted your We Offer Wellness® account and personal data. Any future bookings will require a new account.</p>
  <p style="margin-bottom:0;">If this was a mistake, contact us at <a href="mailto:hello@weofferwellness.co.uk">hello@weofferwellness.co.uk</a> and we’ll help.</p>
@endsection
