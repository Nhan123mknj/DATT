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
        Schema::table('device_categories', function (Blueprint $table) {
            // Thêm các cột mới
            $table->enum('type', ['normal', 'consumable', 'expensive'])
                ->default('normal')
                ->after('code');
            $table->decimal('deposit_rate', 5, 2)
                ->default(0)
                ->after('type')
                ->comment('Tỷ lệ đặt cọc (%)');
            $table->integer('max_borrow_duration')
                ->default(7)
                ->after('deposit_rate')
                ->comment('Số ngày tối đa được mượn');
            $table->boolean('requires_approval')
                ->default(true)
                ->after('max_borrow_duration');

            // Thêm index cho type
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_categories', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn([
                'type',
                'deposit_rate',
                'max_borrow_duration',
                'requires_approval'
            ]);
        });
    }
};
