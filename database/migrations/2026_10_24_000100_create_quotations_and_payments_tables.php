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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->unique()->constrained()->cascadeOnDelete();
            $table->char('currency', 3);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('deposit_amount', 12, 2);
            $table->date('work_due_date')->nullable();
            $table->string('country', 60)->nullable()->index();
            $table->text('message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Payments are financial records: they outlive the submission (no cascade)
        // and keep only a reference and country snapshot, never personal details.
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('submission_reference', 20)->index();
            $table->string('country', 60)->nullable()->index();
            $table->string('type', 10)->default('payment');
            $table->char('currency', 3)->index();
            $table->decimal('amount', 12, 2);
            $table->date('paid_on')->index();
            $table->string('method', 30);
            $table->string('reference', 120)->nullable();
            $table->decimal('rate_to_bdt', 16, 6);
            $table->decimal('amount_bdt', 14, 2);
            $table->string('rate_source', 10);
            $table->text('note')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->string('payment_status', 20)->default('not_quoted')->index()->after('status');
        });

        Schema::table('notification_logs', function (Blueprint $table) {
            $table->foreignId('payment_id')->nullable()->after('enquiry_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_id');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropIndex(['payment_status']);
            $table->dropColumn('payment_status');
        });

        Schema::dropIfExists('payments');
        Schema::dropIfExists('quotations');
    }
};
