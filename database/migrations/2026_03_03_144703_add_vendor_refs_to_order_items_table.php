<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('order_items')) {
            return;
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable()->after('order_id');
                $table->index('product_id');
            }
            if (!Schema::hasColumn('order_items', 'vendor_id')) {
                $table->unsignedBigInteger('vendor_id')->nullable()->after('product_id');
                $table->index('vendor_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('order_items')) {
            return;
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'vendor_id')) {
                $table->dropIndex(['vendor_id']);
                $table->dropColumn('vendor_id');
            }
            if (Schema::hasColumn('order_items', 'product_id')) {
                $table->dropIndex(['product_id']);
                $table->dropColumn('product_id');
            }
        });
    }
};
