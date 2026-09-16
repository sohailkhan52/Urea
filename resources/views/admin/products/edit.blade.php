@extends('layouts.admin')

@section('title', 'Edit Product - ' . $product->name)

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Edit Product: {{ $product->name }}</h3>
            </div>
<<<<<<< HEAD
            <div class="col-auto">
            </div>
=======
>>>>>>> fda2d10da9b7d26919ff41c4eda83db54f46c0be
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.products.update', $product) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                class="form-control @error('name') is-invalid @enderror" 
                                id="name" 
                                name="name" 
                                value="{{ old('name', $product->name) }}"
                                required>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
                            <select class="form-select @error('unit') is-invalid @enderror" id="unit" name="unit" required>
                                <option value="">-- Select Unit --</option>
                                @foreach(\App\Models\Product::getUnits() as $value => $label)
                                    <option value="{{ $value }}" {{ old('unit', $product->unit) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('unit')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="purchase_price" class="form-label">Purchase Price (Rs.) <span class="text-danger">*</span></label>
                            <input 
                                type="number" 
                                class="form-control @error('purchase_price') is-invalid @enderror" 
                                id="purchase_price" 
                                name="purchase_price" 
                                value="{{ old('purchase_price', rtrim(rtrim(sprintf('%.2f', $product->purchase_price), '0'), '.')) }}"
                                min="0" 
                                step="0.01"
                                required>
                            @error('purchase_price')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="sale_price" class="form-label">Sale Price (Rs.) <span class="text-danger">*</span></label>
                            <input 
                                type="number" 
                                class="form-control @error('sale_price') is-invalid @enderror" 
                                id="sale_price" 
                                name="sale_price" 
                                value="{{ old('sale_price', rtrim(rtrim(sprintf('%.2f', $product->sale_price), '0'), '.')) }}"
                                min="0" 
                                step="0.01"
                                required>
                            @error('sale_price')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="minimum_stock_level" class="form-label">Minimum Stock Level</label>
                            <input 
                                type="number" 
                                class="form-control" 
                                id="minimum_stock_level" 
                                name="minimum_stock_level" 
                                value="{{ old('minimum_stock_level', $product->minimum_stock_level ?? '') }}"
                                min="0" 
                                step="1"
                                placeholder="10">
                            <small class="text-muted d-block mt-1">Alert will show when stock falls below this level</small>
                        </div>

                        <div class="mb-0 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Product
                            </button>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
