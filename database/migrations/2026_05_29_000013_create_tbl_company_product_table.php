<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_company_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->enum('type', ['simple', 'variable'])->default('simple');
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->string('sku', 100)->nullable();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();

            // Pricing
            $table->decimal('base_price', 12, 2)->default(0);
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->decimal('sale_price', 12, 2)->nullable();

            // Stock (simple products only — for variable products, stock lives on tbl_product_variation)
            $table->boolean('manage_stock')->default(true);
            $table->integer('stock_quantity')->default(0);
            $table->enum('stock_status', ['in_stock', 'out_of_stock', 'on_backorder'])->default('in_stock');

            // Physical
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();

            // Tax
            $table->enum('tax_status', ['taxable', 'exempt'])->default('taxable');
            $table->string('tax_class', 50)->nullable();

            // Display
            $table->string('featured_image', 500)->nullable();
            $table->boolean('is_featured')->default(false);

            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('tbl_company')->onDelete('cascade');
            $table->unique(['company_id', 'slug'], 'uk_company_slug');
            $table->index('company_id', 'idx_company');
            $table->index('sku', 'idx_sku');
            $table->index('status', 'idx_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_company_product');
    }
};
