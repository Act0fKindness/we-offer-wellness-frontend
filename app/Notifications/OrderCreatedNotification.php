<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(private string $audience, private mixed $order)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $id = (int) ($this->order->id ?? 0);

        $isClient = $this->audience === 'client';

        return [
            'title' => $isClient
                ? 'Order confirmed'
                : ($this->audience === 'admin' ? 'New order completed' : 'New booking confirmed'),
            'message' => $isClient
                ? "Your order #{$id} has been confirmed."
                : "Order #{$id} has been completed.",
            'url' => $isClient
                ? url('/account/orders/'.$id)
                : url('/orders/'.$id),
            'type' => 'order_created',
            'model' => ['id' => $id, 'status' => $this->order->status ?? 'paid'],
            'timestamp' => now(),
        ];
    }
}
