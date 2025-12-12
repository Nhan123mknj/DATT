<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role_new', ['student', 'teacher', 'staff', 'admin'])
                ->default('student')
                ->after('role');
        });

        DB::table('users')->update([
            'role_new' => DB::raw("CASE 
                WHEN role = 'borrower' THEN 'student'
                WHEN role = 'staff' THEN 'staff'
                WHEN role = 'admin' THEN 'admin'
                ELSE 'student'
            END")
        ]);

        // Step 3: Drop old column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        // Step 4: Rename new column to role
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('role_new', 'role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse: Add old enum column
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role_old', ['borrower', 'staff', 'admin'])
                ->default('borrower')
                ->after('role');
        });

        // Copy data back, converting student/teacher to borrower
        DB::table('users')->update([
            'role_old' => DB::raw("CASE 
                WHEN role IN ('student', 'teacher') THEN 'borrower'
                WHEN role = 'staff' THEN 'staff'
                WHEN role = 'admin' THEN 'admin'
                ELSE 'borrower'
            END")
        ]);

        // Drop new column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        // Rename back
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('role_old', 'role');
        });
    }
};
