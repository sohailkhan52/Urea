@extends('layouts.admin')

@section('title', 'Companies')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Companies Management</h1>
    </div>

    {{-- Search and Filter --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.companies.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search by name, code, contact, email, or phone">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-secondary">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Companies Table --}}
    <div class="card">
        <div class="card-body">
            @if($companies->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Company</th>
                            <th>Code</th>
                            <th>Contact Person</th>
                            <th>Contact Info</th>
                            <th style="width: 120px;">Status</th>
                            <th style="width: 180px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($companies as $company)
                        <tr>
                            <td>
                                <img src="{{ $company->logo_url }}" 
                                     alt="{{ $company->name }}" 
                                     class="rounded"
                                     style="width: 40px; height: 40px; object-fit: cover;">
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $company->name }}</div>
                                @if($company->website)
                                <small class="text-muted">
                                    <a href="{{ $company->website }}" target="_blank" class="text-decoration-none">
                                        <i class="bi bi-globe"></i> {{ Str::limit($company->website, 30) }}
                                    </a>
                                </small>
                                @endif
                            </td>
                            <td>
                                <code class="bg-light px-2 py-1 rounded">{{ $company->code }}</code>
                            </td>
                            <td>
                                {{ $company->contact_person ?? '—' }}
                            </td>
                            <td>
                                @if($company->email)
                                <div class="text-muted small">
                                    <i class="bi bi-envelope"></i> {{ $company->email }}
                                </div>
                                @endif
                                @if($company->phone)
                                <div class="text-muted small">
                                    <i class="bi bi-telephone"></i> {{ $company->phone }}
                                </div>
                                @endif
                                @if(!$company->email && !$company->phone)
                                —
                                @endif
                            </td>
                            <td>
                                @if($company->status === 'active')
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-warning">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    @can('companies.view')
                                    <a href="{{ route('admin.companies.show', $company) }}" 
                                       class="btn btn-info" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('companies.update')
                                    <a href="{{ route('admin.companies.edit', $company) }}" 
                                       class="btn btn-warning" 
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('companies.delete')
                                    <form action="{{ route('admin.companies.destroy', $company) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this company?');">
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
                {{ $companies->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-building" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">
                    @if(request()->hasAny(['search', 'status']))
                        No companies found matching your criteria.
                    @else
                        No companies yet. Create your first company to get started.
                    @endif
                </p>
                @can('companies.create')
                @if(!request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.companies.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Add Company
                </a>
                @endif
                @endcan
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
