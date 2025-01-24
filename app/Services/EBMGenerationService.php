<?php

namespace App\Services;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\InvoiceHookRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Models\SystemSettings;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use DateTime;

class EBMGenerationService
{
    public static function do(StoreInvoiceRequest|InvoiceHookRequest|UpdateInvoiceRequest $request)
    {
        try {
            Log::channel('xerowebhooks')->info("Data got: " . print_r($request->toArray(), true));
            $data = $request->all();
            $settings = SystemSettings::first();

            // Prepare the tax breakdown for the whole invoice
            $taxes = [
               'A' => ['taxblAmt' => 0, 'taxAmt' => 0, 'taxRt' => 0],
               'B' => ['taxblAmt' => 0, 'taxAmt' => 0, 'taxRt' => 0],
               'C' => ['taxblAmt' => 0, 'taxAmt' => 0, 'taxRt' => 0],
               'D' => ['taxblAmt' => 0, 'taxAmt' => 0, 'taxRt' => 0],
            ];

            $totTaxblAmt = 0;
            $totTaxAmt = 0;

            // Process each item and calculate the taxes based on taxTyCd
            $itemList = array_map(function ($item, $index) use (&$taxes, &$totTaxblAmt, &$totTaxAmt) {
                $taxType = $item['tax_type'];
                $taxableAmount = $item['taxable_amount'];
                $taxRate = $item['tax_rate'];
                $taxAmount = $item['tax_amount'];

                // Assign values to the corresponding tax type
                if ($taxType === 'A') {
                    $taxes['A']['taxblAmt'] += (int)$taxableAmount;
                    $taxes['A']['taxAmt'] += (float)round($taxAmount, 2);
                    $taxes['A']['taxRt'] = (int)$taxRate;
                } elseif ($taxType === 'B') {
                    $taxes['B']['taxblAmt'] += (int)$taxableAmount;
                    $taxes['B']['taxAmt'] += (float)round($taxAmount, 2);
                    $taxes['B']['taxRt'] = (int)$taxRate;
                } elseif ($taxType === 'C') {
                    $taxes['C']['taxblAmt'] += (int)$taxableAmount;
                    $taxes['C']['taxAmt'] += (float)round($taxAmount, 2);
                    $taxes['C']['taxRt'] = (int)$taxRate;
                } elseif ($taxType === 'D') {
                    $taxes['D']['taxblAmt'] += (int)$taxableAmount;
                    $taxes['D']['taxAmt'] += (float)round($taxAmount, 2);
                    $taxes['D']['taxRt'] = (int)$taxRate;
                }

                $totTaxblAmt += $taxableAmount;
                $totTaxAmt += $taxAmount;

                return [
                   'itemSeq' => $index + 1,
                   'itemCd' => $item['item_classification_code'],
                   'itemClsCd' => $item['item_classification_code'],
                   'itemNm' => $item['name'],
                   'pkgUnitCd' => $item['packaging_unit_code'],
                   'pkg' => $item['package'],
                   'qtyUnitCd' => $item['uom'],
                   'qty' => (int)$item['quantity'],
                   'prc' => (int)$item['rate'],
                   'splyAmt' => (int)$item['amount'],
                   'dcRt' => (int)$item['discount_rate'] ?? 0,
                   'dcAmt' => (int)$item['discount_amount'] ?? 0,
                   'taxTyCd' => $taxType,
                   'taxblAmt' => (int)$taxableAmount,
                   'taxAmt' => (float)round($taxAmount, 2),
                   'totAmt' => (int)$item['amount'],
                ];
            }, $data['items'], array_keys($data['items']));

            // Prepare the invoice data with calculated tax values
            $invoice_data = [
               'tin' => $settings->tin,
               'bhfId' => $settings->branch_id,
               'invcNo' => (int)$data['invoice_number'],
               'orgInvcNo' => $data['receipt_type_code'] == "R" ? (int)$data["original_invoice_number"] : (int) 0,
               'custNm' => $data['recipient'],
               'custTin' => $data['customer_tin'] ?? substr($data["recipient_phone_number"], -9),
               'prcOrdCd' => $data['purchase_code'],
               'salesTyCd' => $data['sales_type_code'],
               'rcptTyCd' => $data['receipt_type_code'],
               'pmtTyCd' => $data['payment_type_code'],
               'salesSttsCd' => $data['invoice_status_code'],
               'cfmDt' => now()->format('YmdHis'),
               'salesDt' => now()->format('Ymd'),
               'stockRlsDt' => now()->format('YmdHis'),
               'cnclReqDt' => now()->format('YmdHis'),
               'totItemCnt' => (int)count($data['items']),
               'taxblAmtA' => (int)$taxes['A']['taxblAmt'],
               'taxblAmtB' => (int) $taxes['B']['taxblAmt'],
               'taxblAmtC' => (int)$taxes['C']['taxblAmt'],
               'taxblAmtD' => (int)$taxes['D']['taxblAmt'],
               'taxRtA' => (int)$taxes['A']['taxRt'],
               'taxRtB' => (int)$taxes['B']['taxRt'],
               'taxRtC' => (int)$taxes['C']['taxRt'],
               'taxRtD' => (int)$taxes['D']['taxRt'],
               'taxAmtA' => (float)round($taxes['A']['taxAmt'], 2),
               'taxAmtB' => (float)round($taxes['B']['taxAmt'], 2),
               'taxAmtC' => (float)round($taxes['C']['taxAmt'], 2),
               'taxAmtD' => (float)round($taxes['D']['taxAmt'], 2),
               'totTaxblAmt' => (int)$totTaxblAmt,
               'totTaxAmt' => (float)round($totTaxAmt, 2),
               'totAmt' => (int)$data['total'],
               'prchrAcptcYn' => 'N',
               'remark' => $data['notes'] ?? null,
               'regrId' => '11999',
               'regrNm' => 'TestVSDC',
               'modrId' => '45678',
               'modrNm' => 'TestVSDC',
               'receipt' => [
                  'rptNo' => 1,
                  'topMsg' => "{$data['sender']}\n$settings->address\nWELCOME",
                  'btmMsg' => "THANK YOU",
                  'prchrAcptcYn' => 'Y',
                  'trdeNm' => $data["sender"],
                  'adrs' => $settings->address,
               ],
               'itemList' => $itemList,
            ];

            if (!(isset($invoice_data['prcOrdCd']) && (int)$invoice_data['prcOrdCd'] > 0)) {
               unset($invoice_data['prcOrdCd']);
            }

            if ($data['receipt_type_code'] === "R") {
                $invoice_data["cnclReqDt"] = (new DateTime($data['cancel_requested_date']))->format('YmdHis');
                $invoice_data["cnclDt"] = (new DateTime($data['cancel_date']))->format('YmdHis');
                $invoice_data["rfdDt"] = (new DateTime($data['refund_date']))->format('YmdHis');
                $invoice_data["rfdRsnCd"] = $data['refunded_reason_code'];
            }
            $client = new Client();
            Log::info("Data sent to EBM: " . \json_encode($invoice_data));
            $response = $client->post('http://huzaccounts.com:8080/rraVsdcSandbox2.1.2.3.3_PC/trnsSales/saveSales', [
               'json' => $invoice_data,
               'headers' => [
                  'Content-Type' => 'application/json',
               ],
            ]);

            $response_data = \json_decode($response->getBody()->getContents(), true);
            Log::info("Result from EBM: " . \json_encode($response_data));

            // Check if API returned a success result
            if (isset($response_data['resultCd']) && $response_data['resultCd'] == '000') {
                // Use a DB transaction to ensure atomicity
                DB::beginTransaction();
                try {
                    // Find the invoice and update with the EBM response
                    $invoice = Invoice::Find($data['invoice_number']);

                    if ($invoice) {
                        $invoice->update([
                           'result_code' => $response_data['resultCd'],
                           'result_message' => $response_data['resultMsg'],
                           'result_date_time' => $response_data['resultDt'],
                           'receipt_number' => $response_data['data']['rcptNo'],
                           'receipt_sign' => $response_data['data']['rcptSign'],
                           'tot_receipt_number' => $response_data['data']['totRcptNo'],
                           'vsdc_receipt_pbct_date' => $response_data['data']['vsdcRcptPbctDate'],
                           'sdc_id' => $response_data['data']['sdcId'],
                           'mrc_number' => $settings->mrc,
                           'internal_data' => $response_data['data']['intrlData']
                        ]);
                        $invoice->refresh();
                        DB::commit();
                        $file_path = GeneratePdf::GetEBMReceipt($invoice, $invoice_data, $settings);
                        return $file_path;
                    } else {
                        DB::rollBack();
                        return 'Invoice not found for updating. --' . $data['invoice_number'];
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('EBM Invoice Update Error: ' . $e->getMessage());
                    return `Failed to update invoice after generating EBM. {$e->getMessage()}`;
                }
            }
            return 'Failed to generate receipt ==> '. json_encode($response_data);
        } catch (\Exception $e) {
            Log::error('EBM Generation Error: ' . $e->getMessage());
            return `Error occurred during EBM generation. Please try again later. ==>  {$e->getMessage()}`;
        }
    }
}
