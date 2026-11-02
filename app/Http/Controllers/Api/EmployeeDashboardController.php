<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\EmployeeLeaveBalance;
use App\Services\AttendanceService; // 💡 1. Import AttendanceService
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeeDashboardController extends Controller
{
    // 💡 2. Inject via Constructor
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $employeeId = $user->employee_id ?? $user->employee?->employee_id;
        $branchId = $user->branch_id ?? $user->employee?->branch_id;
        $today = Carbon::today()->format('Y-m-d');
        $currentYear = date('Y');

        if (! $employeeId) {
            return response()->json([
                'success' => false,
                'message' => 'Data Karyawan (employee_id) tidak ditemukan pada akun ini.',
            ], 422);
        }

        // 💡 3. Panggil method dari Service Class
        $this->attendanceService->syncMissingAlpha($employeeId, $branchId);

        // Ambil Saldo Cuti
        $leaveBalance = EmployeeLeaveBalance::firstOrCreate(
            [
                'employee_id' => $employeeId,
                'leave_type' => 'Cuti Tahunan',
                'year' => $currentYear,
            ],
            [
                'quota' => 12,
                'used' => 0,
            ]
        );

        $totalQuota = (int) $leaveBalance->quota;
        $usedDays = (int) $leaveBalance->used;
        $remainingQuota = max(0, $totalQuota - $usedDays);

        // Cek Presensi Hari Ini
        $attendanceToday = Attendance::where('employee_id', $employeeId)
            ->where('date', $today)
            ->first();

        $todayAttendanceData = null;
        if ($attendanceToday) {
            $clockInTime = $attendanceToday->clock_in_time ?? $attendanceToday->clock_in;
            $clockOutTime = $attendanceToday->clock_out_time ?? $attendanceToday->clock_out;

            $todayAttendanceData = [
                'clock_in' => $clockInTime ? Carbon::parse($clockInTime)->format('H:i').' WIB' : null,
                'clock_out' => $clockOutTime ? Carbon::parse($clockOutTime)->format('H:i').' WIB' : null,
                'status' => $attendanceToday->attendance_status,
            ];
        }

        // Ambil 5 Riwayat Presensi Terakhir
        $recentAttendances = Attendance::where('employee_id', $employeeId)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($att) {
                $clockInTime = $att->clock_in_time ?? $att->clock_in;
                $clockOutTime = $att->clock_out_time ?? $att->clock_out;

                $displayStatus = match ($att->attendance_status) {
                    'Leave' => 'Cuti',
                    'Permit' => 'Izin',
                    'Absent', 'Alpha' => 'Alfa',
                    'Present' => $att->clock_in_status ?? 'Hadir',
                    default => $att->attendance_status ?? 'Hadir'
                };

                return [
                    'id' => $att->attendance_id ?? $att->id,
                    'clock_in' => $clockInTime ? Carbon::parse($clockInTime)->format('H:i').' WIB' : null,
                    'clock_out' => $clockOutTime ? Carbon::parse($clockOutTime)->format('H:i').' WIB' : null,
                    'date_formatted' => Carbon::parse($att->date)->locale('id')->isoFormat('D MMM YYYY'),
                    'status' => $displayStatus,
                    'notes' => $att->notes ?? ($clockOutTime ? 'Presensi Selesai' : ($clockInTime ? 'Presensi Masuk' : 'Cuti/Izin')),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'today_attendance' => $todayAttendanceData,
                'leave_summary' => [
                    'remaining' => (int) $remainingQuota,
                    'total' => (int) $totalQuota,
                ],
                'recent_attendances' => $recentAttendances,
            ],
        ]);
    }
}