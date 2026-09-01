<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Shared (any signed-in user)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active'])->group(function () {
    // Send each role to the portal they belong to.
    Route::get('/', fn () => redirect()->route(auth()->user()->role->homeRoute()))->name('home');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Target of the projected QR code. Guests are bounced to login and returned
    // here afterwards by redirect()->intended().
    Route::get('checkin/{token}', [CheckInController::class, 'viaToken'])->name('checkin.token');
});

require __DIR__.'/admin.php';
require __DIR__.'/lecturer.php';
require __DIR__.'/student.php';
