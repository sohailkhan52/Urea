@extends('layouts.admin')

@section('title', 'Products')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Products Management</h1>
        @can('products.create')
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add Product
        </a>
        @endcan
    </div>

    {{-- Search and Filter --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.products.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search by name, SKU, barcode, or company">
                    </div>
                    <div class="col-md-2">
                        <label for="company_id" class="form-label">Company</label>
                        <select class="form-select" id="company_id" name="company_id">
                            <option value="">All Companies</option>
                            @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
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
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Products Table --}}
    <div class="card">
        <div class="card-body">
            @if($products->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Company</th>
                            <th>Category</th>
                            <th>Weight</th>
                            <th class="text-end">Sale Price</th>
                            <th style="width: 120px;">Status</th>
                            <th style="width: 180px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td>
                                <img src="{{ $product->image_url }}" 
                                     alt="{{ $product->name }}" 
                                     class="rounded"
                                     style="width: 40px; height: 40px; object-fit: cover;">
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $product->name }}</div>
                                @if($product->barcode)
                                <small class="text-muted">
                                    <i class="bi bi-upc-scan"></i> {{ $product->barcode }}
                                </small>
                                @endif
                            </td>
                            <td>
                                <code class="bg-light px-2 py-1 rounded">{{ $product->sku }}</code>
                            </td>
                            <td>
                                <small>{{ $product->company->name }}</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $product->category->name }}</span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $product->weight_display }}</small>
                            </td>
                            <td class="text-end">
                                <strong>{{ $product->formatted_sale_price }}</strong>
                            </td>
                            <td>
                                @if($product->status === 'active')
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-warning">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    @can('products.view')
                                    <a href="{{ route('admin.products.show', $product) }}" 
                                       class="btn btn-info" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('products.update')
                                    <a href="{{ route('admin.products.edit', $product) }}" 
                                       class="btn btn-warning" 
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('products.delete')
                                    <form action="{{ route('admin.products.destroy', $product) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>

                                {{-- Status Actions Dropdown --}}
                                @can('products.update')
                                <div class="btn-group btn-group-sm ms-1" role="group">
                                    <button type="button" 
                                            class="btn btn-outline-secondary dropdown-toggle" 
                                            data-bs-toggle="dropdown" 
                                            aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @if($product->status === 'inactive')
                                        <li>
                                            <form action="{{ route('admin.products.activate', $product) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="dropdown-item text-success">
                                                    <i class="bi bi-check-circle me-1"></i> Activate
                                                </button>
                                            </form>
                                        </li>
                                        @else
                                        <li>
                                            <form action="{{ route('admin.products.deactivate', $product) }}" 
                                                  method="POST"
                                                  onsubmit="return confirm('Are you sure you want to deactivate this product?');">
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
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $products->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-box-seam" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">
                    @if(request()->hasAny(['search', 'company_id', 'category_id', 'status']))
                        No products found matching your criteria.
                    @else
                        No products yet. Create your first product to get started.
                    @endif
                </p>
                @can('products.create')
                @if(!request()->hasAny(['search', 'company_id', 'category_id', 'status']))
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Add Product
                </a>
                @endif
                @endcan
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
