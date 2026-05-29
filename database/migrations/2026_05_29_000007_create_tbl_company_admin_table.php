<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_company_admin', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('role_id');
            $table->string('name', 100);
            $table->string('email', 150);
            $table->string('mobile_no', 20);
            $table->string('password', 255);
            $table->boolean('is_owner')->default(false);
            $table->dateTime('email_verified_at')->nullable();
            $table->dateTime('mobile_verified_at')->nullable();
            $table->dateTime('last_login_at')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('tbl_company')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('tbl_company_role');
            $table->unique(['company_id', 'email'], 'uk_company_email');
            $table->index('company_id', 'idx_company');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_company_admin');
    }
};
