@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="page-title">Purchase Returns</h1>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('admin.purchase-returns.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Create Return
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.purchase-returns.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="Search..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="refund_status" class="form-select">
                        <option value="">All Refund Status</option>
                        <option value="pending" {{ request('refund_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="partial" {{ request('refund_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="completed" {{ request('refund_status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('admin.purchase-returns.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i> Clear
                    </a>
                </div>
            </form>
        </div>
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
                                    <a href="{{ route('admin.purchases.show', $return->purchase) }}">
                                        {{ $return->purchase->purchase_number }}
                                    </a>
                                </td>
                                <td>{{ $return->supplier->name }}</td>
                                <td>{{ $return->return_date->format('d M Y') }}</td>
                                <td>Rs. {{ number_format($return->total_amount, 2) }}</td>
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
@endsection
