<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Clients added by an administrator may have no email (WhatsApp/phone
     * customers), and orders taken off-site have no module code or brief.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('country', 60)->nullable()->after('whatsapp_number');
            $table->foreignId('created_by')->nullable()->after('deletion_requested_at')->constrained('users')->nullOnDelete();
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->string('source', 20)->default('portal')->index()->after('client_token');
            $table->string('module_code', 40)->nullable()->change();
            $table->string('contact_email')->nullable()->change();
            $table->string('whatsapp_number', 20)->nullable()->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('source', 20)->default('portal')->index()->after('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['source']);
            $table->dropColumn('source');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropIndex(['source']);
            $table->dropColumn('source');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn('country');
        });
    }
};
