<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Daftar Nama Permission
        $permissionsList = [
            'view-any-employee',
            'view-employee',
            'create-employee',
            'edit-employee',
            'delete-employee',
            'view-any-attendance',
            'view-attendance',
            'create-attendance',
            'edit-attendance',
            'delete-attendance',
            'view-leave',
            'create-leave',
            'view-permit',
            'create-permit',
            'edit-permit',
            'view-overtime',
            'create-overtime',
            'edit-overtime',
            'view-payroll',
            'create-payroll',
            'edit-payroll',
            'disburse-payroll',
        ];

        $permissionIds = [];
        $i = 1;

        foreach ($permissionsList as $name) {
            // Format ID menjadi string 5 karakter (contoh: P0001, P0002)
            $customId = sprintf('P%04d', $i++);

            $perm = Permission::firstOrCreate(
                ['name' => $name],
                [
                    'permission_id' => $customId,
                    'guard_name' => 'api'
                ]
            );

            $permissionIds[$name] = $perm->permission_id;
        }

        // 2. Sync ke Masing-Masing Role

        // System Administrator (Semua Permission)
        $adminRole = Role::where('role_name', 'System Administrator')->first();
        if ($adminRole) {
            $adminRole->permissions()->sync(array_values($permissionIds));
        }

        // HR Manager
        $hrManagerRole = Role::where('role_name', 'HR Manager')->first();
        if ($hrManagerRole) {
            $hrManagerRole->permissions()->sync([
                $permissionIds['view-employee'],
                $permissionIds['create-employee'],
                $permissionIds['edit-employee'],
                $permissionIds['view-attendance'],
                $permissionIds['edit-attendance'],
                $permissionIds['view-leave'],
                $permissionIds['view-permit'],
                $permissionIds['view-overtime'],
                $permissionIds['view-payroll'],
                $permissionIds['create-payroll'],
                $permissionIds['edit-payroll'],
            ]);
        }

        // Finance
        $financeRole = Role::where('role_name', 'Finance')->first();
        if ($financeRole) {
            $financeRole->permissions()->sync([
                $permissionIds['view-payroll'],
                $permissionIds['disburse-payroll'],
            ]);
        }

        // Employee
        $employeeRole = Role::where('role_name', 'Employee')->first();
        if ($employeeRole) {
            $employeeRole->permissions()->sync([
                $permissionIds['view-attendance'],
                $permissionIds['create-attendance'],
                $permissionIds['view-leave'],
                $permissionIds['create-leave'],
                $permissionIds['view-permit'],
                $permissionIds['create-permit'],
                $permissionIds['view-overtime'],
                $permissionIds['create-overtime'],
                $permissionIds['view-payroll'],
            ]);
        }
    }
}