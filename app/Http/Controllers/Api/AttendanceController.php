<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

        $attendance->update([
            'clock_out' => Carbon::now(),
        ]);

        return response()->json(['message' => 'Berhasil Clock-out', 'data' => $attendance], 200);
    }
}
