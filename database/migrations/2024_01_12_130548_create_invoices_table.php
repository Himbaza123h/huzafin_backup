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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key
            $table->string('logo')->nullable();
            $table->bigInteger('invoice_number')->unique()->nullable(); // Unique instead of auto-increment
            $table->bigInteger('original_invoice_number')->nullable(); // Nullable field
            $table->string('customer_tin');
            $table->integer('purchase_code')->default(0);
            $table->string('sender');
            $table->string('recipient');
            $table->string('recipient_phone_number');
            $table->string('sales_type_code');
            $table->string('receipt_type_code');
            $table->string('payment_type_code');
            $table->string('invoice_status_code');
            $table->dateTime('validated_date');
            $table->dateTime('cancel_requested_date')->nullable();
            $table->dateTime('cancel_date')->nullable();
            $table->dateTime('refund_date')->nullable();
            $table->string('refunded_reason_code')->nullable();
            $table->datetime('date');
            $table->date('due_date');
            $table->string('notes')->nullable();
            $table->longText('terms')->nullable();
            $table->float('subtotal', 15);
            $table->float('total', 15);
            $table->float('tax', 15);
            $table->float('taxable_amount', 15);
            $table->float('discount', 15);
            $table->float('amount_paid', 15);
            $table->float('balance_due', 15);
            $table->string('file_path')->nullable();
            $table->string('registrant_id');
            $table->string('registrant_name');
            $table->string('modifier_name');
            $table->string('modifier_id');
            $table->string('report_number');
            $table->string('result_code')->nullable();
            $table->string('result_message')->nullable();
            $table->datetime('result_date_time')->nullable();
            $table->string('receipt_number')->nullable();
            $table->string('internal_data')->nullable();
            $table->string('receipt_sign')->nullable();
            $table->string('tot_receipt_number')->nullable();
            $table->datetime('vsdc_receipt_pbct_date')->nullable();
            $table->string('sdc_id')->nullable();
            $table->string('mrc_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
