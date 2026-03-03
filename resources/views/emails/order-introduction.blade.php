@extends('emails.layout')

@section('title', 'Booking introduction — Order #'.$order->id)
@section('content')
  @php
    $money = fn ($value) => '£'.number_format(max(0, (int) $value) / 100, 2);
    $vendorName = $vendor->vendor_name ?? 'there';
    $customerLabel = $customerName ?: $customerEmail;
  @endphp
  <span class="eyebrow">Booking introduction</span>
  <h1>Let’s schedule your session</h1>
  <p>Hi {{ $vendorName }},</p>
  <p>{{ $customerLabel }} (cc’d) just completed checkout for the experience(s) below. Please reply-all with a couple of suggested dates/times so you can lock in the booking together. Our concierge team ({{ $supportEmail ?: 'hello@weofferwellness.co.uk' }}) is copied for support.</p>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0;">
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
  </table>
  <p style="margin-bottom:12px;">Booking reference: <strong>#{{ $order->id }}</strong></p>
  <p style="margin-bottom:0;">Thanks for taking great care of our community.<br>— The We Offer Wellness Concierge</p>
@endsection
