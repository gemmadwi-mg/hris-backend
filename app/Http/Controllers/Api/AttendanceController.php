<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PermitRequest;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    // 💡 2. Inject via Constructor
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $employeeId = $user->employee_id ?? $user->employee?->employee_id;
        $branchId = $user->branch_id ?? $user->employee?->branch_id;

        // 💡 3. Panggil method dari Service Class
        if ($user->hasRole('Employee') || $request->has('my_attendances')) {
            if ($employeeId) {
                $this->attendanceService->syncMissingAlpha($employeeId, $branchId);
            }
        }

        $query = Attendance::with(['employee', 'branch']);

        if ($user->hasRole('Employee') || $request->has('my_attendances')) {
            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            }
        }

        if ($request->filled('month')) {
            $query->where('date', 'like', "{$request->month}%");
        }

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

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('clock_in_time', 'desc')
            ->paginate(10);

        return response()->json(['success' => true, 'data' => $attendances], 200);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        // 💡 Pencarian branch_id secara dinamis
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
            $now = now();
            $earliestIn = now()->setTime(6, 0, 0);
            $latestIn = now()->setTime(13, 0, 0);

            if ($now->lt($earliestIn)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Presensi Masuk (Clock In) belum dibuka. Presensi dimulai pukul 06:00 WIB.',
                ], 422);
            }

            if ($now->gt($latestIn)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Presensi Masuk (Clock In) untuk hari ini telah ditutup (Maksimal pukul 13:00 WIB).',
                ], 422);
            }

            if ($attendance && $attendance->clock_in_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan Clock In hari ini.',
                ], 422);
            }

            $isLate = $now->gt($scheduleIn);
            $clockInStatus = $isLate ? 'Late' : 'On Time';
            $lateDuration = $isLate ? $now->diff($scheduleIn)->format('%H:%I:%S') : null;

            if (! $attendance) {
                $attendance = Attendance::create([
                    'attendance_id' => 'AT'.date('ymd').Str::upper(Str::random(2)),
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

            $now = now();
            $isEarly = $now->lt($scheduleOut);
            $clockOutStatus = $isEarly ? 'Early Depart' : 'On Time';
            $earlyLeaveDuration = $isEarly ? $scheduleOut->diff($now)->format('%H:%I:%S') : null;

            $attendance->update([
                'clock_out_time' => $nowDateTime,
                'clock_out_status' => $clockOutStatus,
                'early_leave_duration' => $earlyLeaveDuration,
                'clock_out_coordinates' => $coordinates,
                'clock_out_photo' => $photoPath ?? $attendance->clock_out_photo ?? 'manual_input.jpg',
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