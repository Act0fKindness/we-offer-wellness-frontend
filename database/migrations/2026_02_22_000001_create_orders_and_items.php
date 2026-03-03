<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('email')->nullable();
                $table->string('currency', 3)->default('gbp');
                $table->unsignedBigInteger('amount_total')->default(0); // pence
                $table->enum('status', ['pending','paid','cancelled','failed','refunded'])->default('pending');
                $table->string('stripe_session_id')->nullable()->index();
                $table->string('stripe_payment_intent_id')->nullable()->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->string('name');
                $table->string('sku')->nullable();
                $table->unsignedBigInteger('unit_amount'); // pence
                $table->unsignedInteger('quantity');
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('stripe_webhook_events')) {
            Schema::create('stripe_webhook_events', function (Blueprint $table) {
                $table->id();
                $table->string('event_id')->unique();
                $table->string('type')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void {
        Schema::dropIfExists('stripe_webhook_events');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
