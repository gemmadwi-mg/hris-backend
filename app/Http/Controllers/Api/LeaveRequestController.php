<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LeaveRequestController extends Controller
{
    /**
     * Tampilkan daftar pengajuan cuti.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = LeaveRequest::with(['employee', 'approver']);

        $employeeId = $user->employee_id ?? $user->employee?->employee_id;

        if ($user->hasRole('Employee') || $request->has('my_requests')) {
            $query->where('employee_id', $employeeId);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaveRequests = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $leaveRequests,
        ], 200);
    }

    /**
     * Ambil Sisa Cuti Karyawan Login untuk Tahun Berjalan
     */
    public function getLeaveBalances(Request $request)
    {
        $user = $request->user();
        $employeeId = $user->employee_id ?? $user->employee?->employee_id;
        $currentYear = date('Y');

        $defaultTypes = ['Cuti Tahunan', 'Cuti Sakit', 'Cuti Melahirkan', 'Cuti Alasan Penting'];

        foreach ($defaultTypes as $type) {
            EmployeeLeaveBalance::firstOrCreate(
                [
                    'employee_id' => $employeeId,
                    'leave_type' => $type,
                    'year' => $currentYear,
                ],
                [
                    'quota' => $type === 'Cuti Tahunan' ? 12 : ($type === 'Cuti Melahirkan' ? 90 : 14),
                    'used' => 0,
                ]
            );
        }

        $balances = EmployeeLeaveBalance::where('employee_id', $employeeId)
            ->where('year', $currentYear)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $balances,
        ], 200);
    }

    /**
     * Simpan Pengajuan Cuti (Dengan Validasi Sisa Kuota)
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $employeeId = $request->input('employee_id') ?? $user->employee_id ?? $user->employee?->employee_id;

        $request->merge([
            'leave_type' => $request->input('leave_type') ?? $request->input('leave_type_id'),
        ]);

        $validated = $request->validate([
            'leave_type' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);
        $requestedDays = $start->diffInDaysFiltered(fn (Carbon $date) => ! $date->isWeekend(), $end->copy()->addDay());

        if ($requestedDays <= 0) {
            return response()->json(['success' => false, 'message' => 'Tanggal pengajuan tidak valid.'], 422);
        }

        $currentYear = $start->format('Y');
        $balance = EmployeeLeaveBalance::where('employee_id', $employeeId)
            ->where('leave_type', $validated['leave_type'])
            ->where('year', $currentYear)
            ->first();

        if ($balance && $requestedDays > $balance->remaining) {
            return response()->json([
                'success' => false,
                'message' => "Sisa kuota {$validated['leave_type']} Anda tidak mencukupi. (Sisa: {$balance->remaining} Hari, Diajukan: {$requestedDays} Hari).",
            ], 422);
        }

        $attachmentPath = $request->hasFile('attachment')
            ? $request->file('attachment')->store('leave_attachments', 'public')
            : null;

        do {
            $leaveRequestId = 'LV'.rand(1000, 9999);
        } while (LeaveRequest::where('leave_request_id', $leaveRequestId)->exists());

        $leaveRequest = LeaveRequest::create([
            'leave_request_id' => $leaveRequestId,
            'employee_id' => $employeeId,
            'leave_type' => $validated['leave_type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'attachment' => $attachmentPath,
            'status' => 'Pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan cuti berhasil dikirim.',
            'data' => $leaveRequest,
        ], 201);
    }

    /**
     * Detail pengajuan cuti.
     */
    public function show($id)
    {
        $leaveRequest = LeaveRequest::with(['employee', 'approver'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $leaveRequest,
        ], 200);
    }

    /**
     * 🔄 UPDATE / PERSETUJUAN HRD: Potong Kuota Cuti & Auto-Sync ke Tabel Attendance
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Approved,Rejected',
            'rejection_reason' => 'required_if:status,Rejected|nullable|string',
        ]);

        $leaveRequest = LeaveRequest::with('employee')->findOrFail($id);
        $oldStatus = $leaveRequest->status;
        $newStatus = $request->status;

        $leaveRequest->update([
            'status' => $newStatus,
            'rejection_reason' => $request->rejection_reason,
            'approver_id' => $request->user()->employee_id ?? $request->user()->id,
            'processed_at' => now(),
        ]);

        // POTONG ATAU KEMBALIKAN SALDO CUTI
        $start = Carbon::parse($leaveRequest->start_date);
        $end = Carbon::parse($leaveRequest->end_date);
        $days = $start->diffInDaysFiltered(fn (Carbon $d) => ! $d->isWeekend(), $end->copy()->addDay());
        $year = $start->format('Y');

        $balance = EmployeeLeaveBalance::firstOrCreate(
            ['employee_id' => $leaveRequest->employee_id, 'leave_type' => $leaveRequest->leave_type, 'year' => $year],
            ['quota' => 12, 'used' => 0]
        );

        if ($oldStatus !== 'Approved' && $newStatus === 'Approved') {
            $balance->increment('used', $days);

            // AUTO-SYNC PRESENSI: PUSH DATA KE ATTENDANCES
            $currentDate = $start->copy();
            while ($currentDate->lte($end)) {
                if (! $currentDate->isWeekend()) {
                    $dateStr = $currentDate->format('Y-m-d');

                    $attendance = Attendance::firstOrNew([
                        'employee_id' => $leaveRequest->employee_id,
                        'date' => $dateStr,
                    ]);

                    if (! $attendance->exists) {
                        $attendance->attendance_id = 'AT'.date('ymd', strtotime($dateStr)).strtoupper(Str::random(2));
                        $attendance->branch_id = $leaveRequest->employee->branch_id ?? 'BRANCH01';
                    }

                    $attendance->attendance_status = 'Leave';
                    
                    // 🛡️ ISI PROPERTI TAMBAHAN UNTUK MENCEGAH ERROR MYSQL STRICT MODE
                    $attendance->clock_in_time = null;
                    $attendance->clock_out_time = null;
                    $attendance->clock_in_coordinates = null;
                    $attendance->clock_out_coordinates = null;
                    $attendance->clock_in_photo = null;
                    $attendance->clock_out_photo = null;
                    // $attendance->notes = 'Cuti Disetujui: ' . $leaveRequest->leave_type;

                    $attendance->save();
                }
                $currentDate->addDay();
            }
        } elseif ($oldStatus === 'Approved' && $newStatus !== 'Approved') {
            $balance->decrement('used', min($balance->used, $days));

            // HAPUS RECORD PRESENSI CUTI JIKA DIUBAH MENJADI REJECTED/PENDING
            $currentDate = $start->copy();
            while ($currentDate->lte($end)) {
                if (! $currentDate->isWeekend()) {
                    Attendance::where('employee_id', $leaveRequest->employee_id)
                        ->where('date', $currentDate->format('Y-m-d'))
                        ->where('attendance_status', 'Leave')
                        ->delete();
                }
                $currentDate->addDay();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Status pengajuan cuti berhasil diperbarui dan disinkronkan ke presensi.',
            'data' => $leaveRequest,
        ], 200);
    }

    /**
     * Batalkan pengajuan cuti (Hanya jika status masih Pending).
     */
    public function destroy($id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        if ($leaveRequest->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan cuti yang sudah diproses tidak dapat dibatalkan.',
            ], 422);
        }

        if ($leaveRequest->attachment && Storage::disk('public')->exists($leaveRequest->attachment)) {
            Storage::disk('public')->delete($leaveRequest->attachment);
        }

        $leaveRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan cuti berhasil dibatalkan.',
        ], 200);
    }
}