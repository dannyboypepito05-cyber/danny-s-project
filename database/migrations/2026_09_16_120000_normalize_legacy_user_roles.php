<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereIn('role', ['User', 'user'])
            ->update(['role' => 'Student']);

        DB::table('users')
            ->whereIn('role', ['Admin', 'admin'])
            ->update(['role' => 'Administrator']);
    }

    public function down(): void
    {
        // Legacy role values are intentionally not restored.
    }
};
