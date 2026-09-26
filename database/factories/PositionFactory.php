<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class PositionFactory extends Factory
{
    public function definition(): array
    {
        $jabatan = ['Manager', 'Supervisor', 'Senior Staff', 'Junior Staff', 'Intern'];

        return [
            // Jika Factory dipanggil terpisah, ia akan membuat Department baru.
            // Tapi nanti di Seeder kita akan mengaitkannya dengan Department yang sudah ada.
            'department_id' => Department::factory(),
            'nama_jabatan' => $this->faker->randomElement($jabatan),
            // Standar gaji antara 4 juta hingga 20 juta
            'standar_gaji_pokok' => $this->faker->numberBetween(4000000, 20000000),
        ];
    }
}
