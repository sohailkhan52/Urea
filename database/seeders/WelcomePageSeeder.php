<?php

namespace Database\Seeders;

use App\Models\WelcomePageFeature;
use App\Models\WelcomePageSetting;
use App\Models\WelcomePageWorkflowStep;
use Illuminate\Database\Seeder;

class WelcomePageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default settings (single record)
        $settings = WelcomePageSetting::firstOrCreate(
            [],
            [
                'company_name' => 'Fertilizer Management System',
                'company_short_name' => 'DeraNexa',
                'hero_title' => 'Smart Fertilizer & Inventory Management',
                'hero_description' => 'Manage inventory, warehouses, purchases, sales, customers, suppliers, credit balances, and supplier payments from one centralized system. Streamline your operations with real-time tracking and comprehensive reporting.',
                'hero_primary_button_text' => 'Login to Dashboard',
                'hero_primary_button_url' => '/login',
                'hero_secondary_button_text' => null,
                'hero_secondary_button_url' => null,
                'show_hero_section' => true,
                'features_section_title' => 'Powerful Features',
                'features_section_description' => 'Everything you need to manage your inventory efficiently and professionally',
                'workflow_section_title' => 'How The System Works',
                'workflow_section_description' => 'Follow the complete workflow from purchase to payment',
                'cta_title' => 'Ready to Manage Your Inventory Efficiently?',
                'cta_description' => 'Start managing your fertilizer inventory and business operations with our professional system today.',
                'cta_button_text' => 'Get Started Now',
                'cta_button_url' => '/login',
                'show_cta_section' => true,
                'footer_company_name' => 'Fertilizer Management System',
                'footer_description' => 'Professional inventory and supply chain management solution for fertilizer distribution.',
                'copyright_text' => '© 2024 All rights reserved.',
                'show_footer_links' => true,
            ]
        );

        // Create default features
        $features = [
            [
                'title' => 'Smart Inventory Tracking',
                'description' => 'Track stock accurately across multiple warehouses in real-time. Monitor stock levels, movements, and get alerts for low inventory.',
                'icon' => 'bi-stack',
                'sort_order' => 0,
                'is_active' => true,
            ],
            [
                'title' => 'Purchase Management',
                'description' => 'Manage supplier purchases and stock entries. Track purchase orders, confirm receipts, and maintain supplier relationships efficiently.',
                'icon' => 'bi-file-earmark-arrow-down',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Sales Management',
                'description' => 'Create and manage sales with professional invoices and payment tracking. Generate reports and maintain customer transaction history.',
                'icon' => 'bi-receipt',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Customer Credit Tracking',
                'description' => 'Manage customer credit accounts (Udhar). Track outstanding balances, payment history, and maintain secure credit relationships.',
                'icon' => 'bi-credit-card-2-front',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'Supplier Payables',
                'description' => 'Track money owed to suppliers. Manage payable accounts, payment settlements, and maintain transparent supplier relationships.',
                'icon' => 'bi-arrow-left-right',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'title' => 'Multi-Warehouse Support',
                'description' => 'Manage inventory across multiple warehouse locations. Transfer stock between warehouses and maintain centralized control.',
                'icon' => 'bi-building',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'title' => 'Reports & Analytics',
                'description' => 'View comprehensive business reports and inventory analytics. Make data-driven decisions with detailed insights and visualizations.',
                'icon' => 'bi-bar-chart-fill',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'title' => 'Secure Access Control',
                'description' => 'Role-based authentication and secure access management. Protect your data with enterprise-grade security and granular permissions.',
                'icon' => 'bi-shield-lock',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'title' => 'Stock Transfers',
                'description' => 'Manage stock transfers between warehouses with approval workflows. Track transfer status from dispatch to receipt.',
                'icon' => 'bi-arrow-repeat',
                'sort_order' => 8,
                'is_active' => true,
            ],
        ];

        foreach ($features as $feature) {
            WelcomePageFeature::firstOrCreate(
                ['title' => $feature['title']],
                $feature
            );
        }

        // Create default workflow steps
        $steps = [
            [
                'title' => 'Purchase',
                'description' => 'Create and manage purchase orders from suppliers',
                'icon' => 'bi-file-earmark-arrow-down',
                'sort_order' => 0,
                'is_active' => true,
            ],
            [
                'title' => 'Inventory',
                'description' => 'Receive and track stock in warehouses',
                'icon' => 'bi-stack',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Sales',
                'description' => 'Create and manage customer sales transactions',
                'icon' => 'bi-receipt',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Payments',
                'description' => 'Track customer credit and collect payments',
                'icon' => 'bi-credit-card-2-front',
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($steps as $step) {
            WelcomePageWorkflowStep::firstOrCreate(
                ['title' => $step['title']],
                $step
            );
        }

        $this->command->info('Welcome page data seeded successfully!');
    }
}
