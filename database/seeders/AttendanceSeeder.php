<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::all();
        $startDate = Carbon::now()->subDays(30);
        $endDate   = Carbon::now();

        if ($employees->isEmpty()) {
            return;
        }

        foreach ($employees as $employee) {
            for ($date = $startDate->clone(); $date->lte($endDate); $date->addDay()) {
                // Skip hari Minggu (Libur Pekan)
                if ($date->isSunday()) {
                    continue;
                }

                $dateStr = $date->format('Y-m-d');

                // Lewati jika data absensi tanggal tersebut sudah ada
                if (Attendance::where('employee_id', $employee->employee_id)->where('date', $dateStr)->exists()) {
                    continue;
                }

                $rand = rand(1, 100);

                // 1. HADIR TEPAT WAKTU (80%)
                if ($rand <= 80) {
                    $clockIn  = $date->copy()->setTime(7, rand(30, 59), rand(0, 59));
                    $clockOut = $date->copy()->setTime(17, rand(0, 30), rand(0, 59));

                    Attendance::create([
                        'attendance_id'        => 'AT' . $date->format('ymd') . Str::upper(Str::random(3)),
                        'employee_id'          => $employee->employee_id,
                        'branch_id'            => $employee->branch_id ?? 'B0001',
                        'date'                 => $dateStr,
                        'clock_in_time'        => $clockIn->format('Y-m-d H:i:s'),
                        'clock_out_time'       => $clockOut->format('Y-m-d H:i:s'),
                        'clock_in_status'      => 'On Time',
                        'clock_out_status'     => 'On Time',
                        'clock_in_coordinates' => '-7.311009, 112.728790',
                        'clock_out_coordinates'=> '-7.311009, 112.728790',
                        'attendance_status'    => 'Present',
                        'clock_in_photo'       => 'sample_in.jpg',
                        'clock_out_photo'      => 'sample_out.jpg',
                    ]);
                }
                // 2. HADIR TERLAMBAT (12%)
                elseif ($rand <= 92) {
                    $clockIn  = $date->copy()->setTime(8, rand(5, 45), rand(0, 59));
                    $clockOut = $date->copy()->setTime(17, rand(0, 30), rand(0, 59));

                    Attendance::create([
                        'attendance_id'        => 'AT' . $date->format('ymd') . Str::upper(Str::random(3)),
                        'employee_id'          => $employee->employee_id,
                        'branch_id'            => $employee->branch_id ?? 'B0001',
                        'date'                 => $dateStr,
                        'clock_in_time'        => $clockIn->format('Y-m-d H:i:s'),
                        'clock_out_time'       => $clockOut->format('Y-m-d H:i:s'),
                        'clock_in_status'      => 'Late',
                        'clock_out_status'     => 'On Time',
                        'clock_in_coordinates' => '-7.311009, 112.728790',
                        'clock_out_coordinates'=> '-7.311009, 112.728790',
                        'attendance_status'    => 'Present',
                        'clock_in_photo'       => 'sample_in.jpg',
                        'clock_out_photo'      => 'sample_out.jpg',
                    ]);
                }
                // 3. ALPHA / MANGKIR (8%)
                else {
                    Attendance::create([
                        'attendance_id'     => 'AT' . $date->format('ymd') . Str::upper(Str::random(3)),
                        'employee_id'       => $employee->employee_id,
                        'branch_id'         => $employee->branch_id ?? 'B0001',
                        'date'              => $dateStr,
                        'attendance_status' => 'Absent',
                    ]);
                }
            }
        }
    }
}