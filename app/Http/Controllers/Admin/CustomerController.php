<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Family;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('customers.view');

        $search = trim((string) $request->input('search', ''));
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $customers = Customer::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('village', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        $families = Family::active()->orderBy('name')->get();

        return view('admin.customers.index', compact('customers', 'search', 'families', 'perPage'));
    }

    public function history(Request $request, Customer $customer)
    {
        $this->authorize('customers.view');

        $scope = $request->input('scope') === 'family' && $customer->family_id ? 'family' : 'customer';
        $family = $customer->family;
        $memberIds = $scope === 'family'
            ? $family->customers()->pluck('id')->all()
            : [$customer->id];

        $selectedMemberId = $scope === 'family' && $request->filled('member_id')
            ? (int) $request->input('member_id')
            : null;

        if ($selectedMemberId && in_array($selectedMemberId, $memberIds, true)) {
            $memberIds = [$selectedMemberId];
        } else {
            $selectedMemberId = null;
        }

        $sales = Sale::whereIn('customer_id', $memberIds)
            ->where('status', Sale::STATUS_CONFIRMED)
            ->when($scope === 'customer', fn ($query) => $query
                ->whereNull('family_id')
                ->where('udhar_account_type', Sale::UDHAR_ACCOUNT_TYPE_INDIVIDUAL))
            ->when($scope === 'family', fn ($query) => $query
                ->where('family_id', $family->id)
                ->where('udhar_account_type', Sale::UDHAR_ACCOUNT_TYPE_FAMILY))
            ->with(['customer', 'customerPayments', 'returns'])
            ->orderByDesc('sale_date')
            ->get();

        $payments = CustomerPayment::whereIn('customer_id', $memberIds)
            ->with(['customer', 'sale'])
            ->whereNotIn('payment_method', ['return_adjustment', 'return_credit'])
            ->when($scope === 'customer', fn ($query) => $query->whereHas('sale', fn ($saleQuery) => $saleQuery
                ->whereNull('family_id')
                ->where('udhar_account_type', Sale::UDHAR_ACCOUNT_TYPE_INDIVIDUAL)))
            ->when($scope === 'family', fn ($query) => $query->whereHas('sale', fn ($saleQuery) => $saleQuery
                ->where('family_id', $family->id)
                ->where('udhar_account_type', Sale::UDHAR_ACCOUNT_TYPE_FAMILY)))
            ->orderByDesc('payment_date')
            ->get();

        $returnsQuery = SaleReturn::where('status', SaleReturn::STATUS_CONFIRMED)
            ->where(function ($query) use ($memberIds, $scope, $family, $selectedMemberId) {
                $query->whereIn('customer_id', $memberIds);

                if ($scope === 'family' && $family && $selectedMemberId === null) {
                    $query->orWhere('family_id', $family->id);
                }

                if ($scope === 'customer') {
                    $query->whereHas('sale', fn ($saleQuery) => $saleQuery
                        ->whereNull('family_id')
                        ->where('udhar_account_type', Sale::UDHAR_ACCOUNT_TYPE_INDIVIDUAL));
                }

                if ($scope === 'family') {
                    $query->whereHas('sale', fn ($saleQuery) => $saleQuery
                        ->where('family_id', $family->id)
                        ->where('udhar_account_type', Sale::UDHAR_ACCOUNT_TYPE_FAMILY));
                }
            });

        $returns = $returnsQuery->with(['customer', 'sale'])->orderByDesc('return_date')->get();

        $paymentTotals = $payments->groupBy('sale_id')->map(fn ($items) => (float) $items->sum('amount'));
        $sales->each(function ($sale) use ($paymentTotals) {
            $sale->history_additional_paid_amount = (float) ($paymentTotals[$sale->id] ?? 0);
            $sale->history_initial_paid_amount = (float) $sale->paid_amount;
            $sale->history_paid_amount = (float) $sale->paid_amount;
            $sale->history_return_amount = (float) $sale->total_returned_amount;
            $sale->history_due_amount = max(0, (float) $sale->total_amount - $sale->history_paid_amount - $sale->history_return_amount);
        });

        $totalSales = (float) $sales->sum('total_amount');
        $totalPaid = (float) $sales->sum('history_paid_amount');
        $totalReturns = (float) $returns->sum('total_return_amount');
        $totalDue = max(0, $totalSales - $totalPaid - $totalReturns);
        $members = $scope === 'family' ? $family->customers()->orderBy('name')->get() : collect([$customer]);
        $allTransactions = collect()
            ->concat($sales->map(fn ($sale) => [
                'date' => $sale->sale_date,
                'customer' => $sale->customer,
                'type' => 'Sale',
                'reference' => $sale->invoice_number,
                'amount' => (float) $sale->total_amount,
                'paid' => (float) $sale->history_initial_paid_amount,
                'due' => max(0, (float) $sale->total_amount - (float) $sale->history_initial_paid_amount - (float) $sale->history_return_amount),
                'status' => ucfirst((string) $sale->payment_status),
                'action' => route('admin.sales.show', $sale),
            ]))
            ->concat($payments->map(fn ($payment) => [
                'date' => $payment->payment_date,
                'customer' => $payment->customer,
                'type' => 'Udhar Payment',
                'reference' => $payment->reference_number ?: 'PMT-' . $payment->id,
                'amount' => 0,
                'paid' => (float) $payment->amount,
                'due' => max(0, (float) ($payment->sale?->total_amount ?? 0) - (float) ($payment->sale?->paid_amount ?? 0) - (float) ($payment->sale?->total_returned_amount ?? 0)),
                'status' => 'Received',
                'action' => $payment->sale ? route('admin.sales.show', $payment->sale) : '#',
            ]))
            ->concat($returns->map(fn ($return) => [
                'date' => $return->return_date,
                'customer' => $return->customer,
                'type' => 'Sale Return',
                'reference' => $return->return_number,
                'amount' => (float) $return->total_return_amount,
                'paid' => (float) ($return->refund_amount ?? 0),
                'due' => max(0, (float) $return->total_return_amount - (float) ($return->refund_amount ?? 0)),
                'status' => ucfirst((string) $return->status),
                'action' => route('admin.sale-returns.show', $return),
            ]))
            ->sortByDesc('date')
            ->values();

        return view('admin.customers.history', [
            'customer' => $customer,
            'family' => $family,
            'scope' => $scope,
            'members' => $members,
            'selectedMemberId' => $selectedMemberId,
            'sales' => $sales,
            'payments' => $payments,
            'returns' => $returns,
            'udharPayments' => $payments,
            'udharSales' => $sales->filter(fn ($sale) => $sale->history_paid_amount > 0 || $sale->history_due_amount > 0)->values(),
            'allTransactions' => $allTransactions,
            'summary' => compact('totalSales', 'totalPaid', 'totalDue', 'totalReturns'),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('customers.create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'customer_type' => 'required|in:farmer,dealer,retail_customer',
            'family_id' => 'nullable|exists:families,id',
            'father_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'village' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ]);

        Customer::create($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function update(Request $request, Customer $customer)
    {
        $this->authorize('customers.update');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'customer_type' => 'required|in:farmer,dealer,retail_customer',
            'family_id' => 'nullable|exists:families,id',
            'father_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:customers,email,' . $customer->id,
            'village' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ]);

        $customer->update($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $this->authorize('customers.delete');

        $validated = $request->validate([
            'customer_ids' => 'required|array|min:1',
            'customer_ids.*' => 'integer|exists:customers,id',
        ]);

        $customers = Customer::whereIn('id', $validated['customer_ids'])->get();
        $customers->each->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', $customers->count() . ' customer(s) deleted successfully.');
    }

    public function destroy(Customer $customer)
    {
        $this->authorize('customers.delete');

        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
