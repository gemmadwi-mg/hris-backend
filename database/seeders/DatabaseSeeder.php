<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class, // WAJIB DIPANGGIL DI SINI
            TerCategorySeeder::class,
            PtkpGroupSeeder::class,
            TerRateSeeder::class,            // 2. Buat Roles dan tempelkan ke Permissions
            OperationalDataSeeder::class, // 3. Buat Karyawan (pakai Role) dan data lainnya
        ]);
    }
}
