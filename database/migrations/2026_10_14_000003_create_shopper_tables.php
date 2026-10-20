<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $t->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $t->unsignedSmallInteger('quantity');
            $t->timestamps();
            $t->unique(['cart_id', 'product_variant_id']);
        });

        Schema::create('wishlist_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->timestamps();
            $t->unique(['user_id', 'product_id']);
            $t->index('created_at');
        });

        Schema::create('addresses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('label', 40)->default('Home');
            $t->string('full_name');
            $t->string('phone', 40)->nullable();
            $t->string('line1');
            $t->string('line2')->nullable();
            $t->string('city');
            $t->string('state', 80)->nullable();
            $t->string('postal_code', 20);
            $t->string('country', 2)->default('US');
            $t->boolean('is_default')->default(false);
            $t->timestamps();
        });

        Schema::create('user_preferences', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $t->json('interests')->nullable();   // category slugs
            $t->string('style', 30)->nullable();
            $t->timestamps();
        });

        Schema::create('recently_viewed_products', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->timestamp('viewed_at');
            $t->unique(['user_id', 'product_id']);
            $t->index(['user_id', 'viewed_at']);
        });

        Schema::create('product_views', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->string('visitor_hash', 64)->nullable();
            $t->boolean('is_demo')->default(false);
            $t->timestamp('viewed_at');
            $t->index(['product_id', 'viewed_at']);
            $t->index('viewed_at');
        });

        Schema::create('search_terms', function (Blueprint $t) {
            $t->id();
            $t->string('term')->unique();
            $t->unsignedInteger('hits')->default(1);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['search_terms', 'product_views', 'recently_viewed_products', 'user_preferences', 'addresses', 'wishlist_items', 'cart_items', 'carts'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
