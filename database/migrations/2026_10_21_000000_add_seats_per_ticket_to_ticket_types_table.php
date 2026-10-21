<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->unsignedSmallInteger('seats_per_ticket')->default(1)->after('name');
        });

        DB::table('ticket_types')
            ->where(function ($query) {
                $query->whereIn('key', ['duo', 'boleto-dupla'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%dupla%']);
            })
            ->update(['seats_per_ticket' => 2]);
    }

    public function down(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->dropColumn('seats_per_ticket');
        });
    }
};
