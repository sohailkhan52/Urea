<?php

namespace App\Helpers;

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
        return self::getDefaults();
    }

    /**
     * Get default settings
     */
    private static function getDefaults()
    {
        return (object) [
            'company_name' => config('app.name', 'DeraNexa'),
            'company_short_name' => 'DeraNexa',
            'company_logo' => null,
            'favicon' => null,
            'company_description' => null,
        ];
    }

    /**
     * Get company name
     */
    public static function getCompanyName()
    {
        return config('app.name', 'DeraNexa');
    }

    /**
     * Get company short name
     */
    public static function getCompanyShortName()
    {
        return 'DeraNexa';
    }

    /**
     * Get company logo URL
     */
    public static function getCompanyLogo()
    {
        return null;
    }

    public static function getFaviconUrl()
    {
        return asset('favicon.svg');
    }

    /**
     * Get company description
     */
    public static function getCompanyDescription()
    {
        return null;
    }

    /**
     * Clear the company settings cache
     */
    public static function clearCache()
    {
        Cache::forget(self::CACHE_KEY);
    }
}
