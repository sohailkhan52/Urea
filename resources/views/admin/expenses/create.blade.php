@extends('layouts.admin')

@section('title', 'Add Expense')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="mb-4">
        <div>
            <h1 class="h3 mb-1">Add Expense</h1>
            <p class="text-muted mb-0">Create a new expense record</p>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Expense Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.expenses.store') }}" method="POST">
                        @csrf

                        {{-- Expense Item Field --}}
                        <div class="mb-3">
                            <label for="expense_item" class="form-label">Expense Item <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('expense_item') is-invalid @enderror" 
                                   id="expense_item" 
                                   name="expense_item"
                                   placeholder="e.g., Electricity Bill, Transport, Office Supplies, Rent, Salary, Fuel"
                                   value="{{ old('expense_item') }}"
                                   required>
                            @error('expense_item')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Cost Field --}}
                        <div class="mb-3">
                            <label for="cost" class="form-label">Cost <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" 
                                       class="form-control @error('cost') is-invalid @enderror" 
                                       id="cost" 
                                       name="cost"
                                       placeholder="0.00"
                                       step="0.01"
                                       min="0.01"
                                       value="{{ old('cost') }}"
                                       required>
                                @error('cost')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted d-block mt-1">Must be greater than 0</small>
                        </div>

                        {{-- Warehouse Selection (if user is Super Admin) --}}
                        @if(auth()->user()->isSuperAdmin())
                        <div class="mb-3">
                            <label for="warehouse_id" class="form-label">Warehouse</label>
                            <select class="form-select @error('warehouse_id') is-invalid @enderror" 
                                    id="warehouse_id" 
                                    name="warehouse_id">
                                <option value="">-- Select Warehouse --</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}"
                                        {{ old('warehouse_id') == $warehouse->id || ($defaultWarehouse && $warehouse->id == $defaultWarehouse->id) ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('warehouse_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        @else
                            {{-- For non-super-admin users, show their warehouse info --}}
                            <div class="mb-3">
                                <label class="form-label">Warehouse</label>
                                <div class="form-control-plaintext fw-semibold">
                                    {{ $defaultWarehouse ? $defaultWarehouse->name : 'No warehouse assigned' }}
                                </div>
                                @if($defaultWarehouse)
                                    <input type="hidden" name="warehouse_id" value="{{ $defaultWarehouse->id }}">
                                @endif
                            </div>
                        @endif

                        {{-- Form Actions --}}
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Save Expense
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Help Card --}}
        <div class="col-md-6">
            <div class="card bg-light">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>How to Add Expense
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Follow these steps to create a new expense:</p>
                    <ol class="text-muted small">
                        <li class="mb-2">
                            <strong>Expense Item:</strong> Enter a descriptive name for the expense
                            (e.g., Electricity Bill, Transport, Office Supplies, Rent, Salary, Fuel)
                        </li>
                        <li class="mb-2">
                            <strong>Cost:</strong> Enter the amount in Pakistani Rupees (Rs.)
                        </li>
                        @if(auth()->user()->isSuperAdmin())
                        <li class="mb-2">
                            <strong>Warehouse:</strong> Select the warehouse this expense belongs to
                        </li>
                        @endif
                        <li class="mb-2">
                            <strong>Click Save:</strong> The expense will be created and recorded with the current date/time
                        </li>
                    </ol>
                    <hr>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-lightbulb me-2"></i>
                        <strong>Tip:</strong> Be specific with expense descriptions. This helps with tracking and reporting.
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
