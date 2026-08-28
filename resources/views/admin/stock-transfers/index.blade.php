@extends('layouts.admin')

@section('title', 'Stock Transfers')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Stock Transfer Management</h1>
        @can('transfers.create')
        <a href="{{ route('admin.stock-transfers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Create Transfer
        </a>
        @endcan
    </div>

    {{-- Search and Filter --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.stock-transfers.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search by transfer number">
                    </div>
                    <div class="col-md-2">
                        <label for="source_warehouse_id" class="form-label">From Warehouse</label>
                        <select class="form-select" id="source_warehouse_id" name="source_warehouse_id">
                            <option value="">All Warehouses</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('source_warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="destination_warehouse_id" class="form-label">To Warehouse</label>
                        <select class="form-select" id="destination_warehouse_id" name="destination_warehouse_id">
                            <option value="">All Warehouses</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('destination_warehouse_id') == $warehouse->id ? 'selected' : '' }}>
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
                            <option value="pending_approval" {{ request('status') === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="dispatched" {{ request('status') === 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                            <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                            <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Received</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-secondary flex-grow-1">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.stock-transfers.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Transfers Table --}}
    <div class="card">
        <div class="card-body">
            @if($transfers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Transfer #</th>
                            <th>Date</th>
                            <th>From Warehouse</th>
                            <th>To Warehouse</th>
                            <th>Items</th>
                            <th>Qty</th>
                            <th style="width: 120px;">Status</th>
                            <th style="width: 200px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transfers as $transfer)
                        <tr>
                            <td>
                                <div class="fw-semibold">
                                    <i class="bi bi-arrow-left-right me-1"></i>
                                    {{ $transfer->transfer_number }}
                                </div>
                            </td>
                            <td>
                                <small>{{ $transfer->transfer_date->format('M d, Y') }}</small>
                            </td>
                            <td>
                                <small>{{ $transfer->sourceWarehouse?->name ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <small>{{ $transfer->destinationWarehouse?->name ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $transfer->items()->count() }} item(s)</span>
                            </td>
                            <td>
                                <small>{{ number_format($transfer->items()->sum('quantity'), 2) }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $transfer->status_badge }}">{{ $transfer->status_label }}</span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    @can('transfers.view')
                                    <a href="{{ route('admin.stock-transfers.show', $transfer) }}" 
                                       class="btn btn-info" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @endcan
                                    
                                    @if($transfer->isDraft())
                                        @can('transfers.create')
                                        <a href="{{ route('admin.stock-transfers.edit', $transfer) }}" 
                                           class="btn btn-warning" 
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @endcan
                                    @endif

                                    @can('transfers.create')
                                    <form action="{{ route('admin.stock-transfers.destroy', $transfer) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this transfer? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endcan

                                    @if($transfer->canBeCancelled())
                                        @can('transfers.create')
                                        <button type="button" 
                                                class="btn btn-sm btn-warning" 
                                                title="Cancel"
                                                data-bs-toggle="modal"
                                                data-bs-target="#cancelModal{{ $transfer->id }}">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                        @endcan
                                    @endif
                                </div>

                                {{-- Cancel Modal --}}
                                @can('transfers.create')
                                <div class="modal fade" id="cancelModal{{ $transfer->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Cancel Transfer</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('admin.stock-transfers.cancel', $transfer) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <p>Are you sure you want to cancel this transfer?</p>
                                                    <div class="mb-3">
                                                        <label for="reason{{ $transfer->id }}" class="form-label">Reason (optional)</label>
                                                        <textarea class="form-control" 
                                                                  id="reason{{ $transfer->id }}" 
                                                                  name="reason" 
                                                                  rows="3"
                                                                  placeholder="Enter cancellation reason..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-danger">Cancel Transfer</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $transfers->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-arrow-left-right" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">
                    @if(request()->hasAny(['search', 'source_warehouse_id', 'destination_warehouse_id', 'status']))
                        No transfers found matching your criteria.
                    @else
                        No transfers yet. Create your first transfer to get started.
                    @endif
                </p>
                @can('transfers.create')
                @if(!request()->hasAny(['search', 'source_warehouse_id', 'destination_warehouse_id', 'status']))
                <a href="{{ route('admin.stock-transfers.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Create Transfer
                </a>
                @endif
                @endcan
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
