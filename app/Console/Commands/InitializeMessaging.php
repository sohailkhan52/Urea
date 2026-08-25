<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ConversationInitializationService;

class InitializeMessaging extends Command
{
    protected $signature = 'messaging:initialize';
    protected $description = 'Initialize warehouse conversations and participants';

    public function handle()
    {
        $this->info("\n========================================");
        $this->info("INITIALIZING MESSAGING SYSTEM");
        $this->info("========================================\n");

        try {
            $service = app(ConversationInitializationService::class);
            $count = $service->initializeAllWarehouseConversations();

            $this->info("✅ Successfully initialized {$count} conversations\n");

            // Show what was created
            $this->line("Conversations created:");
            \App\Models\Conversation::with('warehouse', 'conversationParticipants.user')->get()->each(function ($c) {
                $participantCount = $c->conversationParticipants->count();
                $this->line("  - Conversation {$c->id}: {$c->warehouse->name} ({$participantCount} participants)");
                
                $c->conversationParticipants->each(function ($p) {
                    $this->line("      • {$p->user->name}");
                });
            });

            $this->info("\n========================================");
            $this->info("INITIALIZATION COMPLETE");
            $this->info("========================================\n");

            $this->info("✅ Messaging system is now ready!");
            $this->line("Users can now access Messages and start conversations.\n");

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error initializing messaging: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
