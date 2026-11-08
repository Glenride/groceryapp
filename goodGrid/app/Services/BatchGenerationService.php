<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Order;

class BatchGenerationService
{
    public function generateBatchNumber(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return "BATCH-{$date}-{$random}";
    }

    public function createBatchForOrders(array $orderIds, ?float $basePay = null, ?float $estimatedTip = null): Batch
    {
        $orders = Order::whereIn('id', $orderIds)->get();

        if ($orders->isEmpty()) {
            throw new \InvalidArgumentException('No valid orders provided');
        }

        // Calculate distances and estimates
        $totalDistance = 0;
        $estimatedTime = 0;
        $stores = [];

        foreach ($orders as $order) {
            $merchant = $order->merchant;
            $stores[$merchant->id] = $merchant;
            
            // Estimate shopping time (5 min per item, min 15 min)
            $itemCount = $order->items->sum('quantity');
            $estimatedTime += max(15, $itemCount * 5);
        }

        // Add travel time between stores if multiple
        if (count($stores) > 1) {
            $estimatedTime += (count($stores) - 1) * 10;
        }

        // Add delivery time (approx 5 min per mile, min 15 min)
        $estimatedTime += 15 + ($totalDistance * 5);

        // Calculate pay components
        $basePay = $basePay ?? max(7.00, count($orders) * 7.00);
        $distancePay = $totalDistance * 0.50; // $0.50 per mile
        $timePay = ($estimatedTime / 60) * 5.00; // $5 per hour estimated

        $batch = Batch::create([
            'batch_number' => $this->generateBatchNumber(),
            'status' => 'pending',
            'estimated_pay' => $basePay + $distancePay + $timePay + ($estimatedTip ?? 0),
            'base_pay' => $basePay,
            'distance_pay' => $distancePay,
            'time_pay' => $timePay,
            'estimated_tip' => $estimatedTip ?? 0,
            'total_distance' => $totalDistance,
            'estimated_time' => $estimatedTime,
        ]);

        // Associate orders with batch
        Order::whereIn('id', $orderIds)->update(['batch_id' => $batch->id]);

        return $batch->fresh();
    }

    public function autoAssignOrdersToBatches(?int $limit = null): int
    {
        // Get pending orders not yet in a batch
        $pendingOrders = Order::whereNull('batch_id')
            ->where('status', 'pending')
            ->with(['merchant', 'address'])
            ->get();

        if ($pendingOrders->isEmpty()) {
            return 0;
        }

        $batchesCreated = 0;
        $groupedOrders = $pendingOrders->groupBy('merchant_id');

        foreach ($groupedOrders as $merchantId => $orders) {
            // Create batches of up to 3 orders per merchant
            $orderChunks = $orders->chunk(3);

            foreach ($orderChunks as $orderChunk) {
                if ($limit !== null && $batchesCreated >= $limit) {
                    return $batchesCreated;
                }

                $this->createBatchForOrders($orderChunk->pluck('id')->toArray());
                $batchesCreated++;
            }
        }

        return $batchesCreated;
    }
}
