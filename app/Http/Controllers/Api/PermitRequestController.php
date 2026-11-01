<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance; // Import Model Attendance
use App\Models\PermitRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PermitRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = PermitRequest::with(['employee', 'approver']);

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
                })->orWhere('permit_request_id', 'like', "%{$search}%");
            });
        }

        $permits = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json(['success' => true, 'data' => $permits], 200);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $employeeId = $request->input('employee_id') ?? $user->employee_id ?? $user->employee?->employee_id;

        $startDate = $request->input('start_date') ?? $request->input('permit_date');
        $endDate = $request->input('end_date') ?? $startDate;
        $description = $request->input('description') ?? $request->input('reason');

        $request->merge([
            'employee_id' => $employeeId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'description' => $description,
        ]);

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'permit_type' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        do {
            $latest = PermitRequest::withTrashed()->orderBy('created_at', 'desc')->first();
            $nextNum = $latest ? ((int) substr($latest->permit_request_id, 2)) + 1 : 1;
            $permitRequestId = 'PR'.str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        } while (PermitRequest::where('permit_request_id', $permitRequestId)->exists());

        $validated['permit_request_id'] = $permitRequestId;
        $validated['status'] = 'Pending';

        if ($request->hasFile('attachment')) {
            $validated['attachment'] = $request->file('attachment')->store('permit_attachments', 'public');
        }

        $permit = PermitRequest::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan izin berhasil dikirim.',
            'data' => $permit,
        ], 201);
    }

    public function show($id)
    {
        $permit = PermitRequest::with(['employee', 'approver'])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $permit], 200);
    }

    /**
     * Persetujuan HRD: Auto-Sync status Izin ke Tabel Attendance
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Approved,Rejected',
            'rejection_reason' => 'required_if:status,Rejected|nullable|string',
        ]);

        $permit = PermitRequest::with('employee')->findOrFail($id);
        $oldStatus = $permit->status;
        $newStatus = $request->status;

        $permit->update([
            'status' => $newStatus,
            'rejection_reason' => $request->rejection_reason,
            'approver_id' => $request->user()->employee_id ?? $request->user()->id,
            'processed_at' => now(),
        ]);

        $start = Carbon::parse($permit->start_date);
        $end = Carbon::parse($permit->end_date);

        // JIKA DI-APPROVE: BUAT/UPDATE PRESENSI MENJADI 'Permit'
        if ($oldStatus !== 'Approved' && $newStatus === 'Approved') {
            $currentDate = $start->copy();
            while ($currentDate->lte($end)) {
                if (! $currentDate->isWeekend()) {
                    $dateStr = $currentDate->format('Y-m-d');

                    $attendance = Attendance::firstOrNew([
                        'employee_id' => $permit->employee_id,
                        'date' => $dateStr,
                    ]);

                    if (! $attendance->exists) {
                        $attendance->attendance_id = 'AT'.date('ymd', strtotime($dateStr)).strtoupper(Str::random(2));
                        $attendance->branch_id = $permit->employee->branch_id ?? 'BRANCH01';
                    }

                    $attendance->attendance_status = 'Permit';
                    $attendance->save();
                }
                $currentDate->addDay();
            }
        }
        // JIKA DIUBAH DARI APPROVED KE REJECTED: HAPUS RECORD PRESENSI IZIN
        elseif ($oldStatus === 'Approved' && $newStatus !== 'Approved') {
            $currentDate = $start->copy();
            while ($currentDate->lte($end)) {
                if (! $currentDate->isWeekend()) {
                    Attendance::where('employee_id', $permit->employee_id)
                        ->where('date', $currentDate->format('Y-m-d'))
                        ->where('attendance_status', 'Permit')
                        ->delete();
                }
                $currentDate->addDay();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Status pengajuan izin berhasil diperbarui dan disinkronkan ke presensi.',
            'data' => $permit,
        ], 200);
    }

    public function destroy($id)
    {
        $permit = PermitRequest::findOrFail($id);
        if ($permit->status !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'Pengajuan yang sudah diproses tidak dapat dihapus.'], 422);
        }

        $permit->delete();

        return response()->json(['success' => true, 'message' => 'Data pengajuan izin berhasil dibatalkan.'], 200);
    }
}