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
        Schema::table('borrows', function (Blueprint $table) {
            // Return metadata
            $table->text('return_notes')->nullable()->after('status');

            // Digital signatures (Base64 encoded)
            $table->longText('staff_signature')->nullable()->after('return_notes');
            $table->longText('borrower_signature')->nullable()->after('staff_signature');

            // PDF receipt
            $table->string('return_slip_pdf_path')->nullable()->after('borrower_signature');
            $table->timestamp('return_slip_generated_at')->nullable()->after('return_slip_pdf_path');

            // Foreign key
            $table->foreign('returned_by_staff_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->dropColumn([
                'return_notes',
                'staff_signature',
                'borrower_signature',
                'return_slip_pdf_path',
                'return_slip_generated_at'
            ]);
        });
    }
};
