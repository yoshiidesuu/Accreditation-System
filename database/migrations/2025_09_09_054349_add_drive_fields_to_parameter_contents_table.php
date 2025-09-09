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
        Schema::table('parameter_contents', function (Blueprint $table) {
            $table->string('drive_file_id')->nullable()->after('file_path');
            $table->text('share_link')->nullable()->after('drive_file_id');
            $table->string('storage_driver')->default('local')->after('share_link');
            $table->json('file_metadata')->nullable()->after('storage_driver');
            $table->boolean('requires_permission')->default(false)->after('file_metadata');
            $table->timestamp('permission_requested_at')->nullable()->after('requires_permission');
            $table->string('permission_status')->default('none')->after('permission_requested_at'); // none, requested, granted, denied
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parameter_contents', function (Blueprint $table) {
            $table->dropColumn([
                'drive_file_id',
                'share_link',
                'storage_driver',
                'file_metadata',
                'requires_permission',
                'permission_requested_at',
                'permission_status'
            ]);
        });
    }
};
