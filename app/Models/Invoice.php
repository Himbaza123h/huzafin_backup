<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    // Fillable fields based on the invoices table
    protected $fillable = [
        'logo',
        'invoice_number',
        'original_invoice_number',
        'customer_tin',
        'purchase_code',
        'sender',
        'recipient',
        'recipient_phone_number',
        'sales_type_code',
        'receipt_type_code',
        'payment_type_code',
        'invoice_status_code',
        'validated_date',
        'cancel_requested_date',
        'cancel_date',
        'refund_date',
        'refunded_reason_code',
        'date',
        'due_date',
        'notes',
        'terms',
        'subtotal',
        'total',
        'tax',
        'taxable_amount',
        'discount',
        'amount_paid',
        'balance_due',
        'file_path',
        'registrant_id',
        'registrant_name',
        'modifier_name',
        'modifier_id',
        'report_number',
        'result_code',
        'result_message',
        'result_date_time',
        'receipt_number',
        'receipt_sign',
        'tot_receipt_number',
        'vsdc_receipt_pbct_date',
        'sdc_id',
        'mrc_number',
        'internal_data'
    ];

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    // Method to create or update items related to the invoice
    public function createItems(array $itemsData)
    {
        // Delete existing items for the invoice
        $this->items()->delete();

        // Loop through the new items data and create new items
        foreach ($itemsData as $item) {
            $this->items()->create([
                'name' => $item['name'],
                'item_classification_code' => $item['item_classification_code'],
                'packaging_unit_code' => $item['packaging_unit_code'],
                'package' => $item['package'],
                'quantity' => $item['quantity'],
                'uom' => $item['uom'],
                'rate' => $item['rate'],
                'amount' => $item['amount'],
                'tax_type' => $item['tax_type'],
                'taxable_amount' => $item['taxable_amount'],
                'tax_rate' => $item['tax_rate'],
                'tax_amount' => $item['tax_amount'],
                'discount_rate' => $item['discount_rate'],
                'discount_amount' => $item['discount_amount'],
                'external_id' => $item['external_id'] ?? null,
            ]);
        }
    }
}
