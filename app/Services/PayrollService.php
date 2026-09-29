<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    public function generatePayrollBulanIni($bulan, $tahun)
    {
        // 1. Ambil semua karyawan beserta data jabatan (untuk tau gaji pokok & tunjangan)
        $employees = Employee::with('position')->get();
        $payrollsGenerated = 0;

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                // 2. Hitung jumlah kehadiran, keterlambatan, dan ketidakhadiran di bulan tersebut
                $attendances = Attendance::where('employee_id', $employee->id)
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun)
                    ->get();

                $totalHadir = $attendances->where('status', 'Hadir')->count();
                $totalSakitIzin = $attendances->whereIn('status', ['Sakit', 'Izin'])->count();

                // Cari yang terlambat (jam masuk > 08:15:00)
                $totalTerlambat = $attendances->filter(function ($absen) {
                    if (! $absen->clock_in) {
                        return false;
                    }
                    $jamMasuk = Carbon::parse($absen->clock_in)->format('H:i:s');

                    return $jamMasuk > '08:15:00';
                })->count();

                // 3. Ambil Komponen Pendapatan (Dari relasi Jabatan)
                // Jika jabatan belum diatur, gunakan nilai default/UMR
                // UBAH BAGIAN PENGAMBILAN GAJI INI:
                $gajiPokok = $employee->position?->standar_gaji_pokok ?? 3500000;
                $tunjangan = 0; // Karena tabel Position Anda tidak memiliki kolom tunjangan
                // 4. Kalkulasi Potongan
                // Misal: Terlambat dipotong Rp 50.000/hari, Alfa dipotong Rp 100.000/hari
                // Asumsi hari kerja efektif sebulan = 22 hari
                $hariKerjaEfektif = 22;
                $hariAlfa = $hariKerjaEfektif - ($totalHadir + $totalSakitIzin);
                if ($hariAlfa < 0) {
                    $hariAlfa = 0;
                } // Cegah minus jika rajin masuk akhir pekan

                $potonganTerlambat = $totalTerlambat * 50000;
                $potonganAlfa = $hariAlfa * 100000;
                $totalPotongan = $potonganTerlambat + $potonganAlfa;

                // 5. Kalkulasi Total Bersih (Take Home Pay)
                $totalBersih = ($gajiPokok + $tunjangan) - $totalPotongan;

                // 6. Simpan atau Perbarui Data Gaji ke Database (updateOrCreate agar tidak ganda)
                Payroll::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'bulan' => $bulan,
                        'tahun' => $tahun,
                    ],
                    [
                        'gaji_pokok' => $gajiPokok,
                        'tunjangan' => $tunjangan,
                        'potongan' => $totalPotongan,
                        'total_gaji_bersih' => $totalBersih,
                        'status' => 'pending', // Bisa diubah jadi 'Paid' nanti
                    ]
                );

                $payrollsGenerated++;
            }

            DB::commit();

            return ['status' => 'success', 'message' => "$payrollsGenerated slip gaji berhasil digenerate."];

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
