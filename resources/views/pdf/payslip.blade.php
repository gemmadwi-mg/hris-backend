<!DOCTYPE html>
<html>

<head>
    <title>Slip Gaji</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 14px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .table-info {
            width: 100%;
            margin-bottom: 20px;
        }

        .table-info td {
            padding: 5px;
        }

        .amount {
            text-align: right;
            font-weight: bold;
            font-size: 18px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>PT. ENTERPRISE HRIS INDONESIA</h2>
        <p>Slip Gaji Karyawan - Periode {{ $bulan_tahun }}</p>
    </div>

    <!-- Ubah bagian tabel info menjadi seperti ini -->
    <table class="table-info">
        <tr>
            <td><strong>NIP</strong></td>
            <td>: {{ $employee->nip }}</td>
            <td><strong>Departemen</strong></td>
            <td>: {{ $departemen }}</td>
        </tr>
        <tr>
            <td><strong>Nama</strong></td>
            <td>: {{ $employee->nama_lengkap }}</td>
            <td><strong>Jabatan</strong></td>
            <td>: {{ $jabatan }}</td>
        </tr>
    </table>

    <hr>

    <h3>Rincian Pendapatan</h3>
    <p>Gaji Pokok: Rp {{ number_format($gaji_pokok, 0, ',', '.') }}</p>
    <!-- Di aplikasi nyata, Anda bisa menambahkan loop untuk tunjangan dan potongan di sini -->

    <hr>
    <p class="amount">Total Gaji Bersih: Rp {{ number_format($gaji_pokok, 0, ',', '.') }}</p>

    <p style="text-align: right; margin-top: 50px;">
        Ditransfer ke: {{ $employee->nama_bank }} ({{ $employee->nomor_rekening }})<br><br><br>
        <strong>Finance Manager</strong>
    </p>
</body>

</html>
