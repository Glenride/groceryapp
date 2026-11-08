<?php

use App\Models\Favorite;
use App\Models\Merchant;
use App\Models\Shopper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
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

describe('FavoriteController', function () {

    describe('index', function () {
        it('returns empty list when user has no favorites', function () {
            $response = $this->actingAs($this->customer)
                ->getJson('/api/favorites');

            $response->assertOk()
                ->assertJsonPath('data.data', []);
        });

        it('returns user favorites with favoritable data', function () {
            Favorite::create([
                'user_id' => $this->customer->id,
                'favoritable_type' => Shopper::class,
                'favoritable_id' => $this->shopperProfile->id,
            ]);

            $response = $this->actingAs($this->customer)
                ->getJson('/api/favorites');

            $response->assertOk()
                ->assertJsonCount(1, 'data.data')
                ->assertJsonPath('data.data.0.favoritable_id', $this->shopperProfile->id);
        });

        it('filters favorites by type', function () {
            Favorite::create([
                'user_id' => $this->customer->id,
                'favoritable_type' => Shopper::class,
                'favoritable_id' => $this->shopperProfile->id,
            ]);

            Favorite::create([
                'user_id' => $this->customer->id,
                'favoritable_type' => Merchant::class,
                'favoritable_id' => $this->merchantProfile->id,
            ]);

            $response = $this->actingAs($this->customer)
                ->getJson('/api/favorites?type=shopper');

            $response->assertOk()
                ->assertJsonCount(1, 'data.data')
                ->assertJsonPath('data.data.0.favoritable_type', Shopper::class);
        });
    });

    describe('toggleShopper', function () {
        it('adds shopper to favorites', function () {
            $response = $this->actingAs($this->customer)
                ->postJson("/api/favorites/shoppers/{$this->shopperProfile->id}");

            $response->assertOk()
                ->assertJsonPath('data.is_favorited', true)
                ->assertJsonPath('message', 'Shopper added to favorites');

            $this->assertDatabaseHas('favorites', [
                'user_id' => $this->customer->id,
                'favoritable_type' => Shopper::class,
                'favoritable_id' => $this->shopperProfile->id,
            ]);
        });

        it('removes shopper from favorites when already favorited', function () {
            Favorite::create([
                'user_id' => $this->customer->id,
                'favoritable_type' => Shopper::class,
                'favoritable_id' => $this->shopperProfile->id,
            ]);

            $response = $this->actingAs($this->customer)
                ->postJson("/api/favorites/shoppers/{$this->shopperProfile->id}");

            $response->assertOk()
                ->assertJsonPath('data.is_favorited', false)
                ->assertJsonPath('message', 'Shopper removed from favorites');

            $this->assertDatabaseMissing('favorites', [
                'user_id' => $this->customer->id,
                'favoritable_type' => Shopper::class,
                'favoritable_id' => $this->shopperProfile->id,
            ]);
        });

        it('rejects non-customers from favoriting', function () {
            $response = $this->actingAs($this->shopper)
                ->postJson("/api/favorites/shoppers/{$this->shopperProfile->id}");

            $response->assertForbidden()
                ->assertJsonPath('message', 'Only customers can favorite shoppers');
        });

        it('prevents favoriting inactive shoppers', function () {
            $this->shopperProfile->update(['status' => 'suspended']);

            $response = $this->actingAs($this->customer)
                ->postJson("/api/favorites/shoppers/{$this->shopperProfile->id}");

            $response->assertStatus(400)
                ->assertJsonPath('message', 'Cannot favorite inactive shopper');
        });

        it('stores notes with favorite', function () {
            $response = $this->actingAs($this->customer)
                ->postJson("/api/favorites/shoppers/{$this->shopperProfile->id}", [
                    'notes' => 'Great service!',
                ]);

            $response->assertOk();

            $this->assertDatabaseHas('favorites', [
                'user_id' => $this->customer->id,
                'favoritable_id' => $this->shopperProfile->id,
                'notes' => 'Great service!',
            ]);
        });
    });

    describe('toggleMerchant', function () {
        it('adds merchant to favorites', function () {
            $response = $this->actingAs($this->customer)
                ->postJson("/api/favorites/merchants/{$this->merchantProfile->id}");

            $response->assertOk()
                ->assertJsonPath('data.is_favorited', true);

            $this->assertDatabaseHas('favorites', [
                'user_id' => $this->customer->id,
                'favoritable_type' => Merchant::class,
                'favoritable_id' => $this->merchantProfile->id,
            ]);
        });

        it('rejects non-customers from favoriting merchants', function () {
            $response = $this->actingAs($this->merchant)
                ->postJson("/api/favorites/merchants/{$this->merchantProfile->id}");

            $response->assertForbidden()
                ->assertJsonPath('message', 'Only customers can favorite merchants');
        });
    });

    describe('checkStatus', function () {
        it('returns favorite status for multiple shoppers', function () {
            $shopper2 = Shopper::factory()->create(['status' => 'active']);

            Favorite::create([
                'user_id' => $this->customer->id,
                'favoritable_type' => Shopper::class,
                'favoritable_id' => $this->shopperProfile->id,
            ]);

            $response = $this->actingAs($this->customer)
                ->postJson('/api/favorites/check', [
                    'shoppers' => [$this->shopperProfile->id, $shopper2->id],
                ]);

            $response->assertOk()
                ->assertJsonPath('data.shoppers.' . $this->shopperProfile->id, true)
                ->assertJsonPath('data.shoppers.' . $shopper2->id, false);
        });

        it('returns favorite status for multiple merchants', function () {
            $merchant2 = Merchant::factory()->create(['status' => 'approved']);

            Favorite::create([
                'user_id' => $this->customer->id,
                'favoritable_type' => Merchant::class,
                'favoritable_id' => $this->merchantProfile->id,
            ]);

            $response = $this->actingAs($this->customer)
                ->postJson('/api/favorites/check', [
                    'merchants' => [$this->merchantProfile->id, $merchant2->id],
                ]);

            $response->assertOk()
                ->assertJsonPath('data.merchants.' . $this->merchantProfile->id, true)
                ->assertJsonPath('data.merchants.' . $merchant2->id, false);
        });

        it('validates shopper IDs exist', function () {
            $response = $this->actingAs($this->customer)
                ->postJson('/api/favorites/check', [
                    'shoppers' => [99999],
                ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['shoppers.0']);
        });
    });

    describe('updateNotes', function () {
        it('updates notes for a favorite', function () {
            $favorite = Favorite::create([
                'user_id' => $this->customer->id,
                'favoritable_type' => Shopper::class,
                'favoritable_id' => $this->shopperProfile->id,
                'notes' => 'Initial notes',
            ]);

            $response = $this->actingAs($this->customer)
                ->putJson("/api/favorites/{$favorite->id}", [
                    'notes' => 'Updated notes',
                ]);

            $response->assertOk()
                ->assertJsonPath('data.notes', 'Updated notes');

            $this->assertDatabaseHas('favorites', [
                'id' => $favorite->id,
                'notes' => 'Updated notes',
            ]);
        });

        it('prevents updating another users favorite', function () {
            $otherCustomer = User::factory()->create(['role' => 'customer']);
            $favorite = Favorite::create([
                'user_id' => $otherCustomer->id,
                'favoritable_type' => Shopper::class,
                'favoritable_id' => $this->shopperProfile->id,
            ]);

            $response = $this->actingAs($this->customer)
                ->putJson("/api/favorites/{$favorite->id}", [
                    'notes' => 'Updated notes',
                ]);

            $response->assertForbidden();
        });
    });

    describe('destroy', function () {
        it('deletes a favorite', function () {
            $favorite = Favorite::create([
                'user_id' => $this->customer->id,
                'favoritable_type' => Shopper::class,
                'favoritable_id' => $this->shopperProfile->id,
            ]);

            $response = $this->actingAs($this->customer)
                ->deleteJson("/api/favorites/{$favorite->id}");

            $response->assertOk()
                ->assertJsonPath('message', 'Favorite removed successfully');

            $this->assertDatabaseMissing('favorites', ['id' => $favorite->id]);
        });

        it('prevents deleting another users favorite', function () {
            $otherCustomer = User::factory()->create(['role' => 'customer']);
            $favorite = Favorite::create([
                'user_id' => $otherCustomer->id,
                'favoritable_type' => Shopper::class,
                'favoritable_id' => $this->shopperProfile->id,
            ]);

            $response = $this->actingAs($this->customer)
                ->deleteJson("/api/favorites/{$favorite->id}");

            $response->assertForbidden();
        });
    });
});

describe('Favorite Model', function () {
    describe('toggle', function () {
        it('creates favorite when not exists', function () {
            $result = Favorite::toggle(
                $this->customer->id,
                Shopper::class,
                $this->shopperProfile->id
            );

            expect($result)->toBeTrue();
            $this->assertDatabaseHas('favorites', [
                'user_id' => $this->customer->id,
                'favoritable_type' => Shopper::class,
                'favoritable_id' => $this->shopperProfile->id,
            ]);
        });

        it('deletes favorite when exists', function () {
            Favorite::create([
                'user_id' => $this->customer->id,
                'favoritable_type' => Shopper::class,
                'favoritable_id' => $this->shopperProfile->id,
            ]);

            $result = Favorite::toggle(
                $this->customer->id,
                Shopper::class,
                $this->shopperProfile->id
            );

            expect($result)->toBeFalse();
            $this->assertDatabaseMissing('favorites', [
                'user_id' => $this->customer->id,
                'favoritable_type' => Shopper::class,
                'favoritable_id' => $this->shopperProfile->id,
            ]);
        });
    });

    describe('isFavorited', function () {
        it('returns true when item is favorited', function () {
            Favorite::create([
                'user_id' => $this->customer->id,
                'favoritable_type' => Shopper::class,
                'favoritable_id' => $this->shopperProfile->id,
            ]);

            $result = Favorite::isFavorited(
                $this->customer->id,
                Shopper::class,
                $this->shopperProfile->id
            );

            expect($result)->toBeTrue();
        });

        it('returns false when item is not favorited', function () {
            $result = Favorite::isFavorited(
                $this->customer->id,
                Shopper::class,
                $this->shopperProfile->id
            );

            expect($result)->toBeFalse();
        });
    });

    describe('unique constraint', function () {
        it('prevents duplicate favorites', function () {
            Favorite::create([
                'user_id' => $this->customer->id,
                'favoritable_type' => Shopper::class,
                'favoritable_id' => $this->shopperProfile->id,
            ]);

            // Attempt to create duplicate
            try {
                Favorite::create([
                    'user_id' => $this->customer->id,
                    'favoritable_type' => Shopper::class,
                    'favoritable_id' => $this->shopperProfile->id,
                ]);
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                // Expected
            }

            $count = Favorite::where('user_id', $this->customer->id)->count();
            expect($count)->toBe(1);
        });
    });
});
