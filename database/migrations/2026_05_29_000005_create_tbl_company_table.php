<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_company', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('company_name', 150);
            $table->string('name', 100);
            $table->string('email', 150);
            $table->string('mobile_no', 20);
            $table->string('password', 255)->nullable();
            $table->json('company_info')->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->string('timezone', 50)->default('Asia/Kuala_Lumpur');
            $table->unsignedTinyInteger('setup_step')->default(1);
            $table->unsignedBigInteger('agreement_id')->nullable();
            $table->dateTime('agreement_accepted_at')->nullable();
            $table->dateTime('email_verified_at')->nullable();
            $table->dateTime('mobile_verified_at')->nullable();
            $table->enum('status', ['pending', 'active', 'suspended', 'inactive'])->default('pending');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->softDeletes();

            $table->foreign('agreement_id')->references('id')->on('tbl_company_agreement')->onDelete('set null');
            $table->index('code', 'idx_code');
            $table->index('email', 'idx_email');
            $table->index('status', 'idx_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_company');
    }
};
