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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('student_code', 50)->unique()->comment('Mã học sinh');
            $table->string('grade_level', 10)->nullable()->comment('Khối lớp (10, 11, 12)');
            $table->string('class_name', 50)->nullable()->comment('Lớp (10A1, 11B2...)');
            $table->date('enrollment_date')->nullable()->comment('Ngày nhập học');
            $table->string('parent_name', 200)->nullable()->comment('Tên phụ huynh');
            $table->string('parent_phone', 20)->nullable()->comment('SĐT phụ huynh');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('student_code');
            $table->index('grade_level');
            $table->index('class_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
