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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('client_token');
            $table->string('assignment_name', 200);
            $table->string('module_code', 40)->index();
            $table->string('institution', 150)->nullable();
            $table->string('country', 60)->nullable()->index();
            $table->string('subject', 100)->nullable()->index();
            $table->string('assessment_type', 60)->nullable();
            $table->date('deadline')->nullable();
            $table->text('description')->nullable();
            $table->string('contact_email');
            $table->string('whatsapp_number', 20);
            $table->string('status', 40)->default('new')->index();
            $table->timestamp('submitted_at')->index();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'client_token']);
        });

        Schema::create('submission_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_brief')->default(false);
            $table->string('original_name', 255);
            $table->string('storage_path')->unique();
            $table->string('mime_type', 120);
            $table->string('extension', 10);
            $table->unsignedBigInteger('size');
            $table->char('sha256', 64);
            $table->timestamps();
        });

        Schema::create('submission_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('submission_status_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_status_changes');
        Schema::dropIfExists('submission_notes');
        Schema::dropIfExists('submission_files');
        Schema::dropIfExists('submissions');
    }
};
