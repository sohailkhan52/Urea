@extends('layouts.admin')

@section('title', ($scope === 'family' ? 'Family History - ' : 'Payment History - ') . $customer->name)

@section('content')
<style>
    .print-report-header,
    .print-report-footer,
    .print-customer-details {
        display: none;
    }

    .customer-history-tabs .customer-history-pane {
        display: none;
    }

    .customer-history-tabs .customer-history-pane.active {
        display: block;
    }

    @media (max-width: 575.98px) {
        .customer-history-heading {
            flex-wrap: wrap;
            gap: .35rem;
            margin-bottom: .85rem !important;
        }

        .customer-history-heading > div:first-child {
            flex: 0 0 100%;
            min-width: 0;
        }

        .customer-history-heading h1 {
            font-size: 1rem;
            white-space: nowrap;
        }

        .customer-history-heading p {
            display: none;
        }

        .customer-history-actions {
            flex: 0 0 100%;
            justify-content: flex-end;
            gap: .2rem !important;
        }

        .customer-history-actions .btn {
            padding: .35rem .5rem;
            font-size: .7rem;
            line-height: 1.2;
            white-space: nowrap;
        }

        .customer-history-actions .btn i {
            margin-right: .1rem !important;
            font-size: .65rem;
        }

        .customer-history-scope-tabs {
            flex-wrap: nowrap !important;
            gap: .35rem !important;
            margin-bottom: .75rem !important;
        }

        .customer-history-scope-tabs .btn {
            flex: 1 1 0;
            padding: .38rem .45rem;
            font-size: .72rem;
            line-height: 1.2;
            text-align: center;
            white-space: nowrap;
        }

        .customer-history-summary .card-body {
            padding: .65rem .75rem;
        }

        .customer-history-summary .row {
            --bs-gutter-x: .5rem;
            --bs-gutter-y: .45rem;
        }

        .customer-history-summary .row > [class*="col-"] {
            display: grid;
            grid-template-columns: 30% minmax(0, 1fr);
            column-gap: .4rem;
            align-items: center;
            min-width: 0;
        }

        .customer-history-summary .row > [class*="col-"]:nth-child(3) {
            grid-template-columns: 42% minmax(0, 1fr);
        }

        .customer-history-summary .row > [class*="col-"] > small {
            grid-column: 1;
            margin: 0;
            font-size: .72rem;
            white-space: nowrap;
        }

        .customer-history-summary .row > [class*="col-"] > strong,
        .customer-history-summary .row > [class*="col-"] > span {
            grid-column: 2;
            min-width: 0;
            font-size: .82rem;
            line-height: 1.2;
            white-space: nowrap;
        }

        .customer-history-metrics {
            --bs-gutter-x: .75rem;
            --bs-gutter-y: .75rem;
        }

        .customer-history-metrics > [class*="col-"] {
            display: flex;
            flex: 0 0 50%;
            max-width: 50%;
        }

        .customer-history-metrics .card {
            width: 100%;
            min-height: 102px;
            margin-bottom: 0 !important;
            transition: none !important;
            animation: none !important;
            transform: none !important;
            will-change: auto !important;
        }

        .customer-history-metrics .card,
        .customer-history-metrics .card *,
        .customer-history-metrics .card::before,
        .customer-history-metrics .card::after,
        .customer-history-metrics .card *::before,
        .customer-history-metrics .card *::after {
            transition: none !important;
            animation: none !important;
        }

        .customer-history-metrics .card:hover {
            transform: none !important;
        }

        .customer-history-metrics .card-body {
            padding: .65rem !important;
        }

        .customer-history-metrics .card-body small {
            font-size: .72rem;
            line-height: 1.25;
        }

        .customer-history-metrics .card-body h4 {
            font-size: .9rem;
            overflow-wrap: anywhere;
        }

        .customer-history-tabs .customer-history-tab-header {
            overflow-x: auto;
            padding: .5rem !important;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }

        .customer-history-tab-header::-webkit-scrollbar {
            display: none;
        }

        .customer-history-tab-header .nav-tabs {
            display: flex;
            flex-wrap: nowrap;
            gap: .25rem;
            width: max-content;
            min-width: 100%;
            border-bottom: 0;
        }

        .customer-history-tab-header .nav-item {
            flex: 0 0 auto;
        }

        .customer-history-tab-header .nav-link {
            padding: .45rem .55rem;
            border: 0;
            border-radius: .45rem;
            color: #526174;
            font-size: .68rem;
            line-height: 1.2;
            white-space: nowrap;
        }

        .customer-history-tab-header .nav-link.active {
            color: #fff;
            background: #1769ff;
            box-shadow: 0 2px 6px rgba(23, 105, 255, .2);
        }

        .customer-history-filter .card-body {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) auto auto;
            gap: .45rem !important;
            align-items: end !important;
            padding: .7rem;
        }

        .customer-history-filter .card-body > div {
            grid-column: 1 / -1;
            min-width: 0;
        }

        .customer-history-filter .form-label {
            display: block;
            font-size: .72rem;
        }

        .customer-history-filter .form-select {
            width: 100%;
            min-width: 0;
        }

        .customer-history-filter .form-select,
        .customer-history-filter .btn {
            min-height: 36px;
            padding: .3rem .45rem;
            font-size: .74rem;
        }

        .customer-history-filter .btn {
            white-space: nowrap;
        }

        .customer-history-table {
            min-width: 680px;
            font-size: .76rem;
        }

        .customer-history-table th,
        .customer-history-table td {
            padding: .55rem .65rem;
            white-space: nowrap;
        }

        .customer-history-table .btn {
            padding: .2rem .35rem;
            font-size: .7rem;
        }
    }

    @media (min-width: 576px) and (max-width: 991.98px) {
        .customer-history-heading {
            gap: .75rem;
        }

        .customer-history-heading > div:first-child {
            min-width: 0;
        }

        .customer-history-heading h1 {
            font-size: 1.25rem;
        }

        .customer-history-heading p {
            font-size: .85rem;
        }

        .customer-history-actions {
            flex: 0 0 auto;
            gap: .35rem !important;
        }

        .customer-history-actions .btn {
            padding: .38rem .5rem;
            font-size: .76rem;
            white-space: nowrap;
        }

        .customer-history-metrics > [class*="col-"] {
            flex: 0 0 50%;
            max-width: 50%;
        }

        .customer-history-metrics {
            --bs-gutter-x: .85rem;
            --bs-gutter-y: .85rem;
        }

        .customer-history-metrics .card {
            min-height: 108px;
            margin-bottom: 0 !important;
            transition: none !important;
            animation: none !important;
            transform: none !important;
            will-change: auto !important;
        }

        .customer-history-metrics .card,
        .customer-history-metrics .card *,
        .customer-history-metrics .card::before,
        .customer-history-metrics .card::after,
        .customer-history-metrics .card *::before,
        .customer-history-metrics .card *::after {
            transition: none !important;
            animation: none !important;
        }

        .customer-history-metrics .card:hover {
            transform: none !important;
        }

        .customer-history-metrics .card-body {
            padding: .8rem !important;
        }

        .customer-history-metrics .card-body small {
            font-size: .82rem;
        }

        .customer-history-metrics .card-body h4 {
            font-size: 1rem;
        }

        .customer-history-table {
            min-width: 760px;
        }
    }

    @media print {
        .sidebar,
        .sidebar-backdrop,
        .topbar {
            display: none !important;
        }

        .main-wrapper {
            width: 100% !important;
            margin-left: 0 !important;
            min-height: 0 !important;
        }

        .content {
            padding: 0 !important;
        }

        .print-report-header {
            display: flex !important;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #000;
            padding: 0 0 10px;
            margin-bottom: 12px;
            color: #000 !important;
        }

        .print-report-header strong {
            font-size: 20px;
        }

        .print-report-header small {
            display: block;
            font-size: 11px;
        }

        .print-report-title {
            text-align: center;
        }

        .print-report-title strong {
            font-size: 18px;
        }

        .print-customer-details {
            display: grid !important;
            grid-template-columns: 70px 1fr;
            gap: 2px 8px;
            width: fit-content;
            margin: 0 auto 14px;
            padding: 0 0 8px;
            border-bottom: 1px solid #000;
            font-size: 11px;
            color: #000 !important;
        }

        .print-customer-details strong {
            font-weight: 600;
        }

        .no-print,
        .print-summary,
        .customer-history-tab-header,
        .customer-history-pane:not(.active) {
            display: none !important;
        }

        .print-action {
            display: none !important;
        }

        .customer-history-card {
            box-shadow: none !important;
            border: 0 !important;
        }

        .print-report-footer {
            display: block !important;
            position: fixed;
            left: 0;
            right: 0;
            bottom: 8mm;
            border-top: 1px solid #000;
            padding-top: 6px;
            text-align: center;
            font-size: 11px;
            color: #000 !important;
        }
    }
