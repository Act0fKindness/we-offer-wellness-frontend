<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('order_items')) {
            return;
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'name')) {
                $table->string('name')->default('')->after('order_id');
            }
            if (!Schema::hasColumn('order_items', 'sku')) {
                $table->string('sku')->nullable()->after('name');
            }
            if (!Schema::hasColumn('order_items', 'unit_amount')) {
                $table->unsignedBigInteger('unit_amount')->default(0)->after('sku');
            }
            if (!Schema::hasColumn('order_items', 'meta')) {
                $table->json('meta')->nullable()->after('quantity');
            }
        });

        if (Schema::hasColumn('order_items', 'price') && Schema::hasColumn('order_items', 'unit_amount')) {
            DB::table('order_items')->orderBy('id')->select('id', 'price', 'product_id', 'name', 'sku')->chunkById(200, function ($items) {
                foreach ($items as $item) {
                    $name = $item->name ?: ('Product '.($item->product_id ?? ''));
                    $sku = $item->sku ?: ($item->product_id ? (string) $item->product_id : null);
                    $unit = (int) round(((float) $item->price) * 100);
                    DB::table('order_items')->where('id', $item->id)->update([
                        'name' => $name,
                        'sku' => $sku,
                        'unit_amount' => $unit,
                    ]);
                }
            });
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'product_id')) {
                $table->dropForeign(['product_id']);
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'price')) {
                $table->dropColumn('price');
            }
            if (Schema::hasColumn('order_items', 'product_id')) {
                $table->dropColumn('product_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('order_items')) {
            return;
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable()->after('order_id');
            }
            if (!Schema::hasColumn('order_items', 'price')) {
                $table->decimal('price', 8, 2)->default(0)->after('quantity');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'product_id')) {
                $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            }
        });

        if (Schema::hasColumn('order_items', 'price') && Schema::hasColumn('order_items', 'unit_amount')) {
            DB::table('order_items')->orderBy('id')->select('id', 'unit_amount', 'sku')->chunkById(200, function ($items) {
                foreach ($items as $item) {
                    $price = ((int) $item->unit_amount) / 100;
                    DB::table('order_items')->where('id', $item->id)->update([
                        'price' => $price,
                        'product_id' => $item->sku && is_numeric($item->sku) ? (int) $item->sku : null,
                    ]);
                }
            });
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'meta')) {
                $table->dropColumn('meta');
            }
            if (Schema::hasColumn('order_items', 'unit_amount')) {
                $table->dropColumn('unit_amount');
            }
            if (Schema::hasColumn('order_items', 'sku')) {
                $table->dropColumn('sku');
            }
            if (Schema::hasColumn('order_items', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};
