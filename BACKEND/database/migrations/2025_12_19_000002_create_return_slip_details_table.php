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
        Schema::create('return_slip_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_slip_id')->constrained('return_slips')->onDelete('cascade');
            $table->foreignId('borrow_detail_id')->constrained('borrow_details');
            $table->foreignId('device_unit_id')->constrained('device_units');

            // Condition for this specific device
            $table->enum('condition_status', ['good', 'minor_damage', 'major_damage', 'broken'])->default('good');
            $table->text('condition_notes')->nullable();
            $table->text('damage_description')->nullable();

            // Damage fee for this device
            $table->decimal('damage_fee', 10, 2)->default(0);

            $table->timestamps();

            // Indexes
            $table->index('return_slip_id');
            $table->index('borrow_detail_id');
            $table->index('device_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_slip_details');
    }
};
