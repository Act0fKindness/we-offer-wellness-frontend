@extends('emails.layout')

@section('title','Confirm your subscription')
@section('content')
  <span class="eyebrow">Double opt-in</span>
  <h1>Confirm your subscription</h1>
  <p>Hi {{ $subscriber->first_name ?? $subscriber->name ?? 'there' }}, tap the button below so we can start sending insider-only perks, drops, and mindful rituals.</p>
  <a href="{{ $confirmUrl }}" class="btn" target="_blank" rel="noopener">Confirm subscription</a>
  <p class="muted" style="margin-top:18px;">If this wasn’t you, ignore this email and you won’t hear from us again.</p>
  @if(!empty($manageUrl))
    <p style="margin-top:24px;">Want to set preferences first? <a href="{{ $manageUrl }}" target="_blank" rel="noopener">Open your preference centre</a>.</p>
  @endif
@endsection
