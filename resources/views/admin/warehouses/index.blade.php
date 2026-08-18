@extends('layouts.admin')

@section('title', 'Warehouses')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Warehouse Management</h1>
        @can('warehouses.create')
        <a href="{{ route('admin.warehouses.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add Warehouse
        </a>
        @endcan
    </div>

    {{-- Search and Filter --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.warehouses.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search by name, code, address, branch, or manager">
                    </div>
                    <div class="col-md-2">
                        <label for="branch_id" class="form-label">Branch</label>
                        <select class="form-select" id="branch_id" name="branch_id">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">All Types</option>
                            @foreach($warehouseTypes as $value => $label)
                            <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>
                                {{ $label }}
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
                        <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Warehouses Table --}}
    <div class="card">
        <div class="card-body">
            @if($warehouses->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Warehouse</th>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Branch</th>
                            <th>Manager</th>
                            <th>Address</th>
                            <th style="width: 120px;">Status</th>
                            <th style="width: 250px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warehouses as $warehouse)
                        <tr>
                            <td>
                                <div class="fw-semibold">
                                    <i class="bi bi-house-door me-1"></i>
                                    {{ $warehouse->name }}
                                </div>
                            </td>
                            <td>
                                <code class="bg-light px-2 py-1 rounded">{{ $warehouse->code }}</code>
                            </td>
                            <td>
                                @if($warehouse->type === 'main_warehouse')
                                <span class="badge bg-primary">
                                    <i class="bi bi-building me-1"></i>{{ $warehouse->type_label }}
                                </span>
                                @elseif($warehouse->type === 'branch_warehouse')
                                <span class="badge bg-info">
                                    <i class="bi bi-shop me-1"></i>{{ $warehouse->type_label }}
                                </span>
                                @else
                                <span class="badge bg-secondary">
                                    <i class="bi bi-shop-window me-1"></i>{{ $warehouse->type_label }}
                                </span>
                                @endif
                            </td>
                            <td>
                                @if($warehouse->branch)
                                <small>
                                    <i class="bi bi-geo-alt me-1"></i>
                                    {{ $warehouse->branch->name }}
                                </small>
                                @else
                                <small class="text-muted">—</small>
                                @endif
                            </td>
                            <td>
                                @if($warehouse->manager)
                                <small>
                                    <i class="bi bi-person me-1"></i>
                                    {{ $warehouse->manager->name }}
                                </small>
                                @else
                                <small class="text-muted">Not assigned</small>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ Str::limit($warehouse->address, 40) }}</small>
                            </td>
                            <td>
                                @if($warehouse->status === 'active')
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-warning">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-2 justify-content-end flex-wrap">
                                    <div class="btn-group btn-group-sm" role="group">
                                        @can('warehouses.view')
                                        <a href="{{ route('admin.warehouses.show', $warehouse) }}" 
                                           class="btn btn-info" 
                                           title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.warehouses.inventory', $warehouse) }}" 
                                           class="btn btn-secondary" 
                                           title="Inventory">
                                            <i class="bi bi-box-seam"></i>
                                        </a>
                                        @endcan
                                        
                                        @can('warehouses.update')
                                        <a href="{{ route('admin.warehouses.edit', $warehouse) }}" 
                                           class="btn btn-warning" 
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @endcan
                                        
                                        @can('warehouses.delete')
                                        <form action="{{ route('admin.warehouses.destroy', $warehouse) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this warehouse? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>

                                    {{-- Status Actions Dropdown --}}
                                    @can('warehouses.update')
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" 
                                                class="btn btn-outline-secondary dropdown-toggle" 
                                                data-bs-toggle="dropdown" 
                                                aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @if($warehouse->status === 'inactive')
                                            <li>
                                                <form action="{{ route('admin.warehouses.activate', $warehouse) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="dropdown-item text-success">
                                                        <i class="bi bi-check-circle me-1"></i> Activate
                                                    </button>
                                                </form>
                                            </li>
                                            @else
                                            <li>
                                                <form action="{{ route('admin.warehouses.deactivate', $warehouse) }}" 
                                                      method="POST"
                                                      onsubmit="return confirm('Are you sure you want to deactivate this warehouse?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="dropdown-item text-warning">
                                                        <i class="bi bi-x-circle me-1"></i> Deactivate
                                                    </button>
                                                </form>
                                            </li>
                                            @endif
                                        </ul>
                                    </div>
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
                {{ $warehouses->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-house-door" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">
                    @if(request()->hasAny(['search', 'branch_id', 'type', 'status']))
                        No warehouses found matching your criteria.
                    @else
                        No warehouses yet. Create your first warehouse to get started.
                    @endif
                </p>
                @can('warehouses.create')
                @if(!request()->hasAny(['search', 'branch_id', 'type', 'status']))
                <a href="{{ route('admin.warehouses.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Add Warehouse
                </a>
                @endif
                @endcan
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
