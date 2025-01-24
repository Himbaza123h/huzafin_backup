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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->foreign('invoice_id')->references('id')->on('invoices');
            $table->string('name');
            $table->string('item_classification_code');
            $table->string('packaging_unit_code');
            $table->string('package');
            $table->float('quantity');
            $table->string('uom', 10);
            $table->float('rate', 15);
            $table->float('amount', 15);
            $table->string('tax_type', 15);
            $table->string('tax_rate', 15);
            $table->float('taxable_amount', 15);
            $table->float('tax_amount', 15);
            $table->float('discount_rate', 15);
            $table->float('discount_amount', 15);
            $table->string('external_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
