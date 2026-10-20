<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->timestamps();
        });

        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('tagline')->nullable();
            $t->text('description')->nullable();
            $t->string('art_seed')->nullable();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->foreignId('brand_id')->constrained()->restrictOnDelete();
            $t->foreignId('category_id')->constrained()->restrictOnDelete();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('sku')->unique();
            $t->string('short_description', 300);
            $t->text('description');
            $t->decimal('price', 10, 2);
            $t->decimal('compare_at_price', 10, 2)->nullable();
            $t->decimal('rating_avg', 3, 2)->default(0);
            $t->unsignedInteger('rating_count')->default(0);
            $t->unsignedInteger('view_count')->default(0);
            $t->unsignedInteger('sales_count')->default(0);
            $t->boolean('is_featured')->default(false);
            $t->boolean('is_new')->default(false);
            $t->string('status', 20)->default('active'); // active | draft | archived
            $t->json('features')->nullable();
            $t->json('specifications')->nullable();
            $t->string('materials')->nullable();
            $t->timestamp('published_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['status', 'category_id']);
            $t->index('price');
            $t->index('published_at');
        });

        Schema::create('tags', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->string('slug')->unique();
            $t->timestamps();
        });

        Schema::create('product_tag', function (Blueprint $t) {
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $t->primary(['product_id', 'tag_id']);
        });

        Schema::create('product_images', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->string('path')->nullable();   // uploaded file on the public disk
            $t->string('url')->nullable();    // external image URL
            $t->string('art_seed')->nullable(); // locally generated artwork (never breaks)
            $t->string('alt');
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
            $t->index(['product_id', 'sort_order']);
        });

        Schema::create('product_variants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->string('sku')->unique();
            $t->string('color')->nullable();
            $t->string('color_hex', 9)->nullable();
            $t->string('size')->nullable();
            $t->decimal('price_override', 10, 2)->nullable();
            $t->integer('stock')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->index(['product_id', 'is_active']);
        });

        Schema::create('product_attributes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('value');
            $t->index(['name', 'value']);
            $t->index('product_id');
        });

        Schema::create('inventory_movements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $t->integer('change');
            $t->integer('stock_after');
            $t->string('reason', 40); // order, cancellation, adjustment, restock, seed
            $t->nullableMorphs('reference');
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('note')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['inventory_movements', 'product_attributes', 'product_variants', 'product_images', 'product_tag', 'tags', 'products', 'categories', 'brands'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
