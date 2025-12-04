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

        // Migrate data từ profile_picture_id sang avatar type
        if (Schema::hasColumn('users', 'profile_picture_id')) {
            // Update type của các avatar cũ
            DB::statement("
                UPDATE media 
                SET type = 'avatar' 
                WHERE id IN (SELECT profile_picture_id FROM users WHERE profile_picture_id IS NOT NULL)
            ");

            // Drop foreign key và column
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['profile_picture_id']);
                $table->dropColumn('profile_picture_id');
            });
        }

        // Đơn giản hóa media table
        Schema::table('media', function (Blueprint $table) {
            // Drop các columns không cần thiết
            $columnsToDrop = [
                'resource_type',
                'format',
                'size',
                'width',
                'height',
                'folder',
                'mime_type',
                'sort_order',
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('media', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Drop soft deletes nếu không cần
            if (Schema::hasColumn('media', 'deleted_at')) {
                $table->dropSoftDeletes();
            }

            // Drop old indexes
            $table->dropIndex(['type']);
            $table->dropIndex(['folder']);
        });

        // Tạo composite index mới (performance tốt hơn)
        Schema::table('media', function (Blueprint $table) {
            $table->index(['mediable_type', 'mediable_id', 'type'], 'media_polymorphic_type_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->string('secure_url')->nullable();
            $table->string('resource_type')->default('image');
            $table->string('format')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('folder')->nullable();
            $table->string('mime_type')->nullable();
            $table->integer('sort_order')->default(0);
            $table->softDeletes();
            
            $table->index('type');
            $table->index('folder');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('profile_picture_id')
                ->nullable()
                ->constrained('media')
                ->nullOnDelete();
        });
    }
};
