<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    /**
     * Determine whether the user can view any expenses.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('expenses.view');
    }

    /**
     * Determine whether the user can view the expense.
     */
    public function view(User $user, Expense $expense): bool
    {
        // Super admin can view any expense
        if ($user->isSuperAdmin()) {
            return true;
        }

        // User can view if they have permission
        if (!$user->hasPermission('expenses.view')) {
            return false;
        }

        // User can view if they have access to the expense's warehouse
        return $user->canAccessWarehouse($expense->warehouse_id);
    }

    /**
     * Determine whether the user can create expenses.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('expenses.create');
    }

    /**
     * Determine whether the user can edit the expense.
     */
    public function edit(User $user, Expense $expense): bool
    {
        // Super admin can edit any expense
        if ($user->isSuperAdmin()) {
            return true;
        }

        // User must have permission
        if (!$user->hasPermission('expenses.edit')) {
            return false;
        }

        // User can edit if they have access to the expense's warehouse
        return $user->canAccessWarehouse($expense->warehouse_id);
    }

    /**
     * Determine whether the user can delete the expense.
     */
    public function delete(User $user, Expense $expense): bool
    {
        // Super admin can delete any expense
        if ($user->isSuperAdmin()) {
            return true;
        }

        // User must have permission
        if (!$user->hasPermission('expenses.delete')) {
            return false;
        }

        // User can delete if they have access to the expense's warehouse
        return $user->canAccessWarehouse($expense->warehouse_id);
    }
}
