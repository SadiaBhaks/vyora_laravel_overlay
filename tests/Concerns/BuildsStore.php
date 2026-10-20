<?php

namespace Tests\Concerns;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

trait BuildsStore
{
    protected function makeProduct(array $attrs = [], int $stock = 10, ?string $category = null): ProductVariant
    {
        static $n = 0;
        $n++;
        $cat = Category::firstOrCreate(['slug' => $category ?? 'electronics'], ['name' => ucfirst($category ?? 'electronics'), 'is_active' => true]);
        $brand = Brand::firstOrCreate(['slug' => 'testbrand'], ['name' => 'Testbrand']);
        $product = Product::create($attrs + [
            'brand_id' => $brand->id, 'category_id' => $cat->id, 'name' => "Test Product $n", 'slug' => "test-product-$n", 'sku' => "TP-$n",
            'short_description' => 'A test product.', 'description' => 'A test product description.', 'price' => 50.00,
            'status' => 'active', 'published_at' => now()->subDay(),
        ]);

        return ProductVariant::create(['product_id' => $product->id, 'sku' => "TP-$n-A", 'color' => 'Black', 'size' => 'M', 'stock' => $stock, 'is_active' => true]);
    }

    protected function makeUser(string $role = 'customer', array $attrs = []): User
    {
        static $u = 0;
        $u++;
        $user = User::create($attrs + ['name' => "User $u", 'email' => "user$u@example.test", 'password' => Hash::make('Password123')]);
        $user->forceFill(['role' => $role, 'email_verified_at' => now()])->save();

        return $user;
    }

    protected function address(): array
    {
        return ['full_name' => 'Test Buyer', 'line1' => '1 Test St', 'line2' => null, 'city' => 'Austin', 'state' => 'TX', 'postal_code' => '73301', 'country' => 'US', 'phone' => null];
    }

    protected function placeOrderFor(?User $user, ProductVariant $variant, int $qty = 1, string $provider = 'demo', ?string $coupon = null, ?string $key = null): Order
    {
        if ($user) {
            $this->actingAs($user);
        }
        app(\App\Services\CartService::class)->add($variant->id, $qty);

        return app(\App\Services\CheckoutService::class)->placeOrder([
            'email' => $user?->email ?? 'guest@example.test', 'address' => $this->address(), 'shipping_method' => 'standard',
            'coupon' => $coupon, 'idempotency_key' => $key ?? bin2hex(random_bytes(8)), 'payment_provider' => $provider,
        ], $user);
    }
}
