<?php
// routes/web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\QrCodeController;
use App\Http\Controllers\Admin\AttendanceReportController;
use App\Http\Controllers\Admin\LeaveRequestAdminController;
use App\Http\Controllers\Superior\SuperiorDashboardController;
use App\Http\Controllers\Superior\TeamController;
use App\Http\Controllers\Superior\LateReportController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\AttendanceController;
use App\Http\Controllers\Employee\LeaveRequestController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Home route based on user role
Route::get('/home', function () {
    if (auth()->check()) {
        switch (auth()->user()->role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'superior':
                return redirect()->route('superior.dashboard');
            case 'employee':
            default:
                return redirect()->route('employee.dashboard');
        }
    }
    return redirect()->route('login');
})->middleware('auth')->name('home');

// Auth routes
require __DIR__.'/auth.php';

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Employee Management
    Route::resource('employees', EmployeeController::class);
    
    // QR Code
    Route::get('/qr-generator', [QrCodeController::class, 'index'])->name('qr-generator');
    Route::post('/qrcode/generate', [QrCodeController::class, 'generate'])->name('qr-code.generate');
    Route::get('/qrcode/display/{officeLocation}', [QrCodeController::class, 'display'])->name('qr-code.display');
    
    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [AttendanceReportController::class, 'index'])->name('index');
        Route::get('/daily', [AttendanceReportController::class, 'daily'])->name('daily');
        Route::get('/weekly', [AttendanceReportController::class, 'weekly'])->name('weekly');
        Route::get('/monthly', [AttendanceReportController::class, 'monthly'])->name('monthly');
        Route::get('/export', [AttendanceReportController::class, 'export'])->name('export');
    });
    
    // Leave Request Management
    Route::get('/leave-requests', [LeaveRequestAdminController::class, 'index'])->name('leave-requests');
    Route::post('/leave-requests/{id}/approve', [LeaveRequestAdminController::class, 'approve'])->name('leave-requests.approve');
    Route::put('/leave-requests/{id}/reject', [LeaveRequestAdminController::class, 'reject'])->name('leave-requests.reject');
});

// Superior routes
Route::middleware(['auth', 'role:superior'])->prefix('superior')->name('superior.')->group(function () {
    Route::get('/dashboard', [SuperiorDashboardController::class, 'index'])->name('dashboard');
    
    // Team management
    Route::prefix('team')->name('team.')->group(function () {
        Route::get('/', [TeamController::class, 'index'])->name('index');
        Route::get('/{id}', [TeamController::class, 'show'])->name('show');
    });
    
    // Late reports
    Route::get('/late-reports', [LateReportController::class, 'index'])->name('late-reports');
});

// Employee routes
Route::middleware(['auth', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
    
    // Attendance
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/scan', [AttendanceController::class, 'scan'])->name('scan');
        Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('check-in');
        Route::get('/history', [AttendanceController::class, 'history'])->name('history');
    });
    
    // Leave requests
    Route::resource('leave-requests', LeaveRequestController::class)->except(['show', 'edit', 'update']);
    
    // Profile
    Route::get('/profile', [EmployeeDashboardController::class, 'profile'])->name('profile');
    Route::get('/profile/edit', [EmployeeDashboardController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile', [EmployeeDashboardController::class, 'updateProfile'])->name('profile.update');
});

