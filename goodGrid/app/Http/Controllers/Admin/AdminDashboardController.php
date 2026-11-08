<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function dashboard(): Response
    {
        $stats = [
            'total_users' => User::count(),
            'customers' => User::where('role', 'customer')->count(),
            'shoppers' => User::where('role', 'shopper')->count(),
            'merchants' => User::where('role', 'merchant')->count(),
            'pending_merchants' => Merchant::where('status', 'pending')->count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'completed_orders' => Order::where('status', 'delivered')->count(),
            'todays_revenue' => Order::whereDate('created_at', today())->sum('total'),
        ];

        $recentOrders = Order::with(['customer', 'merchant'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentUsers = User::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('admin/dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'recentUsers' => $recentUsers,
        ]);
    }

    public function users(Request $request): Response
    {
        $users = User::query()
            ->when($request->role, fn ($q, $role) => $q->where('role', $role))
            ->when($request->search, fn ($q, $search) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/users', [
            'users' => $users,
            'filters' => $request->only(['role', 'search']),
        ]);
    }

    public function showUser(User $user): Response
    {
        $user->load(['orders', 'shopper', 'merchant']);

        return Inertia::render('admin/user-detail', [
            'user' => $user,
        ]);
    }

    public function updateUser(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'role' => 'sometimes|in:customer,shopper,merchant,admin',
            'is_active' => 'sometimes|boolean',
        ]);

        $user->update($validated);

        return redirect()->back()->with('success', 'User updated successfully');
    }

    public function orders(Request $request): Response
    {
        $orders = Order::with(['customer', 'merchant', 'batch.shopper.user'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->search, fn ($q, $search) => $q->where('order_number', 'like', "%{$search}%"))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $statuses = ['pending', 'confirmed', 'preparing', 'in_transit', 'delivered', 'cancelled'];

        return Inertia::render('admin/orders', [
            'orders' => $orders,
            'statuses' => $statuses,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function showOrder(Order $order): Response
    {
        $order->load(['customer', 'merchant', 'items.product', 'batch.shopper.user', 'address']);

        return Inertia::render('admin/order-detail', [
            'order' => $order,
        ]);
    }

    public function merchants(Request $request): Response
    {
        $merchants = Merchant::with('user')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->search, fn ($q, $search) => $q->where('business_name', 'like', "%{$search}%"))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $statuses = ['pending', 'approved', 'suspended', 'rejected'];

        return Inertia::render('admin/merchants', [
            'merchants' => $merchants,
            'statuses' => $statuses,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function updateMerchantStatus(Request $request, Merchant $merchant): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,suspended,rejected',
        ]);

        $merchant->update($validated);

        return redirect()->back()->with('success', 'Merchant status updated');
    }
}
