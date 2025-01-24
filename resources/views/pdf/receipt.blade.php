<!DOCTYPE html>
<html>

<head>
   <meta charset="UTF-8">
   <style>
      @page {
         size: 110mm 297mm;
         margin: 10mm;
      }

      body {
         font-family: 'DejaVu Sans', sans-serif;
         font-size: 10px;
         margin: 0;
         padding: 0;
      }

      .center {
         text-align: center;
      }

      .right {
         text-align: right;
      }

      .line {
         border-bottom: 1px dashed black;
         margin: 5px 0;
      }

      table {
         width: 100%;
         border-collapse: collapse;
      }

      th,
      td {
         padding: 5px;
         text-align: left;
      }

      .totals {
         font-weight: bold;
      }

      .totals-value {
         text-align: right;
      }
      .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 0;
        }
        .logo, .stamp {
            height: 100px;
            width: 100px;
        }
        .logo {
            align-self: left;
        }
        .stamp {
            align-self: right;
        }
   </style>
</head>

<body>
   <!-- Store and Seller Information -->
   <table>
      <tr>
         <td>
            <img src="{{ public_path('images/rra-logo.png') }}" alt="RRA Logo" class="logo">
         </td>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td>
            <img src="{{ public_path('images/ebm-logo.png') }}" alt="EBM LOGO" class="stamp">
         </td>
      </tr>
   </table>
   <div class="center">
      <h2>{{ $invoice->sender }}</h2>
      <p>{{$settings->address}}</p>
      <p>TIN: {{$settings->tin}}</p>
      @if($invoice->sales_type_code == "C")
      <p><b>COPY</b></p>
      @elseif($invoice->sales_type_code == "P")
      <p><b>PROFORMA</b></p>
      @elseif($invoice->sales_type_code == "T")
      <p><b>TRAINING</b></p>
      @endif
      @if($invoice->receipt_type_code == "R")
      <div class="line"></div>
      <p><b>REFUND</b></p>
      <p>REF. NORMAL RECEIPT#: {{$invoice->original_invoice_number}}</p>
      @endif
      <div class="line"></div>
      <p>WELCOME</p>
      @if (!empty($invoice->recipient))
      <p>Client Name: {{ $invoice->recipient }}</p>
      @endif
      @if (!empty($invoice->customer_tin))
      <p>Client ID: {{ $invoice->customer_tin }}</p>
      @endif
      @if (!empty($invoice->recipient_phone_number))
      <p>Client Phone: {{ $invoice->recipient_phone_number }}</p>
      @endif

      <div class="line"></div>
   </div>

   <!-- Invoice Items -->
   <table>
      @foreach($invoice->items as $item)
      <tr>
         <td>{{ $item->name }}</td>
         <td class="totals-value">{{ number_format($item->rate, 2) }} x {{ number_format($item->quantity, 3) }}</td>
         <td class="totals-value">{{ number_format($item->amount, 2) }} {{ $item->tax_type }}</td>
      </tr>
      @endforeach

      <!-- Discount if available -->
      @if ($invoice->discount > 0)
      <tr>
         <td>Discount -{{ number_format($invoice->discount) }}%</td>
         <td></td>
         <td class="totals-value">{{ number_format($invoice->discount_amount, 2) }}</td>
      </tr>
      @endif
   </table>
   @if($invoice->sales_type_code !== "N")
   <div class="line"></div>
   <p class="center"><b>THIS IS NOT AN OFFICIAL RECEIPT</b></p>
   @endif
   <div class="line"></div>

   <!-- Totals Summary -->
   <table>
      <tr>
         <td class="totals">TOTAL:</td>
         <td class="totals-value"><b>{{ number_format($invoice->total, 2) }}</b></td>
      </tr>
      <!-- Taxable Amount -->
      @if ($invoice_data['taxblAmtA'] > 0)
      <tr>
         <td class="totals">TOTAL A-EX:</td>
         <td class="totals-value">{{ number_format($invoice_data['taxblAmtA'], 2) }}</td>
      </tr>
      @endif

      @if ($invoice_data['taxblAmtB'] > 0)
      <tr>
         <td class="totals">TOTAL B-{{ number_format($invoice_data['taxRtB'], 2) }}%:</td>
         <td class="totals-value">{{ number_format($invoice_data['taxblAmtB'], 2) }}</td>
      </tr>
      @endif

      @if ($invoice_data['taxblAmtC'] > 0)
      <tr>
         <td class="totals">TOTAL C-{{ number_format($invoice_data['taxRtC'], 2) }}%:</td>
         <td class="totals-value">{{ number_format($invoice_data['taxblAmtC'], 2) }}</td>
      </tr>
      @endif

      @if ($invoice_data['taxblAmtD'] > 0)
      <tr>
         <td class="totals">TOTAL D-{{ number_format($invoice_data['taxRtD'], 2) }}%:</td>
         <td class="totals-value">{{ number_format($invoice_data['taxblAmtD'], 2) }}</td>
      </tr>
      @endif

      <!-- Total Tax -->
      <tr>
         <td class="totals">TOTAL TAX:</td>
         <td class="totals-value">{{ number_format($invoice_data['totTaxAmt'], 2) }}</td>
      </tr>
   </table>

   <div class="line"></div>

   <!-- Cash and Items Summary -->
   <table>
      <tr>
         @if($invoice->payment_type_code == "01")
         <td class="totals">CASH:</td>
         @elseif($invoice->payment_type_code == "02")
         <td class="totals">CREDIT:</td>
         @elseif($invoice->payment_type_code == "03")
         <td class="totals">CASH/CREDIT:</td>
         @elseif($invoice->payment_type_code == "04")
         <td class="totals">BANK CHECK:</td>
         @elseif($invoice->payment_type_code == "05")
         <td class="totals">DEBIT&CREDIT CARD:</td>
         @elseif($invoice->payment_type_code == "06")
         <td class="totals">MOBILE MONEY:</td>
         @elseif($invoice->payment_type_code == "07")
         <td class="totals">OTHER:</td>
         @endif
         <td class="totals-value"> {{ number_format($invoice->total, 2) }}</td>
      </tr>
      <tr>
         <td class="totals">ITEMS NUMBER:</td>
         <td class="totals-value"> {{ count($invoice->items) }}</td>
      </tr>
   </table>

   @if($invoice->sales_type_code == "C")
   <div class="line"></div>
   <p class="center"><b>COPY</b></p>
   @elseif($invoice->sales_type_code == "P")
   <div class="line"></div>
   <p class="center"><b>PROFORMA</b></p>
   @elseif($invoice->sales_type_code == "T")
   <div class="line"></div>
   <p class="center"><b>TRAINING MODE</b></p>
   @endif
   <div class="line"></div>

   <!-- SDC and MRC Information in Table Format -->
   <p class="center">SDC INFORMATION</p>
   <table>
      <tr>
         <td>Date: {{ \Carbon\Carbon::parse($invoice->vsdc_receipt_pbct_date)->format('d/m/Y') }}</td>
         <td class="right">TIME: {{ \Carbon\Carbon::parse($invoice->vsdc_receipt_pbct_date)->format('H:i:s') }}</td>
      </tr>
      <tr>
         <td>SDC ID:</td>
         <td class="right">{{ $invoice->sdc_id }}</td>
      </tr>
      <tr>
         <td>RECEIPT NUMBER:</td>
         <td class="right">{{ $invoice->id }}/{{ $invoice->receipt_number }} {{ $invoice->sales_type_code }}{{ $invoice->receipt_type_code }}</td>
      </tr>
   </table>
   @if($invoice->sales_type_code == "C" || $invoice->sales_type_code == "N")
   <p class="center">Internal Data:</p>
   <p class="center">{{ rtrim(preg_replace('/(.{4})/', '$1-', $invoice->internal_data), '-') }}</p>
   <p class="center">Receipt Signature:</p>
   <p class="center">{{ rtrim(preg_replace('/(.{4})/', '$1-', $invoice->receipt_sign), '-') }}</p>
   @endif
   @if($invoice->sales_type_code == "N")
   <div class="center">
      <img
         src="{{ $qrCodePath }}"
         alt="QR Code"
         style="align-content: center">
   </div>
   @endif
   <div class="line"></div>
   <table>
      <tr>
         <td>RECEIPT NUMBER:</td>
         <td class="right">{{ $invoice->id }}</td>
      </tr>
      <tr>
         <td>DATE: {{ \Carbon\Carbon::parse($invoice->created_at)->format('d/m/Y') }}</td>
         <td class="right">TIME: {{ \Carbon\Carbon::parse($invoice->created_at)->format('H:i:s') }}</td>
      </tr>
      <tr>
         <td>MRC:</td>
         <td class="right">{{ $invoice->mrc_number }}</td>
      </tr>
   </table>
   <div class="line"></div>
   <!-- Thank You Message -->
   <div class="center">
      <h3>THANK YOU</h3>
   </div>
</body>

</html>