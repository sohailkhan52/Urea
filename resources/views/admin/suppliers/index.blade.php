@extends('layouts.admin')

@section('title', 'Suppliers')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Supplier Management</h1>
    </div>

    {{-- Search and Filter --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.suppliers.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search by name, company, contact, phone, email, or NTN">
                    </div>
                    <div class="col-md-3">
                        <label for="city" class="form-label">City</label>
                        <select class="form-select" id="city" name="city">
                            <option value="">All Cities</option>
                            @foreach($cities as $city)
                            <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>
                                {{ $city }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-secondary">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Suppliers Table --}}
    <div class="card">
        <div class="card-body">
            @if($suppliers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Contact Person</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>City</th>
                            <th>NTN</th>
                            <th style="width: 120px;">Status</th>
                            <th style="width: 180px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suppliers as $supplier)
                        <tr>
                            <td>
                                <div class="fw-semibold">
                                    <i class="bi bi-person-badge me-1"></i>
                                    {{ $supplier->name }}
                                </div>
                                @if($supplier->company_name)
                                <small class="text-muted">{{ $supplier->company_name }}</small>
                                @endif
                            </td>
                            <td>
                                @if($supplier->contact_person)
                                <small>{{ $supplier->contact_person }}</small>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <small>
                                    <i class="bi bi-telephone me-1"></i>
                                    {{ $supplier->phone }}
                                </small>
                            </td>
                            <td>
                                @if($supplier->email)
                                <small>
                                    <i class="bi bi-envelope me-1"></i>
                                    {{ $supplier->email }}
                                </small>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($supplier->city)
                                <small>
                                    <i class="bi bi-geo-alt me-1"></i>
                                    {{ $supplier->city }}
                                </small>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($supplier->ntn)
                                <code class="bg-light px-2 py-1 rounded">{{ $supplier->ntn }}</code>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($supplier->status === 'active')
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-warning">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    @can('suppliers.view')
                                    <a href="{{ route('admin.suppliers.show', $supplier) }}" 
                                       class="btn btn-info" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('suppliers.update')
                                    <a href="{{ route('admin.suppliers.edit', $supplier) }}" 
                                       class="btn btn-warning" 
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('suppliers.delete')
                                    <form action="{{ route('admin.suppliers.destroy', $supplier) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this supplier?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>

                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $suppliers->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-person-badge" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">
                    @if(request()->hasAny(['search', 'city', 'status']))
                        No suppliers found matching your criteria.
                    @else
                        No suppliers yet. Create your first supplier to get started.
                    @endif
                </p>
                @can('suppliers.create')
                @if(!request()->hasAny(['search', 'city', 'status']))
                <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Add Supplier
                </a>
                @endif
                @endcan
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
