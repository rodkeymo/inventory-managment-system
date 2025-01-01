<!DOCTYPE html>
<html lang="en">
<head>
    <title>Nopal Hardware-Receipt</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <style>
        body {
            font-family: "DejaVu Sans Mono", "Courier New", Courier, monospace;
            background: #f5f5f5;
            padding: 10px;
            margin: 0;
            font-weight: bold;
        }
        .receipt-container {
            width: 320px;
            margin: 0 auto;
            background: #fff;
            padding: 10px;
            border: 1px solid #ccc;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        .header, .footer {
            text-align: center;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 1rem;
            margin: 0;
            font-weight: bold;
        }
        .header p {
            margin: 2px 0;
        }
        .info {
            font-size: 0.8rem;
            margin-bottom: 10px;
        }
        .info p {
            margin: 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
        }
        .table th, .table td {
            text-align: left;
            padding: 5px 0;
        }
        .table th {
            border-bottom: 1px solid #000;
            font-weight: bold;
        }
        .table td {
            text-align: right;
        }
        .table td:first-child {
            text-align: left;
        }
        .totals {
            font-size: 0.9rem;
            margin-top: 10px;
        }
        .totals p {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }
        .footer {
            border-top: 1px dashed #000;
            padding-top: 5px;
            font-size: 0.8rem;
        }
        .footer .delivery-message {
            margin-top: 10px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
    </style>
</head>
<body onload="handlePrintAndRedirect()">
    <div class="receipt-container">
        <!-- Header -->
        <div class="header">
            <h1>Nopal Hardware</h1>
            <p>P.O Box 7, KUTUS</p>
            <p>0735039809</p>
        </div>

        <!-- Receipt Info -->
        <div class="info">
            <p>Receipt No.: <strong>{{ $order->invoice_no }}</strong></p>
            <p>Date: <strong>{{ $order->created_at->format('d-M-y') }} Time: {{ $order->created_at->format('h:i A') }}</strong></p>
            <p>Day: <strong>{{ $order->created_at->format('l') }}</strong></p>
            <p>Customer Name: <strong>{{ $order->customer->name }}</strong></p>
            <p>Seller: <strong>{{ auth()->user()->name }}</strong></p>
            <p>Mode of Payment: <strong>{{ $order->payment_type }}</strong></p>
        </div>

        <div class="divider"></div>

        <!-- Order Items -->
        <table class="table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->details as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ Number::currency($item->unitcost, 'KSH.') }}</td>
                    <td>{{ Number::currency($item->total, 'KSH.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <!-- Totals -->
        <div class="totals">
            <p>Items count: <strong>{{ $order->details->count() }}</strong></p>
            <p>Subtotal: <strong>{{ Number::currency($order->sub_total, 'KSH.') }}</strong></p>
            <p>Tax (9%): <strong>{{ Number::currency($order->vat, 'KSH.') }}</strong></p>
            <p class="total">TOTAL: <strong>{{ Number::currency($order->total, 'KSH.') }}</strong></p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for shopping with us!</p>
            <p>Visit us again!</p>

            <div class="delivery-message">
                <p>We deliver to where you are</p>
                <br>
                <p>Designed and developed by Rodtech: +254798416449</p>
            </div>
        </div>
    </div>

    <script>
        function handlePrintAndRedirect() {
            window.print();

            // Redirect to orders page after a short delay
            setTimeout(() => {
                window.location.href = "{{ route('orders.index') }}";
            }, 1000);
        }
    </script>
</body>
</html>
