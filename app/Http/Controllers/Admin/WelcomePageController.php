<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWelcomePageFeatureRequest;
use App\Http\Requests\Admin\StoreWelcomePageWorkflowStepRequest;
use App\Http\Requests\Admin\UpdateWelcomePageSettingsRequest;
use App\Models\WelcomePageFeature;
use App\Models\WelcomePageSetting;
use App\Models\WelcomePageWorkflowStep;
use App\Services\WelcomePageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class WelcomePageController extends Controller
{
    /**
     * Service for welcome page operations
     */
    protected WelcomePageService $welcomePageService;

    /**
     * Initialize the controller
     */
    public function __construct(WelcomePageService $welcomePageService)
    {
        $this->welcomePageService = $welcomePageService;
    }

    /**
     * Display the welcome page management panel
     */
    public function index(): View
    {
        $this->authorize('welcome-page.manage');

        // Only super admin can manage welcome page settings
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admins can manage welcome page settings.');
        }

        $settings = $this->welcomePageService->getSettings();

        return view('admin.welcome-page.index', compact('settings'));
    }

    /**
     * Update welcome page settings
     */
    public function updateSettings(UpdateWelcomePageSettingsRequest $request): RedirectResponse
    {
        $this->authorize('welcome-page.manage');

        try {
            $data = $request->validated();

            $settings = $this->welcomePageService->getSettings();

            // Check if logo should be deleted
            if ($request->input('delete_logo') === '1') {
                // Delete the old logo
                $this->welcomePageService->deleteImage($settings->company_logo);
                $data['company_logo'] = null;
            } elseif ($request->hasFile('company_logo')) {
                // Handle logo upload only if not marked for deletion
                $data['company_logo'] = $this->welcomePageService->replaceImage(
                    $request->file('company_logo'),
                    $settings->company_logo,
                    'logo'
                );
            }

            if ($request->hasFile('favicon')) {
                $data['favicon'] = $this->welcomePageService->replaceImage(
                    $request->file('favicon'),
                    $settings->favicon,
                    'favicon'
                );
            } elseif ($request->input('delete_favicon') === '1') {
                $this->welcomePageService->deleteImage($settings->favicon);
                $data['favicon'] = null;
            }

            // Update settings
            $this->welcomePageService->updateSettings($data);

            // Clear company helper cache
            \App\Helpers\CompanyHelper::clearCache();

            // Log activity
            Log::info('Welcome page settings updated', [
                'updated_by' => Auth::id(),
                'company_name' => $data['company_name'] ?? null,
                'company_description' => $data['company_description'] ?? null,
            ]);

            return redirect()->route('admin.welcome-page.index')
                ->with('success', 'Welcome page settings updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating welcome page settings', [
                'error' => $e->getMessage(),
                'updated_by' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('admin.welcome-page.index')
                ->with('error', 'An error occurred while updating settings. Please try again.');
        }
    }

    /**
     * Delete the company logo
     */
    public function deleteLogo(): RedirectResponse
    {
        $this->authorize('welcome-page.manage');

        try {
            $settings = $this->welcomePageService->getSettings();

            if ($settings->company_logo) {
                // Delete the file from storage
                $this->welcomePageService->deleteImage($settings->company_logo);

                // Update the database
                $settings->update(['company_logo' => null]);

                // Clear company helper cache
                \App\Helpers\CompanyHelper::clearCache();

                Log::info('Company logo deleted', [
                    'deleted_by' => Auth::id(),
                ]);

                return redirect()->route('admin.welcome-page.index')
                    ->with('success', 'Logo deleted successfully.');
            }

            return redirect()->route('admin.welcome-page.index')
                ->with('error', 'No logo to delete.');
        } catch (\Exception $e) {
            Log::error('Error deleting logo', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('admin.welcome-page.index')
                ->with('error', 'An error occurred while deleting the logo.');
        }
    }

    /**
     * Store a new feature
     */
    public function storeFeature(StoreWelcomePageFeatureRequest $request): RedirectResponse
    {
        $this->authorize('welcome-page.manage');

        try {
            $data = $request->validated();

            // Set sort order
            $data['sort_order'] = WelcomePageFeature::getMaxSortOrder() + 1;

            WelcomePageFeature::create($data);

            Log::info('Welcome page feature created', [
                'created_by' => Auth::id(),
                'feature_title' => $data['title'],
            ]);

            return redirect()->route('admin.welcome-page.index')
                ->with('success', 'Feature added successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating welcome page feature', [
                'error' => $e->getMessage(),
                'created_by' => Auth::id(),
            ]);

            return redirect()->route('admin.welcome-page.index')
                ->with('error', 'An error occurred while adding the feature. Please try again.');
        }
    }

    /**
     * Update a feature
     */
    public function updateFeature(
        StoreWelcomePageFeatureRequest $request,
        WelcomePageFeature $feature
    ): RedirectResponse {
        $this->authorize('welcome-page.manage');

        try {
            $data = $request->validated();

            $feature->update($data);

            Log::info('Welcome page feature updated', [
                'updated_by' => Auth::id(),
                'feature_id' => $feature->id,
                'feature_title' => $feature->title,
            ]);

            return redirect()->route('admin.welcome-page.index')
                ->with('success', 'Feature updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating welcome page feature', [
                'error' => $e->getMessage(),
                'updated_by' => Auth::id(),
                'feature_id' => $feature->id,
            ]);

            return redirect()->route('admin.welcome-page.index')
                ->with('error', 'An error occurred while updating the feature. Please try again.');
        }
    }

    /**
     * Delete a feature
     */
    public function destroyFeature(WelcomePageFeature $feature): RedirectResponse
    {
        $this->authorize('welcome-page.manage');

        try {
            $featureTitle = $feature->title;
            $feature->delete();

            Log::info('Welcome page feature deleted', [
                'deleted_by' => Auth::id(),
                'feature_title' => $featureTitle,
            ]);

            return redirect()->route('admin.welcome-page.index')
                ->with('success', 'Feature deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting welcome page feature', [
                'error' => $e->getMessage(),
                'deleted_by' => Auth::id(),
                'feature_id' => $feature->id,
            ]);

            return redirect()->route('admin.welcome-page.index')
                ->with('error', 'An error occurred while deleting the feature. Please try again.');
        }
    }

    /**
     * Reorder features
     */
    public function reorderFeatures(Request $request): JsonResponse
    {
        $this->authorize('welcome-page.manage');

        try {
            $order = $request->input('order', []);

            foreach ($order as $index => $featureId) {
                WelcomePageFeature::findOrFail($featureId)->update(['sort_order' => $index]);
            }

            Log::info('Welcome page features reordered', [
                'reordered_by' => Auth::id(),
                'feature_count' => count($order),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Features reordered successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error reordering features', [
                'error' => $e->getMessage(),
                'reordered_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while reordering features.',
            ], 500);
        }
    }

    /**
     * Toggle a feature active/inactive
     */
    public function toggleFeature(WelcomePageFeature $feature): JsonResponse
    {
        $this->authorize('welcome-page.manage');

        try {
            $feature->update(['is_active' => !$feature->is_active]);

            Log::info('Welcome page feature toggled', [
                'toggled_by' => Auth::id(),
                'feature_id' => $feature->id,
                'is_active' => $feature->is_active,
            ]);

            return response()->json([
                'success' => true,
                'is_active' => $feature->is_active,
                'message' => $feature->is_active ? 'Feature activated.' : 'Feature deactivated.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling feature', [
                'error' => $e->getMessage(),
                'toggled_by' => Auth::id(),
                'feature_id' => $feature->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while toggling the feature.',
            ], 500);
        }
    }

    /**
     * Store a new workflow step
     */
    public function storeWorkflowStep(StoreWelcomePageWorkflowStepRequest $request): RedirectResponse
    {
        $this->authorize('welcome-page.manage');

        try {
            $data = $request->validated();

            // Set sort order
            $data['sort_order'] = WelcomePageWorkflowStep::getMaxSortOrder() + 1;

            WelcomePageWorkflowStep::create($data);

            Log::info('Welcome page workflow step created', [
                'created_by' => Auth::id(),
                'step_title' => $data['title'],
            ]);

            return redirect()->route('admin.welcome-page.index')
                ->with('success', 'Workflow step added successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating workflow step', [
                'error' => $e->getMessage(),
                'created_by' => Auth::id(),
            ]);

            return redirect()->route('admin.welcome-page.index')
                ->with('error', 'An error occurred while adding the workflow step. Please try again.');
        }
    }

    /**
     * Update a workflow step
     */
    public function updateWorkflowStep(
        StoreWelcomePageWorkflowStepRequest $request,
        WelcomePageWorkflowStep $workflowStep
    ): RedirectResponse {
        $this->authorize('welcome-page.manage');

        try {
            $data = $request->validated();

            $workflowStep->update($data);

            Log::info('Welcome page workflow step updated', [
                'updated_by' => Auth::id(),
                'step_id' => $workflowStep->id,
                'step_title' => $workflowStep->title,
            ]);

            return redirect()->route('admin.welcome-page.index')
                ->with('success', 'Workflow step updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating workflow step', [
                'error' => $e->getMessage(),
                'updated_by' => Auth::id(),
                'step_id' => $workflowStep->id,
            ]);

            return redirect()->route('admin.welcome-page.index')
                ->with('error', 'An error occurred while updating the workflow step. Please try again.');
        }
    }

    /**
     * Delete a workflow step
     */
    public function destroyWorkflowStep(WelcomePageWorkflowStep $workflowStep): RedirectResponse
    {
        $this->authorize('welcome-page.manage');

        try {
            $stepTitle = $workflowStep->title;
            $workflowStep->delete();

            Log::info('Welcome page workflow step deleted', [
                'deleted_by' => Auth::id(),
                'step_title' => $stepTitle,
            ]);

            return redirect()->route('admin.welcome-page.index')
                ->with('success', 'Workflow step deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting workflow step', [
                'error' => $e->getMessage(),
                'deleted_by' => Auth::id(),
                'step_id' => $workflowStep->id,
            ]);

            return redirect()->route('admin.welcome-page.index')
                ->with('error', 'An error occurred while deleting the workflow step. Please try again.');
        }
    }

    /**
     * Reorder workflow steps
     */
    public function reorderSteps(Request $request): JsonResponse
    {
        $this->authorize('welcome-page.manage');

        try {
            $order = $request->input('order', []);

            foreach ($order as $index => $stepId) {
                WelcomePageWorkflowStep::findOrFail($stepId)->update(['sort_order' => $index]);
            }

            Log::info('Welcome page workflow steps reordered', [
                'reordered_by' => Auth::id(),
                'step_count' => count($order),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Workflow steps reordered successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error reordering workflow steps', [
                'error' => $e->getMessage(),
                'reordered_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while reordering workflow steps.',
            ], 500);
        }
    }

    /**
     * Toggle a workflow step active/inactive
     */
    public function toggleStep(WelcomePageWorkflowStep $workflowStep): JsonResponse
    {
        $this->authorize('welcome-page.manage');

        try {
            $workflowStep->update(['is_active' => !$workflowStep->is_active]);

            Log::info('Welcome page workflow step toggled', [
                'toggled_by' => Auth::id(),
                'step_id' => $workflowStep->id,
                'is_active' => $workflowStep->is_active,
            ]);

            return response()->json([
                'success' => true,
                'is_active' => $workflowStep->is_active,
                'message' => $workflowStep->is_active ? 'Step activated.' : 'Step deactivated.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling workflow step', [
                'error' => $e->getMessage(),
                'toggled_by' => Auth::id(),
                'step_id' => $workflowStep->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while toggling the workflow step.',
            ], 500);
        }
    }
}
