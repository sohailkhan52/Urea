@extends('layouts.admin')

@section('title', 'Edit Expense')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="mb-4">
        <div>
            <h1 class="h3 mb-1">Edit Expense</h1>
            <p class="text-muted mb-0">Update expense details</p>
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
                    <form action="{{ route('admin.expenses.update', $expense) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Expense Item Field --}}
                        <div class="mb-3">
                            <label for="expense_item" class="form-label">Expense Item <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('expense_item') is-invalid @enderror" 
                                   id="expense_item" 
                                   name="expense_item"
                                   placeholder="e.g., Electricity Bill, Transport, Office Supplies"
                                   value="{{ old('expense_item', $expense->expense_item) }}"
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
                                       value="{{ old('cost', $expense->cost) }}"
                                       required>
                                @error('cost')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted d-block mt-1">Must be greater than 0</small>
                        </div>

                        {{-- Warehouse Information --}}
                        <div class="mb-3">
                            <label class="form-label">Warehouse</label>
                            <div class="form-control-plaintext fw-semibold">
                                {{ $expense->warehouse->name ?? 'Not assigned' }}
                            </div>
                            <small class="text-muted">Cannot be changed</small>
                        </div>

                        {{-- Created Information --}}
                        <div class="mb-3">
                            <label class="form-label">Created Information</label>
                            <div class="alert alert-info small mb-0">
                                <p class="mb-1">
                                    <strong>Date:</strong> {{ $expense->created_at->format('d-M-Y H:i A') }}
                                </p>
                                <p class="mb-0">
                                    <strong>Created By:</strong> {{ $expense->creator->name ?? 'Unknown' }}
                                </p>
                            </div>
                            <small class="text-muted d-block mt-2">Creation date cannot be changed</small>
                        </div>

                        {{-- Form Actions --}}
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Update Expense
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Info Card --}}
        <div class="col-md-6">
            <div class="card bg-light">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>Update Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info small mb-3">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <strong>Note:</strong> Only the expense item and cost can be edited.
                        The creation date and creator information are permanent and cannot be changed.
                    </div>
                    
                    <h6 class="text-muted">Fields You Can Edit:</h6>
                    <ul class="text-muted small mb-3">
                        <li>Expense Item (description)</li>
                        <li>Cost (amount)</li>
                    </ul>

                    <h6 class="text-muted">Fields You Cannot Edit:</h6>
                    <ul class="text-muted small mb-3">
                        <li>Created Date & Time</li>
                        <li>Created By User</li>
                        <li>Warehouse</li>
                    </ul>

                    <hr>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-lightbulb me-2"></i>
                        <strong>Tip:</strong> If you need to change the warehouse or creator, 
                        delete this expense and create a new one.
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
