<?php

use App\Http\Controllers\Lecturer\ClassSectionController;
use App\Http\Controllers\Lecturer\DashboardController;
use App\Http\Controllers\Lecturer\QrDisplayController;
use App\Http\Controllers\Lecturer\SessionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', 'role:lecturer'])
    ->prefix('lecturer')
    ->name('lecturer.')
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::get('classes', [ClassSectionController::class, 'index'])->name('classes.index');
        Route::get('classes/{section}', [ClassSectionController::class, 'show'])->name('classes.show');
        Route::get('classes/{section}/report', [ClassSectionController::class, 'report'])->name('classes.report');
        Route::get('classes/{section}/report/export', [ClassSectionController::class, 'exportReport'])->name('classes.report.export');

        // Bulk-create sessions for the semester from the section timetable.
        Route::get('classes/{section}/sessions/create', [SessionController::class, 'create'])->name('sessions.create');
        Route::post('classes/{section}/sessions', [SessionController::class, 'store'])->name('sessions.store');
        Route::post('classes/{section}/sessions/generate', [SessionController::class, 'generate'])->name('sessions.generate');

        Route::get('sessions', [SessionController::class, 'index'])->name('sessions.index');
        Route::get('sessions/{session}', [SessionController::class, 'show'])->name('sessions.show');
        Route::get('sessions/{session}/mark', [SessionController::class, 'mark'])->name('sessions.mark');
        Route::put('sessions/{session}/mark', [SessionController::class, 'storeMarks'])->name('sessions.mark.store');
        Route::put('sessions/{session}/open', [SessionController::class, 'open'])->name('sessions.open');
        Route::put('sessions/{session}/close', [SessionController::class, 'close'])->name('sessions.close');
        Route::delete('sessions/{session}', [SessionController::class, 'destroy'])->name('sessions.destroy');

        // Projected QR kiosk plus the polling endpoint that rotates the token.
        Route::get('sessions/{session}/qr', [QrDisplayController::class, 'show'])->name('sessions.qr');
        Route::get('sessions/{session}/qr/refresh', [QrDisplayController::class, 'refresh'])->name('sessions.qr.refresh');
    });
