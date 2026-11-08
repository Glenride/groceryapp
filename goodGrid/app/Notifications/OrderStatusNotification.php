<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $previousStatus,
        public string $newStatus,
        public ?string $customMessage = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->customMessage ?? $this->getDefaultMessage();

        return (new MailMessage)
            ->subject("Order {$this->order->order_number} - Status Update")
            ->greeting("Hello {$notifiable->name},")
            ->line($message)
            ->line("Order: {$this->order->order_number}")
            ->line("Status: {$this->newStatus}")
            ->action('View Order', url('/orders/' . $this->order->id))
            ->line('Thank you for using Good Grid!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'previous_status' => $this->previousStatus,
            'new_status' => $this->newStatus,
            'message' => $this->customMessage ?? $this->getDefaultMessage(),
            'type' => 'order_status_changed',
            'batch_id' => $this->order->batch_id,
        ];
    }

    private function getDefaultMessage(): string
    {
        return match ($this->newStatus) {
            'confirmed' => 'Your order has been confirmed!',
            'preparing' => 'A shopper has started preparing your order.',
            'in_transit' => 'Your order is on the way!',
            'delivered' => 'Your order has been delivered!',
            'cancelled' => 'Your order has been cancelled.',
            'ready_for_pickup' => 'Your order is ready for pickup!',
            'picked_up' => 'Your order has been picked up.',
            default => "Your order status has been updated to: {$this->newStatus}",
        };
    }
}
