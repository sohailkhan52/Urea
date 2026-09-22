@extends('layouts.admin')

@section('title', 'Suppliers')

@section('content')
<style>
    #search::placeholder {
        color: #9ca3af;
        opacity: 1;
    }

    .supplier-create-modal-dialog {
        max-width: 625px;
    }

    .supplier-create-modal .modal-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #dee2e6;
    }

    .supplier-create-modal .modal-title {
        font-size: 1.55rem;
        color: #252a2f;
    }

    .supplier-create-modal .modal-body {
        padding: 1.5rem 1.6rem 1.75rem;
    }

    .supplier-create-modal .form-label {
        font-size: 1.05rem;
        color: #30343a;
        margin-bottom: 0.55rem;
    }

    .supplier-create-modal .form-control {
        min-height: 48px;
        border-radius: 8px;
        font-size: 1rem;
    }

    .supplier-create-modal textarea.form-control {
        min-height: 76px;
    }

    .supplier-create-modal .modal-footer {
        padding: 1.25rem 1.6rem;
        border-top: 1px solid #dee2e6;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold" style="font-size: 2.2rem; color: #1f2937;">Suppliers</h1>
            <p class="text-muted mb-0 mt-1">View and manage all suppliers in your inventory</p>
        </div>
        <button type="button" class="btn btn-primary btn-lg px-4" data-bs-toggle="modal" data-bs-target="#createSupplierModal" style="background: #1d74d9; border-color: #1d74d9; border-radius: 10px; font-weight: 600;">
            <i class="bi bi-plus-lg me-2"></i> Add Supplier
        </button>
    </div>

    <div class="modal fade supplier-create-modal" id="createSupplierModal" tabindex="-1" aria-labelledby="createSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered supplier-create-modal-dialog">
            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="createSupplierModalLabel">Create New Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.suppliers.store') }}" method="POST" autocomplete="new-password">
                    @csrf
                    <input type="hidden" name="status" value="active">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="new-supplier-name" class="form-label">Supplier Name <span class="text-danger">*</span></label>
                            <input type="text" id="new-supplier-name" name="name" class="form-control" placeholder="Enter supplier name" autocomplete="new-password" data-lpignore="true" data-1p-ignore="true" required>
                        </div>
                        <div class="mb-3">
                            <label for="new-supplier-company" class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" id="new-supplier-company" name="company_name" class="form-control" placeholder="Enter company name" autocomplete="new-password" data-lpignore="true" data-1p-ignore="true" required>
                        </div>
                        <div class="mb-3">
                            <label for="new-supplier-phone" class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" id="new-supplier-phone" name="phone" class="form-control" placeholder="03001234567" autocomplete="new-password" data-lpignore="true" data-1p-ignore="true" required>
                        </div>
                        <div class="mb-3">
                            <label for="new-supplier-email" class="form-label">Email</label>
                            <input type="email" id="new-supplier-email" name="email" class="form-control" placeholder="example@company.com" autocomplete="new-password" data-lpignore="true" data-1p-ignore="true">
                        </div>
                        <div class="mb-3">
                            <label for="new-supplier-address" class="form-label">Address</label>
                            <textarea id="new-supplier-address" name="address" class="form-control" rows="2" placeholder="Enter address" autocomplete="new-password" data-lpignore="true" data-1p-ignore="true"></textarea>
                        </div>
                        <div>
                            <label for="new-supplier-city" class="form-label">City</label>
                            <input type="text" id="new-supplier-city" name="city" class="form-control" placeholder="Enter city" autocomplete="new-password" data-lpignore="true" data-1p-ignore="true">
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background: #6b7280; border-color: #6b7280;">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="background: #1769ff; border-color: #1769ff;">Save Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center">
        <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px; background: #f7f8fa; width: min(100%, 900px);">
            <div class="card-body py-3">
                <form action="{{ route('admin.suppliers.index') }}" method="GET" autocomplete="off">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label for="search" class="form-label fs-6 mb-2">Search</label>
                            <input type="search"
                                   class="form-control form-control-lg"
                                   id="search"
                                   name="search"
                                   value="{{ $search ?? '' }}"
                                   placeholder="Search by supplier name, company, phone, email, address or city"
                                   autocomplete="off"
                                   autocorrect="off"
                                   autocapitalize="off"
                                   spellcheck="false"
                                   style="border-radius: 10px; border: 1px solid #d5d9df; background: #fff; width: 100%;">
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-secondary flex-fill" style="background: #6b7280; border-color: #6b7280; border-radius: 9px; font-weight: 600; padding: 0.65rem 0.8rem;">
                                <i class="bi bi-funnel me-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary flex-fill" style="border-radius: 9px; border: 1px solid #c9ced6; color: #374151; background: #fff; font-weight: 600; padding: 0.65rem 0.8rem;">
                                <i class="bi bi-x-circle me-1"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end align-items-center mb-2">
        <form action="{{ route('admin.suppliers.index') }}" method="GET" class="d-flex align-items-center gap-2">
            <input type="hidden" name="search" value="{{ $search ?? '' }}">
            <label for="supplier-per-page" class="small text-muted mb-0">Per Page</label>
            <select id="supplier-per-page" name="per_page" class="form-select form-select-sm" style="width: 82px;" onchange="this.form.submit()">
                @foreach([10, 25, 50, 100] as $option)
                    <option value="{{ $option }}" @selected(request('per_page', 15) == $option)>{{ $option }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
        <div class="card-body p-0">
            @if($suppliers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="border-collapse: collapse;">
                        <thead class="table-light" style="background: #f3f4f6;">
                            <tr>
                                <th class="fw-bold text-dark px-3 py-3" style="font-size: 1.05rem;">Name</th>
                                <th class="fw-bold text-dark px-3 py-3" style="font-size: 1.05rem;">Company Name</th>
                                <th class="fw-bold text-dark px-3 py-3" style="font-size: 1.05rem;">Phone</th>
                                <th class="fw-bold text-dark px-3 py-3" style="font-size: 1.05rem;">Email</th>
                                <th class="fw-bold text-dark px-3 py-3" style="font-size: 1.05rem;">Address</th>
                                <th class="fw-bold text-dark px-3 py-3" style="font-size: 1.05rem;">City</th>
                                <th class="fw-bold text-dark px-3 py-3 text-end" style="font-size: 1.05rem;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($suppliers as $supplier)
                                <tr style="border-top: 1px solid #e5e7eb;">
                                    <td class="fw-semibold px-3 py-3" style="font-size: 1rem; color: #111827;">{{ $supplier->name ?? '-' }}</td>
                                    <td class="px-3 py-3" style="font-size: 0.98rem; color: #374151;">{{ $supplier->company_name ?? '-' }}</td>
                                    <td class="px-3 py-3" style="font-size: 0.98rem; color: #374151;">{{ $supplier->phone ?? '-' }}</td>
                                    <td class="px-3 py-3" style="font-size: 0.98rem; color: #374151;">{{ $supplier->email ?? '-' }}</td>
                                    <td class="px-3 py-3" style="font-size: 0.98rem; color: #374151;">{{ $supplier->address ?? '-' }}</td>
                                    <td class="px-3 py-3" style="font-size: 0.98rem; color: #374151;">{{ $supplier->city ?? '-' }}</td>
                                    <td class="text-end px-3 py-3">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm" title="View" data-bs-toggle="modal" data-bs-target="#supplierDetailsModal{{ $supplier->id }}" style="border-radius: 8px; border-color: #93c5fd; color: #2563eb; width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" title="Edit" data-bs-toggle="modal" data-bs-target="#supplierEditModal{{ $supplier->id }}" style="border-radius: 8px; border-color: #cbd5e1; color: #475569; width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @foreach($suppliers as $supplier)
                    <div class="modal fade" id="supplierDetailsModal{{ $supplier->id }}" tabindex="-1" aria-labelledby="supplierDetailsLabel{{ $supplier->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                                <div class="modal-header">
                                    <div>
                                        <h5 class="modal-title fw-bold" id="supplierDetailsLabel{{ $supplier->id }}">{{ $supplier->name ?: 'Supplier Details' }}</h5>
                                        <small class="text-muted">Complete supplier information</small>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-6"><small class="text-muted d-block">Name</small><strong>{{ $supplier->name ?: '-' }}</strong></div>
                                        <div class="col-md-6"><small class="text-muted d-block">Company Name</small><span>{{ $supplier->company_name ?: '-' }}</span></div>
                                        <div class="col-md-6"><small class="text-muted d-block">Phone</small><span>{{ $supplier->phone ?: '-' }}</span></div>
                                        <div class="col-md-6"><small class="text-muted d-block">Email</small><span>{{ $supplier->email ?: '-' }}</span></div>
                                        <div class="col-md-6"><small class="text-muted d-block">City</small><span>{{ $supplier->city ?: '-' }}</span></div>
                                        <div class="col-md-12"><small class="text-muted d-block">Address</small><span>{{ $supplier->address ?: '-' }}</span></div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#supplierEditModal{{ $supplier->id }}"><i class="bi bi-pencil me-1"></i> Edit Supplier</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="supplierEditModal{{ $supplier->id }}" tabindex="-1" aria-labelledby="supplierEditLabel{{ $supplier->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold" id="supplierEditLabel{{ $supplier->id }}">Edit Supplier</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('admin.suppliers.update', $supplier) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="{{ $supplier->status ?: 'active' }}">
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="supplier-name-{{ $supplier->id }}" class="form-label">Name</label>
                                                <input type="text" id="supplier-name-{{ $supplier->id }}" name="name" class="form-control" value="{{ $supplier->name }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="supplier-company-{{ $supplier->id }}" class="form-label">Company Name</label>
                                                <input type="text" id="supplier-company-{{ $supplier->id }}" name="company_name" class="form-control" value="{{ $supplier->company_name }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="supplier-phone-{{ $supplier->id }}" class="form-label">Phone</label>
                                                <input type="text" id="supplier-phone-{{ $supplier->id }}" name="phone" class="form-control" value="{{ $supplier->phone }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="supplier-email-{{ $supplier->id }}" class="form-label">Email</label>
                                                <input type="email" id="supplier-email-{{ $supplier->id }}" name="email" class="form-control" value="{{ $supplier->email }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="supplier-city-{{ $supplier->id }}" class="form-label">City</label>
                                                <input type="text" id="supplier-city-{{ $supplier->id }}" name="city" class="form-control" value="{{ $supplier->city }}">
                                            </div>
                                            <div class="col-12">
                                                <label for="supplier-address-{{ $supplier->id }}" class="form-label">Address</label>
                                                <textarea id="supplier-address-{{ $supplier->id }}" name="address" class="form-control" rows="2">{{ $supplier->address }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="row align-items-center g-2 p-3">
                    <div class="col-md-6">
                        <small class="text-muted">Showing {{ $suppliers->firstItem() ?? 0 }} to {{ $suppliers->lastItem() ?? 0 }} of {{ $suppliers->total() }} suppliers</small>
                    </div>
                    <div class="col-md-6 d-flex justify-content-md-end">
                        {{ $suppliers->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-person-badge" style="font-size: 3rem; color: #adb5bd;"></i>
                    <p class="mt-3 mb-1 fw-semibold">No suppliers found</p>
                    <p class="text-muted mb-0">Add your first supplier to get started.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
