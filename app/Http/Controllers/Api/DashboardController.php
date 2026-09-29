<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Payroll;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getSummary()
    {
        $hariIni = Carbon::today()->format('Y-m-d');
        $bulanIni = date('m');
        $tahunIni = date('Y');

        // 1. Ringkasan Kartu (Cards)
        $totalEmployees = Employee::count();

        $hadirHariIni = Attendance::where('tanggal', $hariIni)->where('status', 'Hadir')->count();

        $pendingLeaves = Leave::where('status', 'Pending')->count();

        $payrollExpense = Payroll::where('bulan', $bulanIni)
            ->where('tahun', $tahunIni)
            ->sum('total_gaji_bersih');

        // 2. Data untuk Grafik (Cuti Bulan Ini)
        $leavesThisMonth = Leave::whereMonth('created_at', $bulanIni)->get();
        $approvedLeaves = $leavesThisMonth->where('status', 'Approved')->count();
        $rejectedLeaves = $leavesThisMonth->where('status', 'Rejected')->count();
        $pendingLeavesBulanIni = $leavesThisMonth->where('status', 'Pending')->count();

        // 3. Data untuk Grafik (Tren Kehadiran 7 Hari Terakhir)
        $attendanceTrend = [];
        $categories = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $categories[] = $date->format('d M');
            $attendanceTrend[] = Attendance::where('tanggal', $date->format('Y-m-d'))
                ->where('status', 'Hadir')
                ->count();
        }

        return response()->json([
            'cards' => [
                'total_employees' => $totalEmployees,
                'present_today' => $hadirHariIni,
                'pending_leaves' => $pendingLeaves,
                'payroll_expense' => $payrollExpense,
            ],
            'charts' => [
                'leave_distribution' => [$approvedLeaves, $pendingLeavesBulanIni, $rejectedLeaves],
                'attendance_trend' => [
                    'categories' => $categories,
                    'data' => $attendanceTrend,
                ],
            ],
        ]);
    }
}
