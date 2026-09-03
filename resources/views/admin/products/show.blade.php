@extends('layouts.admin')

@section('title', 'View Product - ' . $product->name)

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">{{ $product->name }}</h3>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit
                </a>
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
                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <p class="form-control-plaintext">{{ $product->name }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Unit</label>
                        <p class="form-control-plaintext">{{ $product->unit }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Purchase Price</label>
                        <p class="form-control-plaintext">Rs. {{ number_format($product->purchase_price, 2) }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sale Price</label>
                        <p class="form-control-plaintext">Rs. {{ number_format($product->sale_price, 2) }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Profit Margin</label>
                        <p class="form-control-plaintext">{{ number_format($product->profit_margin, 2) }}%</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Created</label>
                        <p class="form-control-plaintext">{{ $product->created_at->format('Y-m-d H:i:s') }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Updated</label>
                        <p class="form-control-plaintext">{{ $product->updated_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
