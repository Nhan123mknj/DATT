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
        // Bỏ các cột xét duyệt khỏi bảng `borrows`
        Schema::table('borrows', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approved_by', 'approved_at', 'rejection_reason']);
        });

        // Thêm `borrow_detail_id` vào bảng `return_logs`
        Schema::table('return_logs', function (Blueprint $table) {
            $table->foreignId('borrow_detail_id')->nullable()->after('device_unit_id')->constrained('borrow_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hoàn tác thay đổi trên bảng `borrows`
        Schema::table('borrows', function (Blueprint $table) {
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
        });

        // Hoàn tác thay đổi trên bảng `return_logs`
        Schema::table('return_logs', function (Blueprint $table) {
            $table->dropForeign(['borrow_detail_id']);
            $table->dropColumn('borrow_detail_id');
        });
    }
};
