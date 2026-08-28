@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Stock Movements Report</h1>
            <p class="text-muted mb-0">Immutable audit trail of every inventory movement</p>
        </div>
        <div class="d-flex gap-2 no-print">
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Print
            </button>
        </div>
    </div>

    {{-- Filter Panel --}}
    <div class="card mb-4 no-print">
        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
            <span class="fw-semibold"><i class="bi bi-funnel me-2"></i>Filters</span>
            <button class="btn btn-sm btn-link p-0 text-secondary" type="button"
                    data-bs-toggle="collapse" data-bs-target="#filterPanel">
                <i class="bi bi-chevron-down"></i>
            </button>
        </div>
        <div class="collapse show" id="filterPanel">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reports.inventory.stock-movements') }}">
                    <div class="row g-3">

                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Date From</label>
                            <input type="date" name="date_from" class="form-control form-control-sm"
                                   value="{{ $filters['date_from'] ?? now()->toDateString() }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Date To</label>
                            <input type="date" name="date_to" class="form-control form-control-sm"
                                   value="{{ $filters['date_to'] ?? now()->toDateString() }}">
                        </div>

                        {{-- Quick presets --}}
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Quick Range</label>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('today')">Today</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('yesterday')">Yesterday</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('this_week')">This Week</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('this_month')">This Month</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('last_month')">Last Month</button>
                            </div>
                        </div>

                        @if(auth()->user()->isSuperAdmin())
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Warehouse</label>
                            <select name="warehouse_id" class="form-select form-select-sm">
                                <option value="">All Warehouses</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}"
                                        {{ ($filters['warehouse_id'] ?? '') == $wh->id ? 'selected' : '' }}>
                                        {{ $wh->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Product</label>
                            <select name="product_id" class="form-select form-select-sm">
                                <option value="">All Products</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}"
                                        {{ ($filters['product_id'] ?? '') == $prod->id ? 'selected' : '' }}>
                                        {{ $prod->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Movement Type</label>
                            <select name="type" class="form-select form-select-sm">
                                <option value="">All Types</option>
                                @foreach($movementTypes as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ ($filters['type'] ?? '') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Reference</label>
                            <input type="text" name="reference" class="form-control form-control-sm"
                                   placeholder="Doc ID or remarks…"
                                   value="{{ $filters['reference'] ?? '' }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Product name, SKU or remarks…"
                                   value="{{ $filters['search'] ?? '' }}">
                        </div>

                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i>Apply Filters
                        </button>
                        <a href="{{ route('admin.reports.inventory.stock-movements') }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total In</p>
                    <h4 class="mb-0 fw-bold text-success">{{ number_format($summary['total_in'], 2) }}</h4>
                    <small class="text-muted">units received</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-danger border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Out</p>
                    <h4 class="mb-0 fw-bold text-danger">{{ number_format($summary['total_out'], 2) }}</h4>
                    <small class="text-muted">units dispatched</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            @php $net = $summary['net_movement']; @endphp
            <div class="card border-0 shadow-sm h-100 border-start {{ $net >= 0 ? 'border-info' : 'border-warning' }} border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Net Movement</p>
                    <h4 class="mb-0 fw-bold {{ $net >= 0 ? 'text-info' : 'text-warning' }}">
                        {{ $net >= 0 ? '+' : '' }}{{ number_format($net, 2) }}
                    </h4>
                    <small class="text-muted">in − out</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-primary border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Closing Balance</p>
                    <h4 class="mb-0 fw-bold text-primary">{{ number_format($summary['closing_balance'], 2) }}</h4>
                    <small class="text-muted">last balance_after</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-secondary border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Movements</p>
                    <h4 class="mb-0 fw-bold">{{ number_format($summary['movement_count']) }}</h4>
                    <small class="text-muted">records in period</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-dark border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Showing</p>
                    <h4 class="mb-0 fw-bold">{{ $report->total() }}</h4>
                    <small class="text-muted">filtered records</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Movement type legend --}}
    <div class="no-print mb-3 d-flex flex-wrap gap-1">
        @php
        $typeColors = [
            'opening_stock'  => 'bg-dark',
            'purchase'       => 'bg-primary',
            'sale'           => 'bg-success',
            'customer_return'=> 'bg-info text-dark',
            'supplier_return'=> 'bg-secondary',
            'transfer_in'    => 'bg-primary',
            'transfer_out'   => 'bg-warning text-dark',
            'adjustment_in'  => 'bg-success',
            'adjustment_out' => 'bg-danger',
            'damaged'        => 'bg-danger',
            'expired'        => 'bg-warning text-dark',
        ];
        @endphp
        @foreach($movementTypes as $val => $lbl)
            <span class="badge {{ $typeColors[$val] ?? 'bg-secondary' }}">{{ $lbl }}</span>
        @endforeach
    </div>

    {{-- Data Table --}}
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Movement History</h5>
            <span class="text-muted small">
                {{ $filters['date_from'] }} → {{ $filters['date_to'] }}
                &nbsp;|&nbsp; {{ $report->total() }} record(s)
            </span>
        </div>
        <div class="card-body p-0">
            @if($report->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Date & Time</th>
                            <th>Reference</th>
                            <th>Type</th>
                            <th>Product</th>
                            <th>Warehouse</th>
                            <th class="text-end text-success">Qty In</th>
                            <th class="text-end text-danger">Qty Out</th>
                            <th class="text-end">Balance After</th>
                            <th class="text-end">Unit Cost</th>
                            <th class="text-end">Total Value</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report as $mov)
                        @php
                            $typeColor = $typeColors[$mov->type] ?? 'bg-secondary';
                            $isIn  = $mov->quantity_in  > 0;
                            $isOut = $mov->quantity_out > 0;
                            $totalValue = ($mov->unit_cost ?? 0) * max($mov->quantity_in, $mov->quantity_out);
                        @endphp
                        <tr>
                            <td class="ps-3 text-muted">
                                {{ ($report->currentPage() - 1) * $report->perPage() + $loop->iteration }}
                            </td>
                            <td class="text-nowrap">
                                <div>{{ $mov->created_at->format('d M Y') }}</div>
                                <small class="text-muted">{{ $mov->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                @if($mov->reference_id)
                                    <code class="small">
                                        #{{ $mov->reference_id }}
                                        @if($mov->reference_type)
                                            <br><small class="text-muted">{{ class_basename($mov->reference_type) }}</small>
                                        @endif
                                    </code>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $typeColor }}">
                                    {{ $movementTypes[$mov->type] ?? $mov->type }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $mov->product->name }}</div>
                                <small class="text-muted">{{ $mov->product->sku }}</small>
                            </td>
                            <td>
                                <span class="badge bg-dark">{{ $mov->warehouse->name }}</span>
                            </td>
                            <td class="text-end {{ $isIn ? 'text-success fw-semibold' : 'text-muted' }}">
                                {{ $isIn ? '+'.number_format($mov->quantity_in, 2) : '—' }}
                            </td>
                            <td class="text-end {{ $isOut ? 'text-danger fw-semibold' : 'text-muted' }}">
                                {{ $isOut ? '-'.number_format($mov->quantity_out, 2) : '—' }}
                            </td>
                            <td class="text-end fw-semibold">
                                {{ number_format($mov->balance_after, 2) }}
                            </td>
                            <td class="text-end text-muted">
                                {{ $mov->unit_cost ? 'Rs. '.number_format($mov->unit_cost, 2) : '—' }}
                            </td>
                            <td class="text-end {{ $totalValue > 0 ? '' : 'text-muted' }}">
                                {{ $totalValue > 0 ? 'Rs. '.number_format($totalValue, 2) : '—' }}
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $mov->remarks ? \Illuminate\Support\Str::limit($mov->remarks, 40) : '—' }}
                                </small>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-semibold">
                        <tr>
                            <td colspan="6" class="ps-3 text-end">Page Totals:</td>
                            <td class="text-end text-success">
                                +{{ number_format($report->getCollection()->sum('quantity_in'), 2) }}
                            </td>
                            <td class="text-end text-danger">
                                -{{ number_format($report->getCollection()->sum('quantity_out'), 2) }}
                            </td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="card-footer bg-white no-print">
                {{ $report->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-arrow-left-right text-muted" style="font-size:3rem"></i>
                <h5 class="mt-3 text-muted">No Movements Found</h5>
                <p class="text-muted">No stock movements match the selected filters.</p>
                <a href="{{ route('admin.reports.inventory.stock-movements') }}"
                   class="btn btn-outline-primary btn-sm">Reset Filters</a>
            </div>
            @endif
        </div>
    </div>

    <p class="text-muted small mt-3 no-print">
        <i class="bi bi-info-circle me-1"></i>
        Stock movements are <strong>immutable</strong> — they cannot be edited or deleted.
        Balance After = running stock balance at the time of the movement.
        Total Value = Unit Cost × Quantity (where unit cost is recorded on the movement).
    </p>

</div>
@endsection

@push('styles')
<style>
.btn-xs { padding:.2rem .45rem; font-size:.75rem; }
.border-3 { border-width:3px !important; }
code.small { font-size:.8rem; background:#f8f9fa; padding:.1rem .3rem; border-radius:.25rem; }
@media print {
    * { color: #000000 !important; }
    .no-print { display:none !important; }
    .card { border:none !important; box-shadow:none !important; }
}
    .table { font-size:9px; }
    body { font-size:10px; }
    th, td { padding:3px 5px !important; }
}
</style>
@endpush

@push('scripts')
<script>
function setRange(preset) {
    const df = document.querySelector('[name=date_from]');
    const dt = document.querySelector('[name=date_to]');
    const today = new Date();
    const fmt = d => d.toISOString().slice(0,10);
    switch(preset) {
        case 'today':     df.value = dt.value = fmt(today); break;
        case 'yesterday': {
            const y = new Date(today); y.setDate(y.getDate()-1);
            df.value = dt.value = fmt(y); break;
        }
        case 'this_week': {
            const mon = new Date(today);
            mon.setDate(today.getDate() - (today.getDay()===0 ? 6 : today.getDay()-1));
            df.value = fmt(mon); dt.value = fmt(today); break;
        }
        case 'this_month':
            df.value = fmt(new Date(today.getFullYear(), today.getMonth(), 1));
            dt.value = fmt(today); break;
        case 'last_month':
            df.value = fmt(new Date(today.getFullYear(), today.getMonth()-1, 1));
            dt.value = fmt(new Date(today.getFullYear(), today.getMonth(), 0)); break;
    }
}
</script>
@endpush
