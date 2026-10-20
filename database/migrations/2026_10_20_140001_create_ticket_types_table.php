<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('key', 30);
            $table->string('name');
            $table->decimal('early_price', 10, 2)->nullable();
            $table->decimal('regular_price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['event_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};
