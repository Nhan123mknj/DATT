<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->dateTime('reserved_from');
            $table->dateTime('reserved_until');

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'cancelled',
                'completed',
                'no_show'
            ])->default('pending');

            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();     
            $table->boolean('is_no_show')->default(false);      
            $table->text('no_show_notes')->nullable();         

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('reserved_from');
            $table->index('reserved_until');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_reservations');
    }
};
