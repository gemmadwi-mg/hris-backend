<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\PositionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rute Publik (Tidak perlu login)
Route::post('/login', [AuthController::class, 'login']);

// Rute Terlindungi (Wajib login membawa Cookie Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard/summary', [DashboardController::class, 'getSummary']);

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Operasi CRUD Karyawan masuk ke sini
    Route::apiResource('employees', EmployeeController::class);

    // Rute untuk download PDF Slip Gaji
    Route::get('/employees/{employee}/payslip', [PayrollController::class, 'downloadPayslip']);

    // Taruh di dalam Route::middleware('auth:sanctum')->group(...)
    Route::get('/positions', [PositionController::class, 'index']);

    // Endpoint Absensi Karyawan
    Route::post('/attendances/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/attendances/clock-out', [AttendanceController::class, 'clockOut']);
    Route::get('/attendances/today', [AttendanceController::class, 'todayAttendances']);
    Route::get('/attendances/filtered', [AttendanceController::class, 'getAttendances']);
    Route::post('/attendances/import', [AttendanceController::class, 'importExcel']);
    // TAMBAHKAN INI:
    Route::get('/attendances/import-progress', [AttendanceController::class, 'checkImportProgress']);

    // Endpoint Cuti Karyawan
    Route::post('/leaves', [LeaveController::class, 'store']);
    Route::get('/leaves/my-requests', [LeaveController::class, 'myLeaves']);

    // --- ENDPOINT UNTUK MANAGER & HR ---
    // Melihat semua pengajuan cuti
    Route::get('/leaves/all', [LeaveController::class, 'index']);

    // Menyetujui / Menolak cuti (menggunakan parameter UUID cuti)
    Route::patch('/leaves/{leave}/status', [LeaveController::class, 'updateStatus']);

    Route::get('/leaves/{leave}/document', [LeaveController::class, 'downloadDocument']);

    Route::get('/payrolls', [PayrollController::class, 'index']);
    // Route khusus HRD / Manajer
    Route::post('/payrolls/generate', [PayrollController::class, 'generate']);

    Route::post('/payrolls/{payroll}/disburse', [PayrollController::class, 'disburse']);

    // Route khusus Karyawan
    Route::get('/my-payslips', [PayrollController::class, 'myPayslips']);

    // Route unduh PDF (Dipakai HRD maupun Karyawan)
    Route::get('/employees/{id}/payslip', [PayrollController::class, 'downloadPayslip']);

    Route::get('/notifications', function (Request $request) {
        return response()->json([
            'unread_count' => $request->user()->unreadNotifications()->count(),
            // Ambil 20 notifikasi terbaru (baik yang sudah/belum dibaca)
            'notifications' => $request->user()->notifications()->take(20)->get(),
        ]);
    });

    Route::post('/notifications/mark-read', function (Request $request) {
        // Tandai semua notifikasi milik user ini menjadi "Sudah Dibaca"
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'Sukses']);
    });

    Route::delete('/notifications/clear', function (Request $request) {
        // Hanya menghapus notifikasi yang memiliki status "Sudah Dibaca" (read_at tidak null)
        $request->user()->readNotifications()->delete();

        return response()->json(['message' => 'Notifikasi lama berhasil dihapus']);
    });
});

// Rute publik khusus untuk menerima notifikasi dari Xendit
Route::post('/webhooks/xendit', [PayrollController::class, 'xenditWebhook']);
