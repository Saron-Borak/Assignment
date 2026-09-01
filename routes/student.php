<?php

use App\Http\Controllers\CheckInController;
use App\Http\Controllers\Student\AttendanceController;
use App\Http\Controllers\Student\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('attendance/{section}', [AttendanceController::class, 'show'])->name('attendance.show');

        // Typed-code fallback for when a camera is not available.
        Route::get('check-in', [CheckInController::class, 'create'])->name('check-in.create');
        Route::post('check-in', [CheckInController::class, 'viaCode'])->name('check-in.store');
    });
