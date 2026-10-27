@extends('layouts.admin')

@section('title', 'Inventory Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Inventory Report</h1>
    </div>

    {{-- Search and Filter --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.reports.inventory.index') }}" method="GET" autocomplete="off">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="search" class="form-label">Search</label>
                        <input type="search" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ $search ?? '' }}"
                               placeholder="Search by product name or code"
                               autocomplete="off"
                               autocorrect="off"
                               autocapitalize="off"
                               spellcheck="false">
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-secondary flex-grow-1">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.reports.inventory.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-end align-items-center mb-2">
        <form action="{{ route('admin.reports.inventory.index') }}" method="GET" class="d-flex align-items-center gap-2">
            @foreach(request()->except(['page', 'per_page']) as $key => $value)
                @if(is_scalar($value))
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <label for="inventory-per-page" class="small text-muted mb-0">Per Page</label>
            <select id="inventory-per-page" name="per_page" class="form-select form-select-sm" style="width: 82px;" onchange="this.form.submit()">
                @foreach([10, 25, 50, 100] as $option)
                    <option value="{{ $option }}" @selected($perPage == $option)>{{ $option }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Inventory Table --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Product Inventory</h5>
        </div>
        <div class="card-body">
            @if($products->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Code</th>
                                <th>Unit</th>
                                <th class="text-end">Purchased</th>
                                <th class="text-end">Sold</th>
                                <th class="text-end">Purchase Returns</th>
                                <th class="text-end">Sale Returns</th>
                                <th class="text-end">Current Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            <tr>
                                <td>
                                    <strong>{{ $product->name }}</strong>
                                </td>
                                <td>{{ $product->product_code ?? '-' }}</td>
                                <td>{{ $product->unit }}</td>
                                <td class="text-end">
                                    <span class="badge bg-info">{{ number_format($product->total_purchased) }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-warning">{{ number_format($product->total_sold) }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-danger">{{ number_format($product->purchase_returns) }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-secondary">{{ number_format($product->sale_returns) }}</span>
                                </td>
                                <td class="text-end">
                                    @if($product->current_stock > 0)
                                        <strong class="text-success">{{ number_format($product->current_stock) }}</strong>
                                    @elseif($product->current_stock < 0)
                                        <strong class="text-danger">{{ number_format($product->current_stock) }}</strong>
                                    @else
                                        <strong class="text-muted">0</strong>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end align-items-center mt-3 flex-wrap gap-2">
                    {{ $products->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                    <p class="text-muted mt-3">No inventory data found</p>
                    <small>Products with no transactions will not appear in this report.</small>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
