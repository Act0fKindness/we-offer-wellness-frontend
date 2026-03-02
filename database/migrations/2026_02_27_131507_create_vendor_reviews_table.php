<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vendor_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->nullable()->index();
            $table->string('vendor_name')->nullable();
            $table->string('provider_identifier')->nullable();
            $table->string('source', 64);
            $table->string('external_id', 96);
            $table->text('source_url')->nullable();
            $table->decimal('rating', 3, 2)->nullable();
            $table->text('review_text')->nullable();
            $table->string('reviewer_name')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->decimal('confidence', 4, 3)->nullable();
            $table->string('match_reason')->nullable();
            $table->string('query_used')->nullable();
            $table->json('product_titles')->nullable();
            $table->string('place_id')->nullable()->index();
            $table->json('place_payload')->nullable();
            $table->json('raw_payload')->nullable();
            $table->unsignedBigInteger('review_id')->nullable()->index();
            $table->timestamps();

            $table->unique(['source', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_reviews');
    }
};
