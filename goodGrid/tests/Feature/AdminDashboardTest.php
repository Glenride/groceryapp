<?php

use App\Models\Merchant;
use App\Models\Order;
use App\Models\Shopper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->customer = User::factory()->create(['role' => 'customer']);
    $this->shopper = User::factory()->create(['role' => 'shopper']);
    $this->merchant = User::factory()->create(['role' => 'merchant']);

    $this->shopperProfile = Shopper::factory()->create([
        'user_id' => $this->shopper->id,
        'status' => 'active',
    ]);

    $this->merchantProfile = Merchant::factory()->create([
        'user_id' => $this->merchant->id,
        'status' => 'approved',
    ]);
});

describe('AdminDashboardController', function () {

    describe('authentication & authorization', function () {
        it('redirects guests to login', function () {
            $response = $this->get('/admin');
            $response->assertRedirect('/login');
        });

        it('allows admin access', function () {
            $response = $this->actingAs($this->admin)->get('/admin');
            $response->assertOk();
        });

        it('forbids customer access', function () {
            $response = $this->actingAs($this->customer)->get('/admin');
            $response->assertForbidden();
        });

        it('forbids shopper access', function () {
            $response = $this->actingAs($this->shopper)->get('/admin');
            $response->assertForbidden();
        });

        it('forbids merchant access', function () {
            $response = $this->actingAs($this->merchant)->get('/admin');
            $response->assertForbidden();
        });
    });

    describe('dashboard', function () {
        it('returns correct stats', function () {
            // Create some test data
            Order::factory()->count(5)->create([
                'customer_id' => $this->customer->id,
                'merchant_id' => $this->merchantProfile->id,
                'status' => 'delivered',
            ]);

            Order::factory()->count(3)->create([
                'customer_id' => $this->customer->id,
                'merchant_id' => $this->merchantProfile->id,
                'status' => 'pending',
            ]);

            Merchant::factory()->count(2)->create(['status' => 'pending']);

            $response = $this->actingAs($this->admin)->get('/admin');

            $response->assertInertia(fn ($page) => $page
                ->has('stats')
                ->where('stats.total_users', 5) // admin + customer + shopper + merchant + 2 pending merchants
                ->where('stats.customers', 1)
                ->where('stats.shoppers', 1)
                ->where('stats.merchants', 3)
                ->where('stats.pending_merchants', 2)
                ->where('stats.total_orders', 8)
                ->where('stats.pending_orders', 3)
                ->where('stats.completed_orders', 5)
            );
        });

        it('includes recent orders and users', function () {
            $order = Order::factory()->create([
                'customer_id' => $this->customer->id,
                'merchant_id' => $this->merchantProfile->id,
            ]);

            $response = $this->actingAs($this->admin)->get('/admin');

            $response->assertInertia(fn ($page) => $page
                ->has('recentOrders')
                ->has('recentUsers')
            );
        });
    });

    describe('users management', function () {
        it('lists all users with pagination', function () {
            User::factory()->count(25)->create();

            $response = $this->actingAs($this->admin)->get('/admin/users');

            $response->assertInertia(fn ($page) => $page
                ->component('admin/users')
                ->has('users.data', 20)
                ->has('users')
                ->has('filters')
            );
        });

        it('filters users by role', function () {
            $response = $this->actingAs($this->admin)->get('/admin/users?role=customer');

            $response->assertInertia(fn ($page) => $page
                ->where('filters.role', 'customer')
            );
        });

        it('searches users by name or email', function () {
            $response = $this->actingAs($this->admin)->get('/admin/users?search=john');

            $response->assertInertia(fn ($page) => $page
                ->where('filters.search', 'john')
            );
        });

        it('shows user details', function () {
            $response = $this->actingAs($this->admin)
                ->get("/admin/users/{$this->customer->id}");

            $response->assertInertia(fn ($page) => $page
                ->component('admin/user-detail')
                ->has('user')
                ->where('user.id', $this->customer->id)
            );
        });

        it('updates user information', function () {
            $response = $this->actingAs($this->admin)
                ->put("/admin/users/{$this->customer->id}", [
                    'name' => 'Updated Name',
                    'role' => 'shopper',
                    'is_active' => false,
                ]);

            $response->assertRedirect();
            $response->assertSessionHas('success');

            $this->customer->refresh();
            expect($this->customer->name)->toBe('Updated Name');
            expect($this->customer->role)->toBe('shopper');
            expect($this->customer->is_active)->toBeFalse();
        });

        it('validates user update data', function () {
            $response = $this->actingAs($this->admin)
                ->put("/admin/users/{$this->customer->id}", [
                    'email' => 'invalid-email',
                    'role' => 'invalid-role',
                ]);

            $response->assertSessionHasErrors(['email', 'role']);
        });
    });

    describe('orders management', function () {
        beforeEach(function () {
            $this->order = Order::factory()->create([
                'customer_id' => $this->customer->id,
                'merchant_id' => $this->merchantProfile->id,
                'status' => 'pending',
            ]);
        });

        it('lists all orders', function () {
            $response = $this->actingAs($this->admin)->get('/admin/orders');

            $response->assertInertia(fn ($page) => $page
                ->component('admin/orders')
                ->has('orders.data')
                ->has('statuses')
                ->has('filters')
            );
        });

        it('filters orders by status', function () {
            $response = $this->actingAs($this->admin)->get('/admin/orders?status=pending');

            $response->assertInertia(fn ($page) => $page
                ->where('filters.status', 'pending')
            );
        });

        it('searches orders by order number', function () {
            $response = $this->actingAs($this->admin)
                ->get('/admin/orders?search=' . $this->order->order_number);

            $response->assertInertia(fn ($page) => $page
                ->where('filters.search', $this->order->order_number)
            );
        });

        it('shows order details', function () {
            $response = $this->actingAs($this->admin)
                ->get("/admin/orders/{$this->order->id}");

            $response->assertInertia(fn ($page) => $page
                ->component('admin/order-detail')
                ->has('order')
                ->where('order.id', $this->order->id)
            );
        });
    });

    describe('merchants management', function () {
        it('lists all merchants', function () {
            $response = $this->actingAs($this->admin)->get('/admin/merchants');

            $response->assertInertia(fn ($page) => $page
                ->component('admin/merchants')
                ->has('merchants.data')
                ->has('statuses')
                ->has('filters')
            );
        });

        it('filters merchants by status', function () {
            $response = $this->actingAs($this->admin)->get('/admin/merchants?status=pending');

            $response->assertInertia(fn ($page) => $page
                ->where('filters.status', 'pending')
            );
        });

        it('searches merchants by business name', function () {
            $response = $this->actingAs($this->admin)
                ->get('/admin/merchants?search=' . $this->merchantProfile->business_name);

            $response->assertInertia(fn ($page) => $page
                ->where('filters.search', $this->merchantProfile->business_name)
            );
        });

        it('updates merchant status', function () {
            $response = $this->actingAs($this->admin)
                ->put("/admin/merchants/{$this->merchantProfile->id}/status", [
                    'status' => 'suspended',
                ]);

            $response->assertRedirect();
            $response->assertSessionHas('success');

            $this->merchantProfile->refresh();
            expect($this->merchantProfile->status)->toBe('suspended');
        });

        it('validates merchant status update', function () {
            $response = $this->actingAs($this->admin)
                ->put("/admin/merchants/{$this->merchantProfile->id}/status", [
                    'status' => 'invalid-status',
                ]);

            $response->assertSessionHasErrors(['status']);
        });
    });
});

describe('EnsureAdmin middleware', function () {
    it('allows admin users', function () {
        $response = $this->actingAs($this->admin)->get('/admin');
        $response->assertOk();
    });

    it('blocks non-admin users with 403', function () {
        $response = $this->actingAs($this->customer)->get('/admin');
        $response->assertForbidden();
        $response->assertSee('Admin access required');
    });

    it('blocks unauthenticated users', function () {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    });
});
