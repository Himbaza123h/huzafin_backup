<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('report_name');
            $table->enum('report_type', ['entry','sale', 'purchase', 'refund']);
            $table->uuid('product_id')->nullable();
            $table->uuid('purchase_id')->nullable();
            $table->uuid('sale_id')->nullable();
            $table->uuid('refund_id')->nullable();
            $table->bigInteger('user_id');
            $table->json('additional_notes')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('set null');
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('set null');
            $table->foreign('refund_id')->references('id')->on('refunds')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reports');
    }
}
