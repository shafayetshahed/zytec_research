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
        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 10);
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['prefix', 'year']);
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->string('number', 20)->nullable()->unique()->after('submission_id');
            $table->string('invoice_number', 20)->nullable()->unique()->after('sent_at');
            $table->timestamp('invoiced_at')->nullable()->after('invoice_number');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('receipt_number', 20)->nullable()->unique()->after('quotation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['receipt_number']);
            $table->dropColumn('receipt_number');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropUnique(['number']);
            $table->dropUnique(['invoice_number']);
            $table->dropColumn(['number', 'invoice_number', 'invoiced_at']);
        });

        Schema::dropIfExists('document_sequences');
    }
};
