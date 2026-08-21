@extends('layouts.admin')

@section('title', 'Create Sale')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Create Sale</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 mt-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.sales.index') }}">Sales</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.sales.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.sales.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            {{-- Customer --}}
                            <div class="col-md-6">
                                <label for="customer_id" class="form-label">Customer (Optional - for walk-in sales, leave empty)</label>
                                <select class="form-select @error('customer_id') is-invalid @enderror" 
                                        id="customer_id" 
                                        name="customer_id">
                                    <option value="">-- Walk-in Customer --</option>
                                    @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} ({{ $customer->customer_type }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Walk-in Customer Name --}}
                            <div class="col-md-6" id="walkin-name-field" style="display: none;">
                                <label for="walkin_customer_name" class="form-label">Walk-in Customer Name</label>
                                <input type="text" 
                                       class="form-control @error('walkin_customer_name') is-invalid @enderror" 
                                       id="walkin_customer_name" 
                                       name="walkin_customer_name" 
                                       value="{{ old('walkin_customer_name') }}"
                                       placeholder="Enter customer name (optional)">
                                @error('walkin_customer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Walk-in Customer Contact --}}
                            <div class="col-md-6" id="walkin-contact-field" style="display: none;">
                                <label for="walkin_customer_contact" class="form-label">Walk-in Customer Contact</label>
                                <input type="text" 
                                       class="form-control @error('walkin_customer_contact') is-invalid @enderror" 
                                       id="walkin_customer_contact" 
                                       name="walkin_customer_contact" 
                                       value="{{ old('walkin_customer_contact') }}"
                                       placeholder="Enter phone/contact (optional)">
                                @error('walkin_customer_contact')
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

                            {{-- Sale Date --}}
                            <div class="col-md-6">
                                <label for="sale_date" class="form-label">Sale Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('sale_date') is-invalid @enderror" 
                                       id="sale_date" 
                                       name="sale_date" 
                                       value="{{ old('sale_date', date('Y-m-d')) }}"
                                       min="{{ date('Y-m-d') }}"
                                       required>
                                @error('sale_date')
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
                                    <small class="d-block text-muted mt-2">This sale will be saved as draft. Add items and confirm when ready.</small>
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
                            <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary">
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
                        <i class="bi bi-info-circle me-1"></i> Sale Guidelines
                    </h5>
                    <ul class="mb-0 small">
                        <li><strong>Customer:</strong> Optional. Select for credit tracking or leave empty for walk-in sales.</li>
                        <li><strong>Warehouse:</strong> Required. Select source warehouse for stock.</li>
                        <li><strong>Sale Date:</strong> Required. Date of the sale.</li>
                        <li><strong>Draft Status:</strong> All new sales start as draft.</li>
                        <li><strong>No Stock Impact:</strong> Draft sales do not affect inventory.</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-lightbulb me-1"></i> Next Steps
                    </h6>
                    <ol class="mb-0 small text-muted">
                        <li>Create the sale</li>
                        <li>Add products and quantities</li>
                        <li>Review stock availability</li>
                        <li>Add sale-level discount if needed</li>
                        <li>Review totals</li>
                        <li>Confirm to reduce stock</li>
                        <li>Record payment</li>
                    </ol>
                </div>
            </div>

            <div class="card mt-3 border-info">
                <div class="card-body">
                    <h6 class="card-title text-info">
                        <i class="bi bi-shield-check me-1"></i> Stock Safety
                    </h6>
                    <p class="mb-0 small text-muted">
                        Stock is only reduced from the warehouse when you <strong>confirm</strong> the sale. Draft sales can be edited freely without affecting inventory.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const customerSelect = document.getElementById('customer_id');
    const walkinNameField = document.getElementById('walkin-name-field');
    const walkinContactField = document.getElementById('walkin-contact-field');
    
    function toggleWalkinFields() {
        if (customerSelect.value === '') {
            // Show walk-in customer fields
            walkinNameField.style.display = 'block';
            walkinContactField.style.display = 'block';
        } else {
            // Hide walk-in customer fields
            walkinNameField.style.display = 'none';
            walkinContactField.style.display = 'none';
            // Clear the values
            document.getElementById('walkin_customer_name').value = '';
            document.getElementById('walkin_customer_contact').value = '';
        }
    }
    
    // Toggle on page load
    toggleWalkinFields();
    
    // Toggle when customer selection changes
    customerSelect.addEventListener('change', toggleWalkinFields);
});
</script>
@endpush
