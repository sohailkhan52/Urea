@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="page-title">Create Purchase Return</h1>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('admin.purchase-returns.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> Please fix the following errors:
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form id="returnForm" action="{{ route('admin.purchase-returns.store') }}" method="POST">
        @csrf

        <div class="row">
            <!-- LEFT COLUMN -->
            <div class="col-lg-8">
                <!-- SELECT PURCHASE -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-receipt"></i> Select Purchase</h5>
                    </div>
                    <div class="card-body">
                        @if($purchase)
                            <!-- Purchase already selected -->
                            <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
                            <div class="alert alert-info">
                                <div><strong>Purchase:</strong> {{ $purchase->purchase_number }}</div>
                                <div><strong>Supplier:</strong> {{ $purchase->supplier->name }}</div>
                                <div><strong>Date:</strong> {{ $purchase->purchase_date->format('d M Y') }}</div>
                                <div><strong>Total:</strong> Rs. {{ number_format($purchase->total_amount, 2) }}</div>
                            </div>
                        @else
                            <div class="form-group">
                                <label for="purchaseSearch" class="form-label">Find Purchase <span class="text-danger">*</span></label>
                                <input type="text" 
                                       id="purchaseSearch" 
                                       class="form-control" 
                                       placeholder="Search by PO # or supplier name..."
                                       autocomplete="off">
                                <input type="hidden" id="purchase_id" name="purchase_id" required>
                                
                                <div id="purchaseDropdown" class="mt-2" style="display: none; max-height: 400px; overflow-y: auto;">
                                    <div class="list-group" id="purchaseList"></div>
                                </div>

                                <div id="selectedPurchaseInfo" class="alert alert-info mt-3" style="display: none;"></div>
                            </div>

                            <div class="text-muted small mt-2">
                                Select a purchase from the list to begin creating a return
                            </div>
                        @endif
                    </div>
                </div>

                <!-- RETURN ITEMS -->
                <div class="card mb-4" id="itemsCard" style="display: {{ $purchase ? 'block' : 'none' }}">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-box-seam"></i> Return Items</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th style="width: 100px;">Purchased</th>
                                        <th style="width: 100px;">Already Returned</th>
                                        <th style="width: 100px;">Available</th>
                                        <th style="width: 100px;">Return Qty</th>
                                        <th style="width: 100px;">Unit Price</th>
                                        <th style="width: 100px;">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    @if($purchase)
                                        @foreach($purchase->items as $item)
                                            @php
                                                $returnInfo = $itemReturnInfo[$item->id] ?? ['returned' => 0, 'available' => $item->quantity];
                                                $availableQty = $returnInfo['available'];
                                                $returnedQty = $returnInfo['returned'];
                                                $isDisabled = $availableQty <= 0;
                                            @endphp
                                            <tr @if($isDisabled) class="table-secondary" @endif>
                                                <td>
                                                    <strong>{{ $item->product->name }}</strong><br>
                                                    <small class="text-muted">Unit: {{ $item->product->unit }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">{{ $item->quantity }}</span>
                                                </td>
                                                <td>
                                                    @if($returnedQty > 0)
                                                        <span class="badge bg-warning">{{ $returnedQty }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($availableQty > 0)
                                                        <span class="badge bg-success">{{ $availableQty }}</span>
                                                    @else
                                                        <span class="badge bg-danger">0 (all returned)</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                           class="form-control form-control-sm return-qty" 
                                                           data-item-id="{{ $item->id }}"
                                                           data-product-id="{{ $item->product_id }}"
                                                           data-max="{{ $availableQty }}"
                                                           data-price="{{ $item->unit_price }}"
                                                           min="0"
                                                           max="{{ $availableQty }}"
                                                           step="0.01"
                                                           placeholder="0"
                                                           @if($isDisabled) disabled @endif
                                                           oninput="updateItemTotal(this)">
                                                    @if($isDisabled)
                                                        <small class="text-danger">No quantity available to return</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    Rs. {{ number_format($item->unit_price, 2) }}
                                                </td>
                                                <td class="item-total">
                                                    Rs. 0.00
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN -->
            <div class="col-lg-4">
                <div class="card mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-calculator"></i> Return Summary</h5>
                    </div>
                    <div class="card-body">
                        <!-- Return Date -->
                        <div class="mb-3">
                            <label for="return_date" class="form-label">Return Date <span class="text-danger">*</span></label>
                            <input type="date" 
                                   id="return_date" 
                                   name="return_date" 
                                   class="form-control" 
                                   value="{{ date('Y-m-d') }}"
                                   required>
                        </div>

                        <hr>

                        <!-- Financial Summary -->
                        <div class="mb-3 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <strong>Rs. <span id="subtotal">0.00</span></strong>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between">
                                <strong>Total Return Amount:</strong>
                                <strong class="h5 text-danger">Rs. <span id="total_amount">0.00</span></strong>
                            </div>
                        </div>

                        <hr>

                        <!-- Reason -->
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for Return</label>
                            <textarea id="reason" 
                                      name="reason" 
                                      class="form-control form-control-sm" 
                                      rows="2" 
                                      placeholder="Why are these items being returned?"></textarea>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea id="notes" 
                                      name="notes" 
                                      class="form-control form-control-sm" 
                                      rows="2" 
                                      placeholder="Additional notes..."></textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger btn-lg" id="submitBtn" disabled>
                                <i class="bi bi-check-circle"></i> Create Return
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    let returnItems = [];
    let selectedPurchase = @json($purchase);

    document.addEventListener('DOMContentLoaded', function() {
        @if(!$purchase)
            setupPurchaseSearch();
        @endif
        
        updateTotals();
    });

    @if(!$purchase)
    function setupPurchaseSearch() {
        const searchInput = document.getElementById('purchaseSearch');
        const dropdown = document.getElementById('purchaseDropdown');
        const purchaseList = document.getElementById('purchaseList');
        let allPurchases = [];

        // Load all purchases on page load
        loadAllPurchases();

        // Show dropdown on focus
        searchInput.addEventListener('focus', function() {
            if (allPurchases.length > 0) {
                displayPurchases(allPurchases);
                dropdown.style.display = 'block';
            }
        });

        // Filter purchases on input
        searchInput.addEventListener('input', function() {
            const term = this.value.trim().toLowerCase();
            
            if (term.length === 0) {
                // Show all purchases if search is empty
                displayPurchases(allPurchases);
            } else {
                // Filter purchases by search term
                const filtered = allPurchases.filter(purchase => {
                    return purchase.purchase_number.toLowerCase().includes(term) ||
                           purchase.supplier_name.toLowerCase().includes(term);
                });
                displayPurchases(filtered);
            }
            
            dropdown.style.display = 'block';
        });

        // Hide dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        function loadAllPurchases() {
            fetch(`/admin/purchase-returns/purchases/search`)
                .then(response => response.json())
                .then(data => {
                    allPurchases = data;
                    // Show all purchases initially
                    if (searchInput === document.activeElement) {
                        displayPurchases(data);
                        dropdown.style.display = 'block';
                    }
                })
                .catch(error => console.error('Error loading purchases:', error));
        }

        function displayPurchases(purchases) {
            if (purchases.length === 0) {
                purchaseList.innerHTML = '<div class="list-group-item text-muted">No purchases found</div>';
            } else {
                purchaseList.innerHTML = purchases.map(purchase => `
                    <a href="#" class="list-group-item list-group-item-action" 
                       onclick="selectPurchase(${purchase.id}); return false;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <strong>${purchase.purchase_number}</strong>
                                    <span class="badge bg-primary">${purchase.purchase_date}</span>
                                </div>
                                <div class="text-muted small mt-1">
                                    <i class="bi bi-building"></i> ${purchase.supplier_name}
                                </div>
                            </div>
                            <div class="text-end ms-3">
                                <strong class="text-success">Rs. ${parseFloat(purchase.total_amount).toFixed(2)}</strong>
                            </div>
                        </div>
                    </a>
                `).join('');
            }
        }
    }

    function selectPurchase(purchaseId) {
        window.location.href = `{{ route('admin.purchase-returns.create') }}?purchase_id=${purchaseId}`;
    }
    @endif

    function updateItemTotal(input) {
        const qty = parseFloat(input.value) || 0;
        const price = parseFloat(input.dataset.price) || 0;
        const total = qty * price;
        
        const row = input.closest('tr');
        const totalCell = row.querySelector('.item-total');
        totalCell.textContent = `Rs. ${total.toFixed(2)}`;
        
        updateTotals();
    }

    function updateTotals() {
        let subtotal = 0;
        
        document.querySelectorAll('.return-qty').forEach(input => {
            const qty = parseFloat(input.value) || 0;
            const price = parseFloat(input.dataset.price) || 0;
            subtotal += qty * price;
        });

        const totalAmount = subtotal;

        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('total_amount').textContent = totalAmount.toFixed(2);

        // Enable/disable submit button
        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn) {
            submitBtn.disabled = subtotal <= 0;
        }
    }

    // Form submission - collect items
    document.getElementById('returnForm').addEventListener('submit', function(e) {
        const items = [];
        
        document.querySelectorAll('.return-qty').forEach(input => {
            const qty = parseFloat(input.value) || 0;
            if (qty > 0) {
                items.push({
                    purchase_item_id: input.dataset.itemId,
                    product_id: input.dataset.productId,
                    quantity: qty,
                    unit_price: parseFloat(input.dataset.price)
                });
            }
        });

        if (items.length === 0) {
            e.preventDefault();
            alert('Please enter at least one item to return.');
            return false;
        }

        // Add items as hidden inputs
        items.forEach((item, index) => {
            Object.keys(item).forEach(key => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `items[${index}][${key}]`;
                input.value = item[key];
                this.appendChild(input);
            });
        });
    });
</script>
@endpush
@endsection
