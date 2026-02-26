@extends('emails.layout')

@section('title','Verify your email')
@section('content')
  <p>Hello {{ $user->first_name ?? $user->name }},</p>
  <p>Here is your AtEase verification code:</p>
  <p style="font-size:28px;font-weight:800;letter-spacing:4px; margin:8px 0 12px">{{ $code }}</p>
  <p class="muted">This code expires in {{ $expires ?? 60 }} minutes.</p>
  <p style="margin-top:18px">Prefer a link? You can also verify here:</p>
  <p><a href="{{ $url }}" class="btn">Verify your email</a></p>
  <p class="muted" style="margin-top:18px">If you didn’t request this, you can ignore this message.</p>
@endsection
