<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_sessions')) {
            return;
        }

        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('fingerprint', 64)->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('device', 255)->nullable();
            $table->string('location', 255)->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'fingerprint']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};
