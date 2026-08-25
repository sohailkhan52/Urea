<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use App\Services\ConversationInitializationService;
use Illuminate\Database\Seeder;

class ConversationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $service = app(ConversationInitializationService::class);

        // Initialize conversations for all active warehouses
        $count = $service->initializeAllWarehouseConversations();

        $this->command->info("Initialized conversations for $count warehouse(s).");
    }
}
