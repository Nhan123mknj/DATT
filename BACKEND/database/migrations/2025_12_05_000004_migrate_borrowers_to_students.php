<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all users with role 'student' (converted from 'borrower')
        $students = DB::table('users')
            ->where('role', 'student')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('students')
                    ->whereColumn('students.user_id', 'users.id');
            })
            ->get();

        $currentYear = date('Y');
        $counter = 1;

        foreach ($students as $user) {
            // Auto-generate student code
            $studentCode = 'HS' . $currentYear . str_pad($counter, 3, '0', STR_PAD_LEFT);

            // Check if code already exists, increment if needed
            while (DB::table('students')->where('student_code', $studentCode)->exists()) {
                $counter++;
                $studentCode = 'HS' . $currentYear . str_pad($counter, 3, '0', STR_PAD_LEFT);
            }

            DB::table('students')->insert([
                'user_id' => $user->id,
                'student_code' => $studentCode,
                'grade_level' => null,
                'class_name' => null,
                'enrollment_date' => Carbon::now(),
                'parent_name' => null,
                'parent_phone' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            $counter++;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove auto-generated student records if needed
        // DB::table('students')->where('student_code', 'LIKE', 'HS%')->delete();
    }
};
