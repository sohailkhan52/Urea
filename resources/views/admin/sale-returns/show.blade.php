@extends('layouts.admin')

@push('styles')
<style>
    .sale-return-print-header {
        display: none;
    }

    .sale-return-print-meta {
        display: none;
    }

    .sale-return-print-column-headings {
        display: none;
    }

    @media print {
        @page {
            size: A4 portrait;
            margin: 8mm;
        }

        html,
        body {
            height: auto !important;
            min-height: 0 !important;
        }

        .sidebar,
        .topbar,
        .no-print {
            display: none !important;
        }

        .content {
            margin-left: 0 !important;
            padding: 0 !important;
        }

        .sale-return-page,
        .sale-return-page .container-fluid {
            width: 100% !important;
            max-width: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .sale-return-page .card {
            break-inside: avoid;
            margin-bottom: 0.6rem !important;
        }

        .sale-return-page,
        .sale-return-page * {
            line-height: 1.2 !important;
        }

        .sale-return-page {
            font-size: 12px !important;
        }

        .sale-return-page .card-body {
            padding: 0.55rem !important;
        }

        .sale-return-page .card-header {
            padding: 0.45rem 0.55rem !important;
        }

        .sale-return-page .row {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }

        .sale-return-info-card .card-header {
            display: none !important;
        }

        .sale-return-print-meta {
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #000;
            padding: 0 0 0.5rem;
            margin-bottom: 0.75rem;
            font-weight: 700;
        }

        .sale-return-info-grid {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            column-gap: 3rem;
            row-gap: 0.5rem;
        }

        .sale-return-print-column-headings {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            column-gap: 3rem;
            margin-bottom: 0.5rem;
        }

        .sale-return-print-column-headings h6 {
            margin: 0;
        }

        .sale-return-info-grid > div {
            width: auto !important;
            max-width: none !important;
            padding: 0 !important;
        }

        .sale-return-info-grid > div:nth-child(1) {
            grid-column: 2;
            grid-row: 1;
        }

        .sale-return-info-grid > div:nth-child(2) {
            grid-column: 2;
            grid-row: 2;
        }

        .sale-return-info-grid > div:nth-child(3) {
            grid-column: 1;
            grid-row: 1;
        }

        .sale-return-info-grid > div:nth-child(4) {
            grid-column: 1;
            grid-row: 2;
        }

        .sale-return-info-grid > div:nth-child(5) {
            grid-column: 1;
            grid-row: 3;
        }

        .sale-return-info-grid > div:nth-child(6) {
            grid-column: 1;
            grid-row: 4;
        }

        .sale-return-page > .row > .col-lg-8,
        .sale-return-page > .row > .col-lg-4 {
            width: 100% !important;
            max-width: none !important;
            flex: 0 0 100% !important;
        }

        .sale-return-print-header {
            display: flex !important;
            align-items: flex-start;
            justify-content: space-between;
            border-bottom: 2px solid #000;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
        }

        .sale-return-print-header h1,
        .sale-return-print-header h2,
        .sale-return-print-header p {
            margin: 0;
        }
    }
</style>
@endpush

@section('title', 'Return Details - ' . $return->return_number)

@section('content')
<div class="container-fluid sale-return-page">
    <div class="sale-return-print-header">
        <div>
            <h1>{{ $company->name ?? config('app.name') }}</h1>
            @if($company?->address)
                <p>{{ $company->address }}</p>
            @endif
            <p>
                @if($company?->phone) {{ $company->phone }} @endif
                @if($company?->phone && $company?->email) | @endif
                @if($company?->email) {{ $company->email }} @endif
            </p>
        </div>
        <div class="text-end">
            <h2>Sale Return</h2>
            <p><strong>Return #:</strong> {{ $return->return_number }}</p>
            <p><strong>Date:</strong> {{ $return->return_date->format('d M Y') }}</p>
        </div>
    </div>

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h1 class="h3 mb-1">Return Details</h1>
            <p class="text-muted mb-0">{{ $return->return_number }}</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary me-2" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print Return
            </button>
            <a href="{{ route('admin.sale-returns.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Returns
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Main Content --}}
        <div class="col-lg-8">
            {{-- Return Information --}}
            <div class="card mb-4 sale-return-info-card">
                <div class="sale-return-print-meta">
                    <span><i class="bi bi-receipt me-1"></i> {{ $return->return_number }}</span>
                    <span>{{ $return->status_label }}</span>
                </div>
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Return Information</h5>
                    <span class="badge bg-{{ $return->status_badge }} fs-6">
                        {{ $return->status_label }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="sale-return-print-column-headings">
                        <h6>Original Sale</h6>
                        <h6>Return Information</h6>
                    </div>
                    <div class="row g-3 sale-return-info-grid">
                        <div class="col-md-6">
                            <label class="text-muted small">Return Number</label>
                            <div class="fw-semibold">{{ $return->return_number }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Return Date</label>
                            <div class="fw-semibold">{{ $return->return_date->format('d M Y') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Original Sale</label>
                            <div>
                                <a href="{{ route('admin.sales.show', $return->sale_id) }}" class="text-decoration-none">
                                    <i class="bi bi-file-earmark-text me-1"></i>
                                    {{ $return->sale->invoice_number }}
                                </a>
                                <br>
                                <small class="text-muted">{{ $return->sale->sale_date->format('d M Y') }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Warehouse</label>
                            <div class="fw-semibold">
                                <i class="bi bi-building me-1"></i>
                                {{ $return->warehouse->name }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Customer</label>
                            <div>
                                @if($return->customer)
                                    <strong>{{ $return->customer->name }}</strong>
                                    @if($return->customer->phone)
                                    <br><small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $return->customer->phone }}</small>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Walk-in Customer</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Family</label>
                            <div>
                                @if($return->family)
                                    <strong>{{ $return->family->name }}</strong>
                                    <br><small class="text-muted">{{ $return->family->family_code }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </div>
                        @if($return->reason)
                        <div class="col-md-12">
                            <label class="text-muted small">Reason for Return</label>
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-2"></i>{{ $return->reason }}
                            </div>
                        </div>
                        @endif
                        @if($return->notes)
                        <div class="col-md-12">
                            <label class="text-muted small">Additional Notes</label>
                            <div class="border rounded p-3 bg-light">
                                {{ $return->notes }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Original Sale Payment Summary --}}
            <div class="card mb-4 no-print">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Original Sale Payment Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center">
                                <small class="text-muted d-block mb-1">Sale Total</small>
                                <h5 class="mb-0 text-primary">Rs. {{ number_format($paymentInfo['total_amount'], 0) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center">
                                <small class="text-muted d-block mb-1">Paid Amount</small>
                                <h5 class="mb-0 text-success">Rs. {{ number_format($paymentInfo['paid_amount'], 0) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center">
                                <small class="text-muted d-block mb-1">Outstanding</small>
                                <h5 class="mb-0 text-danger">Rs. {{ number_format($paymentInfo['outstanding'], 0) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center">
                                <small class="text-muted d-block mb-1">Status</small>
                                <h5 class="mb-0">
                                    <span class="badge bg-{{ $paymentInfo['payment_status'] === 'Paid' ? 'success' : ($paymentInfo['payment_status'] === 'Unpaid' ? 'danger' : 'warning') }}">
                                        {{ $paymentInfo['payment_status'] }}
                                    </span>
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Return Items --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Returned Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Original Qty</th>
                                    <th class="text-center">Return Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Return Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($return->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->product->name }}</div>
                                        @if($item->product->sku)
                                        <small class="text-muted">SKU: {{ $item->product->sku }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{ number_format($item->saleItem->quantity, 0) }}
                                    </td>
                                    <td class="text-center">
                                        <strong class="text-primary">{{ number_format($item->quantity, 0) }}</strong>
                                    </td>
                                    <td class="text-end">
                                        Rs. {{ number_format($item->unit_price, 0) }}
                                    </td>
                                    <td class="text-end">
                                        <strong>Rs. {{ number_format($item->total, 0) }}</strong>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Total Return Amount:</th>
                                    <th class="text-end">
                                        <strong class="text-primary fs-5">
                                            Rs. {{ number_format($return->total_return_amount, 0) }}
                                        </strong>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Confirmation Alert --}}
            @if($return->canBeConfirmed())
            <div class="card border-warning no-print">
                <div class="card-body">
                    <div class="alert alert-warning mb-3">
                        <h6 class="alert-heading">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            This return is in draft status
                        </h6>
                        <p class="mb-0">Confirming this return will:</p>
                        <ul class="mb-0 mt-2">
                            <li>Add returned items back to warehouse stock</li>
                            <li>Adjust customer balance (reduce udhar or create credit)</li>
                            <li>This action cannot be undone</li>
                        </ul>
                    </div>
                    
                    <div class="d-flex gap-2">
                        @can('sales.approve')
                        <form action="{{ route('admin.sale-returns.confirm', $return) }}" method="POST" onsubmit="return confirm('Are you sure you want to confirm this return? This action cannot be undone.');">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i> Confirm Return
                            </button>
                        </form>
                        @endcan
                        
                        @can('sales.cancel')
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                            <i class="bi bi-x-circle me-1"></i> Cancel Return
                        </button>
                        @endcan
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Summary Card --}}
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">Return Summary</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Total Items:</span>
                        <strong>{{ $return->items->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Total Quantity:</span>
                        <strong>{{ number_format($return->total_quantity, 0) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Total Return Amount:</span>
                        <strong class="text-primary fs-5">Rs. {{ number_format($return->total_return_amount, 0) }}</strong>
                    </div>
                </div>
            </div>

            {{-- Audit Information --}}
            <div class="card mb-4 no-print">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Audit Trail</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Created By</small>
                        <div>
                            <i class="bi bi-person me-1"></i>
                            {{ $return->creator->name }}
                        </div>
                        <small class="text-muted">{{ $return->created_at->format('d M Y, h:i A') }}</small>
                    </div>
                    
                    @if($return->isConfirmed())
                    <div class="mb-3">
                        <small class="text-muted d-block">Confirmed By</small>
                        <div>
                            <i class="bi bi-person-check me-1"></i>
                            {{ $return->confirmer->name }}
                        </div>
                        <small class="text-muted">{{ $return->confirmed_at->format('d M Y, h:i A') }}</small>
                    </div>
                    @endif
                    
                    <div>
                        <small class="text-muted d-block">Status</small>
                        <span class="badge bg-{{ $return->status_badge }}">
                            {{ $return->status_label }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="card no-print">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.sales.show', $return->sale_id) }}" class="btn btn-outline-primary">
                            <i class="bi bi-file-earmark-text me-1"></i> View Original Sale
                        </a>
                        
                        @if($return->customer)
                        <a href="{{ route('admin.customers.statement', $return->customer_id) }}" class="btn btn-outline-info">
                            <i class="bi bi-person me-1"></i> View Customer
                        </a>
                        @endif
                        
                        <a href="{{ route('admin.sale-returns.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-list me-1"></i> All Returns
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Cancel Modal --}}
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Return</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.sale-returns.cancel', $return) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Are you sure you want to cancel this return?
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Cancellation Reason (Optional)</label>
                        <textarea class="form-control" 
                                  id="reason" 
                                  name="reason" 
                                  rows="3" 
                                  placeholder="Enter reason for cancellation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i> Cancel Return
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
