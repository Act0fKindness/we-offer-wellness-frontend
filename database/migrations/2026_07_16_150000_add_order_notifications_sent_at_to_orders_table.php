<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'order_notifications_sent_at')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->timestamp('order_notifications_sent_at')->nullable()->after('vendor_introduction_sent_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'order_notifications_sent_at')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropColumn('order_notifications_sent_at');
            });
        }
    }
};
