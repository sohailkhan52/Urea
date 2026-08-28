<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payable Statement - {{ $supplier->name }}</title>
    <link rel="icon" href="{{ \App\Helpers\CompanyHelper::getFaviconUrl() }}" sizes="any">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 14px;
        }
        .supplier-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .info-item {
            font-size: 14px;
        }
        .info-label {
            color: #666;
            font-weight: 600;
            margin-bottom: 3px;
        }
        .info-value {
            color: #333;
        }
        .balance-summary {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 5px;
            text-align: center;
        }
        .balance-summary h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        .balance-amount {
            font-size: 28px;
            font-weight: bold;
            color: #d32f2f;
        }
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .ledger-table thead {
            background: #f5f5f5;
            border-bottom: 2px solid #333;
        }
        .ledger-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            color: #333;
        }
        .ledger-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
            font-size: 13px;
        }
        .ledger-table tbody tr:last-child td {
            border-bottom: 2px solid #333;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .payable-added {
            color: #d32f2f;
            font-weight: 500;
        }
        .payment-made {
            color: #388e3c;
            font-weight: 500;
        }
        .balance {
            font-weight: 600;
            color: #d32f2f;
        }
        .type-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-purchase {
            background: #e3f2fd;
            color: #1976d2;
        }
        .badge-payment {
            background: #e8f5e9;
            color: #388e3c;
        }
        .badge-return {
            background: #fff3e0;
            color: #f57c00;
        }
        .no-entries {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .print-date {
            font-size: 12px;
            color: #999;
            text-align: right;
            margin-top: 20px;
        }
        .print-button {
            margin-bottom: 20px;
            text-align: right;
            padding: 15px;
            background: #f9f9f9;
            border-bottom: 1px solid #ddd;
        }
        .print-button button,
        .print-button a {
            padding: 10px 20px;
            margin-left: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        .print-button .btn-print {
            background: #198754;
            color: white;
        }
        .print-button .btn-back {
            background: #6c757d;
            color: white;
        }
        @media print {
            html, body, body * {
                color: #000000 !important;
                background: #ffffff !important;
                border-color: #000000 !important;
                text-shadow: none !important;
                filter: none !important;
                box-shadow: none !important;
            }
            body {
                background: white;
                color: #000000;
            }
            .container {
                max-width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
            .print-button {
                display: none;
            }
            .print-date {
                display: none;
            }
            /* Dark text print stylesheet */
            * {
                color: #000000 !important;
            }
            .header {
                border-bottom: 2px solid #000000 !important;
            }
            .header h1 {
                color: #000000 !important;
            }
            .header p {
                color: #000000 !important;
            }
            .supplier-info {
                background: #ffffff !important;
            }
            .info-label {
                color: #000000 !important;
            }
            .info-value {
                color: #000000 !important;
            }
            .balance-summary {
                background: #ffffff !important;
                border: 1px solid #000000 !important;
                color: #000000 !important;
            }
            .balance-summary h3 {
                color: #000000 !important;
            }
            .balance-amount {
                color: #000000 !important;
            }
            .ledger-table thead {
                background: #ffffff !important;
                border-bottom: 2px solid #000000 !important;
            }
            .ledger-table th {
                color: #000000 !important;
            }
            .ledger-table td {
                color: #000000 !important;
                border-bottom: 1px solid #000000 !important;
            }
            .ledger-table tbody tr:last-child td {
                border-bottom: 2px solid #000000 !important;
            }
            .payable-added,
            .payment-made,
            .balance {
                color: #000000 !important;
            }
            .type-badge {
                background: #ffffff !important;
                color: #000000 !important;
                border: 1px solid #000000;
            }
            .footer {
                color: #000000 !important;
                border-top: 1px solid #000000 !important;
            }
        }
    </style>
</head>
<body>
    <div class="print-button">
        <button class="btn-print" onclick="window.print()">
            🖨️ Print Statement
        </button>

    </div>

    <div class="container">
        <div class="header">
            @php
                try {
                    $sidebarSettings = \App\Models\WelcomePageSetting::first();
                    $companyName = $sidebarSettings?->company_name ?? 'Company Name';
                } catch (\Exception $e) {
                    $companyName = 'Company Name';
                }
            @endphp
            <h1>{{ $companyName }}</h1>
            <p>Payable Statement - Supplier Account Statement</p>
        </div>

        <div class="supplier-info">
            <div class="info-item">
                <div class="info-label">Supplier Name</div>
                <div class="info-value">{{ $supplier->name }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Company</div>
                <div class="info-value">{{ $supplier->company_name ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Phone</div>
                <div class="info-value">{{ $supplier->phone ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Email</div>
                <div class="info-value">{{ $supplier->email ?? 'N/A' }}</div>
            </div>
        </div>

        <div class="balance-summary">
            <h3>Amount We Owe (Current Balance)</h3>
            <div class="balance-amount">Rs. {{ number_format($currentBalance, 2) }}</div>
        </div>

        <table class="ledger-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Reference</th>
                    <th>Description</th>
                    <th class="text-right" style="width: 12%;">Payable Added</th>
                    <th class="text-right" style="width: 12%;">Payment Made</th>
                    <th class="text-right" style="width: 12%;">Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ledgerEntries as $entry)
                <tr>
                    <td>{{ $entry['date'] }}</td>
                    <td>
                        @if($entry['type'] === 'purchase')
                            <span class="type-badge badge-purchase">Purchase</span>
                        @elseif($entry['type'] === 'payment')
                            <span class="type-badge badge-payment">Payment</span>
                        @else
                            <span class="type-badge badge-return">{{ $entry['type_label'] }}</span>
                        @endif
                    </td>
                    <td>
                        @if($entry['reference_number'])
                            <strong>{{ $entry['reference_number'] }}</strong>
                        @else
                            <span style="color: #999;">-</span>
                        @endif
                    </td>
                    <td>{{ $entry['description'] }}</td>
                    <td class="text-right payable-added">
                        @if($entry['payable_added'] > 0)
                            Rs. {{ number_format($entry['payable_added'], 2) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right payment-made">
                        @if($entry['payment_made'] > 0)
                            Rs. {{ number_format($entry['payment_made'], 2) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right balance">
                        Rs. {{ number_format($entry['balance'], 2) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="no-entries">
                        No ledger entries found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            <p>This is an automatically generated statement. For inquiries, please contact the office.</p>
        </div>

        <div class="print-date">
            Generated on: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</body>
</html>
