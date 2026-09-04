@extends('layouts.admin')

@section('title', 'Create Sale Return')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-arrow-return-left me-2"></i>Select Sale Invoice to Return
        </h1>
        <a href="{{ route('admin.sales.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Sales
        </a>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Choose a completed sale and select items to return. Stock will be added back to inventory.</h5>
        </div>
        <div class="card-body">
            {{-- Search Bar --}}
            <div class="row mb-4">
                <div class="col-md-4">
                    <input type="text" 
                           id="searchInput" 
                           class="form-control" 
                           placeholder="Search by Invoice No, Customer Name, or Date"
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

            {{-- Sales Table --}}
            <div class="table-responsive">
                <table class="table table-hover" id="salesTable">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice No</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th class="text-end">Total</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 150px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="salesTableBody">
                        @forelse($sales as $sale)
                            <tr>
                                <td><strong>{{ $sale->invoice_number }}</strong></td>
                                <td>{{ $sale->customer ? $sale->customer->name : ($sale->walkin_customer_name ?? 'Walk-in Customer') }}</td>
                                <td>{{ $sale->sale_date->format('M d, Y') }}</td>
                                <td class="text-end">Rs. {{ number_format($sale->total_amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $sale->payment_status === 'Paid' ? 'success' : ($sale->payment_status === 'Partial' ? 'warning' : 'danger') }}">
                                        {{ $sale->payment_status ?? 'Completed' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.sale-returns.create', ['sale_id' => $sale->id]) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-arrow-return-left me-1"></i> Return Items
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No completed sales found. 
                                    <a href="{{ route('admin.sales.index') }}">View all sales</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($sales->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $sales->links() }}
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
    const rows = document.querySelectorAll('#salesTableBody tr');

    rows.forEach(row => {
        const invoiceNo = row.cells[0]?.textContent.toLowerCase() || '';
        const customer = row.cells[1]?.textContent.toLowerCase() || '';
        const date = row.cells[2]?.textContent || '';
        
        const matchesSearch = invoiceNo.includes(searchText) || customer.includes(searchText);
        const matchesDate = !dateFilter || date.includes(dateFilter);
        
        row.style.display = (matchesSearch && matchesDate) ? '' : 'none';
    });
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('dateFilter').value = '';
    document.querySelectorAll('#salesTableBody tr').forEach(row => {
        row.style.display = '';
    });
}
</script>
@endsection
