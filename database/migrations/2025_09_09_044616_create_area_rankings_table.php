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
        Schema::create('area_rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained()->onDelete('cascade');
            $table->foreignId('college_id')->constrained()->onDelete('cascade');
            $table->enum('ranking_period', ['weekly', 'monthly', 'quarterly', 'annual']);
            
            // Ranking metrics
            $table->decimal('completion_percentage', 5, 2)->default(0); // 0-100%
            $table->decimal('quality_score', 5, 2)->default(0); // 0-100
            $table->decimal('accreditor_rating', 5, 2)->default(0); // 0-100
            $table->decimal('weighted_score', 8, 2)->default(0); // Final computed score
            $table->integer('rank_position')->nullable(); // Position in ranking
            
            // Supporting data
            $table->integer('total_parameters')->default(0);
            $table->integer('completed_parameters')->default(0);
            $table->integer('approved_swot_count')->default(0);
            $table->integer('rejected_swot_count')->default(0);
            
            // Computation metadata
            $table->timestamp('computed_at');
            $table->foreignId('computed_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['ranking_period', 'rank_position']);
            $table->index(['college_id', 'ranking_period']);
            $table->index(['area_id', 'ranking_period']);
            $table->index('computed_at');
            
            // Unique constraint to prevent duplicate rankings
            $table->unique(['area_id', 'ranking_period', 'computed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area_rankings');
    }
};
