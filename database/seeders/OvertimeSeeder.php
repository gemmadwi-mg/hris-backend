<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Overtime;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;

class OvertimeSeeder extends Seeder
{
    public function run(): void
    {
        // Cari 3 data absensi secara acak untuk dijadikan basis lembur
        $attendances = Attendance::inRandomOrder()->limit(3)->get();
        $hrd = Employee::first(); // Ambil 1 karyawan sembarang sebagai HRD/Approver

        if ($attendances->count() < 1) {
            $this->command->info('Gagal: Tidak ada data absensi di database. Buat absensi manual terlebih dahulu!');
            return;
        }

        // Skenario 1: Lembur "Pending" (Siap untuk Anda berikan nominal uang di Frontend)
        Overtime::create([
            'overtime_id' => 'OT9901',
            'employee_id' => $attendances[0]->employee_id,
            'attendance_id' => $attendances[0]->attendance_id,
            'overtime_date' => $attendances[0]->date,
            'duration' => '02:30:00', // 2.5 Jam
            'task_description' => 'Deploy pembaruan aplikasi HRIS ke server production dan memastikan tidak ada bug.',
            'status' => 'Pending',
        ]);

        // Skenario 2: Lembur "Approved" (Sudah disetujui dan diberikan insentif Rp 150.000)
        if (isset($attendances[1])) {
            Overtime::create([
                'overtime_id' => 'OT9902',
                'employee_id' => $attendances[1]->employee_id,
                'attendance_id' => $attendances[1]->attendance_id,
                'overtime_date' => $attendances[1]->date,
                'duration' => '04:00:00', // 4 Jam
                'total_incentive' => 150000, // Insentif yang diberikan HRD
                'task_description' => 'Mengerjakan laporan akhir bulan dan rekap absensi.',
                'status' => 'Approved',
                'approver_id' => $hrd->employee_id,
                'processed_at' => Carbon::now()->subDays(2),
            ]);
        }

        // Skenario 3: Lembur "Rejected" (Ditolak)
        if (isset($attendances[2])) {
            Overtime::create([
                'overtime_id' => 'OT9903',
                'employee_id' => $attendances[2]->employee_id,
                'attendance_id' => $attendances[2]->attendance_id,
                'overtime_date' => $attendances[2]->date,
                'duration' => '01:00:00', // 1 Jam
                'task_description' => 'Merapikan meja kerja dan menyusun dokumen lama di lemari.',
                'status' => 'Rejected',
                'rejection_reason' => 'Pekerjaan ini bukan prioritas dan bisa dikerjakan di jam kerja normal (tidak memenuhi syarat lembur).',
                'approver_id' => $hrd->employee_id,
                'processed_at' => Carbon::now()->subHours(5),
            ]);
        }

        $this->command->info('✅ Data uji coba Lembur (Overtime) berhasil disuntikkan!');
    }
}