<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $sale->invoice_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
        body {
            background-color: #f5f5f5;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .invoice-container {
            background: white;
            max-width: 900px;
            margin: 0 auto;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .invoice-header {
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #0d6efd;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            text-align: center;
            margin: 30px 0;
        }
        .invoice-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .meta-section h6 {
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 10px;
            text-transform: uppercase;
            font-size: 12px;
        }
        .meta-section p {
            margin: 5px 0;
            color: #666;
        }
        table {
            width: 100%;
            margin: 20px 0;
        }
        .items-table thead {
            background-color: #f8f9fa;
            border-top: 2px solid #0d6efd;
            border-bottom: 2px solid #0d6efd;
        }
        .items-table th {
            padding: 12px;
            font-weight: 600;
            color: #333;
            text-align: left;
            font-size: 13px;
        }
        .items-table th.text-end {
            text-align: right;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            color: #666;
            font-size: 13px;
        }
        .items-table tr:last-child td {
            border-bottom: none;
        }
        .summary-table {
            margin-top: 20px;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 10px;
            font-size: 13px;
        }
        .summary-label {
            text-align: right;
            color: #666;
            font-weight: 500;
        }
        .summary-value {
            text-align: right;
            font-weight: 600;
            color: #333;
            padding-left: 20px;
        }
        .total-row {
            background-color: #f8f9fa;
            border-top: 2px solid #0d6efd;
            border-bottom: 2px solid #0d6efd;
            font-size: 16px;
            font-weight: bold;
        }
        .total-row .summary-label {
            color: #0d6efd;
        }
        .total-row .summary-value {
            color: #0d6efd;
            font-size: 18px;
        }
        .invoice-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #999;
            font-size: 12px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-confirmed {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .status-partial {
            background-color: #fff3cd;
            color: #664d03;
        }
        .status-paid {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .status-unpaid {
            background-color: #cfe2ff;
            color: #084298;
        }
        .print-button {
            margin-bottom: 20px;
            text-align: right;
        }
        .notes-section {
            margin-top: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-left: 3px solid #0d6efd;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="no-print print-button">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Print Invoice
        </button>
        <button onclick="window.history.back()" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </button>
    </div>

    <div class="invoice-container">
        {{-- Header --}}
        <div class="invoice-header">
            <div class="row align-items-center">
                <div class="col">
                    <div class="company-name">UREA MANAGEMENT SYSTEM</div>
                    <p style="color: #999; margin: 5px 0;">Fertilizer Sales Invoice</p>
                </div>
                <div class="col-auto text-end">
                    <div class="status-badge status-{{ $sale->isConfirmed() ? 'confirmed' : ($sale->isDraft() ? 'unpaid' : 'cancelled') }}">
                        {{ $sale->status_label }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Invoice Title --}}
        <div class="invoice-title">INVOICE</div>

        {{-- Invoice Meta --}}
        <div class="invoice-meta">
            <div>
                <div class="meta-section">
                    <h6>Bill To</h6>
                    @if($sale->customer)
                        <p><strong>{{ $sale->customer->name }}</strong></p>
                        <p>Type: {{ $sale->customer->customer_type }}</p>
                        @if($sale->customer->phone)
                        <p>Phone: {{ $sale->customer->phone }}</p>
                        @endif
                        @if($sale->customer->city)
                        <p>City: {{ $sale->customer->city }}</p>
                        @endif
                    @else
                        <p><strong>Walk-in Customer</strong></p>
                    @endif
                </div>
            </div>
            <div>
                <div class="meta-section">
                    <h6>Invoice Details</h6>
                    <p><strong>Invoice #:</strong> {{ $sale->invoice_number }}</p>
                    <p><strong>Sale Date:</strong> {{ $sale->sale_date->format('M d, Y') }}</p>
                    <p><strong>Warehouse:</strong> {{ $sale->warehouse->name }}</p>
                    <p><strong>Created:</strong> {{ $sale->created_at->format('M d, Y h:i A') }}</p>
                </div>
            </div>
        </div>

        {{-- Items Table --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-end">Item Discount</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->product->name }}</strong><br>
                        <small>SKU: {{ $item->product->sku }}</small>
                    </td>
                    <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-end">{{ number_format($item->discount, 2) }}</td>
                    <td class="text-end"><strong>{{ number_format($item->total, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Summary --}}
        <table class="summary-table">
            <tr>
                <td class="summary-label">Subtotal:</td>
                <td class="summary-value">{{ number_format($sale->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td class="summary-label">Sale Discount:</td>
                <td class="summary-value text-danger">- {{ number_format($sale->discount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td class="summary-label">Total Amount:</td>
                <td class="summary-value">{{ number_format($sale->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="summary-label">Paid Amount:</td>
                <td class="summary-value">{{ number_format($sale->paid_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="summary-label">Amount Due:</td>
                <td class="summary-value text-danger">{{ number_format($sale->due_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="summary-label">Payment Status:</td>
                <td class="summary-value">{{ ucfirst($sale->payment_status) }}</td>
            </tr>
        </table>

        {{-- Notes --}}
        @if($sale->notes)
        <div class="notes-section">
            <strong>Notes:</strong>
            <p style="margin-top: 10px; margin-bottom: 0;">{{ $sale->notes }}</p>
        </div>
        @endif

        {{-- Footer --}}
        <div class="invoice-footer">
            <p>Thank you for your business!</p>
            <p>This is an electronically generated invoice and is valid without a signature.</p>
            <p style="margin-top: 20px;">Generated on {{ now()->format('M d, Y h:i A') }}</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
