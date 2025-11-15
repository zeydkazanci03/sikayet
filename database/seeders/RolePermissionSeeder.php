<?php
namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        Permission::create(['name' => 'users.view', 'guard_name' => 'web']);
        Permission::create(['name' => 'users.create', 'guard_name' => 'web']);
        Permission::create(['name' => 'users.edit', 'guard_name' => 'web']);
        Permission::create(['name' => 'users.delete', 'guard_name' => 'web']);

        Permission::create(['name' => 'complaints.view', 'guard_name' => 'web']);
        Permission::create(['name' => 'complaints.moderate', 'guard_name' => 'web']);
        Permission::create(['name' => 'complaints.approve', 'guard_name' => 'web']);
        Permission::create(['name' => 'complaints.reject', 'guard_name' => 'web']);

        Permission::create(['name' => 'brands.view', 'guard_name' => 'web']);
        Permission::create(['name' => 'brands.manage', 'guard_name' => 'web']);
        Permission::create(['name' => 'brands.approve', 'guard_name' => 'web']);

        Permission::create(['name' => 'blog.manage', 'guard_name' => 'web']);
        Permission::create(['name' => 'settings.manage', 'guard_name' => 'web']);

        // Create roles
        $superAdmin = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        $admin = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $moderator = Role::create(['name' => 'moderator', 'guard_name' => 'web']);
        $contentManager = Role::create(['name' => 'content-manager', 'guard_name' => 'web']);

        // Assign permissions to roles
        $superAdmin->givePermissionTo(Permission::all());
        $admin->givePermissionTo(['users.view', 'users.create', 'users.edit', 'users.delete',
                                 'complaints.view', 'complaints.moderate', 'complaints.approve',
                                 'complaints.reject', 'brands.view', 'brands.manage',
                                 'brands.approve', 'blog.manage', 'settings.manage']);
        $moderator->givePermissionTo(['complaints.view', 'complaints.moderate']);
        $contentManager->givePermissionTo(['blog.manage']);
    }
}
