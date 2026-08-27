<?php

namespace App\Services;

use App\Models\User;
use App\Models\Warehouse;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WarehouseService
{
    /**
     * Create a warehouse with its manager/admin in a single transaction
     *
     * @param array $warehouseData
     * @param array $managerData
     * @return Warehouse
     * @throws Exception
     */
    public function createWarehouseWithManager(array $warehouseData, array $managerData): Warehouse
    {
        return DB::transaction(function () use ($warehouseData, $managerData) {
            // Create the warehouse
            $warehouse = Warehouse::create([
                'name' => $warehouseData['name'],
                'code' => $warehouseData['code'],
                'address' => $warehouseData['address'],
                'status' => $warehouseData['status'] ?? Warehouse::STATUS_ACTIVE,
                'type' => $warehouseData['type'] ?? Warehouse::TYPE_BRANCH,
                // manager_id will be set after user creation
                'manager_id' => null,
            ]);

            try {
                // Create the manager/admin user
                $admin = $this->createAdminUser($managerData, $warehouse);

                // Update warehouse to link the manager
                $warehouse->update(['manager_id' => $admin->id]);

                // Assign admin to warehouse with full access
                $admin->assignToWarehouse($warehouse, 'manage');

                // Assign admin role to user
                $admin->assignRole('admin');

                // Initialize conversation for this warehouse
                try {
                    $initService = app(\App\Services\ConversationInitializationService::class);
                    $initService->initializeWarehouseConversation($warehouse);
                } catch (Exception $e) {
                    Log::warning('Failed to initialize conversation for new warehouse: ' . $e->getMessage());
                }

                Log::info('Warehouse with manager created successfully', [
                    'warehouse_id' => $warehouse->id,
                    'warehouse_name' => $warehouse->name,
                    'manager_id' => $admin->id,
                    'manager_name' => $admin->name,
                    'created_by' => auth()->id(),
                ]);

                return $warehouse;
            } catch (Exception $e) {
                // Transaction will rollback automatically
                Log::error('Failed to create warehouse with manager', [
                    'warehouse_id' => $warehouse->id ?? null,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                throw new Exception('Failed to create warehouse with manager: ' . $e->getMessage());
            }
        });
    }

    /**
     * Create an admin user for a warehouse
     *
     * @param array $data
     * @param Warehouse $warehouse
     * @return User
     * @throws Exception
     */
    protected function createAdminUser(array $data, Warehouse $warehouse): User
    {
        $profileImagePath = null;

        try {
            // Handle profile image upload
            if (isset($data['profile_image']) && $data['profile_image']) {
                $profileImagePath = $data['profile_image']->store('warehouse-managers', 'public');
            }

            $admin = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['contact'] ?? null,
                'profile_image' => $profileImagePath,
                'password' => Hash::make($data['password']),
                'status' => User::STATUS_ACTIVE,
                'warehouse_id' => $warehouse->id,
            ]);

            Log::info('Admin user created for warehouse', [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'warehouse_id' => $warehouse->id,
            ]);

            return $admin;
        } catch (Exception $e) {
            // If image was uploaded, delete it
            if ($profileImagePath) {
                Storage::disk('public')->delete($profileImagePath);
            }

            Log::error('Failed to create admin user', [
                'warehouse_id' => $warehouse->id,
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Failed to create admin user: ' . $e->getMessage());
        }
    }

    /**
     * Update warehouse manager
     *
     * @param Warehouse $warehouse
     * @param array $newManagerData
     * @return User
     * @throws Exception
     */
    public function updateWarehouseManager(Warehouse $warehouse, array $newManagerData): User
    {
        return DB::transaction(function () use ($warehouse, $newManagerData) {
            $oldManager = $warehouse->manager;

            try {
                // Create new admin
                $newAdmin = $this->createAdminUser($newManagerData, $warehouse);

                // Update warehouse to link new manager
                $warehouse->update(['manager_id' => $newAdmin->id]);

                // Assign new admin to warehouse
                $newAdmin->assignToWarehouse($warehouse, 'manage');
                $newAdmin->assignRole('admin');

                // Revoke old manager's warehouse access
                if ($oldManager) {
                    $oldManager->revokeAllWarehouseAccess();
                    $oldManager->removeRole('admin');

                    Log::info('Old warehouse manager access revoked', [
                        'old_manager_id' => $oldManager->id,
                        'warehouse_id' => $warehouse->id,
                        'new_manager_id' => $newAdmin->id,
                    ]);
                }

                // Update conversation participants
                try {
                    $initService = app(\App\Services\ConversationInitializationService::class);
                    $initService->initializeWarehouseConversation($warehouse);
                } catch (Exception $e) {
                    Log::warning('Failed to update conversation for warehouse: ' . $e->getMessage());
                }

                Log::info('Warehouse manager updated', [
                    'warehouse_id' => $warehouse->id,
                    'old_manager_id' => $oldManager?->id,
                    'new_manager_id' => $newAdmin->id,
                    'updated_by' => auth()->id(),
                ]);

                return $newAdmin;
            } catch (Exception $e) {
                Log::error('Failed to update warehouse manager', [
                    'warehouse_id' => $warehouse->id,
                    'error' => $e->getMessage(),
                ]);

                throw new Exception('Failed to update warehouse manager: ' . $e->getMessage());
            }
        });
    }

    /**
     * Remove manager from warehouse
     *
     * @param Warehouse $warehouse
     * @return void
     */
    public function removeManager(Warehouse $warehouse): void
    {
        DB::transaction(function () use ($warehouse) {
            $manager = $warehouse->manager;

            if ($manager) {
                // Update warehouse
                $warehouse->update(['manager_id' => null]);

                // Revoke manager's access
                $manager->revokeAllWarehouseAccess();
                $manager->removeRole('admin');

                Log::info('Warehouse manager removed', [
                    'warehouse_id' => $warehouse->id,
                    'manager_id' => $manager->id,
                    'removed_by' => auth()->id(),
                ]);
            }
        });
    }

    /**
     * Check if warehouse can be deleted
     *
     * @param Warehouse $warehouse
     * @return array ['can_delete' => bool, 'reason' => string|null]
     */
    public function checkCanDelete(Warehouse $warehouse): array
    {
        // Check if warehouse has inventory
        $hasInventory = $warehouse->inventory()->where('quantity', '>', 0)->exists();
        if ($hasInventory) {
            return [
                'can_delete' => false,
                'reason' => 'This warehouse has active inventory.',
            ];
        }

        // Check if warehouse has recent transactions
        $hasPurchases = $warehouse->purchases()->where('status', '!=', 'cancelled')->exists();
        if ($hasPurchases) {
            return [
                'can_delete' => false,
                'reason' => 'This warehouse has purchases.',
            ];
        }

        $hasSales = $warehouse->sales()->where('status', '!=', 'cancelled')->exists();
        if ($hasSales) {
            return [
                'can_delete' => false,
                'reason' => 'This warehouse has sales.',
            ];
        }

        // Check if warehouse has active transfers
        $hasTransfers = $warehouse->sourceTransfers()
            ->orWhereHas('destinationWarehouse', function ($q) use ($warehouse) {
                $q->where('destination_warehouse_id', $warehouse->id);
            })
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($hasTransfers) {
            return [
                'can_delete' => false,
                'reason' => 'This warehouse has stock transfers.',
            ];
        }

        return [
            'can_delete' => true,
            'reason' => null,
        ];
    }
}
