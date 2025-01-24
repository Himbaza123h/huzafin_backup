<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Models\SystemSettings;
use App\Services\EBMGenerationService;
use App\Services\UploadService;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTime;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = Invoice::with('items')->latest()->get();
        return response()->success($invoices, '');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvoiceRequest $request)
    {
        $data = UploadService::upload($request, "images/logos", 'logo');
        // $filePath = GeneratePdf::get($request, $data['logo']);
        // $data = array_merge($data, ["file_path" => $filePath]);
        $invoice = Invoice::create($data);
        $invoice->createItems($data['items']);
        $request["invoice_number"] = $invoice->id;
        $filePath = EBMGenerationService::do($request);
        if(!empty($data['print_size']) && $data['print_size'] === "A4"){
            $filePath = "{$_ENV['APP_URL']}/receipt/$invoice->id";
        }
        $invoice->update(["file_path" => $filePath]);
        return response()->success(["file_path" => $filePath], 'Invoice created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        return response()->success($invoice->load('items'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvoiceRequest $request, $id)
    {
        $invoice = Invoice::find($id);
        $data = $request->validated();
        $invoice->update($data);
        if ($data['items']) {
            $invoice->createItems($data['items']);
        }
        return \response()->success($data, 'Invoice Updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->items()->delete();
        $invoice->delete();
        return response()->success("", "Invoice deleted!");
    }


    //EBM Trial message
    public function generateReceiptPDF()
    {
        // Generate QR code and save it as a PNG file
        $qrCodePath = public_path('qrcode.png');
        QrCode::format('png')->size(100)->generate('QR Code Data', $qrCodePath);

        // Load the view and pass the QR code path to the view
        $pdf = PDF::loadView('pdf.receipt', ['qrCodePath' => $qrCodePath]);

        // Return the PDF as a download
        return $pdf->download('receipt.pdf');
    }

    public function showReceiptPage($invoiceId)
    {
        $invoice = Invoice::with('items')->find($invoiceId); // Eager load items
        $settings = SystemSettings::first();

        // Initialize totals
        $totalTaxAmt = 0;
        $taxableAmounts = [
            'A' => 0, // Total taxable amount for tax type 'A'
            'B' => 0, // Total taxable amount for tax type 'B'
            'C' => 0, // Total taxable amount for tax type 'C'
            'D' => 0  // Total taxable amount for tax type 'D'
        ];

        // Loop through items and calculate totals
        foreach ($invoice->items as $item) {
            $totalTaxAmt += $item->tax_amount; // Add tax amounts
            if (isset($taxableAmounts[$item->tax_type])) {
                $taxableAmounts[$item->tax_type] += $item->tax_amount; // Add taxable amounts by type
            }
        }

        // Prepare invoice data for the view
        $invoice_data = [
            'taxblAmtA' => $taxableAmounts['A'],
            'taxblAmtB' => $taxableAmounts['B'],
            'taxblAmtC' => $taxableAmounts['C'],
            'taxblAmtD' => $taxableAmounts['D'],
            'totTaxAmt' => $totalTaxAmt
        ];

        // QR Code generation
        $qrCodePath = public_path('qrcode.png');
        $date = new DateTime($invoice->vsdc_receipt_pbct_date);
        $formatted_datetime = $date->format('dmYHis');
        $date = substr($formatted_datetime, 0, 8);
        $time = substr($formatted_datetime, 8, 6);
        $qrCodeData = $date . "#" . $time . "#" . $invoice->sdc_id . "#" . $invoice->receipt_number . "#" . $invoice->internal_data . "#" . $invoice->receipt_sign;
        QrCode::format('png')->size(100)->generate($qrCodeData, $qrCodePath);

        // Render the HTML page for the receipt
        return view('pdf.receipt_ii', [
            'qrCodePath' => $qrCodePath,
            'invoice' => $invoice,
            'invoice_data' => $invoice_data,
            'settings' => $settings
        ]);
    }

}
