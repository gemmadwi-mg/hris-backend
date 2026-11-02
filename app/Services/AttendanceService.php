<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PermitRequest;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AttendanceService
{
    /**
     * Sinkronisasi Status Alpha (Lazy Evaluation)
     *
     * @param string $employeeId
     * @param string|null $branchId
     * @return void
     */
    public function syncMissingAlpha(string $employeeId, ?string $branchId = null): void
    {
        if (! $branchId) {
            $employee = Employee::where('employee_id', $employeeId)->first();
            $branchId = $employee?->branch_id ?? 'BRANCH01';
        }

        $startDate = Carbon::now()->startOfMonth();
        $yesterday = Carbon::yesterday()->endOfDay();

        for ($date = $startDate->clone(); $date->lte($yesterday); $date->addDay()) {
            // Abaikan hari Minggu (Libur Pekan)
            if ($date->isSunday()) {
                continue;
            }

            $dateStr = $date->format('Y-m-d');

            // 1. Cek apakah sudah ada data absensi
            $hasAttendance = Attendance::where('employee_id', $employeeId)
                ->where('date', $dateStr)
                ->exists();

            // 2. Cek apakah sedang Cuti (Approved)
            $hasLeave = LeaveRequest::where('employee_id', $employeeId)
                ->where('status', 'Approved')
                ->whereDate('start_date', '<=', $dateStr)
                ->whereDate('end_date', '>=', $dateStr)
                ->exists();

            // 3. Cek apakah sedang Izin (Approved)
            $hasPermit = PermitRequest::where('employee_id', $employeeId)
                ->where('status', 'Approved')
                ->whereDate('permit_date', $dateStr)
                ->exists();

            // 4. Jika bolong, buat record Alpha (Absent)
            if (! $hasAttendance && ! $hasLeave && ! $hasPermit) {
                Attendance::create([
                    'attendance_id' => 'AT'.$date->format('ymd').Str::upper(Str::random(2)),
                    'employee_id' => $employeeId,
                    'branch_id' => $branchId,
                    'date' => $dateStr,
                    'attendance_status' => 'Absent'
                ]);
            }
        }
    }
}