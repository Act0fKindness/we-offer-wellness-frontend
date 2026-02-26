@extends('layouts.app')

@section('content')
<section class="section">
  <div class="container-page auth-card">
    <p class="auth-kicker">Customer account</p>
    <h1>Keep every booking in one place</h1>
    <p class="auth-subline">Track orders, download receipts, and rebook faster by creating a customer account.</p>
    <ul class="auth-perks">
      <li>Instant access to your booking history</li>
      <li>Faster checkout on future orders</li>
      <li>Personalised recommendations + offers</li>
    </ul>
    <form method="POST" action="{{ route('register') }}" class="auth-form">
      @csrf
      <input type="hidden" name="redirect" value="{{ $redirect ?? '/cart' }}">
      <label for="reg-first-name">First name</label>
      <input id="reg-first-name" type="text" name="first_name" value="{{ old('first_name') }}" required autofocus>
      @error('first_name')<div class="field-error">{{ $message }}</div>@enderror

      <label for="reg-last-name">Last name</label>
      <input id="reg-last-name" type="text" name="last_name" value="{{ old('last_name') }}" required>
      @error('last_name')<div class="field-error">{{ $message }}</div>@enderror

      <label for="reg-email">Email</label>
      <input id="reg-email" type="email" name="email" value="{{ old('email') }}" required>
      @error('email')<div class="field-error">{{ $message }}</div>@enderror

      <label for="reg-password">Password</label>
      <input id="reg-password" type="password" name="password" required>
      @error('password')<div class="field-error">{{ $message }}</div>@enderror

      <label for="reg-password_confirmation">Confirm password</label>
      <input id="reg-password_confirmation" type="password" name="password_confirmation" required>

      <button type="submit" class="btn-wow btn-wow--cta">Create customer account</button>
      <p class="auth-switch">Already have one? <a href="{{ route('login', ['redirect' => $redirect ?? '/cart']) }}">Log in</a></p>
    </form>
  </div>
</section>
@endsection
