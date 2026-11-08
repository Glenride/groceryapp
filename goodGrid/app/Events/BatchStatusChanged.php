<?php

namespace App\Events;

use App\Models\Batch;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BatchStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Batch $batch,
        public string $previousStatus,
        public string $newStatus,
        public ?string $message = null
    ) {
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('batch.' . $this->batch->id),
        ];

        // Broadcast to all customers with orders in this batch
        foreach ($this->batch->orders as $order) {
            $channels[] = new PrivateChannel('orders.' . $order->customer_id);
        }

        // Broadcast to assigned shopper
        if ($this->batch->shopper_id) {
            $channels[] = new PrivateChannel('shopper.' . $this->batch->shopper_id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'batch.status.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'batch_id' => $this->batch->id,
            'batch_number' => $this->batch->batch_number,
            'previous_status' => $this->previousStatus,
            'new_status' => $this->newStatus,
            'message' => $this->message ?? $this->getDefaultMessage(),
            'timestamp' => now()->toIso8601String(),
            'shopper_id' => $this->batch->shopper_id,
            'order_ids' => $this->batch->orders->pluck('id'),
        ];
    }

    private function getDefaultMessage(): string
    {
        return match ($this->newStatus) {
            'accepted' => 'A shopper has accepted your batch!',
            'shopping' => 'Your shopper has started shopping.',
            'in_transit' => 'Your order is on the way!',
            'delivered' => 'Your order has been delivered!',
            default => "Batch status updated to: {$this->newStatus}",
        };
    }
}
