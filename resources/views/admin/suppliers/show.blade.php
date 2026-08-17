@extends('layouts.admin')

@section('title', 'Supplier Details')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Supplier Details</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 mt-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.suppliers.index') }}">Suppliers</a></li>
                        <li class="breadcrumb-item active">{{ $supplier->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                @can('suppliers.update')
                <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-warning">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                @endcan
                <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Basic Information --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-1"></i> Basic Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Supplier Name</label>
                            <p class="fw-semibold">{{ $supplier->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Company Name</label>
                            <p>{{ $supplier->company_name ?? '—' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Contact Person</label>
                            <p>{{ $supplier->contact_person ?? '—' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Status</label>
                            <p>
                                @if($supplier->status === 'active')
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-warning">Inactive</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact Information --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-telephone me-1"></i> Contact Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Phone</label>
                            <p>
                                <i class="bi bi-telephone me-1"></i>
                                <a href="tel:{{ $supplier->phone }}">{{ $supplier->phone }}</a>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Email</label>
                            <p>
                                @if($supplier->email)
                                <i class="bi bi-envelope me-1"></i>
                                <a href="mailto:{{ $supplier->email }}">{{ $supplier->email }}</a>
                                @else
                                —
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">City</label>
                            <p>
                                @if($supplier->city)
                                <i class="bi bi-geo-alt me-1"></i>
                                {{ $supplier->city }}
                                @else
                                —
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">NTN</label>
                            <p>
                                @if($supplier->ntn)
                                <code class="bg-light px-2 py-1 rounded">{{ $supplier->ntn }}</code>
                                @else
                                —
                                @endif
                            </p>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-muted small">Address</label>
                            <p>{{ $supplier->address ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Purchase History (Placeholder for future) --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-cart me-1"></i> Purchase History
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="bi bi-cart" style="font-size: 2rem; color: #ccc;"></i>
                        <p class="text-muted mt-2 mb-0">
                            Purchase history will be available once the Purchase module is implemented.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Quick Actions --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning me-1"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @can('suppliers.update')
                        <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-outline-warning">
                            <i class="bi bi-pencil me-1"></i> Edit Supplier
                        </a>

                        @if($supplier->status === 'inactive')
                        <form action="{{ route('admin.suppliers.activate', $supplier) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-success w-100">
                                <i class="bi bi-check-circle me-1"></i> Activate Supplier
                            </button>
                        </form>
                        @else
                        <form action="{{ route('admin.suppliers.deactivate', $supplier) }}" 
                              method="POST"
                              onsubmit="return confirm('Are you sure you want to deactivate this supplier?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-warning w-100">
                                <i class="bi bi-x-circle me-1"></i> Deactivate Supplier
                            </button>
                        </form>
                        @endif
                        @endcan

                        @can('suppliers.delete')
                        <form action="{{ route('admin.suppliers.destroy', $supplier) }}" 
                              method="POST"
                              onsubmit="return confirm('Are you sure you want to delete this supplier? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bi bi-trash me-1"></i> Delete Supplier
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
            </div>

            {{-- Statistics (Placeholder for future) --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up me-1"></i> Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Total Purchase Orders</small>
                        <h4 class="mb-0">—</h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Total Purchase Value</small>
                        <h4 class="mb-0">—</h4>
                    </div>
                    <div>
                        <small class="text-muted">Last Purchase</small>
                        <p class="mb-0">—</p>
                    </div>
                    <hr>
                    <small class="text-muted">Available after Purchase module implementation</small>
                </div>
            </div>

            {{-- Timestamps --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-1"></i> Timestamps
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Created</label>
                        <p class="mb-0">{{ $supplier->created_at->format('M d, Y h:i A') }}</p>
                        <small class="text-muted">{{ $supplier->created_at->diffForHumans() }}</small>
                    </div>
                    <div>
                        <label class="text-muted small">Last Updated</label>
                        <p class="mb-0">{{ $supplier->updated_at->format('M d, Y h:i A') }}</p>
                        <small class="text-muted">{{ $supplier->updated_at->diffForHumans() }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
