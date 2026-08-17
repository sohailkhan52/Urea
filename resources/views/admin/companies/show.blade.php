@extends('layouts.admin')

@section('title', $company->name)

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h1 class="h3 mb-0">Company Details</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.companies.index') }}">Companies</a></li>
                <li class="breadcrumb-item active">{{ $company->name }}</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Company Profile --}}
            <div class="card mb-4">
                <div class="card-body text-center">
                    <img src="{{ $company->logo_url }}" 
                         alt="{{ $company->name }}" 
                         class="rounded mb-3"
                         style="width: 150px; height: 150px; object-fit: contain;">
                    <h3 class="mb-1">{{ $company->name }}</h3>
                    <p class="text-muted mb-2">
                        <code class="bg-light px-3 py-1 rounded">{{ $company->code }}</code>
                    </p>
                    <div class="mb-3">
                        @if($company->status === 'active')
                        <span class="badge bg-success">Active</span>
                        @else
                        <span class="badge bg-warning">Inactive</span>
                        @endif
                    </div>

                    {{-- Quick Actions --}}
                    <div class="d-flex gap-2 justify-content-center">
                        @can('companies.update')
                        <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-warning">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </a>

                        @if($company->status === 'inactive')
                        <form action="{{ route('admin.companies.activate', $company) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i> Activate
                            </button>
                        </form>
                        @else
                        <form action="{{ route('admin.companies.deactivate', $company) }}" 
                              method="POST" 
                              class="d-inline"
                              onsubmit="return confirm('Are you sure you want to deactivate this company?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-warning">
                                <i class="bi bi-x-circle me-1"></i> Deactivate
                            </button>
                        </form>
                        @endif
                        @endcan

                        @can('companies.delete')
                        <form action="{{ route('admin.companies.destroy', $company) }}" 
                              method="POST" 
                              class="d-inline"
                              onsubmit="return confirm('Are you sure you want to delete this company? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash me-1"></i> Delete
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
            </div>

            {{-- Contact Information --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person-lines-fill me-1"></i> Contact Information
                    </h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Contact Person:</dt>
                        <dd class="col-sm-8">{{ $company->contact_person ?? '—' }}</dd>

                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8">
                            @if($company->email)
                            <a href="mailto:{{ $company->email }}" class="text-decoration-none">
                                <i class="bi bi-envelope me-1"></i> {{ $company->email }}
                            </a>
                            @else
                            —
                            @endif
                        </dd>

                        <dt class="col-sm-4">Phone:</dt>
                        <dd class="col-sm-8">
                            @if($company->phone)
                            <a href="tel:{{ $company->phone }}" class="text-decoration-none">
                                <i class="bi bi-telephone me-1"></i> {{ $company->phone }}
                            </a>
                            @else
                            —
                            @endif
                        </dd>

                        <dt class="col-sm-4">Website:</dt>
                        <dd class="col-sm-8">
                            @if($company->website)
                            <a href="{{ $company->website }}" target="_blank" class="text-decoration-none">
                                <i class="bi bi-globe me-1"></i> {{ $company->website }}
                                <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                            </a>
                            @else
                            —
                            @endif
                        </dd>

                        <dt class="col-sm-4">Address:</dt>
                        <dd class="col-sm-8">
                            @if($company->address)
                            {{ $company->address }}
                            @else
                            —
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

            {{-- Products Section (Placeholder for future) --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-box-seam me-1"></i> Products
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-box-seam" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">Products module will be available soon.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Account Information --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-1"></i> Account Information
                    </h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 small">Company Code:</dt>
                        <dd class="col-sm-7 small">
                            <code class="bg-light px-2 py-1 rounded">{{ $company->code }}</code>
                        </dd>

                        <dt class="col-sm-5 small">Status:</dt>
                        <dd class="col-sm-7 small">
                            @if($company->status === 'active')
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-warning">Inactive</span>
                            @endif
                        </dd>

                        <dt class="col-sm-5 small">Created:</dt>
                        <dd class="col-sm-7 small text-muted">
                            {{ $company->created_at->format('M d, Y') }}
                            <br>
                            <span class="text-muted">{{ $company->created_at->diffForHumans() }}</span>
                        </dd>

                        <dt class="col-sm-5 small">Last Updated:</dt>
                        <dd class="col-sm-7 small text-muted">
                            {{ $company->updated_at->format('M d, Y') }}
                            <br>
                            <span class="text-muted">{{ $company->updated_at->diffForHumans() }}</span>
                        </dd>
                    </dl>
                </div>
            </div>

            {{-- Statistics (Placeholder for future) --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-bar-chart me-1"></i> Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Total Products</span>
                        <span class="badge bg-secondary">0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Active Products</span>
                        <span class="badge bg-success">0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Total Sales</span>
                        <span class="badge bg-primary">0</span>
                    </div>
                    <hr>
                    <p class="small text-muted mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Statistics will be available when products and transactions modules are implemented.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
