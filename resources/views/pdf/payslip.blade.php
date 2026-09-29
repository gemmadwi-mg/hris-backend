<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }

        /* HEADER (Kop Surat) */
        .header {
            text-align: center;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #111827;
            margin: 0;
            letter-spacing: 1px;
        }

        .company-address {
            font-size: 10px;
            color: #6b7280;
            margin: 2px 0 10px 0;
        }

        .slip-title {
            font-size: 14px;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            background-color: #f3f4f6;
            display: inline-block;
            padding: 5px 15px;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
        }

        .period {
            font-size: 11px;
            color: #4b5563;
            margin-top: 8px;
            font-weight: bold;
        }

        /* DATA KARYAWAN */
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 4px 0;
            font-size: 11px;
        }

        .info-table .label {
            font-weight: bold;
            width: 15%;
            color: #4b5563;
        }

        .info-table .colon {
            width: 2%;
            font-weight: bold;
        }

        .info-table .value {
            width: 33%;
            font-weight: bold;
            color: #111827;
        }

        /* TABEL KEUANGAN */
        .finance-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .finance-table th {
            background-color: #f9fafb;
            border: 1px solid #d1d5db;
            padding: 10px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            color: #374151;
        }

        .finance-table th.right {
            text-align: right;
        }

        .finance-table td {
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            vertical-align: top;
            font-size: 12px;
        }

        .amount {
            text-align: right;
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
        }

        /* BARIS TOTAL */
        .subtotal-row td {
            font-weight: bold;
            background-color: #f3f4f6;
            border-top: 2px solid #d1d5db;
        }

        .grand-total-row td {
            background-color: #ecfdf5;
            border: 2px solid #10b981;
            padding: 12px 10px;
        }

        .grand-total-label {
            text-align: right;
            font-size: 13px;
            color: #065f46;
        }

        .grand-total-amount {
            text-align: right;
            font-size: 16px;
            color: #047857;
            font-family: 'Courier New', Courier, monospace;
        }

        /* TANDA TANGAN */
        .footer-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
            text-align: center;
            font-size: 11px;
        }

        .footer-table td {
            width: 50%;
            vertical-align: bottom;
            height: 90px;
        }

        .signature-line {
            border-top: 1px solid #111827;
            width: 60%;
            margin: 0 auto;
            padding-top: 5px;
            font-weight: bold;
        }

        /* UTILITAS */
        .text-red {
            color: #dc2626;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1 class="company-name">PT. ENTERPRISE NUSANTARA (SEAL)</h1>
        <p class="company-address">Gedung Pusat Surabaya, Jawa Timur, Indonesia | Telp: (031) 123456</p>
        <div class="slip-title">Slip Gaji Karyawan</div>
        <!-- Ubah angka bulan jadi nama bulan -->
        <p class="period">Periode: {{ date('F', mktime(0, 0, 0, $payroll->bulan, 1)) }} {{ $payroll->tahun }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Nama Lengkap</td>
            <td class="colon">:</td>
            <td class="value">{{ $employee->nama_lengkap }}</td>
            <!-- PANGGIL RELASI DEPARTMENT -->
            <td class="label">Departemen</td>
            <td class="colon">:</td>
            <td class="value">{{ $jabatan && $jabatan->department ? $jabatan->department->nama_departemen : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Nomor Induk (NIP)</td>
            <td class="colon">:</td>
            <td class="value">{{ $employee->nip }}</td>
            <!-- SESUAIKAN NAMA KOLOM JABATAN -->
            <td class="label">Jabatan</td>
            <td class="colon">:</td>
            <td class="value">{{ $jabatan ? $jabatan->nama_jabatan : '-' }}</td>
        </tr>
    </table>

    <table class="finance-table">
        <thead>
            <tr>
                <th colspan="2">A. PENDAPATAN</th>
                <th colspan="2">B. POTONGAN</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1. Gaji Pokok</td>
                <td class="amount">Rp {{ number_format($payroll->gaji_pokok, 0, ',', '.') }}</td>
                <td>1. Potongan Keterlambatan / Alfa</td>
                <td class="amount text-red">Rp {{ number_format($payroll->potongan, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>2. Tunjangan Jabatan</td>
                <td class="amount">Rp {{ number_format($payroll->tunjangan, 0, ',', '.') }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr class="subtotal-row">
                <td>Total Pendapatan</td>
                <td class="amount">Rp {{ number_format($payroll->gaji_pokok + $payroll->tunjangan, 0, ',', '.') }}</td>
                <td>Total Potongan</td>
                <td class="amount text-red">Rp {{ number_format($payroll->potongan, 0, ',', '.') }}</td>
            </tr>
            <!-- Spacer -->
            <tr>
                <td colspan="4" style="border: none; padding: 5px;"></td>
            </tr>

            <tr class="grand-total-row">
                <td colspan="2" class="grand-total-label"><strong>TAKE HOME PAY (GAJI BERSIH YANG DITERIMA)
                        :</strong></td>
                <td colspan="2" class="grand-total-amount"><strong>Rp
                        {{ number_format($payroll->total_gaji_bersih, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <table class="footer-table">
        <tr>
            <td>
                <p>Mengetahui,</p>
                <br><br><br>
                <div class="signature-line">Manajer HRD</div>
            </td>
            <td>
                <p>Surabaya, {{ $tanggal_cetak }}</p>
                <p>Penerima,</p>
                <br><br><br>
                <div class="signature-line">{{ $employee->nama_lengkap }}</div>
            </td>
        </tr>
    </table>

</body>

</html>
