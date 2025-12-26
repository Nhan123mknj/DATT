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
            // Giá thanh lý (khi bán lại)
            $table->decimal('retirement_value', 15, 2)
                ->nullable()
                ->after('retire_reason')
                ->comment('Giá thanh lý/bán lại (VNĐ)');

            // Phương thức thanh lý
            $table->enum('retirement_method', ['sold', 'donated', 'discarded', 'recycled'])
                ->nullable()
                ->after('retirement_value')
                ->default('discarded')
                ->comment('Phương thức thanh lý: sold=bán lại, donated=tặng, discarded=vứt bỏ, recycled=tái chế');

            // Thông tin người mua (nếu bán)
            $table->string('buyer_info', 255)
                ->nullable()
                ->after('retirement_method')
                ->comment('Thông tin người mua khi bán lại');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_units', function (Blueprint $table) {
            $table->dropColumn(['retirement_value', 'retirement_method', 'buyer_info']);
        });
    }
};
