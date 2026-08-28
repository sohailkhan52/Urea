<?php

namespace App\View\Composers;

use App\Services\MultiWarehouseFeatureService;
use Illuminate\View\View;

/**
 * MultiWarehouseComposer
 * 
 * Shares multi-warehouse feature availability status with all views.
 * This allows the sidebar and other UI elements to show/hide features
 * based on the number of active warehouses.
 */
class MultiWarehouseComposer
{
    /**
     * Create a new composer instance.
     */
    public function __construct(
        protected MultiWarehouseFeatureService $multiWarehouseService
    ) {}

    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view): void
    {
        $view->with([
            'isMultiWarehouseEnabled' => $this->multiWarehouseService->isEnabled(),
            'activeWarehouseCount' => $this->multiWarehouseService->getActiveWarehouseCount(),
        ]);
    }
}
