@extends('layouts.account')

@section('page-title', 'Create your account — We Offer Wellness™')
@section('auth-heading', 'Create your account')
@section('auth-subheading', 'Track orders, download receipts, and rebook faster by creating a customer account.')

@section('auth-alert')
  @if($errors->any())
    <div class="account-auth-alert show" role="alert">{{ $errors->first() }}</div>
  @endif
@endsection

@section('auth-form')
  <form id="registerForm" method="POST" action="{{ route('register') }}" class="account-auth-form">
    @csrf
    <input type="hidden" name="redirect" value="{{ $redirect ?? '/cart' }}">

    <div class="account-auth-field-group">
      <label for="reg-first-name" class="account-auth-label">First name</label>
      <div class="account-auth-field">
        <input id="reg-first-name" class="account-auth-input" type="text" name="first_name" value="{{ old('first_name') }}" required autofocus autocomplete="given-name">
      </div>
      @error('first_name')<div class="account-auth-alert show">{{ $message }}</div>@enderror
    </div>

    <div class="account-auth-field-group">
      <label for="reg-last-name" class="account-auth-label">Last name</label>
      <div class="account-auth-field">
        <input id="reg-last-name" class="account-auth-input" type="text" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name">
      </div>
      @error('last_name')<div class="account-auth-alert show">{{ $message }}</div>@enderror
    </div>

    <div class="account-auth-field-group">
      <label for="reg-email" class="account-auth-label">Email</label>
      <div class="account-auth-field">
        <input id="reg-email" class="account-auth-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
      </div>
      @error('email')<div class="account-auth-alert show">{{ $message }}</div>@enderror
    </div>

    <div class="account-auth-field-group">
      <label for="reg-password" class="account-auth-label">Password</label>
      <div class="account-auth-field">
        <input id="reg-password" class="account-auth-input" type="password" name="password" required autocomplete="new-password">
        <button type="button" class="account-auth-toggle" id="toggleRegPw" aria-label="Show password">
          <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" width="16" height="16">
            <path d="M2.2 12s3.6-7 9.8-7 9.8 7 9.8 7-3.6 7-9.8 7-9.8-7-9.8-7Z" stroke="currentColor" stroke-width="1.7"/>
            <path d="M12 15.2a3.2 3.2 0 1 0 0-6.4 3.2 3.2 0 0 0 0 6.4Z" stroke="currentColor" stroke-width="1.7"/>
          </svg>
        </button>
      </div>
      @error('password')<div class="account-auth-alert show">{{ $message }}</div>@enderror
    </div>

    <div class="account-auth-field-group">
      <label for="reg-password_confirmation" class="account-auth-label">Confirm password</label>
      <div class="account-auth-field">
        <input id="reg-password_confirmation" class="account-auth-input" type="password" name="password_confirmation" required autocomplete="new-password">
      </div>
    </div>

    <button class="account-auth-btn primary" id="registerSubmit" type="submit">
      <span class="spinner" aria-hidden="true"></span>
      Create account
    </button>
  </form>
@endsection

@section('auth-meta')
  <div class="account-auth-inline">
    Already have an account?
    <a href="{{ route('login', ['redirect' => $redirect ?? '/cart']) }}">Sign in</a>
  </div>
  <div class="account-auth-fine">
    By creating an account you agree to our <a href="/terms" style="color:inherit;">Terms</a> and <a href="/privacy" style="color:inherit;">Privacy Policy</a>.
  </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('registerForm');
  const submitBtn = document.getElementById('registerSubmit');
  const toggle = document.getElementById('toggleRegPw');
  const passwordInput = document.getElementById('reg-password');
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
