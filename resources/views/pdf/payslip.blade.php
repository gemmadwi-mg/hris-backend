<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Gaji - {{ $payroll->employee->full_name ?? '' }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #1f2937;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        /* HEADER / KOP SURAT */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #111827;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .company-title {
            font-size: 18px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0;
        }
        .company-sub {
            font-size: 9px;
            color: #6b7280;
            margin-top: 2px;
        }
        .slip-badge {
            background-color: #f3f4f6;
            color: #111827;
            font-weight: bold;
            font-size: 10px;
            padding: 3px 8px;
            border-radius: 3px;
            text-transform: uppercase;
        }

        /* INFORMASI KARYAWAN */
        .info-table {
            width: 100%;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .info-table td {
            font-size: 10px;
            padding: 2px 4px;
            vertical-align: top;
        }
        .label {
            color: #6b7280;
            width: 100px;
        }

        /* DUA KOLOM PENDAPATAN & POTONGAN */
        .layout-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .layout-col {
            width: 49%;
            vertical-align: top;
        }
        .col-space {
            width: 2%;
        }

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
        }
        .card-header-green {
            background-color: #ecfdf5;
            border-bottom: 1px solid #d1fae5;
            padding: 6px 10px;
            font-weight: bold;
            color: #065f46;
            font-size: 10px;
            text-transform: uppercase;
        }
        .card-header-red {
            background-color: #fef2f2;
            border-bottom: 1px solid #fee2e2;
            padding: 6px 10px;
            font-weight: bold;
            color: #991b1b;
            font-size: 10px;
            text-transform: uppercase;
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
        }
        .item-table td {
            padding: 5px 8px;
            font-size: 10px;
            border-bottom: 1px dashed #f3f4f6;
        }
        .item-table tr:last-child td {
            border-bottom: none;
        }
        .text-right {
            text-align: right;
        }
        .font-mono {
            font-family: 'Courier', monospace;
            font-weight: bold;
        }
        .text-green { color: #16a34a; }
        .text-red { color: #dc2626; }
        .sub-text {
            font-size: 8px;
            color: #9ca3af;
            font-style: italic;
        }

        .total-row {
            border-top: 1px solid #e5e7eb;
            font-weight: bold;
            background-color: #f9fafb;
        }

        /* PENYESUAIAN MANUAL */
        .adjustment-box {
            background-color: #fefce8;
            border: 1px solid #fef08a;
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        /* TAKE HOME PAY BOX */
        .net-salary-table {
            width: 100%;
            background-color: #eff6ff;
            border: 2px solid #bfdbfe;
            border-radius: 6px;
            padding: 10px 15px;
            margin-bottom: 25px;
        }

        /* TANDA TANGAN */
        .signature-table {
            width: 100%;
            margin-top: 20px;
            text-align: center;
        }
        .signature-space {
            height: 45px;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT PERUSAHAAN -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: top;">
                <div class="company-title">PT TEKNOLOGI NUSANTARA</div>
                <div class="company-sub">Gedung Cyber Tower Lt. 8, Jl. HR Rasuna Said, Jakarta Selatan</div>
                <div class="company-sub">Email: hrd@teknologinusantara.co.id | Telp: (021) 555-0192</div>
            </td>
            <td style="text-align: right; vertical-align: top;">
                <span class="slip-badge">SLIP GAJI</span>
                <div style="font-size: 11px; font-weight: bold; margin-top: 4px;">
                    Periode: {{ $monthName }} {{ $payroll->period_year }}
                </div>
                <div class="sub-text" style="font-family: monospace;">ID: {{ $payroll->payroll_id }}</div>
            </td>
        </tr>
    </table>

    <!-- INFORMASI KARYAWAN -->
    <table class="info-table">
        <tr>
            <td class="label">ID Karyawan</td>
            <td style="font-weight: bold; font-family: monospace;">: {{ $payroll->employee->employee_id ?? '-' }}</td>
            <td class="label">Departemen</td>
            <td>: {{ $payroll->employee->department ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Nama Karyawan</td>
            <td style="font-weight: bold;">: {{ $payroll->employee->full_name ?? '-' }}</td>
            <td class="label">Email</td>
            <td>: {{ $payroll->employee->email ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Jabatan</td>
            <td>: {{ $payroll->employee->job_title ?? '-' }}</td>
            <td class="label">Status Transfer</td>
            <td>: <strong>{{ $payroll->is_transferred ? 'Ditransfer (Lunas)' : 'Belum Ditransfer' }}</strong></td>
        </tr>
    </table>

    <!-- TABEL DUA KOLOM (PENDAPATAN VS POTONGAN) -->
    <table class="layout-table">
        <tr>
            <!-- KOLOM PENDAPATAN -->
            <td class="layout-col">
                <div class="card">
                    <div class="card-header-green">A. Pendapatan (Earnings)</div>
                    <table class="item-table">
                        <tr>
                            <td>Gaji Pokok</td>
                            <td class="text-right font-mono">Rp {{ number_format($payroll->basic_salary, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Tunjangan Jabatan</td>
                            <td class="text-right font-mono text-green">Rp {{ number_format($payroll->position_allowance, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Tunjangan Makan</td>
                            <td class="text-right font-mono text-green">Rp {{ number_format($payroll->meal_allowance, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Tunjangan Transport</td>
                            <td class="text-right font-mono text-green">Rp {{ number_format($payroll->transport_allowance, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>
                                Upah Lembur
                                @if(!empty($payroll->calculation_details['overtime_text']))
                                    <div class="sub-text">{{ $payroll->calculation_details['overtime_text'] }}</div>
                                @endif
                            </td>
                            <td class="text-right font-mono text-green">Rp {{ number_format($payroll->total_overtime, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="total-row">
                            <td style="padding-top: 8px; padding-bottom: 8px;">Total Pendapatan</td>
                            <td class="text-right font-mono text-green" style="padding-top: 8px; padding-bottom: 8px;">
                                Rp {{ number_format($payroll->gross_salary, 0, ',', '.') }}
                            </td>
                        </tr>
                    </table>
                </div>
            </td>

            <td class="col-space"></td>

            <!-- KOLOM POTONGAN -->
            <td class="layout-col">
                <div class="card">
                    <div class="card-header-red">B. Potongan (Deductions)</div>
                    <table class="item-table">
                        <tr>
                            <td>
                                Potongan Alfa
                                @if(!empty($payroll->calculation_details['absence_text']))
                                    <div class="sub-text">{{ $payroll->calculation_details['absence_text'] }}</div>
                                @endif
                            </td>
                            <td class="text-right font-mono text-red">-Rp {{ number_format($payroll->absence_deduction, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>
                                Potongan Terlambat
                                @if(!empty($payroll->calculation_details['late_text']))
                                    <div class="sub-text">{{ $payroll->calculation_details['late_text'] }}</div>
                                @endif
                            </td>
                            <td class="text-right font-mono text-red">-Rp {{ number_format($payroll->late_deduction, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>BPJS Kesehatan</td>
                            <td class="text-right font-mono text-red">-Rp {{ number_format($payroll->bpjs_health, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>BPJS JHT</td>
                            <td class="text-right font-mono text-red">-Rp {{ number_format($payroll->bpjs_jht, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>BPJS JP</td>
                            <td class="text-right font-mono text-red">-Rp {{ number_format($payroll->bpjs_jp, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Pajak PPh 21</td>
                            <td class="text-right font-mono text-red">-Rp {{ number_format($payroll->pph21_deduction, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="total-row">
                            <td style="padding-top: 8px; padding-bottom: 8px;">Total Potongan</td>
                            <td class="text-right font-mono text-red" style="padding-top: 8px; padding-bottom: 8px;">
                                -Rp {{ number_format($payroll->total_deductions, 0, ',', '.') }}
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- PENYESUAIAN MANUAL (JIKA ADA) -->
    @if($payroll->adjustment != 0)
    <table class="adjustment-box" style="width: 100%;">
        <tr>
            <td>
                <strong>Penyesuaian Manual (Adjustment)</strong>
                <div class="sub-text" style="color: #854d0e;">{{ $payroll->adjustment_note ?? 'Tidak ada catatan' }}</div>
            </td>
            <td class="text-right font-mono" style="font-size: 12px; font-weight: bold; color: #854d0e;">
                Rp {{ number_format($payroll->adjustment, 0, ',', '.') }}
            </td>
        </tr>
    </table>
    @endif

    <!-- GAJI BERSIH (TAKE HOME PAY) -->
    <table class="net-salary-table">
        <tr>
            <td>
                <div style="font-size: 10px; font-weight: bold; color: #1e3a8a; text-transform: uppercase;">
                    Penerimaan Bersih (Take Home Pay)
                </div>
                <div style="font-size: 8px; color: #3b82f6;">
                    Total Pendapatan dikurangi Total Potongan + Penyesuaian
                </div>
            </td>
            <td class="text-right font-mono" style="font-size: 16px; font-weight: bold; color: #1e3a8a;">
                Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}
            </td>
        </tr>
    </table>

    <!-- TANDA TANGAN VALIDASI -->
    <table class="signature-table">
        <tr>
            <td style="width: 50%;">
                Penerima,
                <div class="signature-space"></div>
                <div style="font-weight: bold; text-decoration: underline;">{{ $payroll->employee->full_name ?? '-' }}</div>
                <div style="color: #6b7280; font-size: 9px;">Karyawan</div>
            </td>
            <td style="width: 50%;">
                Jakarta, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}
                <div style="color: #6b7280; font-size: 9px; margin-bottom: 2px;">Manager Keuangan / HRD</div>
                <div class="signature-space"></div>
                <div style="font-weight: bold; text-decoration: underline;">PT TEKNOLOGI NUSANTARA</div>
                <div style="color: #6b7280; font-size: 9px;">Payroll Department</div>
            </td>
        </tr>
    </table>

</body>
</html>