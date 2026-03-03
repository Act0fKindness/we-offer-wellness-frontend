<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_sessions') || Schema::hasColumn('user_sessions', 'fingerprint')) {
            return;
        }

        Schema::table('user_sessions', function (Blueprint $table) {
            $table->string('fingerprint', 64)
                ->nullable()
                ->after('user_id');

            $table->index('fingerprint');
            $table->unique(['user_id', 'fingerprint']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('user_sessions') || ! Schema::hasColumn('user_sessions', 'fingerprint')) {
            return;
        }

        Schema::table('user_sessions', function (Blueprint $table) {
            $table->dropUnique('user_sessions_user_id_fingerprint_unique');
            $table->dropIndex('user_sessions_fingerprint_index');
            $table->dropColumn('fingerprint');
        });
    }
};
