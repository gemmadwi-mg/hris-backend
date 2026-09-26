<?php

namespace Database\Factories;

use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        $banks = ['BCA', 'Mandiri', 'BNI', 'BRI', 'BSI'];

        return [
            'position_id' => Position::factory(),
            'user_id' => null, // Dikosongkan dulu karena kita belum membuat sistem Login

            'nip' => 'NIP-'.$this->faker->unique()->numerify('####'),
            'nama_lengkap' => fake('id_ID')->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'nomor_telepon' => fake('id_ID')->phoneNumber(),
            'tanggal_bergabung' => $this->faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),

            'nama_bank' => $this->faker->randomElement($banks),
            'nomor_rekening' => $this->faker->numerify('##########'),
        ];
    }
}
