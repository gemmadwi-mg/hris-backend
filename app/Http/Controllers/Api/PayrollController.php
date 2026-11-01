<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Overtime;
use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $query = Payroll::with(['employee']);
        $user = $request->user();

        // 🔒 PERBAIKAN UTAMA: Filter hanya jika user adalah Karyawan biasa (Bukan Admin / HR)
        if ($user->hasRole('Employee')) {
            $employeeId = $user->employee_id ?? $user->employee?->employee_id;
            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            }
        }

        if ($request->filled('month')) {
            $query->where('period_month', $request->month);
        }
        if ($request->filled('year')) {
            $query->where('period_year', $request->year);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('employee', function ($empQ) use ($search) {
                    $empQ->where('full_name', 'like', "%{$search}%");
                })->orWhere('payroll_id', 'like', "%{$search}%");
            });
        }

        $payrolls = $query->orderBy('period_year', 'desc')
            ->orderBy('period_month', 'desc')
            ->paginate(10);

        return response()->json(['success' => true, 'data' => $payrolls], 200);
    }

    // Fungsi Preview (Live Calculation dengan TER dan BPJS)
    public function preview(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'period_month' => 'required|integer|between:1,12',
            'period_year' => 'required|integer',
            'position_allowance' => 'nullable|numeric|min:0',
            'meal_allowance' => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
        ]);

        $employeeId = $request->employee_id;
        $period = $request->period_year.'-'.str_pad($request->period_month, 2, '0', STR_PAD_LEFT);

        $employee = Employee::find($employeeId);
        $basicSalary = $employee->basic_salary ?? 0;
        $company = DB::table('companies')->first();

        $alJabatan = $request->position_allowance ?? 0;
        $alMakan = $request->meal_allowance ?? 0;
        $alTransport = $request->transport_allowance ?? 0;
        $totalAllowances = $alJabatan + $alMakan + $alTransport;

        // Lembur & Kehadiran
        $overtimeData = Overtime::where('employee_id', $employeeId)
            ->where('status', 'Approved')->where('overtime_date', 'like', "$period-%")->get();
        $totalOvertime = $overtimeData->sum('total_incentive');

        $overtimeHours = $overtimeData->sum(function ($ot) {
            return $ot->duration ? (int) substr($ot->duration, 0, 2) : 0;
        });

        $absentCount = Attendance::where('employee_id', $employeeId)
            ->where('date', 'like', "$period-%")->where('attendance_status', 'Absent')->count();
        $lateCount = Attendance::where('employee_id', $employeeId)
            ->where('date', 'like', "$period-%")->where('clock_in_status', 'Late')->count();

        $absenceDeduction = $absentCount * 100000;
        $latePenalty = $company ? $company->late_penalty : 50000;
        $lateDeduction = $lateCount * $latePenalty;

        // BPJS
        $healthPct = $company ? $company->bpjs_health_percent : 0.01;
        $jhtPct = $company ? $company->bpjs_jht_percent : 0.02;
        $jpPct = $company ? $company->bpjs_jp_percent : 0.01;
        $healthLimit = $company ? $company->bpjs_health_salary_limit : 12000000;

        $baseKesJp = $basicSalary + $alJabatan;
        $baseJht = $basicSalary;

        $bpjsHealth = min($baseKesJp, $healthLimit) * $healthPct;
        $bpjsJht = $baseJht * $jhtPct;
        $bpjsJp = $baseKesJp * $jpPct;

        // PPh21 TER
        $grossIncome = $basicSalary + $totalAllowances + $totalOvertime;
        $terCategoryId = 'A';
        $ptkpLabel = 'TK/0';

        if (Schema::hasColumn('employees', 'ptkp_group_id') && $employee->ptkp_group_id) {
            $ptkpGroup = DB::table('ptkp_groups')->where('ptkp_group_id', $employee->ptkp_group_id)->first();
            if ($ptkpGroup) {
                $terCategoryId = $ptkpGroup->ter_category_id;
                $ptkpLabel = $employee->ptkp_group_id;
            }
        }

        $terRate = DB::table('ter_rates')->where('ter_category_id', $terCategoryId)
            ->where('lower_limit', '<=', $grossIncome)->where('upper_limit', '>=', $grossIncome)->first();
        $taxRate = $terRate ? $terRate->rate : 0;
        $pph21Deduction = $grossIncome * $taxRate;

        $calcDetails = [
            'overtime_text' => $overtimeHours > 0 ? "{$overtimeHours} jam (".$overtimeData->count().' sesi)' : 'Tidak ada lembur',
            'absence_text' => $absentCount > 0 ? "{$absentCount} hari x Rp 100.000/hari" : '-',
            'late_text' => $lateCount > 0 ? "{$lateCount} hari x Rp ".number_format($latePenalty, 0, ',', '.').'/hari' : '-',
            'bpjs_kes_text' => ($healthPct * 100).'% dari gaji pokok + tunjangan tetap (Rp '.number_format($baseKesJp, 0, ',', '.').')',
            'bpjs_jht_text' => ($jhtPct * 100).'% dari gaji pokok (Rp '.number_format($baseJht, 0, ',', '.').')',
            'bpjs_jp_text' => ($jpPct * 100).'% dari gaji pokok + tunjangan tetap (Rp '.number_format($baseKesJp, 0, ',', '.').')',
            'tax_text' => ($taxRate * 100).'% dari penghasilan bruto Rp '.number_format($grossIncome, 0, ',', '.')."\nPTKP: {$ptkpLabel} (Kategori {$terCategoryId})",
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'basic_salary' => $basicSalary,
                'default_position_allowance' => $employee->position_allowance ?? 0,
                'default_meal_allowance' => $employee->meal_allowance ?? 0,
                'default_transport_allowance' => $employee->transport_allowance ?? 0,
                'total_overtime' => $totalOvertime,
                'absence_deduction' => $absenceDeduction,
                'late_deduction' => $lateDeduction,
                'bpjs_health' => $bpjsHealth,
                'bpjs_jht' => $bpjsJht,
                'bpjs_jp' => $bpjsJp,
                'pph21_deduction' => $pph21Deduction,
                'calculation_details' => $calcDetails,
            ],
        ], 200);
    }

    private function calculatePayroll(&$data)
    {
        $data['gross_salary'] = ($data['basic_salary'] ?? 0) +
                                ($data['position_allowance'] ?? 0) +
                                ($data['meal_allowance'] ?? 0) +
                                ($data['transport_allowance'] ?? 0) +
                                ($data['total_overtime'] ?? 0);

        $data['total_deductions'] = ($data['absence_deduction'] ?? 0) +
                                    ($data['late_deduction'] ?? 0) +
                                    ($data['bpjs_health'] ?? 0) +
                                    ($data['bpjs_jht'] ?? 0) +
                                    ($data['bpjs_jp'] ?? 0) +
                                    ($data['pph21_deduction'] ?? 0);

        $data['net_salary'] = $data['gross_salary'] - $data['total_deductions'] + ($data['adjustment'] ?? 0);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $this->calculatePayroll($data);

        $prefix = 'PAY-';
        $latest = Payroll::withTrashed()->where('payroll_id', 'like', "{$prefix}%")->orderBy('payroll_id', 'desc')->first();
        $nextNumber = $latest ? ((int) substr($latest->payroll_id, 4)) + 1 : 1;

        do {
            $newId = $prefix.str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            $checkExists = Payroll::withTrashed()->where('payroll_id', $newId)->exists();
            if ($checkExists) {
                $nextNumber++;
            }
        } while ($checkExists);

        $data['payroll_id'] = $newId;
        $data['status'] = 'Draft';

        $payroll = Payroll::create($data);

        return response()->json(['success' => true, 'data' => $payroll], 201);
    }

    public function show(Request $request, $id)
    {
        $payroll = Payroll::with('employee')->findOrFail($id);

        $user = $request->user();
        if ($user->hasRole('Employee')) {
            $employeeId = $user->employee_id ?? $user->employee?->employee_id;
            if ($employeeId && $payroll->employee_id !== $employeeId) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
            }
        }

        return response()->json(['success' => true, 'data' => $payroll], 200);
    }

    public function update(Request $request, $id)
    {
        $payroll = Payroll::findOrFail($id);
        $data = array_merge($payroll->toArray(), $request->all());

        if ($request->hasAny(['basic_salary', 'position_allowance', 'meal_allowance', 'transport_allowance', 'total_overtime', 'absence_deduction', 'late_deduction', 'bpjs_health', 'bpjs_jht', 'bpjs_jp', 'pph21_deduction', 'adjustment'])) {
            $this->calculatePayroll($data);
        }

        $payroll->update($data);

        return response()->json(['success' => true, 'data' => $payroll], 200);
    }

    public function destroy($id)
    {
        $payroll = Payroll::findOrFail($id);
        $payroll->delete();

        return response()->json(['success' => true, 'message' => 'Data penggajian dihapus'], 200);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Draft,Submitted,Verified,Approved,Rejected',
            'rejection_note' => 'nullable|string',
        ]);

        $payroll = Payroll::findOrFail($id);
        $payroll->status = $request->status;

        if ($request->status === 'Rejected') {
            $payroll->rejection_note = $request->rejection_note;
        } else {
            $payroll->rejection_note = null;
        }

        $payroll->save();

        return response()->json([
            'success' => true,
            'message' => 'Status gaji berhasil diperbarui',
            'data' => $payroll,
        ], 200);
    }

    public function exportPdf(Request $request, $id)
    {
        $payroll = Payroll::with('employee')->findOrFail($id);

        $user = $request->user();
        if ($user->hasRole('Employee')) {
            $employeeId = $user->employee_id ?? $user->employee?->employee_id;
            if ($employeeId && $payroll->employee_id !== $employeeId) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
            }
        }

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $monthName = $months[$payroll->period_month] ?? $payroll->period_month;

        $pdf = Pdf::loadView('pdf.payslip', compact('payroll', 'monthName'))
            ->setPaper('a4', 'portrait');

        $fileName = "Slip_Gaji_{$payroll->employee->full_name}_{$payroll->period_month}_{$payroll->period_year}.pdf";

        return $pdf->download($fileName);
    }

    public function generateMassal(Request $request)
    {
        $request->validate([
            'period_month' => 'required|integer|between:1,12',
            'period_year' => 'required|integer',
        ]);

        $month = $request->period_month;
        $year = $request->period_year;
        $period = $year.'-'.str_pad($month, 2, '0', STR_PAD_LEFT);

        $existingEmployeeIds = Payroll::where('period_month', $month)
            ->where('period_year', $year)
            ->pluck('employee_id')
            ->toArray();

        $employees = Employee::whereNotIn('employee_id', $existingEmployeeIds)->get();

        if ($employees->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Semua karyawan sudah di-generate gajinya untuk bulan ini!',
            ], 400);
        }

        $company = DB::table('companies')->first();
        $generatedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                $basicSalary = $employee->basic_salary ?? 0;
                $alJabatan = $employee->position_allowance ?? 0;
                $alMakan = $employee->meal_allowance ?? 0;
                $alTransport = $employee->transport_allowance ?? 0;
                $totalAllowances = $alJabatan + $alMakan + $alTransport;

                $overtimeData = Overtime::where('employee_id', $employee->employee_id)
                    ->where('status', 'Approved')->where('overtime_date', 'like', "$period-%")->get();
                $totalOvertime = $overtimeData->sum('total_incentive');
                $overtimeHours = $overtimeData->sum(function ($ot) {
                    return $ot->duration ? (int) substr($ot->duration, 0, 2) : 0;
                });

                $absentCount = Attendance::where('employee_id', $employee->employee_id)
                    ->where('date', 'like', "$period-%")->where('attendance_status', 'Absent')->count();
                $lateCount = Attendance::where('employee_id', $employee->employee_id)
                    ->where('date', 'like', "$period-%")->where('clock_in_status', 'Late')->count();

                $absenceDeduction = $absentCount * 100000;
                $latePenalty = $company ? $company->late_penalty : 50000;
                $lateDeduction = $lateCount * $latePenalty;

                $healthPct = $company ? $company->bpjs_health_percent : 0.01;
                $jhtPct = $company ? $company->bpjs_jht_percent : 0.02;
                $jpPct = $company ? $company->bpjs_jp_percent : 0.01;
                $healthLimit = $company ? $company->bpjs_health_salary_limit : 12000000;

                $baseKesJp = $basicSalary + $alJabatan;
                $baseJht = $basicSalary;

                $bpjsHealth = min($baseKesJp, $healthLimit) * $healthPct;
                $bpjsJht = $baseJht * $jhtPct;
                $bpjsJp = $baseKesJp * $jpPct;

                $grossIncome = $basicSalary + $totalAllowances + $totalOvertime;
                $terCategoryId = 'A';
                $ptkpLabel = 'TK/0';
                if ($employee->ptkp_group_id) {
                    $ptkpGroup = DB::table('ptkp_groups')->where('ptkp_group_id', $employee->ptkp_group_id)->first();
                    if ($ptkpGroup) {
                        $terCategoryId = $ptkpGroup->ter_category_id;
                        $ptkpLabel = $employee->ptkp_group_id;
                    }
                }
                $terRate = DB::table('ter_rates')->where('ter_category_id', $terCategoryId)
                    ->where('lower_limit', '<=', $grossIncome)->where('upper_limit', '>=', $grossIncome)->first();
                $taxRate = $terRate ? $terRate->rate : 0;
                $pph21Deduction = $grossIncome * $taxRate;

                $calcDetails = [
                    'overtime_text' => $overtimeHours > 0 ? "{$overtimeHours} jam (".$overtimeData->count().' sesi)' : 'Tidak ada lembur',
                    'absence_text' => $absentCount > 0 ? "{$absentCount} hari x Rp 100.000/hari" : '-',
                    'late_text' => $lateCount > 0 ? "{$lateCount} hari x Rp ".number_format($latePenalty, 0, ',', '.').'/hari' : '-',
                    'bpjs_kes_text' => ($healthPct * 100).'% dari gaji pokok + tunjangan tetap (Rp '.number_format($baseKesJp, 0, ',', '.').')',
                    'bpjs_jht_text' => ($jhtPct * 100).'% dari gaji pokok (Rp '.number_format($baseJht, 0, ',', '.').')',
                    'bpjs_jp_text' => ($jpPct * 100).'% dari gaji pokok + tunjangan tetap (Rp '.number_format($baseKesJp, 0, ',', '.').')',
                    'tax_text' => ($taxRate * 100).'% dari penghasilan bruto Rp '.number_format($grossIncome, 0, ',', '.')."\nPTKP: {$ptkpLabel} (Kategori {$terCategoryId})",
                ];

                $totalDeductions = $absenceDeduction + $lateDeduction + $bpjsHealth + $bpjsJht + $bpjsJp + $pph21Deduction;
                $netSalary = $grossIncome - $totalDeductions;

                $payrollId = 'PAY-'.$month.substr($year, -2).'-'.$employee->employee_id;

                Payroll::create([
                    'payroll_id' => $payrollId,
                    'period_month' => $month,
                    'period_year' => $year,
                    'status' => 'Draft',
                    'employee_id' => $employee->employee_id,
                    'basic_salary' => $basicSalary,
                    'position_allowance' => $alJabatan,
                    'meal_allowance' => $alMakan,
                    'transport_allowance' => $alTransport,
                    'total_overtime' => $totalOvertime,
                    'gross_salary' => $grossIncome,
                    'absence_deduction' => $absenceDeduction,
                    'late_deduction' => $lateDeduction,
                    'bpjs_health' => $bpjsHealth,
                    'bpjs_jht' => $bpjsJht,
                    'bpjs_jp' => $bpjsJp,
                    'pph21_deduction' => $pph21Deduction,
                    'calculation_details' => $calcDetails,
                    'adjustment' => 0,
                    'total_deductions' => $totalDeductions,
                    'net_salary' => $netSalary,
                ]);

                $generatedCount++;
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil men-generate $generatedCount draft slip gaji!",
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: '.$e->getMessage(),
            ], 500);
        }
    }

    public function disburse($id)
    {
        $payroll = Payroll::with('employee')->findOrFail($id);

        if ($payroll->status !== 'Approved') {
            return response()->json(['success' => false, 'message' => 'Gaji belum disetujui!'], 403);
        }
        if ($payroll->is_transferred) {
            return response()->json(['success' => false, 'message' => 'Gaji ini sudah dicairkan sebelumnya!'], 400);
        }

        $pdf = Pdf::loadView('pdf.payslip', compact('payroll'))->setPaper('A4', 'portrait');
        $pdfContent = $pdf->output();

        try {
            Mail::send([], [], function ($message) use ($payroll, $pdfContent) {
                $message->to($payroll->employee->email)
                    ->subject("Slip Gaji {$payroll->period_month}/{$payroll->period_year} - {$payroll->employee->full_name}")
                    ->html("
                        <h3>Halo, {$payroll->employee->full_name}</h3>
                        <p>Gaji Anda untuk periode <strong>{$payroll->period_month}/{$payroll->period_year}</strong> telah berhasil ditransfer ke rekening <strong>{$payroll->employee->bank_name} ({$payroll->employee->account_number})</strong>.</p>
                        <p>Total Take Home Pay: <strong>Rp ".number_format($payroll->net_salary, 0, ',', '.').'</strong></p>
                        <p>Terlampir rincian slip gaji Anda dalam format PDF.</p>
                        <br>
                        <p>Salam hangat,<br>Tim HR & Finance</p>
                    ')
                    ->attachData($pdfContent, "Slip_Gaji_{$payroll->period_month}_{$payroll->period_year}.pdf", [
                        'mime' => 'application/pdf',
                    ]);
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengirim email: '.$e->getMessage()], 500);
        }

        $payroll->is_transferred = true;
        $payroll->save();

        return response()->json([
            'success' => true,
            'message' => 'Gaji berhasil dicairkan dan Slip PDF telah dikirim ke email karyawan.',
        ], 200);
    }
}