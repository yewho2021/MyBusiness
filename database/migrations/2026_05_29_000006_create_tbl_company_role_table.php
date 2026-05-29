<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_company_role', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name', 50);
            $table->string('slug', 50);
            $table->json('permissions')->nullable();
            $table->boolean('is_owner')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            $table->foreign('company_id')->references('id')->on('tbl_company')->onDelete('cascade');
            $table->unique(['company_id', 'slug'], 'uk_company_slug');
            $table->index('company_id', 'idx_company');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_company_role');
    }
};
