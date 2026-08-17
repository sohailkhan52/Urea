<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #f9f9f9; padding: 20px; }
        .header { background: #27ae60; color: white; padding: 20px; text-align: center; }
        .content { background: white; padding: 20px; margin-top: 20px; }
        .summary-box { background: #f0f0f0; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .summary-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #ddd; }
        .summary-row:last-child { border-bottom: none; }
        .label { font-weight: bold; }
        .amount { font-size: 18px; font-weight: bold; color: #27ae60; }
        .table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .table th { background: #34495e; color: white; padding: 10px; text-align: left; }
        .table td { padding: 10px; border-bottom: 1px solid #ddd; }
        .table tr:nth-child(even) { background: #f9f9f9; }
        .total-section { background: #d5f4e6; padding: 15px; margin-top: 20px; border-radius: 5px; border-left: 4px solid #27ae60; }
        .total-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 16px; }
        .total-label { font-weight: bold; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #7f8c8d; margin-top: 20px; border-top: 1px solid #ddd; }
        .highlight { color: #27ae60; font-weight: bold; }
        .success-icon { font-size: 24px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><span class="success-icon">✅</span> Payment Receipt</h1>
            <p>Your payment has been successfully recorded</p>
        </div>

        <div class="content">
            <h2>Dear {{ $payment->sale?->customer?->name ?? $payment->sale?->walkin_customer_name ?? 'Valued Customer' }},</h2>

            <p>Thank you for your payment! We have successfully received your payment. Here are the details:</p>

            <div class="summary-box">
                <div class="summary-row">
                    <span class="label">Receipt Number:</span>
                    <span>#{{ $payment->id }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">Invoice Number:</span>
                    <span><strong>{{ $payment->sale?->invoice_number }}</strong></span>
                </div>
                <div class="summary-row">
                    <span class="label">Payment Date:</span>
                    <span>{{ $payment->created_at->format('F j, Y') }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">Payment Method:</span>
                    <span>{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'Cash')) }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">Reference:</span>
                    <span>{{ $payment->reference_number ?? 'N/A' }}</span>
                </div>
            </div>

            <div class="total-section">
                <div class="total-row" style="font-size: 20px; margin-bottom: 10px;">
                    <span class="total-label">Amount Received:</span>
                    <span class="highlight">Rs. {{ number_format($payment->amount, 2) }}</span>
                </div>
                <div class="total-row">
                    <span class="total-label">Previous Balance:</span>
                    <span>Rs. {{ number_format($payment->sale?->due_amount + $payment->amount, 2) }}</span>
                </div>
                <div class="total-row" style="border-top: 2px solid #27ae60; padding-top: 10px; margin-top: 10px;">
                    <span class="total-label">New Balance:</span>
                    <span class="highlight">Rs. {{ number_format($payment->sale?->due_amount, 2) }}</span>
                </div>
            </div>

            <h3>Invoice Summary</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Total Amount Due</td>
                        <td>Rs. {{ number_format($payment->sale?->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Total Amount Paid (including this payment)</td>
                        <td>Rs. {{ number_format($payment->sale?->paid_amount, 2) }}</td>
                    </tr>
                    <tr style="background: #d5f4e6; font-weight: bold;">
                        <td>Outstanding Balance</td>
                        <td>Rs. {{ number_format($payment->sale?->due_amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <p style="margin-top: 20px;">
                <strong>Thank you for your payment!</strong> Your account has been updated accordingly. 
                @if($payment->sale?->due_amount > 0)
                    Your outstanding balance is Rs. {{ number_format($payment->sale?->due_amount, 2) }}.
                @else
                    Your account is now fully settled.
                @endif
            </p>

            <p>
                If you need a detailed invoice or have any questions, please contact us.
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} FMS - Fertilizer Management System. All rights reserved.</p>
            <p>{{ config('app.name') }} | {{ config('mail.from.address') }}</p>
        </div>
    </div>
</body>
</html>
