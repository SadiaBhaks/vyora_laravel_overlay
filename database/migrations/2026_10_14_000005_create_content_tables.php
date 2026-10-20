<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('tagline')->nullable();
            $t->text('description')->nullable();
            $t->string('art_seed')->nullable();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->boolean('is_featured')->default(false);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('collection_product', function (Blueprint $t) {
            $t->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->primary(['collection_id', 'product_id']);
        });

        Schema::create('newsletter_subscribers', function (Blueprint $t) {
            $t->id();
            $t->string('email')->unique();
            $t->timestamp('subscribed_at');
            $t->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email');
            $t->string('subject');
            $t->text('message');
            $t->timestamp('read_at')->nullable();
            $t->timestamps();
        });

        Schema::create('settings', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->text('value')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['settings', 'contact_messages', 'newsletter_subscribers', 'collection_product', 'collections'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
