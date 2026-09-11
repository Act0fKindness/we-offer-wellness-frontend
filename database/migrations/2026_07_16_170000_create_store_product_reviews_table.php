<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('store_product_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_product_id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('title', 120)->nullable();
            $table->text('body');
            $table->string('status', 24)->default('published');
            $table->timestamps();
            $table->unique(['store_product_id', 'user_id']);
            $table->index(['store_product_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_product_reviews');
    }
};
