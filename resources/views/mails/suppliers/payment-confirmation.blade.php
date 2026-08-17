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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Payment Confirmation</h1>
            <p>Your payment has been successfully processed</p>
        </div>

        <div class="content">
            <h2>Dear {{ $supplierPayment->supplier?->name ?? 'Valued Supplier' }},</h2>

            <p>We are pleased to inform you that we have successfully processed a payment against your invoices. 
            Please find the payment details below:</p>

            <div class="summary-box">
                <div class="summary-row">
                    <span class="label">Payment ID:</span>
                    <span>#{{ $supplierPayment->id }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">Payment Date:</span>
                    <span>{{ $supplierPayment->created_at->format('F j, Y') }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">Supplier Name:</span>
                    <span><strong>{{ $supplierPayment->supplier?->name }}</strong></span>
                </div>
                <div class="summary-row">
                    <span class="label">Payment Method:</span>
                    <span>{{ ucfirst(str_replace('_', ' ', $supplierPayment->payment_method ?? 'Bank Transfer')) }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">Reference:</span>
                    <span>{{ $supplierPayment->reference_number ?? 'N/A' }}</span>
                </div>
            </div>

            <div class="total-section">
                <div class="total-row" style="font-size: 20px; margin-bottom: 10px;">
                    <span class="total-label">Amount Paid:</span>
                    <span class="highlight">Rs. {{ number_format($supplierPayment->amount, 2) }}</span>
                </div>
                <div class="total-row">
                    <span class="total-label">Invoice Covered:</span>
                    <span>{{ $supplierPayment->purchase?->purchase_number ?? 'Multiple Invoices' }}</span>
                </div>
            </div>

            <h3>Payment Details</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Payment Amount</td>
                        <td>Rs. {{ number_format($supplierPayment->amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Payment Status</td>
                        <td><strong>Received</strong></td>
                    </tr>
                    <tr style="background: #d5f4e6; font-weight: bold;">
                        <td>Confirmation</td>
                        <td>✅ Confirmed</td>
                    </tr>
                </tbody>
            </table>

            <p style="margin-top: 20px;">
                <strong>Thank you for your business!</strong> Your payment has been recorded in our system. 
                This payment has been credited against your outstanding invoices.
            </p>

            <p>
                If you have any questions regarding this payment or need a detailed statement of accounts, 
                please feel free to contact us.
            </p>

            <p>
                Best regards,<br>
                <strong>Accounts Department</strong><br>
                FMS - Fertilizer Management System
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} FMS - Fertilizer Management System. All rights reserved.</p>
            <p>{{ config('app.name') }} | {{ config('mail.from.address') }}</p>
        </div>
    </div>
</body>
</html>
