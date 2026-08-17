@extends('layouts.admin')

@section('title', 'Low Stock Items')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i> Low Stock Alert
                </h1>
                <p class="text-muted mb-0">Products below minimum stock level</p>
            </div>
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Inventory
            </a>
        </div>
    </div>

    {{-- Alert Summary --}}
    @if($lowStockItems->count() > 0)
    <div class="alert alert-warning mb-4">
        <h5 class="alert-heading">
            <i class="bi bi-exclamation-triangle me-2"></i> Attention Required
        </h5>
        <p class="mb-0">
            <strong>{{ $lowStockItems->count() }}</strong> product(s) are currently below their minimum stock levels and require restocking.
        </p>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            @if($lowStockItems->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Warehouse</th>
                            <th class="text-end">Current Stock</th>
                            <th class="text-end">Minimum Level</th>
                            <th class="text-end">Shortage</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockItems as $item)
                        <tr class="table-warning">
                            <td>
                                <div class="fw-semibold">{{ $item['product_name'] }}</div>
                            </td>
                            <td>
                                <code class="bg-light px-2 py-1 rounded">{{ $item['product_sku'] }}</code>
                            </td>
                            <td>
                                <small>
                                    <i class="bi bi-house-door me-1"></i>
                                    {{ $item['warehouse_name'] }}
                                </small>
                            </td>
                            <td class="text-end">
                                <strong class="text-danger">{{ number_format($item['current_stock'], 2) }}</strong>
                            </td>
                            <td class="text-end">
                                <span class="text-muted">{{ number_format($item['minimum_level'], 2) }}</span>
                            </td>
                            <td class="text-end">
                                <span class="badge bg-danger">
                                    {{ number_format($item['minimum_level'] - $item['current_stock'], 2) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-warning">
                                    <i class="bi bi-exclamation-triangle me-1"></i> Low Stock
                                </span>
                            </td>
                            <td class="text-end">
                                @can('inventory.view')
                                <a href="{{ route('admin.inventory.movements', ['warehouse_id' => $item['warehouse_id'], 'product_id' => $item['product_id']]) }}" 
                                   class="btn btn-sm btn-info"
                                   title="View Movements">
                                    <i class="bi bi-clock-history"></i>
                                </a>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Summary --}}
            <div class="mt-4 p-3 bg-light rounded">
                <h6 class="mb-2">
                    <i class="bi bi-info-circle me-1"></i> Recommended Actions
                </h6>
                <ul class="mb-0 small">
                    <li>Review low stock items and create purchase orders to replenish inventory</li>
                    <li>Check if stock can be transferred from other warehouses</li>
                    <li>Consider adjusting minimum stock levels if consumption patterns have changed</li>
                    <li>Prioritize products with the highest shortage amounts</li>
                </ul>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                <h5 class="mt-3">All Products Are Above Minimum Stock Level</h5>
                <p class="text-muted">
                    Great! No low stock alerts at this time.
                </p>
                <a href="{{ route('admin.inventory.index') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-box-seam me-1"></i> View Inventory
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
