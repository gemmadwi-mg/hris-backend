<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    protected static ?string $password;

    public function definition(): array
    {
        $fullName = fake()->name();
        $basicSalary = fake()->numberBetween(5000000, 15000000);

        return [
            // Generate ID unik format E0005, E0006, dst.
            'employee_id'       => 'E'.fake()->unique()->numberBetween(1000, 9999),
            'role_id'           => Role::where('role_name', 'Employee')->value('role_id') ?? 'R04',
            'company_id'        => Company::value('company_id') ?? 'C0001',
            'branch_id'         => Branch::value('branch_id') ?? 'B0001',
            'ptkp_group_id'     => fake()->randomElement(['G01', 'G02', 'G05', 'G06']),
            'nik'               => fake()->nik(),
            'full_name'         => $fullName,
            'date_of_birth'     => fake()->dateTimeBetween('-35 years', '-20 years')->format('Y-m-d'),
            'gender'            => fake()->randomElement(['Male', 'Female']),
            'address'           => fake()->address(),
            'phone_number'      => fake()->phoneNumber(),
            'email'             => fake()->unique()->safeEmail(),
            'password'          => static::$password ??= Hash::make('password123'),
            'job_title'         => fake()->randomElement([
                'Software Engineer', 'Frontend Developer', 'Backend Developer',
                'UI/UX Designer', 'QA Engineer', 'DevOps Engineer', 'HR Officer', 'Accounting Staff'
            ]),
            'department'        => fake()->randomElement([
                'Information Technology', 'Human Resources', 'Finance', 'Product & Design', 'Infrastructure'
            ]),
            'employment_status' => fake()->randomElement(['Permanent', 'Contract']),
            'join_date'         => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'basic_salary'      => $basicSalary,
            'position_allowance'=> $basicSalary * 0.10,
            'meal_allowance'    => 400000,
            'transport_allowance'=> 400000,
            'bank_name'         => fake()->randomElement(['BCA', 'Mandiri', 'BNI', 'BRI']),
            'account_number'    => fake()->bankAccountNumber(),
            'account_holder_name' => $fullName,
        ];
    }

    /**
     * Callback otomatis untuk membuat saldo cuti tahunan setelah karyawan berhasil di-generate.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Employee $employee) {
            EmployeeLeaveBalance::updateOrCreate(
                [
                    'employee_id' => $employee->employee_id,
                    'leave_type'  => 'Cuti Tahunan',
                    'year'        => date('Y'),
                ],
                [
                    'quota' => 12,
                    'used'  => fake()->numberBetween(0, 4),
                ]
            );
        });
    }
}