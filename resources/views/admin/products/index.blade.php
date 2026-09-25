@extends('layouts.admin')

@section('title', 'Products')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Products</h3>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Product
                </a>
            </div>
        </div>
    </div>

    @if($products->count())
        <div class="d-flex justify-content-end align-items-center mb-2">
            <form action="{{ route('admin.products.index') }}" method="GET" class="d-flex align-items-center gap-2">
                <label for="product-per-page" class="small text-muted mb-0">Per Page</label>
                <select id="product-per-page" name="per_page" class="form-select form-select-sm" style="width: 82px;" onchange="this.form.submit()">
                    @foreach([10, 25, 50, 100] as $option)
                        <option value="{{ $option }}" @selected(request('per_page', 15) == $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Unit</th>
                            <th>Purchase Price</th>
                            <th>Sale Price</th>
                            <th>Profit Margin</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>
                                    <strong>{{ $product->name }}</strong>
                                </td>
                                <td>{{ $product->unit }}</td>
                                <td>Rs. {{ number_format($product->purchase_price, 0) }}</td>
                                <td>Rs. {{ number_format($product->sale_price, 0) }}</td>
                                <td>
                                    {{ number_format($product->profit_margin, 0) }}%
                                </td>
                                <td>{{ $product->created_at->format('Y-m-d H:i') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.products.show', $product) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row align-items-center g-2 mt-3">
            <div class="col-md-6">
                <small class="text-muted">Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products</small>
            </div>
            <div class="col-md-6 d-flex justify-content-md-end">
                <nav aria-label="Page navigation">
                    {{ $products->links() }}
                </nav>
            </div>
        </div>
    @else
        <div class="alert alert-info">
            No products found. <a href="{{ route('admin.products.create') }}">Create one now</a>
        </div>
    @endif
</div>
@endsection
