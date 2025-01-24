<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('company_endpoints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_app_id');
            $table->foreign('company_app_id')->references('id')->on('company_apps');
            $table->string('endpoint');
            $table->enum('status', ['Active', 'Inactive']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_endpoints');
    }
};
