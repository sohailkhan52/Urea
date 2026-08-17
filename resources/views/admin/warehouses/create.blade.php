@extends('layouts.admin')

@section('title', 'Create Warehouse')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Create Warehouse</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 mt-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.index') }}">Warehouses</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.warehouses.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            {{-- Warehouse Name --}}
                            <div class="col-md-6">
                                <label for="name" class="form-label">Warehouse Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}"
                                       placeholder="Enter warehouse name"
                                       required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Example: Main Warehouse Islamabad</small>
                            </div>

                            {{-- Warehouse Code --}}
                            <div class="col-md-6">
                                <label for="code" class="form-label">Warehouse Code <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('code') is-invalid @enderror" 
                                       id="code" 
                                       name="code" 
                                       value="{{ old('code') }}"
                                       placeholder="Enter warehouse code"
                                       style="text-transform: uppercase;"
                                       required>
                                @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Example: WH-ISB-001 (must be unique)</small>
                            </div>

                            {{-- Warehouse Type --}}
                            <div class="col-md-6">
                                <label for="type" class="form-label">Warehouse Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror" 
                                        id="type" 
                                        name="type" 
                                        required>
                                    <option value="">Select warehouse type</option>
                                    @foreach($warehouseTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('type') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Branch --}}
                            <div class="col-md-6">
                                <label for="branch_id" class="form-label">Branch</label>
                                <select class="form-select @error('branch_id') is-invalid @enderror" 
                                        id="branch_id" 
                                        name="branch_id">
                                    <option value="">No branch (Main Warehouse)</option>
                                    @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }} - {{ $branch->city }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('branch_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Leave empty for Main Warehouse</small>
                            </div>

                            {{-- Manager --}}
                            <div class="col-md-12">
                                <label for="manager_id" class="form-label">Warehouse Manager</label>
                                <select class="form-select @error('manager_id') is-invalid @enderror" 
                                        id="manager_id" 
                                        name="manager_id">
                                    <option value="">Not assigned yet</option>
                                    @foreach($managers as $manager)
                                    <option value="{{ $manager->id }}" {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
                                        {{ $manager->name }} - {{ $manager->email }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('manager_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">You can assign a manager later</small>
                            </div>

                            {{-- Address --}}
                            <div class="col-md-12">
                                <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                          id="address" 
                                          name="address" 
                                          rows="3"
                                          placeholder="Enter complete warehouse address"
                                          required>{{ old('address') }}</textarea>
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
                                <i class="bi bi-check-circle me-1"></i> Create Warehouse
                            </button>
                            <a href="{{ route('admin.warehouses.index') }}" class="btn btn-secondary">
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
                        <i class="bi bi-info-circle me-1"></i> Warehouse Guidelines
                    </h5>
                    <ul class="mb-0 small">
                        <li><strong>Warehouse Code:</strong> Use a unique identifier (e.g., WH-ISB-001).</li>
                        <li><strong>Main Warehouse:</strong> Central warehouse without a branch assignment.</li>
                        <li><strong>Branch Warehouse:</strong> Warehouse assigned to a specific branch location.</li>
                        <li><strong>Store:</strong> Smaller storage facility for retail operations.</li>
                        <li><strong>Manager:</strong> Optional. Can be assigned later from user management.</li>
                        <li><strong>Status:</strong> Set to Active to allow inventory operations.</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-lightbulb me-1"></i> Quick Tips
                    </h6>
                    <ul class="mb-0 small text-muted">
                        <li>Ensure the warehouse code is unique and meaningful.</li>
                        <li>Main warehouses typically don't have a branch assignment.</li>
                        <li>You can manage inventory after creating the warehouse.</li>
                        <li>Deactivated warehouses cannot receive or dispatch stock.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
