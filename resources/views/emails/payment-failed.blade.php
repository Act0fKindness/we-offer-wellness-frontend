@extends('emails.layout')

@section('title','We couldn’t complete your payment')
@section('content')
  @php($money = fn ($value) => '£'.number_format(max(0, (int) $value) / 100, 2))
  <span class="eyebrow">Action needed</span>
  <h1>We couldn’t complete your payment</h1>
  <p>Something stopped the payment for your recent booking attempt. No charges were made.</p>
  @if(!empty($context['reason']))
    <div class="info-card">
      <strong style="display:block; margin-bottom:6px;">Stripe message</strong>
      <p style="margin:0; color:#4b5565;">{{ $context['reason'] }}</p>
    </div>
  @endif
  @if(!empty($items))
    <p style="margin-bottom:6px;">Items we tried to reserve:</p>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">
      @foreach($items as $item)
        <tr>
          <td style="padding:6px 0;">{{ $item['name'] }} × {{ $item['quantity'] }}</td>
          <td style="padding:6px 0; text-align:right;">{{ $money($item['amount'] * $item['quantity']) }}</td>
        </tr>
      @endforeach
    </table>
  @endif
  <p style="margin-bottom:0;">Please retry checkout or use a different card. If this keeps happening, email <a href="mailto:support@weofferwellness.co.uk">support@weofferwellness.co.uk</a>; replies to this message aren’t monitored.</p>
@endsection
