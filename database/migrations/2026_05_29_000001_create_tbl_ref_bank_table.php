<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_ref_bank', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('swift_code', 20)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->index('status', 'idx_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_ref_bank');
    }
};
