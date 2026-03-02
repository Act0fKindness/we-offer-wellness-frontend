@extends('emails.layout')

@section('title','Shape your updates')
@section('content')
  <span class="eyebrow">Make it personal</span>
  <h1>Tell us what you’d like more of</h1>
  <p>Choose whether you want online rituals, nearby sessions, or inspiration for specific goals like sleep, stress, or pain. We’ll only send what you ask for.</p>
  <a href="{{ $preferencesUrl }}" class="btn" target="_blank" rel="noopener">Open preference centre</a>
  <p class="muted" style="margin-top:18px;">You can update these preferences anytime and every email includes a one-tap link.</p>
@endsection
