@extends('layouts.admin')

@section('title', 'Create Purchase')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Create Purchase Order</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 mt-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">Purchases</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.purchases.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            {{-- Supplier --}}
                            <div class="col-md-6">
                                <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                                <select class="form-select @error('supplier_id') is-invalid @enderror" 
                                        id="supplier_id" 
                                        name="supplier_id" 
                                        required>
                                    <option value="">-- Select Supplier --</option>
                                    @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Warehouse --}}
                            <div class="col-md-6">
                                <label for="warehouse_id" class="form-label">Warehouse <span class="text-danger">*</span></label>
                                <select class="form-select @error('warehouse_id') is-invalid @enderror" 
                                        id="warehouse_id" 
                                        name="warehouse_id" 
                                        required>
                                    <option value="">-- Select Warehouse --</option>
                                    @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('warehouse_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Purchase Date --}}
                            <div class="col-md-6">
                                <label for="purchase_date" class="form-label">Purchase Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('purchase_date') is-invalid @enderror" 
                                       id="purchase_date" 
                                       name="purchase_date" 
                                       value="{{ old('purchase_date', date('Y-m-d')) }}"
                                       min="{{ date('Y-m-d') }}"
                                       required>
                                @error('purchase_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Status Info --}}
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <div class="form-control-plaintext pt-2">
                                    <span class="badge bg-warning">
                                        <i class="bi bi-exclamation-circle me-1"></i> Draft
                                    </span>
                                    <small class="d-block text-muted mt-2">This purchase will be saved as draft. Add items and confirm when ready.</small>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="col-md-12">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" 
                                          name="notes" 
                                          rows="3"
                                          placeholder="Add any additional notes or terms..."
                                          maxlength="1000">{{ old('notes') }}</textarea>
                                @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Max 1000 characters</small>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Create & Add Items
                            </button>
                            <a href="{{ route('admin.purchases.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-info-circle me-1"></i> Purchase Guidelines
                    </h5>
                    <ul class="mb-0 small">
                        <li><strong>Supplier:</strong> Required. Select the supplier to purchase from.</li>
                        <li><strong>Warehouse:</strong> Required. Select destination warehouse for stock.</li>
                        <li><strong>Purchase Date:</strong> Required. Date of the purchase order.</li>
                        <li><strong>Draft Status:</strong> All new purchases start as draft.</li>
                        <li><strong>No Stock Impact:</strong> Draft purchases do not affect inventory.</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-lightbulb me-1"></i> Next Steps
                    </h6>
                    <ol class="mb-0 small text-muted">
                        <li>Create the purchase order</li>
                        <li>Add products and quantities</li>
                        <li>Add transport/other expenses if applicable</li>
                        <li>Review totals</li>
                        <li>Confirm to add stock to warehouse</li>
                    </ol>
                </div>
            </div>

            <div class="card mt-3 border-info">
                <div class="card-body">
                    <h6 class="card-title text-info">
                        <i class="bi bi-shield-check me-1"></i> Stock Safety
                    </h6>
                    <p class="mb-0 small text-muted">
                        Stock is only added to the warehouse when you <strong>confirm</strong> the purchase. Draft purchases can be edited freely without affecting inventory.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
