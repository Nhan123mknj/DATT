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
        Schema::create('borrow_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrow_id')->constrained('borrows');
            $table->foreignId('device_unit_id')->constrained('device_units');
            $table->string('condition_at_borrow');
            $table->string('condition_at_return')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->enum('status', ['borrowed', 'returned'])->default('borrowed');
            $table->timestamps();
   
            $table->string('proof_image_return')->nullable()->comment('Ảnh chụp thiết bị khi trả');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrow_details');
    }
};
