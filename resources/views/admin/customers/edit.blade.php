@extends('layouts.admin')

@section('title', 'Edit Customer - ' . $customer->name)

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Edit Customer</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 mt-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.customers.show', $customer) }}">{{ $customer->name }}</a>
                        </li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </nav>
            </div>
            <div class="btn-group">
                <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to View
                </a>
                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-list me-1"></i> All Customers
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.customers.update', $customer) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            {{-- Warehouse (Super Admin Only) --}}
                            @if(auth()->user()->isSuperAdmin())
                            <div class="col-md-6">
                                <label for="warehouse_id" class="form-label">Warehouse <span class="text-danger">*</span></label>
                                <select class="form-select @error('warehouse_id') is-invalid @enderror" 
                                        id="warehouse_id" 
                                        name="warehouse_id" 
                                        required>
                                    <option value="">-- Select Warehouse --</option>
                                    @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $customer->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('warehouse_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @endif

                            {{-- Customer Type --}}
                            <div class="col-md-6">
                                <label for="customer_type" class="form-label">Customer Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('customer_type') is-invalid @enderror" 
                                        id="customer_type" 
                                        name="customer_type" 
                                        required>
                                    <option value="">-- Select Type --</option>
                                    @foreach($types as $value => $label)
                                    <option value="{{ $value }}" {{ old('customer_type', $customer->customer_type) === $value ? 'selected' : '' }}>
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
                                       value="{{ old('name', $customer->name) }}"
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
                                       value="{{ old('father_name', $customer->father_name) }}"
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
                                       value="{{ old('cnic', $customer->cnic) }}"
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
                                       value="{{ old('phone', $customer->phone) }}"
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
                                       value="{{ old('email', $customer->email) }}"
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
                                       value="{{ old('village', $customer->village) }}"
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
                                       value="{{ old('city', $customer->city) }}"
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
                                          placeholder="Enter complete address (optional)">{{ old('address', $customer->address) }}</textarea>
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
                                           value="{{ old('credit_limit', $customer->credit_limit) }}"
                                           step="0.01"
                                           min="0">
                                </div>
                                @error('credit_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Maximum credit allowed for this customer</small>
                            </div>

                            {{-- Status --}}
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" 
                                        name="status" 
                                        required>
                                    <option value="active" {{ old('status', $customer->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $customer->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Update Customer
                            </button>
                            <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Customer Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Customer ID</small>
                        <strong>#{{ $customer->id }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Created</small>
                        <strong>{{ $customer->created_at->format('M d, Y h:i A') }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Last Updated</small>
                        <strong>{{ $customer->updated_at->format('M d, Y h:i A') }}</strong>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Status</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="badge bg-{{ $customer->status_badge }} p-2">
                            {{ $customer->status_label }}
                        </span>
                    </div>
                    <small class="text-muted">
                        @if($customer->isActive())
                            This customer can make purchases and sales.
                        @else
                            This customer is inactive and cannot make transactions.
                        @endif
                    </small>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-lightbulb me-1"></i> Edit Guidelines
                    </h6>
                    <ul class="mb-0 small text-muted">
                        <li>Update customer information as needed.</li>
                        <li>CNIC will be auto-converted to uppercase.</li>
                        <li>Active status allows customer to make purchases.</li>
                        <li>Adjust credit limit to manage customer credit.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
