@extends('layouts.admin')

@section('title', 'Sales')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Sales Management</h1>
        @can('sales.create')
        <a href="{{ route('admin.sales.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Create Sale
        </a>
        @endcan
    </div>

    {{-- Search and Filter --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.sales.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search by invoice number or customer">
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
                        <label for="payment_status" class="form-label">Payment</label>
                        <select class="form-select" id="payment_status" name="payment_status">
                            <option value="">All Statuses</option>
                            <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                            <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-secondary flex-grow-1">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-12 d-flex align-items-end">
                        <a href="{{ route('admin.sales.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i> Clear All Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Sales Table --}}
    <div class="card">
        <div class="card-body">
            @if($sales->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Family</th>
                            <th>Warehouse</th>
                            <th class="text-end">Total Amount</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Udhar</th>
                            <th style="width: 100px;">Payment</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 150px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $sale)
                        <tr>
                            <td>
                                <div class="fw-semibold">
                                    <i class="bi bi-file-earmark-text me-1"></i>
                                    {{ $sale->invoice_number }}
                                </div>
                                <small class="text-muted">{{ $sale->items()->count() }} item(s)</small>
                            </td>
                            <td>
                                <small>{{ $sale->sale_date->format('M d, Y') }}</small>
                            </td>
                            <td>
                                @if($sale->customer)
                                    <div>
                                        <strong>{{ $sale->customer->name }}</strong>
                                    </div>
                                    @if($sale->customer->phone)
                                    <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $sale->customer->phone }}</small>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Walk-in</span>
                                @endif
                            </td>
                            <td>
                                @if($sale->family)
                                    <div>
                                        <strong>{{ $sale->family->name }}</strong>
                                    </div>
                                    <small class="text-muted">{{ $sale->family->family_code }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <small>
                                    <i class="bi bi-building me-1"></i>
                                    {{ $sale->warehouse->name }}
                                </small>
                            </td>
                            <td class="text-end">
                                <strong>{{ number_format($sale->total_amount, 2) }}</strong>
                            </td>
                            <td class="text-end">
                                <small>
                                    @if($sale->paid_amount > 0)
                                        <span class="text-success">{{ number_format($sale->paid_amount, 2) }}</span>
                                    @else
                                        <span class="text-muted">0.00</span>
                                    @endif
                                </small>
                            </td>
                            <td class="text-end">
                                <small>
                                    @php
                                        $udhar = $sale->udhar_amount ?? max(0, $sale->total_amount - $sale->paid_amount);
                                    @endphp
                                    @if($udhar > 0)
                                        <span class="text-danger fw-bold">{{ number_format($udhar, 2) }}</span>
                                    @else
                                        <span class="text-success">0.00</span>
                                    @endif
                                </small>
                            </td>
                            <td>
                                @php
                                    // Calculate payment status based on paid_amount and total_amount
                                    if ($sale->paid_amount == 0) {
                                        $paymentStatus = 'Unpaid';
                                        $badgeClass = 'bg-secondary';
                                    } elseif ($sale->paid_amount >= $sale->total_amount) {
                                        $paymentStatus = 'Paid';
                                        $badgeClass = 'bg-success';
                                    } else {
                                        $paymentStatus = 'Partial';
                                        $badgeClass = 'bg-warning';
                                    }
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $paymentStatus }}</span>
                            </td>
                            <td>
                                @if($sale->isDraft())
                                    <span class="badge bg-warning">Draft</span>
                                @elseif($sale->isConfirmed())
                                    <span class="badge bg-success">Confirmed</span>
                                @else
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    @can('sales.view')
                                    <a href="{{ route('admin.sales.show', $sale) }}" 
                                       class="btn btn-info" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @endcan
                                    
                                    @if($sale->isDraft())
                                        @can('sales.update')
                                        <a href="{{ route('admin.sales.edit', $sale) }}" 
                                           class="btn btn-warning" 
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @endcan
                                    @endif
                                    
                                    @can('sales.delete')
                                    <form action="{{ route('admin.sales.destroy', $sale) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-danger" 
                                                title="Delete"
                                                onclick="return confirm('Are you sure you want to delete this sale? This action cannot be undone.');">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>


                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $sales->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-file-earmark-text" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">
                    @if(request()->hasAny(['search', 'customer_id', 'warehouse_id', 'status', 'payment_status']))
                        No sales found matching your criteria.
                    @else
                        No sales yet. Create your first sale to get started.
                    @endif
                </p>
                @can('sales.create')
                @if(!request()->hasAny(['search', 'customer_id', 'warehouse_id', 'status', 'payment_status']))
                <a href="{{ route('admin.sales.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Create Sale
                </a>
                @endif
                @endcan
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
