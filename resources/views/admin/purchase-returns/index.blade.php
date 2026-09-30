@extends('layouts.admin')

@section('content')
<div class="container-fluid purchase-returns-index-page">
    <div class="page-header">
        <div class="row align-items-center return-index-heading-row">
            <div class="col-md-6 return-index-title-col">
                <h1 class="page-title">Purchase Returns</h1>
            </div>
            <div class="col-md-6 text-end return-index-action-col">
                <a href="{{ route('admin.purchase-returns.create') }}" class="btn btn-primary return-index-create-button">
                    <i class="bi bi-plus-lg"></i> Create Return
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.purchase-returns.index') }}" class="row g-3">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="col-md-3">
                      <label for="purchase_returns_search" class="form-label">Search</label>
                    <input type="text" 
                          id="purchase_returns_search"
                           name="search" 
                           class="form-control" 
                           placeholder="Search..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label for="purchase_returns_status" class="form-label">Status</label>
                    <select id="purchase_returns_status" name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="purchase_date_from" class="form-label">Date From</label>
                    <input type="date" id="purchase_date_from" name="date_from" class="form-control" value="{{ request('date_from') }}" aria-label="Date From">
                </div>
                <div class="col-md-2">
                    <label for="purchase_date_to" class="form-label">Date To</label>
                    <input type="date" id="purchase_date_to" name="date_to" class="form-control" value="{{ request('date_to') }}" aria-label="Date To">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2 return-filter-actions">
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.purchase-returns.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-end align-items-center mb-2">
        <form action="{{ route('admin.purchase-returns.index') }}" method="GET" class="d-flex align-items-center gap-2">
            @foreach(request()->except(['page', 'per_page']) as $key => $value)
                @if(is_scalar($value))
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <label for="purchase-returns-per-page" class="small text-muted mb-0">Per Page</label>
            <select id="purchase-returns-per-page" name="per_page" class="form-select form-select-sm" style="width: 82px;" onchange="this.form.submit()">
                @foreach([10, 25, 50, 100] as $option)
                    <option value="{{ $option }}" @selected($perPage == $option)>{{ $option }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Returns Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Return #</th>
                            <th>Purchase #</th>
                            <th>Supplier</th>
                            <th>Return Date</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Refund Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $return)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.purchase-returns.show', $return) }}">
                                        <strong>{{ $return->return_number }}</strong>
                                    </a>
                                </td>
                                <td>
                                    @if($return->purchase)
                                        <a href="{{ route('admin.purchases.show', $return->purchase) }}">
                                            {{ $return->purchase->purchase_number }}
                                        </a>
                                    @else
                                        <span class="text-muted">(Deleted Purchase)</span>
                                    @endif
                                </td>
                                <td>{{ $return->supplier->name }}</td>
                                <td>{{ $return->return_date->format('d M Y') }}</td>
                                <td>Rs. {{ number_format($return->total_amount, 0) }}</td>
                                <td>
                                    <span class="badge bg-{{ $return->status_badge }}">
                                        {{ $return->status_label }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $return->refund_status_badge }}">
                                        {{ $return->refund_status_label }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.purchase-returns.show', $return) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox"></i> No purchase returns found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $returns->links() }}
            </div>
        </div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .purchase-returns-index-page {
            padding-right: 10px;
            padding-left: 10px;
        }

        .purchase-returns-index-page .page-header {
            padding: 10px 12px;
            margin: -12px -10px 14px;
        }

        .purchase-returns-index-page .return-index-heading-row {
            display: flex;
            flex-wrap: nowrap;
            gap: 8px;
            margin: 0;
        }

        .purchase-returns-index-page .return-index-title-col,
        .purchase-returns-index-page .return-index-action-col {
            width: auto;
            max-width: none;
            padding: 0;
        }

        .purchase-returns-index-page .return-index-title-col {
            flex: 1 1 auto;
            min-width: 0;
        }

        .purchase-returns-index-page .page-title {
            margin: 0;
            font-size: clamp(1.05rem, 4.5vw, 1.3rem);
            line-height: 1.2;
            white-space: nowrap;
            overflow-y: hidden;
            scrollbar-width: none;
        }

        .purchase-returns-index-page .page-title::-webkit-scrollbar {
            display: none;
        }

        .purchase-returns-index-page .return-index-action-col {
            flex: 0 0 auto;
            text-align: right !important;
        }

        .purchase-returns-index-page .return-index-create-button {
            min-height: 36px;
            padding: 6px 9px;
            font-size: 0.76rem;
            white-space: nowrap;
        }

        .purchase-returns-index-page .card {
            margin-bottom: 12px !important;
        }

        .purchase-returns-index-page .card-body {
            padding: 12px;
        }

        .purchase-returns-index-page .form-control,
        .purchase-returns-index-page .form-select {
            min-height: 38px;
            padding: 6px 9px;
            font-size: 14px;
        }

        .purchase-returns-index-page .form-label {
            margin-bottom: 3px;
            font-size: 0.78rem;
        }

        .purchase-returns-index-page form.row {
            row-gap: 4px !important;
        }

        .purchase-returns-index-page form.row > [class*="col-"] {
            padding-right: 4px;
            padding-left: 4px;
        }

        .purchase-returns-index-page form.row .btn {
            min-height: 36px;
            padding: 5px 8px;
            font-size: 0.76rem;
        }

        .purchase-returns-index-page .return-filter-actions {
            display: flex;
            width: 100%;
            gap: 8px;
        }

        .purchase-returns-index-page .return-filter-actions .btn {
            flex: 1 1 0;
            white-space: nowrap;
        }

        .purchase-returns-index-page .return-filter-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .purchase-returns-index-page .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .purchase-returns-index-page table {
            min-width: 720px;
            margin-bottom: 0;
            font-size: 0.75rem;
        }

        .purchase-returns-index-page table th,
        .purchase-returns-index-page table td {
            padding: 7px 8px;
            vertical-align: middle;
        }

        .purchase-returns-index-page .badge {
            font-size: 0.65rem;
        }

        .purchase-returns-index-page .btn-sm {
            padding: 4px 7px;
            font-size: 0.7rem;
        }

        .purchase-returns-index-page .d-flex.justify-content-end form {
            font-size: 0.75rem;
        }

        .purchase-returns-index-page .pagination {
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 0;
        }
    }
</style>
@endsection
