<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Overtime;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OperationalActivitySeeder extends Seeder
{
    public function run(): void
    {
        $staff = Employee::where('employee_id', 'E0004')->first();
        $hrd   = Employee::where('employee_id', 'E0002')->first();
        $branch = Branch::first();
        $today = Carbon::now();

        if (! $staff || ! $hrd) {
            return;
        }

        // Absensi
        $attendance = Attendance::updateOrCreate(
            ['attendance_id' => 'ATT00001'],
            [
                'employee_id' => $staff->employee_id,
                'branch_id' => $branch->branch_id,
                'date' => $today->format('Y-m-d'),
                'clock_in_time' => $today->copy()->setTime(7, 55, 0),
                'clock_out_time' => $today->copy()->setTime(17, 5, 0),
                'clock_in_status' => 'On Time',
                'clock_out_status' => 'On Time',
                'attendance_status' => 'Present',
            ]
        );

        // Lembur
        Overtime::updateOrCreate(
            ['overtime_id' => 'OT0001'],
            [
                'employee_id' => $staff->employee_id,
                'attendance_id' => $attendance->attendance_id,
                'overtime_date' => $today->format('Y-m-d'),
                'duration' => '02:00:00',
                'total_incentive' => 150000,
                'task_description' => 'Fixing bug urgent di server production',
                'status' => 'Approved',
                'approver_id' => $hrd->employee_id,
                'processed_at' => now(),
            ]
        );

        // Cuti
        LeaveRequest::updateOrCreate(
            ['leave_request_id' => 'LR0001'],
            [
                'employee_id' => $staff->employee_id,
                'leave_type' => 'Annual Leave',
                'start_date' => $today->copy()->addDays(5)->format('Y-m-d'),
                'end_date' => $today->copy()->addDays(6)->format('Y-m-d'),
                'reason' => 'Acara keluarga',
                'status' => 'Pending',
            ]
        );

        // Payroll
        Payroll::updateOrCreate(
            ['payroll_id' => 'PAY'.date('Ym').'001'],
            [
                'employee_id' => $staff->employee_id,
                'month_year' => $today->copy()->subMonth()->format('Y-m'),
                'basic_salary' => $staff->basic_salary,
                'allowance_total' => 1000000,
                'deduction_total' => 200000,
                'net_salary' => $staff->basic_salary + 800000,
                'payment_status' => 'Paid',
            ]
        );
    }
}