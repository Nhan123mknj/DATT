<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_reservation_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')
                ->constrained('device_reservations')
                ->onDelete('cascade');

            $table->foreignId('device_unit_id')->constrained()->onDelete('cascade');

            $table->enum('status', ['pending', 'approved', 'cancelled', 'completed'])
                ->default('pending');

            $table->text('notes')->nullable();
            $table->timestamps();

    
            $table->unique(['device_unit_id', 'reservation_id'], 'unique_reservation_unit');

            $table->index(['device_unit_id', 'reservation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_reservation_details');
    }
};
