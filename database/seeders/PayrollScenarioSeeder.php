<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Overtime;
use Carbon\Carbon;

class PayrollScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $employee = Employee::first();
        
        if (!$employee) {
            $this->command->info('Gagal: Tidak ada data karyawan. Buat karyawan dulu!');
            return;
        }

        $empId = $employee->employee_id;
        $branchId = $employee->branch_id; 
        
        // Data Dummy untuk memuaskan Strict Mode Database
        $dummyLocation = '-7.250445, 112.768845';
        $dummyPhoto = 'attendance_photos/dummy_selfie.jpg'; 
        
        $baseDate = Carbon::now()->startOfMonth(); 

        // --- SKENARIO 1: ALFA / MANGKIR 1x ---
        $date1 = $baseDate->copy()->addDays(1)->format('Y-m-d');
        Attendance::create([
            'attendance_id' => 'AT8881',
            'employee_id' => $empId,
            'branch_id' => $branchId,
            'date' => $date1,
            'attendance_status' => 'Absent',
            'clock_in_time' => $date1 . ' 00:00:00',  
            'clock_out_time' => $date1 . ' 00:00:00',
            'clock_in_coordinates' => $dummyLocation,
            'clock_out_coordinates' => $dummyLocation,
            'clock_in_photo' => $dummyPhoto,  // <--- Dummy Foto Selfie Masuk
            'clock_out_photo' => $dummyPhoto, // <--- Dummy Foto Selfie Pulang
        ]);

        // --- SKENARIO 2: TERLAMBAT 1 ---
        $date2 = $baseDate->copy()->addDays(2)->format('Y-m-d');
        Attendance::create([
            'attendance_id' => 'AT8882',
            'employee_id' => $empId,
            'branch_id' => $branchId,
            'date' => $date2,
            'attendance_status' => 'Present',
            'clock_in_status' => 'Late',
            'clock_in_time' => $date2 . ' 09:30:00',
            'clock_out_time' => $date2 . ' 17:00:00',
            'clock_in_coordinates' => $dummyLocation,
            'clock_out_coordinates' => $dummyLocation,
            'clock_in_photo' => $dummyPhoto,
            'clock_out_photo' => $dummyPhoto,
        ]);
        
        // --- SKENARIO 3: TERLAMBAT 2 ---
        $date3 = $baseDate->copy()->addDays(3)->format('Y-m-d');
        Attendance::create([
            'attendance_id' => 'AT8883',
            'employee_id' => $empId,
            'branch_id' => $branchId,
            'date' => $date3,
            'attendance_status' => 'Present',
            'clock_in_status' => 'Late',
            'clock_in_time' => $date3 . ' 10:00:00',
            'clock_out_time' => $date3 . ' 17:00:00',
            'clock_in_coordinates' => $dummyLocation,
            'clock_out_coordinates' => $dummyLocation,
            'clock_in_photo' => $dummyPhoto,
            'clock_out_photo' => $dummyPhoto,
        ]);

        // --- SKENARIO 4: HADIR NORMAL & LEMBUR ---
        $date4 = $baseDate->copy()->addDays(4)->format('Y-m-d');
        Attendance::create([
            'attendance_id' => 'AT8884',
            'employee_id' => $empId,
            'branch_id' => $branchId,
            'date' => $date4,
            'attendance_status' => 'Present',
            'clock_in_status' => 'On Time',
            'clock_in_time' => $date4 . ' 08:00:00',
            'clock_out_time' => $date4 . ' 20:00:00',
            'clock_in_coordinates' => $dummyLocation,
            'clock_out_coordinates' => $dummyLocation,
            'clock_in_photo' => $dummyPhoto,
            'clock_out_photo' => $dummyPhoto,
        ]);

        Overtime::create([
            'overtime_id' => 'OT8884',
            'employee_id' => $empId,
            'attendance_id' => 'AT8884',
            'overtime_date' => $date4,
            'duration' => '04:00:00',
            'total_incentive' => 350000, 
            'task_description' => 'Maintenance server darurat',
            'status' => 'Approved',
            'approver_id' => $empId,
            'processed_at' => Carbon::now(),
        ]);

        $this->command->info("✅ Skenario Payroll Berhasil Dibuat!");
        $this->command->info("👉 Uji Coba: Pilih Karyawan '{$employee->full_name}' di form Gaji bulan ini.");
    }
}