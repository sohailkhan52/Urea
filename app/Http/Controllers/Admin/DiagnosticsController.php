<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DiagnosticsController extends Controller
{
    /**
     * Show diagnostics information
     */
    public function index(): View
    {
        $diagnostics = [];

        // Check database table
        $diagnostics['table_exists'] = Schema::hasTable('welcome_page_settings');

        if ($diagnostics['table_exists']) {
            $diagnostics['columns'] = Schema::getColumnListing('welcome_page_settings');
            $diagnostics['has_company_description'] = Schema::hasColumn('welcome_page_settings', 'company_description');
            
            // Get the current settings
            try {
                $diagnostics['settings'] = DB::table('welcome_page_settings')->first();
            } catch (\Exception $e) {
                $diagnostics['settings_error'] = $e->getMessage();
            }
        }

        // Check file storage
        $diagnostics['storage_link'] = file_exists(public_path('storage'));
        $diagnostics['logo_dir'] = is_dir(storage_path('app/public/welcome/logos'));

        return view('admin.diagnostics', compact('diagnostics'));
    }
}
