<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_company_partner', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('upline_id')->nullable();
            $table->enum('partner_type', ['individual', 'company']);
            $table->string('name', 150);
            $table->string('email', 150);
            $table->string('mobile_no', 20);
            $table->string('password', 255);
            $table->string('referral_code', 20)->nullable()->unique();

            // Individual fields
            $table->string('ic_number', 20)->nullable();

            // Company fields
            $table->string('company_name', 150)->nullable();
            $table->string('registration_no', 50)->nullable();
            $table->string('tin', 30)->nullable();
            $table->string('sst_no', 30)->nullable();

            // Verification
            $table->dateTime('email_verified_at')->nullable();
            $table->dateTime('mobile_verified_at')->nullable();
            $table->dateTime('document_verified_at')->nullable();
            $table->string('country', 50)->default('Malaysia');

            // Status & tracking
            $table->enum('status', ['pending', 'active', 'suspended', 'blacklisted'])->default('pending');
            $table->dateTime('last_login_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('tbl_company')->onDelete('cascade');
            $table->foreign('upline_id')->references('id')->on('tbl_company_partner')->onDelete('set null');
            $table->unique(['company_id', 'email'], 'uk_company_email');
            $table->index('company_id', 'idx_company');
            $table->index('referral_code', 'idx_referral');
            $table->index('upline_id', 'idx_upline');
            $table->index('status', 'idx_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_company_partner');
    }
};
