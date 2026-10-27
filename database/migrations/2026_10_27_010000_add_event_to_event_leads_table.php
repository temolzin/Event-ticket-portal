<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_leads', function (Blueprint $table) {
            $table->string('event')->default('Nexus tech')->after('ticket_type');
        });
    }

    public function down(): void
    {
        Schema::table('event_leads', function (Blueprint $table) {
            $table->dropColumn('event');
        });
    }
};
