@extends('account.base')

@section('account-content')
@php
    $status = $status ?? session('status');
    $profileUser = auth()->user();
    $firstName = trim($profileUser->first_name ?: \Illuminate\Support\Str::of($profileUser->name ?? '')->before(' '));
    $avatarPath = $profileUser->profile_picture ?? null;
    if ($avatarPath && !\Illuminate\Support\Str::startsWith($avatarPath, ['http://', 'https://'])) {
        $avatarPath = \Illuminate\Support\Facades\Storage::disk('public')->url($avatarPath);
    }
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

    <form method="POST" action="{{ route('profile.update') }}" class="account-form" enctype="multipart/form-data">
      @csrf
      @method('PATCH')

      <div class="profile-photo-field">
        <div class="account-avatar account-avatar--profile" aria-hidden="true">
          @if($avatarPath)
            <img src="{{ $avatarPath }}" alt="">
          @else
            <span>{{ $firstName ?: 'You' }}</span>
          @endif
        </div>
        <label class="btn-wow btn-wow--outline btn-sm" for="profile-picture-upload">Upload photo</label>
        <input id="profile-picture-upload" type="file" name="profile_picture" accept="image/*" hidden>
      </div>
      @error('profile_picture')<p class="field-error">{{ $message }}</p>@enderror

      <label for="profile-first-name">First name</label>
      <input id="profile-first-name" type="text" name="first_name" value="{{ old('first_name', $profileUser->first_name) }}" required>
      @error('first_name')<p class="field-error">{{ $message }}</p>@enderror

      <label for="profile-last-name">Last name</label>
      <input id="profile-last-name" type="text" name="last_name" value="{{ old('last_name', $profileUser->last_name) }}" required>
      @error('last_name')<p class="field-error">{{ $message }}</p>@enderror

      <label for="profile-email">Email address</label>
      <input id="profile-email" type="email" name="email" value="{{ old('email', $profileUser->email) }}" required>
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
