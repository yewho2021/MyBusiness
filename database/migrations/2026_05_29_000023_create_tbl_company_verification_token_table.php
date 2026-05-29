<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_company_verification_token', function (Blueprint $table) {
            $table->id();
            $table->string('tokenable_type', 100);
            $table->unsignedBigInteger('tokenable_id');
            $table->enum('type', ['email', 'mobile', 'password_reset']);
            $table->string('code_hash', 255);
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->dateTime('expires_at');
            $table->dateTime('resend_available_at')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tokenable_type', 'tokenable_id'], 'idx_tokenable');
            $table->index('type', 'idx_type');
            $table->index('expires_at', 'idx_expires');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_company_verification_token');
    }
};
