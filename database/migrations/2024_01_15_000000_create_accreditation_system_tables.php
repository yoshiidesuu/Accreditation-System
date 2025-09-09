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
        // Users table with role-based access
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'coordinator', 'faculty', 'staff'])->default('faculty');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->string('phone')->nullable();
            $table->json('permissions')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('theme_mode')->nullable()->default('light'); // 'light' or 'dark'
            $table->json('theme_preferences')->nullable(); // Custom theme settings
            $table->rememberToken();
            $table->timestamps();
            
            $table->index(['role', 'status']);
            $table->index('department');
        });

        // Password reset tokens table
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Sessions table
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Cache table
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        // Accreditation-specific tables start here

        // Cache locks table
        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        // Jobs table
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        // Job batches table
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        // Failed jobs table
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // Accreditation-specific tables start here

        // Colleges table
        Schema::create('colleges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('address')->nullable();
            $table->string('contact')->nullable();
            $table->foreignId('coordinator_id')->nullable()->constrained('users');
            $table->json('meta')->nullable();
            $table->timestamps();
            
            $table->index('code');
        });

        // Academic years table
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('active')->default(false);
            $table->timestamps();
            
            $table->index('active');
        });

        // Parameters table - stores accreditation parameters/criteria
        Schema::create('parameters', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // e.g., 'A1', 'B2.1'
            $table->string('title');
            $table->text('description');
            $table->enum('category', ['A', 'B', 'C', 'D']); // Main categories
            $table->string('subcategory')->nullable(); // e.g., '1', '2.1', '3.2'
            $table->integer('weight')->default(1); // Scoring weight
            $table->enum('status', ['active', 'inactive', 'draft'])->default('active');
            $table->json('required_documents')->nullable(); // List of required document types
            $table->text('evaluation_criteria')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            
            $table->index(['category', 'status']);
            $table->index('code');
        });

        // Parameter contents table - stores uploaded files and content for each parameter
        Schema::create('parameter_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parameter_id')->constrained('parameters')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('content_type', ['document', 'image', 'video', 'link', 'text']);
            $table->string('file_path')->nullable(); // For uploaded files
            $table->string('file_name')->nullable();
            $table->string('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->text('content')->nullable(); // For text content or links
            $table->enum('status', ['pending', 'approved', 'rejected', 'revision_needed'])->default('pending');
            $table->text('review_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->integer('version')->default(1);
            $table->boolean('is_current_version')->default(true);
            $table->timestamps();
            
            $table->index(['parameter_id', 'status']);
            $table->index(['uploaded_by', 'status']);
            $table->index('content_type');
        });

        // Evaluations table - stores evaluation results
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parameter_id')->constrained('parameters');
            $table->foreignId('evaluator_id')->constrained('users');
            $table->decimal('score', 5, 2)->nullable(); // Score out of 100
            $table->enum('rating', ['excellent', 'very_good', 'good', 'satisfactory', 'needs_improvement'])->nullable();
            $table->text('comments')->nullable();
            $table->text('recommendations')->nullable();
            $table->json('evidence_reviewed')->nullable(); // IDs of parameter_contents reviewed
            $table->enum('status', ['draft', 'submitted', 'final'])->default('draft');
            $table->date('evaluation_date');
            $table->timestamps();
            
            $table->index(['parameter_id', 'status']);
            $table->index(['evaluator_id', 'evaluation_date']);
            $table->unique(['parameter_id', 'evaluator_id', 'evaluation_date']);
        });

        // Reports table - generated accreditation reports
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['parameter', 'category', 'comprehensive', 'summary']);
            $table->json('parameters_included')->nullable(); // Parameter IDs included
            $table->date('report_period_start');
            $table->date('report_period_end');
            $table->enum('status', ['generating', 'completed', 'failed'])->default('generating');
            $table->string('file_path')->nullable(); // Generated report file
            $table->json('statistics')->nullable(); // Summary statistics
            $table->foreignId('generated_by')->constrained('users');
            $table->timestamps();
            
            $table->index(['type', 'status']);
            $table->index(['generated_by', 'created_at']);
        });

        // Notifications table - system notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // Class name of notification
            $table->morphs('notifiable'); // User or other model
            $table->text('data'); // JSON data
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index('read_at');
        });

        // Activity log table - audit trail
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->string('event')->nullable();
            $table->nullableMorphs('causer', 'causer');
            $table->json('properties')->nullable();
            $table->string('batch_uuid')->nullable();
            $table->timestamps();
            
            $table->index('log_name');
            $table->index('subject_type');
            $table->index('subject_id');
            $table->index('causer_type');
            $table->index('causer_id');
        });

        // Comments table - for collaborative feedback
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->morphs('commentable'); // Can comment on parameters, contents, evaluations
            $table->foreignId('user_id')->constrained('users');
            $table->text('content');
            $table->foreignId('parent_id')->nullable()->constrained('comments'); // For threaded comments
            $table->enum('status', ['active', 'hidden', 'deleted'])->default('active');
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
        });

        // File uploads table - centralized file management
        Schema::create('file_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('path');
            $table->string('disk')->default('local');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->string('hash')->nullable(); // File hash for deduplication
            $table->morphs('uploadable'); // Related model
            $table->foreignId('uploaded_by')->constrained('users');
            $table->json('metadata')->nullable(); // Additional file metadata
            $table->timestamps();
            
            $table->index('hash');
            $table->index('uploaded_by');
        });

        // Settings table - system configuration
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false); // Can be accessed by non-admin users
            $table->timestamps();
            
            $table->index('key');
            $table->index('is_public');
        });

        // Announcements table - system-wide announcements
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->enum('type', ['info', 'warning', 'success', 'danger'])->default('info');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->json('target_roles')->nullable(); // Which roles should see this
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            $table->index(['is_active', 'starts_at', 'ends_at']);
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('file_uploads');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('activity_log');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('evaluations');
        Schema::dropIfExists('parameter_contents');
        Schema::dropIfExists('parameters');
        Schema::dropIfExists('academic_years');
        Schema::dropIfExists('colleges');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};