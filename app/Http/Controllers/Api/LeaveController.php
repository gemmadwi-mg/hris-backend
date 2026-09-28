<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\User;
use App\Notifications\LeaveRequestedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class LeaveController extends Controller
{
    // Jangan lupa pastikan Leave model sudah di-import di atas:
    // use App\Models\Leave;

    public function index(Request $request)
    {
        // 1. PENJAGA GERBANG RBAC: Pastikan hanya HR atau Manager yang bisa mengakses
        if (! $request->user()->hasAnyRole(['HR', 'Manager'])) {
            return response()->json(['message' => 'Akses ditolak. Anda tidak memiliki izin.'], 403);
        }

        // 2. Ambil semua data cuti beserta profil karyawan yang mengajukan (Eager Loading)
        $leaves = Leave::with('employee')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $leaves], 200);
    }

    public function updateStatus(Request $request, Leave $leave)
    {
        // 1. PENJAGA GERBANG RBAC
        if (! $request->user()->hasAnyRole(['HR', 'Manager'])) {
            return response()->json(['message' => 'Akses ditolak. Anda tidak memiliki izin.'], 403);
        }

        // 2. Validasi input status yang dikirim (Hanya boleh 'Approved' atau 'Rejected')
        $request->validate([
            'status' => 'required|in:Approved,Rejected',
        ]);

        // 3. Update status cuti
        $leave->update([
            'status' => $request->status,
        ]);

        $statusIndo = $request->status === 'Approved' ? 'disetujui' : 'ditolak';

        return response()->json([
            'message' => "Pengajuan cuti berhasil {$statusIndo}.",
            'data' => $leave,
        ], 200);
    }

    // --- 1. UPDATE FUNGSI STORE ---
    public function store(Request $request)
    {
        $request->validate([
            'leave_type' => 'required|in:Tahunan,Sakit,Penting',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048', // Maks 2MB
        ]);

        $employee = $request->user()->employee;
        $documentPath = null;

        // Jika ada file yang diunggah, simpan di folder 'private/leaves'
        if ($request->hasFile('document')) {
            // Storage::put() akan menyimpannya di storage/app/private/leaves
            $documentPath = $request->file('document')->store('private/leaves');
        }

        $leave = Leave::create([
            'employee_id' => $employee->id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'document_path' => $documentPath,
            'status' => 'Pending',
        ]);

        // Ambil semua akun yang memiliki peran HR atau Manager
        $managers = User::role(['HR', 'Manager'])->get();

        // Kirim notifikasi ke mereka semua (Simpan ke DB & Siarkan ke Reverb)
        Notification::send($managers, new LeaveRequestedNotification($employee->nama_lengkap));

        return response()->json(['message' => 'Cuti berhasil diajukan', 'data' => $leave], 201);
    }

    // --- 2. TAMBAH FUNGSI DOWNLOAD AMAN ---
    public function downloadDocument(Request $request, Leave $leave)
    {
        // Hanya yang mengajukan ATAU HR/Manager yang boleh melihat surat dokter ini
        $isOwner = $request->user()->employee && $request->user()->employee->id === $leave->employee_id;
        $isManager = $request->user()->hasAnyRole(['HR', 'Manager']);

        if (! $isOwner && ! $isManager) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if (! $leave->document_path || ! Storage::exists($leave->document_path)) {
            return response()->json(['message' => 'Dokumen tidak ditemukan.'], 404);
        }

        // Return file langsung sebagai unduhan/tampilan
        return Storage::download($leave->document_path);
    }

    // Fungsi tambahan untuk melihat riwayat cuti karyawan itu sendiri
    public function myLeaves(Request $request)
    {
        $employee = $request->user()->employee;

        if (! $employee) {
            return response()->json(['data' => []], 200);
        }

        $leaves = Leave::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $leaves], 200);
    }
}
