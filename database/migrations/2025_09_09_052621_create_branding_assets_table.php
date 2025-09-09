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
        Schema::create('branding_assets', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'logo' or 'favicon'
            $table->string('name'); // Original filename
            $table->string('file_path'); // Storage path
            $table->string('file_url'); // Public URL
            $table->integer('file_size'); // File size in bytes
            $table->string('mime_type'); // MIME type
            $table->integer('width')->nullable(); // Image width
            $table->integer('height')->nullable(); // Image height
            $table->integer('version')->default(1); // Version number
            $table->boolean('is_active')->default(false); // Current active version
            $table->json('metadata')->nullable(); // Additional metadata
            $table->unsignedBigInteger('uploaded_by'); // User who uploaded
            $table->timestamp('activated_at')->nullable(); // When this version was activated
            $table->timestamps();
            
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['type', 'is_active']);
            $table->index(['type', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branding_assets');
    }
};
