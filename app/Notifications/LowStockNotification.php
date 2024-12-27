<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;

class LowStockNotification extends Notification
{
    use Queueable;

    protected $product;
    protected $quantity;
    protected $type; // Add type property

    public function __construct($product, $quantity, $type = 'low_stock') // Add type parameter
    {
        $this->product = $product;
        $this->quantity = $quantity;
        $this->type = $type; // Set type
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $message = "{$this->product->product_name} is low on stock. Only {$this->quantity} left.";
        if ($this->type == 'expired') {
            $message = "{$this->product->product_name} has expired.";
        } elseif ($this->type == 'nearly_expired') {
            $message = "{$this->product->product_name} is nearly expired.";
        }

        return [
            'type' => 'low_stock',
            'product_name' => $this->product->product_name,
            'quantity' => $this->quantity,
            'message' => $message,
            'image' => $this->product->image,
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'low_stock',
            'product' => [
                'name' => $this->product->name,
                'quantity' => $this->product->quantity,
            ],
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Low Stock Alert')
                    ->line("The product {$this->product->product_name} is low in stock.")
                    ->line("Current stock: {$this->currentStock}")
                    ->action('View Products', url('/admin/products'))
                    ->line('Please reorder to maintain inventory.');
    }
}