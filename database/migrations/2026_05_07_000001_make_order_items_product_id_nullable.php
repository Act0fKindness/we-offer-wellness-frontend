<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_items') || ! Schema::hasColumn('order_items', 'product_id')) {
            return;
        }

        try {
            DB::statement('ALTER TABLE order_items MODIFY product_id BIGINT UNSIGNED NULL');
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('order_items') || ! Schema::hasColumn('order_items', 'product_id')) {
            return;
        }

        try {
            DB::statement('ALTER TABLE order_items MODIFY product_id BIGINT UNSIGNED NOT NULL');
        } catch (\Throwable $e) {
            report($e);
        }
    }
};
