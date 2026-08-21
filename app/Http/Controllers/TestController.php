<?php

namespace App\Http\Controllers;

use App\Models\WelcomePageSetting;
use Illuminate\Support\Facades\Schema;

class TestController extends Controller
{
    public function testWelcomePage()
    {
        $output = [];
        
        // Check table structure
        $output['table_exists'] = Schema::hasTable('welcome_page_settings');
        $output['columns'] = Schema::getColumnListing('welcome_page_settings');
        $output['has_description_column'] = Schema::hasColumn('welcome_page_settings', 'company_description');
        
        // Check model
        $settings = WelcomePageSetting::first();
        $output['current_settings'] = [
            'id' => $settings->id ?? null,
            'company_name' => $settings->company_name ?? null,
            'company_description' => $settings->company_description ?? null,
            'company_logo' => $settings->company_logo ?? null,
        ];
        
        $output['model_fillable'] = $settings->getFillable();
        
        return response()->json($output, 200);
    }
}
