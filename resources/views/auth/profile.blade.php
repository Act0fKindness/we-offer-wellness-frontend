@extends('layouts.app')

@section('content')
<section class="section">
  <div class="container-page auth-card">
    <h1>Profile</h1>
    <form method="POST" action="{{ route('profile.update') }}" class="auth-form">
      @csrf
      @method('PATCH')
      <label for="profile-name">Name</label>
      <input id="profile-name" type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required>
      @error('name')<div class="field-error">{{ $message }}</div>@enderror

      <label for="profile-email">Email</label>
      <input id="profile-email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
      @error('email')<div class="field-error">{{ $message }}</div>@enderror

      <button type="submit" class="btn-wow btn-wow--cta">Save changes</button>
    </form>
    <hr style="margin:20px 0">
    <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Delete your account? This cannot be undone')">
      @csrf
      @method('DELETE')
      <label for="delete-password">Confirm password to delete account</label>
      <input id="delete-password" type="password" name="password" required>
      @error('password')<div class="field-error">{{ $message }}</div>@enderror
      <button type="submit" class="btn-wow btn-wow--outline" style="margin-top:12px">Delete account</button>
    </form>
  </div>
</section>
@endsection
