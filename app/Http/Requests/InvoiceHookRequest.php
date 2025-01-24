<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceHookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'logo' => 'nullable|string',
            'invoice_number' => 'required|integer',
            'original_invoice_number' => 'required|integer',
            'customer_tin' => 'string',
            'purchase_code' => 'nullable|string',
            'sender' => 'required|string|max:255',
            'recipient' => 'required|string|max:255',
            'recipient_phone_number' => 'required|string|max:13',
            'sales_type_code' => 'required|string',
            'receipt_type_code' => 'required|string',
            'payment_type_code' => 'required|string',
            'invoice_status_code' => 'required|string',
            'validated_date' => 'required|date',
            'cancel_requested_date' => 'nullable|date',
            'cancel_date' => 'nullable|date',
            'refund_date' => 'nullable|date',
            'refunded_reason_code' => 'nullable|string',
            'date' => 'required|date',
            'due_date' => 'required|date|after:date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'subtotal' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'taxable_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'balance_due' => 'nullable|numeric|min:0',
            'file_path' => 'nullable|string',
            'registrant_id' => 'required|string',
            'registrant_name' => 'required|string',
            'modifier_name' => 'required|string',
            'modifier_id' => 'required|string',
            'report_number' => 'nullable|string',
            'items' => 'required|array',
            'items.*.name' => 'required|string|max:255',
            'items.*.item_classification_code' => 'required|string',
            'items.*.packaging_unit_code' => 'required|string',
            'items.*.package' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.uom' => 'required|string|max:10',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.tax_type' => 'required|string|max:15',
            'items.*.tax_rate' => 'required|numeric|min:0',
            'items.*.taxable_amount' => 'required|numeric|min:0',
            'items.*.tax_amount' => 'required|numeric|min:0',
            'items.*.discount_rate' => 'nullable|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
        ];
    }
}
