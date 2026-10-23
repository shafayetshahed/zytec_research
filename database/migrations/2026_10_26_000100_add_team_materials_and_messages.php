<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Team members (role "expert") prepare guidance and feedback materials for
     * an order; administrators review and release them. Each order has two
     * message threads: student ↔ team and expert ↔ team.
     */
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->foreignId('expert_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->text('expert_instructions')->nullable()->after('description');
            $table->timestamp('assigned_at')->nullable()->after('expert_instructions');
        });

        Schema::create('deliverables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('original_name', 255);
            $table->string('storage_path')->unique();
            $table->string('mime_type', 120);
            $table->string('extension', 10);
            $table->unsignedBigInteger('size');
            $table->char('sha256', 64);
            $table->text('note')->nullable();
            $table->string('status', 20)->default('pending_review')->index();
            $table->text('review_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->boolean('release_before_payment')->default(false);
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 10);
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body')->nullable();
            $table->string('attachment_name', 255)->nullable();
            $table->string('attachment_path')->nullable()->unique();
            $table->string('attachment_mime', 120)->nullable();
            $table->unsignedBigInteger('attachment_size')->nullable();
            $table->timestamps();

            $table->index(['submission_id', 'channel', 'id']);
        });

        Schema::create('message_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 10);
            $table->unsignedBigInteger('last_read_message_id')->default(0);
            $table->timestamps();

            $table->unique(['submission_id', 'user_id', 'channel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_reads');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('deliverables');

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('expert_id');
            $table->dropColumn(['expert_instructions', 'assigned_at']);
        });
    }
};
