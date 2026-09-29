<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Imports\AttendancesImport;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    public function clockIn(Request $request)
    {
        $employee = $request->user()->employee;

        if (! $employee) {
            return response()->json(['message' => 'Profil karyawan tidak ditemukan.'], 404);
        }

        $today = Carbon::today()->toDateString();

        // Cek apakah sudah absen masuk hari ini
        $alreadyClockedIn = Attendance::where('employee_id', $employee->id)
            ->where('tanggal', $today)
            ->exists();

        if ($alreadyClockedIn) {
            return response()->json(['message' => 'Anda sudah melakukan clock-in hari ini.'], 400);
        }

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'tanggal' => $today,
            'clock_in' => Carbon::now(),
            // Contoh hardcode lokasi, nantinya bisa diambil dari GPS device browser
            'location' => $request->input('location', 'Tambak Anakan, Surabaya'),
            'status' => 'Hadir',
        ]);

        return response()->json(['message' => 'Berhasil Clock-in', 'data' => $attendance], 201);
    }

    public function clockOut(Request $request)
    {
        $employee = $request->user()->employee;
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('tanggal', $today)
            ->first();

        if (! $attendance) {
            return response()->json(['message' => 'Anda belum clock-in hari ini.'], 400);
        }

        if ($attendance->clock_out) {
            return response()->json(['message' => 'Anda sudah melakukan clock-out.'], 400);
        }

        // PERBARUI BAGIAN INI: Simpan jam pulang sekaligus lokasi pulangnya
        $attendance->update([
            'clock_out' => Carbon::now(),
            'clock_out_location' => $request->input('location_out'), 
        ]);

        return response()->json(['message' => 'Berhasil Clock-out', 'data' => $attendance], 200);
    }

    public function todayAttendances()
    {
        // SEBELUMNYA: Attendance::with('employee.department')
        // UBAH MENJADI:
        $attendances = Attendance::with('employee')
            ->whereDate('created_at', today())
            ->orderBy('clock_in', 'desc')
            ->get();

        return response()->json($attendances);
    }

    public function getAttendances(Request $request)
    {
        $query = Attendance::with('employee');

        // Filter berdasarkan tanggal tertentu (Format: YYYY-MM-DD)
        if ($request->has('date')) {
            $query->whereDate('tanggal', $request->date);
        } 
        // Filter berdasarkan Bulan & Tahun (Format: YYYY-MM)
        else if ($request->has('month')) {
            // Misal request: 2026-09
            $parts = explode('-', $request->month);
            if (count($parts) == 2) {
                $query->whereYear('tanggal', $parts[0])
                      ->whereMonth('tanggal', $parts[1]);
            }
        } 
        // Default: Ambil data hari ini jika tidak ada filter
        else {
            $query->whereDate('tanggal', Carbon::today()->toDateString());
        }

        // Urutkan berdasarkan waktu masuk terbaru, lalu kirim
        $attendances = $query->orderBy('clock_in', 'desc')->get();
        return response()->json($attendances);
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            // EKSEKUSI LANGSUNG DI TEMPAT (Synchronous)
            Excel::import(new AttendancesImport, $request->file('file'));

            return response()->json([
                'status' => 'success',
                'message' => 'Data absensi berhasil diimpor sepenuhnya.'
            ]);
            
        // 2. UBAH \Exception MENJADI \Throwable
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error Server: ' . $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    // FUNGSI BARU: Untuk menjawab pertanyaan "Polling" dari Vue
    public function checkImportProgress(Request $request)
    {
        $cacheKey = $request->query('cache_key');
        $totalRows = $request->query('total_rows');

        if (! $cacheKey || ! $totalRows) {
            return response()->json(['progress' => 0, 'processed' => 0]);
        }

        // Ambil jumlah baris yang sudah diproses dari Cache
        $processed = Cache::get($cacheKey, 0);
        $percentage = min(100, round(($processed / $totalRows) * 100));

        return response()->json([
            'progress' => $percentage,
            'processed' => $processed,
            'total' => $totalRows,
        ]);
    }
}
