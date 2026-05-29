<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_ref_industry_subcategory', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('industry_id');
            $table->string('name', 255);
            $table->unsignedInteger('sort_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->foreign('industry_id')->references('id')->on('tbl_ref_industry')->onDelete('cascade');
            $table->index('industry_id', 'idx_industry');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_ref_industry_subcategory');
    }
};
