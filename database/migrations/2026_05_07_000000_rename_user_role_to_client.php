<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('user_roles')) {
            return;
        }

        $clientRoleId = DB::table('roles')
            ->whereRaw('LOWER(name) = ?', ['client'])
            ->value('id');

        $userRole = DB::table('roles')
            ->whereRaw('LOWER(name) = ?', ['user'])
            ->first();

        if (! $userRole) {
            return;
        }

        if ($clientRoleId) {
            DB::table('user_roles')
                ->where('role_id', $userRole->id)
                ->update(['role_id' => $clientRoleId]);

            DB::table('roles')->where('id', $userRole->id)->delete();

            return;
        }

        DB::table('roles')
            ->where('id', $userRole->id)
            ->update(['name' => 'Client']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $clientRole = DB::table('roles')
            ->whereRaw('LOWER(name) = ?', ['client'])
            ->first();

        if ($clientRole) {
            DB::table('roles')
                ->where('id', $clientRole->id)
                ->update(['name' => 'User']);
        }
    }
};
