@extends('layouts.account')

@section('page-title', 'Create an account — We Offer Wellness™')
@section('auth-heading', 'Create account')
@section('auth-subheading', 'Create an account to save favourites, manage bookings, and checkout faster.')

@section('auth-alert')
  <div id="registerAlert" class="account-auth-alert {{ $errors->any() ? 'show' : '' }}" role="alert" aria-live="polite">
    {{ $errors->first() }}
  </div>
@endsection

@section('auth-form')
  <form id="registerForm" method="POST" action="{{ route('register') }}" class="account-auth-form" novalidate>
    @csrf
    <input type="hidden" name="redirect" value="{{ $redirect ?? '/cart' }}">

    <div class="account-auth-grid account-auth-grid--two">
      <div class="account-auth-field-group">
        <label for="reg-first-name" class="account-auth-label">First name</label>
        <div id="reg-first-field" @class(['account-auth-field', 'is-bad' => $errors->has('first_name')])>
          <input id="reg-first-name" class="account-auth-input" type="text" name="first_name" value="{{ old('first_name') }}" placeholder="First name" required autofocus autocomplete="given-name">
        </div>
        @error('first_name')<div class="account-auth-alert show">{{ $message }}</div>@enderror
      </div>

      <div class="account-auth-field-group">
        <label for="reg-last-name" class="account-auth-label">Last name</label>
        <div id="reg-last-field" @class(['account-auth-field', 'is-bad' => $errors->has('last_name')])>
          <input id="reg-last-name" class="account-auth-input" type="text" name="last_name" value="{{ old('last_name') }}" placeholder="Last name" required autocomplete="family-name">
        </div>
        @error('last_name')<div class="account-auth-alert show">{{ $message }}</div>@enderror
      </div>
    </div>

    <div class="account-auth-field-group">
      <label for="reg-email" class="account-auth-label">Email</label>
      <div id="reg-email-field" @class(['account-auth-field', 'is-bad' => $errors->has('email')])>
        <input id="reg-email" class="account-auth-input" type="email" name="email" value="{{ old('email') }}" placeholder="example@email.com" inputmode="email" required autocomplete="email">
      </div>
      @error('email')<div class="account-auth-alert show">{{ $message }}</div>@enderror
    </div>

    <div class="account-auth-grid account-auth-grid--two">
      <div class="account-auth-field-group">
        <label for="reg-password" class="account-auth-label">Password</label>
        <div id="reg-password-field" @class(['account-auth-field', 'is-bad' => $errors->has('password')])>
          <input id="reg-password" class="account-auth-input" type="password" name="password" placeholder="Create a password" required autocomplete="new-password">
          <button type="button" class="account-auth-toggle" id="toggleRegPw" aria-label="Show password">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" width="16" height="16">
              <path d="M2.2 12s3.6-7 9.8-7 9.8 7 9.8 7-3.6 7-9.8 7-9.8-7-9.8-7Z" stroke="currentColor" stroke-width="1.7" />
              <path d="M12 15.2a3.2 3.2 0 1 0 0-6.4 3.2 3.2 0 0 0 0 6.4Z" stroke="currentColor" stroke-width="1.7" />
            </svg>
          </button>
        </div>
        @error('password')<div class="account-auth-alert show">{{ $message }}</div>@enderror
      </div>

      <div class="account-auth-field-group">
        <label for="reg-password_confirmation" class="account-auth-label">Confirm password</label>
        <div id="reg-password2-field" @class(['account-auth-field', 'is-bad' => $errors->has('password')])>
          <input id="reg-password_confirmation" class="account-auth-input" type="password" name="password_confirmation" placeholder="Repeat password" required autocomplete="new-password">
          <button type="button" class="account-auth-toggle" id="toggleRegPw2" aria-label="Show password confirmation">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" width="16" height="16">
              <path d="M2.2 12s3.6-7 9.8-7 9.8 7 9.8 7-3.6 7-9.8 7-9.8-7-9.8-7Z" stroke="currentColor" stroke-width="1.7" />
              <path d="M12 15.2a3.2 3.2 0 1 0 0-6.4 3.2 3.2 0 0 0 0 6.4Z" stroke="currentColor" stroke-width="1.7" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <div class="account-auth-row account-auth-row--stack">
      <label for="reg-terms" class="account-auth-check account-auth-check--stack">
        <input type="checkbox" id="reg-terms" name="terms" {{ old('terms') ? 'checked' : '' }} required>
        <span>I agree to the <a href="/terms" style="color:inherit;">Terms</a> and <a href="/privacy" style="color:inherit;">Privacy Policy</a>.</span>
      </label>
      <a href="{{ route('login', ['redirect' => $redirect ?? '/cart']) }}">Login</a>
    </div>

    <button class="account-auth-btn primary" id="registerSubmit" type="submit">
      <span class="spinner" aria-hidden="true"></span>
      Create account
    </button>

    <div class="account-auth-inline account-auth-inline--center">
      Already have an account?
      <a href="{{ route('login', ['redirect' => $redirect ?? '/cart']) }}">Login</a>
    </div>

    <div class="account-auth-fine">
      We’ll only email you about bookings and important account updates.
    </div>
  </form>
