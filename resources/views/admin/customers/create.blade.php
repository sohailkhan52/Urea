@extends('layouts.admin')

@section('title', 'Create Customer')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Create Customer</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 mt-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.customers.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            {{-- Customer Type --}}
                            <div class="col-md-6">
                                <label for="customer_type" class="form-label">Customer Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('customer_type') is-invalid @enderror" 
                                        id="customer_type" 
                                        name="customer_type" 
                                        required>
                                    <option value="">-- Select Type --</option>
                                    @foreach($types as $value => $label)
                                    <option value="{{ $value }}" {{ old('customer_type') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('customer_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Customer Name --}}
                            <div class="col-md-6">
                                <label for="name" class="form-label">Customer Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}"
                                       placeholder="Enter customer name"
                                       required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Father Name --}}
                            <div class="col-md-6">
                                <label for="father_name" class="form-label">Father's Name</label>
                                <input type="text" 
                                       class="form-control @error('father_name') is-invalid @enderror" 
                                       id="father_name" 
                                       name="father_name" 
                                       value="{{ old('father_name') }}"
                                       placeholder="Enter father's name (optional)">
                                @error('father_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- CNIC --}}
                            <div class="col-md-6">
                                <label for="cnic" class="form-label">CNIC</label>
                                <input type="text" 
                                       class="form-control @error('cnic') is-invalid @enderror" 
                                       id="cnic" 
                                       name="cnic" 
                                       value="{{ old('cnic') }}"
                                       placeholder="Enter CNIC (optional)"
                                       style="text-transform: uppercase;">
                                @error('cnic')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Will be converted to uppercase</small>
                            </div>

                            {{-- Phone --}}
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone') }}"
                                       placeholder="Enter phone number (optional)">
                                @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}"
                                       placeholder="Enter email address (optional)">
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Village --}}
                            <div class="col-md-6">
                                <label for="village" class="form-label">Village</label>
                                <input type="text" 
                                       class="form-control @error('village') is-invalid @enderror" 
                                       id="village" 
                                       name="village" 
                                       value="{{ old('village') }}"
                                       placeholder="Enter village name (optional)">
                                @error('village')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- City --}}
                            <div class="col-md-6">
                                <label for="city" class="form-label">City</label>
                                <input type="text" 
                                       class="form-control @error('city') is-invalid @enderror" 
                                       id="city" 
                                       name="city" 
                                       value="{{ old('city') }}"
                                       placeholder="Enter city name (optional)">
                                @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Address --}}
                            <div class="col-md-12">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                          id="address" 
                                          name="address" 
                                          rows="3"
                                          placeholder="Enter complete address (optional)">{{ old('address') }}</textarea>
                                @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Credit Limit --}}
                            <div class="col-md-6">
                                <label for="credit_limit" class="form-label">Credit Limit</label>
                                <div class="input-group">
                                    <span class="input-group-text">PKR</span>
                                    <input type="number" 
                                           class="form-control @error('credit_limit') is-invalid @enderror" 
                                           id="credit_limit" 
                                           name="credit_limit" 
                                           value="{{ old('credit_limit', 0) }}"
                                           step="0.01"
                                           min="0">
                                </div>
                                @error('credit_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Default: 0 (No credit allowed)</small>
                            </div>

                            {{-- Status --}}
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" 
                                        name="status" 
                                        required>
                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Create Customer
                            </button>
                            <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">
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
                        <i class="bi bi-info-circle me-1"></i> Customer Guidelines
                    </h5>
                    <ul class="mb-0 small">
                        <li><strong>Customer Type:</strong> Required. Choose farmer, dealer, or retail customer.</li>
                        <li><strong>Name:</strong> Required. Full name of the customer.</li>
                        <li><strong>Phone:</strong> Recommended for communications.</li>
                        <li><strong>CNIC:</strong> Optional. National ID for tax purposes.</li>
                        <li><strong>Credit Limit:</strong> Maximum credit allowed for this customer.</li>
                        <li><strong>Status:</strong> Active customers can make purchases.</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-lightbulb me-1"></i> Quick Tips
                    </h6>
                    <ul class="mb-0 small text-muted">
                        <li>Fill in required fields (type, name, status) to create.</li>
                        <li>Optional fields can be added later.</li>
                        <li>Set credit limit to 0 for cash-only customers.</li>
                        <li>Keep phone numbers accurate for contact.</li>
                        <li>You can update customer info anytime from the edit page.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
