<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_company_attribute_term', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attribute_id');
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->string('color_code', 7)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            $table->foreign('attribute_id')->references('id')->on('tbl_company_attribute')->onDelete('cascade');
            $table->index('attribute_id', 'idx_attribute');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_company_attribute_term');
    }
};
