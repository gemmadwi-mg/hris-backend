<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveRequest;
use App\Models\Employee;
use Carbon\Carbon;

class LeaveRequestSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil 3 karyawan secara acak untuk dijadikan bahan percobaan
        $employees = Employee::inRandomOrder()->limit(3)->get();

        if ($employees->count() < 1) {
            $this->command->info('Gagal: Tidak ada data karyawan di database. Buat karyawan dulu!');
            return;
        }

        // Skenario 1: Cuti berstatus "Pending" (Siap untuk Anda setujui/tolak di Frontend)
        LeaveRequest::create([
            'leave_request_id' => 'LR9901',
            'employee_id' => $employees[0]->employee_id,
            'leave_type' => 'Annual Leave',
            'start_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
            'reason' => 'Acara pernikahan saudara kandung di luar kota.',
            'status' => 'Pending',
        ]);

        // Skenario 2: Cuti berstatus "Approved" (Sudah disetujui sebelumnya)
        if (isset($employees[1])) {
            LeaveRequest::create([
                'leave_request_id' => 'LR9902',
                'employee_id' => $employees[1]->employee_id,
                'leave_type' => 'Sick Leave',
                'start_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'end_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
                'reason' => 'Gejala tifus, butuh istirahat total (surat dokter menyusul).',
                'status' => 'Approved',
                'approver_id' => $employees[0]->employee_id, // Asumsi karyawan 0 adalah HRD
                'processed_at' => Carbon::now()->subDays(5),
            ]);
        }

        // Skenario 3: Cuti berstatus "Rejected" (Ditolak karena alasan tertentu)
        if (isset($employees[2])) {
            LeaveRequest::create([
                'leave_request_id' => 'LR9903',
                'employee_id' => $employees[2]->employee_id,
                'leave_type' => 'Annual Leave',
                'start_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
                'reason' => 'Healing ke pantai untuk melepas penat.',
                'status' => 'Rejected',
                'approver_id' => $employees[0]->employee_id,
                'rejection_reason' => 'Project sedang dalam masa rilis (Production), tidak bisa mengambil cuti dadakan.',
                'processed_at' => Carbon::now()->subHours(2),
            ]);
        }

        $this->command->info('✅ Data uji coba Leave Requests berhasil disuntikkan!');
    }
}