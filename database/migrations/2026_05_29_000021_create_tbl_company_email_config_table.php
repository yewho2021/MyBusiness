<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_company_email_config', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('name', 100);
            $table->string('host', 255);
            $table->unsignedSmallInteger('port')->default(587);
            $table->string('username', 255);
            $table->string('password', 255);
            $table->enum('encryption', ['tls', 'ssl', 'none'])->default('tls');
            $table->string('from_name', 100);
            $table->string('from_email', 150);
            $table->string('reply_to', 150)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            $table->foreign('company_id')->references('id')->on('tbl_company')->onDelete('cascade');
            $table->index('company_id', 'idx_company');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_company_email_config');
    }
};
