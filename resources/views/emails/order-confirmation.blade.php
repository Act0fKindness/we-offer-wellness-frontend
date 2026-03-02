@extends('emails.layout')

@section('title','Order #'.$order->id.' confirmed')
@section('content')
  @php($money = fn ($value) => '£'.number_format(max(0, (int) $value) / 100, 2))
  <span class="eyebrow">Order confirmed</span>
  <h1>Thanks for your booking</h1>
  <p>We received your payment and reserved every item below. You’ll find the full receipt anytime inside your account.</p>
  <div class="info-card">
    <p style="margin:0;">Order ID: #{{ $order->id }}</p>
    <p style="margin:0;">Total charged: {{ strtoupper($order->currency) }} · {{ $money($order->amount_total) }}</p>
    <p style="margin:0;">Status: {{ \Illuminate\Support\Str::headline($order->status) }}</p>
  </div>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:12px;">
    @foreach($order->items as $item)
      <tr>
        <td style="padding:6px 0;">{{ $item->name }} × {{ $item->quantity }}</td>
        <td style="padding:6px 0; text-align:right;">{{ $money($item->unit_amount * $item->quantity) }}</td>
      </tr>
    @endforeach
    <tr>
      <td style="border-top:1px solid #eceff4; padding-top:10px; font-weight:700;">Total</td>
      <td style="border-top:1px solid #eceff4; padding-top:10px; font-weight:700; text-align:right;">{{ $money($order->amount_total) }}</td>
    </tr>
  </table>
  <p style="margin-bottom:0;">Need adjustments? Email <a href="mailto:support@weofferwellness.co.uk">support@weofferwellness.co.uk</a>. This email address is no-reply.</p>
@endsection
