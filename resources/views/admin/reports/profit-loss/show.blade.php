@extends('layouts.admin')

@section('title', 'Profit & Loss Detail - ' . $sale->invoice_number)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.profit-loss.index') }}">Profit & Loss Report</a></li>
    <li class="breadcrumb-item active">{{ $sale->invoice_number }}</li>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="bi bi-graph-up-arrow me-2"></i>Profit & Loss Analysis
            </h1>
            <p class="text-muted mb-0">Invoice: {{ $sale->invoice_number }}</p>
        </div>
        <div>
            <a href="{{ route('admin.reports.profit-loss.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to P&L Report
            </a>
        </div>
    </div>

    {{-- Profit/Loss Summary Card --}}
    @if($sale->has_cost_data)
    <div class="card mb-4 border-{{ $sale->profit_status === 'profit' ? 'success' : ($sale->profit_status === 'loss' ? 'danger' : 'secondary') }}">
        <div class="card-header bg-{{ $sale->profit_status === 'profit' ? 'success' : ($sale->profit_status === 'loss' ? 'danger' : 'secondary') }} text-white">
            <h5 class="mb-0">
                <i class="bi bi-{{ $sale->profit_status === 'profit' ? 'arrow-up-circle' : ($sale->profit_status === 'loss' ? 'arrow-down-circle' : 'dash-circle') }} me-2"></i>
                @if($sale->profit_status === 'profit')
                    This Sale is Profitable
                @elseif($sale->profit_status === 'loss')
                    This Sale Made a Loss
                @else
                    This Sale Broke Even
                @endif
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                        <small class="text-muted d-block mb-1">Net Revenue</small>
                        <h4 class="mb-0 text-success">Rs. {{ number_format($sale->net_revenue, 2) }}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                        <small class="text-muted d-block mb-1">Cost of Goods</small>
                        <h4 class="mb-0 text-danger">Rs. {{ number_format($sale->total_cogs, 2) }}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                        <small class="text-muted d-block mb-1">Gross Profit/Loss</small>
                        <h4 class="mb-0 text-{{ $sale->profit_status === 'profit' ? 'success' : ($sale->profit_status === 'loss' ? 'danger' : 'secondary') }}">
                            Rs. {{ number_format(abs($sale->gross_profit), 2) }}
                        </h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                        <small class="text-muted d-block mb-1">Profit Margin</small>
                        <h4 class="mb-0 text-info">{{ number_format($sale->profit_margin_percentage, 2) }}%</h4>
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
    @else
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Cost Data Unavailable:</strong> Profit/loss cannot be calculated for this sale because cost price information is not available. 
        This may be an older sale created before cost tracking was implemented.
    </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            {{-- Sale Information --}}
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-receipt me-2"></i>Sale Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2"><i class="bi bi-person me-1"></i>Customer</small>
                            @if($sale->customer)
                                <p class="mb-0 fw-bold">{{ $sale->customer->name }}</p>
                                @if($sale->customer->phone)
                                    <small class="text-muted">{{ $sale->customer->phone }}</small>
                                @endif
                            @else
                                <p class="mb-0">{{ $sale->walkin_customer_name ?? 'Walk-in Customer' }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2"><i class="bi bi-calendar me-1"></i>Sale Date</small>
                            <p class="mb-0 fw-bold">{{ $sale->sale_date->format('d M Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2"><i class="bi bi-building me-1"></i>Warehouse</small>
                            <p class="mb-0 fw-bold">{{ $sale->warehouse->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2"><i class="bi bi-person-badge me-1"></i>Created By</small>
                            <p class="mb-0 fw-bold">{{ $sale->creator->name }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Item Profit Breakdown --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-list-check me-2"></i>Item Profit Breakdown
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Cost Price</th>
                                    <th class="text-end">Revenue</th>
                                    <th class="text-end">COGS</th>
                                    <th class="text-end">Profit/Loss</th>
                                    <th class="text-end">Margin</th>
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
                                        {{ number_format($item->net_quantity, 2) }}
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
                                    <td class="text-end">Rs. {{ number_format($item->net_revenue, 2) }}</td>
                                    <td class="text-end">
                                        @if($item->cost_price)
                                            Rs. {{ number_format($item->COGS, 2) }}
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($item->cost_price)
                                            @if($item->gross_profit >= 0)
                                                <span class="text-success fw-bold">
                                                    <i class="bi bi-arrow-up-circle me-1"></i>Rs. {{ number_format($item->gross_profit, 2) }}
                                                </span>
                                            @else
                                                <span class="text-danger fw-bold">
                                                    <i class="bi bi-arrow-down-circle me-1"></i>Rs. {{ number_format(abs($item->gross_profit), 2) }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($item->cost_price && $item->net_revenue > 0)
                                            <span class="badge bg-{{ $item->profit_margin >= 20 ? 'success' : ($item->profit_margin >= 10 ? 'warning' : ($item->profit_margin >= 0 ? 'info' : 'danger')) }}">
                                                {{ number_format($item->profit_margin, 1) }}%
                                            </span>
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Sale Returns (if any) --}}
            @if($sale->returns->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-arrow-return-left me-2"></i>Sale Returns
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

        {{-- Right Sidebar --}}
        <div class="col-lg-4">
            {{-- Financial Summary --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-wallet2 me-2"></i>Financial Summary
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

                    <div class="border-top pt-3 mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-success">Paid:</span>
                            <strong class="text-success">Rs. {{ number_format($sale->paid_amount, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-danger">Outstanding:</span>
                            <strong class="text-danger">Rs. {{ number_format($sale->due_amount, 2) }}</strong>
                        </div>
                    </div>

                    <div class="text-center pt-3 border-top">
                        <span class="text-muted small">Payment Status</span><br>
                        @if($sale->paid_amount >= $sale->total_amount)
                            <span class="badge bg-success fs-6 mt-1">Paid</span>
                        @elseif($sale->paid_amount > 0)
                            <span class="badge bg-warning fs-6 mt-1">Partial</span>
                        @else
                            <span class="badge bg-danger fs-6 mt-1">Unpaid</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Profit Metrics --}}
            @if($sale->has_cost_data)
            <div class="card mb-4 border-{{ $sale->profit_status === 'profit' ? 'success' : ($sale->profit_status === 'loss' ? 'danger' : 'secondary') }}">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up me-2"></i>Profit Metrics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Net Revenue:</span>
                        <strong class="text-success">Rs. {{ number_format($sale->net_revenue, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total COGS:</span>
                        <strong class="text-danger">Rs. {{ number_format($sale->total_cogs, 2) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-bold">Gross Profit:</span>
                        <strong class="text-{{ $sale->profit_status === 'profit' ? 'success' : ($sale->profit_status === 'loss' ? 'danger' : 'secondary') }}">
                            Rs. {{ number_format(abs($sale->gross_profit), 2) }}
                        </strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Margin:</span>
                        <strong class="text-info">{{ number_format($sale->profit_margin_percentage, 2) }}%</strong>
                    </div>
                </div>
            </div>
            @endif

            {{-- Statistics --}}
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart me-2"></i>Statistics
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
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
