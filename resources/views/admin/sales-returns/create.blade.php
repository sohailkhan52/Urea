@extends('layouts.admin')

@section('title', 'Create Sales Return')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Create Sales Return</h1>
        <a href="{{ route('admin.sales.returns.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Returns
        </a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.sales.returns.store') }}" method="POST" id="returnForm">
        @csrf

        {{-- Sale Selection --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Select Sale</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label for="sale_id" class="form-label">Sale Invoice <span class="text-danger">*</span></label>
                        <select class="form-select @error('sale_id') is-invalid @enderror" 
                                id="sale_id" name="sale_id" required>
                            <option value="">-- Select Sale --</option>
                            @foreach($sales as $sale)
                            <option value="{{ $sale->id }}"
                                    data-customer="{{ $sale->customer->name }}"
                                    data-warehouse="{{ $sale->warehouse->name }}"
                                    data-total="{{ number_format($sale->total_amount, 2) }}"
                                    data-status="{{ ucfirst($sale->status) }}"
                                    {{ old('sale_id') == $sale->id ? 'selected' : '' }}>
                                {{ $sale->invoice_number }} - {{ $sale->customer->name }} 
                                ({{ $sale->sale_date->format('d M Y') }}) - Rs. {{ number_format($sale->total_amount, 2) }}
                            </option>
                            @endforeach
                        </select>
                        @error('sale_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="return_date" class="form-label">Return Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('return_date') is-invalid @enderror" 
                               id="return_date" name="return_date" 
                               value="{{ old('return_date', date('Y-m-d')) }}" 
                               max="{{ date('Y-m-d') }}" required>
                        @error('return_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row mt-3" id="saleDetails" style="display: none;">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <strong>Sale Information:</strong>
                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <small><strong>Customer:</strong> <span id="saleCustomer"></span></small>
                                </div>
                                <div class="col-md-3">
                                    <small><strong>Warehouse:</strong> <span id="saleWarehouse"></span></small>
                                </div>
                                <div class="col-md-3">
                                    <small><strong>Total:</strong> Rs. <span id="saleTotal"></span></small>
                                </div>
                                <div class="col-md-3">
                                    <small><strong>Status:</strong> <span id="saleStatus"></span></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Return Items --}}
        <div class="card mb-4" id="itemsCard" style="display: none;">
            <div class="card-header">
                <h5 class="mb-0">Return Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th style="width: 30%;">Product</th>
                                <th style="width: 10%;" class="text-end">Unit Price</th>
                                <th style="width: 10%;" class="text-end">Original Qty</th>
                                <th style="width: 10%;" class="text-end">Returned</th>
                                <th style="width: 10%;" class="text-end">Remaining</th>
                                <th style="width: 10%;" class="text-end">Return Qty</th>
                                <th style="width: 10%;" class="text-end">Discount</th>
                                <th style="width: 15%;" class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <!-- Items will be loaded via JavaScript -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="8" class="text-end">Total Return Amount:</th>
                                <th class="text-end">
                                    Rs. <span id="totalAmount">0.00</span>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Additional Details --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Additional Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label for="reason" class="form-label">Reason for Return</label>
                        <textarea class="form-control @error('reason') is-invalid @enderror" 
                                  id="reason" name="reason" rows="3" 
                                  placeholder="Enter reason for return">{{ old('reason') }}</textarea>
                        @error('reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3" 
                                  placeholder="Additional notes">{{ old('notes') }}</textarea>
                        @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.sales.returns.index') }}" class="btn btn-secondary">Cancel</a>
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
    const saleSelect = document.getElementById('sale_id');
    const itemsCard = document.getElementById('itemsCard');
    const saleDetails = document.getElementById('saleDetails');
    const itemsBody = document.getElementById('itemsBody');
    const submitBtn = document.getElementById('submitBtn');
    const selectAll = document.getElementById('selectAll');
    
    let saleItems = [];

    saleSelect.addEventListener('change', function() {
        if (this.value) {
            const option = this.options[this.selectedIndex];
            document.getElementById('saleCustomer').textContent = option.dataset.customer;
            document.getElementById('saleWarehouse').textContent = option.dataset.warehouse;
            document.getElementById('saleTotal').textContent = option.dataset.total;
            document.getElementById('saleStatus').textContent = option.dataset.status;
            saleDetails.style.display = 'block';
            
            loadSaleItems(this.value);
        } else {
            itemsCard.style.display = 'none';
            saleDetails.style.display = 'none';
            submitBtn.disabled = true;
        }
    });

    function loadSaleItems(saleId) {
        fetch(`/admin/sales/${saleId}`)
            .then(response => response.json())
            .then(data => {
                saleItems = data.items;
                renderItems();
                itemsCard.style.display = 'block';
            })
            .catch(error => {
                console.error('Error loading sale items:', error);
                alert('Failed to load sale items. Please try again.');
            });
    }

    function renderItems() {
        itemsBody.innerHTML = '';
        saleItems.forEach((item, index) => {
            const remainingQty = parseFloat(item.remaining_quantity || item.quantity);
            
            const row = `
                <tr>
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input item-checkbox" data-index="${index}">
                    </td>
                    <td>${item.product.name}</td>
                    <td class="text-end">Rs. ${parseFloat(item.unit_price).toFixed(2)}</td>
                    <td class="text-end">${parseFloat(item.quantity).toFixed(2)}</td>
                    <td class="text-end">${parseFloat(item.returned_quantity || 0).toFixed(2)}</td>
                    <td class="text-end">${remainingQty.toFixed(2)}</td>
                    <td>
                        <input type="hidden" name="items[${index}][sale_item_id]" value="${item.id}">
                        <input type="number" class="form-control form-control-sm text-end return-qty" 
                               name="items[${index}][quantity]" 
                               data-index="${index}"
                               min="0.01" 
                               max="${remainingQty}" 
                               step="0.01" 
                               value="0" 
                               disabled>
                    </td>
                    <td class="text-end">Rs. ${parseFloat(item.discount || 0).toFixed(2)}</td>
                    <td class="text-end item-amount" data-index="${index}">Rs. 0.00</td>
                </tr>
            `;
            itemsBody.insertAdjacentHTML('beforeend', row);
        });
        
        attachItemEventListeners();
    }

    function attachItemEventListeners() {
        document.querySelectorAll('.item-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const index = this.dataset.index;
                const qtyInput = document.querySelector(`input.return-qty[data-index="${index}"]`);
                qtyInput.disabled = !this.checked;
                if (!this.checked) {
                    qtyInput.value = 0;
                    calculateItemAmount(index);
                }
                calculateTotal();
                checkSubmitButton();
            });
        });

        document.querySelectorAll('.return-qty').forEach(input => {
            input.addEventListener('input', function() {
                const index = this.dataset.index;
                calculateItemAmount(index);
                calculateTotal();
                checkSubmitButton();
            });
        });

        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                checkbox.checked = this.checked;
                checkbox.dispatchEvent(new Event('change'));
            });
        });
    }

    function calculateItemAmount(index) {
        const item = saleItems[index];
        const qtyInput = document.querySelector(`input.return-qty[data-index="${index}"]`);
        const qty = parseFloat(qtyInput.value) || 0;
        const discountPerUnit = (item.discount || 0) / item.quantity;
        const amount = (item.unit_price * qty) - (discountPerUnit * qty);
        document.querySelector(`.item-amount[data-index="${index}"]`).textContent = 'Rs. ' + amount.toFixed(2);
    }

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.item-amount').forEach(el => {
            const amountText = el.textContent.replace('Rs. ', '').replace(',', '');
            total += parseFloat(amountText) || 0;
        });
        document.getElementById('totalAmount').textContent = total.toFixed(2);
    }

    function checkSubmitButton() {
        const hasSelectedItems = document.querySelectorAll('.item-checkbox:checked').length > 0;
        const hasValidQuantities = Array.from(document.querySelectorAll('.return-qty:not([disabled])')).some(input => parseFloat(input.value) > 0);
        submitBtn.disabled = !(hasSelectedItems && hasValidQuantities);
    }
});
</script>
@endpush
@endsection
