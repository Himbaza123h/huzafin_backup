<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\SystemSettings;
use Fpdf\Fpdf;
use Illuminate\Support\Facades\Storage;
use GenerateP;
use Illuminate\Http\Request;
use TCPDF;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTime;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GeneratePdf extends Fpdf
{
    public static function get(Request $request, string $image): string
    {
        // $html_data = self::generate_html($request);

        // // TCPDF Usage
        // $pdf = new TCPDF();
        // $pdf->AddPage();
        // $filename = uniqid() . '.pdf';
        // $filePath = 'public/invoices/' . $filename;
        // $pdf->writeHTML($html_data, true, false, true, false, '');
        // Storage::put($filePath, $pdf->Output('', 'I'));

        // //MPDF USAGE
        // $mpdf = new \Mpdf\Mpdf();
        // $mpdf->WriteHTML($html_data);
        // $filename = uniqid() . '.pdf';
        // $filePath = 'public/invoices/' . $filename;
        // Storage::put($filePath, $mpdf->Output('', 'S'));
        // return 'storage/invoices/' . $filename;
        $pdf = new GeneratePdf();
        $pdf->AddPage();
        $pdf->AddInvoiceHeader($request, $image);
        $pdf->AddSenderRecipientInfo($request);
        $pdf->AddInvoiceItems($request);
        $pdf->AddSummaryAndFooter($request);
        $pdf->AddNotesAndTerms($request);
        $filename = uniqid() . '.pdf';
        $filePath = 'public/invoices/' . $filename;
        Storage::put($filePath, $pdf->Output('', 'S'));
        return '/storage/invoices/' . $filename;
    }

    protected static function generate_html($request): string
    {
        $payload = $request->all();
        $htmlContent = view('invoice', ['payload' => $payload])->render();
        return $htmlContent;
    }
    public function Header()
    {
        // Header content goes here
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Invoice', 0, 1, 'C');
    }

    public function Footer()
    {
        // Footer content goes here
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    public function AddInvoiceHeader(Request $request, string $image)
    {
        // Invoice header content goes here
        $this->Ln(10);
        $imageURL = (substr($image, 0, 1) === '/') ? substr($image, 1) : $image;

        $this->Image($imageURL, 10, 27, 30);
        $this->Cell(0, 10, "Invoice {$request->invoice_number}", 0, 1, 'R');
        $this->Cell(0, 10, $request->date, 0, 1, 'R');
    }

    public function AddSenderRecipientInfo(Request $request)
    {
        // Sender and recipient information content goes here
        $this->Ln(10);
        $this->Cell(0, 10, 'From:', 0, 1, 'L');
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, $request->sender, 0, 1, 'L');
        $this->Ln();
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'To:', 0, 1, 'L');
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, $request->recipient, 0, 1, 'L');
    }

    public function AddInvoiceItems(Request $request)
    {
        // Invoice items content goes here
        $this->SetFont('Arial', '', 12);
        $this->Ln(10);
        $this->Cell(115, 10, 'Item Name', 1);
        $this->Cell(20, 10, 'Quantity', 1);
        $this->Cell(20, 10, 'UOM', 1);
        $this->Cell(20, 10, 'Rate', 1);
        $this->Cell(20, 10, 'Amount', 1);
        foreach ($request->items as $item) {
            $this->Ln();
            $this->Cell(115, 10, $item['name'], 1);
            $this->Cell(20, 10, $item['quantity'], 1);
            $this->Cell(20, 10, $item['uom'], 1);
            $this->Cell(20, 10, $item['rate'], 1);
            $this->Cell(20, 10, $item['amount'], 1);
        }
    }

    public function AddSummaryAndFooter(Request $request)
    {
        // Summary and footer content goes here
        $this->SetFont('Arial', 'B', 12);
        $this->Ln(10);
        $this->Cell(0, 10, "Subtotal: $request->subtotal", 0, 1, 'R');
        $this->Cell(0, 10, "Tax (18%): $request->tax", 0, 1, 'R');
        $this->Cell(0, 10, "Discount: $request->discount", 0, 1, 'R');
        $this->Cell(0, 10, "Total: $request->total", 0, 1, 'R');
        $this->Cell(0, 10, "Amount Paid: $request->amount_paid", 0, 1, 'R');
        $this->Cell(0, 10, "Balance Due: $request->balance_due", 0, 1, 'R');
    }

    public function AddNotesAndTerms(Request $request)
    {
        // Notes and terms content goes here
        $this->Ln(10);
        $this->Cell(0, 10, 'Notes:', 0, 1, 'L');
        $this->SetFont('Arial', '', 12);
        $this->MultiCell(0, 10, $request->notes, 0, 'L');
        $this->Ln(10);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Terms:', 0, 1, 'L');
        $this->SetFont('Arial', '', 12);
        $this->MultiCell(0, 10, $request->notes, 0, 'L');
    }
    public static function GetEBMReceipt(Invoice $invoice, array $invoice_data, SystemSettings $settings): string
    {
        $qrCodePath = public_path('qrcode.png');
        $date = new DateTime($invoice->vsdc_receipt_pbct_date);
        $formatted_datetime = $date->format('dmYHis');
        $date = substr($formatted_datetime, 0, 8);
        $time = substr($formatted_datetime, 8, 6);
        $qrCodeData = $date . "#" . $time . "#" . $invoice->sdc_id . "#" . $invoice->receipt_number . "#" . $invoice->internal_data . "#" . $invoice->receipt_sign;
        QrCode::format('png')->size(100)->generate($qrCodeData, $qrCodePath);

        $pdfFilePath = public_path('ebm-receipts/receipt_' . $invoice->id . '.pdf');
        if (!file_exists(public_path('ebm-receipts'))) {
            mkdir(public_path('ebm-receipts'), 0777, true);
        }
        $pdf = PDF::loadView('pdf.receipt', ['qrCodePath' => $qrCodePath, 'invoice' => $invoice, 'invoice_data' => $invoice_data, "settings" => $settings]);
        $pdf->save($pdfFilePath);
        $pdfUrl = url('ebm-receipts/receipt_' . $invoice->id . '.pdf');
        return $pdfUrl;
    }
}
