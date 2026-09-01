<?php

use App\Http\Controllers\Admin\ClassSectionController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\FacultyController;
use App\Http\Controllers\Admin\LecturerController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SemesterController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::resource('faculties', FacultyController::class)->except('show');
        Route::resource('programs', ProgramController::class)->except('show');
        Route::resource('courses', CourseController::class)->except('show');
        Route::resource('semesters', SemesterController::class)->except('show');
        Route::resource('lecturers', LecturerController::class);
        Route::resource('students', StudentController::class);
        Route::resource('class-sections', ClassSectionController::class)
            ->parameters(['class-sections' => 'section']);

        // Roster management for one section.
        Route::get('class-sections/{section}/enrollments', [EnrollmentController::class, 'edit'])
            ->name('class-sections.enrollments.edit');
        Route::post('class-sections/{section}/enrollments', [EnrollmentController::class, 'store'])
            ->name('class-sections.enrollments.store');
        Route::delete('class-sections/{section}/enrollments/{enrollment}', [EnrollmentController::class, 'destroy'])
            ->name('class-sections.enrollments.destroy');

        // Account administration.
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::put('users/{user}/password', [UserController::class, 'resetPassword'])->name('users.password');
        Route::put('users/{user}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/low-attendance', [ReportController::class, 'lowAttendance'])->name('reports.low-attendance');
        Route::get('reports/class-sections/{section}', [ReportController::class, 'classSection'])->name('reports.class-section');
        Route::get('reports/students/{student}', [ReportController::class, 'student'])->name('reports.student');

        // CSV downloads of the same three reports.
        Route::get('reports/low-attendance/export', [ReportController::class, 'exportLowAttendance'])
            ->name('reports.low-attendance.export');
        Route::get('reports/class-sections/{section}/export', [ReportController::class, 'exportClassSection'])
            ->name('reports.class-section.export');
        Route::get('reports/students/{student}/export', [ReportController::class, 'exportStudent'])
            ->name('reports.student.export');
    });
