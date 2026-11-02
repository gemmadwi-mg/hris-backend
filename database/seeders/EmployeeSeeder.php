<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $roleAdmin = Role::where('role_id', 'R01')->first();
        $roleHrd   = Role::where('role_id', 'R02')->first();
        $roleFinance = Role::where('role_id', 'R03')->first();
        $roleStaff = Role::where('role_id', 'R04')->first();

        $company = Company::first();
        $branch  = Branch::first();
        $defaultPassword = Hash::make('password123');

        // 1. DATA KARYAWAN FIXED (DIISIKAN KOLOM DATE_OF_BIRTH, GENDER, & ADDRESS)
        $employeesData = [
            [
                'employee_id'   => 'E0001',
                'role_id'       => $roleAdmin?->role_id ?? 'R01',
                'company_id'    => $company?->company_id ?? 'C0001',
                'branch_id'     => $branch?->branch_id ?? 'B0001',
                'nik'           => '3578012345678001',
                'full_name'     => 'Gemma Dwi Prasetya',
                'date_of_birth' => '2000-01-01', // 💡 Wajib diisi
                'gender'        => 'Male',       // 💡 Wajib diisi
                'address'       => 'Surabaya',   // 💡 Wajib diisi
                'email'         => 'admin@example.com',
                'password'      => $defaultPassword,
                'job_title'     => 'System Administrator',
                'department'    => 'Information Technology',
                'basic_salary'  => 12000000,
            ],
            [
                'employee_id'   => 'E0002',
                'role_id'       => $roleHrd?->role_id ?? 'R02',
                'company_id'    => $company?->company_id ?? 'C0001',
                'branch_id'     => $branch?->branch_id ?? 'B0001',
                'nik'           => '3578012345678002',
                'full_name'     => 'Anita Larasati',
                'date_of_birth' => '1990-05-15', // 💡 Wajib diisi
                'gender'        => 'Female',     // 💡 Wajib diisi
                'address'       => 'Sidoarjo',   // 💡 Wajib diisi
                'email'         => 'hrd@example.com',
                'password'      => $defaultPassword,
                'job_title'     => 'HR Manager',
                'department'    => 'Human Resources',
                'basic_salary'  => 15000000,
            ],
            // E0003: Finance Manager
            [
                'employee_id' => 'E0003',
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
                'department' => 'Finance',
                'employment_status' => 'Permanent',
                'join_date' => '2021-08-15',
                'basic_salary' => 15000000,
                'position_allowance' => 3000000,
                'meal_allowance' => 1000000,
                'transport_allowance' => 1000000,
                'bank_name' => 'BNI',
                'account_number' => '1122334455',
                'account_holder_name' => 'Budi Santoso',
            ],
            [
                'employee_id'   => 'E0004',
                'role_id'       => $roleStaff?->role_id ?? 'R04',
                'company_id'    => $company?->company_id ?? 'C0001',
                'branch_id'     => $branch?->branch_id ?? 'B0001',
                'nik'           => '3578012345678004',
                'full_name'     => 'Rizky Pratama',
                'date_of_birth' => '1998-07-10', // 💡 Wajib diisi
                'gender'        => 'Male',       // 💡 Wajib diisi
                'address'       => 'Surabaya',   // 💡 Wajib diisi
                'email'         => 'staff@example.com',
                'password'      => $defaultPassword,
                'job_title'     => 'Software Engineer',
                'department'    => 'Information Technology',
                'basic_salary'  => 7500000,
            ],
        ];

        // 2. SIMPAN DATA KARYAWAN FIXED & INSIALISASI SALDO CUTI
        foreach ($employeesData as $data) {
            $employee = Employee::updateOrCreate(['employee_id' => $data['employee_id']], $data);

            EmployeeLeaveBalance::updateOrCreate(
                ['employee_id' => $employee->employee_id, 'leave_type' => 'Cuti Tahunan', 'year' => 2026],
                ['quota' => 12, 'used' => 0]
            );
        }

        // 3. GENERATE 10 DATA KARYAWAN ACAK TAMBAHAN VIA FACTORY
        Employee::factory()->count(10)->create();
    }
}