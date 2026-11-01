<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TerCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ter_categories')->insert([
            ['ter_category_id' => 'C01', 'category_name' => 'Category A', 'description' => 'PTKP TK/0, K/0, TK/1'],
            ['ter_category_id' => 'C02', 'category_name' => 'Category B', 'description' => 'PTKP TK/2, K/1, TK/3, K/2'],
            ['ter_category_id' => 'C03', 'category_name' => 'Category C', 'description' => 'PTKP K/3'],
        ]);
    }
}
