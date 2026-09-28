<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Http\Request;


class PayrollController extends Controller
{
    public function generate(Request $request)
    {
        // 1. PENJAGA GERBANG RBAC
        if (! $request->user()->hasAnyRole(['HR', 'Manager'])) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'periode' => 'required|string', // Format dari frontend: "09-2026"
        ]);

        $periode = $request->periode;
        $parts = explode('-', $periode);

        // Konversi ke Integer agar sesuai dengan kolom tabel Anda
        $bulan = (int) $parts[0];
        $tahun = (int) $parts[1];

        $employees = Employee::with('position')->get();
        $generated = 0;

        foreach ($employees as $employee) {
            if (! $employee->position) {
                continue;
            }

            // Pengecekan data ganda disesuaikan dengan kolom 'bulan' dan 'tahun'
            $existingPayroll = Payroll::where('employee_id', $employee->id)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->first();

            if ($existingPayroll) {
                continue;
            }

            // Hitung jumlah kehadiran (ubah '09' menjadi 9 sesuai bulan)
            $totalHadir = Attendance::where('employee_id', $employee->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->where('status', 'Hadir')
                ->count();

            $hariKerjaStandar = 22;
            $gajiPokok = $employee->position->gaji_pokok;

            $potongan = 0;
            if ($totalHadir < $hariKerjaStandar) {
                $hariAbsen = $hariKerjaStandar - $totalHadir;
                $potonganPerHari = $gajiPokok / $hariKerjaStandar;
                $potongan = $hariAbsen * $potonganPerHari;
            }

            $totalGajiBersih = $gajiPokok - $potongan;

            // Simpan data menggunakan NAMA KOLOM ASLI ANDA
            Payroll::create([
                'employee_id' => $employee->id,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'gaji_pokok' => $gajiPokok,
                'tunjangan' => 0, // Default 0
                'potongan' => $potongan,
                'total_gaji_bersih' => $totalGajiBersih > 0 ? $totalGajiBersih : 0,
                'status' => 'paid', // Kita asumsikan langsung lunas saat digenerate
                'tanggal_pembayaran' => Carbon::now()->toDateString(),
            ]);

            $generated++;
        }

        return response()->json([
            'message' => "Berhasil memproses slip gaji untuk {$generated} karyawan.",
        ], 201);
    }

    // Fungsi untuk mengambil riwayat gaji karyawan yang sedang login
    public function myPayslips(Request $request)
    {
        $employee = $request->user()->employee;

        if (! $employee) {
            return response()->json(['data' => []], 200);
        }

        $payrolls = Payroll::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $payrolls], 200);
    }
}
