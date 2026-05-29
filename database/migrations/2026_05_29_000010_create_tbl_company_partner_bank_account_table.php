<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_company_partner_bank_account', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('partner_id');
            $table->unsignedInteger('bank_id');
            $table->string('account_name', 150);
            $table->string('account_number', 50);
            $table->boolean('is_primary')->default(false);
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            $table->foreign('company_id')->references('id')->on('tbl_company')->onDelete('cascade');
            $table->foreign('partner_id')->references('id')->on('tbl_company_partner')->onDelete('cascade');
            $table->foreign('bank_id')->references('id')->on('tbl_ref_bank');
            $table->index('company_id', 'idx_company');
            $table->index('partner_id', 'idx_partner');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_company_partner_bank_account');
    }
};
