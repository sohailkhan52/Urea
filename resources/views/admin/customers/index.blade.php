@extends('layouts.admin')

@section('title', 'Customers')

@section('content')
<style>
    #customer-search::placeholder {
        color: #9ca3af;
        opacity: 1;
    }

    body.modal-open .sidebar-edge-toggle {
        display: none !important;
    }

    .customer-create-modal-dialog {
        max-width: 625px;
    }

    .customer-modal .modal-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #dee2e6;
    }

    .customer-modal .modal-title {
        font-size: 1.55rem;
        color: #252a2f;
    }

    .customer-modal .modal-body {
        padding: 1.5rem 1.6rem 1.75rem;
    }

    .customer-modal .form-label {
        font-size: 1.05rem;
        color: #30343a;
        margin-bottom: 0.55rem;
    }

    .customer-modal .form-control,
    .customer-modal .form-select {
        min-height: 48px;
        border-radius: 8px;
        font-size: 1rem;
    }

    .customer-modal textarea.form-control {
        min-height: 76px;
    }

    .customer-modal .modal-footer {
        padding: 1.25rem 1.6rem;
        border-top: 1px solid #dee2e6;
    }

    @media (max-width: 575.98px) {
        .customer-page {
            min-width: 0;
            padding: .75rem !important;
        }

        .customer-page-heading {
            flex-wrap: wrap;
            gap: .35rem;
            margin-bottom: .85rem !important;
        }

        .customer-page-heading > div:first-child {
            flex: 0 0 100%;
            min-width: 0;
        }

        .customer-page-heading h1 {
            font-size: 1.35rem !important;
            white-space: nowrap;
        }

        .customer-page-heading p {
            display: none;
        }

        .customer-page-actions {
            flex: 0 0 100%;
            justify-content: flex-end;
            gap: 0 !important;
        }

        .customer-page-actions .btn {
            padding: .34rem .42rem !important;
            font-size: .68rem;
            line-height: 1.25;
            white-space: nowrap;
        }

        .customer-page-actions .btn i {
            margin-right: .12rem !important;
            font-size: .7rem;
        }

        #deleteSelectedCustomersBtn {
            order: 2;
        }

        .customer-page-actions [data-bs-target="#createCustomerModal"] {
            order: 1;
        }

        .customer-search-card {
            width: min(100%, 360px) !important;
            margin-bottom: .75rem !important;
        }

        .customer-search-card .card-body {
            padding: .6rem !important;
        }

        .customer-search-card .form-label {
            margin-bottom: .3rem !important;
            font-size: .82rem !important;
        }

        .customer-search-card .form-control,
        .customer-search-card .form-control-lg {
            min-height: 40px;
            padding: .38rem .6rem;
            font-size: .86rem;
        }

        .customer-search-card .row {
            --bs-gutter-x: .55rem;
            --bs-gutter-y: .55rem;
        }

        .customer-search-actions {
            gap: .4rem !important;
        }

        .customer-search-actions .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .25rem;
            min-height: 38px;
            padding: .35rem .45rem !important;
            font-size: .75rem;
            line-height: 1.2;
        }

        .customer-search-actions .btn i {
            margin: 0 !important;
            font-size: .78rem;
        }

        .customer-per-page {
            margin-bottom: .4rem !important;
        }

        .customer-table-card {
            min-width: 0;
        }

        .customer-table {
            min-width: 680px;
        }

        .customer-table th,
        .customer-table td {
            padding: .55rem .65rem !important;
            font-size: .78rem !important;
            white-space: nowrap;
        }

        .customer-table th {
            font-size: .8rem !important;
        }

        .customer-table td:last-child {
            position: static;
        }

        .customer-table th:last-child {
            position: static;
        }

        .customer-table .btn {
            width: 32px !important;
            height: 32px !important;
        }

        .customer-table-pagination {
            overflow-x: auto;
            padding: .75rem !important;
        }

        .customer-table-pagination nav {
            min-width: max-content;
        }

        .customer-create-modal-dialog,
        .customer-details-modal,
        .customer-edit-modal {
            width: calc(100% - 2rem);
            max-width: none;
            margin: 1rem auto;
        }

        .customer-modal .modal-content {
            max-height: calc(100dvh - 2rem);
            overflow: hidden;
        }

        .customer-modal form {
            display: flex;
            flex: 1 1 auto;
            flex-direction: column;
            min-height: 0;
        }

        .customer-modal .modal-header {
            flex-shrink: 0;
            padding: .55rem .75rem;
        }

        .customer-modal .modal-title {
            font-size: .95rem;
        }

        .customer-modal .modal-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            padding: .6rem .75rem;
        }

        .customer-modal .modal-body .mb-3 {
            margin-bottom: .45rem !important;
        }

        .customer-modal .modal-body .row {
            --bs-gutter-y: .45rem;
        }

        .customer-modal .form-label {
            margin-bottom: .2rem;
            font-size: .78rem;
        }

        .customer-modal .form-control,
        .customer-modal .form-select {
            min-height: 34px;
            padding: .28rem .5rem;
            font-size: .8rem;
        }

        .customer-modal textarea.form-control {
            min-height: 48px;
        }

        .customer-modal .modal-footer {
            flex-shrink: 0;
            flex-wrap: wrap;
            gap: .3rem;
            padding: .5rem .75rem;
        }

        .customer-modal .modal-footer .btn {
            padding: .3rem .5rem;
            font-size: .74rem;
        }

        .customer-details-modal .modal-body .row {
            --bs-gutter-x: .65rem;
            --bs-gutter-y: .7rem;
        }

        .customer-details-modal .modal-body small,
        .customer-details-modal .modal-body span,
        .customer-details-modal .modal-body strong {
            font-size: .86rem;
            overflow-wrap: anywhere;
        }
    }

    @media (min-width: 576px) and (max-width: 991.98px) {
        .customer-page-heading {
            flex-wrap: nowrap;
            align-items: center !important;
            gap: .65rem;
        }

        .customer-page-heading > div:first-child {
            flex: 1 1 auto;
            min-width: 0;
        }

        .customer-page-heading h1 {
            font-size: 1.45rem !important;
        }

        .customer-page-heading p {
            font-size: .78rem;
        }

        .customer-page-actions {
            flex: 0 0 auto;
            flex-wrap: nowrap;
            justify-content: flex-end;
            gap: .3rem !important;
        }

        .customer-page-actions .btn {
            flex: 0 0 auto;
            padding: .34rem .42rem !important;
            font-size: .68rem;
            white-space: nowrap;
        }

        .customer-page-actions .btn i {
            margin-right: .12rem !important;
        }

        .customer-search-card {
            width: min(100%, 760px) !important;
        }

        .customer-search-card .row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: .75rem;
            margin: 0;
            --bs-gutter-x: 0;
            --bs-gutter-y: 0;
        }

        .customer-search-card .row > [class*="col-"] {
            width: auto;
            max-width: none;
            padding: 0;
        }

        .customer-search-actions {
            align-self: end;
        }

        .customer-table {
            min-width: 760px;
        }

        .customer-table th,
        .customer-table td {
            padding: .65rem .75rem !important;
            font-size: .88rem !important;
        }
    }
