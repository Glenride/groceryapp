<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public string $previousStatus,
        public string $newStatus,
        public ?string $message = null
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('orders.' . $this->order->customer_id),
            new PrivateChannel('order.' . $this->order->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.status.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'previous_status' => $this->previousStatus,
            'new_status' => $this->newStatus,
            'message' => $this->message ?? $this->getDefaultMessage(),
            'timestamp' => now()->toIso8601String(),
            'batch_id' => $this->order->batch_id,
            'shopper' => $this->order->batch?->shopper?->user?->only(['id', 'name']),
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
