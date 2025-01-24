<!-- resources/views/your/invoice/blade/view.blade.php -->
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
        >
        <title>Invoice</title>
        <!-- Include Tailwind CSS -->
        <link
            href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css"
            rel="stylesheet"
        >
        <style>
            /* Add your custom styles here */
        </style>
    </head>

    <body class="font-sans bg-black-100">

        <div class="container mx-auto p-8 bg-white shadow-md">

            <!-- Header -->
            <header class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <img
                            src="{{$payload['logo']}}"
                            alt="Company Logo"
                            class="w-20 h-20"
                        >
                    </div>
                    <div class="text-right">
                        <h1 class="text-2xl font-bold">Invoice #{{$payload['invoice_number']}}</h1>
                        <p>{{$payload['date']}}</p>
                    </div>
                </div>
            </header>

            <!-- Sender and Recipient Information -->
            <div class="mb-6">
                <div class="flex justify-between">
                    <div>
                        <h2 class="font-bold">From:</h2>
                        <p>{{$payload['sender']}}</p>
                    </div>
                    <div>
                        <h2 class="font-bold">To:</h2>
                        <p>{{$payload['recipient']}}</p>
                    </div>
                </div>
            </div>

            <!-- Invoice Items -->
            <div class="mb-6">
                <table class="w-full border-collapse border border-black-300">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border border-black-300">Item Name</th>
                            <th class="py-2 px-4 border border-black-300">Quantity</th>
                            <th class="py-2 px-4 border border-black-300">Rate</th>
                            <th class="py-2 px-4 border border-black-300">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payload['items'] as $item)
                        <tr>
                            <td class="py-2 px-4 border border-black-300">{{$item['name']}}</td>
                            <td class="py-2 px-4 border border-black-300">{{$item['quantity']}}</td>
                            <td class="py-2 px-4 border border-black-300">{{$item['rate']}}</td>
                            <td class="py-2 px-4 border border-black-300">{{$item['amount']}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Summary and Footer -->
            <div class="flex justify-end">
                <div class="w-1/2">
                    <div class="flex justify-between mb-2">
                        <span>Subtotal:</span>
                        <span>{{$payload['subtotal']}}</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span>Tax ({{$payload['tax']}}%):</span>
                        <span>{{$payload['tax']}}</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span>Discount:</span>
                        <span>{{$payload['discount']}}</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span>Total:</span>
                        <span>{{$payload['total']}}</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span>Amount Paid:</span>
                        <span>{{$payload['amount_paid']}}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Balance Due:</span>
                        <span>{{$payload['balance_due']}}</span>
                    </div>
                </div>
            </div>

            <!-- Notes and Terms -->
            <div class="mt-6">
                <div class="mb-4">
                    <h2 class="font-bold">Notes:</h2>
                    <p>{{$payload['notes']}}</p>
                </div>
                <div>
                    <h2 class="font-bold">Terms:</h2>
                    <p>{{$payload['terms']}}</p>
                </div>
            </div>

        </div>

    </body>

</html>