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
        Schema::create('return_slips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrow_id')->constrained('borrows')->onDelete('cascade');

            // Return information
            $table->dateTime('return_date');
            $table->foreignId('returned_by_staff_id')->constrained('users');

            // Condition
            $table->enum('overall_condition', ['good', 'minor_damage', 'major_damage', 'broken'])->default('good');
            $table->text('condition_notes')->nullable();

            // Penalties
            $table->integer('late_days')->default(0);
            $table->decimal('late_fee', 10, 2)->default(0);
            $table->decimal('damage_fee', 10, 2)->default(0);
            $table->decimal('total_penalty', 10, 2)->default(0);

            // Credit score
            $table->integer('credit_score_change')->default(0);

            // PDF & Signatures
            $table->string('return_slip_pdf_path')->nullable();
            $table->text('borrower_signature')->nullable();
            $table->text('staff_signature')->nullable();

            // OTP verification
            $table->string('otp_code', 6)->nullable();
            $table->dateTime('otp_verified_at')->nullable();
            $table->dateTime('otp_expires_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('borrow_id');
            $table->index('return_date');
            $table->index('returned_by_staff_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_slips');
    }
};
