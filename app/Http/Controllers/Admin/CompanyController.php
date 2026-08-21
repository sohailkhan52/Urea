<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCompanyRequest;
use App\Http\Requests\Admin\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CompanyController extends Controller
{
    /**
     * Display a listing of companies.
     */
    public function index(Request $request): View
    {
        $this->authorize('companies.view');

        // Only super admin can manage companies
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admins can manage companies.');
        }

        $query = Company::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $companies = $query->latest()->paginate(15)->withQueryString();

        return view('admin.companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new company.
     */
    public function create(): View
    {
        $this->authorize('companies.create');

        return view('admin.companies.create');
    }

    /**
     * Store a newly created company in storage.
     */
    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        $this->authorize('companies.create');

        $data = $request->validated();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('company-logos', 'public');
        }

        $company = Company::create($data);

        // Log activity
        Log::info('Company created', [
            'created_by' => Auth::id(),
            'company_id' => $company->id,
            'company_name' => $company->name,
            'company_code' => $company->code,
        ]);

        return redirect()->route('admin.companies.index')
            ->with('success', 'Company created successfully.');
    }

    /**
     * Display the specified company.
     */
    public function show(Company $company): View
    {
        $this->authorize('companies.view');

        return view('admin.companies.show', compact('company'));
    }

    /**
     * Show the form for editing the specified company.
     */
    public function edit(Company $company): View
    {
        $this->authorize('companies.update');

        return view('admin.companies.edit', compact('company'));
    }

    /**
     * Update the specified company in storage.
     */
    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $this->authorize('companies.update');

        $data = $request->validated();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }
            $data['logo'] = $request->file('logo')->store('company-logos', 'public');
        }

        $company->update($data);

        // Log activity
        Log::info('Company updated', [
            'updated_by' => Auth::id(),
            'company_id' => $company->id,
            'company_name' => $company->name,
            'company_code' => $company->code,
        ]);

        return redirect()->route('admin.companies.index')
            ->with('success', 'Company updated successfully.');
    }

    /**
     * Remove the specified company from storage (soft delete).
     */
    public function destroy(Company $company): RedirectResponse
    {
        $this->authorize('companies.delete');

        // Check if company can be deleted
        if (!$company->canBeDeleted()) {
            return back()->with('error', 'Cannot delete this company. It has associated products or transactions.');
        }

        $companyName = $company->name;
        $companyCode = $company->code;

        // Delete logo
        if ($company->logo) {
            Storage::disk('public')->delete($company->logo);
        }

        // Soft delete
        $company->delete();

        // Log activity
        Log::warning('Company deleted', [
            'deleted_by' => Auth::id(),
            'company_name' => $companyName,
            'company_code' => $companyCode,
        ]);

        return redirect()->route('admin.companies.index')
            ->with('success', 'Company deleted successfully.');
    }

    /**
     * Activate a company.
     */
    public function activate(Company $company): RedirectResponse
    {
        $this->authorize('companies.update');

        if ($company->status === Company::STATUS_ACTIVE) {
            return back()->with('info', 'Company is already active.');
        }

        $company->update(['status' => Company::STATUS_ACTIVE]);

        // Log activity
        Log::info('Company activated', [
            'activated_by' => Auth::id(),
            'company_id' => $company->id,
            'company_name' => $company->name,
        ]);

        return back()->with('success', 'Company activated successfully.');
    }

    /**
     * Deactivate a company.
     */
    public function deactivate(Company $company): RedirectResponse
    {
        $this->authorize('companies.update');

        if ($company->status === Company::STATUS_INACTIVE) {
            return back()->with('info', 'Company is already inactive.');
        }

        $company->update(['status' => Company::STATUS_INACTIVE]);

        // Log activity
        Log::warning('Company deactivated', [
            'deactivated_by' => Auth::id(),
            'company_id' => $company->id,
            'company_name' => $company->name,
        ]);

        return back()->with('success', 'Company deactivated successfully.');
    }
}
