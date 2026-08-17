@extends('layouts.admin')

@section('title', 'Create Supplier')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Create Supplier</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 mt-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.suppliers.index') }}">Suppliers</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.suppliers.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            {{-- Supplier Name --}}
                            <div class="col-md-6">
                                <label for="name" class="form-label">Supplier Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}"
                                       placeholder="Enter supplier name"
                                       required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Company Name --}}
                            <div class="col-md-6">
                                <label for="company_name" class="form-label">Company Name</label>
                                <input type="text" 
                                       class="form-control @error('company_name') is-invalid @enderror" 
                                       id="company_name" 
                                       name="company_name" 
                                       value="{{ old('company_name') }}"
                                       placeholder="Enter company name (if different)">
                                @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Contact Person --}}
                            <div class="col-md-6">
                                <label for="contact_person" class="form-label">Contact Person</label>
                                <input type="text" 
                                       class="form-control @error('contact_person') is-invalid @enderror" 
                                       id="contact_person" 
                                       name="contact_person" 
                                       value="{{ old('contact_person') }}"
                                       placeholder="Enter contact person name">
                                @error('contact_person')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Phone --}}
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone') }}"
                                       placeholder="Enter phone number"
                                       required>
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
                                       placeholder="Enter email address">
                                @error('email')
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
                                       placeholder="Enter city">
                                @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- NTN --}}
                            <div class="col-md-12">
                                <label for="ntn" class="form-label">NTN (National Tax Number)</label>
                                <input type="text" 
                                       class="form-control @error('ntn') is-invalid @enderror" 
                                       id="ntn" 
                                       name="ntn" 
                                       value="{{ old('ntn') }}"
                                       placeholder="Enter NTN"
                                       style="text-transform: uppercase;">
                                @error('ntn')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Will be automatically converted to uppercase</small>
                            </div>

                            {{-- Address --}}
                            <div class="col-md-12">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                          id="address" 
                                          name="address" 
                                          rows="3"
                                          placeholder="Enter complete address">{{ old('address') }}</textarea>
                                @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div class="col-md-12">
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
                                <i class="bi bi-check-circle me-1"></i> Create Supplier
                            </button>
                            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary">
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
                        <i class="bi bi-info-circle me-1"></i> Supplier Guidelines
                    </h5>
                    <ul class="mb-0 small">
                        <li><strong>Supplier Name:</strong> Required. Main identifier for the supplier.</li>
                        <li><strong>Company Name:</strong> Optional. Use if trading name is different from supplier name.</li>
                        <li><strong>Contact Person:</strong> Optional. Name of person to contact.</li>
                        <li><strong>Phone:</strong> Required. Primary contact number.</li>
                        <li><strong>Email:</strong> Optional but recommended for communications.</li>
                        <li><strong>NTN:</strong> National Tax Number for tax purposes.</li>
                        <li><strong>Status:</strong> Set to Active to allow purchase orders.</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-lightbulb me-1"></i> Quick Tips
                    </h6>
                    <ul class="mb-0 small text-muted">
                        <li>Ensure contact information is accurate for communications.</li>
                        <li>NTN is important for tax reporting and compliance.</li>
                        <li>You can update supplier information anytime from the edit page.</li>
                        <li>Inactive suppliers cannot be used in new purchase orders.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
