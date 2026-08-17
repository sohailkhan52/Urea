<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle user registration.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        try {
            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => User::STATUS_ACTIVE,
            ]);

            // Assign default role (Admin)
            $user->assignRole('admin');

            // Log the user in
            Auth::login($user);

            return redirect()->route('admin.dashboard')
                ->with('success', 'Account created successfully! Welcome to Urea Inventory Management System.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error creating account: ' . $e->getMessage());
        }
    }
}
