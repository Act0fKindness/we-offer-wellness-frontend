@extends('layouts.account')

@section('page-title', 'Forgot password — We Offer Wellness™')
@section('auth-heading', 'Reset your password')
@section('auth-subheading', 'Enter your email address and we’ll send you a link to create a new password.')

@php($status = session('status'))

@section('auth-alert')
  @if($status)
    <div class="account-auth-alert show" role="alert">{{ $status }}</div>
  @endif
@endsection

@section('auth-form')
  <form id="forgotPasswordForm" method="POST" action="{{ route('password.email') }}" class="account-auth-form">
    @csrf
    <div class="account-auth-field-group">
      <label for="forgot-email" class="account-auth-label">Email</label>
      <div class="account-auth-field">
        <input id="forgot-email" class="account-auth-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
      </div>
      @error('email')<div class="account-auth-alert show">{{ $message }}</div>@enderror
    </div>

    <button class="btn btn--primary account-auth-btn" id="forgotSubmit" type="submit">
      <span class="spinner" aria-hidden="true"></span>
      Send reset link
    </button>

    <div class="account-auth-inline">
      Remembered your password? <a href="{{ route('login') }}">Login</a>
    </div>
  </form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('forgotPasswordForm');
  const submitBtn = document.getElementById('forgotSubmit');
  if (form && submitBtn) {
    form.addEventListener('submit', function () {
      submitBtn.classList.add('is-loading');
      submitBtn.disabled = true;
    });
  }
});
</script>
@endpush
