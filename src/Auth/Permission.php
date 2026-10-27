<?php

namespace Veltrix\Auth;

use Illuminate\Http\Exceptions\HttpResponseException;
use Veltrix\Facades\Veltrix;

class Permission
{
    /**
     * Check if current user has permission(s).
     */
    public static function check(string|array $permission): bool
    {
        if (static::isAdministrator()) {
            return true;
        }

        $permissions = (array)$permission;

        foreach ($permissions as $perm) {
            if (Veltrix::user()?->cannot($perm)) {
                static::error();
            }
        }

        return true;
    }

    /**
     * Allow only specified roles.
     */
    public static function allow(string|array $roles): bool
    {
        if (static::isAdministrator()) {
            return true;
        }

        if (!Veltrix::user()?->inRoles((array)$roles)) {
            static::error();
        }

        return true;
    }

    /**
     * Deny specified roles.
     */
    public static function deny(string|array $roles): bool
    {
        if (static::isAdministrator()) {
            return true;
        }

        if (Veltrix::user()?->inRoles((array)$roles)) {
            static::error();
        }

        return true;
    }

    /**
     * Free route from permission check.
     */
    public static function free(): bool
    {
        return true;
    }

    /**
     * Send error response.
     */
    public static function error(): void
    {
        $message = veltrix_trans('deny') ?: 'Permission Denied';

        if (request()->expectsJson() || request()->is('*/_handle_*')) {
            abort(403, $message);
        }

        $content = Veltrix::content()->withError($message);
        throw new HttpResponseException(response($content->render(), 403));
    }

    public static function isAdministrator(): bool
    {
        $user = Veltrix::user();
        return (bool)$user?->isAdministrator();
    }
}
