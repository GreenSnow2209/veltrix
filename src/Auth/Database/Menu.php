<?php

namespace Veltrix\Auth\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Veltrix\Traits\ModelTree;

class Menu extends Model
{
    use ModelTree {
        ModelTree::boot as treeBoot;
    }

    protected $fillable = ['parent_id', 'order', 'title', 'icon', 'uri', 'permission'];

    public function __construct(array $attributes = [])
    {
        $connection = config('veltrix.database.connection') ?: config('admin.database.connection') ?: config('database.default');
        $this->setConnection($connection);

        $table = config('veltrix.database.menu_table') ?: config('admin.database.menu_table', 'admin_menu');
        $this->setTable($table);

        parent::__construct($attributes);
    }

    public function roles(): BelongsToMany
    {
        $pivotTable = config('veltrix.database.role_menu_table', config('admin.database.role_menu_table', 'admin_role_menu'));
        $relatedModel = config('veltrix.database.roles_model', config('admin.database.roles_model', Role::class));

        return $this->belongsToMany($relatedModel, $pivotTable, 'menu_id', 'role_id')->withTimestamps();
    }

    public function allNodes(): array
    {
        $query = static::query();

        if (config('veltrix.check_menu_roles', config('admin.check_menu_roles', true)) !== false) {
            $query->with('roles');
        }

        return $query->orderBy('order', 'asc')->get()->toArray();
    }

    public function withPermission(): bool
    {
        return (bool) config('veltrix.menu_bind_permission', config('admin.menu_bind_permission', true));
    }

    protected static function boot(): void
    {
        static::treeBoot();

        static::deleting(function ($model) {
            $model->roles()->detach();
        });
    }
}
