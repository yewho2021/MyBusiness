<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_company_variation_attribute', function (Blueprint $table) {
            $table->unsignedBigInteger('variation_id');
            $table->unsignedBigInteger('attribute_id');
            $table->unsignedBigInteger('term_id');

            $table->primary(['variation_id', 'attribute_id']);
            $table->foreign('variation_id')->references('id')->on('tbl_company_product_variation')->onDelete('cascade');
            $table->foreign('attribute_id')->references('id')->on('tbl_company_attribute')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('tbl_company_attribute_term')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_company_variation_attribute');
    }
};
