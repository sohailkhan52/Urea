@extends('layouts.admin')

@section('title', 'Create Product')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Create New Product</h3>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.products.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                class="form-control @error('name') is-invalid @enderror" 
                                id="name" 
                                name="name" 
                                value="{{ old('name') }}"
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
                                    <option value="{{ $value }}" {{ old('unit') === $value ? 'selected' : '' }}>{{ $label }}</option>
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
                                value="{{ old('purchase_price') }}"
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
                                value="{{ old('sale_price') }}"
                                min="0" 
                                step="0.01"
                                required>
                            @error('sale_price')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-0 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Product
                            </button>
                            <a href="{{ route('admin.reports.products.index') }}" class="btn btn-secondary">
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
