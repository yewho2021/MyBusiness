<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_company_industry', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id');
            $table->unsignedInteger('subcategory_id');

            $table->primary(['company_id', 'subcategory_id']);
            $table->foreign('company_id')->references('id')->on('tbl_company')->onDelete('cascade');
            $table->foreign('subcategory_id')->references('id')->on('tbl_ref_industry_subcategory')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_company_industry');
    }
};
