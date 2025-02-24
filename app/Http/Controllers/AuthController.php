<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Jobs\SendVerificationEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    /**
     * Show Login Page
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Show Register Page
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Handle Registration
     */
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->first_name.' '.$request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user = $user->fresh();

        dispatch(new SendVerificationEmail($user->id)); // Queue email verification

        return redirect()->route('login')->with('success', 'Registration successful! Please check your email to verify.');
    }

    /**
     * Handle Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && !$user->hasVerifiedEmail()) {
            return back()->withErrors(['email' => 'You need to verify your email before logging in.']);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return redirect()->route('dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    /**
     * Handle Logout
     */
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
