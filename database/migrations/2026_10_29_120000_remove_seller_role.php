<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('role', 'seller')->update(['role' => 'admin']);
    }

    public function down(): void
    {
        // The seller role is intentionally removed and cannot be restored safely.
    }
};
