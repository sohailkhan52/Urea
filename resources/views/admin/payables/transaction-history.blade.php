@extends('layouts.admin')

@section('title', 'Payable History - ' . $supplier->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Payable Transaction History</h1>
            <p class="text-muted mb-0">{{ $supplier->name ?? 'Supplier' }} - Complete Payment History</p>
        </div>
        <div class="btn-group" role="group">
            <a href="{{ route('admin.payables.details', $supplier) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
            <a href="{{ route('admin.payables.ledger', $supplier) }}" class="btn btn-outline-secondary">
                <i class="bi bi-book me-1"></i> Ledger View
            </a>
            <button class="btn btn-outline-primary" id="toggleView" title="Toggle Table/Timeline View">
                <i class="bi bi-table me-1"></i> Table View
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Transactions</h6>
                    <h3 class="mb-0">{{ $summary['total_transactions'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Purchases Created</h6>
                    <h3 class="mb-0 text-danger">{{ $summary['purchases_created'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Payments Recorded</h6>
                    <h3 class="mb-0 text-success">{{ $summary['payments_recorded'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Adjustments</h6>
                    <h3 class="mb-0 text-info">{{ $summary['adjustments'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    @if(($history->count() ?? 0) == 0)
    <div class="alert alert-info" role="alert">
        <i class="bi bi-info-circle me-2"></i>
        <strong>No transaction history yet.</strong> This supplier's payable history will appear here once purchases are created and payments are recorded.
    </div>
    @endif

    {{-- Filter Form --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.payables.transaction-history', $supplier) }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="type" class="form-label">Transaction Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">All Types</option>
                            @foreach($transactionTypes as $key => $label)
                            <option value="{{ $key }}" {{ $filters['type'] == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">Date From</label>
                        <input type="date" 
                               class="form-control" 
                               id="date_from" 
                               name="date_from" 
                               value="{{ $filters['date_from'] }}">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">Date To</label>
                        <input type="date" 
                               class="form-control" 
                               id="date_to" 
                               name="date_to" 
                               value="{{ $filters['date_to'] }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.payables.transaction-history', $supplier) }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLE VIEW --}}
    <div id="tableView" class="card" style="display: none;">
        <div class="table-responsive">
            <table class="table table-hover mb-0 table-sm">
                <thead class="table-light sticky-top">
                    <tr>
                        <th style="width: 120px;">Date & Time</th>
                        <th style="width: 130px;">Type</th>
                        <th>Description</th>
                        <th style="width: 100px;">Reference</th>
                        <th class="text-end" style="width: 110px;">Purchase Amt</th>
                        <th class="text-end" style="width: 100px;">Paid</th>
                        <th class="text-end" style="width: 110px;">Payable Before</th>
                        <th class="text-end" style="width: 110px;">Payable After</th>
                        <th class="text-end" style="width: 100px;">Change</th>
                        <th style="width: 120px;">Status</th>
                        <th style="width: 100px;">By</th>
                        <th class="text-center" style="width: 50px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $transaction)
                    <tr>
                        <td>
                            <small class="text-muted d-block">
                                {{ $transaction->transaction_date->format('d M Y') }}
                            </small>
                            <small class="text-muted">
                                {{ $transaction->transaction_date->format('h:i A') }}
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-{{ $transaction->type_badge }}">
                                {{ $transaction->type_label }}
                            </span>
                        </td>
                        <td>
                            <small>{{ $transaction->description }}</small>
                            @if($transaction->notes)
                            <br><small class="text-muted">{{ substr($transaction->notes, 0, 40) }}...</small>
                            @endif
                        </td>
                        <td>
                            <small>{{ $transaction->reference_number ?? '-' }}</small>
                        </td>
                        <td class="text-end">
                            <small>Rs. {{ number_format($transaction->current_total_amount, 2) }}</small>
                        </td>
                        <td class="text-end">
                            <small>Rs. {{ number_format($transaction->current_paid_amount, 2) }}</small>
                        </td>
                        <td class="text-end">
                            <small class="text-danger">
                                Rs. {{ number_format($transaction->previous_payable_amount, 2) }}
                            </small>
                        </td>
                        <td class="text-end">
                            <small class="text-danger">
                                <strong>Rs. {{ number_format($transaction->current_payable_amount, 2) }}</strong>
                            </small>
                        </td>
                        <td class="text-end">
                            @if($transaction->amount_changed > 0)
                                <span class="text-danger">+Rs. {{ number_format($transaction->amount_changed, 2) }}</span>
                            @elseif($transaction->amount_changed < 0)
                                <span class="text-success">Rs. {{ number_format($transaction->amount_changed, 2) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $transaction->status_badge }}">
                                {{ $transaction->status_label }}
                            </span>
                        </td>
                        <td>
                            <small>{{ $transaction->creator->name ?? 'System' }}</small>
                        </td>
                        <td class="text-center">
                            @if($transaction->purchase)
                            <a href="{{ route('admin.purchases.show', $transaction->purchase) }}" 
                               class="btn btn-sm btn-outline-info" 
                               target="_blank"
                               title="View PO">
                                <i class="bi bi-eye"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 2rem; color: #ccc;"></i>
                            <p class="text-muted mt-2">No transaction history found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($history->hasPages())
        <div class="card-footer bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Showing {{ $history->firstItem() ?? 0 }} to {{ $history->lastItem() ?? 0 }} 
                    of {{ $history->total() }} transactions
                </small>
                {{ $history->links() }}
            </div>
        </div>
        @endif
    </div>

    {{-- TIMELINE VIEW --}}
    <div id="timelineView" class="card">
        <div class="card-body">
            @forelse($history as $transaction)
            <div class="transaction-item mb-3 pb-3 border-bottom" style="display: flex; gap: 15px;">
                {{-- Transaction Type Icon --}}
                <div style="flex-shrink: 0;">
                    <span class="badge bg-{{ $transaction->type_badge }}" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        @if($transaction->transaction_type === 'purchase_created')
                            <i class="bi bi-plus-circle-fill"></i>
                        @elseif($transaction->transaction_type === 'payment_recorded')
                            <i class="bi bi-check-circle-fill"></i>
                        @elseif($transaction->transaction_type === 'payment_adjusted')
                            <i class="bi bi-arrow-left-right"></i>
                        @elseif($transaction->transaction_type === 'purchase_modified')
                            <i class="bi bi-pencil-square"></i>
                        @elseif($transaction->transaction_type === 'purchase_cancelled')
                            <i class="bi bi-x-circle-fill"></i>
                        @endif
                    </span>
                </div>

                {{-- Transaction Details --}}
                <div style="flex-grow: 1;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="mb-1">
                                <strong>{{ $transaction->type_label }}</strong>
                                <span class="badge bg-{{ $transaction->status_badge }} ms-2">
                                    {{ $transaction->status_label }}
                                </span>
                            </h6>
                            <p class="text-muted mb-1" style="font-size: 13px;">
                                {{ $transaction->transaction_date->format('d M Y, h:i A') }}
                            </p>
                            <p class="mb-2">{{ $transaction->description }}</p>
                            
                            @if($transaction->reference_number)
                            <small class="text-muted d-block">
                                <strong>Ref:</strong> {{ $transaction->reference_number }}
                            </small>
                            @endif

                            @if($transaction->payment_method)
                            <small class="text-muted d-block">
                                <strong>Method:</strong> {{ \App\Models\PurchasePayment::$methods[$transaction->payment_method] ?? $transaction->payment_method }}
                            </small>
                            @endif

                            @if($transaction->notes)
                            <small class="text-muted d-block mt-1">
                                <strong>Notes:</strong> {{ $transaction->notes }}
                            </small>
                            @endif

                            <small class="text-muted d-block mt-2">
                                <strong>By:</strong> {{ $transaction->creator->name ?? 'System' }}
                            </small>
                        </div>

                        {{-- Amount and Status --}}
                        <div class="text-end">
                            <div class="mb-3">
                                @if($transaction->amount_changed > 0)
                                    <span class="text-danger" style="font-size: 16px;">
                                        <strong>+Rs. {{ number_format($transaction->amount_changed, 2) }}</strong>
                                    </span>
                                    <span class="badge bg-danger ms-2">Increased</span>
                                @elseif($transaction->amount_changed < 0)
                                    <span class="text-success" style="font-size: 16px;">
                                        <strong>Rs. {{ number_format($transaction->amount_changed, 2) }}</strong>
                                    </span>
                                    <span class="badge bg-success ms-2">Decreased</span>
                                @else
                                    <span class="text-muted" style="font-size: 16px;">
                                        <strong>No Change</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Before/After Details --}}
                    <div class="row g-2" style="background-color: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 12px;">
                        <div class="col-md-6">
                            <strong class="text-muted d-block mb-2">Before:</strong>
                            <div class="mb-2">
                                <span class="text-muted">Purchase Amount:</span> 
                                <strong>Rs. {{ number_format($transaction->previous_total_amount, 2) }}</strong>
                            </div>
                            <div class="mb-2">
                                <span class="text-muted">Paid:</span> 
                                <strong>Rs. {{ number_format($transaction->previous_paid_amount, 2) }}</strong>
                            </div>
                            <div>
                                <span class="text-muted">Outstanding Payable:</span> 
                                <strong class="text-danger">Rs. {{ number_format($transaction->previous_payable_amount, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <strong class="text-muted d-block mb-2">After:</strong>
                            <div class="mb-2">
                                <span class="text-muted">Purchase Amount:</span> 
                                <strong>Rs. {{ number_format($transaction->current_total_amount, 2) }}</strong>
                            </div>
                            <div class="mb-2">
                                <span class="text-muted">Paid:</span> 
                                <strong>Rs. {{ number_format($transaction->current_paid_amount, 2) }}</strong>
                            </div>
                            <div>
                                <span class="text-muted">Outstanding Payable:</span> 
                                <strong class="text-danger">Rs. {{ number_format($transaction->current_payable_amount, 2) }}</strong>
                            </div>
                        </div>
                    </div>

                    @if($transaction->purchase)
                    <div class="mt-2">
                        <a href="{{ route('admin.purchases.show', $transaction->purchase) }}" 
                           class="btn btn-sm btn-outline-info" 
                           target="_blank">
                            <i class="bi bi-eye me-1"></i> View PO
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">No transaction history found</p>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($history->hasPages())
        <div class="card-footer bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Showing {{ $history->firstItem() ?? 0 }} to {{ $history->lastItem() ?? 0 }} 
                    of {{ $history->total() }} transactions
                </small>
                {{ $history->links() }}
            </div>
        </div>
        @endif
    </div>

    {{-- Summary Statistics --}}
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Total Changes</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="text-muted">Total Amount Increased (Purchases):</span>
                        <h5 class="text-danger">Rs. {{ number_format($summary['total_amount_increased'] ?? 0, 2) }}</h5>
                    </div>
                    <div>
                        <span class="text-muted">Total Amount Decreased (Payments):</span>
                        <h5 class="text-success">Rs. {{ number_format($summary['total_amount_decreased'] ?? 0, 2) }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Transaction Breakdown</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="text-muted">Purchases Modified:</span>
                        <h5>{{ $summary['modifications'] ?? 0 }}</h5>
                    </div>
                    <div>
                        <span class="text-muted">Purchases Cancelled:</span>
                        <h5>{{ $summary['cancellations'] ?? 0 }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Legend --}}
    <div class="card mt-4">
        <div class="card-body">
            <h6 class="mb-3">Transaction Types Legend</h6>
            <div class="row">
                <div class="col-md-4">
                    <small>
                        <span class="badge bg-danger me-2"><i class="bi bi-plus-circle-fill"></i></span>
                        <strong>Purchase Created</strong> - New payable added
                    </small>
                </div>
                <div class="col-md-4">
                    <small>
                        <span class="badge bg-success me-2"><i class="bi bi-check-circle-fill"></i></span>
                        <strong>Payment Recorded</strong> - Payable reduced
                    </small>
                </div>
                <div class="col-md-4">
                    <small>
                        <span class="badge bg-warning me-2"><i class="bi bi-pencil-square"></i></span>
                        <strong>Purchase Modified</strong> - Amount adjusted
                    </small>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-4">
                    <small>
                        <span class="badge bg-info me-2"><i class="bi bi-arrow-left-right"></i></span>
                        <strong>Payment Adjusted</strong> - Manual adjustment
                    </small>
                </div>
                <div class="col-md-4">
                    <small>
                        <span class="badge bg-secondary me-2"><i class="bi bi-x-circle-fill"></i></span>
                        <strong>Purchase Cancelled</strong> - Payable removed
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.transaction-item {
    transition: background-color 0.2s ease;
}

.transaction-item:hover {
    background-color: #f8f9fa;
    border-radius: 4px;
}

table.table-sm td, table.table-sm th {
    padding: 0.5rem;
    font-size: 0.875rem;
}

#tableView table thead {
    position: sticky;
    top: 0;
    z-index: 10;
}

.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('toggleView');
    const tableView = document.getElementById('tableView');
    const timelineView = document.getElementById('timelineView');
    
    // Check for saved preference
    const savedView = localStorage.getItem('payableHistoryView') || 'timeline';
    
    function setView(view) {
        if (view === 'table') {
            tableView.style.display = 'block';
            timelineView.style.display = 'none';
            toggleButton.innerHTML = '<i class="bi bi-list-ul me-1"></i> Timeline View';
            localStorage.setItem('payableHistoryView', 'table');
        } else {
            tableView.style.display = 'none';
            timelineView.style.display = 'block';
            toggleButton.innerHTML = '<i class="bi bi-table me-1"></i> Table View';
            localStorage.setItem('payableHistoryView', 'timeline');
        }
    }
    
    // Initialize with saved view
    setView(savedView);
    
    // Toggle on button click
    toggleButton.addEventListener('click', function() {
        const currentView = localStorage.getItem('payableHistoryView') || 'timeline';
        const newView = currentView === 'timeline' ? 'table' : 'timeline';
        setView(newView);
    });
});
</script>
@endsection
