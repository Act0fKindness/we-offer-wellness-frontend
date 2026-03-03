@extends('layouts.account')

@section('page-title', 'Create a new password — We Offer Wellness™')
@section('auth-heading', 'Set a new password')
@section('auth-subheading', 'Enter your email and choose a new password to finish resetting your account.')

@section('auth-form')
  <form method="POST" action="{{ route('password.store') }}" class="account-auth-form">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <div class="account-auth-field-group">
      <label for="reset-email" class="account-auth-label">Email</label>
      <div class="account-auth-field">
        <input id="reset-email" class="account-auth-input" type="email" name="email" value="{{ old('email', $email) }}" required autofocus autocomplete="email">
      </div>
      @error('email')<div class="account-auth-alert show">{{ $message }}</div>@enderror
    </div>

    <div class="account-auth-field-group">
      <label for="reset-password" class="account-auth-label">New password</label>
      <div class="account-auth-field">
        <input id="reset-password" class="account-auth-input" type="password" name="password" required autocomplete="new-password">
      </div>
      @error('password')<div class="account-auth-alert show">{{ $message }}</div>@enderror
    </div>

    <div class="account-auth-field-group">
      <label for="reset-password-confirm" class="account-auth-label">Confirm password</label>
      <div class="account-auth-field">
        <input id="reset-password-confirm" class="account-auth-input" type="password" name="password_confirmation" required autocomplete="new-password">
      </div>
    </div>

    <button class="btn btn--primary account-auth-btn" type="submit">
      <span class="spinner" aria-hidden="true"></span>
      Update password
    </button>

    <div class="account-auth-inline">
      Remembered it? <a href="{{ route('login') }}">Back to login</a>
    </div>
  </form>
@endsection
