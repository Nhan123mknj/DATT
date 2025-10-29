<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('device_reservations', function (Blueprint $table) {
             $table->id();
            $table->foreignId('device_id')->constrained();
            $table->foreignId('device_unit_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->dateTime('reserved_from');
            $table->dateTime('reserved_until');
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'completed'])
                  ->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status']);
            $table->index(['reserved_from', 'reserved_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_reservations');
    }
};
