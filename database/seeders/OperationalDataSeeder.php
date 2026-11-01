<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OperationalDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Data Master Roles
        $roleAdmin = Role::updateOrCreate(['role_id' => 'R01'], ['role_name' => 'System Administrator']);
        $roleHrd = Role::updateOrCreate(['role_id' => 'R02'], ['role_name' => 'HR Manager']);
        $roleFinance = Role::updateOrCreate(['role_id' => 'R03'], ['role_name' => 'Finance']);
        $roleEmployee = Role::updateOrCreate(['role_id' => 'R04'], ['role_name' => 'Employee']);

        // 2. Buat Data Master Perusahaan & Cabang
        $company = Company::updateOrCreate(
            ['company_id' => 'C0001'],
            [
                'company_name' => 'PT Teknologi Nusantara',
                'email'        => 'info@teknologinusantara.com',
                'phone_number' => '03187654321',
                'clock_in_time'  => '08:00:00',
                'clock_out_time' => '17:00:00',
            ]
        );

        $branch = Branch::updateOrCreate(
            ['branch_id' => 'B0001'],
            [
                'company_id' => $company->company_id,
                'branch_name' => 'Kantor Pusat Surabaya',
                'address' => 'Jl. Ketintang, Surabaya',
                'latitude' => -7.311009,
                'longitude' => 112.728790,
                'radius_meters' => 50,
            ]
        );

        $defaultPassword = Hash::make('password123'); // Password seragam untuk testing

        // 3a. Buat Akun ADMIN (Gemma)
        $admin = Employee::updateOrCreate(
            ['employee_id' => 'E0001'],
            [
                'role_id' => $roleAdmin->role_id,
                'company_id' => $company->company_id,
                'branch_id' => $branch->branch_id,
                'ptkp_group_id' => 'G01', // TK0
                'nik' => '3578012345678001',
                'full_name' => 'Gemma Dwi Prasetya',
                'date_of_birth' => '2000-01-01',
                'gender' => 'Male',
                'address' => 'Surabaya',
                'phone_number' => '081234567890',
                'email' => 'admin@example.com',
                'password' => $defaultPassword,
                'job_title' => 'System Administrator',
                'department' => 'Information Technology',
                'employment_status' => 'Permanent',
                'join_date' => '2025-01-01',
                'basic_salary' => 12000000,
                'position_allowance' => 2000000,
                'meal_allowance' => 500000,
                'transport_allowance' => 500000,
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_holder_name' => 'Gemma Dwi Prasetya',
            ]
        );

        // 3b. Buat Akun MANAGER HRD
        $hrd = Employee::updateOrCreate(
            ['employee_id' => 'E0002'],
            [
                'role_id' => $roleHrd->role_id,
                'company_id' => $company->company_id,
                'branch_id' => $branch->branch_id,
                'ptkp_group_id' => 'G05', // K0
                'nik' => '3578012345678002',
                'full_name' => 'Anita Larasati',
                'date_of_birth' => '1990-05-15',
                'gender' => 'Female',
                'address' => 'Sidoarjo',
                'phone_number' => '082233445566',
                'email' => 'hrd@example.com',
                'password' => $defaultPassword,
                'job_title' => 'HR Manager',
                'department' => 'Information Technology',
                'employment_status' => 'Permanent',
                'join_date' => '2022-03-01',
                'basic_salary' => 15000000,
                'position_allowance' => 3000000,
                'meal_allowance' => 1000000,
                'transport_allowance' => 1000000,
                'bank_name' => 'Mandiri',
                'account_number' => '0987654321',
                'account_holder_name' => 'Anita Larasati',
            ]
        );

        // 3c. Buat Akun MANAGER FINANCE
        $finance = Employee::updateOrCreate(
            ['employee_id' => 'E0003'],
            [
                'role_id' => $roleFinance->role_id,
                'company_id' => $company->company_id,
                'branch_id' => $branch->branch_id,
                'ptkp_group_id' => 'G06', // K1
                'nik' => '3578012345678003',
                'full_name' => 'Budi Santoso',
                'date_of_birth' => '1988-11-20',
                'gender' => 'Male',
                'address' => 'Gresik',
                'phone_number' => '083344556677',
                'email' => 'finance@example.com',
                'password' => $defaultPassword,
                'job_title' => 'Finance Manager',
                'department' => 'Information Technology',
                'employment_status' => 'Permanent',
                'join_date' => '2021-08-15',
                'basic_salary' => 15000000,
                'position_allowance' => 3000000,
                'meal_allowance' => 1000000,
                'transport_allowance' => 1000000,
                'bank_name' => 'BNI',
                'account_number' => '1122334455',
                'account_holder_name' => 'Budi Santoso',
            ]
        );

        // 3d. Buat Akun KARYAWAN BIASA (Staff)
        $staff = Employee::updateOrCreate(
            ['employee_id' => 'E0004'],
            [
                'role_id' => $roleEmployee->role_id,
                'company_id' => $company->company_id,
                'branch_id' => $branch->branch_id,
                'ptkp_group_id' => 'G01', // TK0
                'nik' => '3578012345678004',
                'full_name' => 'Rizky Pratama',
                'date_of_birth' => '1998-07-10',
                'gender' => 'Male',
                'address' => 'Surabaya',
                'phone_number' => '085566778899',
                'email' => 'staff@example.com',
                'password' => $defaultPassword,
                'job_title' => 'Software Engineer',
                'department' => 'Information Technology',
                'employment_status' => 'Contract',
                'join_date' => '2024-01-10',
                'basic_salary' => 6000000,
                'position_allowance' => 500000,
                'meal_allowance' => 300000,
                'transport_allowance' => 300000,
                'bank_name' => 'BRI',
                'account_number' => '9988776655',
                'account_holder_name' => 'Rizky Pratama',
            ]
        );

        $today = Carbon::now();

        // 4. Seed Data Absensi untuk STAFF
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
                'clock_in_coordinates' => '-7.311000, 112.728795',
                'attendance_status' => 'Present',
                'clock_in_photo' => 'sample.jpg',
            ]
        );

        // 5. Seed Data Lembur (Staff yang lembur, HRD yang approve)
        Overtime::updateOrCreate(
            ['overtime_id' => 'OT0001'],
            [
                'employee_id' => $staff->employee_id,
                'attendance_id' => $attendance->attendance_id,
                'overtime_date' => $today->format('Y-m-d'),
                'duration' => '02:00:00',
                'total_incentive' => 150000,
                'task_description' => 'Fixing bug urgent di production',
                'status' => 'Approved',
                'approver_id' => $hrd->employee_id,
                'processed_at' => now(),
            ]
        );

        // 6. Seed Data Cuti (Staff ajukan cuti, status masih Pending)
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

        
    }
}