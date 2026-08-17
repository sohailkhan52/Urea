<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #f9f9f9; padding: 20px; }
        .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
        .content { background: white; padding: 20px; margin-top: 20px; }
        .summary-box { background: #f0f0f0; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .summary-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #ddd; }
        .summary-row:last-child { border-bottom: none; }
        .label { font-weight: bold; }
        .amount { font-size: 18px; font-weight: bold; }
        .due-amount { color: #e74c3c; }
        .table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .table th { background: #34495e; color: white; padding: 10px; text-align: left; }
        .table td { padding: 10px; border-bottom: 1px solid #ddd; }
        .table tr:nth-child(even) { background: #f9f9f9; }
        .total-section { background: #ecf0f1; padding: 15px; margin-top: 20px; border-radius: 5px; }
        .total-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 16px; }
        .total-label { font-weight: bold; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #7f8c8d; margin-top: 20px; border-top: 1px solid #ddd; }
        .status-badge { display: inline-block; padding: 5px 10px; border-radius: 3px; font-weight: bold; font-size: 12px; }
        .status-paid { background: #27ae60; color: white; }
        .status-partial { background: #f39c12; color: white; }
        .status-unpaid { background: #e74c3c; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Sales Invoice Confirmation</h1>
            <p>Invoice Number: <strong>{{ $sale->invoice_number }}</strong></p>
        </div>

        <div class="content">
            <h2>Dear {{ $customerName }},</h2>

            <p>Thank you for your purchase! Your sales invoice has been confirmed and processed. Here are the details:</p>

            <div class="summary-box">
                <div class="summary-row">
                    <span class="label">Invoice Number:</span>
                    <span><strong>{{ $sale->invoice_number }}</strong></span>
                </div>
                <div class="summary-row">
                    <span class="label">Invoice Date:</span>
                    <span>{{ $sale->sale_date->format('F j, Y') }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">Warehouse:</span>
                    <span>{{ $sale->warehouse->name }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">Status:</span>
                    <span>
                        @if($sale->payment_status === 'paid')
                            <span class="status-badge status-paid">Paid</span>
                        @elseif($sale->payment_status === 'partial')
                            <span class="status-badge status-partial">Partial</span>
                        @else
                            <span class="status-badge status-unpaid">Unpaid / Udhar</span>
                        @endif
                    </span>
                </div>
            </div>

            <h3>Order Details</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>Rs. {{ number_format($item->unit_price, 2) }}</td>
                        <td>Rs. {{ number_format($item->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="total-section">
                <div class="total-row">
                    <span class="total-label">Subtotal:</span>
                    <span>Rs. {{ number_format($sale->subtotal, 2) }}</span>
                </div>
                <div class="total-row">
                    <span class="total-label">Discount:</span>
                    <span>-Rs. {{ number_format($sale->discount, 2) }}</span>
                </div>
                <div class="total-row" style="font-size: 18px; margin-top: 10px; border-top: 2px solid #34495e; padding-top: 10px;">
                    <span class="total-label">Total Amount:</span>
                    <span style="color: #2c3e50;">Rs. {{ number_format($sale->total_amount, 2) }}</span>
                </div>
                <div class="total-row">
                    <span class="total-label">Amount Paid:</span>
                    <span style="color: #27ae60;">Rs. {{ number_format($sale->paid_amount, 2) }}</span>
                </div>
                <div class="total-row">
                    <span class="total-label">Remaining Udhar:</span>
                    <span class="due-amount">Rs. {{ number_format($sale->due_amount, 2) }}</span>
                </div>
            </div>

            <p style="margin-top: 20px;">
                @if($sale->payment_status === 'paid')
                    ✅ <strong>Thank you!</strong> Your full payment has been received. Your invoice is now fully paid.
                @elseif($sale->payment_status === 'partial')
                    ⚠️ <strong>Payment Status:</strong> A partial payment of Rs. {{ number_format($sale->paid_amount, 2) }} has been received. 
                    An outstanding balance of Rs. {{ number_format($sale->due_amount, 2) }} remains due.
                @else
                    ℹ️ <strong>Payment Required:</strong> The full amount of Rs. {{ number_format($sale->total_amount, 2) }} is outstanding. 
                    Please arrange payment at your earliest convenience.
                @endif
            </p>

            <p style="margin-top: 20px;">
                If you have any questions about this invoice, please don't hesitate to contact us.
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} FMS - Fertilizer Management System. All rights reserved.</p>
            <p>{{ config('app.name') }} | {{ config('mail.from.address') }}</p>
        </div>
    </div>
</body>
</html>
