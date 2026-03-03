<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'vendor_notified_at')) {
                $table->timestamp('vendor_notified_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('orders', 'vendor_introduction_sent_at')) {
                $table->timestamp('vendor_introduction_sent_at')->nullable()->after('vendor_notified_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'vendor_introduction_sent_at')) {
                $table->dropColumn('vendor_introduction_sent_at');
            }
            if (Schema::hasColumn('orders', 'vendor_notified_at')) {
                $table->dropColumn('vendor_notified_at');
            }
        });
    }
};
