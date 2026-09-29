<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Services\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PayrollController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    public function index(Request $request)
    {
        // PENJAGA GERBANG RBAC: Hanya HR dan Manager yang boleh melihat data seluruh gaji
        if (! $request->user()->hasAnyRole(['HR', 'Manager'])) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        // Ambil semua data gaji dan sertakan relasi karyawan (Eager Loading)
        // Urutkan dari tahun dan bulan terbaru
        $payrolls = Payroll::with('employee')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($payrolls, 200);
    }

    // Fungsi HRD: Generate Gaji Massal
    public function generate(Request $request)
    {
        // Format request yg diharapkan: "09-2026"
        $request->validate(['periode' => 'required|string']);

        $parts = explode('-', $request->periode);
        if (count($parts) !== 2) {
            return response()->json(['message' => 'Format periode tidak valid.'], 400);
        }

        $bulan = $parts[0];
        $tahun = $parts[1];

        try {
            $hasil = $this->payrollService->generatePayrollBulanIni($bulan, $tahun);

            return response()->json($hasil);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghitung gaji: '.$e->getMessage(),
            ], 500);
        }
    }

    // Fungsi Karyawan: Melihat riwayat gaji sendiri
    public function myPayslips()
    {
        $karyawan = auth()->user()->employee; // Asumsi User terelasi dengan Employee
        if (! $karyawan) {
            return response()->json(['data' => []]);
        }

        $payslips = Payroll::where('employee_id', $karyawan->id)
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();

        return response()->json(['data' => $payslips]);
    }

    // Fungsi Bersama (HRD & Karyawan): Unduh Slip Gaji PDF
    public function downloadPayslip($id)
    {
        // Cari data gaji berdasarkan ID Employee (karena di frontend Anda melempar employee.id)
        // Kita ambil gaji terbaru dari karyawan tersebut
        // TAMBAHKAN .department AGAR RELASINYA IKUT TERBAWA
        $payroll = Payroll::with(['employee.position.department'])
            ->where('employee_id', $id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $payroll) {
            return response()->json(['message' => 'Data gaji tidak ditemukan.'], 404);
        }

        // Siapkan data untuk dikirim ke template PDF
        $data = [
            'title' => 'Slip Gaji Karyawan',
            'payroll' => $payroll,
            'employee' => $payroll->employee,
            'jabatan' => $payroll->employee->position,
            'tanggal_cetak' => Carbon::now()->format('d F Y'),
        ];

        // Render view HTML menjadi PDF
        $pdf = Pdf::loadView('pdf.payslip', $data);

        // Atur ukuran kertas ke A5 agar lebih pantas untuk Slip Gaji
        $pdf->setPaper('a5', 'landscape');

        // Kembalikan ke browser sebagai file unduhan langsung (attachment)
        return $pdf->download('Slip_Gaji_'.str_replace(' ', '_', $payroll->employee->nama_lengkap).'.pdf');
    }

    public function disburse(Request $request, Payroll $payroll)
    {
        // 1. Validasi Akses & Status
        if (! $request->user()->hasAnyRole(['HR', 'Manager'])) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if ($payroll->status !== 'pending') {
            return response()->json(['message' => 'Gaji ini sudah diproses atau dibayar.'], 400);
        }

        // 2. Ambil data rekening karyawan
        // Asumsi: tabel employees memiliki nama_bank, nomor_rekening, dan nama_lengkap
        $employee = $payroll->employee;

        if (! $employee->nama_bank || ! $employee->nomor_rekening) {
            return response()->json(['message' => 'Data rekening karyawan tidak lengkap.'], 400);
        }

        // 3. Panggil API Xendit menggunakan Laravel HTTP Client
        $secretKey = env('XENDIT_SECRET_KEY').':'; // Xendit butuh titik dua (:) di akhir key untuk Basic Auth

        $response = Http::withBasicAuth($secretKey, '')
            ->post('https://api.xendit.co/disbursements', [
                'external_id' => 'PAYROLL-'.$payroll->id, // ID unik dari sistem kita
                'amount' => $payroll->total_gaji_bersih,
                'bank_code' => $employee->nama_bank, // Contoh: 'BCA', 'BNI', 'MANDIRI'
                'account_holder_name' => $employee->nama_lengkap, // Nama pemilik rekening
                'account_number' => $employee->nomor_rekening,
                'description' => "Gaji Bulan {$payroll->bulan}-{$payroll->tahun}",
            ]);

        // 4. Tangani Respon dari Xendit
        if ($response->successful()) {
            $xenditData = $response->json();

            // Simpan ID Transaksi dari Xendit untuk keperluan Webhook nanti
            $payroll->update([
                'xendit_disbursement_id' => $xenditData['id'],
                // Status tetap pending sampai Webhook Xendit mengabarkan status berhasil/gagal
            ]);

            return response()->json([
                'message' => 'Pencairan dana sedang diproses oleh bank.',
                'data' => $xenditData,
            ]);
        }

        // Jika API Xendit menolak (contoh: saldo test mode habis, kode bank salah)
        return response()->json([
            'message' => 'Gagal terhubung ke gerbang pembayaran.',
            'error' => $response->json(),
        ], 500);
    }

    public function xenditWebhook(Request $request)
    {
        // 1. VALIDASI KEAMANAN TINGKAT TINGGI
        // Xendit selalu mengirimkan token rahasia di dalam HTTP Header 'x-callback-token'
        $xenditToken = $request->header('x-callback-token');

        // Cek apakah token yang dikirim cocok dengan yang ada di .env kita
        if ($xenditToken !== env('XENDIT_WEBHOOK_TOKEN')) {
            // Tolak mentah-mentah jika token salah/tidak ada (Hacker detected!)
            return response()->json(['message' => 'Akses ditolak! Token tidak valid.'], 403);
        }

        // 2. Ambil isi data yang dikirim oleh Xendit
        $data = $request->all();

        // 3. Cari data payroll kita berdasarkan ID Transaksi Xendit
        $payroll = Payroll::where('xendit_disbursement_id', $data['id'])->first();

        if (! $payroll) {
            // Balas 404 jika transaksi tidak dikenali sistem kita
            return response()->json(['message' => 'Data payroll tidak ditemukan.'], 404);
        }

        // 4. Update status gaji berdasarkan laporan dari bank
        if ($data['status'] === 'COMPLETED') {
            $payroll->update([
                'status' => 'paid',
                'tanggal_pembayaran' => Carbon::now(), // Catat tanggal & jam uang masuk
            ]);
        } elseif ($data['status'] === 'FAILED') {
            $payroll->update([
                'status' => 'failed',
                // Di dunia nyata, di sini kita bisa mengirim email notifikasi ke HRD
                // bahwa rekening karyawan ternyata invalid/tutup.
            ]);
        }

        // 5. WAJIB: Balas HTTP 200 OK
        // Jika kita tidak membalas 200, Xendit akan mengira server kita mati
        // dan akan terus-terusan menembak endpoint ini berulang kali (Retry mechanism)
        return response()->json(['message' => 'Webhook berhasil diproses.'], 200);
    }
}
