<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_company_stock_movement', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('variation_id')->nullable();
            $table->enum('type', ['receipt', 'sale', 'adjustment', 'return', 'transfer']);
            $table->integer('quantity');
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('company_id')->references('id')->on('tbl_company')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('tbl_company_product')->onDelete('set null');
            $table->foreign('variation_id')->references('id')->on('tbl_company_product_variation')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('tbl_company_admin')->onDelete('set null');
            $table->index('company_id', 'idx_company');
            $table->index('product_id', 'idx_product');
            $table->index('type', 'idx_type');
            $table->index('created_at', 'idx_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_company_stock_movement');
    }
};
