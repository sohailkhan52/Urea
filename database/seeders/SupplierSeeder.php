<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Agri Supplies Pakistan',
                'company_name' => 'Agri Supplies (Pvt) Ltd',
                'contact_person' => 'Ahmed Khan',
                'phone' => '+92-51-1234567',
                'email' => 'contact@agrisupplies.pk',
                'address' => 'Plot 45, Industrial Area, I-9',
                'city' => 'Islamabad',
                'ntn' => 'AGS-2345678-9',
                'status' => 'active',
            ],
            [
                'name' => 'Fertilizer Traders',
                'company_name' => 'Fertilizer Traders Co.',
                'contact_person' => 'Muhammad Ali',
                'phone' => '+92-21-9876543',
                'email' => 'info@ferttrade.pk',
                'address' => 'Block 12, Landhi Industrial Area',
                'city' => 'Karachi',
                'ntn' => 'FTC-3456789-1',
                'status' => 'active',
            ],
            [
                'name' => 'Green Agriculture',
                'company_name' => 'Green Agriculture Solutions',
                'contact_person' => 'Fatima Malik',
                'phone' => '+92-42-8765432',
                'email' => 'sales@greenagri.pk',
                'address' => 'Ferozepur Road, Near Toll Plaza',
                'city' => 'Lahore',
                'ntn' => 'GAS-4567890-2',
                'status' => 'active',
            ],
            [
                'name' => 'Punjab Fertilizers',
                'company_name' => null,
                'contact_person' => 'Hassan Ahmed',
                'phone' => '+92-41-2345678',
                'email' => 'punjabfert@example.com',
                'address' => 'Susan Road, Jinnah Colony',
                'city' => 'Faisalabad',
                'ntn' => 'PF-5678901-3',
                'status' => 'active',
            ],
            [
                'name' => 'Multan Agro Trading',
                'company_name' => 'Multan Agro Trading House',
                'contact_person' => 'Bilal Hussain',
                'phone' => '+92-61-3456789',
                'email' => 'info@multanagro.pk',
                'address' => 'Bosan Road, Industrial Estate',
                'city' => 'Multan',
                'ntn' => 'MAT-6789012-4',
                'status' => 'active',
            ],
            [
                'name' => 'Sindh Commodities',
                'company_name' => 'Sindh Commodities International',
                'contact_person' => 'Imran Sheikh',
                'phone' => '+92-22-4567890',
                'email' => 'contact@sindhcomm.pk',
                'address' => 'Main Hyderabad Road, Unit 23',
                'city' => 'Hyderabad',
                'ntn' => 'SCI-7890123-5',
                'status' => 'active',
            ],
            [
                'name' => 'KPK Agri Mart',
                'company_name' => null,
                'contact_person' => 'Zahid Khan',
                'phone' => '+92-91-5678901',
                'email' => null,
                'address' => 'GT Road, Industrial Zone',
                'city' => 'Peshawar',
                'ntn' => null,
                'status' => 'active',
            ],
            [
                'name' => 'Balochistan Traders',
                'company_name' => 'Balochistan Traders & Suppliers',
                'contact_person' => 'Rashid Mengal',
                'phone' => '+92-81-6789012',
                'email' => 'info@balochistantraders.pk',
                'address' => 'Jinnah Road, Commercial Area',
                'city' => 'Quetta',
                'ntn' => 'BTS-8901234-6',
                'status' => 'active',
            ],
            [
                'name' => 'Northern Agriculture',
                'company_name' => 'Northern Agriculture Supplies',
                'contact_person' => 'Asif Malik',
                'phone' => '+92-992-234567',
                'email' => 'northern.agri@example.com',
                'address' => 'Mansehra Road, Sector 5',
                'city' => 'Abbottabad',
                'ntn' => 'NAS-9012345-7',
                'status' => 'active',
            ],
            [
                'name' => 'Legacy Fertilizers',
                'company_name' => 'Legacy Fertilizers (Inactive)',
                'contact_person' => 'Old Contact',
                'phone' => '+92-300-1111111',
                'email' => 'legacy@old.com',
                'address' => 'Old Industrial Area',
                'city' => 'Karachi',
                'ntn' => 'LF-0123456-8',
                'status' => 'inactive',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
