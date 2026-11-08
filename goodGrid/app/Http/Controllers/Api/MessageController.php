<?php

namespace App\Http\Controllers\Api;

use App\Events\NewMessage;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function index(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        // Verify user has access to this order
        if ($order->customer_id !== $user->id &&
            $order->batch?->shopper?->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = $order->messages()
            ->with(['sender', 'recipient'])
            ->orderBy('created_at', 'asc')
            ->paginate($request->per_page ?? 50);

        // Mark messages as read for the current user
        $order->messages()
            ->where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'data' => $messages,
            'message' => 'Messages retrieved successfully',
        ]);
    }

    public function store(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        // Verify user has access to this order
        if ($order->customer_id !== $user->id &&
            $order->batch?->shopper?->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
            'message_type' => 'nullable|in:text,substitution_request,substitution_approved,substitution_denied,delivery_issue,general',
            'metadata' => 'nullable|array',
        ]);

        // Determine recipient
        if ($order->customer_id === $user->id) {
            // Customer is sending to shopper
            $recipientId = $order->batch?->shopper?->user_id;
        } else {
            // Shopper is sending to customer
            $recipientId = $order->customer_id;
        }

        if (!$recipientId) {
            return response()->json(['message' => 'No recipient available'], 400);
        }

        DB::beginTransaction();

        try {
            $message = Message::create([
                'order_id' => $order->id,
                'sender_id' => $user->id,
                'recipient_id' => $recipientId,
                'content' => $validated['content'],
                'message_type' => $validated['message_type'] ?? 'text',
                'metadata' => $validated['metadata'] ?? null,
            ]);

            // Broadcast to recipient
            event(new NewMessage($message->fresh()));

            DB::commit();

            return response()->json([
                'data' => $message->load(['sender', 'recipient']),
                'message' => 'Message sent successfully',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to send message'], 500);
        }
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        $count = Message::where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->count();

        // Group by order for detailed view
        $byOrder = Message::where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->select('order_id', DB::raw('count(*) as count'))
            ->groupBy('order_id')
            ->with('order:id,order_number')
            ->get();

        return response()->json([
            'data' => [
                'total_unread' => $count,
                'by_order' => $byOrder,
            ],
            'message' => 'Unread count retrieved',
        ]);
    }

    public function markAsRead(Request $request, Message $message): JsonResponse
    {
        if ($message->recipient_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message->markAsRead();

        return response()->json([
            'data' => $message->fresh(),
            'message' => 'Message marked as read',
        ]);
    }

    public function conversations(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get all orders where user is customer or assigned shopper
        $orderIds = Order::where(function ($query) use ($user) {
            $query->where('customer_id', $user->id)
                ->orWhereHas('batch.shopper', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
        })
            ->whereHas('messages')
            ->pluck('id');

        $conversations = [];

        foreach ($orderIds as $orderId) {
            $latestMessage = Message::where('order_id', $orderId)
                ->with(['sender', 'order'])
                ->latest()
                ->first();

            $unreadCount = Message::where('order_id', $orderId)
                ->where('recipient_id', $user->id)
                ->whereNull('read_at')
                ->count();

            $conversations[] = [
                'order_id' => $orderId,
                'order_number' => $latestMessage->order->order_number,
                'latest_message' => $latestMessage,
                'unread_count' => $unreadCount,
            ];
        }

        // Sort by latest message
        usort($conversations, function ($a, $b) {
            return $b['latest_message']->created_at <=> $a['latest_message']->created_at;
        });

        return response()->json([
            'data' => $conversations,
            'message' => 'Conversations retrieved successfully',
        ]);
    }
}
