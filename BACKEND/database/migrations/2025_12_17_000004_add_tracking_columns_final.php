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
            // Drop approved_by if exists
            if (Schema::hasColumn('borrows', 'approved_by_user_id')) {
                $table->dropForeign(['approved_by_user_id']);
                $table->dropColumn('approved_by_user_id');
            }

            // Add created_by if not exists
            if (!Schema::hasColumn('borrows', 'created_by_user_id')) {
                $table->foreignId('created_by_user_id')->nullable()->after('borrower_id')->constrained('users')->nullOnDelete();
            }

            // Add issued_by if not exists
            if (!Schema::hasColumn('borrows', 'issued_by_user_id')) {
                $table->foreignId('issued_by_user_id')->nullable()->after('created_by_user_id')->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrows', function (Blueprint $table) {
            if (Schema::hasColumn('borrows', 'created_by_user_id')) {
                $table->dropForeign(['created_by_user_id']);
                $table->dropColumn('created_by_user_id');
            }
            if (Schema::hasColumn('borrows', 'issued_by_user_id')) {
                $table->dropForeign(['issued_by_user_id']);
                $table->dropColumn('issued_by_user_id');
            }

            // Re-add approved_by
            $table->foreignId('approved_by_user_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
        });
    }
};
