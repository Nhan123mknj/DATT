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
        Schema::table('device_units', function (Blueprint $table) {
            $table->timestamp('retired_at')->nullable()->after('status');
            $table->foreignId('retired_by')->nullable()->after('retired_at')->constrained('users')->onDelete('set null');
            $table->text('retire_reason')->nullable()->after('retired_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_units', function (Blueprint $table) {
            $table->dropForeign(['retired_by']);
            $table->dropColumn(['retired_at', 'retired_by', 'retire_reason']);
        });
    }
};
