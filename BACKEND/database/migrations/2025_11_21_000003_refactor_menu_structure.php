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
        // Drop foreign key constraint first
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropForeign(['menu_id']);
        });

        // Drop menu_id column from menu_items
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn('menu_id');
        });

        // Drop menus table
        Schema::dropIfExists('menus');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate menus table
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Add menu_id back to menu_items
        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreignId('menu_id')->after('id')->constrained('menus')->cascadeOnDelete();
        });
    }
};