@endsection

@push('styles')
<style>
  .account-auth-grid {
    display: grid;
    gap: 12px;
  }
  .account-auth-grid--two {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  @media (max-width: 640px) {
    .account-auth-grid--two {
      grid-template-columns: 1fr;
    }
  }
  .account-auth-field.is-bad {
    border-color: rgba(217,45,32,.35);
    box-shadow: 0 0 0 4px rgba(217,45,32,.10), 0 12px 22px rgba(16,24,40,.08);
  }
  .account-auth-row--stack {
    align-items: flex-start;
    flex-wrap: wrap;
    margin-top: 6px;
  }
  .account-auth-row--stack > a {
    margin-left: auto;
    white-space: nowrap;
  }
  @media (max-width: 520px) {
    .account-auth-row--stack {
      flex-direction: column;
      gap: 8px;
    }
    .account-auth-row--stack > a {
      margin-left: 0;
    }
  }
  .account-auth-check--stack {
    align-items: flex-start;
    line-height: 1.35;
    max-width: 56ch;
  }
  .account-auth-check--stack span {
    display: inline-block;
  }
  .account-auth-check--stack span a {
    color: inherit;
    font-weight: 600;
    text-decoration: none;
  }
  .account-auth-check--stack span a:hover {
    text-decoration: underline;
  }
  .account-auth-inline--center {
    text-align: left;
  }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('registerForm');
  if (!form) return;

  const submitBtn = document.getElementById('registerSubmit');
  const alertBox = document.getElementById('registerAlert');
  const firstField = document.getElementById('reg-first-field');
  const lastField = document.getElementById('reg-last-field');
  const emailField = document.getElementById('reg-email-field');
  const passwordField = document.getElementById('reg-password-field');
  const password2Field = document.getElementById('reg-password2-field');

  const firstInput = document.getElementById('reg-first-name');
  const lastInput = document.getElementById('reg-last-name');
  const emailInput = document.getElementById('reg-email');
  const passwordInput = document.getElementById('reg-password');
  const password2Input = document.getElementById('reg-password_confirmation');
  const termsInput = document.getElementById('reg-terms');

  const togglePw = document.getElementById('toggleRegPw');
  const togglePw2 = document.getElementById('toggleRegPw2');

  const fieldWrappers = [firstField, lastField, emailField, passwordField, password2Field];
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  const showAlert = (message) => {
    if (!alertBox || !message) return;
    alertBox.textContent = message;
    alertBox.classList.add('show');
  };

  const clearAlert = () => {
    if (!alertBox) return;
    alertBox.textContent = '';
    alertBox.classList.remove('show');
  };

  const clearBad = () => {
    fieldWrappers.forEach((field) => field && field.classList.remove('is-bad'));
  };

  const bad = (field) => {
    if (field) field.classList.add('is-bad');
  };

  const wireToggle = (btn, input, labelShown, labelHidden) => {
    if (!btn || !input) return;
    btn.addEventListener('click', () => {
      const showing = input.type === 'text';
      input.type = showing ? 'password' : 'text';
      btn.setAttribute('aria-label', showing ? labelShown : labelHidden);
    });
  };

  wireToggle(togglePw, passwordInput, 'Show password', 'Hide password');
  wireToggle(togglePw2, password2Input, 'Show password confirmation', 'Hide password confirmation');

  form.addEventListener('submit', (event) => {
    clearAlert();
    clearBad();

    const first = firstInput.value.trim();
    const last = lastInput.value.trim();
    const email = emailInput.value.trim();
    const pass1 = passwordInput.value;
    const pass2 = password2Input.value;
    const termsChecked = termsInput && termsInput.checked;

    if (!first) {
      event.preventDefault();
      bad(firstField);
      showAlert('Please enter your first name.');
      return;
    }
    if (!last) {
      event.preventDefault();
      bad(lastField);
      showAlert('Please enter your last name.');
      return;
    }
    if (!email) {
      event.preventDefault();
      bad(emailField);
      showAlert('Please enter your email.');
      return;
    }
    if (!emailPattern.test(email)) {
      event.preventDefault();
      bad(emailField);
      showAlert('That email doesn’t look quite right.');
      return;
    }
    if (!pass1) {
      event.preventDefault();
      bad(passwordField);
      showAlert('Please create a password.');
      return;
    }
    if (pass1.length < 8) {
      event.preventDefault();
      bad(passwordField);
      showAlert('Password must be at least 8 characters.');
      return;
    }
    if (!pass2) {
      event.preventDefault();
      bad(password2Field);
      showAlert('Please confirm your password.');
      return;
    }
    if (pass1 !== pass2) {
      event.preventDefault();
      bad(password2Field);
      showAlert('Passwords don’t match.');
      return;
    }
    if (!termsChecked) {
      event.preventDefault();
      showAlert('Please agree to the Terms and Privacy Policy.');
      if (termsInput) termsInput.focus();
      return;
    }

    if (submitBtn) {
      submitBtn.classList.add('is-loading');
      submitBtn.disabled = true;
    }
  });
});
</script>
@endpush
