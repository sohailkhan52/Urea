@extends('layouts.admin')

@section('title', 'View Customer - ' . $customer->name)

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Customer Details</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 mt-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
                        <li class="breadcrumb-item active">{{ $customer->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="btn-group">
                @can('customers.update')
                <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-warning">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                @endcan
                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-list me-1"></i> All Customers
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Customer Header --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                <i class="bi bi-person me-2"></i>
                                {{ $customer->name }}
                            </h5>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-{{ $customer->type_badge }} me-2">
                                {{ $customer->type_label }}
                            </span>
                            <span class="badge bg-{{ $customer->status_badge }}">
                                {{ $customer->status_label }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        {{-- Personal Information --}}
                        @if($customer->father_name)
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Father's Name</small>
                            <p class="mb-0">
                                <strong>{{ $customer->father_name }}</strong>
                            </p>
                        </div>
                        @endif

                        {{-- Contact Information --}}
                        @if($customer->phone)
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Phone</small>
                            <p class="mb-0">
                                <i class="bi bi-telephone me-1"></i>
                                <strong>{{ $customer->phone }}</strong>
                            </p>
                        </div>
                        @endif

                        @if($customer->email)
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Email</small>
                            <p class="mb-0">
                                <i class="bi bi-envelope me-1"></i>
                                <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                            </p>
                        </div>
                        @endif

                        @if($customer->cnic)
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">CNIC</small>
                            <p class="mb-0">
                                <code class="bg-light px-2 py-1 rounded">{{ $customer->cnic }}</code>
                            </p>
                        </div>
                        @endif

                        {{-- Address Information --}}
                        @if($customer->village)
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Village</small>
                            <p class="mb-0">
                                <i class="bi bi-geo-alt me-1"></i>
                                <strong>{{ $customer->village }}</strong>
                            </p>
                        </div>
                        @endif

                        @if($customer->city)
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">City</small>
                            <p class="mb-0">
                                <i class="bi bi-geo-alt me-1"></i>
                                <strong>{{ $customer->city }}</strong>
                            </p>
                        </div>
                        @endif

                        @if($customer->address)
                        <div class="col-md-12">
                            <small class="text-muted d-block mb-2">Address</small>
                            <p class="mb-0">
                                {{ $customer->address }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Quick Stats --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Quick Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Customer ID</small>
                        <code class="bg-light px-2 py-1 rounded">#{{ $customer->id }}</code>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Type</small>
                        <strong>{{ $customer->type_label }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Status</small>
                        <span class="badge bg-{{ $customer->status_badge }}">
                            {{ $customer->status_label }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Audit Information --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Audit Trail</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Created</small>
                        <small>{{ $customer->created_at->format('M d, Y h:i A') }}</small>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted d-block">Last Updated</small>
                        <small>{{ $customer->updated_at->format('M d, Y h:i A') }}</small>
                    </div>
                </div>
            </div>

            {{-- Customer Type Info --}}
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-info-circle me-1"></i>
                        {{ $customer->type_label }}
                    </h6>
                    @if($customer->isFarmer())
                        <p class="mb-0 small text-muted">
                            Farmers are individual agricultural producers who purchase fertilizers for their crops.
                        </p>
                    @elseif($customer->isDealer())
                        <p class="mb-0 small text-muted">
                            Dealers are wholesale or retail businesses that buy and sell fertilizer products.
                        </p>
                    @else
                        <p class="mb-0 small text-muted">
                            Retail customers are small shops, nurseries, or other retail businesses.
                        </p>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            @can('customers.update')
            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-warning">
                        <i class="bi bi-pencil me-1"></i> Edit Customer
                    </a>

                    @if($customer->isActive())
                    <form action="{{ route('admin.customers.deactivate', $customer) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-secondary w-100" onclick="return confirm('Deactivate this customer?');">
                            <i class="bi bi-x-circle me-1"></i> Deactivate
                        </button>
                    </form>
                    @else
                    <form action="{{ route('admin.customers.activate', $customer) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle me-1"></i> Activate
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endcan

            @can('customers.delete')
            <div class="card mt-3">
                <div class="card-body">
                    <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" 
                          onsubmit="return confirm('Are you sure? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash me-1"></i> Delete Customer
                        </button>
                    </form>
                </div>
            </div>
            @endcan
        </div>
    </div>
</div>
@endsection
