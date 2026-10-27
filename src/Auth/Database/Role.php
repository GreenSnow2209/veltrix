<?php

namespace Veltrix\Auth\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = ['name', 'slug'];

    public function __construct(array $attributes = [])
    {
        $connection = config('veltrix.database.connection') ?: config('admin.database.connection') ?: config('database.default');
        $this->setConnection($connection);

        $table = config('veltrix.database.roles_table') ?: config('admin.database.roles_table', 'admin_roles');
        $this->setTable($table);

        parent::__construct($attributes);
    }

    public function administrators(): BelongsToMany
    {
        $pivotTable = config('veltrix.database.role_users_table', config('admin.database.role_users_table', 'admin_role_users'));
        $relatedModel = config('veltrix.database.users_model', config('admin.database.users_model', Administrator::class));

        return $this->belongsToMany($relatedModel, $pivotTable, 'role_id', 'user_id')->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        $pivotTable = config('veltrix.database.role_permissions_table', config('admin.database.role_permissions_table', 'admin_role_permissions'));
        $relatedModel = config('veltrix.database.permissions_model', config('admin.database.permissions_model', Permission::class));

        return $this->belongsToMany($relatedModel, $pivotTable, 'role_id', 'permission_id')->withTimestamps();
    }

    public function menus(): BelongsToMany
    {
        $pivotTable = config('veltrix.database.role_menu_table', config('admin.database.role_menu_table', 'admin_role_menu'));
        $relatedModel = config('veltrix.database.menu_model', config('admin.database.menu_model', Menu::class));

        return $this->belongsToMany($relatedModel, $pivotTable, 'role_id', 'menu_id')->withTimestamps();
    }

    public function isAdministrator(): bool
    {
        return $this->slug === 'administrator';
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function ($role) {
            $role->administrators()->detach();
            $role->permissions()->detach();
            $role->menus()->detach();
        });
    }
}
