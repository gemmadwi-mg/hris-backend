<?php

namespace App\Imports;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class AttendancesImport implements ToModel, WithHeadingRow, WithChunkReading
{
    public function model(array $row): Model|array|null
    {
        $employee = Employee::where('nip', $row['nip'])->first();

        if ($employee) {
            // 1. FORMAT TANGGAL
            $tanggalMentah = $row['tanggal'];
            try {
                if (is_numeric($tanggalMentah)) {
                    $tanggalFormat = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggalMentah)->format('Y-m-d');
                } else {
                    $tanggalFormat = Carbon::parse($tanggalMentah)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                return null;
            }

            // 2. FORMAT JAM MASUK (Gabungkan Tanggal + Jam)
            $clockIn = null;
            if (!empty($row['jam_masuk'])) {
                $jamMasuk = $row['jam_masuk'];
                if (is_numeric($jamMasuk)) {
                    $jamMasuk = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($jamMasuk)->format('H:i:s');
                }
                $clockIn = Carbon::parse("$tanggalFormat $jamMasuk")->format('Y-m-d H:i:s');
            }

            // 3. FORMAT JAM PULANG (Gabungkan Tanggal + Jam)
            $clockOut = null;
            if (!empty($row['jam_pulang'])) {
                $jamPulang = $row['jam_pulang'];
                if (is_numeric($jamPulang)) {
                    $jamPulang = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($jamPulang)->format('H:i:s');
                }
                $clockOut = Carbon::parse("$tanggalFormat $jamPulang")->format('Y-m-d H:i:s');
            }

            // 4. SIMPAN KE DATABASE
            Attendance::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'tanggal' => $tanggalFormat, 
                ],
                [
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'status' => $row['status'] ?? 'Hadir',
                ]
            );
        }

        return null;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}