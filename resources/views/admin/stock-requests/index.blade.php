@extends('layouts.admin')

@section('title', 'Stock Requests')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Stock Request Management</h1>
        @can('stock_requests.create')
        <a href="{{ route('admin.stock-requests.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Create Stock Request
        </a>
        @endcan
    </div>

    {{-- Search and Filter --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.stock-requests.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search by request number">
                    </div>
                    @if(auth()->user()->isSuperAdmin())
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
                    @endif
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            @foreach($statuses as $statusKey => $statusLabel)
                            <option value="{{ $statusKey }}" {{ request('status') === $statusKey ? 'selected' : '' }}>
                                {{ $statusLabel }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="priority" class="form-label">Priority</label>
                        <select class="form-select" id="priority" name="priority">
                            <option value="">All Priorities</option>
                            @foreach($priorities as $priorityKey => $priorityLabel)
                            <option value="{{ $priorityKey }}" {{ request('priority') === $priorityKey ? 'selected' : '' }}>
                                {{ $priorityLabel }}
                            </option>
                            @endforeach
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
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-secondary flex-grow-1">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.stock-requests.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Stock Requests Table --}}
    <div class="card">
        <div class="card-body">
            @if($requests->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Request #</th>
                            <th>Date</th>
                            <th>Warehouse</th>
                            <th>Requested By</th>
                            <th>Items</th>
                            <th>Priority</th>
                            <th style="width: 120px;">Status</th>
                            <th style="width: 200px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $request)
                        <tr>
                            <td>
                                <div class="fw-semibold">
                                    <i class="bi bi-box-seam me-1"></i>
                                    {{ $request->request_number }}
                                </div>
                            </td>
                            <td>
                                <small>{{ $request->created_at->format('M d, Y') }}</small>
                                <br>
                                <small class="text-muted">{{ $request->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <small>
                                    <i class="bi bi-building me-1"></i>
                                    {{ $request->warehouse->name }}
                                </small>
                            </td>
                            <td>
                                <small>{{ $request->requester->name }}</small>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $request->items()->count() }} item(s)</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $request->priority_badge }}">{{ $request->priority_label }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $request->status_badge }}">{{ $request->status_label }}</span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    @can('stock_requests.view')
                                    <a href="{{ route('admin.stock-requests.show', $request) }}" 
                                       class="btn btn-info" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @endcan
                                    
                                    @if($request->canBeEdited())
                                        @can('stock_requests.update')
                                        <a href="{{ route('admin.stock-requests.edit', $request) }}" 
                                           class="btn btn-warning" 
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @endcan
                                    @endif
                                    
                                    @if($request->isPending())
                                        @can('stock_requests.delete')
                                        <form action="{{ route('admin.stock-requests.destroy', $request) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-danger" 
                                                    title="Delete"
                                                    onclick="return confirm('Are you sure you want to delete this stock request? This action cannot be undone.');">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    @endif
                                </div>

                                {{-- Reject Modal --}}
                                @can('stock_requests.approve')
                                @if($request->canBeRejected())
                                <div class="modal fade" id="rejectModal{{ $request->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Reject Stock Request</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('admin.stock-requests.reject', $request) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <p>Are you sure you want to reject this stock request?</p>
                                                    <div class="alert alert-info mb-3">
                                                        <i class="bi bi-info-circle me-1"></i>
                                                        Request: {{ $request->request_number }}<br>
                                                        Warehouse: {{ $request->warehouse->name }}<br>
                                                        Requested by: {{ $request->requester->name }}
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="rejection_reason{{ $request->id }}" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                                        <textarea class="form-control" 
                                                                  id="rejection_reason{{ $request->id }}" 
                                                                  name="rejection_reason" 
                                                                  rows="3"
                                                                  required
                                                                  placeholder="Enter reason for rejection..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-danger">Reject Request</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @endcan

                                {{-- Cancel Modal --}}
                                @can('stock_requests.cancel')
                                @if($request->canBeCancelled())
                                <div class="modal fade" id="cancelModal{{ $request->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Cancel Stock Request</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('admin.stock-requests.cancel', $request) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <p>Are you sure you want to cancel this stock request?</p>
                                                    <div class="mb-3">
                                                        <label for="cancellation_reason{{ $request->id }}" class="form-label">Reason (optional)</label>
                                                        <textarea class="form-control" 
                                                                  id="cancellation_reason{{ $request->id }}" 
                                                                  name="cancellation_reason" 
                                                                  rows="3"
                                                                  placeholder="Enter cancellation reason..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-warning">Cancel Request</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $requests->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-box-seam" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">
                    @if(request()->hasAny(['search', 'warehouse_id', 'status', 'priority', 'date_from', 'date_to']))
                        No stock requests found matching your criteria.
                    @else
                        No stock requests yet. Create your first stock request to get started.
                    @endif
                </p>
                @can('stock_requests.create')
                @if(!request()->hasAny(['search', 'warehouse_id', 'status', 'priority', 'date_from', 'date_to']))
                <a href="{{ route('admin.stock-requests.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Create Stock Request
                </a>
                @endif
                @endcan
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
