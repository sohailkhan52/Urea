<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #f9f9f9; padding: 20px; }
        .header { background: #2980b9; color: white; padding: 20px; text-align: center; }
        .content { background: white; padding: 20px; margin-top: 20px; }
        .summary-box { background: #f0f0f0; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .summary-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #ddd; }
        .summary-row:last-child { border-bottom: none; }
        .label { font-weight: bold; }
        .table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .table th { background: #34495e; color: white; padding: 10px; text-align: left; }
        .table td { padding: 10px; border-bottom: 1px solid #ddd; }
        .table tr:nth-child(even) { background: #f9f9f9; }
        .total-section { background: #ecf0f1; padding: 15px; margin-top: 20px; border-radius: 5px; }
        .total-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 16px; }
        .total-label { font-weight: bold; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #7f8c8d; margin-top: 20px; border-top: 1px solid #ddd; }
        .status-badge { display: inline-block; padding: 5px 10px; border-radius: 3px; font-weight: bold; font-size: 12px; background: #3498db; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 Purchase Order Confirmation</h1>
            <p>Purchase Order Number: <strong>{{ $purchase->purchase_number }}</strong></p>
        </div>

        <div class="content">
            <h2>Dear {{ $purchase->supplier?->name ?? 'Valued Supplier' }},</h2>

            <p>Thank you for partnering with us! Your purchase order has been confirmed and is ready for processing. 
            Please see the details below:</p>

            <div class="summary-box">
                <div class="summary-row">
                    <span class="label">PO Number:</span>
                    <span><strong>{{ $purchase->purchase_number }}</strong></span>
                </div>
                <div class="summary-row">
                    <span class="label">Order Date:</span>
                    <span>{{ $purchase->purchase_date->format('F j, Y') }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">Supplier:</span>
                    <span>{{ $purchase->supplier?->name }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">Delivery To:</span>
                    <span>{{ $purchase->warehouse?->name }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">Status:</span>
                    <span><span class="status-badge">Confirmed</span></span>
                </div>
            </div>

            <h3>Order Items</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase->items ?? [] as $item)
                    <tr>
                        <td>{{ $item->product?->name ?? 'Product' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->unit ?? 'kg' }}</td>
                        <td>Rs. {{ number_format($item->unit_price ?? 0, 2) }}</td>
                        <td>Rs. {{ number_format($item->total ?? 0, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="total-section">
                <div class="total-row">
                    <span class="total-label">Subtotal:</span>
                    <span>Rs. {{ number_format($purchase->subtotal ?? 0, 2) }}</span>
                </div>
                <div class="total-row">
                    <span class="total-label">Discount:</span>
                    <span>-Rs. {{ number_format($purchase->discount ?? 0, 2) }}</span>
                </div>
                <div class="total-row" style="font-size: 18px; margin-top: 10px; border-top: 2px solid #34495e; padding-top: 10px;">
                    <span class="total-label">Total Amount:</span>
                    <span style="color: #2980b9;">Rs. {{ number_format($purchase->total_amount ?? 0, 2) }}</span>
                </div>
            </div>

            <h3>Delivery Instructions</h3>
            <p>
                Please arrange delivery to: <strong>{{ $purchase->warehouse?->name }}</strong><br>
                Contact: {{ $purchase->warehouse?->contact_person ?? 'Warehouse Manager' }}<br>
                Phone: {{ $purchase->warehouse?->phone ?? 'N/A' }}
            </p>

            <p style="margin-top: 20px;">
                Please confirm receipt of this order and provide an estimated delivery date. 
                If you have any questions or need clarification, please contact us immediately.
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} DN - DeraNexa. All rights reserved.</p>
            <p>{{ config('app.name') }} | {{ config('mail.from.address') }}</p>
        </div>
    </div>
</body>
</html>
