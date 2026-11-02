<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
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
        // ==========================================
        // 1. DATA MASTER ROLES
        // ==========================================
        $roleAdmin = Role::updateOrCreate(['role_id' => 'R01'], ['role_name' => 'System Administrator']);
        $roleHrd = Role::updateOrCreate(['role_id' => 'R02'], ['role_name' => 'HR Manager']);
        $roleFinance = Role::updateOrCreate(['role_id' => 'R03'], ['role_name' => 'Finance']);
        $roleEmployee = Role::updateOrCreate(['role_id' => 'R04'], ['role_name' => 'Employee']);

        // ==========================================
        // 2. DATA MASTER PERUSAHAAN & CABANG
        // ==========================================
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
                'radius_meters' => 100,
            ]
        );

        $defaultPassword = Hash::make('password123'); // Password universal untuk testing

        // ==========================================
        // 3. DAFTAR 10 KARYAWAN (EMPLOYEES)
        // ==========================================
        $employeesData = [
            // E0001: System Admin
            [
                'employee_id' => 'E0001',
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
                'join_date' => '2023-01-01',
                'basic_salary' => 12000000,
                'position_allowance' => 2000000,
                'meal_allowance' => 500000,
                'transport_allowance' => 500000,
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_holder_name' => 'Gemma Dwi Prasetya',
            ],
            // E0002: HR Manager
            [
                'employee_id' => 'E0002',
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
                'department' => 'Human Resources',
                'employment_status' => 'Permanent',
                'join_date' => '2022-03-01',
                'basic_salary' => 15000000,
                'position_allowance' => 3000000,
                'meal_allowance' => 1000000,
                'transport_allowance' => 1000000,
                'bank_name' => 'Mandiri',
                'account_number' => '0987654321',
                'account_holder_name' => 'Anita Larasati',
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
            // E0004: Software Engineer
            [
                'employee_id' => 'E0004',
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
                'basic_salary' => 7500000,
                'position_allowance' => 500000,
                'meal_allowance' => 400000,
                'transport_allowance' => 400000,
                'bank_name' => 'BRI',
                'account_number' => '9988776655',
                'account_holder_name' => 'Rizky Pratama',
            ],
            // E0005: UI/UX Designer
            [
                'employee_id' => 'E0005',
                'role_id' => $roleEmployee->role_id,
                'company_id' => $company->company_id,
                'branch_id' => $branch->branch_id,
                'ptkp_group_id' => 'G01', // TK0
                'nik' => '3578012345678005',
                'full_name' => 'Maya Putri Pertiwi',
                'date_of_birth' => '1999-03-25',
                'gender' => 'Female',
                'address' => 'Surabaya',
                'phone_number' => '081299887766',
                'email' => 'maya.putri@example.com',
                'password' => $defaultPassword,
                'job_title' => 'UI/UX Designer',
                'department' => 'Product & Design',
                'employment_status' => 'Permanent',
                'join_date' => '2024-02-01',
                'basic_salary' => 7000000,
                'position_allowance' => 500000,
                'meal_allowance' => 400000,
                'transport_allowance' => 400000,
                'bank_name' => 'BCA',
                'account_number' => '5544332211',
                'account_holder_name' => 'Maya Putri Pertiwi',
            ],
            // E0006: Backend Developer
            [
                'employee_id' => 'E0006',
                'role_id' => $roleEmployee->role_id,
                'company_id' => $company->company_id,
                'branch_id' => $branch->branch_id,
                'ptkp_group_id' => 'G02', // TK1
                'nik' => '3578012345678006',
                'full_name' => 'Dimas Anggara',
                'date_of_birth' => '1996-09-12',
                'gender' => 'Male',
                'address' => 'Sidoarjo',
                'phone_number' => '087711223344',
                'email' => 'dimas.anggara@example.com',
                'password' => $defaultPassword,
                'job_title' => 'Backend Developer',
                'department' => 'Information Technology',
                'employment_status' => 'Permanent',
                'join_date' => '2023-06-15',
                'basic_salary' => 8500000,
                'position_allowance' => 750000,
                'meal_allowance' => 500000,
                'transport_allowance' => 400000,
                'bank_name' => 'Mandiri',
                'account_number' => '6677889900',
                'account_holder_name' => 'Dimas Anggara',
            ],
            // E0007: QA Engineer
            [
                'employee_id' => 'E0007',
                'role_id' => $roleEmployee->role_id,
                'company_id' => $company->company_id,
                'branch_id' => $branch->branch_id,
                'ptkp_group_id' => 'G01', // TK0
                'nik' => '3578012345678007',
                'full_name' => 'Siti Nurhaliza',
                'date_of_birth' => '1997-12-04',
                'gender' => 'Female',
                'address' => 'Surabaya',
                'phone_number' => '089988776655',
                'email' => 'siti.nurhaliza@example.com',
                'password' => $defaultPassword,
                'job_title' => 'QA Engineer',
                'department' => 'Information Technology',
                'employment_status' => 'Contract',
                'join_date' => '2024-03-01',
                'basic_salary' => 6500000,
                'position_allowance' => 400000,
                'meal_allowance' => 350000,
                'transport_allowance' => 350000,
                'bank_name' => 'BNI',
                'account_number' => '7788990011',
                'account_holder_name' => 'Siti Nurhaliza',
            ],
            // E0008: DevOps Engineer
            [
                'employee_id' => 'E0008',
                'role_id' => $roleEmployee->role_id,
                'company_id' => $company->company_id,
                'branch_id' => $branch->branch_id,
                'ptkp_group_id' => 'G06', // K1
                'nik' => '3578012345678008',
                'full_name' => 'Farhan Ramadhan',
                'date_of_birth' => '1994-08-18',
                'gender' => 'Male',
                'address' => 'Gresik',
                'phone_number' => '081344556677',
                'email' => 'farhan.ramadhan@example.com',
                'password' => $defaultPassword,
                'job_title' => 'DevOps Engineer',
                'department' => 'Infrastructure',
                'employment_status' => 'Permanent',
                'join_date' => '2022-11-01',
                'basic_salary' => 10000000,
                'position_allowance' => 1000000,
                'meal_allowance' => 500000,
                'transport_allowance' => 500000,
                'bank_name' => 'BCA',
                'account_number' => '8899001122',
                'account_holder_name' => 'Farhan Ramadhan',
            ],
            // E0009: HR Specialist
            [
                'employee_id' => 'E0009',
                'role_id' => $roleEmployee->role_id,
                'company_id' => $company->company_id,
                'branch_id' => $branch->branch_id,
                'ptkp_group_id' => 'G01', // TK0
                'nik' => '3578012345678009',
                'full_name' => 'Dewi Lestari',
                'date_of_birth' => '1998-04-30',
                'gender' => 'Female',
                'address' => 'Sidoarjo',
                'phone_number' => '085211223344',
                'email' => 'dewi.lestari@example.com',
                'password' => $defaultPassword,
                'job_title' => 'HR Officer',
                'department' => 'Human Resources',
                'employment_status' => 'Permanent',
                'join_date' => '2023-09-01',
                'basic_salary' => 6000000,
                'position_allowance' => 400000,
                'meal_allowance' => 350000,
                'transport_allowance' => 350000,
                'bank_name' => 'BRI',
                'account_number' => '3344556677',
                'account_holder_name' => 'Dewi Lestari',
            ],
            // E0010: Accounting Staff
            [
                'employee_id' => 'E0010',
                'role_id' => $roleEmployee->role_id,
                'company_id' => $company->company_id,
                'branch_id' => $branch->branch_id,
                'ptkp_group_id' => 'G05', // K0
                'nik' => '3578012345678010',
                'full_name' => 'Hendra Wijaya',
                'date_of_birth' => '1995-10-08',
                'gender' => 'Male',
                'address' => 'Surabaya',
                'phone_number' => '087855667788',
                'email' => 'hendra.wijaya@example.com',
                'password' => $defaultPassword,
                'job_title' => 'Accounting Staff',
                'department' => 'Finance',
                'employment_status' => 'Permanent',
                'join_date' => '2023-05-10',
                'basic_salary' => 6500000,
                'position_allowance' => 400000,
                'meal_allowance' => 350000,
                'transport_allowance' => 350000,
                'bank_name' => 'Mandiri',
                'account_number' => '4455667788',
                'account_holder_name' => 'Hendra Wijaya',
            ],
        ];

        // Execute Employee Insertion & Leave Balance Initialization
        foreach ($employeesData as $empData) {
            $employee = Employee::updateOrCreate(
                ['employee_id' => $empData['employee_id']],
                $empData
            );

            // Inisialisasi Saldo Cuti 12 Hari untuk Setiap Karyawan di Tahun 2026
            EmployeeLeaveBalance::updateOrCreate(
                [
                    'employee_id' => $employee->employee_id,
                    'leave_type'  => 'Cuti Tahunan',
                    'year'        => 2026,
                ],
                [
                    'quota' => 12,
                    'used'  => rand(0, 4), // Dummy penggunaan cuti acak 0-4 hari
                ]
            );
        }

        // Ambil reference akun utama untuk relational seeding
        $staffRizky = Employee::where('employee_id', 'E0004')->first();
        $hrdAnita   = Employee::where('employee_id', 'E0002')->first();
        $devDimas   = Employee::where('employee_id', 'E0006')->first();

        $today = Carbon::now();

        // ==========================================
        // 4. SEED DATA RIWAYAT ABSENSI MULTI-HARI
        // ==========================================
        $attendancesToCreate = [
            // Presensi Hari Ini (Rizky)
            [
                'attendance_id' => 'ATT00001',
                'employee_id' => $staffRizky->employee_id,
                'branch_id' => $branch->branch_id,
                'date' => $today->format('Y-m-d'),
                'clock_in_time' => $today->copy()->setTime(7, 55, 0),
                'clock_out_time' => $today->copy()->setTime(17, 5, 0),
                'clock_in_status' => 'On Time',
                'clock_out_status' => 'On Time',
                'clock_in_coordinates' => '-7.311009, 112.728790',
                'attendance_status' => 'Present',
                'clock_in_photo' => 'selfie_sample1.jpg',
            ],
            // Presensi Kemarin (Rizky - Terlambat)
            [
                'attendance_id' => 'ATT00002',
                'employee_id' => $staffRizky->employee_id,
                'branch_id' => $branch->branch_id,
                'date' => $today->copy()->subDay()->format('Y-m-d'),
                'clock_in_time' => $today->copy()->subDay()->setTime(8, 25, 0),
                'clock_out_time' => $today->copy()->subDay()->setTime(17, 10, 0),
                'clock_in_status' => 'Late',
                'clock_out_status' => 'On Time',
                'clock_in_coordinates' => '-7.311009, 112.728790',
                'attendance_status' => 'Present',
                'clock_in_photo' => 'selfie_sample2.jpg',
            ],
            // Presensi Hari Ini (Dimas)
            [
                'attendance_id' => 'ATT00003',
                'employee_id' => $devDimas->employee_id,
                'branch_id' => $branch->branch_id,
                'date' => $today->format('Y-m-d'),
                'clock_in_time' => $today->copy()->setTime(7, 45, 0),
                'clock_out_time' => null, // Belum Clock Out
                'clock_in_status' => 'On Time',
                'clock_out_status' => null,
                'clock_in_coordinates' => '-7.311009, 112.728790',
                'attendance_status' => 'Present',
                'clock_in_photo' => 'selfie_sample3.jpg',
            ],
        ];

        foreach ($attendancesToCreate as $attData) {
            Attendance::updateOrCreate(['attendance_id' => $attData['attendance_id']], $attData);
        }

        // ==========================================
        // 5. SEED DATA LEMBUR (OVERTIME)
        // ==========================================
        // $firstAttendance = Attendance::first();

        // Overtime::updateOrCreate(
        //     ['overtime_id' => 'OT0001'],
        //     [
        //         'employee_id' => $staffRizky->employee_id,
        //         'attendance_id' => $firstAttendance->attendance_id,
        //         'overtime_date' => $today->format('Y-m-d'),
        //         'duration' => '02:00:00',
        //         'total_incentive' => 150000,
        //         'task_description' => 'Fixing bug urgent di server production',
        //         'status' => 'Approved',
        //         'approver_id' => $hrdAnita->employee_id,
        //         'processed_at' => now(),
        //     ]
        // );

        // Overtime::updateOrCreate(
        //     ['overtime_id' => 'OT0002'],
        //     [
        //         'employee_id' => $devDimas->employee_id,
        //         'attendance_id' => null,
        //         'overtime_date' => $today->copy()->addDay()->format('Y-m-d'),
        //         'duration' => '03:00:00',
        //         'total_incentive' => 225000,
        //         'task_description' => 'Optimasi query database & deployment fitur baru',
        //         'status' => 'Pending',
        //         'approver_id' => null,
        //         'processed_at' => null,
        //     ]
        // );

        // ==========================================
        // 6. SEED DATA LEAVE REQUESTS (CUTI & IZIN)
        // ==========================================
        LeaveRequest::updateOrCreate(
            ['leave_request_id' => 'LR0001'],
            [
                'employee_id' => $staffRizky->employee_id,
                'leave_type' => 'Annual Leave',
                'start_date' => $today->copy()->addDays(5)->format('Y-m-d'),
                'end_date' => $today->copy()->addDays(6)->format('Y-m-d'),
                'reason' => 'Acara keluarga di luar kota',
                'status' => 'Pending',
            ]
        );

        LeaveRequest::updateOrCreate(
            ['leave_request_id' => 'LR0002'],
            [
                'employee_id' => $devDimas->employee_id,
                'leave_type' => 'Sick Leave',
                'start_date' => $today->copy()->subDays(10)->format('Y-m-d'),
                'end_date' => $today->copy()->subDays(9)->format('Y-m-d'),
                'reason' => 'Demam dan flu (Surat Dokter Terlampir)',
                'status' => 'Approved',
                'approver_id' => $hrdAnita->employee_id,
            ]
        );

        // ==========================================
        // 7. SEED DATA PAYROLL (SLIP GAJI)
        // ==========================================
        // $previousMonth = $today->copy()->subMonth()->format('Y-m');

        // Payroll::updateOrCreate(
        //     ['payroll_id' => 'PAY'.date('Ym').'001'],
        //     [
        //         'employee_id' => $staffRizky->employee_id,
        //         'month_year' => $previousMonth,
        //         'basic_salary' => $staffRizky->basic_salary,
        //         'allowance_total' => $staffRizky->position_allowance + $staffRizky->meal_allowance + $staffRizky->transport_allowance,
        //         'deduction_total' => 250000, // Misal potongan BPJS / Keterlambatan
        //         'overtime_pay' => 150000,
        //         'net_salary' => ($staffRizky->basic_salary + 1300000 + 150000) - 250000,
        //         'payment_status' => 'Paid',
        //         'payment_date' => $today->copy()->subMonth()->endOfMonth()->format('Y-m-d'),
        //     ]
        // );
    }
}