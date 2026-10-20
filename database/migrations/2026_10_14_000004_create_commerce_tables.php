<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('description')->nullable();
            $t->string('type', 10); // percent | fixed
            $t->decimal('value', 10, 2);
            $t->decimal('min_subtotal', 10, 2)->default(0);
            $t->unsignedInteger('max_uses')->nullable();
            $t->unsignedInteger('used_count')->default(0);
            $t->timestamp('starts_at')->nullable();
            $t->timestamp('ends_at')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('promotions', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->unsignedTinyInteger('discount_percent');
            $t->timestamp('starts_at');
            $t->timestamp('ends_at');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->index(['is_active', 'starts_at', 'ends_at']);
        });

        Schema::create('promotion_product', function (Blueprint $t) {
            $t->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->primary(['promotion_id', 'product_id']);
        });

        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->string('number')->unique();
            $t->string('idempotency_key')->nullable()->unique();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('email');
            $t->string('status', 30)->default('pending');          // pending|processing|packed|shipped|out_for_delivery|delivered|cancelled
            $t->string('payment_status', 20)->default('pending');  // pending|paid|failed|cancelled
            $t->string('payment_provider', 20)->nullable();        // stripe | demo
            $t->boolean('is_demo')->default(false);
            $t->string('currency', 3)->default('USD');
            $t->unsignedBigInteger('subtotal_cents');
            $t->unsignedBigInteger('discount_cents')->default(0);
            $t->unsignedBigInteger('shipping_cents')->default(0);
            $t->unsignedBigInteger('tax_cents')->default(0);
            $t->unsignedBigInteger('total_cents');
            $t->string('coupon_code')->nullable();
            $t->string('shipping_method', 30);
            $t->json('shipping_address');
            $t->date('estimated_delivery_from')->nullable();
            $t->date('estimated_delivery_to')->nullable();
            $t->string('carrier')->nullable();
            $t->string('tracking_number')->nullable();
            $t->timestamp('placed_at');
            $t->timestamp('paid_at')->nullable();
            $t->timestamp('restocked_at')->nullable();
            $t->timestamps();
            $t->index(['user_id', 'placed_at']);
            $t->index(['status', 'payment_status']);
        });

        Schema::create('order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name');
            $t->string('sku');
            $t->string('color')->nullable();
            $t->string('size')->nullable();
            $t->string('short_description', 300)->nullable();
            $t->string('image_url', 500)->nullable();
            $t->unsignedBigInteger('unit_price_cents');
            $t->unsignedSmallInteger('quantity');
            $t->unsignedBigInteger('line_total_cents');
            $t->timestamps();
        });

        Schema::create('order_status_histories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->string('status', 30);
            $t->string('note')->nullable();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamp('created_at')->useCurrent();
            $t->index(['order_id', 'created_at']);
        });

        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->string('provider', 20);             // stripe | demo
            $t->string('provider_reference')->nullable()->index(); // Checkout Session id
            $t->string('payment_intent')->nullable();
            $t->string('status', 20)->default('pending'); // pending|paid|failed|cancelled|expired
            $t->unsignedBigInteger('amount_cents');
            $t->string('currency', 3)->default('USD');
            $t->boolean('is_demo')->default(false);
            $t->string('failure_reason')->nullable();
            $t->timestamps();
        });

        Schema::create('stripe_events', function (Blueprint $t) {
            $t->id();
            $t->string('event_id')->unique();
            $t->string('type');
            $t->timestamp('processed_at')->nullable();
            $t->timestamps();
        });

        Schema::create('reviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $t->unsignedTinyInteger('rating');
            $t->string('title', 120)->nullable();
            $t->text('body');
            $t->boolean('is_verified')->default(false);
            $t->boolean('is_demo')->default(false);
            $t->string('status', 20)->default('pending'); // pending | approved | rejected
            $t->timestamps();
            $t->unique(['product_id', 'user_id']);
            $t->index(['product_id', 'status']);
        });
    }

    public function down(): void
    {
        foreach (['reviews', 'stripe_events', 'payments', 'order_status_histories', 'order_items', 'orders', 'promotion_product', 'promotions', 'coupons'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
