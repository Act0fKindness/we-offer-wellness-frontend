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
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'currency')) {
                $table->string('currency', 3)->default('GBP')->after('email');
            }
            if (!Schema::hasColumn('orders', 'amount_total')) {
                $table->unsignedBigInteger('amount_total')->default(0)->after('currency');
            }
            if (!Schema::hasColumn('orders', 'stripe_session_id')) {
                $table->string('stripe_session_id')->nullable()->after('status')->index();
            }
            if (!Schema::hasColumn('orders', 'stripe_payment_intent_id')) {
                $table->string('stripe_payment_intent_id')->nullable()->after('stripe_session_id')->index();
            }
        });

        if (Schema::hasColumn('orders', 'total_price') && Schema::hasColumn('orders', 'amount_total')) {
            DB::table('orders')->orderBy('id')->select('id', 'total_price')->chunkById(200, function ($orders) {
                foreach ($orders as $order) {
                    $amount = (int) round(((float) $order->total_price) * 100);
                    DB::table('orders')->where('id', $order->id)->update(['amount_total' => $amount]);
                }
            });
        }

        if (Schema::hasColumn('orders', 'total_price')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('total_price');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'total_price')) {
                $table->decimal('total_price', 8, 2)->nullable()->after('email');
            }
        });

        if (Schema::hasColumn('orders', 'amount_total') && Schema::hasColumn('orders', 'total_price')) {
            DB::table('orders')->orderBy('id')->select('id', 'amount_total')->chunkById(200, function ($orders) {
                foreach ($orders as $order) {
                    $total = ((int) $order->amount_total) / 100;
                    DB::table('orders')->where('id', $order->id)->update(['total_price' => $total]);
                }
            });
        }

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'stripe_payment_intent_id')) {
                $table->dropColumn('stripe_payment_intent_id');
            }
            if (Schema::hasColumn('orders', 'stripe_session_id')) {
                $table->dropColumn('stripe_session_id');
            }
            if (Schema::hasColumn('orders', 'amount_total')) {
                $table->dropColumn('amount_total');
            }
            if (Schema::hasColumn('orders', 'currency')) {
                $table->dropColumn('currency');
            }
        });
    }
};
