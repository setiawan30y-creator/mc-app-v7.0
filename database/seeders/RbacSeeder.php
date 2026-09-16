<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('code', 'ALMARA')->firstOrFail();

        $branch = Branch::where('tenant_id', $tenant->id)
            ->where('code', 'PUSAT')
            ->firstOrFail();

        $user = User::where('email', 'admin@almara.local')
            ->firstOrFail();

        $permissions = [
            [
                'name' => 'Lihat Dashboard',
                'code' => 'dashboard.view',
                'module' => 'dashboard',
                'description' => 'Melihat dashboard',
            ],
            [
                'name' => 'Kelola Pengaturan',
                'code' => 'settings.manage',
                'module' => 'settings',
                'description' => 'Mengelola pengaturan tenant',
            ],
            [
                'name' => 'Lihat Customer',
                'code' => 'customer.view',
                'module' => 'customer',
                'description' => 'Melihat daftar dan data customer',
            ],
            [
                'name' => 'Tambah Customer',
                'code' => 'customer.create',
                'module' => 'customer',
                'description' => 'Menambahkan customer baru',
            ],
            [
                'name' => 'Ubah Customer',
                'code' => 'customer.update',
                'module' => 'customer',
                'description' => 'Mengubah data customer',
            ],
        ];

        $role = Role::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'code' => 'tenant_admin',
            ],
            [
                'name' => 'Tenant Admin',
                'description' => 'Administrator tenant',
                'is_system' => false,
                'status' => 'active',
            ]
        );

        foreach ($permissions as $permissionData) {
            $permission = Permission::updateOrCreate(
                [
                    'code' => $permissionData['code'],
                ],
                [
                    'name' => $permissionData['name'],
                    'module' => $permissionData['module'],
                    'description' => $permissionData['description'],
                    'is_system' => true,
                    'status' => 'active',
                ]
            );

            RolePermission::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'branch_id' => $branch->id,
                    'role_id' => $role->id,
                    'permission_id' => $permission->id,
                ]
            );
        }

        UserRole::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'branch_id' => $branch->id,
                'user_id' => $user->id,
                'role_id' => $role->id,
            ]
        );
    }
}