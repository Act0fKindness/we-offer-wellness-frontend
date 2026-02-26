@extends('layouts.app')

@section('content')
<section class="section">
  <div class="container-page auth-card">
    <h1>Create an account</h1>
    <form method="POST" action="{{ route('register') }}" class="auth-form">
      @csrf
      <input type="hidden" name="redirect" value="{{ $redirect ?? '/cart' }}">
      <label for="reg-name">Name</label>
      <input id="reg-name" type="text" name="name" value="{{ old('name') }}" required autofocus>
      @error('name')<div class="field-error">{{ $message }}</div>@enderror

      <label for="reg-email">Email</label>
      <input id="reg-email" type="email" name="email" value="{{ old('email') }}" required>
      @error('email')<div class="field-error">{{ $message }}</div>@enderror

      <label for="reg-password">Password</label>
      <input id="reg-password" type="password" name="password" required>
      @error('password')<div class="field-error">{{ $message }}</div>@enderror

      <label for="reg-password_confirmation">Confirm password</label>
      <input id="reg-password_confirmation" type="password" name="password_confirmation" required>

      <button type="submit" class="btn-wow btn-wow--cta">Create account</button>
    </form>
  </div>
</section>
@endsection
