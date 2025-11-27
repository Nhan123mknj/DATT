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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('public_id')->unique(); // Cloudinary public_id
            $table->string('url'); // Cloudinary URL
            $table->string('secure_url')->nullable(); // Cloudinary secure URL
            $table->string('resource_type')->default('image'); // image, video, raw
            $table->string('format')->nullable(); // jpg, png, pdf, etc
            $table->unsignedBigInteger('size')->nullable(); // file size in bytes
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('folder')->nullable(); // Cloudinary folder path
            $table->string('type')->nullable(); // avatar, document, gallery, etc
            $table->string('mime_type')->nullable();
            $table->integer('sort_order')->default(0);

            // Polymorphic relationship
            $table->morphs('mediable');

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['mediable_type', 'mediable_id']);
            $table->index('type');
            $table->index('folder');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
