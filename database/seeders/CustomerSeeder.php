<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample Farmers
        Customer::create([
            'customer_type' => Customer::TYPE_FARMER,
            'name' => 'Muhammad Ahmed',
            'father_name' => 'Ahmed Ali Khan',
            'cnic' => '12345-6789012-3',
            'phone' => '03001234567',
            'email' => 'muhammadahmed@example.com',
            'village' => 'Chakbeli',
            'city' => 'Faisalabad',
            'address' => 'Farm No. 5, Near Main Canal',
            'credit_limit' => 50000,
            'status' => Customer::STATUS_ACTIVE,
        ]);

        Customer::create([
            'customer_type' => Customer::TYPE_FARMER,
            'name' => 'Fatima Bibi',
            'father_name' => 'Khan Muhammad',
            'cnic' => '23456-7890123-4',
            'phone' => '03002345678',
            'email' => null,
            'village' => 'Sargodha Road',
            'city' => 'Sargodha',
            'address' => 'Farm House, Opposite School',
            'credit_limit' => 75000,
            'status' => Customer::STATUS_ACTIVE,
        ]);

        Customer::create([
            'customer_type' => Customer::TYPE_FARMER,
            'name' => 'Ali Raza',
            'father_name' => 'Raza Khan',
            'cnic' => null,
            'phone' => '03003456789',
            'email' => null,
            'village' => 'Toba Tek Singh',
            'city' => 'Toba Tek Singh',
            'address' => 'Near Graveyard, Main Road',
            'credit_limit' => 40000,
            'status' => Customer::STATUS_ACTIVE,
        ]);

        // Sample Dealers
        Customer::create([
            'customer_type' => Customer::TYPE_DEALER,
            'name' => 'Hassan & Sons Fertilizer',
            'father_name' => null,
            'cnic' => '34567-8901234-5',
            'phone' => '03214567890',
            'email' => 'hassan@fertilizershop.com',
            'village' => null,
            'city' => 'Lahore',
            'address' => 'Shop No. 5, Canal Road Market',
            'credit_limit' => 500000,
            'status' => Customer::STATUS_ACTIVE,
        ]);

        Customer::create([
            'customer_type' => Customer::TYPE_DEALER,
            'name' => 'Agricultural Solutions',
            'father_name' => null,
            'cnic' => '45678-9012345-6',
            'phone' => '03325678901',
            'email' => 'info@agrisol.com',
            'village' => null,
            'city' => 'Multan',
            'address' => 'Wholesale Market, Adda Road',
            'credit_limit' => 750000,
            'status' => Customer::STATUS_ACTIVE,
        ]);

        // Sample Retail Customers
        Customer::create([
            'customer_type' => Customer::TYPE_RETAIL_CUSTOMER,
            'name' => 'ABC Hardware & Chemicals',
            'father_name' => null,
            'cnic' => '56789-0123456-7',
            'phone' => '03436789012',
            'email' => null,
            'village' => null,
            'city' => 'Faisalabad',
            'address' => 'Street 5, Industrial Area',
            'credit_limit' => 100000,
            'status' => Customer::STATUS_ACTIVE,
        ]);

        Customer::create([
            'customer_type' => Customer::TYPE_RETAIL_CUSTOMER,
            'name' => 'Green Gardens Nursery',
            'father_name' => null,
            'cnic' => null,
            'phone' => '03547890123',
            'email' => 'greengardens@example.com',
            'village' => null,
            'city' => 'Sargodha',
            'address' => 'Outside GT Road',
            'credit_limit' => 75000,
            'status' => Customer::STATUS_ACTIVE,
        ]);

        Customer::create([
            'customer_type' => Customer::TYPE_RETAIL_CUSTOMER,
            'name' => 'Mohammad Farooq Store',
            'father_name' => 'Farooq Ahmad',
            'cnic' => '67890-1234567-8',
            'phone' => '03658901234',
            'email' => null,
            'village' => 'Chiniot',
            'city' => 'Jhang',
            'address' => 'Main Bazaar, Near Mosque',
            'credit_limit' => 50000,
            'status' => Customer::STATUS_ACTIVE,
        ]);

        // Inactive customer
        Customer::create([
            'customer_type' => Customer::TYPE_FARMER,
            'name' => 'Abdul Hameed',
            'father_name' => 'Hameed Khan',
            'cnic' => '78901-2345678-9',
            'phone' => '03769012345',
            'email' => null,
            'village' => 'Mandi Bahauddin',
            'city' => 'Mandi Bahauddin',
            'address' => 'Near Railway Station',
            'credit_limit' => 60000,
            'status' => Customer::STATUS_INACTIVE,
        ]);

        // Additional farmers for better testing
        Customer::create([
            'customer_type' => Customer::TYPE_FARMER,
            'name' => 'Nasir Ahmad',
            'father_name' => 'Ahmad Hassan',
            'cnic' => '89012-3456789-0',
            'phone' => '03870123456',
            'email' => 'nasir.ahmad@example.com',
            'village' => 'Kamoke',
            'city' => 'Gujranwala',
            'address' => 'Farm Area, Opposite Gate',
            'credit_limit' => 45000,
            'status' => Customer::STATUS_ACTIVE,
        ]);
    }
}
