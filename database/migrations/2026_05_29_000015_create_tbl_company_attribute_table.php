<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_company_attribute', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->enum('type', ['select', 'button', 'color'])->default('select');
            $table->integer('sort_order')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            $table->foreign('company_id')->references('id')->on('tbl_company')->onDelete('cascade');
            $table->unique(['company_id', 'slug'], 'uk_company_slug');
            $table->index('company_id', 'idx_company');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_company_attribute');
    }
};
