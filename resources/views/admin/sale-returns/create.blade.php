@extends('layouts.admin')

@section('title', 'Create Sale Return')

@section('content')
<div class="container-fluid sale-return-selection">
    <div class="d-flex justify-content-between align-items-center mb-4 return-selection-heading">
        <h1 class="h3 mb-0">
            Select Sale Invoice to Return
        </h1>
        <a href="{{ route('admin.sales.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Sales
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            {{-- Search Bar --}}
            <div class="row mb-4 return-selection-filters">
                <div class="col-md-4">
                    <input type="text" 
                           id="searchInput" 
                           class="form-control" 
                           placeholder="Search by Invoice No, Customer Name, or Date"
                           data-mobile-placeholder="Search invoice or customer"
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

            <div class="d-flex justify-content-end align-items-center mb-2">
                <form action="{{ route('admin.sale-returns.create') }}" method="GET" class="d-flex align-items-center gap-2">
                    <label for="sale-returns-per-page" class="small text-muted mb-0">Per page</label>
                    <select id="sale-returns-per-page" name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        @foreach([10, 25, 50, 100] as $option)
                            <option value="{{ $option }}" @selected($perPage == $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </form>
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
                                <td class="text-end">Rs. {{ number_format($sale->total_amount, 0) }}</td>
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

<style>
    @media (max-width: 768px) {
        .sale-return-selection {
            width: 100%;
            max-width: 100%;
            padding-right: 10px;
            padding-left: 10px;
        }

        .return-selection-heading {
            display: flex !important;
            flex-direction: row;
            align-items: center !important;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 16px !important;
        }

        .return-selection-heading h1 {
            flex: 1 1 auto;
            min-width: 0;
            margin: 0;
            font-size: clamp(0.72rem, 2.9vw, 0.95rem);
            line-height: 1.2;
            white-space: nowrap;
            overflow-wrap: normal;
        }

        .return-selection-heading > a {
            flex: 0 0 auto;
            width: auto;
            min-height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 8px;
            font-size: clamp(0.65rem, 2.3vw, 0.8rem);
            line-height: 1.1;
            white-space: nowrap;
        }

        .sale-return-selection .card-body {
            padding: 14px;
        }

        .return-selection-filters {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr);
            gap: 10px;
            margin: 0 0 16px !important;
        }

        .return-selection-filters > [class*="col-"] {
            width: 100%;
            max-width: 100%;
            padding-right: 0;
            padding-left: 0;
        }

        .return-selection-filters .form-control {
            display: block;
            width: 100%;
            min-height: 42px;
            padding: 7px 10px;
            font-size: 16px;
        }

        .return-selection-filters .btn {
            width: auto;
            min-width: 108px;
            min-height: 38px;
            justify-self: start;
            padding: 6px 12px;
            font-size: 15px;
        }
    }

    @media (min-width: 576px) and (max-width: 1024px) {
        .return-selection-filters {
            display: grid !important;
            grid-template-columns: minmax(0, 2fr) minmax(120px, 1fr) auto;
            align-items: center;
            gap: 10px;
            margin: 0 0 16px !important;
        }

        .return-selection-filters > [class*="col-"] {
            flex: initial;
            width: auto;
            max-width: none;
            padding-right: 0;
            padding-left: 0;
        }

        .return-selection-filters .form-control {
            width: 100%;
            min-height: 42px;
            font-size: 15px;
        }

        .return-selection-filters .btn {
            width: auto;
            min-width: 100px;
            min-height: 38px;
            padding: 6px 10px;
            font-size: 14px;
            white-space: nowrap;
        }
    }
</style>

<script>
// Client-side search functionality
const returnSearchInput = document.getElementById('searchInput');
const returnSearchMedia = window.matchMedia('(max-width: 768px)');
const updateReturnSearchPlaceholder = () => {
    returnSearchInput.placeholder = returnSearchMedia.matches
        ? returnSearchInput.dataset.mobilePlaceholder
        : 'Search by Invoice No, Customer Name, or Date';
};
updateReturnSearchPlaceholder();
returnSearchMedia.addEventListener('change', updateReturnSearchPlaceholder);

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
