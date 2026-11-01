<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\EmployeeDashboardController;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\OvertimeController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\PermitRequestController;
use App\Http\Controllers\Api\ReferenceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rute Publik (Auth)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Rute Privat (Wajib Login via Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Api\DashboardController::class, 'index']);

    // Cek Data Diri
    Route::get('/me', function (Request $request) {
        // Otomatis me-load relasi role dan perusahaannya
        return response()->json([
            'success' => true,
            'data' => $request->user()->load(['role.permissions', 'company', 'branch']),
        ]);
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // Rute Master Data / Referensi
    Route::get('/references/employee-options', [ReferenceController::class, 'getEmployeeFormOptions']);
    // Rute Master Karyawan
    Route::apiResource('employees', EmployeeController::class);
    Route::get('employee/dashboard-summary', [EmployeeDashboardController::class, 'index']);

    // ... route lain ...
    Route::apiResource('attendances', AttendanceController::class);

    Route::apiResource('leave-requests', LeaveRequestController::class);
    Route::get('/leave-balances', [LeaveRequestController::class, 'getLeaveBalances']);

    Route::apiResource('permit-requests', PermitRequestController::class);

    Route::apiResource('overtimes', OvertimeController::class);

    // Rute Preview harus di atas apiResource
    Route::get('payrolls/preview', [PayrollController::class, 'preview']);
    Route::apiResource('payrolls', PayrollController::class);
    Route::patch('/payrolls/{id}/status', [\App\Http\Controllers\Api\PayrollController::class, 'updateStatus']);
    Route::get('/payrolls/{id}/export-pdf', [\App\Http\Controllers\Api\PayrollController::class, 'exportPdf']);
    Route::post('/payrolls/generate', [\App\Http\Controllers\Api\PayrollController::class, 'generateMassal']);
    Route::post('/payrolls/{id}/disburse', [\App\Http\Controllers\Api\PayrollController::class, 'disburse']);
});
