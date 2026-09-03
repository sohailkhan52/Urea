@extends('layouts.admin')

@section('title', 'Edit Product - ' . $product->name)

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Edit Product: {{ $product->name }}</h3>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
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
                                <option value="KG" {{ old('unit', $product->unit) === 'KG' ? 'selected' : '' }}>Kilogram (KG)</option>
                                <option value="MG" {{ old('unit', $product->unit) === 'MG' ? 'selected' : '' }}>Milligram (MG)</option>
                                <option value="Piece" {{ old('unit', $product->unit) === 'Piece' ? 'selected' : '' }}>Piece</option>
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
                                value="{{ old('purchase_price', $product->purchase_price) }}"
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
                                value="{{ old('sale_price', $product->sale_price) }}"
                                min="0" 
                                step="0.01"
                                required>
                            @error('sale_price')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
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
