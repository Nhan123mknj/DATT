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
        Schema::table('device_reservations', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('checked_in_at');
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('approved_by');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');

            $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_reservations', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['completed_at', 'cancelled_by', 'cancelled_at']);
        });
    }
};
