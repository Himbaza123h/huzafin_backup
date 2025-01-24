<?php

namespace App\Services;

use App\Enums\ActionEnum;
use Illuminate\Http\Request;
use App\Models\Invoice as ModelsInvoice;
use App\Services\ConvertToDate;
use App\Services\GeneratePdf;
use App\Services\UploadService;
use Illuminate\Support\Facades\Log;
use XeroApi\XeroPHP\Models\Accounting\Invoice;
use App\Http\Requests\InvoiceHookRequest as StoreInvoiceRequest;
use Illuminate\Support\Facades\Validator;

class XeroWebHookService
{
   public function handle(Request $request, Invoice $invoice, string $action)
   {
      Log::channel('xerowebhooks')->info('Xero webhook data contact name: ' . json_encode($invoice->getLineItems()));
      Log::channel('xerowebhooks')->info('Xero webhook action -->' . $action);
      try {
         $xeroService = new XeroService();
         $invoiceExists = ModelsInvoice::firstWhere('invoice_number', $invoice->getInvoiceNumber());
         if ($invoiceExists)
            $newInvoice = $invoiceExists;
         else
            $newInvoice = new ModelsInvoice();
         $items = $this->getLineItems($invoice, $xeroService);
         $company = $xeroService->getCompanyDetail();
         $newInvoice->invoice_number = $invoice->getInvoiceNumber();
         $newInvoice->original_invoice_number = $invoice->getInvoiceNumber();
         $newInvoice->recipient = $invoice->getContact()->getName();
         $newInvoice->customer_tin = $invoice->getContact()->getTaxNumber() ?? $invoice->getContact()->getPhones()[0]->getPhoneNumber();
         $newInvoice->purchase_code = "PC-2024";
         $newInvoice->sender = $company['name'];
         $newInvoice->image_url = $company['logo'];
         $newInvoice->sales_type_code = "N";
         $newInvoice->receipt_type_code = "S";
         $newInvoice->payment_type_code = "01";
         $newInvoice->invoice_status_code = "02";
         $newInvoice->validated_date = ConvertToDate::generate($invoice->getDate());
         $newInvoice->date = ConvertToDate::generate($invoice->getDate());
         $newInvoice->due_date = ConvertToDate::generate($invoice->getDueDate());
         $newInvoice->subtotal = floatval($invoice->getSubTotal());
         $newInvoice->total = floatval($invoice->getTotal());
         $newInvoice->tax = floatval($invoice->getTotalTax());
         $newInvoice->taxable_amount = floatval($invoice->getTotal());
         $newInvoice->discount = floatval($invoice->getTotalDiscount());
         $newInvoice->amount_paid = floatval($invoice->getAmountPaid());
         $newInvoice->balance_due = floatval($invoice->getAmountDue());
         $newInvoice->registrant_id = "11999";
         $newInvoice->registrant_name = "TestVSDC";
         $newInvoice->modifier_name = "TestModifier";
         $newInvoice->modifier_id = "45678";
         $newInvoice->report_number = "RP3903";
         $newInvoice->items = $items;
         $request->merge($newInvoice->toArray());
         $newInvoice->logo = UploadService::upload($request, "images/logos");
         Log::channel('xerowebhooks')->info("Invoice:", $newInvoice->toArray());
         $storeInvoiceRequest = new StoreInvoiceRequest();
        $validator = Validator::make($request->all(), $storeInvoiceRequest->rules());
        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
        $validatedData = $validator->validated();
        $storeInvoiceRequest = StoreInvoiceRequest::createFromBase($request);
        $storeInvoiceRequest->replace($validatedData);
         unset($newInvoice->image_url);
         unset($newInvoice->items);
         if ($invoiceExists) {
            $newInvoice->update();
            $newInvoice->createItems($items);
         } else {
            $newInvoice->save();
            $newInvoice->createItems($items);
         }
         $filePath = EBMGenerationService::do($storeInvoiceRequest);
         $newInvoice->update(["file_path" => $filePath]);
      } catch (\Throwable $exception) {
         $errorMessage = $exception->getMessage();
         $stackTrace = $exception->getTraceAsString();

         Log::channel('xerowebhooks')->error("Error creating invoice: $errorMessage\n$stackTrace");
      }
   }
   protected function getLineItems(Invoice $invoice, $xeroService): array
   {
      $items = array();
      foreach ($invoice->getLineItems() as $item) {
         array_push($items, [
            "name" => $item->getItem()->getName(),
            "item_classification_code" => $item->getItem()->getCode(),
            "packaging_unit_code" => "CT",
            "package" => "1",
            "quantity" => $item->getQuantity(),
            "uom" => "BG",
            "rate" => $item->getUnitAmount(),
            "amount" => $item->getLineAmount(),
            "tax_type" => $xeroService->getTaxName($item->getTaxType()),
            "tax_rate" => $xeroService->getTaxRate($item->getTaxType()),
            "taxable_amount" => $item->getLineAmount(),
            "tax_amount" => $item->getTaxAmount(),
            "discount_rate" => $item->getDiscountRate() ?? 0,
            "discount_amount" => $item->getDiscountAmount() ?? 0,
            "external_id" => $item->getLineItemId()
         ]);
      }
      Log::channel('xerowebhooks')->info("Items --->". json_encode($items));
      return $items;
   }
}
