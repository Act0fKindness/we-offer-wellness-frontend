@extends('account.base')

@section('account-content')
@php
    $status = $status ?? session('status');
@endphp

<div class="account-card">
  <div class="account-card__header">
    <p class="eyebrow">Contact details</p>
    <h2>Profile & preferences</h2>
  </div>
  <div class="account-card__body account-form-grid">
    @if (session('status') === 'profile-updated')
      <div class="account-alert account-alert--success">Profile updated successfully.</div>
    @elseif($status)
      <div class="account-alert account-alert--info">{{ $status }}</div>
    @endif

    @if ($mustVerifyEmail ?? false)
      @if (!auth()->user()->hasVerifiedEmail())
        <div class="account-alert account-alert--warning">
          <p><strong>Verify your email.</strong> We’ve sent a verification link so you can receive booking confirmations. <a href="{{ route('verification.send') }}" onclick="event.preventDefault(); document.getElementById('resend-verification').submit();">Resend email</a></p>
          <form id="resend-verification" method="POST" action="{{ route('verification.send') }}" style="display:none;">
            @csrf
          </form>
        </div>
      @endif
    @endif

    <form method="POST" action="{{ route('profile.update') }}" class="account-form">
      @csrf
      @method('PATCH')

      <label for="profile-name">Full name</label>
      <input id="profile-name" type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required>
      @error('name')<p class="field-error">{{ $message }}</p>@enderror

      <label for="profile-email">Email address</label>
      <input id="profile-email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
      @error('email')<p class="field-error">{{ $message }}</p>@enderror

      <button type="submit" class="btn-wow btn-wow--cta">Save changes</button>
    </form>
  </div>
</div>

<div class="account-card account-card--danger">
  <div class="account-card__header">
    <p class="eyebrow">Danger zone</p>
    <h2>Delete account</h2>
  </div>
  <div class="account-card__body">
    <p>Deleting your account removes upcoming bookings, saved preferences, and receipts. This action is permanent.</p>
    <form method="POST" action="{{ route('profile.destroy') }}" class="account-form" onsubmit="return confirm('Delete your account? This cannot be undone');">
      @csrf
      @method('DELETE')
      <label for="delete-password">Confirm your password</label>
      <input id="delete-password" type="password" name="password" required>
      @error('password')<p class="field-error">{{ $message }}</p>@enderror
      <button type="submit" class="btn-wow btn-wow--outline" style="margin-top:12px">Delete account</button>
    </form>
  </div>
</div>
@endsection
