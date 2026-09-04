@extends('layouts.admin')

@section('title', 'Create Purchase Return')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-arrow-return-left me-2"></i>Create Purchase Return
        </h1>
        <a href="{{ route('admin.purchase-returns.create') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Purchases List
        </a>
    </div>

    {{-- Purchase Summary --}}
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Purchase Order Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>PO Number:</strong> {{ $purchase->purchase_number }}</p>
                            <p class="mb-2"><strong>Supplier:</strong> {{ $purchase->supplier->name ?? 'Unknown' }}</p>
                            <p class="mb-0"><strong>Date:</strong> {{ $purchase->purchase_date->format('d M Y') }}</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <p class="mb-2"><strong>Total Amount:</strong></p>
                            <h4 class="text-primary">Rs. {{ number_format($purchase->total_amount, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Payment Status</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Paid:</strong> Rs. {{ number_format($purchase->paid_amount ?? 0, 2) }}</p>
                    <p class="mb-0"><strong>Outstanding:</strong> Rs. {{ number_format(($purchase->total_amount - ($purchase->paid_amount ?? 0)), 2) }}</p>
                    <hr>
                    <span class="badge bg-{{ $purchase->payment_status === 'Paid' ? 'success' : ($purchase->payment_status === 'Partial' ? 'warning' : 'danger') }}">
                        {{ $purchase->payment_status ?? 'Completed' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Return Items Form --}}
    <form action="{{ route('admin.purchase-returns.store') }}" method="POST" id="returnForm">
        @csrf
        <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">

        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Select Items to Return</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Product</th>
                                <th class="text-center" style="width: 100px;">Purchased Qty</th>
                                <th class="text-center" style="width: 80px;">Returned</th>
                                <th class="text-center" style="width: 80px;">Remaining</th>
                                <th class="text-end" style="width: 100px;">Unit Price</th>
                                <th style="width: 150px;">Return Qty</th>
                                <th class="text-end" style="width: 150px;">Return Amount</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            @forelse($purchase->items as $index => $item)
                                <tr>
                                    <td>
                                        <input type="checkbox" 
                                               class="form-check-input item-checkbox" 
                                               data-index="{{ $index }}"
                                               onchange="updateItemRow({{ $index }})">
                                    </td>
                                    <td><strong>{{ $item->product->name }}</strong></td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ $returnedQuantities[$item->id] ?? 0 }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning">{{ $item->quantity - ($returnedQuantities[$item->id] ?? 0) }}</span>
                                    </td>
                                    <td class="text-end">Rs. {{ number_format($item->unit_price, 2) }}</td>
                                    <td>
                                        <input type="number" 
                                               class="form-control form-control-sm return-qty" 
                                               name="items[{{ $index }}][quantity]"
                                               data-index="{{ $index }}"
                                               min="0" 
                                               max="{{ $item->quantity - ($returnedQuantities[$item->id] ?? 0) }}" 
                                               step="0.01"
                                               value="0"
                                               onchange="updateItemRow({{ $index }})">
                                    </td>
                                    <td class="text-end">
                                        <strong class="return-amount" data-index="{{ $index }}">Rs. 0.00</strong>
                                        <input type="hidden" name="items[{{ $index }}][purchase_item_id]" value="{{ $item->id }}">
                                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                        <input type="hidden" name="items[{{ $index }}][unit_price]" value="{{ $item->unit_price }}">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No items in this purchase order</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <th colspan="6" class="text-end">Total Return Amount:</th>
                                <th class="text-end"><strong>Rs. <span id="totalReturnAmount">0.00</span></strong></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Return Details --}}
        <div class="card mt-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Return Details</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="return_date" class="form-label">Return Date <span class="text-danger">*</span></label>
                        <input type="date" 
                               class="form-control" 
                               id="return_date" 
                               name="return_date" 
                               value="{{ date('Y-m-d') }}"
                               required>
                    </div>
                    <div class="col-md-8">
                        <label for="reason" class="form-label">Reason for Return</label>
                        <input type="text" 
                               class="form-control" 
                               id="reason" 
                               name="reason" 
                               placeholder="e.g., Defective, Damaged, Wrong item..."
                               maxlength="500">
                    </div>
                    <div class="col-md-12">
                        <label for="notes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" 
                                  id="notes" 
                                  name="notes" 
                                  rows="3"
                                  maxlength="1000"></textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="card mt-4">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.purchase-returns.create') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-circle me-1"></i> Create Return
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Select all checkbox
document.getElementById('selectAll').addEventListener('change', function(e) {
    document.querySelectorAll('.item-checkbox').forEach(checkbox => {
        checkbox.checked = e.target.checked;
        const index = checkbox.dataset.index;
        updateItemRow(index);
    });
});

function updateItemRow(index) {
    const checkbox = document.querySelector(`[data-index="${index}"][type="checkbox"]`);
    const qtyInput = document.querySelector(`.return-qty[data-index="${index}"]`);
    const amountSpan = document.querySelector(`.return-amount[data-index="${index}"]`);
    
    if (checkbox.checked && qtyInput.value == 0) {
        qtyInput.value = qtyInput.max;
    }
    
    updateAmount(index);
    updateTotal();
}

function updateAmount(index) {
    const qtyInput = document.querySelector(`.return-qty[data-index="${index}"]`);
    const priceInput = document.querySelector(`input[name="items[${index}][unit_price]"]`);
    const amountSpan = document.querySelector(`.return-amount[data-index="${index}"]`);
    
    const qty = parseFloat(qtyInput.value) || 0;
    const price = parseFloat(priceInput.value) || 0;
    const amount = qty * price;
    
    amountSpan.textContent = 'Rs. ' + amount.toFixed(2);
}

function updateTotal() {
    let total = 0;
    document.querySelectorAll('.return-qty').forEach(input => {
        const qty = parseFloat(input.value) || 0;
        const index = input.dataset.index;
        const priceInput = document.querySelector(`input[name="items[${index}][unit_price]"]`);
        const price = parseFloat(priceInput.value) || 0;
        total += qty * price;
    });
    
    document.getElementById('totalReturnAmount').textContent = total.toFixed(2);
}

// Validate form
document.getElementById('returnForm').addEventListener('submit', function(e) {
    const hasItems = Array.from(document.querySelectorAll('.return-qty')).some(input => parseFloat(input.value) > 0);
    if (!hasItems) {
        e.preventDefault();
        alert('Please select at least one item to return');
    }
});
</script>
@endsection
