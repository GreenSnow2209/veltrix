<?php

namespace Veltrix\Auth\Database;

use Illuminate\Support\Collection;

trait HasPermissions
{
    /**
     * Get all permissions of user.
     *
     * @return Collection
     */
    public function allPermissions(): Collection
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->merge($this->permissions);
    }

    /**
     * Check if user has permission.
     *
     * @param mixed $ability
     * @param array $arguments
     * @return bool
     */
    public function can($ability, $arguments = []): bool
    {
        if (empty($ability)) {
            return true;
        }

        if ($this->isAdministrator()) {
            return true;
        }

        if ($this->permissions->pluck('slug')->contains($ability)) {
            return true;
        }

        return $this->roles
            ->pluck('permissions')
            ->flatten()
            ->pluck('slug')
            ->contains($ability);
    }

    /**
     * Check if user has no permission.
     *
     * @param string $permission
     * @return bool
     */
    public function cannot(string $permission): bool
    {
        return !$this->can($permission);
    }

    /**
     * Check if user is administrator.
     *
     * @return bool
     */
    public function isAdministrator(): bool
    {
        return $this->isRole('administrator');
    }

    /**
     * Check if user has specified role.
     *
     * @param string $role
     * @return bool
     */
    public function isRole(string $role): bool
    {
        return $this->roles->pluck('slug')->contains($role);
    }

    /**
     * Check if user in any of the specified roles.
     *
     * @param array $roles
     * @return bool
     */
    public function inRoles(array $roles = []): bool
    {
        return $this->roles->pluck('slug')->intersect($roles)->isNotEmpty();
    }

    /**
     * If visible for roles.
     *
     * @param array $roles
     * @return bool
     */
    public function visible(array $roles = []): bool
    {
        if (empty($roles)) {
            return true;
        }

        $roles = array_column($roles, 'slug') ?: $roles;

        return $this->inRoles($roles) || $this->isAdministrator();
    }

    /**
     * Detach models from relationship on deletion.
     *
     * @return void
     */
    protected static function bootHasPermissions(): void
    {
        static::deleting(function ($model) {
            $model->roles()->detach();
            $model->permissions()->detach();
        });
    }
}
