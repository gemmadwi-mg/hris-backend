<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee; // 1. Import Model Employee
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class ReferenceController extends Controller
{
    /**
     * Mengambil semua opsi dropdown untuk form Karyawan & Transaksi
     */
    public function getEmployeeFormOptions()
    {
        $roles = Role::select('role_id', 'role_name')->get();
        $companies = Company::select('company_id', 'company_name')->get();
        $branches = Branch::select('branch_id', 'branch_name')->get();
        $ptkpGroups = DB::table('ptkp_groups')->orderBy('ptkp_group_name', 'asc')->get();

        // 2. Ambil data Karyawan (ringkas: ID & Nama saja) untuk dropdown Payroll/Presensi
        $employees = Employee::select('employee_id', 'full_name', 'job_title', 'department')
            ->orderBy('full_name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'roles' => $roles,
                'companies' => $companies,
                'branches' => $branches,
                'ptkp_groups' => $ptkpGroups,
                'employees' => $employees, // 3. Masukkan ke response
            ],
        ], 200);
    }
}