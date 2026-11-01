<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Overtime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OvertimeController extends Controller
{
    /**
     * Tampilkan daftar pengajuan lembur.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Overtime::with(['employee', 'attendance', 'approver']);

        // Karyawan biasa hanya melihat data pengajuan lembur milik sendiri
        $employeeId = $user->employee_id ?? $user->employee?->employee_id;
        if ($user->role === 'Employee' || $request->has('my_requests')) {
            $query->where('employee_id', $employeeId);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('employee', function ($emp) use ($search) {
                    $emp->where('full_name', 'like', "%{$search}%");
                })->orWhere('overtime_id', 'like', "%{$search}%");
            });
        }

        $overtimes = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json(['success' => true, 'data' => $overtimes], 200);
    }

    /**
     * Simpan pengajuan lembur baru.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $employeeId = $request->input('employee_id') ?? $user->employee_id ?? $user->employee?->employee_id;

        if (!$employeeId) {
            return response()->json(['success' => false, 'message' => 'Data karyawan tidak ditemukan.'], 422);
        }

        // 1. Validasi Input
        $validated = $request->validate([
            'overtime_date'    => 'required|date',
            'duration'         => 'required|string', // Format HH:MM dari frontend
            'task_description' => 'required|string',
            'attachment'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // 2. Cari / Buat Record Absensi untuk Tanggal Lembur Tersebut
        $attendance = Attendance::firstOrCreate(
            [
                'employee_id' => $employeeId,
                'date'        => $validated['overtime_date'],
            ],
            [
                'attendance_id'     => 'AT' . date('ymd', strtotime($validated['overtime_date'])) . strtoupper(Str::random(2)),
                'branch_id'         => $user->employee->branch_id ?? 'BRANCH01',
                'attendance_status' => 'Present',
            ]
        );

        // 3. Format Durasi (HH:MM:SS) & Hitung Estimasi Insentif (Contoh: Rp 25.000 / Jam)
        $durationStr = strlen($validated['duration']) === 5 ? $validated['duration'] . ':00' : $validated['duration'];
        $parts = explode(':', $durationStr);
        $hours = (int)($parts[0] ?? 0) + ((int)($parts[1] ?? 0) / 60);
        $ratePerHour = 25000; // Standar tarif lembur per jam
        $calculatedIncentive = round($hours * $ratePerHour);

        // 4. Auto Generate Overtime ID (OT0001)
        do {
            $latest = Overtime::withTrashed()->orderBy('created_at', 'desc')->first();
            $nextNum = $latest ? ((int) substr($latest->overtime_id, 2)) + 1 : 1;
            $overtimeId = 'OT' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        } while (Overtime::where('overtime_id', $overtimeId)->exists());

        // 5. Upload Berkas Lampiran / SPKL Jika Ada
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('overtime_attachments', 'public');
        }

        $overtime = Overtime::create([
            'overtime_id'      => $overtimeId,
            'employee_id'      => $employeeId,
            'attendance_id'    => $attendance->attendance_id,
            'overtime_date'    => $validated['overtime_date'],
            'duration'         => $durationStr,
            'total_incentive'  => $request->input('total_incentive') ?? $calculatedIncentive,
            'task_description' => $validated['task_description'],
            'attachment'       => $attachmentPath,
            'status'           => 'Pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan lembur berhasil dikirim.',
            'data'    => $overtime
        ], 201);
    }

    public function show($id)
    {
        $overtime = Overtime::with(['employee', 'attendance', 'approver'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $overtime], 200);
    }

    /**
     * Persetujuan / Penolakan Lembur oleh HRD.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status'           => 'required|in:Approved,Rejected',
            'rejection_reason' => 'required_if:status,Rejected|nullable|string',
            'total_incentive'  => 'nullable|numeric',
        ]);

        $overtime = Overtime::findOrFail($id);
        $user = $request->user();

        $updateData = [
            'status'           => $request->status,
            'rejection_reason' => $request->rejection_reason,
            'approver_id'      => $user->employee_id ?? $user->id,
            'processed_at'     => now(),
        ];

        if ($request->filled('total_incentive')) {
            $updateData['total_incentive'] = $request->total_incentive;
        }

        $overtime->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Status pengajuan lembur berhasil diperbarui.',
            'data'    => $overtime
        ], 200);
    }

    /**
     * Batalkan pengajuan lembur (Hanya jika status masih Pending).
     */
    public function destroy($id)
    {
        $overtime = Overtime::findOrFail($id);

        if ($overtime->status !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'Pengajuan yang sudah diproses tidak dapat dibatalkan.'], 422);
        }

        if ($overtime->attachment && Storage::disk('public')->exists($overtime->attachment)) {
            Storage::disk('public')->delete($overtime->attachment);
        }

        $overtime->delete();

        return response()->json(['success' => true, 'message' => 'Pengajuan lembur berhasil dibatalkan.'], 200);
    }
}