<?php

use App\Livewire\Employee\EmployeeForm;
use App\Livewire\Employee\EmployeeList;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// QR Code Token Generation (Signed URL from Office Kiosk)
Route::get('/auth/qr-token', [App\Http\Controllers\Auth\QrTokenController::class, 'generate'])
    ->middleware('signed')
    ->name('auth.qr-token');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Employee Management Routes (Admin only)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/employees', EmployeeList::class)
        ->middleware('role:admin')
        ->name('employees.index');

    Route::get('/employees/create', EmployeeForm::class)
        ->middleware('role:admin')
        ->name('employees.create');

    Route::get('/employees/{employee}/edit', EmployeeForm::class)
        ->middleware('role:admin')
        ->name('employees.edit');

    // Office Kiosk (Admin only)
    Route::get('/office-kiosk', \App\Livewire\OfficeKiosk::class)
        ->middleware('role:admin')
        ->name('office.kiosk');

    Route::get('/attendance/monitor', \App\Livewire\Attendance\AttendanceMonitor::class)
        ->middleware('role:admin')
        ->name('attendance.monitor');

    Route::get('/attendance/history', \App\Livewire\Attendance\AttendanceHistory::class)
        ->middleware('role:admin')
        ->name('attendance.history');
});

// Employee Attendance Routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Route for employees to access their punch pad manually via sidebar
    Route::get('/employee/punch', \App\Livewire\Employee\EmployeePunchPad::class)
        ->name('employee.punch');

    // Route for scanning QR codes (signed required)
    Route::get('/attendance/punch', \App\Livewire\Employee\EmployeePunchPad::class)
        ->name('attendance.punch')
        ->middleware('signed');
});

require __DIR__.'/settings.php';
