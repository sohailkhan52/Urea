@extends('layouts.admin')

@section('title', 'Sale Returns')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Sale Returns Management</h1>
        @can('sales.create')
        <a href="{{ route('admin.sale-returns.create') }}" class="btn btn-primary">
            <i class="bi bi-arrow-return-left me-1"></i> Create Return
        </a>
        @endcan
    </div>

    {{-- Search and Filter --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.sale-returns.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search by return #, customer, or sale invoice">
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
                        <label for="date_from" class="form-label">Date From</label>
                        <input type="date" 
                               class="form-control" 
                               id="date_from" 
                               name="date_from" 
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="date_to" class="form-label">Date To</label>
                        <input type="date" 
                               class="form-control" 
                               id="date_to" 
                               name="date_to" 
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-secondary flex-grow-1">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-12">
                        <a href="{{ route('admin.sale-returns.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i> Clear All Filters
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
                            <th>Original Sale</th>
                            <th>Customer</th>
                            <th>Family</th>
                            <th>Warehouse</th>
                            <th class="text-end">Return Amount</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($returns as $return)
                        <tr>
                            <td>
                                <div class="fw-semibold">
                                    <i class="bi bi-arrow-return-left me-1"></i>
                                    {{ $return->return_number }}
                                </div>
                                <small class="text-muted">{{ $return->items()->count() }} item(s)</small>
                            </td>
                            <td>
                                <small>{{ $return->return_date->format('M d, Y') }}</small>
                            </td>
                            <td>
                                <div>
                                    <i class="bi bi-file-earmark-text me-1"></i>
                                    <a href="{{ route('admin.sales.show', $return->sale_id) }}" 
                                       class="text-decoration-none">
                                        {{ $return->sale->invoice_number }}
                                    </a>
                                </div>
                                <small class="text-muted">{{ $return->sale->sale_date->format('M d, Y') }}</small>
                            </td>
                            <td>
                                @if($return->customer)
                                    <div>{{ $return->customer->name }}</div>
                                    @if($return->customer->phone)
                                    <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $return->customer->phone }}</small>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Walk-in</span>
                                @endif
                            </td>
                            <td>
                                @if($return->family)
                                    <div>{{ $return->family->name }}</div>
                                    <small class="text-muted">{{ $return->family->family_code }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <small>
                                    <i class="bi bi-building me-1"></i>
                                    {{ $return->warehouse->name }}
                                </small>
                            </td>
                            <td class="text-end">
                                <strong>Rs. {{ number_format($return->total_return_amount, 2) }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-{{ $return->status_badge }}">
                                    {{ $return->status_label }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    @can('sales.view')
                                    <a href="{{ route('admin.sale-returns.show', $return) }}" 
                                       class="btn btn-outline-primary"
                                       title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @endcan
                                    
                                    @if($return->canBeConfirmed())
                                        @can('sales.approve')
                                        <button type="button" 
                                                class="btn btn-outline-success" 
                                                onclick="confirmReturn({{ $return->id }})"
                                                title="Confirm Return">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                        @endcan
                                    @endif
                                    
                                    @if($return->canBeCancelled())
                                        @can('sales.cancel')
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                onclick="cancelReturn({{ $return->id }})"
                                                title="Cancel Return">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $returns->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-inbox display-4 text-muted"></i>
                <p class="text-muted mt-3">No sale returns found</p>
                @can('sales.create')
                <a href="{{ route('admin.sale-returns.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Create First Return
                </a>
                @endcan
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Confirm Return Modal --}}
<div class="modal fade" id="confirmReturnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Return</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Confirming this return will:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Add returned items back to warehouse stock</li>
                        <li>Adjust customer balance (reduce udhar or create credit)</li>
                        <li>This action cannot be undone</li>
                    </ul>
                </div>
                <p>Are you sure you want to confirm this return?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="confirmReturnForm" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i> Confirm Return
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Cancel Return Modal --}}
<div class="modal fade" id="cancelReturnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Return</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="cancelReturnForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="cancel_reason" class="form-label">Cancellation Reason (Optional)</label>
                        <textarea class="form-control" 
                                  id="cancel_reason" 
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

@push('scripts')
<script>
function confirmReturn(returnId) {
    const form = document.getElementById('confirmReturnForm');
    form.action = `/admin/sale-returns/${returnId}/confirm`;
    
    const modal = new bootstrap.Modal(document.getElementById('confirmReturnModal'));
    modal.show();
}

function cancelReturn(returnId) {
    const form = document.getElementById('cancelReturnForm');
    form.action = `/admin/sale-returns/${returnId}/cancel`;
    
    const modal = new bootstrap.Modal(document.getElementById('cancelReturnModal'));
    modal.show();
}
</script>
@endpush
