<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Profile;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if ($user && is_string($user->getAuthPassword()) && Hash::check($credentials['password'], $user->getAuthPassword())) {
            if (!$user->is_active) {
                return back()->withErrors([
                    'email' => 'Your account has been deactivated.',
                ])->onlyInput('email');
            }

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['nullable', 'in:user,admin']
        ]);

        try {
            $user = DB::transaction(function () use ($request) {
                $displayName = $request->input('name', $request->full_name);
                $passwordColumn = Schema::hasColumn('users', 'password') ? 'password' : 'password_hash';

                $user = User::create([
                    'name' => $displayName,
                    'email' => $request->email,
                    'role' => $request->input('role', 'user'),
                    'is_active' => true,
                ] + [$passwordColumn => Hash::make($request->password)]);

                Profile::create([
                    'user_id' => $user->id,
                    'full_name' => $displayName,
                ]);

                // Create wallet with starting credits - use firstOrCreate for idempotency
                Wallet::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'balance' => 10.00,
                        'total_earned' => 10.00,
                        'total_spent' => 0.00,
                    ]
                );

                return $user;
            });

            Auth::login($user);

            return redirect('/');
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['error' => 'Registration failed. Please try again.'])->withInput();
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
