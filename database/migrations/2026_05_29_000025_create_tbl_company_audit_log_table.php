<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_company_audit_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('actor_type', 100);
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action', 50);
            $table->string('subject_type', 100)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('company_id')->references('id')->on('tbl_company')->onDelete('cascade');
            $table->index('company_id', 'idx_company');
            $table->index(['actor_type', 'actor_id'], 'idx_actor');
            $table->index(['subject_type', 'subject_id'], 'idx_subject');
            $table->index('action', 'idx_action');
            $table->index('created_at', 'idx_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_company_audit_log');
    }
};
