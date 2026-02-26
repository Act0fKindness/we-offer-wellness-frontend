@extends('emails.layout')

@section('title','Verify your email')
@section('content')
  <span class="eyebrow">Action required</span>
  <h1>Verify your email</h1>
  <p>Hello {{ $user->first_name ?? $user->name }}, here is your secure verification code:</p>
  <div class="code-box">{{ $code }}</div>
  <p class="muted">This code expires in {{ $expires ?? 60 }} minutes.</p>

  <div class="info-card">
    <p style="margin:0 0 10px; font-weight:600;">Prefer a magic link?</p>
    <a href="{{ $url }}" class="btn" target="_blank" rel="noopener">Verify your email</a>
  </div>

  <p class="muted" style="margin-top:18px">If you didn’t request this email, you can safely ignore it—your account remains secure.</p>
@endsection
