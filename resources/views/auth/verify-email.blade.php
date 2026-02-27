@extends('layouts.account')

@section('page-title', 'Verify your email — We Offer Wellness™')
@section('auth-heading', 'Verify your email')
@section('auth-subheading')
  We sent a 6-digit code and verification link to <strong>{{ auth()->user()->email }}</strong>. Confirm it so we can send booking receipts and reminders.
@endsection

@section('auth-alert')
  @if(session('status') === 'verification-link-sent')
    <div class="account-auth-alert show" role="alert">A fresh verification email is on its way.</div>
  @endif
@endsection

@section('auth-no-right', true)

@section('auth-form')
  <form id="verifyEmailForm" method="POST" action="{{ route('verification.send') }}" class="account-auth-form">
    @csrf
    <div class="account-auth-field-group">
      <div class="account-auth-field" style="flex-direction:column; align-items:flex-start; gap:10px; background:rgba(11,18,32,.03); border:1px dashed rgba(11,18,32,.18);">
        <span class="account-auth-label" style="margin-bottom:0; font-size:13px; text-transform:uppercase; letter-spacing:.18em; color:rgba(11,18,32,.55);">Didn’t get the email?</span>
        <p class="account-auth-inline" style="margin:0; color:rgba(11,18,32,.82); font-size:14px;">Check spam or tap below to resend the verification link.</p>
      </div>
    </div>
    <button class="btn btn--primary account-auth-btn" id="verifyEmailSubmit" type="submit">
      <span class="spinner" aria-hidden="true"></span>
      Resend verification email
    </button>
    <div class="account-auth-inline">
      Wrong address? <a href="{{ route('profile.edit') }}">Update it in your profile</a>.
    </div>
  </form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('verifyEmailForm');
  const submitBtn = document.getElementById('verifyEmailSubmit');
  if (form && submitBtn) {
    form.addEventListener('submit', function () {
      submitBtn.classList.add('is-loading');
      submitBtn.disabled = true;
    });
  }
});
</script>
@endpush
