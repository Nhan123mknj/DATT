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
        Schema::table('devices', function (Blueprint $table) {
            $table->enum('device_type', ['normal', 'consumable', 'expensive'])
                  ->default('normal')
                  ->after('id');
            $table->decimal('price', 10, 2)
                  ->default(0)
                  ->after('device_type');
            
            $table->index('device_type');
        });

        Schema::table('device_units', function (Blueprint $table) {
            $table->dateTime('reserved_until')->nullable();
            $table->foreignId('reserved_by_user_id')
                  ->nullable()
                  ->constrained('users');
            
            $table->index(['reserved_until', 'reserved_by_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_units', function (Blueprint $table) {
            $table->dropForeign(['reserved_by_user_id']);
            $table->dropColumn(['reserved_until', 'reserved_by_user_id']);
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn(['device_type', 'price']);
        });
    }
};
