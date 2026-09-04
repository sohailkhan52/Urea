@extends('layouts.admin')

@section('title', 'Create Purchase Return')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-arrow-return-left me-2"></i>Select Purchase Order to Return
        </h1>
        <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Purchases
        </a>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Choose a completed purchase order and select items to return. Stock will be deducted from inventory.</h5>
        </div>
        <div class="card-body">
            {{-- Search Bar --}}
            <div class="row mb-4">
                <div class="col-md-4">
                    <input type="text" 
                           id="searchInput" 
                           class="form-control" 
                           placeholder="Search by PO No, Supplier Name, or Date"
                           autocomplete="off">
                </div>
                <div class="col-md-2">
                    <input type="date" id="dateFilter" class="form-control" placeholder="Filter by date">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary" onclick="resetFilters()">
                        <i class="bi bi-arrow-clockwise me-1"></i> Reset
                    </button>
                </div>
            </div>

            {{-- Purchases Table --}}
            <div class="table-responsive">
                <table class="table table-hover" id="purchasesTable">
                    <thead class="table-light">
                        <tr>
                            <th>PO Number</th>
                            <th>Supplier</th>
                            <th>Date</th>
                            <th class="text-end">Total</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 150px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="purchasesTableBody">
                        @forelse($purchases as $purchase)
                            <tr>
                                <td><strong>{{ $purchase->purchase_number }}</strong></td>
                                <td>{{ $purchase->supplier->name ?? 'Unknown Supplier' }}</td>
                                <td>{{ $purchase->purchase_date->format('M d, Y') }}</td>
                                <td class="text-end">Rs. {{ number_format($purchase->total_amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $purchase->payment_status === 'Paid' ? 'success' : ($purchase->payment_status === 'Partial' ? 'warning' : 'danger') }}">
                                        {{ $purchase->payment_status ?? 'Completed' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.purchase-returns.create', ['purchase_id' => $purchase->id]) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-arrow-return-left me-1"></i> Return Items
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No completed purchases found. 
                                    <a href="{{ route('admin.purchases.index') }}">View all purchases</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($purchases->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $purchases->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
// Client-side search functionality
document.getElementById('searchInput').addEventListener('input', filterTable);
document.getElementById('dateFilter').addEventListener('change', filterTable);

function filterTable() {
    const searchText = document.getElementById('searchInput').value.toLowerCase();
    const dateFilter = document.getElementById('dateFilter').value;
    const rows = document.querySelectorAll('#purchasesTableBody tr');

    rows.forEach(row => {
        const poNo = row.cells[0]?.textContent.toLowerCase() || '';
        const supplier = row.cells[1]?.textContent.toLowerCase() || '';
        const date = row.cells[2]?.textContent || '';
        
        const matchesSearch = poNo.includes(searchText) || supplier.includes(searchText);
        const matchesDate = !dateFilter || date.includes(dateFilter);
        
        row.style.display = (matchesSearch && matchesDate) ? '' : 'none';
    });
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('dateFilter').value = '';
    document.querySelectorAll('#purchasesTableBody tr').forEach(row => {
        row.style.display = '';
    });
}
</script>
@endsection
