<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Get database connection.
     */
    public function getConnection()
    {
        return config('veltrix.database.connection') ?: config('admin.database.connection') ?: config('database.default');
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $usersTable = config('veltrix.database.users_table', config('admin.database.users_table', 'admin_users'));
        $rolesTable = config('veltrix.database.roles_table', config('admin.database.roles_table', 'admin_roles'));
        $permissionsTable = config('veltrix.database.permissions_table', config('admin.database.permissions_table', 'admin_permissions'));
        $menuTable = config('veltrix.database.menu_table', config('admin.database.menu_table', 'admin_menu'));
        $roleUsersTable = config('veltrix.database.role_users_table', config('admin.database.role_users_table', 'admin_role_users'));
        $rolePermissionsTable = config('veltrix.database.role_permissions_table', config('admin.database.role_permissions_table', 'admin_role_permissions'));
        $userPermissionsTable = config('veltrix.database.user_permissions_table', config('admin.database.user_permissions_table', 'admin_user_permissions'));
        $roleMenuTable = config('veltrix.database.role_menu_table', config('admin.database.role_menu_table', 'admin_role_menu'));
        $operationLogTable = config('veltrix.database.operation_log_table', config('admin.database.operation_log_table', 'admin_operation_log'));

        if (!Schema::hasTable($usersTable)) {
            Schema::create($usersTable, function (Blueprint $table) {
                $table->increments('id');
                $table->string('username', 190)->unique();
                $table->string('password', 255);
                $table->string('name');
                $table->string('avatar')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable($rolesTable)) {
            Schema::create($rolesTable, function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 50)->unique();
                $table->string('slug', 50)->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable($permissionsTable)) {
            Schema::create($permissionsTable, function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 50)->unique();
                $table->string('slug', 50)->unique();
                $table->string('http_method')->nullable();
                $table->text('http_path')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable($menuTable)) {
            Schema::create($menuTable, function (Blueprint $table) {
                $table->increments('id');
                $table->integer('parent_id')->default(0);
                $table->integer('order')->default(0);
                $table->string('title', 50);
                $table->string('icon', 50);
                $table->string('uri')->nullable();
                $table->string('permission')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable($roleUsersTable)) {
            Schema::create($roleUsersTable, function (Blueprint $table) {
                $table->integer('role_id');
                $table->integer('user_id');
                $table->index(['role_id', 'user_id']);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable($rolePermissionsTable)) {
            Schema::create($rolePermissionsTable, function (Blueprint $table) {
                $table->integer('role_id');
                $table->integer('permission_id');
                $table->index(['role_id', 'permission_id']);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable($userPermissionsTable)) {
            Schema::create($userPermissionsTable, function (Blueprint $table) {
                $table->integer('user_id');
                $table->integer('permission_id');
                $table->index(['user_id', 'permission_id']);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable($roleMenuTable)) {
            Schema::create($roleMenuTable, function (Blueprint $table) {
                $table->integer('role_id');
                $table->integer('menu_id');
                $table->index(['role_id', 'menu_id']);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable($operationLogTable)) {
            Schema::create($operationLogTable, function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('path');
                $table->string('method', 10);
                $table->string('ip');
                $table->text('input');
                $table->index('user_id');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $usersTable = config('veltrix.database.users_table', config('admin.database.users_table', 'admin_users'));
        $rolesTable = config('veltrix.database.roles_table', config('admin.database.roles_table', 'admin_roles'));
        $permissionsTable = config('veltrix.database.permissions_table', config('admin.database.permissions_table', 'admin_permissions'));
        $menuTable = config('veltrix.database.menu_table', config('admin.database.menu_table', 'admin_menu'));
        $roleUsersTable = config('veltrix.database.role_users_table', config('admin.database.role_users_table', 'admin_role_users'));
        $rolePermissionsTable = config('veltrix.database.role_permissions_table', config('admin.database.role_permissions_table', 'admin_role_permissions'));
        $userPermissionsTable = config('veltrix.database.user_permissions_table', config('admin.database.user_permissions_table', 'admin_user_permissions'));
        $roleMenuTable = config('veltrix.database.role_menu_table', config('admin.database.role_menu_table', 'admin_role_menu'));
        $operationLogTable = config('veltrix.database.operation_log_table', config('admin.database.operation_log_table', 'admin_operation_log'));

        Schema::dropIfExists($usersTable);
        Schema::dropIfExists($rolesTable);
        Schema::dropIfExists($permissionsTable);
        Schema::dropIfExists($menuTable);
        Schema::dropIfExists($userPermissionsTable);
        Schema::dropIfExists($roleUsersTable);
        Schema::dropIfExists($rolePermissionsTable);
        Schema::dropIfExists($roleMenuTable);
        Schema::dropIfExists($operationLogTable);
    }
};
