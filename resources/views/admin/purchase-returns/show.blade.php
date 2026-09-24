@extends('layouts.admin')

@section('title', 'Purchase Return - ' . $purchaseReturn->return_number)

@push('styles')
<style>
    .purchase-return-print-header {
        display: none;
    }

    .purchase-return-print-footer { display: none; }

    @media print {
        .sidebar,
        .topbar,
        .no-print {
            display: none !important;
        }

        .content {
            margin-left: 0 !important;
            padding: 0 !important;
        }

        .purchase-return-page,
        .purchase-return-page .container-fluid {
            width: 100% !important;
            max-width: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .purchase-return-print-header {
            display: flex !important;
            align-items: flex-start;
            justify-content: space-between;
            padding-bottom: 12px;
            border-bottom: 3px solid #000;
            margin-bottom: 20px;
            font-size: 11px;
            line-height: 1.4;
        }

        .purchase-return-print-header .company-name,
        .purchase-return-print-header .print-title {
            font-size: 24px;
            font-weight: 800;
            line-height: 1.2;
        }

        .purchase-return-print-header .print-title-block {
            text-align: right;
        }

        .purchase-return-print-header .print-title-block small {
            display: block;
            font-size: 11px;
            font-weight: 400;
        }

        .purchase-return-print-footer {
            display: block !important;
            position: fixed;
            right: 0;
            bottom: 0;
            left: 0;
            padding-top: 6px;
            border-top: 1px solid #000;
            text-align: center;
            font-size: 11px;
            color: #000 !important;
        }

        .purchase-return-page .card {
            break-inside: avoid;
        }

        .purchase-return-info-grid {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 2rem;
        }

        .purchase-return-info-grid > div {
            width: auto !important;
            max-width: none !important;
            flex: none !important;
        }

        .purchase-return-print-header {
            display: flex !important;
            align-items: flex-start;
            justify-content: space-between;
            border-bottom: 2px solid #000;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
        }

        .purchase-return-print-header h1,
        .purchase-return-print-header h2,
        .purchase-return-print-header p {
            margin: 0;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid purchase-return-page">
    <div class="purchase-return-print-header">
        <div>
            <div class="company-name">{{ $company?->name ?? 'DeraNexa' }}</div>
            <div>{{ implode(' / ', array_filter([$company?->phone ?: '03239123800', $company?->additional_number])) }}</div>
        </div>
        <div class="print-title-block">
            <div class="print-title">Purchase Return</div>
            <small>Return #: {{ $purchaseReturn->return_number }}</small>
            <small>Date: {{ $purchaseReturn->return_date->format('d M Y') }}</small>
        </div>
    </div>

    <div class="page-header no-print">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="page-title">Purchase Return Details</h1>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-primary me-2" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print Return
                </button>
                <a href="{{ route('admin.purchase-returns.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Returns
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- LEFT COLUMN -->
        <div class="col-lg-8">
            <!-- Return Info -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-receipt"></i> {{ $purchaseReturn->return_number }}</h5>
                    <span class="badge bg-{{ $purchaseReturn->status_badge }}">
                        {{ $purchaseReturn->status_label }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row purchase-return-info-grid">
                        <div class="col-md-6">
                            <h6>Original Purchase</h6>
                            <p>
                                <strong>Purchase #:</strong> 
                                @if($purchaseReturn->purchase)
                                    <a href="{{ route('admin.purchases.show', $purchaseReturn->purchase) }}">
                                        {{ $purchaseReturn->purchase->purchase_number }}
                                    </a>
                                @else
                                    <span class="text-muted">(Deleted Purchase)</span>
                                @endif
                                <br>
                                <strong>Date:</strong> {{ $purchaseReturn->purchase->purchase_date->format('d M Y') }}
                            </p>

                            <h6 class="mt-3">Supplier</h6>
                            <p>
                                {{ $purchaseReturn->supplier->name }}<br>
                                @if($purchaseReturn->supplier->phone)
                                    <i class="bi bi-telephone"></i> {{ $purchaseReturn->supplier->phone }}
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Return Information</h6>
                            <p>
                                <strong>Return Date:</strong> {{ $purchaseReturn->return_date->format('d M Y') }}<br>
                                <strong>Warehouse:</strong> {{ $purchaseReturn->warehouse->name }}<br>
                                <strong>Created By:</strong> {{ $purchaseReturn->creator->name }}<br>
                                <strong>Created:</strong> {{ $purchaseReturn->created_at->format('d M Y H:i') }}
                            </p>

                            @if($purchaseReturn->isConfirmed())
                                <p>
                                    <strong>Confirmed By:</strong> {{ $purchaseReturn->confirmer->name ?? 'N/A' }}<br>
                                    <strong>Confirmed At:</strong> {{ $purchaseReturn->confirmed_at?->format('d M Y H:i') ?? 'N/A' }}
                                </p>
                            @endif
                        </div>
                    </div>

                    @if($purchaseReturn->reason)
                        <div class="alert alert-warning mt-3">
                            <strong>Reason for Return:</strong><br>
                            {{ $purchaseReturn->reason }}
                        </div>
                    @endif

                    @if($purchaseReturn->notes)
                        <div class="alert alert-info mt-3">
                            <strong>Notes:</strong><br>
                            {{ $purchaseReturn->notes }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Return Items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Returned Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Quantity</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseReturn->items as $item)
                                    <tr>
                                        <td>
                                            @if($item->product)
                                                <strong>{{ $item->product->name }}</strong><br>
                                                <small class="text-muted">Unit: {{ $item->product->unit }}</small>
                                            @else
                                                <span class="text-danger">Product Deleted</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-light text-dark">{{ $item->quantity }}</span>
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
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN -->
        <div class="col-lg-4">
            <!-- Financial Summary -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-calculator"></i> Return Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Items:</span>
                        <strong>{{ $purchaseReturn->total_items_count }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Total Quantity:</span>
                        <strong>{{ number_format($purchaseReturn->total_quantity, 0) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Total Return Amount:</span>
                        <strong class="text-primary fs-5">Rs. {{ number_format($purchaseReturn->total_amount, 0) }}</strong>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            @if($purchaseReturn->isDraft())
                <div class="card mb-4 no-print">
                    <div class="card-header bg-warning bg-opacity-10">
                        <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Actions</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.purchase-returns.confirm', $purchaseReturn) }}" 
                              method="POST" 
                              onsubmit="return confirm('Confirm this return? Stock will be adjusted.')">
                            @csrf
                            @method('POST')
                            <button type="submit" class="btn btn-success w-100 mb-2">
                                <i class="bi bi-check-circle"></i> Confirm Return
                            </button>
                        </form>

                        <form action="{{ route('admin.purchase-returns.cancel', $purchaseReturn) }}" 
                              method="POST" 
                              onsubmit="return confirm('Cancel this return?')">
                            @csrf
                            @method('POST')
                            <button type="submit" class="btn btn-warning w-100 mb-2">
                                <i class="bi bi-x-circle"></i> Cancel Return
                            </button>
                        </form>

                        <form action="{{ route('admin.purchase-returns.destroy', $purchaseReturn) }}" 
                              method="POST" 
                              onsubmit="return confirm('Delete this draft return?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-trash"></i> Delete Draft
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="purchase-return-print-footer">
        Address: {{ $company?->address ?: 'Naivela Dera Ismail Khan' }}
    </div>
</div>
@endsection
