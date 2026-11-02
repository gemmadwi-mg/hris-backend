<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $date = Carbon::parse(fake()->dateTimeBetween('-30 days', 'now'));
        $isLate = fake()->boolean(20); // 20% probabilitas terlambat

        $clockInHour = $isLate ? 8 : 7;
        $clockInMinute = $isLate ? fake()->numberBetween(1, 45) : fake()->numberBetween(30, 59);
        $clockInTime = $date->copy()->setTime($clockInHour, $clockInMinute, 0);

        $clockOutTime = $date->copy()->setTime(17, fake()->numberBetween(0, 30), 0);

        return [
            'attendance_id'        => 'AT' . $date->format('ymd') . Str::upper(Str::random(3)),
            'employee_id'          => Employee::inRandomOrder()->value('employee_id') ?? 'E0001',
            'branch_id'            => Branch::value('branch_id') ?? 'B0001',
            'date'                 => $date->format('Y-m-d'),
            'clock_in_time'        => $clockInTime->format('Y-m-d H:i:s'),
            'clock_out_time'       => $clockOutTime->format('Y-m-d H:i:s'),
            'clock_in_status'      => $isLate ? 'Late' : 'On Time',
            'clock_out_status'     => 'On Time',
            'clock_in_coordinates' => '-7.311009, 112.728790',
            'clock_out_coordinates'=> '-7.311009, 112.728790',
            'attendance_status'    => 'Present',
            'clock_in_photo'       => 'sample_in.jpg',
            'clock_out_photo'      => 'sample_out.jpg',
        ];
    }
}