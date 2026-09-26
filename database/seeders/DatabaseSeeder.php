<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat 4 Departemen
        $departments = Department::factory()->count(4)->create();

        // 2. Untuk setiap Departemen, buat 3 Jabatan
        foreach ($departments as $department) {
            $positions = Position::factory()->count(3)->create([
                'department_id' => $department->id,
            ]);

            // 3. Untuk setiap Jabatan, isi dengan 5 Karyawan
            foreach ($positions as $position) {
                Employee::factory()->count(5)->create([
                    'position_id' => $position->id,
                ]);
            }
        }

        // Hasil akhir: 4 Departemen, 12 Jabatan, dan 60 Karyawan.
        // Jumlah yang sangat ideal untuk mengetes fitur Pagination di API Anda.
    }
}
