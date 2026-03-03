@extends('emails.layout')

@section('title','Dispute received on your payment')
@section('content')
  @php($money = fn ($value) => '£'.number_format(max(0, (int) $value) / 100, 2))
  <span class="eyebrow">Heads up</span>
  <h1>We received a dispute</h1>
  <p>Stripe notified us that the cardholder opened a dispute for Order #{{ $order->id }}. Nothing is required from you right now, but we’ll let you know if we need extra details.</p>
  <div class="info-card">
    <p style="margin:0;">Amount challenged: {{ strtoupper($context['currency'] ?? $order->currency) }} · {{ $money($context['amount'] ?? 0) }}</p>
    @if(!empty($context['reason']))
      <p style="margin:0;">Reason: {{ \Illuminate\Support\Str::headline($context['reason']) }}</p>
    @endif
    @if(!empty($context['due_by']))
      <p style="margin:0;">Stripe review ends {{ \Illuminate\Support\Carbon::createFromTimestamp($context['due_by'])->format('d M Y') }}</p>
    @endif
  </div>
  <p style="margin-bottom:0;">Have questions? Email <a href="mailto:support@weofferwellness.co.uk">support@weofferwellness.co.uk</a>; this notification address is no-reply.</p>
@endsection
