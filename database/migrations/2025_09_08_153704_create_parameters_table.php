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
        Schema::create('parameters', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['text', 'textarea', 'number', 'date', 'file', 'select', 'checkbox', 'radio']);
            $table->json('validation_rules')->nullable(); // Store validation rules as JSON
            $table->json('options')->nullable(); // For select, checkbox, radio options
            $table->boolean('required')->default(false);
            $table->integer('order')->default(0);
            $table->boolean('active')->default(true);
            
            // Foreign keys
            $table->unsignedBigInteger('area_id');
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('cascade');
            
            // Indexes
            $table->index(['area_id', 'active']);
            $table->index(['area_id', 'order']);
            $table->index('code');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parameters');
    }
};
