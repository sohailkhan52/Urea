@extends('layouts.admin')

@section('title', 'Create Sale Return')

@section('content')
<div class="container-fluid sale-return-form-page">
    <div class="d-flex justify-content-between align-items-center mb-4 return-form-heading">
        <h1 class="h3 mb-0">
            Create Sale Return
        </h1>
        <a href="{{ route('admin.sale-returns.create') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Sales
        </a>
    </div>

    {{-- Sale Summary --}}
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Sale Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Invoice:</strong> {{ $sale->invoice_number }}</p>
                            <p class="mb-2"><strong>Customer:</strong> {{ $sale->customer ? $sale->customer->name : ($sale->walkin_customer_name ?? 'Walk-in') }}</p>
                            <p class="mb-0"><strong>Date:</strong> {{ $sale->sale_date->format('d M Y') }}</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <p class="mb-2"><strong>Total Amount:</strong></p>
                            <h4 class="text-primary">Rs. {{ number_format($sale->total_amount, 0) }}</h4>
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
                    <p class="mb-1"><strong>Paid:</strong> Rs. {{ number_format($sale->paid_amount ?? 0, 0) }}</p>
                    <p class="mb-0"><strong>Outstanding:</strong> Rs. {{ number_format($sale->current_remaining_udhar, 0) }}</p>
                    <hr>
                    <span class="badge bg-{{ $sale->current_payment_status === 'paid' ? 'success' : ($sale->current_payment_status === 'partial' ? 'warning' : 'danger') }}">
                        {{ ucfirst($sale->current_payment_status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Return Items Form --}}
    <form action="{{ route('admin.sale-returns.store') }}" method="POST" id="returnForm">
        @csrf
        <input type="hidden" name="sale_id" value="{{ $sale->id }}">

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
                                <th class="text-center" style="width: 100px;">Sold Qty</th>
                                <th class="text-center" style="width: 100px;">Can Return</th>
                                <th class="text-end" style="width: 100px;">Unit Price</th>
                                <th style="width: 150px;">Return Qty</th>
                                <th class="text-end" style="width: 150px;">Return Amount</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            @forelse($sale->items as $index => $item)
                                @php
                                    // Calculate how much can still be returned
                                    $alreadyReturned = \App\Models\SaleReturnItem::where('sale_item_id', $item->id)
                                        ->whereHas('saleReturn', function ($q) {
                                            $q->where('status', 'confirmed');
                                        })
                                        ->sum('quantity');
                                    $canReturn = max(0, $item->quantity - $alreadyReturned);
                                @endphp
                                <tr>
                                    <td>
                                        <input type="checkbox" 
                                               class="form-check-input item-checkbox" 
                                               data-index="{{ $index }}"
                                               onchange="updateItemRow({{ $index }})">
                                    </td>
                                    <td><strong>{{ $item->product->name }}</strong></td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-center">{{ $canReturn }}</td>
                                    <td class="text-end">Rs. {{ number_format($item->unit_price, 0) }}</td>
                                    <td>
                                        <input type="number" 
                                               class="form-control form-control-sm return-qty" 
                                               name="items[{{ $index }}][quantity]"
                                               data-index="{{ $index }}"
                                               min="0" 
                                               max="{{ $canReturn }}" 
                                               step="0.01"
                                               placeholder="0"
                                               disabled
                                               onchange="updateItemRow({{ $index }})">
                                    </td>
                                    <td class="text-end">
                                        <strong class="return-amount" data-index="{{ $index }}">Rs. 0</strong>
                                        <input type="hidden" name="items[{{ $index }}][sale_item_id]" value="{{ $item->id }}">
                                        <input type="hidden" name="items[{{ $index }}][unit_price]" value="{{ $item->unit_price }}">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No items in this sale</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <th colspan="6" class="text-end">Total Return Amount:</th>
                                <th class="text-end"><strong>Rs. <span id="totalReturnAmount">0</span></strong></th>
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
                               placeholder="e.g., Defective, Wrong item, Customer request..."
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
                    <a href="{{ route('admin.sale-returns.create') }}" class="btn btn-outline-secondary">
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

<style>
    @media (max-width: 768px) {
        .sale-return-form-page {
            padding-right: 10px;
            padding-left: 10px;
        }

        .return-form-heading {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center !important;
            gap: 8px;
            margin-bottom: 14px !important;
        }

        .return-form-heading h1 {
            min-width: 0;
            margin: 0;
            font-size: clamp(0.88rem, 3.8vw, 1.05rem);
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .return-form-heading > a {
            max-width: none;
            min-height: 30px;
            padding: 4px 5px;
            font-size: 0.58rem;
            line-height: 1.1;
            text-align: center;
            white-space: nowrap;
        }

        .sale-return-form-page .row.mb-4 {
            margin-bottom: 14px !important;
        }

        .sale-return-form-page .card {
            margin-bottom: 12px;
        }

        .sale-return-form-page .card-header {
            padding: 9px 12px;
        }

        .sale-return-form-page .card-header h5 {
            font-size: 0.95rem;
        }

        .sale-return-form-page .card-body {
            padding: 12px;
            font-size: 0.82rem;
        }

        .sale-return-form-page .card-body p {
            margin-bottom: 6px !important;
            line-height: 1.35;
        }

        .sale-return-form-page .card-body h4 {
            font-size: 1.1rem;
            margin-bottom: 0;
        }

        .sale-return-form-page .row > .col-md-6.text-end {
            display: flex;
            align-items: baseline;
            justify-content: flex-start;
            gap: 6px;
            text-align: left !important;
        }

        .sale-return-form-page .row > .col-md-6.text-end p {
            margin: 0 !important;
            font-size: 0.78rem;
        }

        .sale-return-form-page .row > .col-md-6.text-end h4 {
            font-size: 1rem;
        }

        .sale-return-form-page .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .sale-return-form-page .table {
            min-width: 600px;
            margin-bottom: 0;
            font-size: 0.72rem;
        }

        .sale-return-form-page .table th,
        .sale-return-form-page .table td {
            padding: 6px 7px;
            vertical-align: middle;
        }

        .sale-return-form-page .return-qty {
            min-width: 68px;
            min-height: 34px;
            padding: 4px 6px;
            font-size: 14px;
        }

        .sale-return-form-page .form-control,
        .sale-return-form-page .form-select {
            min-height: 38px;
            padding: 6px 9px;
            font-size: 15px;
        }

        .sale-return-form-page textarea.form-control {
            min-height: auto;
        }

        .sale-return-form-page .form-label {
            margin-bottom: 4px;
            font-size: 0.78rem;
        }

        .sale-return-form-page .badge {
            font-size: 0.66rem;
        }

        .sale-return-form-page .btn {
            padding: 6px 9px;
            font-size: 0.78rem;
        }

        .sale-return-form-page .btn-lg {
            padding: 7px 10px;
            font-size: 0.82rem;
        }

        .sale-return-form-page #returnForm > .card:last-child .card-body > .d-flex {
            gap: 8px;
        }

        .sale-return-form-page #returnForm > .card:last-child .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            text-align: center;
        }
    }
</style>

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
    
    if (checkbox.checked) {
        qtyInput.disabled = false;
        // Do NOT auto-fill with max value - keep it at 0, let user enter manually
        // if (qtyInput.value == 0 || qtyInput.value == '') {
        //     qtyInput.value = qtyInput.max;
        // }
    } else {
        qtyInput.disabled = true;
        qtyInput.value = '';
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
    
    amountSpan.textContent = 'Rs. ' + Math.round(amount);
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
    
    document.getElementById('totalReturnAmount').textContent = Math.round(total);
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
