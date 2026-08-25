@extends('layouts.admin')

@section('title', 'Create Purchase Return')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Create Purchase Return</h1>
        <a href="{{ route('admin.purchases.returns.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Errors:</strong>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form action="{{ route('admin.purchases.returns.store') }}" method="POST">
        @csrf

        {{-- Step 1: Select Purchase --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Step 1: Select Purchase</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <label for="purchase_id" class="form-label">Purchase Order <span class="text-danger">*</span></label>
                        <select class="form-select @error('purchase_id') is-invalid @enderror" 
                                id="purchase_id" name="purchase_id" required>
                            <option value="">-- Select a Purchase --</option>
                            @foreach($purchases as $purchase)
                            <option value="{{ $purchase->id }}" {{ old('purchase_id') == $purchase->id ? 'selected' : '' }}>
                                {{ $purchase->purchase_number }} - {{ $purchase->supplier->name }} 
                                (Rs. {{ number_format($purchase->total_amount, 2) }})
                            </option>
                            @endforeach
                        </select>
                        @error('purchase_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="return_date" class="form-label">Return Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('return_date') is-invalid @enderror" 
                               id="return_date" name="return_date" 
                               value="{{ old('return_date', date('Y-m-d')) }}" required>
                        @error('return_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 2: Select Return Type --}}
        <div class="card mb-4" id="returnTypeCard" style="display: none;">
            <div class="card-header">
                <h5 class="mb-0">Step 2: Select Return Type</h5>
            </div>
            <div class="card-body">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="radio" name="return_type" id="wholeOrder" 
                           value="WHOLE_ORDER" checked>
                    <label class="form-check-label" for="wholeOrder">
                        <strong>Return Whole Purchase Order</strong>
                        <p class="text-muted small mb-0">Return all remaining items from this purchase</p>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="return_type" id="partial" 
                           value="PARTIAL_ITEMS">
                    <label class="form-check-label" for="partial">
                        <strong>Select Products / Quantities</strong>
                        <p class="text-muted small mb-0">Choose specific products and quantities to return</p>
                    </label>
                </div>
                @error('return_type')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Step 3: Select Items (for Partial Return) --}}
        <div class="card mb-4" id="itemsCard" style="display: none;">
            <div class="card-header">
                <h5 class="mb-0">Step 3: Select Items to Return</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Product</th>
                                <th class="text-end" style="width: 12%;">Unit Price</th>
                                <th class="text-end" style="width: 10%;">Purchased</th>
                                <th class="text-end" style="width: 10%;">Already Returned</th>
                                <th class="text-end" style="width: 10%;">Remaining</th>
                                <th class="text-end" style="width: 15%;">Return Quantity</th>
                                <th class="text-end" style="width: 12%;">Return Amount</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <!-- Items will be loaded here -->
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary">
                                <th colspan="7" class="text-end">Total:</th>
                                <th class="text-end">Rs. <span id="totalAmount">0.00</span></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @error('items')
                <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Additional Info --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Additional Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label for="reason" class="form-label">Reason for Return</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" 
                                  placeholder="Enter reason">{{ old('reason') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Additional notes">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.purchases.returns.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                        <i class="bi bi-check-circle me-1"></i> Create Return
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const purchaseSelect = document.getElementById('purchase_id');
    const returnTypeCard = document.getElementById('returnTypeCard');
    const itemsCard = document.getElementById('itemsCard');
    const wholeOrderRadio = document.getElementById('wholeOrder');
    const partialRadio = document.getElementById('partial');
    const itemsBody = document.getElementById('itemsBody');
    const submitBtn = document.getElementById('submitBtn');
    const selectAll = document.getElementById('selectAll');
    const totalAmountSpan = document.getElementById('totalAmount');

    purchaseSelect.addEventListener('change', function() {
        if (this.value) {
            returnTypeCard.style.display = 'block';
            submitBtn.disabled = false;
            loadPurchaseItems(this.value);
        } else {
            returnTypeCard.style.display = 'none';
            itemsCard.style.display = 'none';
            submitBtn.disabled = true;
            itemsBody.innerHTML = '';
        }
    });

    document.querySelectorAll('input[name="return_type"]').forEach(radio => {
        radio.addEventListener('change', handleReturnTypeChange);
    });

    selectAll.addEventListener('change', function() {
        document.querySelectorAll('.item-checkbox').forEach(checkbox => {
            checkbox.checked = this.checked;
            checkbox.dispatchEvent(new Event('change'));
        });
    });

    function handleReturnTypeChange() {
        if (partialRadio.checked) {
            itemsCard.style.display = 'block';
        } else {
            itemsCard.style.display = 'none';
        }
    }

    function loadPurchaseItems(purchaseId) {
        // Fetch items from API endpoint
        fetch(`/admin/purchases/${purchaseId}/details`)
            .then(response => {
                if (!response.ok) throw new Error('Failed to load items');
                return response.json();
            })
            .then(data => {
                if (data.items) {
                    renderItems(data.items);
                } else {
                    itemsBody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No returnable items found</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading items:', error);
                itemsBody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error loading items. Please refresh and try again.</td></tr>';
            });
    }

    function renderItems(items) {
        itemsBody.innerHTML = '';

        if (!items || items.length === 0) {
            itemsBody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No returnable items found</td></tr>';
            return;
        }

        items.forEach((item, index) => {
            const unitPrice = parseFloat(item.unit_price || 0);
            const remaining = parseFloat(item.remaining_quantity || 0);
            const alreadyReturned = parseFloat(item.already_returned || 0);
            const purchased = parseFloat(item.quantity || 0);
            const productName = item.product_name || 'Unknown';
            const purchaseItemId = item.id || '';
            const isFullyReturned = remaining <= 0;

            const row = document.createElement('tr');
            
            // If fully returned, disable the checkbox and show as disabled
            const checkboxDisabled = isFullyReturned ? 'disabled' : '';
            const rowClass = isFullyReturned ? 'table-secondary' : '';

            row.innerHTML = `
                <td>
                    <input type="checkbox" class="form-check-input item-checkbox" 
                           data-index="${index}" data-unit-price="${unitPrice}" 
                           data-remaining="${remaining}" data-purchase-item-id="${purchaseItemId}"
                           ${checkboxDisabled}
                           title="${isFullyReturned ? 'This item has been fully returned' : 'Select to return'}">
                </td>
                <td>
                    <strong>${productName}</strong>
                    ${isFullyReturned ? '<br><span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Fully Returned</span>' : ''}
                </td>
                <td class="text-end">Rs. ${unitPrice.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td class="text-end">${purchased.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td class="text-end">${alreadyReturned.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td class="text-end">
                    ${remaining.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                </td>
                <td class="text-end">
                    <input type="number" class="form-control form-control-sm return-quantity" 
                           data-index="${index}" step="0.01" min="0" max="${remaining}"
                           placeholder="0" value="0" style="width: 100%; background-color: #fff;" disabled
                           ${checkboxDisabled}>
                    <small class="text-muted d-block">Max: ${remaining.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</small>
                </td>
                <td class="text-end">
                    <span class="return-amount-display">Rs. 0.00</span>
                    <input type="hidden" class="return-amount" name="items[${index}][amount]" value="0">
                    <input type="hidden" class="item-purchase-item-id" name="items[${index}][purchase_item_id]" value="${purchaseItemId}">
                    <input type="hidden" class="item-quantity" name="items[${index}][quantity]" value="0">
                </td>
            `;
            
            if (rowClass) row.classList.add(rowClass);
            itemsBody.appendChild(row);
        });

        // Add event listeners to quantity inputs
        document.querySelectorAll('.return-quantity').forEach(input => {
            input.addEventListener('change', updateRowAmount);
            input.addEventListener('keyup', updateRowAmount);
        });

        // Add event listeners to checkboxes
        document.querySelectorAll('.item-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // Don't allow checking disabled items
                if (this.disabled) {
                    this.checked = false;
                    return;
                }

                const row = this.closest('tr');
                const quantityInput = row.querySelector('.return-quantity');
                
                if (this.checked) {
                    quantityInput.disabled = false;
                    quantityInput.value = '0';
                    quantityInput.focus();
                } else {
                    quantityInput.value = '0';
                    quantityInput.disabled = true;
                    quantityInput.dispatchEvent(new Event('change'));
                }
                
                calculateTotal();
            });
        });
    }

    function updateRowAmount(e) {
        const row = e.target.closest('tr');
        const quantityInput = row.querySelector('.return-quantity');
        const amountSpan = row.querySelector('.return-amount-display');
        const amountHidden = row.querySelector('.return-amount');
        const quantityHidden = row.querySelector('.item-quantity');
        const unitPrice = parseFloat(e.target.dataset?.unitPrice || row.querySelector('.item-checkbox').dataset.unitPrice);
        const quantity = parseFloat(quantityInput.value) || 0;
        const remaining = parseFloat(e.target.dataset?.remaining || row.querySelector('.item-checkbox').dataset.remaining);

        // Validate quantity doesn't exceed remaining
        if (quantity > remaining) {
            quantityInput.value = remaining;
            quantityInput.classList.add('is-invalid');
            alert(`Cannot return more than ${remaining.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2})} units`);
        } else {
            quantityInput.classList.remove('is-invalid');
        }

        const amount = quantity * unitPrice;
        amountSpan.textContent = `Rs. ${amount.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        amountHidden.value = amount.toFixed(2);
        quantityHidden.value = quantityInput.value;

        calculateTotal();
    }

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.return-amount').forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        totalAmountSpan.textContent = total.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    // Form submission validation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // If partial items mode is selected, validate at least one item has quantity > 0
            if (partialRadio.checked) {
                let hasItems = false;
                document.querySelectorAll('.item-quantity').forEach(input => {
                    if (parseFloat(input.value) > 0) {
                        hasItems = true;
                    }
                });

                if (!hasItems) {
                    e.preventDefault();
                    alert('Please select at least one product and enter a return quantity.');
                    return false;
                }
            }
        });
    }

    // Load items if purchase_id is pre-selected
    if (purchaseSelect.value) {
        purchaseSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection
