@extends('emails.layout')

@section('title','You’re unsubscribed')
@section('content')
  <span class="eyebrow">You’re in control</span>
  <h1>You’re unsubscribed</h1>
  <p>We’ve removed {{ $subscriber->email }} from We Offer Wellness® marketing updates. Transactional emails (like receipts) may still arrive if you book with us.</p>
  <p style="margin:18px 0 0;">Want back in later? Save this link:</p>
  <p><a href="{{ $resubscribeUrl }}" target="_blank" rel="noopener">Resubscribe to We Offer Wellness</a></p>
@endsection
