@extends('layouts.admin')

@section('title', 'Stock Movements')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Stock Movement History</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 mt-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
                        <li class="breadcrumb-item active">Movement History</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Inventory
            </a>
        </div>
    </div>

    {{-- Product and Warehouse Info --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Product</h6>
                    <h5 class="mb-1">{{ $product->name }}</h5>
                    <p class="mb-0">
                        <code class="bg-light px-2 py-1 rounded me-2">{{ $product->sku }}</code>
                        <span class="text-muted">{{ $product->weight_display }}</span>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Warehouse</h6>
                    <h5 class="mb-1">{{ $warehouse->name }}</h5>
                    <p class="mb-0">
                        <code class="bg-light px-2 py-1 rounded me-2">{{ $warehouse->code }}</code>
                        <span class="badge bg-success">
                            Current: {{ number_format($currentStock, 2) }} {{ $product->weight_unit }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.inventory.movements') }}" method="GET">
                <input type="hidden" name="warehouse_id" value="{{ $warehouse->id }}">
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="type" class="form-label">Movement Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">All Types</option>
                            @foreach($movementTypes as $value => $label)
                            <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">Date From</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">Date To</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-secondary">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.inventory.movements', ['warehouse_id' => $warehouse->id, 'product_id' => $product->id]) }}" 
                           class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Movements Table --}}
    <div class="card">
        <div class="card-body">
            @if($movements->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Type</th>
                            <th class="text-end">Quantity In</th>
                            <th class="text-end">Quantity Out</th>
                            <th class="text-end">Balance</th>
                            <th class="text-end">Unit Cost</th>
                            <th>Remarks</th>
                            <th>Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movements as $movement)
                        <tr>
                            <td>
                                <div>{{ $movement->created_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $movement->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                @if($movement->isStockIn())
                                <span class="badge bg-success">
                                    <i class="bi bi-arrow-down-circle me-1"></i>{{ $movement->type_label }}
                                </span>
                                @else
                                <span class="badge bg-danger">
                                    <i class="bi bi-arrow-up-circle me-1"></i>{{ $movement->type_label }}
                                </span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($movement->quantity_in > 0)
                                <span class="text-success fw-bold">+{{ number_format($movement->quantity_in, 2) }}</span>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($movement->quantity_out > 0)
                                <span class="text-danger fw-bold">-{{ number_format($movement->quantity_out, 2) }}</span>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <strong>{{ number_format($movement->balance_after, 2) }}</strong>
                            </td>
                            <td class="text-end">
                                @if($movement->unit_cost)
                                <small>Rs. {{ number_format($movement->unit_cost, 2) }}</small>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($movement->remarks)
                                <small class="text-muted">{{ Str::limit($movement->remarks, 40) }}</small>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <small>
                                    <i class="bi bi-person me-1"></i>
                                    {{ $movement->creator->name }}
                                </small>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $movements->links() }}
            </div>

            {{-- Legend --}}
            <div class="mt-3 p-3 bg-light rounded">
                <h6 class="mb-2">
                    <i class="bi bi-info-circle me-1"></i> Movement Legend
                </h6>
                <div class="row g-2 small">
                    <div class="col-md-4">
                        <span class="badge bg-success me-1">Stock In</span>
                        Opening Stock, Purchase, Customer Return, Transfer In, Adjustment In
                    </div>
                    <div class="col-md-4">
                        <span class="badge bg-danger me-1">Stock Out</span>
                        Sale, Supplier Return, Transfer Out, Adjustment Out, Damaged, Expired
                    </div>
                    <div class="col-md-4">
                        <strong>Balance:</strong> Running balance after each movement
                    </div>
                </div>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-clock-history" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">
                    No stock movements found for this product in this warehouse.
                </p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
