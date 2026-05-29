<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_company_notification_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('recipient_type', 100);
            $table->unsignedBigInteger('recipient_id');
            $table->enum('channel', ['email', 'sms', 'push']);
            $table->unsignedInteger('template_id')->nullable();
            $table->string('subject', 255);
            $table->text('content');
            $table->string('email_to', 255)->nullable();
            $table->string('email_cc', 255)->nullable();
            $table->enum('status', ['sent', 'failed', 'queued'])->default('queued');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('company_id')->references('id')->on('tbl_company')->onDelete('cascade');
            $table->foreign('template_id')->references('id')->on('tbl_company_email_template')->onDelete('set null');
            $table->index('company_id', 'idx_company');
            $table->index(['recipient_type', 'recipient_id'], 'idx_recipient');
            $table->index('channel', 'idx_channel');
            $table->index('status', 'idx_status');
            $table->index('sent_at', 'idx_sent');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_company_notification_log');
    }
};
