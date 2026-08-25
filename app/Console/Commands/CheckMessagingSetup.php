<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Warehouse;
use App\Models\User;
use App\Models\Conversation;
use App\Models\UserWarehouseAssignment;
use App\Services\MultiWarehouseFeatureService;

class CheckMessagingSetup extends Command
{
    protected $signature = 'check:messaging';
    protected $description = 'Check messaging system setup and diagnostics';

    public function handle()
    {
        $this->info("\n========================================");
        $this->info("MESSAGING SYSTEM DIAGNOSTIC CHECK");
        $this->info("========================================\n");

        // 1. Check warehouse count
        $activeWarehouses = Warehouse::active()->count();
        $totalWarehouses = Warehouse::count();

        $this->line("✓ WAREHOUSE STATUS");
        $this->line("  Total warehouses: {$totalWarehouses}");
        $this->line("  Active warehouses: {$activeWarehouses}");

        if ($activeWarehouses < 2) {
            $this->error("  ❌ PROBLEM: Need at least 2 active warehouses for messaging");
        } else {
            $this->info("  ✅ OK: Minimum warehouses requirement met");
        }

        $this->line("\n  Active Warehouses List:");
        Warehouse::active()->get(['id', 'name', 'status', 'manager_id'])->each(function ($w) {
            $this->line("    - ID {$w->id}: {$w->name} (Manager ID: " . ($w->manager_id ? $w->manager_id : 'None') . ")");
        });

        // 2. Check conversations
        $this->line("\n✓ CONVERSATION STATUS");
        $conversationCount = Conversation::count();
        $this->line("  Total conversations: {$conversationCount}");

        if ($conversationCount === 0) {
            $this->warn("  ⚠️  No conversations yet. They will be created when users first access Messages.");
        } else {
            $this->line("  Conversations by warehouse:");
            Conversation::with('warehouse')->get(['id', 'warehouse_id', 'created_by'])->each(function ($c) {
                $this->line("    - Conversation {$c->id}: Warehouse {$c->warehouse_id} ({$c->warehouse->name}), Created by User {$c->created_by}");
            });
        }

        // 3. Check participants
        $this->line("\n✓ CONVERSATION PARTICIPANTS");
        $participantCount = \App\Models\ConversationParticipant::count();
        $this->line("  Total participants: {$participantCount}");

        if ($participantCount > 0) {
            \App\Models\ConversationParticipant::with('user:id,name', 'conversation.warehouse:id,name')->get()->each(function ($p) {
                $this->line("    - User {$p->user->name} in Conversation {$p->conversation_id} ({$p->conversation->warehouse->name})");
            });
        }

        // 4. Check user warehouse assignments
        $this->line("\n✓ USER WAREHOUSE ASSIGNMENTS");
        $assignmentCount = UserWarehouseAssignment::where('revoked_at', null)->count();
        $this->line("  Active assignments: {$assignmentCount}");

        if ($assignmentCount > 0) {
            UserWarehouseAssignment::where('revoked_at', null)
                ->with('user:id,name', 'warehouse:id,name')
                ->get()
                ->each(function ($a) {
                    $this->line("    - User {$a->user->name} assigned to {$a->warehouse->name} ({$a->access_level})");
                });
        } else {
            $this->warn("  ⚠️  No user assignments found. Regular admins won't have conversations.");
        }

        // 5. Check users and roles
        $this->line("\n✓ USERS AND ROLES");
        User::with('roles:id,name,is_super_admin')->get(['id', 'name', 'email'])->each(function ($u) {
            $roleNames = $u->roles->pluck('name')->join(', ') ?: 'No roles';
            $isSuperAdmin = $u->roles->where('is_super_admin', true)->count() > 0;
            $badge = $isSuperAdmin ? ' [SUPER ADMIN]' : '';
            $this->line("    - {$u->name} ({$u->email}) - {$roleNames}{$badge}");
        });

        // 6. Check MultiWarehouse feature status
        $this->line("\n✓ MULTI-WAREHOUSE FEATURE STATUS");
        $service = app(MultiWarehouseFeatureService::class);
        $stats = $service->getStatistics();

        $enabled = $stats['multi_warehouse_enabled'] ? 'YES' : 'NO';
        $this->line("  Enabled: {$enabled}");
        $this->line("  Active warehouses: {$stats['active_warehouse_count']}");
        $this->line("  Minimum required: {$stats['minimum_required']}");

        if ($stats['reason']) {
            $this->line("  Reason: {$stats['reason']}");
        }

        // 7. Summary
        $this->info("\n========================================");
        $this->info("SUMMARY");
        $this->info("========================================\n");

        if ($activeWarehouses < 2) {
            $this->error("❌ MESSAGING IS DISABLED\n");
            $this->line("Reason: You need at least 2 active warehouses.");
            $this->line("Current: {$activeWarehouses} active warehouse(s)\n");
            $this->line("ACTION REQUIRED:");
            $this->line("1. Go to Admin → Warehouses");
            $this->line("2. Create a new warehouse or activate an existing one");
            $this->line("3. Make sure its status is set to 'Active'");
        } else {
            $this->info("✅ MESSAGING IS ENABLED\n");
            
            if ($conversationCount === 0) {
                $this->line("Note: Conversations haven't been created yet.");
                $this->line("They will be created automatically when users first access the Messages page.");
            } else {
                $this->info("Conversations are created and ready to use.");
            }
            
            if ($assignmentCount === 0) {
                $this->warn("\n⚠️  No warehouse assignments found.");
                $this->line("Regular admins need to be assigned to warehouses to see conversations.");
                $this->line("\nACTION: Assign users to warehouses via Admin → Warehouses → Edit → Assign Admin");
            } else {
                $this->info("\n✅ Users are assigned to warehouses.");
            }
        }

        $this->info("\n========================================\n");

        return 0;
    }
}
