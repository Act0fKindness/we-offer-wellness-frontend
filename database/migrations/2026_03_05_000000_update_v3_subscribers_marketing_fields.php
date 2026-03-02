<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('v3_subscribers')) {
            Schema::create('v3_subscribers', function (Blueprint $table) {
                $table->id();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('business_name')->nullable();
                $table->boolean('offers_online')->nullable();
                $table->boolean('offers_in_person')->nullable();
                $table->string('in_person_locations')->nullable();
                $table->uuid('session_token')->nullable()->unique();
                $table->string('email')->index();
                $table->string('name')->nullable();
                $table->string('status')->default('pending')->index();
                $table->string('confirmation_token', 80)->nullable()->unique();
                $table->string('manage_token', 80)->nullable()->unique();
                $table->timestamp('confirmation_sent_at')->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('unsubscribed_at')->nullable();
                $table->json('preferences')->nullable();
                $table->string('landing_path', 2048)->nullable();
                $table->string('referrer', 2048)->nullable();
                $table->string('timezone', 100)->nullable();
                $table->string('locale', 20)->nullable();
                $table->string('languages', 255)->nullable();
                $table->string('platform', 120)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('device_memory', 20)->nullable();
                $table->string('hardware_concurrency', 20)->nullable();
                $table->unsignedInteger('screen_width')->nullable();
                $table->unsignedInteger('screen_height')->nullable();
                $table->decimal('geo_lat', 10, 7)->nullable();
                $table->decimal('geo_lng', 10, 7)->nullable();
                $table->decimal('geo_accuracy', 10, 2)->nullable();
                $table->timestamp('session_started_at')->nullable();
                $table->timestamp('last_seen_at')->nullable();
                $table->unsignedInteger('session_duration_seconds')->default(0);
                $table->timestamps();
            });

            return;
        }

        Schema::table('v3_subscribers', function (Blueprint $table) {
            if (!Schema::hasColumn('v3_subscribers', 'status')) {
                $table->string('status')->default('pending')->after('name')->index();
            }
            if (!Schema::hasColumn('v3_subscribers', 'confirmation_token')) {
                $table->string('confirmation_token', 80)->nullable()->after('status')->unique();
            }
            if (!Schema::hasColumn('v3_subscribers', 'manage_token')) {
                $table->string('manage_token', 80)->nullable()->after('confirmation_token')->unique();
            }
            if (!Schema::hasColumn('v3_subscribers', 'confirmation_sent_at')) {
                $table->timestamp('confirmation_sent_at')->nullable()->after('manage_token');
            }
            if (!Schema::hasColumn('v3_subscribers', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('confirmation_sent_at');
            }
            if (!Schema::hasColumn('v3_subscribers', 'unsubscribed_at')) {
                $table->timestamp('unsubscribed_at')->nullable()->after('confirmed_at');
            }
            if (!Schema::hasColumn('v3_subscribers', 'preferences')) {
                $table->json('preferences')->nullable()->after('unsubscribed_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('v3_subscribers')) {
            return;
        }

        Schema::table('v3_subscribers', function (Blueprint $table) {
            foreach (['status', 'confirmation_token', 'manage_token', 'confirmation_sent_at', 'confirmed_at', 'unsubscribed_at', 'preferences'] as $column) {
                if (Schema::hasColumn('v3_subscribers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
