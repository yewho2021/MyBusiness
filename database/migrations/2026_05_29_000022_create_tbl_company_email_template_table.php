<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_company_email_template', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedInteger('smtp_id')->nullable();
            $table->string('slug', 100);
            $table->string('name', 255);
            $table->string('subject', 255);
            $table->text('content');
            $table->string('email_to', 255)->nullable();
            $table->string('email_cc', 255)->nullable();
            $table->string('email_bcc', 255)->nullable();
            $table->json('variables')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            $table->foreign('company_id')->references('id')->on('tbl_company')->onDelete('cascade');
            $table->foreign('smtp_id')->references('id')->on('tbl_company_email_config')->onDelete('set null');
            $table->unique(['company_id', 'slug'], 'uk_company_slug');
            $table->index('company_id', 'idx_company');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_company_email_template');
    }
};
