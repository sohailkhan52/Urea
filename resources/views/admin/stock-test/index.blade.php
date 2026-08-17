@extends('layouts.admin')

@section('title', 'Stock Service Test')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-bug me-2"></i> Stock Service Test Interface
        </h1>
        <p class="text-muted">Test stock operations before integrating with Purchase and Sales modules</p>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Development Only:</strong> This test interface should be disabled in production.
        </div>
    </div>

    <div class="row">
        {{-- Add Stock (Stock In) --}}
        <div class="col-md-6 mb-4">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-arrow-down-circle me-2"></i> Add Stock (Stock In)
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.stock-test.add') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Warehouse *</label>
                            <select class="form-select" name="warehouse_id" required>
                                <option value="">Select warehouse</option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Product *</label>
                            <select class="form-select" name="product_id" required>
                                <option value="">Select product</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity *</label>
                            <input type="number" class="form-control" name="quantity" step="0.01" min="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type *</label>
                            <select class="form-select" name="type" required>
                                <option value="opening_stock">Opening Stock</option>
                                <option value="purchase">Purchase</option>
                                <option value="customer_return">Customer Return</option>
                                <option value="transfer_in">Transfer In</option>
                                <option value="adjustment_in">Adjustment In</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Unit Cost</label>
                            <input type="number" class="form-control" name="unit_cost" step="0.01" min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-plus-circle me-1"></i> Add Stock
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Remove Stock (Stock Out) --}}
        <div class="col-md-6 mb-4">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-arrow-up-circle me-2"></i> Remove Stock (Stock Out)
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.stock-test.remove') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Warehouse *</label>
                            <select class="form-select" name="warehouse_id" required>
                                <option value="">Select warehouse</option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Product *</label>
                            <select class="form-select" name="product_id" required>
                                <option value="">Select product</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity *</label>
                            <input type="number" class="form-control" name="quantity" step="0.01" min="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type *</label>
                            <select class="form-select" name="type" required>
                                <option value="sale">Sale</option>
                                <option value="supplier_return">Supplier Return</option>
                                <option value="transfer_out">Transfer Out</option>
                                <option value="adjustment_out">Adjustment Out</option>
                                <option value="damaged">Damaged</option>
                                <option value="expired">Expired</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-dash-circle me-1"></i> Remove Stock
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Transfer Stock --}}
        <div class="col-md-6 mb-4">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-arrow-left-right me-2"></i> Transfer Stock
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.stock-test.transfer') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Source Warehouse *</label>
                            <select class="form-select" name="source_warehouse_id" required>
                                <option value="">Select source warehouse</option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Destination Warehouse *</label>
                            <select class="form-select" name="destination_warehouse_id" required>
                                <option value="">Select destination warehouse</option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Product *</label>
                            <select class="form-select" name="product_id" required>
                                <option value="">Select product</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity *</label>
                            <input type="number" class="form-control" name="quantity" step="0.01" min="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-info w-100">
                            <i class="bi bi-arrow-left-right me-1"></i> Transfer Stock
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Adjust Stock --}}
        <div class="col-md-6 mb-4">
            <div class="card border-warning">
                <div class="card-header bg-warning">
                    <h5 class="mb-0">
                        <i class="bi bi-pencil-square me-2"></i> Adjust Stock
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.stock-test.adjust') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Warehouse *</label>
                            <select class="form-select" name="warehouse_id" required>
                                <option value="">Select warehouse</option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Product *</label>
                            <select class="form-select" name="product_id" required>
                                <option value="">Select product</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity * (+ for increase, - for decrease)</label>
                            <input type="number" class="form-control" name="quantity" step="0.01" required>
                            <small class="text-muted">Example: +50 or -20</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason * (Required for adjustments)</label>
                            <textarea class="form-control" name="reason" rows="2" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="bi bi-pencil-square me-1"></i> Adjust Stock
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Check Stock --}}
        <div class="col-md-12 mb-4">
            <div class="card border-secondary">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-search me-2"></i> Check Current Stock
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.stock-test.check') }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-4">
                            <label class="form-label">Warehouse *</label>
                            <select class="form-select" name="warehouse_id" required>
                                <option value="">Select warehouse</option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Product *</label>
                            <select class="form-select" name="product_id" required>
                                <option value="">Select product</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-secondary w-100">
                                <i class="bi bi-search me-1"></i> Check Stock
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Test Results --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-clipboard-check me-2"></i> Testing Guidelines
            </h5>
        </div>
        <div class="card-body">
            <h6>Test Scenarios:</h6>
            <ol>
                <li><strong>Stock In:</strong> Add opening stock for multiple products in different warehouses</li>
                <li><strong>Stock Out:</strong> Try to remove more stock than available (should fail with negative stock error)</li>
                <li><strong>Transfer:</strong> Transfer stock between warehouses and verify balances</li>
                <li><strong>Adjustment:</strong> Make positive and negative adjustments with reasons</li>
                <li><strong>Concurrent:</strong> Try multiple operations simultaneously to test locking</li>
                <li><strong>Validation:</strong> Try invalid inputs (inactive warehouse, inactive product, zero quantity)</li>
                <li><strong>History:</strong> Check stock movements in Inventory Dashboard</li>
            </ol>

            <h6 class="mt-3">Expected Behaviors:</h6>
            <ul>
                <li>✓ All operations should create stock movement records</li>
                <li>✓ Negative stock should be prevented</li>
                <li>✓ Balance after should be calculated correctly</li>
                <li>✓ Transactions should be atomic (all or nothing)</li>
                <li>✓ Adjustments without reasons should fail</li>
                <li>✓ Inactive warehouses/products should be rejected</li>
            </ul>

            <div class="mt-3">
                <a href="{{ route('admin.inventory.index') }}" class="btn btn-primary">
                    <i class="bi bi-box-seam me-1"></i> View Inventory Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
