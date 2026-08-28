@extends('layouts.admin')

@section('title', 'Expenses')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Expense Management</h1>
            <p class="text-muted mb-0">View, edit, and manage all expenses</p>
        </div>
        <div class="d-flex gap-2 no-print">
            @can('expenses.create')
            <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i>Add Expense
            </a>
            @endcan
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
                <form method="GET" action="{{ route('admin.expenses.index') }}">
                    <div class="row g-3">
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
                        <a href="{{ route('admin.expenses.index') }}"
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
    </div>

    {{-- Expenses Table --}}
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Expenses</h5>
            <span class="text-muted small">{{ $expenses->total() }} total</span>
        </div>
        <div class="card-body p-0">
            @if($expenses->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Expense Item</th>
                            <th>Cost</th>
                            <th>Date</th>
                            <th class="text-center no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenses as $expense)
                        <tr>
                            <td>
                                {{ ($expenses->currentPage() - 1) * $expenses->perPage() + $loop->iteration }}
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $expense->expense_item }}</div>
                                @if($expense->warehouse)
                                    <small class="text-muted">{{ $expense->warehouse->name }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold text-danger">Rs.&nbsp;{{ number_format($expense->cost, 2) }}</span>
                            </td>
                            <td class="text-nowrap">
                                {{ $expense->created_at->format('d-M-Y H:i A') }}
                                <br>
                                <small class="text-muted">by {{ $expense->creator->name ?? 'Unknown' }}</small>
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
                            <td colspan="2">Page Total:</td>
                            <td>
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
                <p class="text-muted">No expenses match the current filters.</p>
                @can('expenses.create')
                <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary btn-sm mt-2">
                    <i class="bi bi-plus-circle me-1"></i>Create First Expense
                </a>
                @endcan
            </div>
            @endif
        </div>
    </div>

</div>

@push('styles')
<style>
    /* Expense table equal column widths - 20% each */
    .table {
        table-layout: fixed;
    }

    .table thead th, .table tbody td {
        vertical-align: middle;
        width: 20%;
        padding: 10px 8px;
        word-break: break-word;
    }

    /* Column specific alignment */
    .table th:nth-child(1),
    .table td:nth-child(1) {
        text-align: center;
        width: 20%;
    }

    .table th:nth-child(2),
    .table td:nth-child(2) {
        text-align: left;
        width: 20%;
    }

    .table th:nth-child(3),
    .table td:nth-child(3) {
        text-align: left;
        width: 20%;
    }

    .table th:nth-child(4),
    .table td:nth-child(4) {
        text-align: left;
        width: 20%;
    }

    .table th:nth-child(5),
    .table td:nth-child(5) {
        text-align: center;
        width: 20%;
    }

    /* Expense item formatting */
    .table td:nth-child(2) .fw-semibold {
        margin-bottom: 4px;
        display: block;
        word-break: break-word;
    }

    .table td:nth-child(2) small {
        display: block;
        margin-top: 2px;
        font-size: 0.8rem;
        word-break: break-word;
    }

    /* Date column formatting */
    .table td:nth-child(4) {
        font-size: 0.9rem;
        line-height: 1.3;
    }

    .table td:nth-child(4) small {
        display: block;
        margin-top: 2px;
        font-size: 0.75rem;
    }

    /* Actions buttons */
    .table td:nth-child(5) .btn {
        padding: 0.35rem 0.5rem;
        margin: 0 2px;
        font-size: 0.85rem;
    }

    .table td:nth-child(5) .btn i {
        font-size: 0.9rem;
    }

    .table td:nth-child(5) form {
        display: inline;
        margin: 0;
    }

    /* Responsive adjustments for tablets */
    @media (max-width: 768px) {
        .table thead th, .table tbody td {
            padding: 8px 6px;
            font-size: 0.85rem;
        }

        .table td:nth-child(2) .fw-semibold {
            font-size: 0.85rem;
        }

        .table td:nth-child(2) small {
            font-size: 0.7rem;
        }

        .table td:nth-child(4) {
            font-size: 0.8rem;
        }

        .table td:nth-child(4) small {
            font-size: 0.65rem;
        }

        .table td:nth-child(5) .btn {
            padding: 0.3rem 0.4rem;
            margin: 0 1px;
            font-size: 0.75rem;
        }
    }

    /* Responsive adjustments for mobile */
    @media (max-width: 425px) {
        .table {
            font-size: 0.75rem;
        }

        .table thead th, .table tbody td {
            padding: 6px 4px;
            font-size: 0.7rem;
        }

        .table td:nth-child(1) {
            font-size: 0.75rem;
        }

        .table td:nth-child(2) .fw-semibold {
            font-size: 0.75rem;
            margin-bottom: 2px;
        }

        .table td:nth-child(2) small {
            font-size: 0.65rem;
        }

        .table td:nth-child(3) {
            font-size: 0.75rem;
        }

        .table td:nth-child(4) {
            font-size: 0.7rem;
            line-height: 1.2;
        }

        .table td:nth-child(4) small {
            font-size: 0.6rem;
        }

        .table td:nth-child(5) .btn {
            padding: 0.25rem 0.35rem;
            margin: 0 1px;
            font-size: 0.65rem;
        }

        .table td:nth-child(5) .btn i {
            font-size: 0.8rem;
        }
    }

@media print {
    .no-print { display:none !important; }
    .card { border:none !important; box-shadow:none !important; }
    .table { font-size:10px; }
    body { font-size:11px; }
}
</style>
@endpush

@endsection
