@extends('layouts.admin')

@section('title', 'Account Statement - ' . $customer->name)

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Customer Account Statement</h1>
                <p class="text-muted mb-0">{{ $customer->name }}</p>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-info" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
                <a href="{{ route('admin.udhar.show', $customer) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
    </div>

    {{-- Date Range Filter --}}
    <div class="card mb-4 no-print">
        <div class="card-body">
            <form action="{{ route('admin.customers.statement', $customer) }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" 
                               class="form-control" 
                               id="start_date" 
                               name="start_date" 
                               value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" 
                               class="form-control" 
                               id="end_date" 
                               name="end_date" 
                               value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.customers.statement', $customer) }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Statement Content --}}
    <div class="card">
        <div class="card-body">
            {{-- Statement Header --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <h4 class="mb-3">Customer Information</h4>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="150"><strong>Name:</strong></td>
                            <td>{{ $customer->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Mobile:</strong></td>
                            <td>{{ $customer->phone ?? 'N/A' }}</td>
                        </tr>
                        @if($customer->family)
                        <tr>
                            <td><strong>Family:</strong></td>
                            <td>{{ $customer->family->name }} ({{ $customer->family->family_code }})</td>
                        </tr>
                        @endif
                        <tr>
                            <td><strong>Warehouse:</strong></td>
                            <td>{{ $customer->warehouse->name ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6 text-end">
                    <h4 class="mb-3">Statement Period</h4>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="150"><strong>From:</strong></td>
                            <td>{{ request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->format('M d, Y') : 'Beginning' }}</td>
                        </tr>
                        <tr>
                            <td><strong>To:</strong></td>
                            <td>{{ request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->format('M d, Y') : 'Today' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Generated:</strong></td>
                            <td>{{ now()->format('M d, Y h:i A') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Opening Balance --}}
            @if($openingBalance != 0)
            <div class="alert alert-info">
                <strong>Opening Balance:</strong> Rs. {{ number_format(abs($openingBalance), 2) }}
                @if($openingBalance > 0)
                    <span class="text-danger">(Debit)</span>
                @else
                    <span class="text-success">(Credit)</span>
                @endif
            </div>
            @endif

            {{-- Transaction Table --}}
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th width="100">Date</th>
                            <th width="150">Reference</th>
                            <th>Description</th>
                            <th width="120" class="text-end">Debit (Sales)</th>
                            <th width="120" class="text-end">Credit (Payments)</th>
                            <th width="120" class="text-end">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($statement['transactions']) > 0)
                            @foreach($statement['transactions'] as $transaction)
                            <tr>
                                <td>
                                    <small>{{ $transaction['date']->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    @if($transaction['type'] === 'sale')
                                        {{ $transaction['reference'] }}
                                    @else
                                        {{ $transaction['reference'] }}
                                    @endif
                                </td>
                                <td>{{ $transaction['description'] }}</td>
                                <td class="text-end">
                                    @if($transaction['debit'] > 0)
                                        {{ number_format($transaction['debit'], 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($transaction['credit'] > 0)
                                        {{ number_format($transaction['credit'], 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end">
                                    <strong>{{ number_format($transaction['balance'], 2) }}</strong>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center text-muted">No transactions in this period</td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">Totals:</th>
                            <th class="text-end">{{ number_format($statement['summary']['total_sales'], 2) }}</th>
                            <th class="text-end">{{ number_format($statement['summary']['total_payments'], 2) }}</th>
                            <th class="text-end">
                                <strong class="{{ $statement['summary']['current_balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($statement['summary']['current_balance'], 2) }}
                                </strong>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Summary Box --}}
            <div class="row mt-4">
                <div class="col-md-6 offset-md-6">
                    <div class="card border-primary">
                        <div class="card-body">
                            <h5 class="card-title">Account Summary</h5>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td><strong>Total Sales:</strong></td>
                                    <td class="text-end">{{ number_format($statement['summary']['total_sales'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Payments:</strong></td>
                                    <td class="text-end text-success">{{ number_format($statement['summary']['total_payments'], 2) }}</td>
                                </tr>
                                <tr class="border-top">
                                    <td><strong>Current Balance:</strong></td>
                                    <td class="text-end">
                                        <strong class="{{ $statement['summary']['current_balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($statement['summary']['current_balance'], 2) }}
                                        </strong>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Notes --}}
            <div class="mt-4 pt-3 border-top">
                <p class="text-muted small mb-0">
                    <strong>Note:</strong> This statement is generated electronically. All figures are in Pakistani Rupees (PKR).
                    Debit represents sales/charges, Credit represents payments received.
                </p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
@media print {
    .no-print {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    body {
        font-size: 12px;
    }
}
</style>
@endpush
