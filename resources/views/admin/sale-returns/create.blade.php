@extends('layouts.admin')

@section('title', 'Create Sale Return')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Create Sale Return</h1>
        <a href="{{ route('admin.sale-returns.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Returns
        </a>
    </div>

    <form id="returnForm" action="{{ route('admin.sale-returns.store') }}" method="POST">
        @csrf

        {{-- Step 1: Search and Select Sale --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Step 1: Select Sale</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <label for="saleSearch" class="form-label">Search Sale</label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="saleSearch" 
                               placeholder="Search by invoice number, customer name, or phone...">
                        <small class="text-muted">Start typing to search for confirmed sales</small>
                    </div>
                </div>

                {{-- Search Results --}}
                <div id="searchResults" class="mt-3" style="display: none;">
                    <div class="list-group" id="salesList"></div>
                </div>

                {{-- Selected Sale Display --}}
                <div id="selectedSale" class="mt-4" style="display: none;">
                    <div class="alert alert-info">
                        <h6 class="alert-heading">Selected Sale</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Invoice:</strong> <span id="sale_invoice"></span></p>
                                <p class="mb-1"><strong>Customer:</strong> <span id="sale_customer"></span></p>
                                <p class="mb-1"><strong>Family:</strong> <span id="sale_family"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Warehouse:</strong> <span id="sale_warehouse"></span></p>
                                <p class="mb-1"><strong>Date:</strong> <span id="sale_date"></span></p>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="clearSale()">
                            <i class="bi bi-x-circle me-1"></i> Change Sale
                        </button>
                    </div>
                </div>

                <input type="hidden" name="sale_id" id="sale_id">
            </div>
        </div>

        {{-- Step 2: Payment Summary --}}
        <div id="paymentSummary" class="card mb-4" style="display: none;">
            <div class="card-header bg-light">
                <h5 class="mb-0">Original Sale Payment Summary</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="border rounded p-3 text-center">
                            <small class="text-muted d-block mb-1">Sale Total</small>
                            <h4 class="mb-0 text-primary">Rs. <span id="payment_total">0.00</span></h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 text-center">
                            <small class="text-muted d-block mb-1">Paid Amount</small>
                            <h4 class="mb-0 text-success">Rs. <span id="payment_paid">0.00</span></h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 text-center">
                            <small class="text-muted d-block mb-1">Outstanding/Udhar</small>
                            <h4 class="mb-0 text-danger">Rs. <span id="payment_outstanding">0.00</span></h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 text-center">
                            <small class="text-muted d-block mb-1">Payment Status</small>
                            <h4 class="mb-0"><span id="payment_status_badge" class="badge"></span></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 3: Select Items to Return --}}
        <div id="itemsSection" class="card mb-4" style="display: none;">
            <div class="card-header">
                <h5 class="mb-0">Step 2: Select Items to Return</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Product</th>
                                <th class="text-center" style="width: 100px;">Sold Qty</th>
                                <th class="text-center" style="width: 100px;">Returned</th>
                                <th class="text-center" style="width: 120px;">Can Return</th>
                                <th class="text-end" style="width: 120px;">Unit Price</th>
                                <th style="width: 150px;">Return Qty</th>
                                <th class="text-end" style="width: 150px;">Return Amount</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <th colspan="7" class="text-end">Total Return Amount:</th>
                                <th class="text-end">
                                    <strong class="text-primary fs-5">Rs. <span id="totalReturnAmount">0.00</span></strong>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Step 4: Return Details --}}
        <div id="detailsSection" class="card mb-4" style="display: none;">
            <div class="card-header">
                <h5 class="mb-0">Step 3: Return Details</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="return_date" class="form-label">Return Date <span class="text-danger">*</span></label>
                        <input type="date" 
                               class="form-control @error('return_date') is-invalid @enderror" 
                               id="return_date" 
                               name="return_date" 
                               value="{{ old('return_date', date('Y-m-d')) }}"
                               required>
                        @error('return_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-8">
                        <label for="reason" class="form-label">Reason for Return</label>
                        <input type="text" 
                               class="form-control @error('reason') is-invalid @enderror" 
                               id="reason" 
                               name="reason" 
                               value="{{ old('reason') }}"
                               placeholder="e.g., Damaged product, Wrong item, etc."
                               maxlength="500">
                        @error('reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-12">
                        <label for="notes" class="form-label">Additional Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" 
                                  name="notes" 
                                  rows="3" 
                                  placeholder="Any additional notes or comments..."
                                  maxlength="1000">{{ old('notes') }}</textarea>
                        @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit Button --}}
        <div id="submitSection" class="card" style="display: none;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">Ready to Create Return</h5>
                        <p class="text-muted mb-0">
                            <span id="itemCount">0</span> item(s) selected for return
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('admin.sale-returns.index') }}" class="btn btn-outline-secondary me-2">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle me-1"></i> Create Return
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
let selectedSale = null;
let saleItems = [];
let searchTimeout = null;

