<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Position;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OperationalDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Data Master (Hapus kolom deskripsi)
        // Tambahkan 'kode_departemen' pada kedua baris ini:
        $deptIT = Department::create([
            'kode_departemen' => 'IT-001',
            'nama_departemen' => 'IT & Engineering',
        ]);

        $deptHR = Department::create([
            'kode_departemen' => 'HR-001',
            'nama_departemen' => 'Human Resources',
        ]);

        $posManager = Position::create(['department_id' => $deptIT->id, 'nama_jabatan' => 'Engineering Manager', 'gaji_pokok' => 15000000]);
        $posStaff = Position::create(['department_id' => $deptIT->id, 'nama_jabatan' => 'Frontend Developer', 'gaji_pokok' => 8000000]);
        $posHR = Position::create(['department_id' => $deptHR->id, 'nama_jabatan' => 'HR Specialist', 'gaji_pokok' => 7000000]);

        $users = User::all();

        foreach ($users as $user) {
            $positionId = $posStaff->id;
            if ($user->hasRole('Manager')) {
                $positionId = $posManager->id;
            }
            if ($user->hasRole('HR')) {
                $positionId = $posHR->id;
            }

            // 2. Buat Profil Karyawan (Sesuaikan dengan kolom lama Anda)
            // Jika di tabel lama Anda ada email/tanggal_bergabung, silakan tambahkan kembali
            $employee = Employee::create([
                'user_id' => $user->id,
                'position_id' => $positionId,
                'nip' => 'EMP-'.rand(1000, 9999),
                'nama_lengkap' => $user->name,
                'email' => $user->email, // <--- BARIS INI YANG DITAMBAHKAN
                'tanggal_bergabung' => '2025-01-01', // <--- KEMBALIKAN BARIS INI
                // Hapus nomor_rekening, nama_bank, dan tanggal_bergabung (jika tidak ada)
            ]);

            // 3. Buat Data Absensi (Ini tabel baru, jadi pasti aman)
            for ($i = 1; $i <= 5; $i++) {
                $tanggal = Carbon::now()->subDays($i)->toDateString();
                Attendance::create([
                    'employee_id' => $employee->id,
                    'tanggal' => $tanggal,
                    'clock_in' => Carbon::parse($tanggal.' 08:00:00'),
                    'clock_out' => Carbon::parse($tanggal.' 17:00:00'),
                    'location' => 'Kantor Pusat Surabaya',
                    'status' => 'Hadir',
                ]);
            }

            // 4. Buat Pengajuan Cuti Khusus untuk Karyawan (Ini juga tabel baru, pasti aman)
            if ($user->hasRole('Karyawan')) {
                Leave::create([
                    'employee_id' => $employee->id,
                    'start_date' => Carbon::now()->addDays(3)->toDateString(),
                    'end_date' => Carbon::now()->addDays(5)->toDateString(),
                    'reason' => 'Acara keluarga di luar kota',
                    'status' => 'Pending',
                ]);
            }
        }
    }
}
