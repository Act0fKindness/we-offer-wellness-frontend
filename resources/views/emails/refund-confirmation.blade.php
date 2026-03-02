@extends('emails.layout')

@section('title','Your refund is on the way')
@section('content')
  @php($money = fn ($value) => '£'.number_format(max(0, (int) $value) / 100, 2))
  <span class="eyebrow">Refund update</span>
  <h1>Your refund is on the way</h1>
  <p>We’ve processed a {{ !empty($context['partial']) ? 'partial ' : '' }}refund for Order #{{ $order->id }}. Depending on your bank it may take 5–10 business days to appear.</p>
  <div class="info-card">
    <p style="margin:0;">Amount: {{ strtoupper($context['currency'] ?? $order->currency) }} · {{ $money($context['amount'] ?? 0) }}</p>
    @if(!empty($context['reason']))
      <p style="margin:0;">Reason: {{ $context['reason'] }}</p>
    @endif
  </div>
  <p style="margin-bottom:0;">Questions? Email <a href="mailto:support@weofferwellness.co.uk">support@weofferwellness.co.uk</a>. Replies to this message aren’t monitored.</p>
@endsection