// Sale Search
document.getElementById('saleSearch').addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    const search = e.target.value.trim();
    
    if (search.length < 2) {
        document.getElementById('searchResults').style.display = 'none';
        return;
    }
    
    searchTimeout = setTimeout(() => {
        searchSales(search);
    }, 300);
});

function searchSales(query) {
    fetch(`/admin/sales/search-for-return?search=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            displaySearchResults(data);
        })
        .catch(error => {
            console.error('Error searching sales:', error);
        });
}

function displaySearchResults(sales) {
    const resultsDiv = document.getElementById('searchResults');
    const salesList = document.getElementById('salesList');
    
    if (sales.length === 0) {
        salesList.innerHTML = '<div class="list-group-item">No confirmed sales found</div>';
    } else {
        salesList.innerHTML = sales.map(sale => `
            <a href="javascript:void(0)" 
               class="list-group-item list-group-item-action"
               onclick='selectSale(${JSON.stringify(sale)})'>
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <h6 class="mb-1">${sale.invoice_number}</h6>
                        <p class="mb-1"><strong>${sale.customer_name}</strong> ${sale.customer_phone ? '· ' + sale.customer_phone : ''}</p>
                        ${sale.family_name ? `<small class="text-muted">Family: ${sale.family_name}</small>` : ''}
                    </div>
                    <div class="text-end">
                        <strong class="text-primary">Rs. ${parseFloat(sale.total_amount).toLocaleString('en-PK', {minimumFractionDigits: 2})}</strong>
                        <br>
                        <small class="text-muted">${sale.sale_date}</small>
                        <br>
                        <span class="badge bg-${getPaymentBadge(sale.payment_status)}">${sale.payment_status}</span>
                    </div>
                </div>
            </a>
        `).join('');
    }
    
    resultsDiv.style.display = 'block';
}

function getPaymentBadge(status) {
    const badges = {
        'Paid': 'success',
        'Partially Paid': 'warning',
        'Unpaid': 'danger'
    };
    return badges[status] || 'secondary';
}

function selectSale(sale) {
    console.log('Selecting sale:', sale);
    selectedSale = sale;
    
    // Hide search results
    document.getElementById('searchResults').style.display = 'none';
    document.getElementById('saleSearch').value = '';
    
    // Show selected sale
    document.getElementById('sale_id').value = sale.id;
    
    // Safely set text content with null checks
    const invoice = document.getElementById('sale_invoice');
    const customer = document.getElementById('sale_customer');
    const family = document.getElementById('sale_family');
    const warehouse = document.getElementById('sale_warehouse');
    const date = document.getElementById('sale_date');
    
    console.log('Elements found:', {invoice, customer, family, warehouse, date});
    
    if (invoice) invoice.textContent = sale.invoice_number || '';
    if (customer) customer.textContent = sale.customer_name || '';
    if (family) family.textContent = sale.family_name || '—';
    if (warehouse) warehouse.textContent = sale.warehouse_name || '';
    if (date) date.textContent = sale.sale_date || '';
    
    document.getElementById('selectedSale').style.display = 'block';
    
    // Load sale details
    loadSaleDetails(sale.id);
}

function clearSale() {
    selectedSale = null;
    saleItems = [];
    document.getElementById('selectedSale').style.display = 'none';
    document.getElementById('paymentSummary').style.display = 'none';
    document.getElementById('itemsSection').style.display = 'none';
    document.getElementById('detailsSection').style.display = 'none';
    document.getElementById('submitSection').style.display = 'none';
    document.getElementById('sale_id').value = '';
    document.getElementById('saleSearch').focus();
}

function loadSaleDetails(saleId) {
    fetch(`/admin/sales/${saleId}/return-summary`)
        .then(response => response.json())
        .then(data => {
            // Display payment summary
            document.getElementById('payment_total').textContent = parseFloat(data.sale.total_amount).toLocaleString('en-PK', {minimumFractionDigits: 2});
            document.getElementById('payment_paid').textContent = parseFloat(data.sale.paid_amount).toLocaleString('en-PK', {minimumFractionDigits: 2});
            document.getElementById('payment_outstanding').textContent = parseFloat(data.sale.outstanding).toLocaleString('en-PK', {minimumFractionDigits: 2});
            
            const statusBadge = document.getElementById('payment_status_badge');
            statusBadge.textContent = data.sale.payment_status;
            statusBadge.className = 'badge bg-' + getPaymentBadge(data.sale.payment_status);
            
            document.getElementById('paymentSummary').style.display = 'block';
            
            // Display items
            saleItems = data.items;
            displayItems(data.items);
            
            // Show sections
            document.getElementById('itemsSection').style.display = 'block';
            document.getElementById('detailsSection').style.display = 'block';
            document.getElementById('submitSection').style.display = 'block';
        })
        .catch(error => {
            console.error('Error loading sale details:', error);
            alert('Error loading sale details. Please try again.');
        });
}

function displayItems(items) {
    const tbody = document.getElementById('itemsTableBody');
    
    tbody.innerHTML = items.filter(item => item.can_be_returned).map((item, index) => `
        <tr>
            <td class="text-center">
                <input type="checkbox" 
                       class="form-check-input item-checkbox" 
                       data-index="${index}"
                       onchange="toggleItem(${index})">
            </td>
            <td>
                <strong>${item.product_name}</strong>
                ${item.product_sku !== 'N/A' ? `<br><small class="text-muted">SKU: ${item.product_sku}</small>` : ''}
            </td>
            <td class="text-center"><strong>${parseFloat(item.original_quantity).toFixed(2)}</strong></td>
            <td class="text-center">${parseFloat(item.total_returned).toFixed(2)}</td>
            <td class="text-center text-success"><strong>${parseFloat(item.returnable_quantity).toFixed(2)}</strong></td>
            <td class="text-end">Rs. ${parseFloat(item.unit_price).toFixed(2)}</td>
            <td>
                <input type="number" 
                       class="form-control form-control-sm return-qty-input" 
                       id="qty_${index}"
                       data-index="${index}"
                       min="0.01" 
                       max="${item.returnable_quantity}" 
                       step="0.01"
                       value="${item.returnable_quantity}"
                       disabled
                       oninput="updateReturnAmount(${index})">
            </td>
            <td class="text-end">
                <strong class="text-primary" id="amount_${index}">Rs. 0.00</strong>
            </td>
        </tr>
    `).join('');
    
    if (items.filter(item => item.can_be_returned).length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No items available for return</td></tr>';
    }
}

function toggleItem(index) {
    const checkbox = document.querySelector(`[data-index="${index}"]`);
    const qtyInput = document.getElementById(`qty_${index}`);
    
    qtyInput.disabled = !checkbox.checked;
    
    if (checkbox.checked) {
        updateReturnAmount(index);
    } else {
        document.getElementById(`amount_${index}`).textContent = 'Rs. 0.00';
    }
    
    updateTotal();
}

function updateReturnAmount(index) {
    const item = saleItems.filter(i => i.can_be_returned)[index];
    const qtyInput = document.getElementById(`qty_${index}`);
    const qty = parseFloat(qtyInput.value) || 0;
    
    // Validate quantity
    if (qty > item.returnable_quantity) {
        qtyInput.value = item.returnable_quantity;
        alert(`Cannot return more than ${item.returnable_quantity} units`);
        return;
    }
    
    const amount = qty * item.unit_price;
    document.getElementById(`amount_${index}`).textContent = 'Rs. ' + amount.toFixed(2);
    
    updateTotal();
}

function updateTotal() {
    let total = 0;
    let itemCount = 0;
    
    document.querySelectorAll('.item-checkbox:checked').forEach(checkbox => {
        const index = checkbox.dataset.index;
        const qtyInput = document.getElementById(`qty_${index}`);
        const item = saleItems.filter(i => i.can_be_returned)[index];
        const qty = parseFloat(qtyInput.value) || 0;
        
        if (qty > 0) {
            total += qty * item.unit_price;
            itemCount++;
        }
    });
    
    document.getElementById('totalReturnAmount').textContent = total.toFixed(2);
    document.getElementById('itemCount').textContent = itemCount;
}

// Select All
document.getElementById('selectAll').addEventListener('change', function(e) {
    document.querySelectorAll('.item-checkbox').forEach(checkbox => {
        checkbox.checked = e.target.checked;
        const index = checkbox.dataset.index;
        const qtyInput = document.getElementById(`qty_${index}`);
        qtyInput.disabled = !e.target.checked;
        
        if (e.target.checked) {
            updateReturnAmount(index);
        } else {
            document.getElementById(`amount_${index}`).textContent = 'Rs. 0.00';
        }
    });
    updateTotal();
});

// Form Submission
document.getElementById('returnForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const items = [];
    document.querySelectorAll('.item-checkbox:checked').forEach(checkbox => {
        const index = checkbox.dataset.index;
        const qtyInput = document.getElementById(`qty_${index}`);
        const qty = parseFloat(qtyInput.value);
        const item = saleItems.filter(i => i.can_be_returned)[index];
        
        if (qty > 0) {
            items.push({
                sale_item_id: item.sale_item_id,
                quantity: qty
            });
        }
    });
    
    if (items.length === 0) {
        alert('Please select at least one item to return');
        return;
    }
    
    // Add items to form
    items.forEach((item, idx) => {
        const saleItemInput = document.createElement('input');
        saleItemInput.type = 'hidden';
        saleItemInput.name = `items[${idx}][sale_item_id]`;
        saleItemInput.value = item.sale_item_id;
        this.appendChild(saleItemInput);
        
        const qtyInput = document.createElement('input');
        qtyInput.type = 'hidden';
        qtyInput.name = `items[${idx}][quantity]`;
        qtyInput.value = item.quantity;
        this.appendChild(qtyInput);
    });
    
    this.submit();
});
</script>
@endpush
