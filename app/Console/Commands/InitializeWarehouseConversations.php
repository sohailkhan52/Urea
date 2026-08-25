<?php

namespace App\Console\Commands;

use App\Models\Warehouse;
use App\Services\ConversationInitializationService;
use Illuminate\Console\Command;

class InitializeWarehouseConversations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat:init-conversations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize warehouse conversations for the chat system';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Initializing warehouse conversations...');

        $service = app(ConversationInitializationService::class);

        // Initialize all warehouses
        $count = $service->initializeAllWarehouseConversations();

        if ($count > 0) {
            $this->info("✅ Successfully initialized conversations for $count warehouse(s)");
        } else {
            $this->warn('No active warehouses found to initialize');
        }

        // Show statistics
        $totalConversations = \App\Models\Conversation::count();
        $totalMessages = \App\Models\Message::count();
        $totalParticipants = \App\Models\ConversationParticipant::count();

        $this->newLine();
        $this->info('Chat System Statistics:');
        $this->line("  Conversations: $totalConversations");
        $this->line("  Messages: $totalMessages");
        $this->line("  Participants: $totalParticipants");

        return Command::SUCCESS;
    }
}
