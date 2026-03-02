@extends('emails.layout')

@section('title','Email updated')
@section('content')
  <span class="eyebrow">Security heads-up</span>
  <h1>Email {{ $scope === 'old' ? 'changed' : 'confirmed' }}</h1>
  @if($scope === 'old')
    <p>A We Offer Wellness® account previously using this address now signs in with {{ $user->email }}.</p>
    <p>If you didn’t request this, reset the password immediately or contact <a href="mailto:hello@weofferwellness.co.uk">hello@weofferwellness.co.uk</a>.</p>
  @else
    <p>Your login email is now {{ $user->email }}. Use it next time you sign in.</p>
    <p>If you didn’t request this, let us know straight away at <a href="mailto:hello@weofferwellness.co.uk">hello@weofferwellness.co.uk</a>.</p>
  @endif
@endsection
