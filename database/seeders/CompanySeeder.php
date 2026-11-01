<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::create([
            'company_id'               => 'CMP01',
            'company_name'             => 'PT Cipta Teknologi',
            'email'                    => 'hrd@ciptateknologi.com',
            'phone_number'             => '031-1234567',
            'clock_in_time'            => '08:00:00',
            'clock_out_time'           => '17:00:00',
            'late_penalty'             => 50000,
            'bpjs_health_percent'      => 0.0100,
            'bpjs_jht_percent'         => 0.0200,
            'bpjs_jp_percent'          => 0.0100,
            'bpjs_health_salary_limit' => 12000000,
            'bpjs_jp_salary_limit'     => 10547400,
        ]);
    }
}