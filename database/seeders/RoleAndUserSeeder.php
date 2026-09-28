<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reset cache roles dan permissions milik Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Buat Roles
        $roleHR = Role::create(['name' => 'HR']);
        $roleManager = Role::create(['name' => 'Manager']);
        $roleKaryawan = Role::create(['name' => 'Karyawan']);

        // 3. Buat User Superadmin / HR
        $hr = User::create([
            'name' => 'HR Admin',
            'email' => 'admin@hris.com',
            'password' => bcrypt('password'),
        ]);
        $hr->assignRole($roleHR);

        // 4. Buat User Manager
        $manager = User::create([
            'name' => 'Manager Operasional',
            'email' => 'manager@hris.com',
            'password' => bcrypt('password'),
        ]);
        $manager->assignRole($roleManager);

        // 5. Buat User Karyawan Biasa
        $karyawan = User::create([
            'name' => 'Gemma Dwi Prasetya',
            'email' => 'karyawan@hris.com',
            'password' => bcrypt('password'),
        ]);
        $karyawan->assignRole($roleKaryawan);
    }
}
