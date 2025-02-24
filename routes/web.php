<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\VerificationController;
use Illuminate\Auth\Events\Verified;
use App\Models\User;

Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Email Verification Routes
// Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verifyEmail'])->name('verification.verify');
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    // Find user by ID
    $user = User::find($id);

    // Check if user exists
    if (!$user) {
        return redirect('/login')->with('error', 'User not found.');
    }

    // Check if the hash is valid
    if (!hash_equals(sha1($user->getEmailForVerification()), $hash)) {
        return redirect('/login')->with('error', 'Invalid verification link.');
    }

    // If user already verified, redirect
    if ($user->hasVerifiedEmail()) {
        return redirect('/dashboard')->with('success', 'Email already verified.');
    }

    // Mark email as verified
    $user->markEmailAsVerified();
    event(new Verified($user));

    return redirect('/dashboard')->with('success', 'Email successfully verified.');
})->middleware(['signed'])->name('verification.verify');
Route::post('/email/resend', [VerificationController::class, 'resendVerification'])->name('verification.resend');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [BookingController::class, 'showForm'])->name('dashboard'); // Acts as the dashboard
    Route::get('/bookings/create', [BookingController::class, 'showForm'])->name('bookings.create'); // Define this route
    Route::post('/bookings/store', [BookingController::class, 'store'])->name('bookings.store');
});
