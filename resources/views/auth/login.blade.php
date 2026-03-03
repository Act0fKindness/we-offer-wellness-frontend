@extends('layouts.account')

@section('page-title', 'Customer Login — We Offer Wellness™')
@section('auth-heading', 'Customer Login')
@section('auth-subheading', 'Log in to manage bookings, save favourites, and checkout faster.')

@php($status = session('status'))
@php($recaptchaEnabled = config('recaptcha.enabled') && config('recaptcha.site_key'))

@if($recaptchaEnabled)
  @pushOnce('scripts', 'recaptcha-script')
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  @endPushOnce
@endif

@section('auth-alert')
  @if($errors->any() || $status)
    <div class="account-auth-alert show" role="alert">
      {{ $errors->first() ?? $status }}
    </div>
  @endif
@endsection

@section('auth-form')
  <form id="loginForm" method="POST" action="{{ route('login') }}" class="account-auth-form">
    @csrf
    <input type="hidden" name="redirect" value="{{ $redirect ?? '/cart' }}">

    <div class="account-auth-field-group">
      <label for="login-email" class="account-auth-label">Email</label>
      <div class="account-auth-field">
        <input id="login-email" class="account-auth-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
      </div>
      @error('email')<div class="account-auth-alert show">{{ $message }}</div>@enderror
    </div>

    <div class="account-auth-field-group">
      <label for="login-password" class="account-auth-label">Password</label>
      <div class="account-auth-field">
        <input id="login-password" class="account-auth-input" type="password" name="password" required autocomplete="current-password">
        <button type="button" class="account-auth-toggle" id="togglePw" aria-label="Show password">
          <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" width="16" height="16">
            <path d="M2.2 12s3.6-7 9.8-7 9.8 7 9.8 7-3.6 7-9.8 7-9.8-7-9.8-7Z" stroke="currentColor" stroke-width="1.7"/>
            <path d="M12 15.2a3.2 3.2 0 1 0 0-6.4 3.2 3.2 0 0 0 0 6.4Z" stroke="currentColor" stroke-width="1.7"/>
          </svg>
        </button>
      </div>
      @error('password')<div class="account-auth-alert show">{{ $message }}</div>@enderror
    </div>

    <div class="account-auth-row">
      <label class="account-auth-check">
        <input type="checkbox" name="remember"> Remember me
      </label>
      <a href="{{ route('password.request') }}">Forgot password?</a>
    </div>

    @if($recaptchaEnabled)
      <div class="account-auth-field-group">
        <label class="account-auth-label">Security check</label>
        <div class="account-auth-field account-auth-field--recaptcha">
          <div class="g-recaptcha" data-sitekey="{{ config('recaptcha.site_key') }}"></div>
        </div>
        @error('g-recaptcha-response')<div class="account-auth-alert show">{{ $message }}</div>@enderror
      </div>
    @endif

    <button class="btn btn--primary account-auth-btn" id="loginSubmit" type="submit">
      <span class="spinner" aria-hidden="true"></span>
      Log in
    </button>
  </form>
@endsection

@section('auth-meta')
  <div class="account-auth-inline">
    Don’t have an account?
    <a href="{{ route('register', ['redirect' => $redirect ?? '/cart']) }}">Create account</a>
  </div>
  <div class="account-auth-fine">
    By continuing, you agree to our <a href="/terms" style="color:inherit;">Terms</a> and <a href="/privacy" style="color:inherit;">Privacy Policy</a>.
  </div>
@endsection

@push('styles')
<style>
  .account-auth-field--recaptcha {
    border: none;
    padding: 0;
    box-shadow: none;
  }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('loginForm');
  const submitBtn = document.getElementById('loginSubmit');
  const toggle = document.getElementById('togglePw');
  const passwordInput = document.getElementById('login-password');
  if (toggle && passwordInput) {
    toggle.addEventListener('click', function () {
      const shown = passwordInput.type === 'text';
      passwordInput.type = shown ? 'password' : 'text';
      toggle.setAttribute('aria-label', shown ? 'Show password' : 'Hide password');
    });
  }
  if (form && submitBtn) {
    form.addEventListener('submit', function () {
      submitBtn.classList.add('is-loading');
      submitBtn.disabled = true;
    });
  }
});
</script>
@endpush
