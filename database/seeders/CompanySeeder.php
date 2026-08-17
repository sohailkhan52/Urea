<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            [
                'name' => 'Fauji Fertilizer Company Limited',
                'code' => 'FFC',
                'contact_person' => 'Muhammad Ahmed',
                'phone' => '+92-51-111-111-332',
                'email' => 'info@ffc.com.pk',
                'website' => 'https://www.ffc.com.pk',
                'address' => 'PIDC House, Main Diplomatic Enclave, G-5, Islamabad, Pakistan',
                'status' => 'active',
            ],
            [
                'name' => 'Engro Fertilizers Limited',
                'code' => 'ENGRO',
                'contact_person' => 'Ali Hassan',
                'phone' => '+92-21-111-111-774',
                'email' => 'info@engro.com',
                'website' => 'https://www.engro.com',
                'address' => 'The Harbour Front Building, HC-3, Marine Drive, Block-4, Clifton, Karachi',
                'status' => 'active',
            ],
            [
                'name' => 'Fatima Fertilizer Company Limited',
                'code' => 'FATIMA',
                'contact_person' => 'Sana Khan',
                'phone' => '+92-41-111-222-928',
                'email' => 'info@fatima-group.com',
                'website' => 'https://www.fatima-group.com',
                'address' => 'Sheikhupura Road, Faisalabad, Pakistan',
                'status' => 'active',
            ],
            [
                'name' => 'Agritech Limited',
                'code' => 'AGRITECH',
                'contact_person' => 'Zain Malik',
                'phone' => '+92-42-111-247-483',
                'email' => 'contact@agritech.pk',
                'website' => 'https://www.agritech.pk',
                'address' => 'Main Boulevard, Gulberg III, Lahore, Pakistan',
                'status' => 'active',
            ],
            [
                'name' => 'National Fertilizer Corporation',
                'code' => 'NFC',
                'contact_person' => 'Ayesha Bhatti',
                'phone' => '+92-51-920-5678',
                'email' => 'info@nfc.gov.pk',
                'website' => 'https://www.nfc.gov.pk',
                'address' => 'Blue Area, F-7, Islamabad, Pakistan',
                'status' => 'active',
            ],
            [
                'name' => 'Pak-Arab Fertilizers Limited',
                'code' => 'PAKARA',
                'contact_person' => 'Omar Farooq',
                'phone' => '+92-61-9270201',
                'email' => 'info@pakarab.com',
                'website' => 'https://www.pakarab.com',
                'address' => 'Khanpur Road, Multan, Pakistan',
                'status' => 'active',
            ],
            [
                'name' => 'Dawood Hercules Corporation',
                'code' => 'DHC',
                'contact_person' => 'Fatima Dawood',
                'phone' => '+92-21-111-329-663',
                'email' => 'info@dhcl.com',
                'website' => 'https://www.dhcl.com',
                'address' => 'Dawood Center, M.T. Khan Road, Karachi, Pakistan',
                'status' => 'inactive',
            ],
        ];

        foreach ($companies as $company) {
            Company::create($company);
        }

        $this->command->info('Companies seeded successfully!');
    }
}
