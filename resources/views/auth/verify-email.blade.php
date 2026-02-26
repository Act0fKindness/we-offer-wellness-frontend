@extends('layouts.app')

@section('content')
<section class="section">
  <div class="container-page auth-card">
    <p class="auth-kicker">Verify your email</p>
    <h1>Confirm your address to finish signing up</h1>
    <p class="auth-subline">We’ve sent a 6-digit code and verification link to <strong>{{ auth()->user()->email }}</strong>.</p>
    @if($status === 'verification-link-sent')
      <div class="alert alert-success">A fresh verification email is on its way.</div>
    @endif
    <form method="POST" action="{{ route('verification.send') }}" class="auth-form">
      @csrf
      <button type="submit" class="btn-wow btn-wow--cta">Resend verification email</button>
    </form>
    <p class="auth-switch" style="margin-top:16px;">Wrong email? <a href="/profile">Update it in your account</a>.</p>
  </div>
</section>
@endsection
