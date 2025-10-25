<?php
// routes/web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\QrCodeController;
use App\Http\Controllers\Admin\AttendanceReportController;
use App\Http\Controllers\Admin\LeaveRequestAdminController;
use App\Http\Controllers\Superior\SuperiorDashboardController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Auth routes (Laravel Breeze/UI)
// require __DIR__.'/auth.php';

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Employee Management
    Route::resource('employees', EmployeeController::class);
    
    // QR Code
    Route::prefix('qrcode')->name('qrcode.')->group(function () {
        Route::get('/', [QrCodeController::class, 'index'])->name('index');
        Route::post('/generate', [QrCodeController::class, 'generate'])->name('generate');
        Route::get('/display/{officeLocation}', [QrCodeController::class, 'display'])->name('display');
    });
    
    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/daily', [AttendanceReportController::class, 'daily'])->name('daily');
        Route::get('/weekly', [AttendanceReportController::class, 'weekly'])->name('weekly');
        Route::get('/monthly', [AttendanceReportController::class, 'monthly'])->name('monthly');
        Route::get('/export', [AttendanceReportController::class, 'export'])->name('export');
    });
    
    // Leave Request Management
    Route::prefix('leave-requests')->name('leave-requests.')->group(function () {
        Route::get('/', [LeaveRequestAdminController::class, 'index'])->name('index');
        Route::get('/{id}', [LeaveRequestAdminController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [LeaveRequestAdminController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [LeaveRequestAdminController::class, 'reject'])->name('reject');
    });
});
// Superior routes
Route::middleware(['auth', 'role:superior'])->prefix('superior')->name('superior.')->group(function () {
    Route::get('/dashboard', [SuperiorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/team-attendance', [SuperiorDashboardController::class, 'teamAttendance'])->name('team-attendance');
    Route::get('/late-arrivals', [SuperiorDashboardController::class, 'lateArrivals'])->name('late-arrivals');
});