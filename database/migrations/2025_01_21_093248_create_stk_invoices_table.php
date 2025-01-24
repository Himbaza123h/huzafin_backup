<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stk_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('invoice_number')->unique();
            $table->enum('type', ['purchase', 'sale']);
            $table->uuid('user_id'); // who created the invoice
            $table->uuid('entity_id')->nullable(); // customer_id or supplier_id
            $table->uuid('purchase_id')->nullable();
            $table->uuid('sale_id')->nullable();
            $table->integer('download_count')->default(0);
            $table->enum('status', ['draft', 'final', 'void'])->default('draft');
            $table->decimal('total_amount', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('purchase_id')->references('id')->on('purchases');
            $table->foreign('sale_id')->references('id')->on('sales');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stk_invoices');
    }
};
