@extends('layouts.admin')

@section('title', 'Purchase Report Detail - ' . $purchase->purchase_number)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.purchases.index') }}">Purchase Report</a></li>
    <li class="breadcrumb-item active">{{ $purchase->purchase_number }}</li>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Purchase Report Detail</h1>
        </div>
        <div>
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Purchase Report
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Purchase Header Information --}}
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                <i class="bi bi-cart me-2"></i>
                                Purchase Order: {{ $purchase->purchase_number }}
                            </h5>
                        </div>
                        <div class="col-auto">
                            @if($purchase->status === 'confirmed')
                                <span class="badge bg-success">Confirmed</span>
                            @elseif($purchase->status === 'draft')
                                <span class="badge bg-warning">Draft</span>
                            @else
                                <span class="badge bg-danger">Cancelled</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        {{-- Supplier Information --}}
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2"><i class="bi bi-shop me-1"></i>Supplier</small>
                            <p class="mb-0 fw-bold">{{ $purchase->supplier->name }}</p>
                            @if($purchase->supplier->phone)
                                <small class="text-muted">
                                    <i class="bi bi-telephone me-1"></i>{{ $purchase->supplier->phone }}
                                </small>
                            @endif
                            @if($purchase->supplier->email)
                                <br><small class="text-muted">
                                    <i class="bi bi-envelope me-1"></i>{{ $purchase->supplier->email }}
                                </small>
                            @endif
                        </div>

                        {{-- Warehouse --}}
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2"><i class="bi bi-building me-1"></i>Warehouse</small>
                            <p class="mb-0 fw-bold">{{ $purchase->warehouse->name }}</p>
                        </div>

                        {{-- Purchase Date --}}
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2"><i class="bi bi-calendar me-1"></i>Purchase Date</small>
                            <p class="mb-0 fw-bold">{{ $purchase->purchase_date->format('d M Y') }}</p>
                        </div>

                        {{-- Created By --}}
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2"><i class="bi bi-person-badge me-1"></i>Created By</small>
                            <p class="mb-0 fw-bold">{{ $purchase->creator->name }}</p>
                            <small class="text-muted">{{ $purchase->created_at->format('d M Y, h:i A') }}</small>
                        </div>

                        {{-- Confirmed By --}}
                        @if($purchase->isConfirmed() && $purchase->confirmer)
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2"><i class="bi bi-check-circle me-1"></i>Confirmed By</small>
                            <p class="mb-0 fw-bold">{{ $purchase->confirmer->name }}</p>
                            <small class="text-muted">{{ $purchase->confirmed_at->format('d M Y, h:i A') }}</small>
                        </div>
                        @endif
                    </div>

                    {{-- Notes --}}
                    @if($purchase->notes)
                    <div class="mt-3 pt-3 border-top">
                        <small class="text-muted d-block mb-2"><i class="bi bi-sticky me-1"></i>Notes</small>
                        <p class="mb-0">{{ $purchase->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Purchase Items --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-box-seam me-2"></i>Purchase Items ({{ $purchase->items->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($purchase->items->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Unit Cost</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $item->product->name }}</strong>
                                        @if($item->product->sku)
                                            <br><small class="text-muted">SKU: {{ $item->product->sku }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="text-end">Rs. {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end fw-bold">Rs. {{ number_format($item->total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-3"></i>
                        <p class="mt-2 mb-0">No items in this purchase</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Purchase Returns (if any) --}}
            @if($purchase->returns && $purchase->returns->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-arrow-return-right me-2"></i>Purchase Returns ({{ $purchase->returns->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Return No.</th>
                                    <th>Date</th>
                                    <th class="text-end">Return Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->returns as $return)
                                <tr>
                                    <td>{{ $return->return_number }}</td>
                                    <td>{{ $return->return_date->format('d M Y') }}</td>
                                    <td class="text-end">Rs. {{ number_format($return->total_amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $return->status === 'confirmed' ? 'success' : 'warning' }}">
                                            {{ ucfirst($return->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Right Sidebar - Payment Summary --}}
        <div class="col-lg-4">
            {{-- Payment Summary --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-wallet2 me-2"></i>Payment Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <strong>Rs. {{ number_format($purchase->subtotal, 2) }}</strong>
                        </div>
                        @if($purchase->discount > 0)
                        <div class="d-flex justify-content-between mb-2 text-danger">
                            <span>Discount 
                                @if($purchase->discount_type === 'percentage')
                                    ({{ $purchase->discount }}%):
                                @else
                                    :
                                @endif
                            </span>
                            <strong>- Rs. {{ number_format($purchase->discount_amount, 2) }}</strong>
                        </div>
                        @endif
                        @if($purchase->transport_cost > 0)
                        <div class="d-flex justify-content-between mb-2 text-primary">
                            <span>Transport Cost:</span>
                            <strong>+ Rs. {{ number_format($purchase->transport_cost, 2) }}</strong>
                        </div>
                        @endif
                        @if($purchase->other_expenses > 0)
                        <div class="d-flex justify-content-between mb-2 text-primary">
                            <span>Other Expenses:</span>
                            <strong>+ Rs. {{ number_format($purchase->other_expenses, 2) }}</strong>
                        </div>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">Total Amount:</span>
                            <strong class="fs-5">Rs. {{ number_format($purchase->total_amount, 2) }}</strong>
                        </div>
                    </div>

                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-success">Paid Amount:</span>
                            <strong class="text-success">Rs. {{ number_format($purchase->paid_amount, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-danger">Payable:</span>
                            <strong class="text-danger">Rs. {{ number_format($purchase->total_amount - $purchase->paid_amount, 2) }}</strong>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-top text-center">
                        <span class="text-muted small">Payment Status</span><br>
                        @if($purchase->payment_status === 'paid')
                            <span class="badge bg-success fs-6 mt-1">Paid</span>
                        @elseif($purchase->payment_status === 'partial')
                            <span class="badge bg-warning fs-6 mt-1">Partially Paid</span>
                        @else
                            <span class="badge bg-danger fs-6 mt-1">Unpaid</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Payment History --}}
            @if($purchase->payments->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>Payment History
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($purchase->payments as $payment)
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">{{ $payment->created_at->format('d M Y, h:i A') }}</small>
                                    @if($payment->payment_method)
                                        <br><small class="badge bg-info">{{ ucfirst($payment->payment_method) }}</small>
                                    @endif
                                </div>
                                <div>
                                    <strong class="text-success">Rs. {{ number_format($payment->amount, 2) }}</strong>
                                </div>
                            </div>
                            @if($payment->notes)
                                <small class="text-muted d-block mt-1">{{ $payment->notes }}</small>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Statistics --}}
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up me-2"></i>Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Items:</span>
                        <strong>{{ $purchase->items->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Quantity:</span>
                        <strong>{{ number_format($purchase->items->sum('quantity'), 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
