<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

/**
 * Dashboard Controller
 * 
 * Handles dashboard and main statistics display.
 * All data loaded through DashboardService for optimized queries.
 */
class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display dashboard with all statistics
     */
    public function index(): View
    {
        // Today's statistics
        $todayStats = $this->dashboardService->getTodayStats();
        
        // Inventory statistics
        $inventoryStats = $this->dashboardService->getInventoryStats();
        
        // Financial summary
        $financialSummary = $this->dashboardService->getFinancialSummary();
        
        // Sales and purchase summaries
        $salesSummary = $this->dashboardService->getSalesSummary();
        $purchaseSummary = $this->dashboardService->getPurchaseSummary();
        
        // Management counts
        $totalSales = $this->dashboardService->getTotalSalesCount();
        $totalPurchases = $this->dashboardService->getTotalPurchasesCount();
        $totalUdhar = $this->dashboardService->getTotalUdharAmount();
        $totalPayables = $this->dashboardService->getTotalPayablesAmount();
        
        // Charts data
        $dailySalesData = $this->dashboardService->getDailySalesData();
        $monthlySalesData = $this->dashboardService->getMonthlySalesData();
        $topProducts = $this->dashboardService->getTopProductsBySales(10);
        $salesByWarehouse = $this->dashboardService->getSalesByWarehouse();
        $topCustomers = $this->dashboardService->getTopCustomers(10);
        
        // Recent movements and low stock
        $recentMovements = $this->dashboardService->getRecentStockMovements(10);
        $lowStockItems = $this->dashboardService->getLowStockItems(10);
        
        // Get company/project settings
        $company = Company::first();

        return view('admin.dashboard.index', [
            // Today
            'todayStats' => $todayStats,
            
            // Inventory
            'inventoryStats' => $inventoryStats,
            'lowStockItems' => $lowStockItems,
            
            // Financial
            'financialSummary' => $financialSummary,
            'salesSummary' => $salesSummary,
            'purchaseSummary' => $purchaseSummary,
            
            // Management counts
            'totalSales' => $totalSales,
            'totalPurchases' => $totalPurchases,
            'totalUdhar' => $totalUdhar,
            'totalPayables' => $totalPayables,
            
            // Charts
            'dailySalesData' => json_encode($dailySalesData),
            'monthlySalesData' => json_encode($monthlySalesData),
            'topProducts' => $topProducts,
            'topCustomers' => $topCustomers,
            'salesByWarehouse' => $salesByWarehouse,
            
            // Recent
            'recentMovements' => $recentMovements,
            
            // Project settings
            'company' => $company,
        ]);
    }

    /**
     * Update project settings (name and logo)
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $this->authorize('admin');

        $validated = $request->validate([
            'project_name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,gif,svg|max:2048',
        ]);

        try {
            // Get or create company
            $company = Company::first();
            if (!$company) {
                $company = Company::create([
                    'name' => config('app.name'),
                    'code' => 'DEFAULT',
                    'status' => Company::STATUS_ACTIVE,
                ]);
            }

            // Update project name
            $company->name = $validated['project_name'];

            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                    Storage::disk('public')->delete($company->logo);
                }

                // Store new logo
                $logoPath = $request->file('logo')->store('logos', 'public');
                $company->logo = $logoPath;

                // Create favicon from uploaded image
                $this->generateFavicon($request->file('logo'));
            }

            $company->save();

            // Update APP_NAME in .env or session
            session(['app_name' => $company->name]);

            return response()->json([
                'success' => true,
                'message' => 'Project settings updated successfully!',
                'new_name' => $company->name,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to update project settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate favicon from uploaded image
     */
    private function generateFavicon($imageFile): void
    {
        try {
            $faviconPath = public_path('favicon.ico');
            $tempPath = $imageFile->getPathname();

            // Delete old favicon if exists
            if (file_exists($faviconPath)) {
                @unlink($faviconPath);
            }

            // Copy uploaded image as favicon.ico
            copy($tempPath, $faviconPath);

            \Log::info('Favicon updated successfully', [
                'path' => $faviconPath,
            ]);

        } catch (\Exception $e) {
            \Log::warning('Failed to generate favicon', [
                'error' => $e->getMessage(),
            ]);
            // Don't throw error, favicon generation is optional
        }
    }
}
