<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
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
            'logo' => 'sometimes|image|max:2048',
            'invoice_number' => 'nullable|integer|unique:invoices,invoice_number,' . $this->route('invoice'),
            'original_invoice_number' => 'sometimes|required|integer',
            'customer_tin' => 'sometimes|required|string',
            'purchase_code' => 'sometimes|required|string',
            'sender' => 'sometimes|required|string|max:255',
            'recipient' => 'sometimes|required|string|max:255',
            'recipient_phone_number' => 'required|string|max:13',
            'sales_type_code' => 'sometimes|required|string',
            'purchase_code' => 'nullable|string',
            'receipt_type_code' => 'sometimes|required|string',
            'payment_type_code' => 'sometimes|required|string',
            'invoice_status_code' => 'sometimes|required|string',
            'validated_date' => 'sometimes|required|date',
            'cancel_requested_date' => 'nullable|date',
            'cancel_date' => 'nullable|date',
            'refund_date' => 'nullable|date',
            'refunded_reason_code' => 'nullable|string',
            'date' => 'sometimes|required|date',
            'due_date' => 'sometimes|required|date|after:date',
            'notes' => 'sometimes|nullable|string',
            'terms' => 'sometimes|nullable|string',
            'subtotal' => 'sometimes|required|numeric',
            'total' => 'sometimes|required|numeric',
            'tax' => 'sometimes|required|numeric',
            'taxable_amount' => 'sometimes|required|numeric',
            'discount' => 'sometimes|required|numeric',
            'amount_paid' => 'sometimes|required|numeric',
            'balance_due' => 'sometimes|required|numeric',
            'file_path' => 'nullable|string',
            'registrant_id' => 'sometimes|required|string',
            'registrant_name' => 'sometimes|required|string',
            'modifier_name' => 'sometimes|required|string',
            'modifier_id' => 'sometimes|required|string',
            'report_number' => 'nullable|string',
            'print_size' => 'nullable|string',
            'items' => 'sometimes|required|array',
            'items.*.name' => 'sometimes|required|string|max:255',
            'items.*.item_classification_code' => 'sometimes|required|string',
            'items.*.packaging_unit_code' => 'sometimes|required|string',
            'items.*.package' => 'nullable|string',
            'items.*.quantity' => 'sometimes|required|numeric|min:1',
            'items.*.uom' => 'sometimes|required|string|max:10',
            'items.*.rate' => 'sometimes|required|numeric',
            'items.*.amount' => 'sometimes|required|numeric',
            'items.*.tax_type' => 'sometimes|required|string|max:15',
            'items.*.tax_rate' => 'sometimes|required|numeric',
            'items.*.taxable_amount' => 'sometimes|required|numeric',
            'items.*.tax_amount' => 'sometimes|required|numeric',
            'items.*.discount_rate' => 'nullable|numeric',
            'items.*.discount_amount' => 'nullable|numeric',
            'items.*.external_id' => 'nullable|string',
        ];
    }
}
