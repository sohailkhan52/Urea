@extends('layouts.admin')

@section('title', 'Udhar Ledger - ' . $customer->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Udhar Ledger</h1>
            <p class="text-muted mb-0">{{ $customer->name }}</p>
        </div>
        <div class="btn-group" role="group">
            <a href="{{ route('admin.udhar.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-list me-1"></i> All Customers
            </a>
            <a href="{{ route('admin.udhar.details', $customer) }}" class="btn btn-outline-secondary">
                <i class="bi bi-person me-1"></i> Customer Details
            </a>
        </div>
    </div>

    {{-- Current Balance Summary --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Current Outstanding Balance</h6>
                            <h3 class="mb-0 text-danger">Rs. {{ number_format($currentBalance, 2) }}</h3>
                        </div>
                        <div class="text-end">
                            <p class="text-muted mb-1">Last Transaction</p>
                            @if($paginator->count() > 0)
                                <p class="mb-0">{{ $paginator->first()['date'] }}</p>
                            @else
                                <p class="mb-0">N/A</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Date Filter --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.udhar.ledger', $customer) }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">Date From</label>
                        <input type="date" 
                               class="form-control" 
                               id="date_from" 
                               name="date_from" 
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">Date To</label>
                        <input type="date" 
                               class="form-control" 
                               id="date_to" 
                               name="date_to" 
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-6 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.udhar.ledger', $customer) }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Ledger Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Invoice/Reference</th>
                        <th>Description</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                        <th class="text-end">Running Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ledgerEntries as $entry)
                    <tr>
                        <td>{{ $entry['date'] }}</td>
                        <td>
                            <span class="badge bg-{{ $entry['type_badge'] }}">
                                {{ $entry['type_label'] }}
                            </span>
                        </td>
                        <td>
                            <strong>{{ $entry['invoice_number'] }}</strong>
                            @if($entry['reference_number'])
                            <br><small class="text-muted">{{ $entry['reference_number'] }}</small>
                            @endif
                        </td>
                        <td>{{ $entry['description'] }}</td>
                        <td class="text-end">
                            @if($entry['debit'] > 0)
                                <span class="text-danger">Rs. {{ number_format($entry['debit'], 2) }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($entry['credit'] > 0)
                                <span class="text-success">Rs. {{ number_format($entry['credit'], 2) }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <strong>Rs. {{ number_format($entry['balance'], 2) }}</strong>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 2rem; color: #ccc;"></i>
                            <p class="text-muted mt-2">No ledger entries found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($paginator->hasPages())
        <div class="card-footer bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} 
                    of {{ $paginator->total() }} entries
                </small>
                {{ $paginator->links() }}
            </div>
        </div>
        @endif
    </div>

    {{-- Legend --}}
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Legend</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <small>
                                <span class="badge bg-light text-danger">Debit</span> = Amount owed by customer
                            </small>
                        </div>
                        <div class="col-md-4">
                            <small>
                                <span class="badge bg-success">Credit</span> = Payment received from customer
                            </small>
                        </div>
                        <div class="col-md-4">
                            <small>
                                <strong>Balance</strong> = Running balance (positive = customer owes, negative = overpaid)
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
