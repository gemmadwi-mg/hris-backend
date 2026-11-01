<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\EmployeeLeaveBalance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeeDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $employeeId = $user->employee_id ?? $user->employee?->employee_id;
        $today = Carbon::today()->format('Y-m-d');
        $currentYear = date('Y');

        // 1. AMBIL SALDO CUTI TAHUNAN LANGSUNG DARI TABEL EmployeeLeaveBalance
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

        // 2. Cek Presensi Hari Ini
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

        // 3. Ambil 5 Riwayat Presensi Terakhir
        $recentAttendances = Attendance::where('employee_id', $employeeId)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($att) {
                $clockInTime = $att->clock_in_time ?? $att->clock_in;
                $clockOutTime = $att->clock_out_time ?? $att->clock_out;

                // 💡 FIX STATUS: Prioritaskan attendance_status (Present, Leave, Permit, Absent)
                $displayStatus = match ($att->attendance_status) {
                    'Leave' => 'Cuti',
                    'Permit' => 'Izin',
                    'Absent' => 'Alfa',
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