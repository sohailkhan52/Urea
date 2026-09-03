@extends('layouts.admin')

@section('title', 'Sale Report Detail - ' . $sale->invoice_number)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.sales.index') }}">Sale Report</a></li>
    <li class="breadcrumb-item active">{{ $sale->invoice_number }}</li>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Sale Report Detail</h1>
        </div>
        <div>
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Sale Report
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Sale Header Information --}}
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                <i class="bi bi-receipt me-2"></i>
                                Invoice: {{ $sale->invoice_number }}
                            </h5>
                        </div>
                        <div class="col-auto">
                            @if($sale->status === 'confirmed')
                                <span class="badge bg-success">Confirmed</span>
                            @elseif($sale->status === 'draft')
                                <span class="badge bg-warning">Draft</span>
                            @else
                                <span class="badge bg-danger">Cancelled</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        {{-- Customer Information --}}
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2"><i class="bi bi-person me-1"></i>Customer</small>
                            @if($sale->customer)
                                <p class="mb-0 fw-bold">{{ $sale->customer->name }}</p>
                                @if($sale->customer->phone)
                                    <small class="text-muted">
                                        <i class="bi bi-telephone me-1"></i>{{ $sale->customer->phone }}
                                    </small>
                                @endif
                                @if($sale->customer->email)
                                    <br><small class="text-muted">
                                        <i class="bi bi-envelope me-1"></i>{{ $sale->customer->email }}
                                    </small>
                                @endif
                            @elseif($sale->walkin_customer_name)
                                <p class="mb-0 fw-bold">
                                    {{ $sale->walkin_customer_name }}
                                    <span class="badge bg-secondary ms-1">Walk-in</span>
                                </p>
                                @if($sale->walkin_customer_contact)
                                    <small class="text-muted">{{ $sale->walkin_customer_contact }}</small>
                                @endif
                            @else
                                <p class="mb-0"><span class="badge bg-secondary">Walk-in Customer</span></p>
                            @endif
                        </div>

                        {{-- Family --}}
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2"><i class="bi bi-people me-1"></i>Family</small>
                            @if($sale->family)
                                <p class="mb-0 fw-bold">{{ $sale->family->name }}</p>
                            @else
                                <p class="mb-0 text-muted">-</p>
                            @endif
                        </div>

                        {{-- Warehouse --}}
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2"><i class="bi bi-building me-1"></i>Warehouse</small>
                            <p class="mb-0 fw-bold">{{ $sale->warehouse->name }}</p>
                        </div>

                        {{-- Sale Date --}}
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2"><i class="bi bi-calendar me-1"></i>Sale Date</small>
                            <p class="mb-0 fw-bold">{{ $sale->sale_date->format('d M Y') }}</p>
                        </div>

                        {{-- Created By --}}
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2"><i class="bi bi-person-badge me-1"></i>Created By</small>
                            <p class="mb-0 fw-bold">{{ $sale->creator->name }}</p>
                            <small class="text-muted">{{ $sale->created_at->format('d M Y, h:i A') }}</small>
                        </div>

                        {{-- Confirmed By --}}
                        @if($sale->isConfirmed() && $sale->confirmer)
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2"><i class="bi bi-check-circle me-1"></i>Confirmed By</small>
                            <p class="mb-0 fw-bold">{{ $sale->confirmer->name }}</p>
                            <small class="text-muted">{{ $sale->confirmed_at->format('d M Y, h:i A') }}</small>
                        </div>
                        @endif
                    </div>

                    {{-- Notes --}}
                    @if($sale->notes)
                    <div class="mt-3 pt-3 border-top">
                        <small class="text-muted d-block mb-2"><i class="bi bi-sticky me-1"></i>Notes</small>
                        <p class="mb-0">{{ $sale->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Sale Items --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-cart me-2"></i>Sale Items ({{ $sale->items->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($sale->items->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Cost Price</th>
                                    <th class="text-end">Discount</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Profit/Loss</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $item->product->name }}</strong>
                                        @if($item->product->sku)
                                            <br><small class="text-muted">SKU: {{ $item->product->sku }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{ number_format($item->quantity, 2) }}
                                        @if($item->returned_qty > 0)
                                            <br><small class="text-warning">Returned: {{ number_format($item->returned_qty, 2) }}</small>
                                        @endif
                                    </td>
                                    <td class="text-end">Rs. {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end">
                                        @if($item->cost_price)
                                            Rs. {{ number_format($item->cost_price, 2) }}
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-end">Rs. {{ number_format($item->discount, 2) }}</td>
                                    <td class="text-end fw-bold">Rs. {{ number_format($item->total, 2) }}</td>
                                    <td class="text-end">
                                        @if($item->cost_price)
                                            @if($item->gross_profit >= 0)
                                                <span class="text-success fw-bold">
                                                    <i class="bi bi-arrow-up-circle me-1"></i>Rs. {{ number_format($item->gross_profit, 2) }}
                                                </span>
                                                <br><small class="text-muted">{{ number_format($item->profit_margin, 1) }}%</small>
                                            @else
                                                <span class="text-danger fw-bold">
                                                    <i class="bi bi-arrow-down-circle me-1"></i>Rs. {{ number_format(abs($item->gross_profit), 2) }}
                                                </span>
                                                <br><small class="text-muted">{{ number_format($item->profit_margin, 1) }}%</small>
                                            @endif
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-3"></i>
                        <p class="mt-2 mb-0">No items in this sale</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Profit/Loss Analysis --}}
            @if($sale->has_cost_data)
            <div class="card mb-4">
                <div class="card-header bg-{{ $sale->profit_status === 'profit' ? 'success' : ($sale->profit_status === 'loss' ? 'danger' : 'secondary') }} text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up-arrow me-2"></i>Profit/Loss Analysis
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <small class="text-muted d-block mb-1">Net Revenue</small>
                                <h5 class="mb-0 text-success">Rs. {{ number_format($sale->net_revenue, 2) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <small class="text-muted d-block mb-1">Cost of Goods</small>
                                <h5 class="mb-0 text-danger">Rs. {{ number_format($sale->total_cogs, 2) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <small class="text-muted d-block mb-1">Gross Profit/Loss</small>
                                <h5 class="mb-0 text-{{ $sale->profit_status === 'profit' ? 'success' : ($sale->profit_status === 'loss' ? 'danger' : 'secondary') }}">
                                    @if($sale->profit_status === 'profit')
                                        <i class="bi bi-arrow-up-circle me-1"></i>
                                    @elseif($sale->profit_status === 'loss')
                                        <i class="bi bi-arrow-down-circle me-1"></i>
                                    @else
                                        <i class="bi bi-dash-circle me-1"></i>
                                    @endif
                                    Rs. {{ number_format(abs($sale->gross_profit), 2) }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <small class="text-muted d-block mb-1">Profit Margin</small>
                                <h5 class="mb-0 text-info">{{ number_format($sale->profit_margin_percentage, 2) }}%</h5>
                            </div>
                        </div>
                    </div>

                    @if($sale->returns->count() > 0)
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Note:</strong> This sale has {{ $sale->returns->count() }} return(s). 
                        Profit calculation accounts for returned items (Net Quantity = Original - Returned).
                    </div>
                    @endif
                </div>
            </div>
            @elseif($sale->items->count() > 0)
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Cost Data Unavailable:</strong> Profit/loss cannot be calculated for this sale because cost price information is not available for the items. 
                This may be an older sale created before cost tracking was implemented.
            </div>
            @endif

            {{-- Sale Returns (if any) --}}
            @if($sale->returns->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-arrow-return-left me-2"></i>Sale Returns ({{ $sale->returns->count() }})
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
                                @foreach($sale->returns as $return)
                                <tr>
                                    <td>{{ $return->return_number }}</td>
                                    <td>{{ $return->return_date->format('d M Y') }}</td>
                                    <td class="text-end">Rs. {{ number_format($return->total_return_amount, 2) }}</td>
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
                            <strong>Rs. {{ number_format($sale->subtotal, 2) }}</strong>
                        </div>
                        @if($sale->discount > 0)
                        <div class="d-flex justify-content-between mb-2 text-danger">
                            <span>Discount:</span>
                            <strong>- Rs. {{ number_format($sale->discount, 2) }}</strong>
                        </div>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">Total Amount:</span>
                            <strong class="fs-5">Rs. {{ number_format($sale->total_amount, 2) }}</strong>
                        </div>
                    </div>

                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-success">Paid Amount:</span>
                            <strong class="text-success">Rs. {{ number_format($sale->paid_amount, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-danger">Outstanding (Udhar):</span>
                            <strong class="text-danger">Rs. {{ number_format($sale->due_amount, 2) }}</strong>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-top text-center">
                        <span class="text-muted small">Payment Status</span><br>
                        @if($sale->paid_amount >= $sale->total_amount)
                            <span class="badge bg-success fs-6 mt-1">Paid</span>
                        @elseif($sale->paid_amount > 0)
                            <span class="badge bg-warning fs-6 mt-1">Partially Paid</span>
                        @else
                            <span class="badge bg-danger fs-6 mt-1">Unpaid</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Payment History --}}
            @if($sale->customerPayments->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>Payment History
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($sale->customerPayments as $payment)
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">{{ $payment->created_at->format('d M Y, h:i A') }}</small>
                                    <br><small>By: {{ $payment->receiver->name ?? 'System' }}</small>
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
                        <strong>{{ $sale->items->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Quantity:</span>
                        <strong>{{ number_format($sale->items->sum('quantity'), 2) }}</strong>
                    </div>
                    @if($sale->returns->count() > 0)
                    <div class="d-flex justify-content-between mb-2 text-warning">
                        <span>Total Returns:</span>
                        <strong>{{ $sale->returns->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between text-warning">
                        <span>Returned Amount:</span>
                        <strong>Rs. {{ number_format($sale->returns->sum('total_return_amount'), 2) }}</strong>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
