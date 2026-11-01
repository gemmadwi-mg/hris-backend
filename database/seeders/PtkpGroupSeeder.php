<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PtkpGroupSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ptkp_groups')->insert([
            ['ptkp_group_id' => 'G01', 'ptkp_group_name' => 'TK0', 'description' => 'Single, no dependents', 'annual_amount' => 54000000, 'ter_category_id' => 'C01'],
            ['ptkp_group_id' => 'G02', 'ptkp_group_name' => 'TK1', 'description' => 'Single, 1 dependent', 'annual_amount' => 58500000, 'ter_category_id' => 'C01'],
            ['ptkp_group_id' => 'G03', 'ptkp_group_name' => 'TK2', 'description' => 'Single, 2 dependents', 'annual_amount' => 63000000, 'ter_category_id' => 'C02'],
            ['ptkp_group_id' => 'G04', 'ptkp_group_name' => 'TK3', 'description' => 'Single, 3 dependents', 'annual_amount' => 67500000, 'ter_category_id' => 'C02'],
            ['ptkp_group_id' => 'G05', 'ptkp_group_name' => 'K0', 'description' => 'Married, no dependents', 'annual_amount' => 58500000, 'ter_category_id' => 'C01'],
            ['ptkp_group_id' => 'G06', 'ptkp_group_name' => 'K1', 'description' => 'Married, 1 dependent', 'annual_amount' => 63000000, 'ter_category_id' => 'C02'],
            ['ptkp_group_id' => 'G07', 'ptkp_group_name' => 'K2', 'description' => 'Married, 2 dependents', 'annual_amount' => 67500000, 'ter_category_id' => 'C02'],
            ['ptkp_group_id' => 'G08', 'ptkp_group_name' => 'K3', 'description' => 'Married, 3 dependents', 'annual_amount' => 72000000, 'ter_category_id' => 'C03'],
        ]);
    }
}