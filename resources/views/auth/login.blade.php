@extends('layouts.app')

@section('content')
<section class="section">
  <div class="container-page auth-card">
    <h1>Log in</h1>
    @if($status)
      <div class="alert alert-success">{{ $status }}</div>
    @endif
    <form method="POST" action="{{ route('login') }}" class="auth-form">
      @csrf
      <input type="hidden" name="redirect" value="{{ $redirect ?? '/cart' }}">
      <label for="login-email">Email</label>
      <input id="login-email" type="email" name="email" value="{{ old('email') }}" required autofocus>
      @error('email')<div class="field-error">{{ $message }}</div>@enderror

      <label for="login-password">Password</label>
      <input id="login-password" type="password" name="password" required>
      @error('password')<div class="field-error">{{ $message }}</div>@enderror

      <label class="remember"><input type="checkbox" name="remember"> Remember me</label>

      <button type="submit" class="btn-wow btn-wow--cta">Continue</button>
    </form>
  </div>
</section>
@endsection
