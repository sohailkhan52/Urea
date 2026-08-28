@extends('layouts.admin')

@section('title', 'Expense Report')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Expense Report</h1>
            <p class="text-muted mb-0">Comprehensive expense analysis and tracking</p>
        </div>
        <div class="d-flex gap-2 no-print">
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Print
            </button>
        </div>
    </div>

    {{-- Filter Panel --}}
    <div class="card mb-4 no-print">
        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
            <span class="fw-semibold"><i class="bi bi-funnel me-2"></i>Filters</span>
            <button class="btn btn-sm btn-link p-0 text-secondary" type="button"
                    data-bs-toggle="collapse" data-bs-target="#filterPanel">
                <i class="bi bi-chevron-down"></i>
            </button>
        </div>
        <div class="collapse show" id="filterPanel">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reports.expenses') }}">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Date From</label>
                            <input type="date" name="date_from" class="form-control form-control-sm"
                                   value="{{ request('date_from', now()->startOfMonth()->toDateString()) }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Date To</label>
                            <input type="date" name="date_to" class="form-control form-control-sm"
                                   value="{{ request('date_to', now()->toDateString()) }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Search Expense Item</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="e.g., Electricity, Transport..."
                                   value="{{ request('search') }}">
                        </div>

                        @if(auth()->user()->isSuperAdmin())
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Warehouse</label>
                            <select name="warehouse_id" class="form-select form-select-sm">
                                <option value="">All Warehouses</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}"
                                        {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i>Apply
                        </button>
                        <a href="{{ route('admin.reports.expenses') }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Summary Card --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-danger border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Expenses</p>
                    <h4 class="mb-0 fw-bold text-danger">Rs.&nbsp;{{ number_format($totalExpenses, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-info border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Number of Expenses</p>
                    <h4 class="mb-0 fw-bold text-info">{{ $expenses->total() }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Expenses Table --}}
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Expense Records</h5>
            <span class="text-muted small">
                {{ request('date_from', now()->startOfMonth()->toDateString()) }} 
                to 
                {{ request('date_to', now()->toDateString()) }}
            </span>
        </div>
        <div class="card-body p-0">
            @if($expenses->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Expense Item</th>
                            <th class="text-end">Cost</th>
                            <th>Date</th>
                            <th class="text-center no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenses as $expense)
                        <tr>
                            <td class="ps-3 text-muted">
                                {{ ($expenses->currentPage() - 1) * $expenses->perPage() + $loop->iteration }}
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $expense->expense_item }}</div>
                                @if($expense->warehouse)
                                    <small class="text-muted">{{ $expense->warehouse->name }}</small>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-danger">Rs.&nbsp;{{ number_format($expense->cost, 2) }}</span>
                            </td>
                            <td class="text-nowrap">
                                {{ $expense->created_at->format('d-M-Y H:i A') }}
                            </td>
                            <td class="text-center no-print">
                                @can('expenses.edit', $expense)
                                <a href="{{ route('admin.expenses.edit', $expense) }}"
                                   class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan
                                @can('expenses.delete', $expense)
                                <form action="{{ route('admin.expenses.destroy', $expense) }}" 
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete this expense?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-semibold">
                        <tr>
                            <td colspan="2" class="ps-3 text-end">Period Total:</td>
                            <td class="text-end text-danger">
                                Rs.&nbsp;{{ number_format($expenses->getCollection()->sum('cost'), 2) }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="card-footer bg-white no-print">
                {{ $expenses->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size:3rem"></i>
                <h5 class="mt-3 text-muted">No Expenses Found</h5>
                <p class="text-muted">No expenses match the current filters for the selected period.</p>
            </div>
            @endif
        </div>
    </div>

</div>

@push('styles')
<style>
@media print {
    * { color: #000000 !important; }
    .no-print { display:none !important; }
    .card { border:none !important; box-shadow:none !important; }
}
    .table { font-size:10px; }
    body { font-size:11px; }
}
</style>
@endpush

@endsection
