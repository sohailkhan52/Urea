@extends('layouts.admin')

@section('title', 'Supplier Payable Ledger - ' . $supplier->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $supplier->name }} - Payable Ledger</h1>
            <p class="text-muted mb-0">Complete Transaction History</p>
        </div>
        <div>
            <a href="{{ route('admin.payables.details', $supplier) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    {{-- Current Balance Card --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <h6 class="text-muted mb-1">Current Balance (Amount We Owe)</h6>
                    <h2 class="text-danger">Rs. {{ number_format($currentBalance, 2) }}</h2>
                </div>
                <div class="col-md-3">
                    <h6 class="text-muted mb-1">Supplier</h6>
                    <p class="mb-0">
                        <strong>{{ $supplier->name }}</strong><br>
                        <small>{{ $supplier->company_name ?? 'N/A' }}</small>
                    </p>
                </div>
                <div class="col-md-3">
                    <h6 class="text-muted mb-1">Phone</h6>
                    <p class="mb-0">{{ $supplier->phone ?? 'N/A' }}</p>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('admin.payables.details', $supplier) }}" class="btn btn-sm btn-outline-primary w-100">
                        <i class="bi bi-credit-card me-1"></i> Record Payment
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Date Filter --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.payables.ledger', $supplier) }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" 
                           class="form-control" 
                           id="date_from" 
                           name="date_from" 
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-4">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" 
                           class="form-control" 
                           id="date_to" 
                           name="date_to" 
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.payables.ledger', $supplier) }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Ledger Entries Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Reference</th>
                        <th>Description</th>
                        <th class="text-end">Payable Added</th>
                        <th class="text-end">Payment Made</th>
                        <th class="text-end">Balance</th>
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
                            @if($entry['reference_number'])
                                <strong>{{ $entry['reference_number'] }}</strong>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $entry['description'] }}</td>
                        <td class="text-end">
                            @if($entry['payable_added'] > 0)
                                <span class="text-danger">Rs. {{ number_format($entry['payable_added'], 2) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($entry['payment_made'] > 0)
                                <span class="text-success">Rs. {{ number_format($entry['payment_made'], 2) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <strong class="text-danger">Rs. {{ number_format($entry['balance'], 2) }}</strong>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 2rem; color: #ccc;"></i>
                            <p class="text-muted mt-2">No ledger entries found for this date range</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="card-footer bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} 
                    of {{ $paginator->total() }} entries
                </small>
                {{ $paginator->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
