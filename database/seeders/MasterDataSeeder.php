<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Role;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        Role::updateOrCreate(['role_id' => 'R01'], ['role_name' => 'System Administrator']);
        Role::updateOrCreate(['role_id' => 'R02'], ['role_name' => 'HR Manager']);
        Role::updateOrCreate(['role_id' => 'R03'], ['role_name' => 'Finance']);
        Role::updateOrCreate(['role_id' => 'R04'], ['role_name' => 'Employee']);

        // Company
        $company = Company::updateOrCreate(
            ['company_id' => 'C0001'],
            [
                'company_name' => 'PT Teknologi Nusantara',
                'email'        => 'info@teknologinusantara.com',
                'phone_number' => '03187654321',
                'clock_in_time'  => '08:00:00',
                'clock_out_time' => '17:00:00',
            ]
        );

        // Branch
        Branch::updateOrCreate(
            ['branch_id' => 'B0001'],
            [
                'company_id'    => $company->company_id,
                'branch_name'   => 'Kantor Pusat Surabaya',
                'address'       => 'Jl. Ketintang, Surabaya',
                'latitude'      => -7.311009,
                'longitude'     => 112.728790,
                'radius_meters' => 100,
            ]
        );
    }
}