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
        Schema::create('return_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrow_id')->constrained();
            $table->foreignId('device_unit_id')->constrained();
            $table->foreignId('returned_by')->constrained('users');
            $table->foreignId('received_by')->nullable()->constrained('users');
            $table->dateTime('return_date');
            $table->enum('condition_before', ['excellent', 'good', 'fair', 'poor']);
            $table->enum('condition_after', ['excellent', 'good', 'fair', 'poor']);
            $table->text('damage_description')->nullable();
            $table->decimal('damage_fee', 10, 2)->default(0);
            $table->decimal('late_fee', 10, 2)->default(0);
            $table->enum('status', ['pending', 'completed', 'disputed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('return_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_logs');
    }
};
