<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Payroll;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $currentMonth = (int) date('m');
        $currentYear = (int) date('Y');

        // 1. Kartu Metrik Utama (Bulan Ini)
        $totalEmployees = Employee::count();
        $totalNetSalary = Payroll::where('period_month', $currentMonth)
                            ->where('period_year', $currentYear)
                            ->where('status', 'Approved')
                            ->sum('net_salary');
        $totalPph21 = Payroll::where('period_month', $currentMonth)
                            ->where('period_year', $currentYear)
                            ->where('status', 'Approved')
                            ->sum('pph21_deduction');

        // 2. Data Grafik 6 Bulan Terakhir
        $chartCategories = [];
        $grossSeries = [];
        $netSeries = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $m = (int) $date->format('m');
            $y = (int) $date->format('Y');
            
            $monthName = $date->translatedFormat('M Y'); // ex: Oct 2026
            $chartCategories[] = $monthName;

            $payrolls = Payroll::where('period_month', $m)
                            ->where('period_year', $y)
                            ->where('status', 'Approved')
                            ->get();

            $grossSeries[] = $payrolls->sum('gross_salary');
            $netSeries[] = $payrolls->sum('net_salary');
        }

        return response()->json([
            'success' => true,
            'data' => [
                'metrics' => [
                    'total_employees' => $totalEmployees,
                    'total_net_salary' => $totalNetSalary,
                    'total_pph21' => $totalPph21
                ],
                'chart' => [
                    'categories' => $chartCategories,
                    'series' => [
                        [ 'name' => 'Gross Salary', 'data' => $grossSeries ],
                        [ 'name' => 'Take Home Pay', 'data' => $netSeries ]
                    ]
                ]
            ]
        ]);
    }
}