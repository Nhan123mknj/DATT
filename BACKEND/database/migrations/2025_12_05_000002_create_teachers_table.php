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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('teacher_code', 50)->unique()->comment('Mã giáo viên');
            $table->string('department', 100)->nullable()->comment('Bộ môn');
            $table->string('position', 100)->nullable()->comment('Chức vụ');
            $table->date('hire_date')->nullable()->comment('Ngày tuyển dụng');
            $table->string('office_room', 50)->nullable()->comment('Phòng làm việc');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('teacher_code');
            $table->index('department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
