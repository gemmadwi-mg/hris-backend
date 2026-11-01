<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller implements HasMiddleware
{
    /**
     * Daftarkan Middleware Permission untuk RBAC
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view-any-employee', only: ['index']),
            new Middleware('permission:view-employee', only: ['show']),
            new Middleware('permission:create-employee', only: ['store']),
            new Middleware('permission:edit-employee', only: ['update']),
            new Middleware('permission:delete-employee', only: ['destroy']),
        ];
    }

    /**
     * Menampilkan semua data karyawan (Read)
     */
    public function index(Request $request)
    {
        // 1. Mulai query dasar (jangan lupa Eager Load relasi agar tidak lambat/N+1 issue)
        $query = Employee::with(['role', 'company', 'branch']);

        // 2. Fitur Pencarian (Search berdasarkan Nama, ID, atau NIK)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        // 3. Fitur Filter (Berdasarkan Status Karyawan)
        if ($request->filled('status')) {
            $query->where('employment_status', $request->status);
        }

        // 4. Pagination (Ambil 10 data per halaman, urutkan dari yang terbaru)
        $employees = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $employees,
        ], 200);
    }

    /**
     * Menyimpan data karyawan baru (Create)
     */
    /**
     * Menyimpan data karyawan baru (Create)
     */
    public function store(Request $request)
    {
        // 1. Validasi Super Lengkap Sesuai Migrasi
        $validator = Validator::make($request->all(), [
            // Foreign Keys & Relasi
            'role_id' => 'nullable|exists:roles,role_id',
            'company_id' => 'required|exists:companies,company_id',
            'branch_id' => 'nullable|exists:branches,branch_id',
            'ptkp_group_id' => 'nullable|string|max:3',

            // Data Pribadi & Autentikasi
            'nik' => 'required|string|max:20|unique:employees,nik',
            'full_name' => 'required|string|max:50',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:Male,Female',
            'address' => 'required|string',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'required|email|max:50|unique:employees,email',
            'password' => 'required|string|min:6',

            // Data Pekerjaan
            'job_title' => 'nullable|string|max:30',
            'department' => 'nullable|string|max:50',
            'employment_status' => 'nullable|in:Permanent,Contract,Internship,Freelance',
            'join_date' => 'nullable|date',
            'annual_leave_quota' => 'nullable|integer|min:0',

            // Data Gaji & Tunjangan
            'basic_salary' => 'nullable|numeric|min:0',
            'position_allowance' => 'nullable|numeric|min:0',
            'meal_allowance' => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',

            // Data Bank & BPJS
            'bank_name' => 'nullable|string|max:50',
            'account_number' => 'nullable|string|max:50',
            'account_holder_name' => 'nullable|string|max:50',
            'bpjs_health_number' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        // 2. Auto-Generate Employee ID (E0001, dst)
        $lastEmployee = Employee::withTrashed()->orderBy('employee_id', 'desc')->first();
        if ($lastEmployee) {
            $lastNumber = (int) substr($lastEmployee->employee_id, 1);
            $newId = 'E'.str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newId = 'E0001';
        }

        // 3. Persiapan Data & Hashing Password
        $data = $request->all();
        $data['employee_id'] = $newId;
        $data['password'] = Hash::make($request->password);

        // Berikan nilai default 0 untuk tunjangan jika dikosongkan (mencegah error null constraint)
        $data['position_allowance'] = $request->position_allowance ?? 0;
        $data['meal_allowance'] = $request->meal_allowance ?? 0;
        $data['transport_allowance'] = $request->transport_allowance ?? 0;
        $data['annual_leave_quota'] = $request->annual_leave_quota ?? 12;

        // 4. Simpan ke Database
        $employee = Employee::create($data);

        // 5. Load Relasi untuk Response Vue
        $employee->load(['role', 'company', 'branch']);

        return response()->json([
            'success' => true,
            'message' => 'Karyawan berhasil ditambahkan',
            'data' => $employee,
        ], 201);
    }

    /**
     * Menampilkan detail satu karyawan spesifik (Read Detail)
     */
    public function show(string $id)
    {
        $employee = Employee::with(['role', 'company', 'branch'])->find($id);

        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail karyawan berhasil diambil',
            'data' => $employee,
        ], 200);
    }

    /**
     * Memperbarui data karyawan (Update)
     */
    public function update(Request $request, string $id)
    {
        $employee = Employee::find($id);

        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'role_id' => 'sometimes|required|exists:roles,role_id',
            'nik' => 'sometimes|required|unique:employees,nik,'.$id.',employee_id',
            'email' => 'sometimes|required|email|unique:employees,email,'.$id.',employee_id',
            'full_name' => 'sometimes|required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->all();

        // Cek jika password ikut di-update
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        $employee->update($data);
        $employee->load(['role', 'company', 'branch']);

        return response()->json([
            'success' => true,
            'message' => 'Data karyawan berhasil diperbarui',
            'data' => $employee,
        ], 200);
    }

    /**
     * Menghapus data karyawan (Delete - Soft Delete)
     */
    public function destroy(string $id)
    {
        $employee = Employee::find($id);

        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan',
            ], 404);
        }

        $employee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Karyawan berhasil dihapus',
        ], 200);
    }
}
