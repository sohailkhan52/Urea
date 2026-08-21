<?php

namespace App\Services;

use App\Models\WelcomePageSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class WelcomePageService
{
    /**
     * Storage disk for welcome page images
     */
    protected const STORAGE_DISK = 'public';

    /**
     * Logo storage path
     */
    protected const LOGO_PATH = 'welcome/logos';

    /**
     * Background image storage path
     */
    protected const BACKGROUND_PATH = 'welcome/backgrounds';

    /**
     * Get the welcome page settings
     */
    public function getSettings(): WelcomePageSetting
    {
        return WelcomePageSetting::firstOrCreate(
            [],
            [
                'company_name' => 'Fertilizer Management System',
                'hero_title' => 'Welcome to Fertilizer Management System',
                'hero_primary_button_text' => 'Get Started',
                'features_section_title' => 'Key Features',
                'workflow_section_title' => 'How It Works',
                'cta_title' => 'Ready to Get Started?',
                'cta_button_text' => 'Start Now',
                'footer_company_name' => 'Fertilizer Management System',
                'copyright_text' => '© 2024 All rights reserved.',
            ]
        );
    }

    /**
     * Update the welcome page settings
     */
    public function updateSettings(array $data): WelcomePageSetting
    {
        $settings = $this->getSettings();

        // Set the audit fields
        $data['updated_by'] = Auth::id();

        // Remove company_description if the column doesn't exist
        if (!Schema::hasColumn('welcome_page_settings', 'company_description')) {
            unset($data['company_description']);
        }

        // Update the settings
        $settings->update($data);

        return $settings;
    }

    /**
     * Handle logo upload
     */
    public function handleLogoUpload(UploadedFile $file): string
    {
        $filename = 'logo-' . time() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs(self::LOGO_PATH, $filename, self::STORAGE_DISK);
    }

    /**
     * Handle background image upload
     */
    public function handleBackgroundImageUpload(UploadedFile $file): string
    {
        $filename = 'background-' . time() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs(self::BACKGROUND_PATH, $filename, self::STORAGE_DISK);
    }

    /**
     * Delete an image file
     */
    public function deleteImage(?string $imagePath): bool
    {
        if (!$imagePath) {
            return false;
        }

        try {
            return Storage::disk(self::STORAGE_DISK)->delete($imagePath);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete old image when replacing with new one
     */
    public function replaceImage(
        UploadedFile $newFile,
        ?string $oldImagePath,
        string $imageType = 'logo'
    ): string {
        // Delete the old image
        $this->deleteImage($oldImagePath);

        // Upload the new image
        if ($imageType === 'background') {
            return $this->handleBackgroundImageUpload($newFile);
        }

        return $this->handleLogoUpload($newFile);
    }

    /**
     * Get welcome page data for frontend (with featured content only)
     */
    public function getFrontendData(): array
    {
        $settings = $this->getSettings();

        return [
            'settings' => $settings,
            'features' => $this->getActiveFeatures(),
            'workflow_steps' => $this->getActiveWorkflowSteps(),
        ];
    }

    /**
     * Get active features ordered by sort_order
     */
    public function getActiveFeatures()
    {
        return \App\Models\WelcomePageFeature::active()->ordered()->get();
    }

    /**
     * Get active workflow steps ordered by sort_order
     */
    public function getActiveWorkflowSteps()
    {
        return \App\Models\WelcomePageWorkflowStep::active()->ordered()->get();
    }
}
