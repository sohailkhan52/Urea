<?php
/**
 * Quick diagnostic script to check messaging system setup
 * Run from terminal: php check_messaging_setup.php
 */

// Bootstrap Laravel
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Warehouse;
use App\Models\User;
use App\Models\Conversation;
use App\Models\UserWarehouseAssignment;
use App\Services\MultiWarehouseFeatureService;

echo "\n========================================\n";
echo "MESSAGING SYSTEM DIAGNOSTIC CHECK\n";
echo "========================================\n\n";

// 1. Check warehouse count
$activeWarehouses = Warehouse::active()->count();
$totalWarehouses = Warehouse::count();

echo "✓ WAREHOUSE STATUS\n";
echo "  Total warehouses: {$totalWarehouses}\n";
echo "  Active warehouses: {$activeWarehouses}\n";

if ($activeWarehouses < 2) {
    echo "  ❌ PROBLEM: Need at least 2 active warehouses for messaging\n";
} else {
    echo "  ✅ OK: Minimum warehouses requirement met\n";
}

echo "\n  Active Warehouses List:\n";
Warehouse::active()->get(['id', 'name', 'status', 'manager_id'])->each(function ($w) {
    echo "    - ID {$w->id}: {$w->name} (Manager ID: " . ($w->manager_id ? $w->manager_id : 'None') . ")\n";
});

// 2. Check conversations
echo "\n✓ CONVERSATION STATUS\n";
$conversationCount = Conversation::count();
echo "  Total conversations: {$conversationCount}\n";

if ($conversationCount === 0) {
    echo "  ⚠️  No conversations yet. They will be created when users first access Messages.\n";
} else {
    echo "  Conversations by warehouse:\n";
    Conversation::with('warehouse')->get(['id', 'warehouse_id', 'created_by'])->each(function ($c) {
        echo "    - Conversation {$c->id}: Warehouse {$c->warehouse_id} ({$c->warehouse->name}), Created by User {$c->created_by}\n";
    });
}

// 3. Check participants
echo "\n✓ CONVERSATION PARTICIPANTS\n";
$participantCount = \App\Models\ConversationParticipant::count();
echo "  Total participants: {$participantCount}\n";

if ($participantCount > 0) {
    \App\Models\ConversationParticipant::with('user:id,name', 'conversation.warehouse:id,name')->get()->each(function ($p) {
        echo "    - User {$p->user->name} in Conversation {$p->conversation_id} ({$p->conversation->warehouse->name})\n";
    });
}

// 4. Check user warehouse assignments
echo "\n✓ USER WAREHOUSE ASSIGNMENTS\n";
$assignmentCount = UserWarehouseAssignment::where('revoked_at', null)->count();
echo "  Active assignments: {$assignmentCount}\n";

if ($assignmentCount > 0) {
    UserWarehouseAssignment::where('revoked_at', null)
        ->with('user:id,name', 'warehouse:id,name')
        ->get()
        ->each(function ($a) {
            echo "    - User {$a->user->name} assigned to {$a->warehouse->name} ({$a->access_level})\n";
        });
} else {
    echo "  ⚠️  No user assignments found. Regular admins won't have conversations.\n";
}

// 5. Check users and roles
echo "\n✓ USERS AND ROLES\n";
User::with('roles:id,name,is_super_admin')->get(['id', 'name', 'email', 'role'])->each(function ($u) {
    $roleNames = $u->roles->pluck('name')->join(', ') ?: 'No roles';
    $isSuperAdmin = $u->roles->where('is_super_admin', true)->count() > 0;
    echo "    - {$u->name} ({$u->email}) - {$roleNames} " . ($isSuperAdmin ? '[SUPER ADMIN]' : '') . "\n";
});

// 6. Check MultiWarehouse feature status
echo "\n✓ MULTI-WAREHOUSE FEATURE STATUS\n";
$service = app(MultiWarehouseFeatureService::class);
$stats = $service->getStatistics();

echo "  Enabled: " . ($stats['multi_warehouse_enabled'] ? 'YES' : 'NO') . "\n";
echo "  Active warehouses: {$stats['active_warehouse_count']}\n";
echo "  Minimum required: {$stats['minimum_required']}\n";

if ($stats['reason']) {
    echo "  Reason: {$stats['reason']}\n";
}

// 7. Summary
echo "\n========================================\n";
echo "SUMMARY\n";
echo "========================================\n\n";

if ($activeWarehouses < 2) {
    echo "❌ MESSAGING IS DISABLED\n\n";
    echo "Reason: You need at least 2 active warehouses.\n";
    echo "Current: {$activeWarehouses} active warehouse(s)\n\n";
    echo "ACTION REQUIRED:\n";
    echo "1. Go to Admin → Warehouses\n";
    echo "2. Create a new warehouse or activate an existing one\n";
    echo "3. Make sure its status is set to 'Active'\n";
} else {
    echo "✅ MESSAGING IS ENABLED\n\n";
    
    if ($conversationCount === 0) {
        echo "Note: Conversations haven't been created yet.\n";
        echo "They will be created automatically when users first access the Messages page.\n";
    } else {
        echo "Conversations are created and ready to use.\n";
    }
    
    if ($assignmentCount === 0) {
        echo "\n⚠️  No warehouse assignments found.\n";
        echo "Regular admins need to be assigned to warehouses to see conversations.\n";
        echo "\nACTION: Assign users to warehouses via Admin → Warehouses → Edit → Assign Admin\n";
    } else {
        echo "\n✅ Users are assigned to warehouses.\n";
    }
}

echo "\n========================================\n\n";
