<?php

namespace App\Helpers;

use App\Models\WelcomePageSetting;
use Illuminate\Support\Facades\Cache;

class CompanyHelper
{
    private const CACHE_KEY = 'company_settings';
    private const CACHE_DURATION = 3600; // 1 hour

    /**
     * Get the company settings
     */
    public static function getSettings()
    {
        try {
            return Cache::remember(self::CACHE_KEY, self::CACHE_DURATION, function () {
                return WelcomePageSetting::first() ?? self::getDefaults();
            });
        } catch (\Throwable $e) {
            return self::getDefaults();
        }
    }

    /**
     * Get default settings
     */
    private static function getDefaults()
    {
        return (object) [
            'company_name' => config('app.name', 'Fertilizer Management System'),
            'company_short_name' => 'DeraNexa',
            'company_logo' => null,
            'company_description' => null,
        ];
    }

    /**
     * Get company name
     */
    public static function getCompanyName()
    {
        try {
            $settings = self::getSettings();
            return $settings->company_name ?? config('app.name', 'Fertilizer Management System');
        } catch (\Throwable $e) {
            return config('app.name', 'Fertilizer Management System');
        }
    }

    /**
     * Get company short name
     */
    public static function getCompanyShortName()
    {
        try {
            $settings = self::getSettings();
            return $settings->company_short_name ?? self::getCompanyName();
        } catch (\Throwable $e) {
            return 'DeraNexa';
        }
    }

    /**
     * Get company logo URL
     */
    public static function getCompanyLogo()
    {
        try {
            $settings = self::getSettings();
            if ($settings->company_logo) {
                return asset('storage/' . $settings->company_logo);
            }
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get company description
     */
    public static function getCompanyDescription()
    {
        try {
            $settings = self::getSettings();
            return $settings->company_description;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Clear the company settings cache
     */
    public static function clearCache()
    {
        Cache::forget(self::CACHE_KEY);
    }
}
