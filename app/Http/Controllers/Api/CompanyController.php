<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function show()
    {
        $company = Company::first();
        
        if (!$company) {
            return response()->json(['message' => 'Data perusahaan belum diatur'], 404);
        }

        return response()->json(['data' => $company], 200);
    }

    public function update(Request $request)
    {
        if (! $request->user()->hasAnyRole(['HR', 'Manager'])) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        // Validasi menggunakan field bahasa Inggris
        $validatedData = $request->validate([
            'company_name'             => 'required|string|max:50',
            'email'                    => 'required|email|max:50',
            'phone_number'             => 'required|string|max:20',
            'clock_in_time'            => 'required',
            'clock_out_time'           => 'required',
            'late_penalty'             => 'required|numeric|min:0',
            'bpjs_health_percent'      => 'required|numeric|min:0',
            'bpjs_jht_percent'         => 'required|numeric|min:0',
            'bpjs_jp_percent'          => 'required|numeric|min:0',
            'bpjs_health_salary_limit' => 'required|numeric|min:0',
            'bpjs_jp_salary_limit'     => 'required|numeric|min:0',
        ]);

        $company = Company::first();
        $company->update($validatedData);

        return response()->json([
            'message' => 'Pengaturan Perusahaan & Kebijakan Gaji berhasil diperbarui!',
            'data' => $company
        ], 200);
    }
}