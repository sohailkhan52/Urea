<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Urea',
                'slug' => 'urea',
                'description' => 'Urea is a nitrogen fertilizer containing 46% nitrogen. Widely used for crop production.',
                'status' => 'active',
            ],
            [
                'name' => 'DAP',
                'slug' => 'dap',
                'description' => 'Diammonium Phosphate (DAP) is a phosphate fertilizer with 18% nitrogen and 46% phosphorus.',
                'status' => 'active',
            ],
            [
                'name' => 'NPK',
                'slug' => 'npk',
                'description' => 'NPK fertilizers contain nitrogen, phosphorus, and potassium in various ratios.',
                'status' => 'active',
            ],
            [
                'name' => 'SOP',
                'slug' => 'sop',
                'description' => 'Sulphate of Potash (SOP) is a premium potassium fertilizer.',
                'status' => 'active',
            ],
            [
                'name' => 'MOP',
                'slug' => 'mop',
                'description' => 'Muriate of Potash (MOP) is a potassium chloride fertilizer.',
                'status' => 'active',
            ],
            [
                'name' => 'Micronutrients',
                'slug' => 'micronutrients',
                'description' => 'Micronutrient fertilizers contain zinc, boron, iron, and other essential elements.',
                'status' => 'active',
            ],
            [
                'name' => 'Other Fertilizers',
                'slug' => 'other-fertilizers',
                'description' => 'Other specialized fertilizers and soil amendments.',
                'status' => 'active',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        $this->command->info('Categories seeded successfully!');
    }
}
