<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Define roles with their custom IDs
        $roles = [
            ['role_id' => 'R01', 'name' => 'System Administrator'],
            ['role_id' => 'R02', 'name' => 'HR Manager'],
            ['role_id' => 'R03', 'name' => 'Finance'],
            ['role_id' => 'R04', 'name' => 'Employee'],
        ];

        // Create roles
        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['role_id' => $roleData['role_id']],
                ['role_name' => $roleData['name']] // Disesuaikan dengan nama kolom kita
            );
        }

        // Assign permissions to roles
        // $this->assignPermissions();

        $this->command->info('Roles created successfully!');
        $this->command->info('Total roles created: ' . count($roles));
    }

    /**
     * Assign permissions to roles menggunakan Eloquent Sync (Lebih Bersih & Aman)
     */
    // private function assignPermissions(): void
    // {
    //     // Get all permissions
    //     $allPermissions = Permission::all();

    //     // 1. Admin gets all permissions
    //     $adminRole = Role::where('role_name', 'Admin')->first();
    //     if ($adminRole) {
    //         // sync() akan otomatis menghapus yang lama dan memasukkan yang baru di tabel pivot
    //         $adminRole->permissions()->sync($allPermissions->pluck('permission_id'));
    //     }

    //     // 2. CEO gets most permissions
    //     $ceoRole = Role::where('role_name', 'CEO')->first();
    //     if ($ceoRole) {
    //         $ceoPermissions = $allPermissions->whereIn('name', [
    //             'menu_laporan_keuangan', 'view_any_laporan_keuangan', 'view_laporan_keuangan',
    //             'menu_laporan_kinerja', 'view_any_laporan_kinerja', 'view_laporan_kinerja',
    //         ])->pluck('permission_id');

    //         $ceoRole->permissions()->sync($ceoPermissions);
    //     }

    //     // 3. Manager HRD permissions
    //     $managerHRDRole = Role::where('role_name', 'Manager HRD')->first();
    //     if ($managerHRDRole) {
    //         $managerHRDPermissions = $allPermissions->whereIn('name', [
    //             'view_any_karyawan', 'view_karyawan', 'create_karyawan', 'update_karyawan', 'delete_karyawan', 'restore_karyawan',
    //             'view_any_role', 'view_role',
    //             'view_any_absensi', 'view_absensi', 'create_absensi', 'update_absensi', 'delete_absensi', 'restore_absensi',
    //             'view_any_lembur', 'view_lembur', 'create_lembur', 'update_lembur', 'delete_lembur', 'restore_lembur',
    //             'view_any_cuti', 'view_cuti', 'create_cuti', 'update_cuti', 'delete_cuti', 'restore_cuti',
    //             'view_any_izin', 'view_izin', 'create_izin', 'update_izin', 'delete_izin', 'restore_izin',
    //             'view_any_penggajian', 'view_penggajian', 'update_penggajian', 'delete_penggajian', 'restore_penggajian',
    //         ])->pluck('permission_id');

    //         $managerHRDRole->permissions()->sync($managerHRDPermissions);
    //     }

    //     // 4. Staff HRD permissions
    //     $staffHRDRole = Role::where('role_name', 'Staff HRD')->first();
    //     if ($staffHRDRole) {
    //         $staffHRDPermissions = $allPermissions->whereIn('name', [
    //             'view_any_karyawan', 'view_karyawan', 'create_karyawan', 'update_karyawan',
    //             'view_any_absensi', 'view_absensi', 'create_absensi', 'update_absensi',
    //             'view_any_lembur', 'view_lembur', 'create_lembur', 'update_lembur',
    //             'view_any_cuti', 'view_cuti', 'create_cuti', 'update_cuti',
    //             'view_any_izin', 'view_izin', 'create_izin', 'update_izin',
    //             'view_any_penggajian', 'view_penggajian', 'create_penggajian', 'update_penggajian', 'delete_penggajian', 'restore_penggajian',
    //         ])->pluck('permission_id');

    //         $staffHRDRole->permissions()->sync($staffHRDPermissions);
    //     }

    //     // 5. Manager Finance & Account Payment permissions
    //     $financePermissions = $allPermissions->whereIn('name', [
    //         'view_any_penggajian', 'view_penggajian', 'update_penggajian', 'delete_penggajian', 'restore_penggajian',
    //     ])->pluck('permission_id');

    //     $managerFinanceRole = Role::where('role_name', 'Manager Finance')->first();
    //     if ($managerFinanceRole) $managerFinanceRole->permissions()->sync($financePermissions);

    //     $accountPaymentRole = Role::where('role_name', 'Account Payment')->first();
    //     if ($accountPaymentRole) $accountPaymentRole->permissions()->sync($financePermissions);

    //     // 6. Karyawan permissions
    //     $karyawanRole = Role::where('role_name', 'Karyawan')->first();
    //     if ($karyawanRole) {
    //         $karyawanPermissions = $allPermissions->whereIn('name', [
    //             'view_absensi', 'create_absensi', 'update_absensi',
    //             'view_lembur', 'create_lembur', 'update_lembur',
    //             'view_cuti', 'create_cuti', 'update_cuti'
    //         ])->pluck('permission_id');

    //         $karyawanRole->permissions()->sync($karyawanPermissions);
    //     }

    //     $this->command->info('Permissions assigned to roles successfully!');
    // }
}