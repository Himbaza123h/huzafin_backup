<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class StoreInvoiceRequest extends FormRequest
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
            'logo' => 'nullable|image|max:2048',
            'invoice_number' => 'nullable|integer|unique:invoices',
            'original_invoice_number' => 'nullable|integer',
            'customer_tin' => 'string',
            'purchase_code' => 'integer',
            'sender' => 'required|string|max:255',
            'recipient' => 'required|string|max:255',
            'recipient_phone_number' => 'required|string|max:13',
            'sales_type_code' => 'required|string',
            'purchase_code' => 'nullable|string',
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
            'subtotal' => 'required|numeric',
            'total' => 'required|numeric',
            'tax' => 'required|numeric',
            'taxable_amount' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'amount_paid' => 'nullable|numeric',
            'balance_due' => 'nullable|numeric',
            'file_path' => 'nullable|string',
            'registrant_id' => 'required|string',
            'registrant_name' => 'required|string',
            'modifier_name' => 'required|string',
            'modifier_id' => 'required|string',
            'report_number' => 'nullable|string',
            'print_size' => 'nullable|string',
            'items' => 'required|array',
            'items.*.name' => 'required|string|max:255',
            'items.*.item_classification_code' => 'required|string',
            'items.*.packaging_unit_code' => 'required|string',
            'items.*.package' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.uom' => 'required|string|max:10',
            'items.*.rate' => 'required|numeric',
            'items.*.amount' => 'required|numeric',
            'items.*.tax_type' => 'required|string|max:15',
            'items.*.tax_rate' => 'required|numeric',
            'items.*.taxable_amount' => 'required|numeric',
            'items.*.tax_amount' => 'required|numeric',
            'items.*.discount_rate' => 'nullable|numeric',
            'items.*.discount_amount' => 'nullable|numeric',
        ];
    }
    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();

        throw new HttpResponseException(
            response()->error($errors, 'Invalid inputs', Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
