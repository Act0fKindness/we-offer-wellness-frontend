<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('store_abandoned_carts')) return;
        Schema::create('store_abandoned_carts', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_key', 100)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->nullable()->index();
            $table->string('customer_name')->nullable();
            $table->json('items');
            $table->decimal('cart_total', 10, 2)->default(0);
            $table->timestamp('first_added_at')->nullable();
            $table->timestamp('last_activity_at')->nullable()->index();
            $table->timestamp('purchased_at')->nullable();
            $table->timestamp('stage_one_sent_at')->nullable();
            $table->timestamp('stage_two_sent_at')->nullable();
            $table->timestamp('stage_three_sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('store_abandoned_carts'); }
};
