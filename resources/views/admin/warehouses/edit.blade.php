@extends('layouts.admin')

@section('title', 'Edit Warehouse')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Edit Warehouse</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 mt-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.index') }}">Warehouses</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.show', $warehouse) }}">{{ $warehouse->name }}</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.warehouses.show', $warehouse) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Details
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.warehouses.update', $warehouse) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            {{-- Warehouse Name --}}
                            <div class="col-md-6">
                                <label for="name" class="form-label">Warehouse Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $warehouse->name) }}"
                                       placeholder="Enter warehouse name"
                                       @if(!auth()->user()->isSuperAdmin()) readonly @endif
                                       required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Warehouse Code --}}
                            <div class="col-md-6">
                                <label for="code" class="form-label">Warehouse Code <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('code') is-invalid @enderror" 
                                       id="code" 
                                       name="code" 
                                       value="{{ old('code', $warehouse->code) }}"
                                       placeholder="Enter warehouse code"
                                       style="text-transform: uppercase;"
                                       @if(!auth()->user()->isSuperAdmin()) readonly @endif
                                       required>
                                @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Must be unique</small>
                            </div>

                            {{-- Address --}}
                            <div class="col-md-12">
                                <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                          id="address" 
                                          name="address" 
                                          rows="3"
                                          placeholder="Enter complete warehouse address"
                                          @if(!auth()->user()->isSuperAdmin()) readonly @endif
                                          required>{{ old('address', $warehouse->address) }}</textarea>
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
                                        @if(!auth()->user()->isSuperAdmin()) disabled @endif
                                        required>
                                    <option value="active" {{ old('status', $warehouse->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $warehouse->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Admins Assignment - Only for Super Admin --}}
                            @if(auth()->user()->isSuperAdmin())
                            <div class="col-md-12">
                                <label for="admin_id" class="form-label">
                                    <i class="bi bi-person me-1"></i> Assign Admin
                                </label>
                                <select class="form-select @error('admin_id') is-invalid @enderror" 
                                        id="admin_id" 
                                        name="admin_id">
                                    <option value="">-- Select an Admin --</option>
                                    @forelse($availableUsers as $user)
                                    <option value="{{ $user->id }}" 
                                            {{ old('admin_id', $currentAdmins[0] ?? null) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                    @empty
                                    <option disabled>No users available</option>
                                    @endforelse
                                </select>
                                @error('admin_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-2">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Each warehouse can only be assigned to one admin.
                                </small>
                            </div>
                            @endif
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            @if(auth()->user()->isSuperAdmin())
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Update Warehouse
                            </button>
                            @endif
                            <a href="{{ route('admin.warehouses.show', $warehouse) }}" class="btn btn-secondary">
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
                        <i class="bi bi-info-circle me-1"></i> Update Information
                    </h5>
                    <ul class="mb-0 small">
                        <li><strong>Created:</strong> {{ $warehouse->created_at->format('M d, Y h:i A') }}</li>
                        <li><strong>Last Updated:</strong> {{ $warehouse->updated_at->format('M d, Y h:i A') }}</li>
                        <li><strong>Current Status:</strong> 
                            @if($warehouse->status === 'active')
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-warning">Inactive</span>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-exclamation-triangle me-1"></i> Important Notes
                    </h6>
                    <ul class="mb-0 small text-muted">
                        @if(auth()->user()->isSuperAdmin())
                        <li>Ensure the warehouse code remains unique across all warehouses.</li>
                        <li>Deactivating will prevent inventory transactions.</li>
                        <li>Admin changes will be logged for audit purposes.</li>
                        @else
                        <li>You can view warehouse information here.</li>
                        <li>Contact super admin to make any changes.</li>
                        <li>Your access is limited to this warehouse only.</li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
