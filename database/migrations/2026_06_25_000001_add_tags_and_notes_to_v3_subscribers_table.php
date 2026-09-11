<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('v3_subscribers')) {
            return;
        }

        Schema::table('v3_subscribers', function (Blueprint $table) {
            if (!Schema::hasColumn('v3_subscribers', 'notes')) {
                $table->text('notes')->nullable()->after('business_name');
            }
            if (!Schema::hasColumn('v3_subscribers', 'tags')) {
                $table->json('tags')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('v3_subscribers')) {
            return;
        }

        Schema::table('v3_subscribers', function (Blueprint $table) {
            if (Schema::hasColumn('v3_subscribers', 'tags')) {
                $table->dropColumn('tags');
            }
            if (Schema::hasColumn('v3_subscribers', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