</style>

<div class="container-fluid py-4">
    @php($company = \App\Models\Company::first())
    <div class="print-report-header">
        <div>
            <strong>{{ $company?->name ?? 'DeraNexa' }}</strong>
            <small>{{ implode(' / ', array_filter([$company?->phone, $company?->additional_number])) }}</small>
        </div>
        <div class="print-report-title">
            <strong>Customer Account</strong>
            <small>Date: {{ now()->format('d M Y') }}</small>
        </div>
    </div>

    <div class="print-customer-details">
        <span>Customer</span><strong>{{ $customer->name }}</strong>
        <span>Family</span><strong>{{ $family?->name ?: '-' }}</strong>
        <span>Phone</span><strong>{{ $customer->phone ?: '-' }}</strong>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4 no-print customer-history-heading">
        <div>
            <h1 class="h3 mb-0 fw-bold" style="color: #1f2937;">{{ $scope === 'family' ? 'Family History' : 'Payment History' }}</h1>
            <p class="text-muted mb-0 mt-1">{{ $scope === 'family' ? 'Complete financial history for all selected family members' : 'Complete financial history for this customer' }}</p>
        </div>
        <div class="d-flex gap-2 customer-history-actions">
            <button type="button" class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i> Print</button>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back to Customers</a>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4 print-summary customer-history-summary" style="border-radius: 12px;">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4"><small class="text-muted d-block">Customer</small><strong>{{ $customer->name }}</strong></div>
                <div class="col-md-4"><small class="text-muted d-block">Family</small><span>{{ $family?->name ?: '-' }}</span></div>
                @if($scope === 'family')
                    <div class="col-md-4"><small class="text-muted d-block">Family Members</small><strong>{{ $members->count() }}</strong></div>
                @endif
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-4 no-print customer-history-scope-tabs">
        <a href="{{ route('admin.customers.history', $customer) }}" class="btn {{ $scope === 'customer' ? 'btn-primary' : 'btn-outline-primary' }}">Customer History</a>
        @if($family)
            <a href="{{ route('admin.customers.history', [$customer, 'scope' => 'family']) }}" class="btn {{ $scope === 'family' ? 'btn-primary' : 'btn-outline-primary' }}">Family History</a>
        @endif
    </div>

    @if($scope === 'family')
        <form method="GET" action="{{ route('admin.customers.history', $customer) }}" class="card shadow-sm border-0 mb-4 no-print customer-history-filter" style="border-radius: 12px;">
            <input type="hidden" name="scope" value="family">
            <div class="card-body d-flex flex-wrap align-items-end gap-3">
                <div>
                    <label for="member_id" class="form-label text-muted mb-1">Family Member Filter</label>
                    <select name="member_id" id="member_id" class="form-select">
                        <option value="">All Family Members</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" @selected($selectedMemberId === $member->id)>{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-outline-primary"><i class="bi bi-funnel me-1"></i> Apply</button>
                <a href="{{ route('admin.customers.history', [$customer, 'scope' => 'family']) }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    @endif

    <div class="row g-3 mb-4 print-summary customer-history-metrics">
        <div class="col-md-6 col-xl-3"><div class="card border-start border-4 border-dark shadow-sm h-100"><div class="card-body"><small class="text-muted">{{ $scope === 'family' ? 'Total Family Sales' : 'Total Sales' }}</small><h4 class="mb-0 mt-1">Rs. {{ number_format($summary['totalSales'], 0) }}</h4></div></div></div>
        <div class="col-md-6 col-xl-3"><div class="card border-start border-4 border-success shadow-sm h-100"><div class="card-body"><small class="text-muted">{{ $scope === 'family' ? 'Total Family Paid' : 'Total Paid' }}</small><h4 class="mb-0 mt-1 text-success">Rs. {{ number_format($summary['totalPaid'], 0) }}</h4></div></div></div>
        <div class="col-md-6 col-xl-3"><div class="card border-start border-4 border-warning shadow-sm h-100"><div class="card-body"><small class="text-muted">{{ $scope === 'family' ? 'Total Family Due' : 'Total Due' }}</small><h4 class="mb-0 mt-1 text-warning">Rs. {{ number_format($summary['totalDue'], 0) }}</h4></div></div></div>
        <div class="col-md-6 col-xl-3"><div class="card border-start border-4 border-info shadow-sm h-100"><div class="card-body"><small class="text-muted">{{ $scope === 'family' ? 'Total Family Returns' : 'Total Sale Returns' }}</small><h4 class="mb-0 mt-1 text-info">Rs. {{ number_format($summary['totalReturns'], 0) }}</h4></div></div></div>
    </div>

    <div class="card shadow-sm border-0 customer-history-tabs customer-history-card" style="border-radius: 12px;">
        <div class="card-header bg-white border-bottom-0 pt-3 px-3 customer-history-tab-header">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#sales" type="button">Sales</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#udhar" type="button">Udhar Management</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#returns" type="button">Sale Returns</button></li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="tab-content">

                <div class="tab-pane fade show active customer-history-pane" id="sales"><div class="table-responsive"><table class="table table-hover align-middle mb-0 customer-history-table"><thead class="table-light"><tr><th>Date</th><th>Customer</th><th>Invoice / Sale Number</th><th>Total Sale Amount</th><th>Paid Amount</th><th class="print-action">Action</th></tr></thead><tbody>
                @forelse($sales as $sale)
                    <tr><td>{{ optional($sale->sale_date)->format('d M Y') }}</td><td>{{ $sale->customer?->name ?: '-' }}</td><td>{{ $sale->invoice_number }}</td><td>Rs. {{ number_format((float) $sale->total_amount, 0) }}</td><td class="text-success">Rs. {{ number_format($sale->history_initial_paid_amount, 0) }}</td><td class="print-action"><a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td></tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No sales found.</td></tr>
                @endforelse
                </tbody></table></div></div>

                <div class="tab-pane fade customer-history-pane" id="udhar"><div class="table-responsive"><table class="table table-hover align-middle mb-0 customer-history-table"><thead class="table-light"><tr><th>Date</th><th>Customer</th><th>Reference / Invoice</th><th>Udhar Amount</th><th>Paid Amount</th></tr></thead><tbody>
                @forelse($udharPayments as $payment)
                    <tr><td>{{ optional($payment->payment_date)->format('d M Y') }}</td><td>{{ $payment->customer?->name ?: '-' }}</td><td>{{ $payment->reference_number ?: ($payment->sale?->invoice_number ?: '-') }}</td><td>Rs. {{ number_format((float) ($payment->sale?->total_amount ?? 0), 0) }}</td><td class="text-success">Rs. {{ number_format((float) $payment->amount, 0) }}</td></tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No Udhar transactions found.</td></tr>
                @endforelse
                </tbody></table></div></div>

                <div class="tab-pane fade customer-history-pane" id="returns"><div class="table-responsive"><table class="table table-hover align-middle mb-0 customer-history-table"><thead class="table-light"><tr><th>Date</th><th>Return Number</th><th>Customer</th><th>Return Amount</th><th class="print-action">Action</th></tr></thead><tbody>
                @forelse($returns as $return)
                    <tr><td>{{ optional($return->return_date)->format('d M Y') }}</td><td>{{ $return->return_number }}</td><td>{{ $return->customer?->name ?: '-' }}</td><td>Rs. {{ number_format((float) $return->total_return_amount, 0) }}</td><td class="print-action"><a href="{{ route('admin.sale-returns.show', $return) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td></tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No sale returns found.</td></tr>
                @endforelse
                </tbody></table></div></div>

                <div class="tab-pane fade customer-history-pane" id="summary"><div class="p-4"><div class="row g-3"><div class="col-md-6"><div class="border rounded p-3"><span class="text-muted">Total Sales</span><h5>Rs. {{ number_format($summary['totalSales'], 0) }}</h5></div></div><div class="col-md-6"><div class="border rounded p-3"><span class="text-muted">Total Paid</span><h5 class="text-success">Rs. {{ number_format($summary['totalPaid'], 0) }}</h5></div></div><div class="col-md-6"><div class="border rounded p-3"><span class="text-muted">Total Due / Current Balance</span><h5 class="text-warning">Rs. {{ number_format($summary['totalDue'], 0) }}</h5></div></div><div class="col-md-6"><div class="border rounded p-3"><span class="text-muted">Total Sale Returns</span><h5 class="text-info">Rs. {{ number_format($summary['totalReturns'], 0) }}</h5></div></div>@if($scope === 'family')<div class="col-12"><div class="border rounded p-3"><span class="text-muted">Family Members Included</span><h5 class="mb-0">{{ $members->count() }}</h5></div></div>@endif</div></div></div>
            </div>
        </div>
    </div>
</div>

<div class="print-report-footer">
    {{ $company?->address ?? 'Dera Ismail Khan' }}
</div>

<script>
    document.querySelectorAll('.customer-history-tabs [data-bs-target]').forEach(function (tab) {
        tab.addEventListener('click', function () {
            var container = tab.closest('.customer-history-tabs');
            var target = container.querySelector(tab.getAttribute('data-bs-target'));

            container.querySelectorAll('.customer-history-tabs .nav-link').forEach(function (item) {
                item.classList.remove('active');
            });
            container.querySelectorAll('.customer-history-pane').forEach(function (pane) {
                pane.classList.remove('active', 'show');
            });

            tab.classList.add('active');
            target.classList.add('active', 'show');
        });
    });
</script>
@endsection
