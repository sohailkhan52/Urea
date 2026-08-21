@extends('layouts.admin')

@section('title', 'Create Stock Transfer')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Create Stock Transfer</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 mt-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.stock-transfers.index') }}">Stock Transfers</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.stock-transfers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.stock-transfers.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            {{-- Source Warehouse --}}
                            <div class="col-md-6">
                                <label for="source_warehouse_id" class="form-label">From Warehouse <span class="text-danger">*</span></label>
                                <select class="form-select @error('source_warehouse_id') is-invalid @enderror" 
                                        id="source_warehouse_id" 
                                        name="source_warehouse_id" 
                                        required>
                                    <option value="">-- Select Source Warehouse --</option>
                                    @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('source_warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('source_warehouse_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Destination Warehouse --}}
                            <div class="col-md-6">
                                <label for="destination_warehouse_id" class="form-label">To Warehouse <span class="text-danger">*</span></label>
                                <select class="form-select @error('destination_warehouse_id') is-invalid @enderror" 
                                        id="destination_warehouse_id" 
                                        name="destination_warehouse_id" 
                                        required>
                                    <option value="">-- Select Destination Warehouse --</option>
                                    @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('destination_warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('destination_warehouse_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Transfer Date --}}
                            <div class="col-md-6">
                                <label for="transfer_date" class="form-label">Transfer Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('transfer_date') is-invalid @enderror" 
                                       id="transfer_date" 
                                       name="transfer_date" 
                                       value="{{ old('transfer_date', date('Y-m-d')) }}"
                                       min="{{ date('Y-m-d') }}"
                                       required>
                                @error('transfer_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Status Info --}}
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <div class="form-control-plaintext pt-2">
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-exclamation-circle me-1"></i> Draft
                                    </span>
                                    <small class="d-block text-muted mt-2">This transfer will be saved as draft. Add items and submit for approval.</small>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="col-md-12">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" 
                                          name="notes" 
                                          rows="3"
                                          placeholder="Add any additional notes or special instructions..."
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
                            <a href="{{ route('admin.stock-transfers.index') }}" class="btn btn-secondary">
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
                        <i class="bi bi-info-circle me-1"></i> Transfer Guidelines
                    </h5>
                    <ul class="mb-0 small">
                        <li><strong>From/To Warehouse:</strong> Required. Must be different warehouses.</li>
                        <li><strong>Transfer Date:</strong> Required. Cannot be in the past.</li>
                        <li><strong>Draft Status:</strong> All new transfers start as draft.</li>
                        <li><strong>No Stock Impact:</strong> Draft transfers do not affect inventory.</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-lightbulb me-1"></i> Transfer Process
                    </h6>
                    <ol class="mb-0 small text-muted">
                        <li>Create draft transfer</li>
                        <li>Add products and quantities</li>
                        <li>Submit for approval</li>
                        <li>Approve transfer</li>
                        <li>Dispatch to reduce source stock</li>
                        <li>Mark as in transit</li>
                        <li>Receive items at destination</li>
                    </ol>
                </div>
            </div>

            <div class="card mt-3 border-info">
                <div class="card-body">
                    <h6 class="card-title text-info">
                        <i class="bi bi-shield-check me-1"></i> Stock Safety
                    </h6>
                    <p class="mb-0 small text-muted">
                        Stock is reduced from source warehouse on <strong>dispatch</strong> and added to destination warehouse on <strong>receive</strong>. Draft and pending transfers do not affect inventory.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
