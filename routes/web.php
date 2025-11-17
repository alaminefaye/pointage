<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\EmployeeAuthController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\EmployeeRestDayController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BadgeController;

// Admin Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Employee Authentication Routes
Route::get('/employee/login', [EmployeeAuthController::class, 'showLoginForm'])->name('employee.login');
Route::post('/employee/login', [EmployeeAuthController::class, 'login']);
Route::post('/employee/logout', [EmployeeAuthController::class, 'logout'])->name('employee.logout');

// Employee Routes (for employees to check in/out)
Route::middleware(['employee.session'])->group(function () {
    Route::get('/employee/dashboard', [EmployeeAuthController::class, 'dashboard'])->name('employee.dashboard');
    Route::get('/employee/qr-scanner', function () {
        return view('employee-auth.qr-scanner');
    })->name('employee.qr-scanner');
    Route::get('/employee/attendance-history', [EmployeeAuthController::class, 'attendanceHistory'])->name('employee.attendance-history');
});

// Attendance routes accessible by both employees and admins
Route::middleware(['auth.employee.or.admin'])->group(function () {
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::get('/attendance/today-status', [AttendanceController::class, 'getTodayStatus'])->name('attendance.today-status');
});

// Badge scanning route (public route for badge QR code scanning)
Route::post('/attendance/badge-scan', [AttendanceController::class, 'badgeScan'])->name('attendance.badge-scan');

// Admin Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Sites
    Route::resource('sites', SiteController::class);
    Route::get('/sites/{site}/download-qr', [SiteController::class, 'downloadQrCode'])->name('sites.download-qr');
    
    // Departments
    Route::resource('departments', DepartmentController::class);
    
    // Employees
    Route::resource('employees', EmployeeController::class);
    
    // Attendance
    Route::get('/attendance/today', [AttendanceController::class, 'today'])->name('attendance.today');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    // Note: check-in, check-out, and today-status routes are also available for employees below
    
    // QR Codes
    Route::get('/qr-code/current', [QrCodeController::class, 'getCurrent'])->name('qr-code.current');
    Route::get('/qr-code/all-current', [QrCodeController::class, 'getAllCurrent'])->name('qr-code.all-current');
    Route::post('/qr-code/generate', [QrCodeController::class, 'generate'])->name('qr-code.generate');
    Route::post('/qr-code/generate-all', [QrCodeController::class, 'generateAll'])->name('qr-code.generate-all');
    
    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');
    Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export-excel');
    
    // Alerts
    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::post('/alerts/{alert}/read', [AlertController::class, 'markAsRead'])->name('alerts.mark-read');
    Route::post('/alerts/read-all', [AlertController::class, 'markAllAsRead'])->name('alerts.read-all');
    Route::get('/alerts/unread-count', [AlertController::class, 'unreadCount'])->name('alerts.unread-count');
    
    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/geolocation', [SettingsController::class, 'updateGeolocation'])->name('settings.update-geolocation');
    Route::post('/settings/overtime-threshold', [SettingsController::class, 'updateOvertimeThreshold'])->name('settings.update-overtime-threshold');
    
    // Overtime - Les routes personnalisées doivent être avant la resource pour éviter les conflits
    Route::get('/overtime/accounting', [OvertimeController::class, 'accounting'])->name('overtime.accounting');
    Route::get('/overtime/report', [OvertimeController::class, 'report'])->name('overtime.report');
    Route::get('/overtime/export-pdf', [OvertimeController::class, 'exportPdf'])->name('overtime.export-pdf');
    Route::resource('overtime', OvertimeController::class)->except(['show']);
    
    // Rest Days
    Route::get('/rest-days', [EmployeeRestDayController::class, 'index'])->name('rest-days.index');
    Route::post('/rest-days', [EmployeeRestDayController::class, 'store'])->name('rest-days.store');
    Route::delete('/rest-days/{restDay}', [EmployeeRestDayController::class, 'destroy'])->name('rest-days.destroy');
    
    // Users (Administration)
    Route::resource('users', UserController::class);
    
    // Badges
    Route::resource('badges', BadgeController::class);
    Route::get('/badges/{badge}/download-qr', [BadgeController::class, 'downloadQrCode'])->name('badges.download-qr');
    Route::post('/badges/{badge}/toggle-status', [BadgeController::class, 'toggleStatus'])->name('badges.toggle-status');
});

// API Routes for employee attendance (can be called from mobile app)
Route::prefix('api/employee')->group(function () {
    Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('api.attendance.check-in');
    Route::post('/check-out', [AttendanceController::class, 'checkOut'])->name('api.attendance.check-out');
    Route::get('/today-status', [AttendanceController::class, 'getTodayStatus'])->name('api.attendance.today-status');
    Route::get('/qr-code/current', [QrCodeController::class, 'getCurrent'])->name('api.qr-code.current');
});
