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
        Schema::create('accreditation_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accreditation_id')->constrained()->onDelete('cascade');
            $table->foreignId('parameter_content_id')->constrained()->onDelete('cascade');
            $table->foreignId('tagged_by')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Unique constraint to prevent duplicate tags
            $table->unique(['accreditation_id', 'parameter_content_id']);
            
            // Indexes for better performance
            $table->index('accreditation_id');
            $table->index('parameter_content_id');
            $table->index('tagged_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accreditation_tags');
    }
};
