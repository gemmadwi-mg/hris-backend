<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;

class PayrollController extends Controller
{
    public function downloadPayslip(Employee $employee)
    {
        // Muat data relasi untuk ditampilkan di slip
        $employee->load('position.department');

        // Ekstrak data dengan aman menggunakan Null-Safe Operator
        // Sesuaikan 'gaji_pokok' dengan nama kolom yang ada di database Anda
        $gaji = $employee->position?->gaji_pokok ?? 5000000; 
        $jabatan = $employee->position?->nama_jabatan ?? 'Staff';
        $departemen = $employee->position?->department?->nama_departemen ?? 'Umum';

        // Siapkan data untuk disuntikkan ke template HTML/Blade
        $data = [
            'employee' => $employee,
            'gaji_pokok' => $gaji,
            'jabatan' => $jabatan,
            'departemen' => $departemen,
            'bulan_tahun' => date('F Y'),
        ];

        // Render view blade menjadi PDF
        $pdf = Pdf::loadView('pdf.payslip', $data);

        // Return file PDF untuk diunduh
        return $pdf->download("slip_gaji_{$employee->nip}.pdf");
    }
}
