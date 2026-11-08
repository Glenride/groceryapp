<?php

namespace App\Notifications;

use App\Models\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BatchStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Batch $batch,
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
        $orderCount = $this->batch->orders->count();

        return (new MailMessage)
            ->subject("Batch Update - Status Changed")
            ->greeting("Hello {$notifiable->name},")
            ->line($message)
            ->line("Batch: {$this->batch->batch_number}")
            ->line("Orders: {$orderCount}")
            ->line("Status: {$this->newStatus}")
            ->action('View Orders', url('/orders'))
            ->line('Thank you for using Good Grid!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'batch_id' => $this->batch->id,
            'batch_number' => $this->batch->batch_number,
            'previous_status' => $this->previousStatus,
            'new_status' => $this->newStatus,
            'message' => $this->customMessage ?? $this->getDefaultMessage(),
            'type' => 'batch_status_changed',
            'order_ids' => $this->batch->orders->pluck('id')->toArray(),
            'shopper_id' => $this->batch->shopper_id,
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
