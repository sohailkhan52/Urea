<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): View
    {
        $this->authorize('users.view');

        $query = User::with('roles');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('slug', $request->role);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $this->authorize('users.create');

        $roles = Role::orderBy('name')->get();

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('users.create');

        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['email_verified_at'] = now();

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $request->file('profile_image')->store('profile-images', 'public');
        }

        $user = User::create($data);

        // Assign roles
        if ($request->filled('roles')) {
            $user->syncRoles($request->roles);
        }

        // Log activity
        Log::info('User created', [
            'created_by' => Auth::id(),
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): View
    {
        $this->authorize('users.view');

        $user->load('roles.permissions');

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $this->authorize('users.update');

        $user->load('roles');
        $roles = Role::orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('users.update');

        $data = $request->validated();

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $data['profile_image'] = $request->file('profile_image')->store('profile-images', 'public');
        }

        // Update password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        // Sync roles
        if ($request->has('roles')) {
            $user->syncRoles($request->roles ?? []);
        }

        // Log activity
        Log::info('User updated', [
            'updated_by' => Auth::id(),
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('users.delete');

        // Prevent self-deletion
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        // Prevent deleting the last super admin
        if ($user->isSuperAdmin()) {
            $superAdminCount = User::whereHas('roles', function ($query) {
                $query->where('is_super_admin', true);
            })->count();

            if ($superAdminCount <= 1) {
                return back()->with('error', 'Cannot delete the last Super Admin.');
            }
        }

        // Delete profile image
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $email = $user->email;
        $user->delete();

        // Log activity
        Log::warning('User deleted', [
            'deleted_by' => Auth::id(),
            'user_email' => $email,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Activate a user.
     */
    public function activate(User $user): RedirectResponse
    {
        $this->authorize('users.update');

        if ($user->status === User::STATUS_ACTIVE) {
            return back()->with('info', 'User is already active.');
        }

        $user->update(['status' => User::STATUS_ACTIVE]);

        // Log activity
        Log::info('User activated', [
            'activated_by' => Auth::id(),
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return back()->with('success', 'User activated successfully.');
    }

    /**
     * Deactivate a user.
     */
    public function deactivate(User $user): RedirectResponse
    {
        $this->authorize('users.update');

        // Prevent self-deactivation
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot deactivate yourself.');
        }

        $user->update(['status' => User::STATUS_INACTIVE]);

        // Log activity
        Log::warning('User deactivated', [
            'deactivated_by' => Auth::id(),
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return back()->with('success', 'User deactivated successfully.');
    }

    /**
     * Suspend a user.
     */
    public function suspend(User $user): RedirectResponse
    {
        $this->authorize('users.update');

        // Prevent self-suspension
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot suspend yourself.');
        }

        $user->update(['status' => User::STATUS_SUSPENDED]);

        // Log activity
        Log::warning('User suspended', [
            'suspended_by' => Auth::id(),
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return back()->with('success', 'User suspended successfully.');
    }

    /**
     * Reset user password.
     */
    public function resetPassword(User $user): RedirectResponse
    {
        $this->authorize('users.update');

        // Generate random password
        $newPassword = 'Password' . rand(1000, 9999) . '!';
        $user->update(['password' => Hash::make($newPassword)]);

        // Log activity
        Log::warning('User password reset', [
            'reset_by' => Auth::id(),
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return back()->with('success', "Password reset successfully. New password: {$newPassword}");
    }
}
