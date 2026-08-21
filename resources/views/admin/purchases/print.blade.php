<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - {{ $purchase->purchase_number }}</title>
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
            border-bottom: 3px solid #198754;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #198754;
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
            color: #198754;
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
            border-top: 2px solid #198754;
            border-bottom: 2px solid #198754;
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
            border-top: 2px solid #198754;
            border-bottom: 2px solid #198754;
            font-size: 16px;
            font-weight: bold;
        }
        .total-row .summary-label {
            color: #198754;
        }
        .total-row .summary-value {
            color: #198754;
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
        .status-draft {
            background-color: #cfe2ff;
            color: #084298;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #842029;
        }
        .print-button {
            margin-bottom: 20px;
            text-align: right;
        }
        .notes-section {
            margin-top: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-left: 3px solid #198754;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="no-print print-button">
        <button onclick="window.print()" class="btn btn-success">
            <i class="bi bi-printer"></i> Print Purchase Order
        </button>

    </div>

    <div class="invoice-container">
        {{-- Header --}}
        <div class="invoice-header">
            <div class="row align-items-center">
                <div class="col">
                    <div class="company-name">UREA MANAGEMENT SYSTEM</div>
                    <p style="color: #999; margin: 5px 0;">Purchase Order</p>
                </div>
                <div class="col-auto text-end">
                    <div class="status-badge status-{{ $purchase->status }}">
                        {{ ucfirst($purchase->status) }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Invoice Title --}}
        <div class="invoice-title">PURCHASE ORDER</div>

        {{-- Invoice Meta --}}
        <div class="invoice-meta">
            <div>
                <div class="meta-section">
                    <h6>Supplier</h6>
                    @if($purchase->supplier)
                        <p><strong>{{ $purchase->supplier->name }}</strong></p>
                        @if($purchase->supplier->company_name)
                        <p>Company: {{ $purchase->supplier->company_name }}</p>
                        @endif
                        @if($purchase->supplier->phone)
                        <p>Phone: {{ $purchase->supplier->phone }}</p>
                        @endif
                        @if($purchase->supplier->city)
                        <p>City: {{ $purchase->supplier->city }}</p>
                        @endif
                    @else
                        <p><strong>N/A</strong></p>
                    @endif
                </div>
            </div>
            <div>
                <div class="meta-section">
                    <h6>Purchase Details</h6>
                    <p><strong>PO #:</strong> {{ $purchase->purchase_number }}</p>
                    <p><strong>Purchase Date:</strong> {{ $purchase->purchase_date->format('M d, Y') }}</p>
                    <p><strong>Warehouse:</strong> {{ $purchase->warehouse->name }}</p>
                    <p><strong>Created:</strong> {{ $purchase->created_at->format('M d, Y h:i A') }}</p>
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
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->product->name }}</strong><br>
                        <small>SKU: {{ $item->product->sku }}</small>
                    </td>
                    <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-end"><strong>{{ number_format($item->total, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Summary --}}
        <table class="summary-table">
            <tr>
                <td class="summary-label">Subtotal:</td>
                <td class="summary-value">{{ number_format($purchase->subtotal, 2) }}</td>
            </tr>
            @if($purchase->discount > 0)
            <tr>
                <td class="summary-label">Discount:</td>
                <td class="summary-value text-danger">- {{ number_format($purchase->discount, 2) }}</td>
            </tr>
            @endif
            @if($purchase->transport_cost > 0)
            <tr>
                <td class="summary-label">Transport Cost:</td>
                <td class="summary-value">+ {{ number_format($purchase->transport_cost, 2) }}</td>
            </tr>
            @endif
            @if($purchase->other_expenses > 0)
            <tr>
                <td class="summary-label">Other Expenses:</td>
                <td class="summary-value">+ {{ number_format($purchase->other_expenses, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td class="summary-label">Total Amount:</td>
                <td class="summary-value">{{ number_format($purchase->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="summary-label">Amount Paid:</td>
                <td class="summary-value">{{ number_format($purchase->paid_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="summary-label">Amount Due:</td>
                <td class="summary-value text-danger">{{ number_format($purchase->total_amount - $purchase->paid_amount, 2) }}</td>
            </tr>
        </table>

        {{-- Notes --}}
        @if($purchase->notes)
        <div class="notes-section">
            <strong>Notes:</strong>
            <p style="margin-top: 10px; margin-bottom: 0;">{{ $purchase->notes }}</p>
        </div>
        @endif

        {{-- Footer --}}
        <div class="invoice-footer">
            <p>Please ensure delivery matches this purchase order.</p>
            <p>This is an electronically generated purchase order and is valid without a signature.</p>
            <p style="margin-top: 20px;">Generated on {{ now()->format('M d, Y h:i A') }}</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
