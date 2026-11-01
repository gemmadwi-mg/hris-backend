<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PermitRequest;
use App\Models\Employee;
use Carbon\Carbon;

class PermitRequestSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil 3 karyawan secara acak
        $employees = Employee::inRandomOrder()->limit(3)->get();

        if ($employees->count() < 1) {
            $this->command->info('Gagal: Tidak ada data karyawan di database. Buat karyawan dulu!');
            return;
        }

        // Skenario 1: Izin berstatus "Pending" (Siap untuk Anda setujui/tolak)
        PermitRequest::create([
            'permit_request_id' => 'PR9901',
            'employee_id' => $employees[0]->employee_id,
            'permit_type' => 'Sick',
            'start_date' => Carbon::now()->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'description' => 'Sakit demam tinggi dan flu berat. Surat keterangan dokter menyusul setelah periksa.',
            'status' => 'Pending',
        ]);

        // Skenario 2: Izin berstatus "Approved" (Sudah disetujui HRD)
        if (isset($employees[1])) {
            PermitRequest::create([
                'permit_request_id' => 'PR9902',
                'employee_id' => $employees[1]->employee_id,
                'permit_type' => 'Condolence',
                'start_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'end_date' => Carbon::now()->subDays(8)->format('Y-m-d'),
                'description' => 'Keluarga inti (nenek) meninggal dunia di luar kota.',
                'status' => 'Approved',
                'approver_id' => $employees[0]->employee_id, // Asumsi karyawan 0 adalah HRD
                'processed_at' => Carbon::now()->subDays(10),
            ]);
        }

        // Skenario 3: Izin berstatus "Rejected" (Ditolak)
        if (isset($employees[2])) {
            PermitRequest::create([
                'permit_request_id' => 'PR9903',
                'employee_id' => $employees[2]->employee_id,
                'permit_type' => 'Personal',
                'start_date' => Carbon::now()->addDays(1)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(1)->format('Y-m-d'),
                'description' => 'Izin untuk mengurus perpanjangan SIM dan STNK di Samsat.',
                'status' => 'Rejected',
                'approver_id' => $employees[0]->employee_id,
                'rejection_reason' => 'Perpanjangan SIM bisa dilakukan di akhir pekan (Samsat Corner). Sedang banyak antrean pekerjaan.',
                'processed_at' => Carbon::now()->subHours(5),
            ]);
        }

        $this->command->info('✅ Data uji coba Permit Requests (Izin) berhasil disuntikkan!');
    }
}