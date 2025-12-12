<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix existing data before converting to enum
        DB::table('borrow_details')->where('condition_at_borrow', 'tốt')->update(['condition_at_borrow' => 'good']);
        DB::table('borrow_details')->whereNotIn('condition_at_borrow', ['excellent', 'good', 'fair', 'damaged'])->update(['condition_at_borrow' => 'good']);

        Schema::table('borrow_details', function (Blueprint $table) {
            $table->enum('condition_at_borrow', [
                'excellent',
                'good',
                'fair',
                'damaged'
            ])->default('good')->change();

            $table->enum('condition_at_return', [
                'excellent',
                'good',
                'fair',
                'damaged',
                'broken'
            ])->nullable()->change();

            $table->timestamp('returned_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrow_details', function (Blueprint $table) {
            $table->dropColumn([
                'condition_at_borrow',
                'condition_at_return',
                'damage_notes',
                'damage_fee',
                'return_photos',
                'returned_at'
            ]);
        });
    }
};
