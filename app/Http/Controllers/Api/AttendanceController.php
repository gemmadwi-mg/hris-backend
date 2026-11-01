<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        // Eager load relasi karyawan dan cabang
        $query = Attendance::with(['employee', 'branch']);

        $user = $request->user();
        $employeeId = $user->employee_id ?? $user->employee?->employee_id;

        // 🔒 1. PROTEKSI: Jika login sebagai Karyawan (bukan Admin/HRD), kunci query khusus data dirinya
        if ($user->hasRole('Employee') || $request->has('my_attendances')) {
            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            }
        }

        // 📅 2. FILTER BULAN (Format YYYY-MM dari frontend)
        if ($request->filled('month')) {
            $query->where('date', 'like', "{$request->month}%");
        }

        // 🔍 3. FITUR PENCARIAN (Berdasarkan nama karyawan atau ID presensi)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('employee', function ($empQ) use ($search) {
                    $empQ->where('full_name', 'like', "%{$search}%");
                })->orWhere('attendance_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('attendance_status', $request->status);
        }

        // Ambil data terbaru, 10 per halaman
        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('clock_in_time', 'desc')
            ->paginate(10);

        return response()->json(['success' => true, 'data' => $attendances], 200);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $employeeId = $request->input('employee_id') ?? $user->employee_id ?? $user->employee?->employee_id;
        $branchId = $request->input('branch_id') ?? $user->branch_id ?? $user->employee?->branch_id ?? 'BRANCH01';

        if (! $employeeId) {
            return response()->json([
                'success' => false,
                'message' => 'Data Karyawan (employee_id) tidak ditemukan pada akun ini.',
            ], 422);
        }

        $request->validate([
            'type' => 'required|in:In,Out',
            'latitude' => 'required',
            'longitude' => 'required',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
        ]);

        $today = now()->format('Y-m-d');
        $nowDateTime = now()->format('Y-m-d H:i:s');
        $coordinates = $request->latitude.','.$request->longitude;

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('attendances', 'public');
        }

        $scheduleIn = now()->setTime(8, 0, 0);
        $scheduleOut = now()->setTime(17, 0, 0);

        $attendance = Attendance::where('employee_id', $employeeId)
            ->where('date', $today)
            ->first();

        // LOGIKA ABSEN MASUK (CLOCK IN)
        if ($request->type === 'In') {
            if ($attendance && $attendance->clock_in_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan Clock In hari ini.',
                ], 422);
            }

            $isLate = now()->gt($scheduleIn);
            $clockInStatus = $isLate ? 'Late' : 'On Time';
            $lateDuration = $isLate ? now()->diff($scheduleIn)->format('%H:%I:%S') : null;

            if (! $attendance) {
                $attendance = Attendance::create([
                    'attendance_id' => 'AT'.date('ymd').strtoupper(Str::random(2)),
                    'employee_id' => $employeeId,
                    'branch_id' => $branchId,
                    'date' => $today,
                    'attendance_status' => 'Present',
                    'clock_in_time' => $nowDateTime,
                    'clock_in_status' => $clockInStatus,
                    'late_duration' => $lateDuration,
                    'clock_in_coordinates' => $coordinates,
                    'clock_in_photo' => $photoPath ?? 'manual_input.jpg',
                ]);
            } else {
                $attendance->update([
                    'clock_in_time' => $nowDateTime,
                    'clock_in_status' => $clockInStatus,
                    'late_duration' => $lateDuration,
                    'clock_in_coordinates' => $coordinates,
                    'clock_in_photo' => $photoPath ?? $attendance->clock_in_photo,
                ]);
            }
        }
        // LOGIKA ABSEN PULANG (CLOCK OUT)
        else {
            if (! $attendance || ! $attendance->clock_in_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum melakukan Clock In hari ini.',
                ], 422);
            }

            if ($attendance->clock_out_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan Clock Out hari ini.',
                ], 422);
            }

            $isEarly = now()->lt($scheduleOut);
            $clockOutStatus = $isEarly ? 'Early Depart' : 'On Time';
            $earlyLeaveDuration = $isEarly ? $scheduleOut->diff(now())->format('%H:%I:%S') : null;

            $attendance->update([
                'clock_out_time' => $nowDateTime,
                'clock_out_status' => $clockOutStatus,
                'early_leave_duration' => $earlyLeaveDuration,
                'clock_out_coordinates' => $coordinates,
                'clock_out_photo' => $photoPath ?? 'manual_input.jpg',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data absensi '.($request->type === 'In' ? 'Masuk' : 'Pulang').' berhasil disimpan.',
            'data' => $attendance,
        ], 200);
    }

    public function show($id)
    {
        $attendance = Attendance::with(['employee', 'branch'])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $attendance], 200);
    }

    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->update($request->all());

        return response()->json(['success' => true, 'data' => $attendance], 200);
    }

    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();

        return response()->json(['success' => true, 'message' => 'Data absensi dihapus'], 200);
    }
}