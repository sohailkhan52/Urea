@extends('layouts.admin')

@section('title', 'Create Product')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h1 class="h3 mb-0">Create New Product</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
    </div>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Product Information</h5>
                    </div>
                    <div class="card-body">
                        {{-- Product Name --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}"
                                   required
                                   autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">e.g., Sona Urea, Engro Zarkhez Urea</div>
                        </div>

                        <div class="row">
                            {{-- Company --}}
                            <div class="col-md-6 mb-3">
                                <label for="company_id" class="form-label">Company <span class="text-danger">*</span></label>
                                <select class="form-select @error('company_id') is-invalid @enderror" 
                                        id="company_id" 
                                        name="company_id" 
                                        required>
                                    <option value="">Select Company</option>
                                    @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('company_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Category --}}
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-select @error('category_id') is-invalid @enderror" 
                                            id="category_id" 
                                            name="category_id" 
                                            required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addCategoryModal" title="Add New Category">
                                        <i class="bi bi-plus-circle"></i>
                                    </button>
                                </div>
                                @error('category_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            {{-- SKU --}}
                            <div class="col-md-6 mb-3">
                                <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('sku') is-invalid @enderror" 
                                       id="sku" 
                                       name="sku" 
                                       value="{{ old('sku') }}"
                                       required
                                       style="text-transform: uppercase;">
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Unique code, e.g., SONA-UREA-50</div>
                            </div>

                            {{-- Barcode --}}
                            <div class="col-md-6 mb-3">
                                <label for="barcode" class="form-label">Barcode</label>
                                <input type="text" 
                                       class="form-control @error('barcode') is-invalid @enderror" 
                                       id="barcode" 
                                       name="barcode" 
                                       value="{{ old('barcode') }}">
                                @error('barcode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            {{-- Bag Weight --}}
                            <div class="col-md-6 mb-3">
                                <label for="bag_weight" class="form-label">Bag Weight <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control @error('bag_weight') is-invalid @enderror" 
                                       id="bag_weight" 
                                       name="bag_weight" 
                                       value="{{ old('bag_weight') }}"
                                       step="0.01"
                                       min="0.01"
                                       required>
                                @error('bag_weight')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Weight Unit --}}
                            <div class="col-md-6 mb-3">
                                <label for="weight_unit" class="form-label">Weight Unit <span class="text-danger">*</span></label>
                                <select class="form-select @error('weight_unit') is-invalid @enderror" 
                                        id="weight_unit" 
                                        name="weight_unit" 
                                        required>
                                    @foreach($weightUnits as $key => $label)
                                    <option value="{{ $key }}" {{ old('weight_unit', 'KG') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('weight_unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Pricing & Stock</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            {{-- Purchase Price --}}
                            <div class="col-md-4 mb-3">
                                <label for="purchase_price" class="form-label">Purchase Price (Rs.) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control @error('purchase_price') is-invalid @enderror" 
                                       id="purchase_price" 
                                       name="purchase_price" 
                                       value="{{ old('purchase_price') }}"
                                       step="0.01"
                                       min="0"
                                       placeholder="0.00"
                                       required>
                                @error('purchase_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Sale Price --}}
                            <div class="col-md-4 mb-3">
                                <label for="sale_price" class="form-label">Sale Price (Rs.) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control @error('sale_price') is-invalid @enderror" 
                                       id="sale_price" 
                                       name="sale_price" 
                                       value="{{ old('sale_price') }}"
                                       step="0.01"
                                       min="0"
                                       placeholder="0.00"
                                       required>
                                @error('sale_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Minimum Stock Level --}}
                            <div class="col-md-4 mb-3">
                                <label for="minimum_stock_level" class="form-label">Min Stock Level <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control @error('minimum_stock_level') is-invalid @enderror" 
                                       id="minimum_stock_level" 
                                       name="minimum_stock_level" 
                                       value="{{ old('minimum_stock_level', 0) }}"
                                       min="0"
                                       required>
                                @error('minimum_stock_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Alert threshold</div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Note:</strong> Stock quantities are NOT managed here. Stock will be calculated from warehouse inventory and managed through purchases and sales.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Product Image</h5>
                    </div>
                    <div class="card-body">
                        {{-- Image Upload --}}
                        <div class="mb-3">
                            <label for="image" class="form-label">Product Image</label>
                            <input type="file" 
                                   class="form-control @error('image') is-invalid @enderror" 
                                   id="image" 
                                   name="image"
                                   accept="image/*">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Max 2MB. JPG, PNG, GIF</div>
                        </div>

                        {{-- Image Preview --}}
                        <div id="imagePreview" style="display: none;">
                            <img id="imagePreviewImg" src="" alt="Preview" class="img-fluid rounded">
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Status</h5>
                    </div>
                    <div class="card-body">
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-info-circle me-1"></i> Tips
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="small text-muted mb-0">
                            <li>SKU must be unique across all products</li>
                            <li>Sale price should be ≥ purchase price</li>
                            <li>Min stock level triggers low stock alerts</li>
                            <li>Stock is managed via warehouse inventory</li>
                        </ul>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Create Product
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Image preview
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreviewImg').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('imagePreview').style.display = 'none';
    }
});

// Auto-uppercase SKU
document.getElementById('sku').addEventListener('input', function(e) {
    e.target.value = e.target.value.toUpperCase();
});

// Handle add category modal
document.getElementById('addCategoryForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('{{ route("admin.categories.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        if (response.ok) {
            const result = await response.json();
            
            // Add new category to select
            const categorySelect = document.getElementById('category_id');
            const newOption = new Option(result.name, result.id, false, true);
            categorySelect.add(newOption);
            categorySelect.value = result.id;
            
            // Close modal and reset form
            bootstrap.Modal.getInstance(document.getElementById('addCategoryModal')).hide();
            this.reset();
            
            // Show success message
            alert('Category added successfully!');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error adding category');
    }
});
</script>
@endpush

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addCategoryForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_category_name" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="new_category_name" 
                               name="name"
                               placeholder="e.g., Urea, DAP, NPK"
                               required
                               autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="new_category_description" class="form-label">Description</label>
                        <textarea class="form-control" 
                                  id="new_category_description" 
                                  name="description"
                                  rows="2"
                                  placeholder="Enter description (optional)"></textarea>
                    </div>
                    <input type="hidden" name="status" value="active">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
