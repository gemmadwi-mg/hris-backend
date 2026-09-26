<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\PositionController;
use Illuminate\Support\Facades\Route;

// Rute Publik (Tidak perlu login)
Route::post('/login', [AuthController::class, 'login']);

// Rute Terlindungi (Wajib login membawa Cookie Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Operasi CRUD Karyawan masuk ke sini
    Route::apiResource('employees', EmployeeController::class);

    // Rute untuk download PDF Slip Gaji
    Route::get('/employees/{employee}/payslip', [PayrollController::class, 'downloadPayslip']);

    // Taruh di dalam Route::middleware('auth:sanctum')->group(...)
    Route::get('/positions', [PositionController::class, 'index']);

});
