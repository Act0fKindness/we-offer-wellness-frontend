@extends('emails.layout')

@section('title','Reset your password')
@section('content')
  <span class="eyebrow">Password reset</span>
  <h1>Reset your password</h1>
  <p>Hello {{ $user->first_name ?? $user->name }}, we received a request to reset your We Offer Wellness password.</p>
  <p style="margin:18px 0 12px;font-weight:600;">Tap the button below to choose a new password:</p>
  <a href="{{ $url }}" class="btn" target="_blank" rel="noopener">Create a new password</a>
  <p class="muted" style="margin-top:18px">If you didn’t request this, you can ignore this email — your account remains secure.</p>
@endsection
