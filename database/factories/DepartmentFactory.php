<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        // Daftar nama departemen yang masuk akal
        $departments = ['Human Resources', 'Information Technology', 'Finance', 'Marketing', 'Operations'];
        $name = $this->faker->unique()->randomElement($departments);

        return [
            'nama_departemen' => $name,
            // Membuat kode seperti HR, IT, FIN
            'kode_departemen' => strtoupper(substr($name, 0, 3)).$this->faker->unique()->numberBetween(10, 99),
        ];
    }
}
