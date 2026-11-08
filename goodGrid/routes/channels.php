<?php

use App\Models\Order;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// User's private channel
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Customer orders channel - customer can listen to their own orders
Broadcast::channel('orders.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Individual order channel - customer or assigned shopper can listen
Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    $order = Order::find($orderId);

    if (!$order) {
        return false;
    }

    // Customer can listen to their own orders
    if ($order->customer_id === $user->id) {
        return true;
    }

    // Shopper assigned to this order's batch can listen
    if ($order->batch?->shopper?->user_id === $user->id) {
        return true;
    }

    return false;
});

// Shopper channel - only the shopper can listen
Broadcast::channel('shopper.{shopperId}', function ($user, $shopperId) {
    return $user->shopper?->id === (int) $shopperId;
});

// Batch channel - customers with orders in batch or assigned shopper can listen
Broadcast::channel('batch.{batchId}', function ($user, $batchId) {
    $order = Order::where('batch_id', $batchId)
        ->where(function ($query) use ($user) {
            $query->where('customer_id', $user->id)
                ->orWhereHas('batch.shopper', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
        })
        ->first();

    return $order !== null;
});