</style>

<div class="container-fluid py-4 customer-page">
    <div class="d-flex justify-content-between align-items-center mb-4 customer-page-heading">
        <div>
            <h1 class="h3 mb-0 fw-bold" style="font-size: 2.2rem; color: #1f2937;">Customers</h1>
            <p class="text-muted mb-0 mt-1">View and manage all customers in your inventory</p>
        </div>
        <div class="d-flex gap-2 customer-page-actions">
            @can('customers.delete')
                <button type="button" class="btn btn-danger btn-sm px-3" id="deleteSelectedCustomersBtn" disabled onclick="deleteSelectedCustomers()" style="border-radius: 8px; font-weight: 600;">
                    <i class="bi bi-trash me-1"></i> Delete Selected
                </button>
            @endcan
            <button type="button" class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#createCustomerModal" style="background: #1d74d9; border-color: #1d74d9; border-radius: 8px; font-weight: 600;">
                <i class="bi bi-plus-lg me-1"></i> Add Customer
            </button>
        </div>
    </div>

    <div class="modal fade customer-modal" id="createCustomerModal" tabindex="-1" aria-labelledby="createCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered customer-create-modal-dialog">
            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="createCustomerModalLabel">Create New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.customers.store') }}" method="POST" autocomplete="new-password">
                    @csrf
                    <input type="hidden" name="status" value="active">
                    <input type="hidden" name="customer_type" value="retail_customer">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="new-customer-name" class="form-label">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" id="new-customer-name" name="name" class="form-control" placeholder="Enter customer name" autocomplete="new-password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new-customer-family" class="form-label">Family</label>
                            <select id="new-customer-family" name="family_id" class="form-select">
                                <option value="">-- Select Family (Optional) --</option>
                                @foreach($families as $family)
                                    <option value="{{ $family->id }}">{{ $family->name }} - {{ $family->family_code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="new-customer-phone" class="form-label">Phone</label>
                            <input type="text" id="new-customer-phone" name="phone" class="form-control" placeholder="03001234567" autocomplete="new-password">
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background: #6b7280; border-color: #6b7280;">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="background: #1769ff; border-color: #1769ff;">Save Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center">
        <div class="card mb-4 border-0 shadow-sm customer-search-card" style="border-radius: 12px; background: #f7f8fa; width: min(100%, 900px);">
            <div class="card-body py-3">
                <form action="{{ route('admin.customers.index') }}" method="GET" autocomplete="off">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label for="customer-search" class="form-label fs-6 mb-2">Search</label>
                            <input type="search" class="form-control form-control-lg" id="customer-search" name="search" value="{{ $search ?? '' }}" placeholder="Search by customer name, phone, email, address or city" autocomplete="off" style="border-radius: 10px; border: 1px solid #d5d9df; background: #fff; width: 100%;">
                            <input type="hidden" name="per_page" value="{{ $perPage }}">
                        </div>
                        <div class="col-md-4 d-flex gap-2 customer-search-actions">
                            <button type="submit" class="btn btn-secondary flex-fill" style="background: #6b7280; border-color: #6b7280; border-radius: 9px; font-weight: 600; padding: 0.65rem 0.8rem;"><i class="bi bi-funnel me-1"></i> Filter</button>
                            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary flex-fill" style="border-radius: 9px; border: 1px solid #c9ced6; color: #374151; background: #fff; font-weight: 600; padding: 0.65rem 0.8rem;"><i class="bi bi-x-circle me-1"></i> Clear</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end align-items-center mb-2 customer-per-page">
        <form action="{{ route('admin.customers.index') }}" method="GET" class="d-flex align-items-center gap-2">
            <input type="hidden" name="search" value="{{ $search ?? '' }}">
            <label for="customer-per-page" class="small text-muted mb-0">Per Page</label>
            <select id="customer-per-page" name="per_page" class="form-select form-select-sm" style="width: 82px;" onchange="this.form.submit()">
                @foreach([10, 25, 50, 100] as $option)
                    <option value="{{ $option }}" @selected($perPage == $option)>{{ $option }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="card shadow-sm border-0 customer-table-card" style="border-radius: 12px; overflow: hidden;">
        <div class="card-body p-0">
            @if($customers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 customer-table" style="border-collapse: collapse;">
                        <thead class="table-light" style="background: #f3f4f6;">
                            <tr>
                                @can('customers.delete')
                                    <th class="px-3 py-3" style="width: 52px;"><input type="checkbox" id="selectAllCustomers" class="form-check-input" onchange="toggleCustomerSelection()" aria-label="Select all customers"></th>
                                @endcan
                                <th class="fw-bold text-dark px-3 py-3" style="font-size: 1.05rem;">Name</th>
                                <th class="fw-bold text-dark px-3 py-3" style="font-size: 1.05rem;">Customer Type</th>
                                <th class="fw-bold text-dark px-3 py-3" style="font-size: 1.05rem;">Family</th>
                                <th class="fw-bold text-dark px-3 py-3" style="font-size: 1.05rem;">Phone</th>
                                <th class="fw-bold text-dark px-3 py-3 text-end" style="font-size: 1.05rem;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $customer)
                                <tr style="border-top: 1px solid #e5e7eb;">
                                    @can('customers.delete')
                                        <td class="px-3 py-3"><input type="checkbox" class="form-check-input customer-checkbox" value="{{ $customer->id }}" onchange="updateCustomerDeleteButton()" aria-label="Select {{ $customer->name }}"></td>
                                    @endcan
                                    <td class="fw-semibold px-3 py-3" style="font-size: 1rem; color: #111827;">{{ $customer->name ?: '-' }}</td>
                                    <td class="px-3 py-3" style="font-size: 0.98rem; color: #374151;">{{ $customer->type_label }}</td>
                                    <td class="px-3 py-3" style="font-size: 0.98rem; color: #374151;">{{ $customer->family?->name ?: '-' }}</td>
                                    <td class="px-3 py-3" style="font-size: 0.98rem; color: #374151;">{{ $customer->phone ?: '-' }}</td>
                                    <td class="text-end px-3 py-3">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm" title="View" data-bs-toggle="modal" data-bs-target="#customerDetailsModal{{ $customer->id }}" style="border-radius: 8px; border-color: #93c5fd; color: #2563eb; width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;"><i class="bi bi-eye"></i></button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" title="Edit" data-bs-toggle="modal" data-bs-target="#customerEditModal{{ $customer->id }}" style="border-radius: 8px; border-color: #cbd5e1; color: #475569; width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;"><i class="bi bi-pencil"></i></button>
                                            <a href="{{ route('admin.customers.history', $customer) }}" class="btn btn-outline-info btn-sm" title="Payment History" style="border-radius: 8px; border-color: #67e8f9; color: #0891b2; width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;"><i class="bi bi-clock-history"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @foreach($customers as $customer)
                    <div class="modal fade customer-modal" id="customerDetailsModal{{ $customer->id }}" tabindex="-1" aria-labelledby="customerDetailsLabel{{ $customer->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg customer-details-modal">
                            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                                <div class="modal-header">
                                    <div><h5 class="modal-title fw-bold" id="customerDetailsLabel{{ $customer->id }}">{{ $customer->name ?: 'Customer Details' }}</h5><small class="text-muted">Complete customer information</small></div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-6"><small class="text-muted d-block">Name</small><strong>{{ $customer->name ?: '-' }}</strong></div>
                                        <div class="col-md-6"><small class="text-muted d-block">Customer Type</small><span>{{ $customer->type_label }}</span></div>
                                        <div class="col-md-6"><small class="text-muted d-block">Family</small><span>{{ $customer->family?->name ?: '-' }}</span></div>
                                        <div class="col-md-6"><small class="text-muted d-block">Phone</small><span>{{ $customer->phone ?: '-' }}</span></div>
                                    </div>
                                </div>
                                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button><button type="button" class="btn btn-primary" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#customerEditModal{{ $customer->id }}"><i class="bi bi-pencil me-1"></i> Edit Customer</button></div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade customer-modal" id="customerEditModal{{ $customer->id }}" tabindex="-1" aria-labelledby="customerEditLabel{{ $customer->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg customer-edit-modal">
                            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                                <div class="modal-header"><h5 class="modal-title fw-bold" id="customerEditLabel{{ $customer->id }}">Edit Customer</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                                <form action="{{ route('admin.customers.update', $customer) }}" method="POST" autocomplete="new-password">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="{{ $customer->status ?: 'active' }}">
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label for="customer-name-{{ $customer->id }}" class="form-label">Name</label><input type="text" id="customer-name-{{ $customer->id }}" name="name" class="form-control" value="{{ $customer->name }}" required></div>
                                            <div class="col-md-6"><label for="customer-type-{{ $customer->id }}" class="form-label">Customer Type</label><select id="customer-type-{{ $customer->id }}" name="customer_type" class="form-select" required>@foreach(\App\Models\Customer::$types as $value => $label)<option value="{{ $value }}" @selected($customer->customer_type === $value)>{{ $label }}</option>@endforeach</select></div>
                                            <div class="col-md-6"><label for="customer-family-{{ $customer->id }}" class="form-label">Family</label><select id="customer-family-{{ $customer->id }}" name="family_id" class="form-select"><option value="">-- No Family --</option>@foreach($families as $family)<option value="{{ $family->id }}" @selected($customer->family_id === $family->id)>{{ $family->name }} - {{ $family->family_code }}</option>@endforeach</select></div>
                                            <div class="col-md-6"><label for="customer-phone-{{ $customer->id }}" class="form-label">Phone</label><input type="text" id="customer-phone-{{ $customer->id }}" name="phone" class="form-control" value="{{ $customer->phone }}"></div>
                                        </div>
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save Changes</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="row align-items-center g-2 p-3 customer-table-pagination">
                    <div class="col-12 d-flex justify-content-end">
                        {{ $customers->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5"><i class="bi bi-person-badge" style="font-size: 3rem; color: #adb5bd;"></i><p class="mt-3 mb-1 fw-semibold">No customers found</p><p class="text-muted mb-0">Add your first customer to get started.</p></div>
            @endif
        </div>
    </div>
</div>
@endsection

@can('customers.delete')
    <form id="bulkCustomerDeleteForm" action="{{ route('admin.customers.bulk-delete') }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>
@endcan

@push('scripts')
<script>
    function toggleCustomerSelection() {
        const selectAll = document.getElementById('selectAllCustomers');
        document.querySelectorAll('.customer-checkbox').forEach((checkbox) => {
            checkbox.checked = selectAll.checked;
        });
        updateCustomerDeleteButton();
    }

    function updateCustomerDeleteButton() {
        const selectedCount = document.querySelectorAll('.customer-checkbox:checked').length;
        const deleteButton = document.getElementById('deleteSelectedCustomersBtn');
        if (deleteButton) {
            deleteButton.disabled = selectedCount === 0;
        }
    }

    function deleteSelectedCustomers() {
        const selected = Array.from(document.querySelectorAll('.customer-checkbox:checked'));
        if (selected.length === 0 || !confirm(`Delete ${selected.length} selected customer(s)?`)) {
            return;
        }

        const form = document.getElementById('bulkCustomerDeleteForm');
        selected.forEach((checkbox) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'customer_ids[]';
            input.value = checkbox.value;
            form.appendChild(input);
        });
        form.submit();
    }
</script>
@endpush
