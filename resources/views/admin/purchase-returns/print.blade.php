<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Return - {{ $return->return_number }}</title>
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
            border-bottom: 3px solid #dc3545;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #dc3545;
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
            color: #dc3545;
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
            border-top: 2px solid #dc3545;
            border-bottom: 2px solid #dc3545;
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
            margin-left: auto;
            width: 50%;
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
            border-top: 2px solid #dc3545;
            border-bottom: 2px solid #dc3545;
            font-size: 16px;
            font-weight: bold;
        }
        .total-row .summary-label {
            color: #dc3545;
        }
        .total-row .summary-value {
            color: #dc3545;
            font-size: 18px;
        }
        .settlement-box {
            background-color: #f8f9fa;
            border: 2px solid #198754;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        .settlement-box h5 {
            color: #198754;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .settlement-table {
            width: 100%;
        }
        .settlement-table td {
            padding: 8px 0;
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
            background-color: #fff3cd;
            color: #664d03;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #842029;
        }
        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #0d6efd;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .btn-print:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>
<body>
    <button class="btn-print no-print" onclick="window.print()">🖨️ Print</button>

    <div class="invoice-container">
        {{-- Header --}}
        <div class="invoice-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="company-name">{{ $company->name ?? 'Company Name' }}</div>
                    @if($company)
                    <p class="mb-0" style="font-size: 13px; color: #666;">
                        @if($company->address)
                            <strong>Address:</strong> {{ $company->address }}<br>
                        @endif
                        @if($company->phone)
                            <strong>Phone:</strong> {{ $company->phone }}
                        @endif
                        @if($company->email)
                            | <strong>Email:</strong> {{ $company->email }}
                        @endif
                    </p>
                    @endif
                </div>
                <div class="col-md-4 text-end">
                    <span class="status-badge status-{{ strtolower($return->status) }}">
                        {{ strtoupper($return->status) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Title --}}
        <div class="invoice-title">
            PURCHASE RETURN NOTE
        </div>

        {{-- Return Metadata --}}
        <div class="invoice-meta">
            <div class="meta-section">
                <h6>Return Information</h6>
                <p><strong>Return Number:</strong> {{ $return->return_number }}</p>
                <p><strong>Return Date:</strong> {{ $return->return_date->format('d M Y') }}</p>
                <p><strong>Return Type:</strong> 
                    @if($return->return_type === 'WHOLE_ORDER')
                        Whole Order Return
                    @else
                        Partial Items Return
                    @endif
                </p>
                <p><strong>Original PO:</strong> {{ $return->purchase->purchase_number }}</p>
            </div>

            <div class="meta-section">
                <h6>Supplier Details</h6>
                <p><strong>{{ $return->supplier->name }}</strong></p>
                @if($return->supplier->company_name)
                    <p>{{ $return->supplier->company_name }}</p>
                @endif
                @if($return->supplier->phone)
                    <p>Phone: {{ $return->supplier->phone }}</p>
                @endif
                @if($return->supplier->email)
                    <p>Email: {{ $return->supplier->email }}</p>
                @endif
            </div>

            <div class="meta-section">
                <h6>Warehouse</h6>
                <p><strong>{{ $return->warehouse->name }}</strong></p>
                @if($return->warehouse->location)
                    <p>{{ $return->warehouse->location }}</p>
                @endif
            </div>

            <div class="meta-section">
                <h6>Created By</h6>
                <p><strong>{{ $return->creator->name }}</strong></p>
                <p>{{ $return->created_at->format('d M Y H:i') }}</p>
                @if($return->status === 'confirmed' && $return->confirmer)
                    <p class="mt-2"><strong>Confirmed By:</strong></p>
                    <p>{{ $return->confirmer->name }}</p>
                    <p>{{ $return->confirmed_at->format('d M Y H:i') }}</p>
                @endif
            </div>
        </div>

        {{-- Items Table --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 10%">#</th>
                    <th style="width: 40%">Product</th>
                    <th class="text-end" style="width: 15%">Quantity</th>
                    <th class="text-end" style="width: 15%">Unit Price</th>
                    <th class="text-end" style="width: 20%">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($return->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product->name }}</strong><br>
                        <small style="color: #999;">SKU: {{ $item->product->sku }}</small>
                        @if($item->reason)
                        <br><small style="color: #dc3545;">Reason: {{ $item->reason }}</small>
                        @endif
                    </td>
                    <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-end">Rs. {{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-end">Rs. {{ number_format(($item->quantity * $item->unit_price) - ($item->discount ?? 0), 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Summary --}}
        <table class="summary-table">
            <tr>
                <td class="summary-label">Subtotal:</td>
                <td class="summary-value">Rs. {{ number_format($return->subtotal ?? $return->total_amount, 2) }}</td>
            </tr>
            @if(($return->discount_amount ?? 0) > 0)
            <tr>
                <td class="summary-label">Discount:</td>
                <td class="summary-value" style="color: #dc3545;">- Rs. {{ number_format($return->discount_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td class="summary-label">TOTAL RETURN AMOUNT:</td>
                <td class="summary-value">Rs. {{ number_format($return->total_amount, 2) }}</td>
            </tr>
        </table>

        {{-- Settlement Details (if confirmed) --}}
        @if($return->status === 'confirmed')
        <div class="settlement-box">
            <h5>💰 Settlement Details</h5>
            <table class="settlement-table">
                <tr>
                    <td style="width: 50%; color: #666;">Expected Refund from Supplier:</td>
                    <td class="text-end" style="font-weight: bold; color: #198754;">Rs. {{ number_format($return->refund_amount ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td style="width: 50%; color: #666;">Supplier Credit Applied:</td>
                    <td class="text-end" style="font-weight: bold; color: #0d6efd;">Rs. {{ number_format($return->supplier_credit_amount ?? 0, 2) }}</td>
                </tr>
                @if($return->refund_method)
                <tr>
                    <td style="width: 50%; color: #666;">Refund Method:</td>
                    <td class="text-end" style="font-weight: bold;">{{ str_replace('_', ' ', ucwords($return->refund_method)) }}</td>
                </tr>
                @endif
                @if($return->refund_reference)
                <tr>
                    <td style="width: 50%; color: #666;">Reference:</td>
                    <td class="text-end" style="font-weight: bold;">{{ $return->refund_reference }}</td>
                </tr>
                @endif
            </table>
        </div>
        @endif

        {{-- Notes --}}
        @if($return->reason || $return->notes || $return->settlement_notes)
        <div style="margin-top: 30px; padding: 15px; background-color: #f8f9fa; border-left: 4px solid #dc3545;">
            @if($return->reason)
            <p style="margin: 0 0 10px 0;"><strong>Return Reason:</strong> {{ $return->reason }}</p>
            @endif
            @if($return->notes)
            <p style="margin: 0 0 10px 0;"><strong>Notes:</strong> {{ $return->notes }}</p>
            @endif
            @if($return->settlement_notes)
            <p style="margin: 0;"><strong>Settlement Notes:</strong> {{ $return->settlement_notes }}</p>
            @endif
        </div>
        @endif

        {{-- Footer --}}
        <div class="invoice-footer">
            <p>This is a computer-generated document. No signature is required.</p>
            <p>Printed on: {{ now()->format('d M Y H:i:s') }}</p>
        </div>
    </div>

    <script>
        // Auto print on load if requested
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('autoprint') === '1') {
            window.onload = function() {
                window.print();
            };
        }
    </script>
</body>
</html>
