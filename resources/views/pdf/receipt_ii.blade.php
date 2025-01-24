<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Section</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }

        .page {
            width: 210mm;
            height: 297mm;
            margin: auto;
            padding: 20mm;
            box-sizing: border-box;
            background: #fff;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .contact-info {
            flex: 4;
            text-align: left;
            line-height: 1.5;
        }

        .contact-info p {
            margin: 0;
        }

        .contact-info p:first-child {
            font-weight: bold;
        }

        .logo, .stamp {
            flex: 1;
            height: 100px;
            width: 100px;
        }

        .logo {
            margin-right: 10px;
        }

        .stamp {
            margin-left: 10px;
        }

        .invoice-section {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .invoice-box {
            border: 1px solid #000;
            padding: 10px;
            width: 45%;
            box-sizing: border-box;
            line-height: 1.5;
        }

        .invoice-box p {
            margin: 0;
        }

        .invoice-box p strong {
            font-weight: bold;
        }

        /* Table Styles */
        table {
            width: 100%;
            height: 50%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table th, table td {
            border: 1px solid #000;
            padding: 5px; /* Reduce padding for smaller spacing */
            text-align: left;
            vertical-align: top; /* Align content to the top */
        }

        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        table td {
            border-top: none; /* No border at the top of each cell */
        }

        tbody tr {
            height: auto; /* Allow rows to adjust dynamically */
        }

        tbody tr:not(:last-child) td {
            border-bottom: none; /* Remove bottom border between rows */
        }

        .final-section {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .sdc-info {
            width: 40%;
            line-height: 1.5;
        }

        .sdc-info p {
            margin: 5px 0;
        }

        .sdc-info hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        .qr-code {
            width: 20%;
            text-align: center;
        }

        .qr-code img {
            width: 80px;
            height: 80px;
        }

        .totals-table {
            width: 30%;
            border-collapse: collapse;
            margin-left: 10px;
        }

        .totals-table th, .totals-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: right;
        }

        .totals-table th {
            text-align: left;
            background-color: #f2f2f2;
        }

        .footer {
            margin-top: 10px;
            font-size: 10px;
            text-align: left;
        }
        table::before {
            content: "{{ $invoice->sales_type_code == 'C' ? 'COPY' : ($invoice->sales_type_code == 'P' ? 'PROFORMA' : ($invoice->sales_type_code == 'T' ? 'TRAINING' : '')) }}";
            position: absolute;
            top: 50%; /* Center vertically */
            left: 50%; /* Center horizontally */
            transform: translate(-50%, -50%);
            font-size: 50px;
            color: rgba(0, 0, 0, 0.1); /* Light gray and semi-transparent */
            font-weight: bold;
            z-index: 1;
            pointer-events: none; /*Ensures it does not interfere with interactions*/
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #000;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .print-button:hover {
            background-color: #111;
        }
        @media print {
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">Print</button>
    <div class="page">
        <div class="header">
            <div>
                <img src="/images/rra-logo.png" alt="Logo Placeholder" class="logo">
            </div>
            <div class="contact-info">
                <p>{{ $invoice->sender }}</p>
                <p>{{ $settings->address }}</p>
                <p>TEL: {{ $settings->phone }}</p>
                <p>EMAIL: {{ $settings->email }}</p>
                <p>TIN: {{ $settings->tin }}</p>
            </div>
            <div>
                <img src="/images/ebm-logo.png" alt="Stamp Placeholder" class="stamp">
            </div>
        </div>

        <!-- Invoice Section -->
        <p><strong>INVOICE TO:</strong></p>
        <div class="invoice-section">
            <div class="invoice-box">
                <p>TIN: {{ $invoice->customer_tin }}</p>
                <p>Name: {{ $invoice->recipient }}</p>
            </div>
            <div class="invoice-box">
                <p><strong>INVOICE NO:</strong> {{ $invoice->id }}</p>
                <p>Date: {{ \Carbon\Carbon::parse($invoice->created_at)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($invoice->created_at)->format('H:i:s') }}</p>
            </div>
        </div>

        <!-- Table Section -->
        <table>
            <thead>
                <tr>
                    <th>Item Code</th>
                    <th>Item Description</th>
                    <th>Qty</th>
                    <th>Tax</th>
                    <th>Unit Price</th>
                    <th>Total Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->item_classification_code }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ number_format($item->quantity, 2) }}</td>
                    <td>{{ $item->tax_type }}</td>
                    <td>{{ number_format($item->rate, 2) }}</td>
                    <td>{{ number_format($item->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
      <!-- Final Section -->
        <div class="final-section">
            <!-- SDC Information -->
            <div class="sdc-info">
                <p><strong>SDC INFORMATION</strong></p>
                <hr>
                <p>Date: {{ \Carbon\Carbon::parse($invoice->vsdc_receipt_pbct_date)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($invoice->vsdc_receipt_pbct_date)->format('H:i:s') }}</p>
                <p>SDC ID: {{ $invoice->sdc_id }}</p>
                <p>Receipt Number: {{ $invoice->id }}/{{ $invoice->receipt_number }} {{ $invoice->sales_type_code }}{{ $invoice->receipt_type_code }}</p>
                <p>Internal Data: {{ rtrim(preg_replace('/(.{4})/', '$1-', $invoice->internal_data), '-') }}</p>
                <p>Receipt Signature: {{ rtrim(preg_replace('/(.{4})/', '$1-', $invoice->receipt_sign), '-') }}</p>
                <hr>
                <p>MRC: {{ $invoice->mrc_number }}</p>
                <hr>
                <p>Powered by EBM v2</p>
            </div>

            <!-- QR Code -->
            <div class="qr-code">
                <img src="/qrcode.png" alt="QR Code">
            </div>

            <!-- Totals Table -->
            <table class="totals-table">
                <tr>
                    <th>Total Rwf</th>
                    <td>{{ number_format($invoice->total, 2) }}</td>
                </tr>
                <tr>
                    <th>Total A-EX Rwf</th>
                    <td>{{ number_format($invoice_data['taxblAmtA'], 2) }}</td>
                </tr>
                <tr>
                    <th>Total B-18% Rwf</th>
                    <td>{{ number_format($invoice_data['taxblAmtB'], 2) }}</td>
                </tr>
                <tr>
                    <th>Total C Rwf</th>
                    <td>{{ number_format($invoice_data['taxblAmtC'], 2) }}</td>
                </tr>
                <tr>
                    <th>Total D Rwf</th>
                    <td>{{ number_format($invoice_data['taxblAmtD'], 2) }}</td>
                </tr>
                <tr>
                    <th>Total Tax Rwf</th>
                    <td>{{ number_format($invoice_data['totTaxAmt'], 2) }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>