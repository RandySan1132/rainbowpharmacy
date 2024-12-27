<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class NearlyExpiredNotification extends Notification
{
    use Queueable;

    protected $purchase;

    public function __construct($purchase)
    {
        $this->purchase = $purchase;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'nearly_expired',
            'purchase_id' => $this->purchase->id,
            'product_name' => $this->purchase->barCodeData->product_name,
            'message' => "{$this->purchase->barCodeData->product_name} is nearly expired.",
            'image' => $this->purchase->barCodeData->image,
            'quantity' => $this->purchase->quantity,
        ];
    }
}
