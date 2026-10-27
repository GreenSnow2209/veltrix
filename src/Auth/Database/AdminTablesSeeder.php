<?php

namespace Veltrix\Auth\Database;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminTablesSeeder extends Seeder
{
    /**
     * Optional custom admin credentials for seeding.
     */
    public static ?string $defaultPassword = null;
    public static string $defaultUsername = 'admin';
    public static string $defaultName = 'Administrator';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userModel = config('veltrix.database.users_model', config('admin.database.users_model', Administrator::class));
        $roleModel = config('veltrix.database.roles_model', config('admin.database.roles_model', Role::class));
        $permissionModel = config('veltrix.database.permissions_model', config('admin.database.permissions_model', Permission::class));
        $menuModel = config('veltrix.database.menu_model', config('admin.database.menu_model', Menu::class));

        // Use custom password if set, or env, or prompt/test, or fallback to 'admin' ONLY in testing environment
        $password = static::$defaultPassword
            ?: env('VELTRIX_ADMIN_PASSWORD', env('ADMIN_PASSWORD'))
            ?: (app()->environment('testing') ? 'admin' : Str::random(16));

        // Create initial administrator if none exists
        $user = $userModel::firstOrCreate(
            ['username' => static::$defaultUsername],
            [
                'name'     => static::$defaultName,
                'password' => Hash::make($password),
            ]
        );

        // Create Administrator Role
        $role = $roleModel::firstOrCreate(
            ['slug' => 'administrator'],
            ['name' => 'Administrator']
        );

        if (!$user->roles->contains($role->id)) {
            $user->roles()->attach($role->id);
        }

        // Create initial permissions
        $permissions = [
            [
                'name'        => 'All permission',
                'slug'        => '*',
                'http_method' => '',
                'http_path'   => '*',
            ],
            [
                'name'        => 'Dashboard',
                'slug'        => 'dashboard',
                'http_method' => 'GET',
                'http_path'   => '/',
            ],
            [
                'name'        => 'Login',
                'slug'        => 'auth.login',
                'http_method' => '',
                'http_path'   => "/auth/login\n/auth/logout",
            ],
            [
                'name'        => 'User setting',
                'slug'        => 'auth.setting',
                'http_method' => 'GET,PUT',
                'http_path'   => '/auth/setting',
            ],
            [
                'name'        => 'Auth management',
                'slug'        => 'auth.management',
                'http_method' => '',
                'http_path'   => "/auth/users*\n/auth/roles*\n/auth/permissions*\n/auth/menu*\n/auth/logs*",
            ],
        ];

        foreach ($permissions as $permData) {
            $permissionModel::firstOrCreate(
                ['slug' => $permData['slug']],
                $permData
            );
        }

        $allPermission = $permissionModel::where('slug', '*')->first();
        if ($allPermission && !$role->permissions->contains($allPermission->id)) {
            $role->permissions()->attach($allPermission->id);
        }

        // Add default menus
        $defaultMenus = [
            [
                'parent_id'  => 0,
                'order'      => 1,
                'title'      => 'Dashboard',
                'icon'       => 'layout-dashboard',
                'uri'        => '/',
                'permission' => 'dashboard',
            ],
            [
                'parent_id'  => 0,
                'order'      => 2,
                'title'      => 'Admin',
                'icon'       => 'shield-check',
                'uri'        => '',
                'permission' => 'auth.management',
            ],
            [
                'parent_id'  => 2,
                'order'      => 3,
                'title'      => 'Users',
                'icon'       => 'users',
                'uri'        => 'auth/users',
                'permission' => null,
            ],
            [
                'parent_id'  => 2,
                'order'      => 4,
                'title'      => 'Roles',
                'icon'       => 'user-check',
                'uri'        => 'auth/roles',
                'permission' => null,
            ],
            [
                'parent_id'  => 2,
                'order'      => 5,
                'title'      => 'Permissions',
                'icon'       => 'key',
                'uri'        => 'auth/permissions',
                'permission' => null,
            ],
            [
                'parent_id'  => 2,
                'order'      => 6,
                'title'      => 'Menu',
                'icon'       => 'menu',
                'uri'        => 'auth/menu',
                'permission' => null,
            ],
            [
                'parent_id'  => 2,
                'order'      => 7,
                'title'      => 'Operation log',
                'icon'       => 'file-text',
                'uri'        => 'auth/logs',
                'permission' => null,
            ],
        ];

        foreach ($defaultMenus as $menuData) {
            $menuModel::firstOrCreate(
                ['title' => $menuData['title'], 'parent_id' => $menuData['parent_id']],
                $menuData
            );
        }
    }
}
