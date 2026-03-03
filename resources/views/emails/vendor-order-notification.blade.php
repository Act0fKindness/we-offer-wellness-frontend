@extends('emails.layout')

@section('title', 'New booking — Order #'.$order->id)
@section('content')
  @php
    $money = fn ($value) => '£'.number_format(max(0, (int) $value) / 100, 2);
    $vendorName = $vendor->vendor_name ?? 'there';
    $itemCount = collect($items)->sum(fn($it) => (int) ($it['quantity'] ?? 0));
    $subtotal = collect($items)->sum(fn($it) => (int) ($it['unit_amount'] ?? 0) * max(1, (int) ($it['quantity'] ?? 1)));
  @endphp
  <span class="eyebrow">New booking</span>
  <h1>{{ $vendorName }}, you have a new We Offer Wellness order</h1>
  <p style="margin-bottom:16px;">We just captured payment for {{ $customerEmail ?: 'a guest booking' }}. Please review the details below and reach out to confirm the appointment time.</p>
  <div class="info-card">
    <p style="margin:0;">Order ID: #{{ $order->id }}</p>
    <p style="margin:0;">Customer email: {{ $customerEmail ?: 'Guest checkout' }}</p>
    <p style="margin:0;">Line items in this order: {{ $itemCount }}</p>
    <p style="margin:0;">Subtotal (your items): {{ $money($subtotal) }}</p>
  </div>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
    @foreach($items as $item)
      @php($lineTotal = (int)($item['unit_amount'] ?? 0) * max(1, (int)($item['quantity'] ?? 1)))
      <tr>
        <td style="padding:8px 0;">
          <strong>{{ $item['name'] }}</strong>
          <div style="color:#6b7280;font-size:13px;">
            Qty × {{ $item['quantity'] ?? 1 }}
            @if(!empty($item['variant']))
              · {{ $item['variant'] }}
            @endif
          </div>
        </td>
        <td style="padding:8px 0;text-align:right; white-space:nowrap;">{{ $money($lineTotal) }}</td>
      </tr>
    @endforeach
    <tr>
      <td style="border-top:1px solid #eceff4; padding-top:10px; font-weight:700;">Subtotal</td>
      <td style="border-top:1px solid #eceff4; padding-top:10px; font-weight:700; text-align:right;">{{ $money($subtotal) }}</td>
    </tr>
  </table>
  <p style="margin-bottom:16px;">Please reply-all to the introduction email you will receive shortly to coordinate availability with the client. Need help? Just loop in <a href="mailto:hello@weofferwellness.co.uk">hello@weofferwellness.co.uk</a>.</p>
@endsection
