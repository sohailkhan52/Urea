<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\View\View;

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
        ]);
    }
}
