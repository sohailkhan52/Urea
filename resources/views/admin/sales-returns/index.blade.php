@extends('layouts.admin')

@section('title', 'Sales Returns')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Sales Returns</h1>
        @can('sales.create')
        <a href="{{ route('admin.sales.returns.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Create Return
        </a>
        @endcan
    </div>

    {{-- Search and Filter --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.sales.returns.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search by return# or invoice#">
                    </div>
                    <div class="col-md-2">
                        <label for="customer_id" class="form-label">Customer</label>
                        <select class="form-select" id="customer_id" name="customer_id">
                            <option value="">All Customers</option>
                            @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="warehouse_id" class="form-label">Warehouse</label>
                        <select class="form-select" id="warehouse_id" name="warehouse_id">
                            <option value="">All Warehouses</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="payment_status" class="form-label">Payment Status</label>
                        <select class="form-select" id="payment_status" name="payment_status">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="refunded" {{ request('payment_status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                            <option value="credited" {{ request('payment_status') === 'credited' ? 'selected' : '' }}>Credited</option>
                            <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-secondary flex-grow-1">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <a href="{{ route('admin.sales.returns.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i> Clear Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Returns Table --}}
    <div class="card">
        <div class="card-body">
            @if($returns->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Return #</th>
                            <th>Date</th>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Warehouse</th>
                            <th>Items</th>
                            <th class="text-end">Return Amount</th>
                            <th class="text-end">Refund</th>
                            <th class="text-end">Credit</th>
                            <th>Payment Status</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($returns as $return)
                        <tr>
                            <td>
                                <a href="{{ route('admin.sales.returns.show', $return) }}" class="text-decoration-none">
                                    <strong>{{ $return->return_number }}</strong>
                                </a>
                            </td>
                            <td>{{ $return->return_date->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('admin.sales.show', $return->sale) }}">
                                    {{ $return->sale->invoice_number }}
                                </a>
                            </td>
                            <td>{{ $return->sale->customer->name }}</td>
                            <td>{{ $return->sale->warehouse->name }}</td>
                            <td>{{ $return->items_count }}</td>
                            <td class="text-end">Rs. {{ number_format($return->total_amount, 2) }}</td>
                            <td class="text-end">Rs. {{ number_format($return->refund_amount ?? 0, 2) }}</td>
                            <td class="text-end">Rs. {{ number_format($return->credit_amount ?? 0, 2) }}</td>
                            <td>
                                @if($return->payment_status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($return->payment_status === 'refunded')
                                    <span class="badge bg-success">Refunded</span>
                                @elseif($return->payment_status === 'credited')
                                    <span class="badge bg-info">Credited</span>
                                @elseif($return->payment_status === 'partial')
                                    <span class="badge bg-secondary">Partial</span>
                                @endif
                            </td>
                            <td>
                                @if($return->status === 'draft')
                                    <span class="badge bg-secondary">Draft</span>
                                @elseif($return->status === 'confirmed')
                                    <span class="badge bg-success">Confirmed</span>
                                @elseif($return->status === 'cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.sales.returns.show', $return) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($return->status === 'draft' && auth()->user()->can('sales.cancel'))
                                <form action="{{ route('admin.sales.returns.cancel', $return) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="reason" value="Cancelled from list">
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Cancel this return?')">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $returns->links() }}
            </div>
            @else
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox display-1"></i>
                <p class="mt-3">No sales returns found</p>
                @can('sales.create')
                <a href="{{ route('admin.sales.returns.create') }}" class="btn btn-primary mt-2">
                    <i class="bi bi-plus-circle me-1"></i> Create First Return
                </a>
                @endcan
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
