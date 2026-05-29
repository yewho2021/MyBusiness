<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_company_agreement', function (Blueprint $table) {
            $table->id();
            $table->string('version', 20);
            $table->string('title', 255)->default('Terms & Conditions');
            $table->longText('content');
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->index('is_active', 'idx_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_company_agreement');
    }
};
