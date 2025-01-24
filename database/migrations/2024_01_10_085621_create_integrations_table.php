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
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->unsignedBigInteger('source_endpoint');
            $table->unsignedBigInteger('destination_endpoint');
            $table->foreign('source_endpoint')->references('id')->on('company_endpoints');
            $table->foreign('destination_endpoint')->references('id')->on('company_endpoints');
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
        Schema::dropIfExists('integrations');
    }
};
