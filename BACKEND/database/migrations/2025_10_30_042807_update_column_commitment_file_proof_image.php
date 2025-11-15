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
            $table->string('commitment_file')->nullable()->comment('Đường dẫn file cam kết (nếu có)');
            $table->string('proof_image')->nullable()->comment('Ảnh chụp thiết bị khi mượn');
        });
        Schema::table('borrow_details', function (Blueprint $table) {
            $table->string('proof_image_borrow')->nullable()->comment('Ảnh chụp thiết bị khi mượn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->dropColumn('commitment_file');
            $table->dropColumn('proof_image');
        });
        Schema::table('borrow_details', function (Blueprint $table) {
            $table->dropColumn('proof_image_borrow');
        });
    }
};
