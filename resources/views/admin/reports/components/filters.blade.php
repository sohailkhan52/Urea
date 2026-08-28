@props([
    'action' => '#',
    'method' => 'GET',
    'warehouses' => [],
    'showWarehouse' => true,
    'customers' => [],
    'showCustomer' => false,
    'suppliers' => [],
    'showSupplier' => false,
    'categories' => [],
    'showCategory' => false,
    'products' => [],
    'showProduct' => false,
    'statuses' => [],
    'showStatus' => false,
    'paymentStatuses' => [],
    'showPaymentStatus' => false,
    'movementTypes' => [],
    'showMovementType' => false,
    'showSearch' => true,
    'filters' => [],
])

<div class="card mb-4">
    <div class="card-header bg-light">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filters</h5>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleFilters()">
                <i class="bi bi-chevron-down" id="filter-toggle-icon"></i>
            </button>
        </div>
    </div>
    <div class="card-body" id="filter-body">
        <form action="{{ $action }}" method="{{ $method }}">
            @if($method !== 'GET')
                @csrf
            @endif

            <div class="row g-3">
                {{-- Date Range --}}
                <div class="col-md-3">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" 
                           class="form-control" 
                           id="date_from" 
                           name="date_from" 
                           value="{{ $filters['date_from'] ?? old('date_from', Carbon\Carbon::today()->format('Y-m-d')) }}">
                </div>

                <div class="col-md-3">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" 
                           class="form-control" 
                           id="date_to" 
                           name="date_to" 
                           value="{{ $filters['date_to'] ?? old('date_to', Carbon\Carbon::today()->format('Y-m-d')) }}">
                </div>

                {{-- Date Presets --}}
                <div class="col-md-6">
                    <label class="form-label">Quick Dates</label>
                    <div class="btn-group w-100" role="group">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDateRange('today')">Today</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDateRange('yesterday')">Yesterday</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDateRange('this_week')">This Week</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDateRange('this_month')">This Month</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDateRange('last_month')">Last Month</button>
                    </div>
                </div>

                {{-- Warehouse --}}
                @if($showWarehouse && count($warehouses) > 0)
                <div class="col-md-3">
                    <label for="warehouse_id" class="form-label">Warehouse</label>
                    <select class="form-select" id="warehouse_id" name="warehouse_id">
                        <option value="">All Warehouses</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ ($filters['warehouse_id'] ?? '') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Customer --}}
                @if($showCustomer && count($customers) > 0)
                <div class="col-md-3">
                    <label for="customer_id" class="form-label">Customer</label>
                    <select class="form-select" id="customer_id" name="customer_id">
                        <option value="">All Customers</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ ($filters['customer_id'] ?? '') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Supplier --}}
                @if($showSupplier && count($suppliers) > 0)
                <div class="col-md-3">
                    <label for="supplier_id" class="form-label">Supplier</label>
                    <select class="form-select" id="supplier_id" name="supplier_id">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ ($filters['supplier_id'] ?? '') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Category --}}
                @if($showCategory && count($categories) > 0)
                <div class="col-md-3">
                    <label for="category_id" class="form-label">Category</label>
                    <select class="form-select" id="category_id" name="category_id">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ ($filters['category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Product --}}
                @if($showProduct && count($products) > 0)
                <div class="col-md-3">
                    <label for="product_id" class="form-label">Product</label>
                    <select class="form-select" id="product_id" name="product_id">
                        <option value="">All Products</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ ($filters['product_id'] ?? '') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Status --}}
                @if($showStatus && count($statuses) > 0)
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['status'] ?? '') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Payment Status --}}
                @if($showPaymentStatus && count($paymentStatuses) > 0)
                <div class="col-md-3">
                    <label for="payment_status" class="form-label">Payment Status</label>
                    <select class="form-select" id="payment_status" name="payment_status">
                        <option value="">All Payment Status</option>
                        @foreach($paymentStatuses as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['payment_status'] ?? '') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Movement Type --}}
                @if($showMovementType && count($movementTypes) > 0)
                <div class="col-md-3">
                    <label for="movement_type" class="form-label">Movement Type</label>
                    <select class="form-select" id="movement_type" name="movement_type">
                        <option value="">All Types</option>
                        @foreach($movementTypes as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['movement_type'] ?? '') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Search --}}
                @if($showSearch)
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" 
                           class="form-control" 
                           id="search" 
                           name="search" 
                           placeholder="Search..." 
                           value="{{ $filters['search'] ?? old('search') }}">
                </div>
                @endif

                {{-- Additional Filters Slot --}}
                {{ $slot }}
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>Apply Filters
                    </button>
                    <a href="{{ $action }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function toggleFilters() {
    const filterBody = document.getElementById('filter-body');
    const icon = document.getElementById('filter-toggle-icon');
    
    filterBody.classList.toggle('d-none');
    icon.classList.toggle('bi-chevron-down');
    icon.classList.toggle('bi-chevron-up');
}

function setDateRange(range) {
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');
    const today = new Date();
    
    switch(range) {
        case 'today':
            dateFrom.value = formatDate(today);
            dateTo.value = formatDate(today);
            break;
        case 'yesterday':
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            dateFrom.value = formatDate(yesterday);
            dateTo.value = formatDate(yesterday);
            break;
        case 'this_week':
            const firstDay = new Date(today);
            firstDay.setDate(today.getDate() - today.getDay());
            dateFrom.value = formatDate(firstDay);
            dateTo.value = formatDate(today);
            break;
        case 'this_month':
            const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
            dateFrom.value = formatDate(firstDayOfMonth);
            dateTo.value = formatDate(today);
            break;
        case 'last_month':
            const firstDayLastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            const lastDayLastMonth = new Date(today.getFullYear(), today.getMonth(), 0);
            dateFrom.value = formatDate(firstDayLastMonth);
            dateTo.value = formatDate(lastDayLastMonth);
            break;
    }
}

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}
</script>
@endpush
