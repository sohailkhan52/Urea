@extends('layouts.admin')

@section('title', 'Udhar Management')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h1 class="h3 mb-0">Udhar Management</h1>
        <p class="text-muted mb-0">Track individual customer and family outstanding balances</p>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <p class="text-muted mb-1 small">Total Accounts with Udhar</p>
                    <h3 class="mb-0">{{ $accountsCount }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <p class="text-muted mb-1 small">Total Sales</p>
                    <h3 class="mb-0">Rs. {{ number_format($totalSales, 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <p class="text-muted mb-1 small">Total Paid</p>
                    <h3 class="mb-0 text-success">Rs. {{ number_format($totalPaid, 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body">
                    <p class="text-muted mb-1 small">Outstanding Udhar</p>
                    <h3 class="mb-0 text-danger">Rs. {{ number_format($totalUdhar, 0) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'customers' ? 'active' : '' }}" 
               href="{{ route('admin.udhar.index', array_merge(request()->except('tab'), ['tab' => 'customers'])) }}">
                <i class="bi bi-person me-2"></i>Individual Customers
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'families' ? 'active' : '' }}" 
               href="{{ route('admin.udhar.index', array_merge(request()->except('tab'), ['tab' => 'families'])) }}">
                <i class="bi bi-diagram-3 me-2"></i>Family Accounts
            </a>
        </li>
    </ul>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.udhar.index') }}" method="GET">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small">Search</label>
                        <input type="text" class="form-control form-control-sm" name="search" 
                               value="{{ $filters['search'] ?? '' }}" 
                               placeholder="{{ $activeTab === 'families' ? 'Family name...' : 'Name or phone...' }}">
                    </div>
                    {{-- Show warehouse filter only if multiple warehouses exist --}}
                    @if($showWarehouseFilter)
                    <div class="col-md-2">
                        <label class="form-label small">Warehouse</label>
                        <select class="form-select form-select-sm" name="warehouse_id">
                            <option value="">All</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ ($filters['warehouse_id'] ?? '') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Display</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="only_outstanding" value="1"
                                   {{ ($filters['only_outstanding'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label small">Only Outstanding</label>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="{{ route('admin.udhar.index', ['tab' => $activeTab]) }}" class="btn btn-outline-secondary btn-sm">
                            Clear
                        </a>
                    </div>
                    @else
                    {{-- Single warehouse - simplified filter --}}
                    <div class="col-md-2">
                        <label class="form-label small">Display</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="only_outstanding" value="1"
                                   {{ ($filters['only_outstanding'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label small">Only Outstanding</label>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="{{ route('admin.udhar.index', ['tab' => $activeTab]) }}" class="btn btn-outline-secondary btn-sm">
                            Clear
                        </a>
                    </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if($activeTab === 'families')
        {{-- FAMILIES VIEW --}}
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Family Accounts</h5>
            </div>
            <div class="card-body">
                @if($families->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Family</th>
                                <th class="text-center">Members</th>
                                <th class="text-end">Total Sales</th>
                                <th class="text-end">Total Paid</th>
                                <th class="text-end">Outstanding</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($families as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item['family']->name }}</strong>
                                    <br><small class="text-muted">{{ $item['family']->family_code }}</small>
                                </td>
                                <td class="text-center"><span class="badge bg-info">{{ $item['members_count'] }}</span></td>
                                <td class="text-end">Rs. {{ number_format($item['total_sales'], 0) }}</td>
                                <td class="text-end text-success">Rs. {{ number_format($item['total_paid'], 0) }}</td>
                                <td class="text-end">
                                    <strong class="text-danger">Rs. {{ number_format($item['outstanding'], 0) }}</strong>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.udhar.show-family', $item['family']) }}" class="btn btn-outline-primary" title="View Family">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($item['outstanding'] > 0)
                                        <button type="button" class="btn btn-outline-success" title="Record Payment"
                                                data-bs-toggle="modal" data-bs-target="#familyPaymentModal"
                                                data-family-id="{{ $item['family']->id }}"
                                                data-family-name="{{ $item['family']->name }}"
                                                data-outstanding="{{ $item['outstanding'] }}"
                                                data-payment-url="{{ route('admin.udhar.receive-family-payment', $item['family']) }}">
                                            <i class="bi bi-cash-coin"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $families->appends(request()->query())->links() }}</div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="text-muted">No family accounts found.</p>
                </div>
                @endif
            </div>
        </div>

    @else
        {{-- INDIVIDUAL CUSTOMERS VIEW --}}
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-person me-2"></i>Individual Customer Accounts</h5>
            </div>
            <div class="card-body">
                @if($customers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Customer</th>
                                <th>Phone</th>
                                @if($showWarehouseFilter)
                                <th>Warehouse</th>
                                @endif
                                <th class="text-end">Total Sales</th>
                                <th class="text-end">Total Paid</th>
                                <th class="text-end">Outstanding</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item['customer']->name }}</strong>
                                    <br><small class="text-muted">{{ $item['customer']->type_label }}</small>
                                </td>
                                <td><small>{{ $item['customer']->phone ?? '—' }}</small></td>
                                @if($showWarehouseFilter)
                                <td><small>{{ $item['customer']->warehouse->name ?? 'N/A' }}</small></td>
                                @endif
                                <td class="text-end">Rs. {{ number_format($item['total_sales'], 0) }}</td>
                                <td class="text-end text-success">Rs. {{ number_format($item['total_paid'], 0) }}</td>
                                <td class="text-end">
                                    <strong class="text-danger">Rs. {{ number_format($item['outstanding'], 0) }}</strong>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.udhar.show-customer', $item['customer']) }}" class="btn btn-outline-primary" title="View Customer">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($item['outstanding'] > 0)
                                        <button type="button" class="btn btn-outline-success" title="Record Payment"
                                                data-bs-toggle="modal" data-bs-target="#individualPaymentModal"
                                                data-customer-id="{{ $item['customer']->id }}"
                                                data-customer-name="{{ $item['customer']->name }}"
                                                data-outstanding="{{ $item['outstanding'] }}">
                                            <i class="bi bi-cash-coin"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $customers->appends(request()->query())->links() }}</div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="text-muted">No individual customer accounts found.</p>
                </div>
                @endif
            </div>
        </div>
    @endif
</div>

<div class="modal fade" id="familyPaymentModal" tabindex="-1" aria-labelledby="familyPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="familyPaymentModalLabel">Record Cash Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="familyPaymentForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Family</label>
                        <input type="text" id="familyPaymentName" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Outstanding Amount</label>
                        <input type="text" id="familyPaymentOutstanding" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                        <input type="number" id="familyPaymentAmount" class="form-control" name="amount" step="0.01" min="0.01" required>
                        <small class="text-muted">Max: <span id="familyPaymentMaxAmount"></span></small>
                    </div>
                    <input type="hidden" name="payment_date" value="{{ date('Y-m-d') }}">
                    <input type="hidden" name="allocation_type" value="auto">
                    <div class="mb-3">
                        <label class="form-label">Reference (Optional)</label>
                        <input type="text" class="form-control" name="reference" maxlength="100" placeholder="Transaction ID, etc.">
                    </div>
                    <div>
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Add any notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i> Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="individualPaymentModal" tabindex="-1" aria-labelledby="individualPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="individualPaymentModalLabel">Record Cash Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="individualPaymentForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Customer</label>
                        <input type="text" id="paymentCustomerName" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Outstanding Amount</label>
                        <input type="text" id="paymentOutstanding" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                        <input type="number" id="individualPaymentAmount" class="form-control" name="amount" step="0.01" min="0.01" required>
                        <small class="text-muted">Max: <span id="paymentMaxAmount"></span></small>
                    </div>
                    <input type="hidden" name="payment_date" value="{{ date('Y-m-d') }}">
                    <div class="mb-3">
                        <label class="form-label">Reference (Optional)</label>
                        <input type="text" class="form-control" name="reference" maxlength="100" placeholder="Transaction ID, etc.">
                    </div>
                    <div>
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Add any notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i> Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let individualPaymentCustomerId = null;
let familyPaymentUrl = null;

document.querySelectorAll('[data-bs-target="#familyPaymentModal"]').forEach(function (button) {
    button.addEventListener('click', function () {
        const outstanding = Number(this.dataset.outstanding);
        const formattedAmount = 'Rs. ' + Math.round(outstanding).toLocaleString('en-PK');
        familyPaymentUrl = this.dataset.paymentUrl;
        document.getElementById('familyPaymentName').value = this.dataset.familyName;
        document.getElementById('familyPaymentOutstanding').value = formattedAmount;
        document.getElementById('familyPaymentMaxAmount').textContent = formattedAmount;
        document.getElementById('familyPaymentAmount').max = outstanding;
        document.getElementById('familyPaymentAmount').value = '';
    });
});

document.getElementById('familyPaymentForm').addEventListener('submit', function (event) {
    event.preventDefault();

    const form = this;
    const submitButton = form.querySelector('button[type="submit"]');
    const formData = new FormData(form);
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

    fetch(familyPaymentUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': formData.get('_token'),
            'Accept': 'application/json'
        },
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Unable to record family payment.');
            }
            alert(data.message);
            window.location.reload();
        })
        .catch(error => {
            alert('Error: ' + error.message);
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="bi bi-check-circle me-1"></i> Record Payment';
        });
});

document.querySelectorAll('[data-bs-target="#individualPaymentModal"]').forEach(function (button) {
    button.addEventListener('click', function () {
        setIndividualPayment(
            this.dataset.customerId,
            this.dataset.customerName,
            Number(this.dataset.outstanding)
        );
    });
});

function setIndividualPayment(customerId, customerName, outstanding) {
    individualPaymentCustomerId = customerId;
    const formattedAmount = 'Rs. ' + Math.round(outstanding).toLocaleString('en-PK');
    document.getElementById('paymentCustomerName').value = customerName;
    document.getElementById('paymentOutstanding').value = formattedAmount;
    document.getElementById('paymentMaxAmount').textContent = formattedAmount;
    document.getElementById('individualPaymentAmount').max = outstanding;
    document.getElementById('individualPaymentAmount').value = '';
}

document.getElementById('individualPaymentForm').addEventListener('submit', function (event) {
    event.preventDefault();

    const form = this;
    const submitButton = form.querySelector('button[type="submit"]');
    const formData = new FormData(form);
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

    fetch(`/admin/udhar/customer/${individualPaymentCustomerId}/receive-payment`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': formData.get('_token'),
            'Accept': 'application/json'
        },
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Unable to record payment.');
            }
            alert(data.message);
            window.location.reload();
        })
        .catch(error => {
            alert('Error: ' + error.message);
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="bi bi-check-circle me-1"></i> Record Payment';
        });
});
</script>
@endpush
@endsection
