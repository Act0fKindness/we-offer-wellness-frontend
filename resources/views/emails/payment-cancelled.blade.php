@extends('emails.layout')

@section('title','We saved your cart')
@section('content')
  <span class="eyebrow">Need more time?</span>
  <h1>Your basket is safe</h1>
  <p>We noticed you didn’t finish checking out. No stress — we’re holding everything in your cart for the next {{ $holdHours ?? 48 }} hours so you can jump back in when you’re ready.</p>
  @if(!empty($items))
    <div class="info-card">
      <strong style="display:block; margin-bottom:6px;">Still waiting for you</strong>
      <ul style="margin:0; padding-left:20px; color:#4b5565;">
        @foreach($items as $item)
          <li>{{ $item['quantity'] ?? 1 }} × {{ $item['name'] }} — £{{ number_format(max(0, ($item['amount'] ?? 0))/100, 2) }}</li>
        @endforeach
      </ul>
    </div>
  @endif
  <a href="{{ $resumeUrl ?? url('/cart') }}" class="btn" target="_blank" rel="noopener">Return to my cart</a>
  <p class="muted" style="margin-top:18px;">Need help? Email <a href="mailto:support@weofferwellness.co.uk">support@weofferwellness.co.uk</a>. This inbox doesn’t accept replies.</p>
@endsection
